<?php
/**
 * Copyright (c) since 2004 Martin Takáč
 * @author Martin Takáč <martin@takac.name>
 */

namespace Taco\Http\Client;


/**
 * Držíme session - cookies, auth, etc.
 *
 * @author Martin Takáč <martin@takac.name>
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
