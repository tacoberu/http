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
 * Chybný dotaz.
 */
class FailedRequestException extends \RuntimeException
{

	/**
	 * Available types of requests
	 */
	public $method;

	/**
	 * Request url.
	 */
	public $url;

	/**
	 * Sending data.
	 */
	public $data;

	/**
	 * About comunication.
	 */
	public $info = array();


	/**
	 * Vrácena špatná, nezpracovatelná odpověď.
	 *
	 * @param $content Obsah odpovědi.
	 * @param $info Informace o komunikaci.
	 * @param message Text zprávy.
	 * @param $code Kod zprávy.
	 * @param $e původní výjimka.
	 */
	public function __construct($method, $url, $data, array $info, $message = '', $code = 0, $e = Null)
	{
		$this->method = $method;
		$this->url = $url;
		$this->data = $data;
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
	 * Available types of requests
	 */
	const GET = 'GET';
	const POST = 'POST';
	const PUT = 'PUT';
	const DELETE = 'DELETE';
	const HEAD = 'HEAD';
	const DOWNLOAD = 'DOWNLOAD';


	/**
	 *	Maximální počet pokusů ze špatného dotazu.
	 */
	const FAULT_TOLERANCE = 9;
	

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
	private $headers = array();



	/**
	 *	Tolerovaný timeout.
	 */
	public $timeout = 2;



	/**
	 *	Následovat přesměrování? Automaticky přesměrovávat?
	 */
	public $followRedirects = True;



	/**
	 * Maximální počet přesměrování.
	 */
	public $maxRedirects = 15;



	/**
	 * Maximální počet přesměrování.
	 */
	public $faultTolerance = self::FAULT_TOLERANCE;



	/**
	 * Nastavení CURL.
	 */
	private $options = array();



	/**
	 *	Vytvoření požadavku.
	 *	@param HttpSession $session
	 *	@param string $domain Doménová adresa.
	 *
	 *	@throw InvalidArgumentException
	 */
	public function __construct(HttpSession $session, $domain)
	{
		if (empty($domain)) {
			throw new \InvalidArgumentException('Empty domain.');
		}
		$this->session = $session;
		$this->domain = $domain;
		$this->timeout = $session->timeout;
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
			. "\nPOST: " . self::preparePostData($this->posts);
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
	 * Sets option for request
	 *
	 * @param string $option
	 * @param string $value
	 *
	 * @return HttpRequest
	 */
	public function setOption($option, $value)
	{
		$option = str_replace('CURLOPT_', '', strtoupper($option));
		$this->options[$option] = $value;

		if ($option === 'MAXREDIRS') {
			$this->maxRedirects = $value;
		}

		return $this;
	}



	/**
	 * Returns specific option value
	 * @param string $option
	 * @return string
	 */
	public function getOption($option)
	{
		$option = str_replace('CURLOPT_', '', strtoupper($option));
		if (isset($this->options[$option])) {
			return $this->options[$option];
		}

		return Null;
	}



	/**
	 * The maximum number of seconds to allow cURL functions to execute.
	 *
	 * @param int
	 *
	 * @return HttpRequest
	 */
	public function setTimeOut($seconds = 15)
	{
		$this->timeout = (int)$seconds;
		$this->setOption('timeout', $this->timeout);
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
		$url = $this->prepareUri();
		$data = $this->posts;

		if (!$data /* || !is_array($data) */ ){
			throw new \RuntimeException("Empty data fields, use Request::get(\$url) instead.");
		}

		return $this->sendRequest(self::POST, $url, $data);
	}



	/**
	 *	Odeslat požadavek jako PUT
	 *
	 *	@return Taco\Http\Client\HttpResponse
	 */
	public function put()
	{
		$url = $this->prepareUri();
		$data = $this->posts;

		if (!$data /* || !is_array($data) */ ){
			throw new \RuntimeException("Empty data fields, use Request::get(\$url) instead.");
		}

		return $this->sendRequest(self::PUT, $url, $data);
	}



	/**
	 * Makes a HTTP DELETE request to the specified $url with an optional array or string of $vars
	 * Returns a Response object if the request was successful, false otherwise
	 *
	 * @param string    [optional] $url
	 * @param array $post
	 *
	 * @return HttpResponse
	 */
	public function delete()
	{
		$url = $this->prepareUri();
		$data = $this->posts;

		return $this->sendRequest(self::DELETE, $url, $data);
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



	//	PROTECTED



	/**
	 * Makes an HTTP request of the specified $method to a $url with an optional array or string of $vars
	 * Returns a Curl\Response object if the request was successful, false otherwise
	 *
	 * @param string $method
	 * @param string $url
	 * @param array $data
	 * @param int $cycles
	 *
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 * @throws BadStatusException
	 * @throws FailedRequestException
	 *
	 * @return HttpResponse
	 */
	protected function sendRequest($method, $url, $data = array(), $cycles = 0)
	{
		if ($cycles > $this->maxRedirects) {
			throw new \RuntimeException("Redirect loop");
		}

#		$this->Error = NULL;
#		$used_proxies = 0;

		if (!is_string($url) && $url !== '') {
			throw new \InvalidArgumentException("Invalid URL: [$url].");
		}

#		do {
			$resource = curl_init($url);

			//$this->tryProxy($used_proxies++);
			$this->setRequestMethod($resource, $method);
			$this->setRequestOptions($resource, $url, $data);
			$this->setRequestHeaders($resource);
			$this->setRequestSession($resource);

			$response = curl_exec($resource);

			$errorno = curl_errno($resource);
			$errormsg = curl_error($resource);
			$info = curl_getinfo($resource);
#		}
#		while ($error == 6 && count($this->proxies) < $used_proxies);

		curl_close($resource);

		if ($response || ! $errorno) {
			$response = $this->createResponse($response, $info);
			$response->url = $url;
		}
		else {
			if ($this->faultTolerance > $cycles) {
				return $this->sendRequest($method, $url, $data, ++$cycles);
			}
			throw new FailedRequestException($method, $url, $data, $info, $errormsg, $errorno);
		}

/*
		if (!in_array($response->getHeader('Status-Code'), self::$badStatusCodes)) {
			$response_headers = $response->getHeaders();

			if (isset($response_headers['Location']) && $this->getFollowRedirects())  {
				$url = static::fixUrl($this->info['url'], $response_headers['Location']);

				if ($this->tryConfirmRedirect($response)) {
					$response = $this->sendRequest($this->getMethod(), (string)$url, $post, ++$cycles);
				}
			}

		} else {
			throw new BadStatusException('Response status: '.$response->getHeader('Status'), $this->info['http_code'], $response);
		}
*/

		//	Zaznamenat cookies
		$this->applyHeaders($response);

		return $response;
	}



	/**
	 * Set the associated Curl options for a request method
	 * @param string $method
	 */
	protected function setRequestMethod($resource, $method)
	{
		$method = strtoupper($method);
		switch ($method) {
			case self::HEAD:
				$this->setOption('nobody', TRUE);
				break;

			case self::GET:
			case self::DOWNLOAD:
				$this->setOption('httpget', TRUE);
				break;

			case self::POST:
				$this->setOption('post', TRUE);
				break;

//			case self::UPLOAD_FTP:
//				$this->setOption('upload', TRUE);
//				break;

			default:
				$this->setOption('customrequest', $method);
				break;
		}
	}




	/**
	 *
	 * @param
	 */
	protected function setRequestSession($resource)
	{
		if (count($this->session->cookies)) {
			curl_setopt($resource, CURLOPT_COOKIE, http_build_query($this->session->cookies, '', '; '));
		}
	}




	/**
	 * Sets the CURLOPT options for the current request
	 *
	 * @param string $url
	 */
	protected function setRequestOptions($resource, $url, $post = Null)
	{
		$this->setOption('url', $url);

		$post = self::preparePostData($post);
		if ($post) {
			$this->setOption('postfields', $post);
		}

		// Prepend headers in response
		$this->setOption('header', True); // this makes me literally cry sometimes

		// Sets whether return result page
		$this->setOption('returntransfer', True);

		// we shouldn't trust to all certificates but we have to!
		if ($this->getOption('ssl_verifypeer') === Null) {
			$this->setOption('ssl_verifypeer', False);
		}

		// fix:Sairon http://forum.nette.org/cs/profile.php?id=1844 thx
		if ($this->followRedirects === NULL && !$this->safeMode() && ini_get('open_basedir') == ""){
			$this->followRedirects = TRUE;
		}

		// Set all cURL options
		foreach ($this->options as $name => $value) {
			if ($name == "FOLLOWLOCATION" && ( $this->safeMode() || ini_get('open_basedir') != "" )) {
				continue;
			}
			curl_setopt($resource, constant('CURLOPT_' . $name), $value);
		}
	}



	/**
	 * Formats and adds custom headers to the current request
	 */
	protected function setRequestHeaders($resource)
	{
		$headers = array();
		foreach ($this->headers as $key => $value) {
			$headers[] = $value;
		}

		if (count($this->headers) > 0) {
			curl_setopt($resource, CURLOPT_HTTPHEADER, $headers);
		}

		return $headers;
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
		$errorno = curl_errno($ch);
		$errormsg = curl_error($ch);
#		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
#		$totaltime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
		curl_close ($ch);

		try {
			$response = $this->createResponse($content, $info);
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
			if ($this->faultTolerance > $count) {
				return $this->sendRequestGet(++$count);
			}
			throw new FailedRequestException(self::GET, $this->url, $content, $info, $errormsg, $errorno, $e);
		}
	}



	//	PRIVATE



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
	 *	Vytvorit objekt s odpovedi.
	 */
	private function createResponse($content, array $info)
	{
		if (empty($content)) {
			throw new \RuntimeException('Empty response content [' . $this->url . ']');
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
			throw new \RuntimeException("Invalid response content.");
		}
		$response = new HttpResponse($vars[0], $vars[1], $vars[2]);
		$response->url = $this->url;
		$response->setContent($content);
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
		if ($data = self::prepareGetData($this->gets)) {
			return $this->domain
				. '?'
				. $data;
		}
		return $this->domain;
	}



	/**
	 *	Zpracování get dat do řetězce.
	 */
	private static function prepareGetData(array $gets)
	{
		return http_build_query($gets);
	}



	/**
	 *	Zpracování post dat do pole.
	 */
	private static function preparePostData($posts)
	{
		if (is_array($posts)) {
			return http_build_query($posts, '', '&');
		}
		return $posts;
	}



	/**
	 *	Zpracování cookies dat do řetězce.
	 */
	private function prepareCookiesData()
	{
		return http_build_query($this->session->cookies, '', '; ');
	}


}
