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
 *	Jednoduchý klient pro komunikaci s http serverem.
 *
 *	@author     Martin Takáč <taco@taco-beru.name>
 */
class HttpSession
{


	/**
	 * Nastavené sušenky.
	 */
	public $cookies = array();



	/**
	 *	Tolerovaný timeout.
	 */
	public $timeout = 2;


	/**
	 * @return Taco\Http\Client\IHttpRequest
	 */
	public function createRequest($url)
	{
		return new HttpRequest($this, $url);
	}



	/**
	 * @return Taco\Http\Client\IHttpRequest
	 */
	public function setCookie($key, $value)
	{
		$key = trim($key);
		$this->cookies[$key] = $value;

		return $this;
	}



}
