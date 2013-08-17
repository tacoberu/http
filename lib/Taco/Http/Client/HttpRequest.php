<?php

/**
 * Description of Client of http
 *
 * PHP version 5.3
 *
 * @author	 Martin Takáč <taco@taco-beru.name>
 * @copyright  Copyright (c) 2010 Martin Takáč
 */

namespace Taco\Http\Client;


/**
 * Vrácena špatná, nezpracovatelná odpověď.
 */
class ResponseException extends \RuntimeException
{

	const GET = 10;
	const POST = 20;
	const PUT = 30;
	const DELETE = 40;

	public $content;
	public $info;


	/**
	 * Vrácena špatná, nezpracovatelná odpověď.
	 *
	 * @param $content Obsah odpovědi.
	 * @param $info Informace o komunikaci.
	 * @param message Text zprávy.
	 * @param $code Kod zprávy.
	 * @param $e původní výjimka.
	 */
	public function __construct($content, $info, $message, $code, $e)
	{
		$this->content = $content;
		$this->info = $info;
		parent::__construct($message, $code, $e);
	}


}



/**
 *	Požadavek na data serveru.
 *
 *	@author	 Martin Takáč <taco@taco-beru.name>
 */
class HttpRequest
{

	/**
	 *	Maximální počet pokusů ze špatného dotazu.
	 */
	const MAX_ATTEMPT = 9;
	

	/**
	 *	Seznam GET parametrů.
	 */
	private $gets = array();



	/**
	 *	Seznam POST parametrů.
	 */
	private $posts = array();



	/**
	 *	Mateřská relace clienta.
	 */
	private $session;
	


	/**
	 *	Adresa.
	 */
	private $domain;



	/**
	 *	Adresa.
	 */
	private $url;



	/**
	 *	Hlavičky dotazu.
	 */
	public $headers = array();



	/**
	 *	Tolerovaný timeout.
	 */
	public $timeout = 2;



	/**
	 *	Následovat přesměrování? Automaticky přesměrovávat?
	 */
	public $followLocation = True;



	/**
	 *	Vytvoření požadavku.
	 */
	public function __construct($session, $domain)
	{
		if (empty($domain)) {
			throw new \InvalidArgumentException('Empty domain.');
		}
		$this->session = $session;
		$this->domain = $domain;
		$this->status = (object) array (
				'code' => 200,
				'message' => ''
			);
	}



	/**
	 *	Náhled požadavku.
	 */
	public function __toString()
	{
		return 'GET: ' . $this->prepareUri() 
			. "\nPOST: " . $this->preparePostData();
	}



	/**
	 *	Nastaví GET parametr
	 *
	 *	@return self
	 */
	public function addGetParam($key, $value)
	{
		$this->gets[$key] = $value;

		return $this;
	}



	/**
	 *	Nastaví POST parametr
	 *
	 *	@return self
	 */
	public function addPostParam($key, $value)
	{
		$this->posts[$key] = $value;

		return $this;
	}



	/**
	 *	Nastaví POST jako jeden velký stream.
	 *
	 *	@return self
	 */
	public function setPostData($value)
	{
		$this->posts = $value;

		return $this;
	}



	/**
	 *	Odeslat požadavek jako GET
	 *
	 *	@return Taco\Http\Client\HttpResponse
	 */
	public function get()
	{
		$response = $this->sendRequestGet();
		$this->applyHeaders($response);
		return $response;
	}



	/**
	 *	Odeslat požadavek jako POST
	 *
	 *	@return Taco\Http\Client\HttpResponse
	 */
	public function post()
	{
		$response = $this->sendRequestPost();
		$this->applyHeaders($response);
		return $response;
	}



	/**
	 *	Zapíše hlavičky z odpovědi do session.
	 */
	private function applyHeaders(HttpResponse $response)
	{
		foreach ($response->headers as $head) {
			$var = explode(':', $head, 2);
			switch (strtolower($var[0])) {
				case 'set-cookie':
					$chip = explode(';', $var[1]);
					$par = explode('=', $chip[0], 2);
					$this->session->setCookie($par[0], $par[1]);
					break;
			}
		}
	}



	/**
	 * Adds HTTP header.
	 * @param  string  header name
	 * @param  string  header value
	 *
	 * @return HttpResponse  provides a fluent interface
	 */
	public function addHeader($key, $value)
	{
		$this->headers[] = trim($key) . ': ' . trim($value);
		return $this;
	}



	/**
	 *	Volani serveru.
	 */
	protected function sendRequestGet($count = 0)
	{
		$ch = curl_init($this->url = $this->prepareUri());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
		curl_setopt($ch, CURLOPT_HEADER, True); //		Zajimaji nas i hlavicky.
#		curl_setopt($ch, CURLOPT_NOBODY, True); //		Naopak telo ne.
#		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, True); //		Následovat presmerovani
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		
		if (count($this->session->cookies)) {
			curl_setopt($ch, CURLOPT_COOKIE, $this->prepareCookiesData());
		}
		if (count($this->headers) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		}

		//		Ziskame data dotazem na server.
		$content = curl_exec($ch);
		$info = curl_getinfo($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
#		$totaltime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
		curl_close ($ch);

		try {
			$response = $this->createResponse($content, $http_code);
			if (9 > $count) {
				switch ($response->status->code) {
					case 301:
					case 302:
						foreach ($response->headers as $row) {
							if (substr(strtolower($row), 0, 9) == 'location:') {
								$url = trim(substr($row, 9));
								//		Jedna se neabsolutni cestu.
								if (!strpos($url, '://')) {
									$url = substr($response->url, 0, strpos($response->url, '/', strpos($response->url, '://') + 3)) . $url;
								}
							}
						}
						$this->domain = $url;
						$response = $this->sendRequestGet(++$count);
						break;
				}
			}
			return $response;
		}
		catch (\RuntimeException $e) {
			if (self::MAX_ATTEMPT > $count) {
				return $this->sendRequestGet(++$count);
			}
			throw new ResponseException(
					$content, // Obsah odpovědi
					$info,	  // Informace o komunikaci.
					$e->getMessage(),
					ResponseException::GET,
					$e // původní výjimka
					);
		}
	}



	/**
	 *		Volani serveru.
	 */
	protected function sendRequestPost($count = 0)
	{
		$ch = curl_init($this->url = $this->prepareUri());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
		curl_setopt($ch, CURLOPT_HEADER, True); //		Zajimaji nas i hlavicky.
		curl_setopt($ch, CURLOPT_POST, True);
		
		$data = $this->preparePostData();
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
#		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, True);
		if (count($this->session->cookies)) {
			curl_setopt($ch, CURLOPT_COOKIE, $this->prepareCookiesData());
		}
		if (count($this->headers) > 0) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		}

		$content = curl_exec($ch);
		$response = curl_getinfo($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
#		$totaltime = curl_getinfo($ch, CURLINFO_TOTAL_TIME); 
		curl_close ($ch);

		try {
			return $this->createResponse($content, Null);
		}
		catch (\RuntimeException $e) {
			if (self::MAX_ATTEMPT > $count) {
				return $this->sendRequestPost(++$count);
			}
			throw new ResponseException(
					$content, // Obsah odpovědi
					$info,	  // Informace o komunikaci.
					$e->getMessage(),
					ResponseException::POST,
					$e // původní výjimka
					);
		}
	}



	/**
	 *	Vytvorit objekt s odpovedi.
	 */
	private function createResponse($content, $http_code)
	{
		if (empty($content)) {
			throw new \RuntimeException('Empty content [' . $this->url . ']');
		}

		while (substr($content, 0, 4) == 'HTTP') {
			//	Oddělit obsah a hlavičky
			$var = explode("\n\r\n", $content, 2);
			if (isset($var[0])) {
				$headers = $var[0];
			}
			if (isset($var[1])) {
				$content = $var[1];
			}
		}

		if (256 < $index = strpos($content, '</html>')) {
			 $content = substr($content, 0, $index + 7);
		}
		$headers = explode("\n", $headers);

		$vars = explode(' ', trim($headers[0]), 3);
#		if ($vars[1] != $http_code) {
#			throw new \Exception("V hlavicce je jina httpcode [{$vars[1]}], nez co vratil curl_getinfo() [$http_code].");
#		}
		if (count($vars) != 3) {
			print_r($vars);
			throw new \RuntimeException("Obsah [{$headers[0]}] nelze rozdělit na správné díly.");
		}
		$response = new HttpResponse($vars[0], $vars[1], $vars[2]);
		$response->url = $this->url;
		$response->setContent(trim($content));
		foreach ($headers as $header) {
			$response->addHeader($header);
		}

		return $response;
	}


	
	/**
	 *	Pripravi url vcetne parametru z getu.
	 */
	private function prepareUri()
	{
		if ($data = $this->prepareGetData()) {
			return $this->domain
				. '?'
				. $data;
		}
		return $this->domain;
	}



	/**
	 *	Zpracování get dat do řetězce.
	 */
	private function prepareGetData()
	{
		return http_build_query($this->gets);
	}



	/**
	 *	Zpracování post dat do pole.
	 */
	private function preparePostData()
	{
		if (is_array($this->posts)) {
			return http_build_query($this->posts);
		}
		return $this->posts;
	}



	/**
	 *	Zpracování cookies dat do řetězce.
	 */
	private function prepareCookiesData()
	{
		return http_build_query($this->session->cookies, '', '; ');
	}


}
