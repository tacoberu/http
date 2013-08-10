<?php

/**
 * Description of Client of http
 *
 * PHP version 5.3
 *
 * @author     Martin Takáč <taco@taco-beru.name>
 * @copyright  Copyright (c) 2010 Martin Takáč
 */

namespace Taco\Http\Client;



/**
 *	Odpověď serveru.
 *
 *	@author     Martin Takáč <taco@taco-beru.name>
 */
class HttpResponse
{


	/**
	 *	Adresa odpovědí - po přesměrování.
	 */
	public $url;



	/**
	 *	Status odpovědi.
	 */
	public $status;
	
	

	/**
	 *	Hlavičky odpovědi.
	 */
	public $headers = array();



	/**
	 *	Obsah odpovědi.
	 */
	public $content;



	/**
	 *	Vytvoření instance odpovědi.
	 */
	public function __construct($version = 'HTTP/1.1', $code = 200, $message = 'OK')
	{
		$this->version = $version;
		$this->status = (object) array (
				'code' => $code,
				'message' => $message
			);
	}



	/**
	 * Adds HTTP header.
	 * @param  string  header name
	 * @param  string  header value
	 *
	 * @return HttpResponse  provides a fluent interface
	 */
	public function addHeader($header)
	{
		$header = trim($header);
		$var = explode(':', $header, 2);
		switch ($var[0]) {
			case 'Content-Type':
				$var1 = explode(';', $var[1]);
				if (isset($var1[0])) {
					$this->content->type = trim($var1[0]);
				}
				if (isset($var1[1])) {
					$var2 = explode('=', $var1[1]);
					switch (strtolower(trim($var2[0]))) {
						case 'charset':
							$this->content->charset = trim($var2[1]);
							break;
							
						default:
							throw new \Exception("Unknow chunk for content-type [{$var2[0]}] => [$header].");
					}
				}
				break;
		}
		
		$this->headers[] = $header;
		return $this;
	}



	/**
	 * Sets content with Content-type and charset.
	 * @param  string  content
	 *
	 * @return HttpResponse  provides a fluent interface
	 */
	public function setContent($content)
	{
		$this->content = (object) array(
				'type' => Null,
				'content' => $content,
				'charset' => Null
			);
		return $this;
	}




}
