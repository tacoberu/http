<?php

/**
 * Copyright (c) 2004, 2011 Martin Takáč
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author     Martin Takáč <taco@taco-beru.name>
 */


require_once __dir__ . '/../../lib/Http/Client/HttpSession.php';
require_once __dir__ . '/../../lib/Http/Client/HttpRequest.php';
require_once __dir__ . '/../../lib/Http/Client/HttpResponse.php';


use Taco\Http\Client;


/**
 * @call phpunit Tests_Unit_Taco_Http_Client_HttpRequestTest HttpRequestTest.php 
 */
class Tests_Unit_Taco_Http_Client_HttpRequestTest extends PHPUnit_Framework_TestCase
{



	/**
	 *	Price
	 */
	public function testCreateRequest()
	{
		$client = new \Taco\Http\Client\HttpSession();
		$request = $client->createRequest('localhost/test');
#		print_r($request);
		$response = $request->get();
#		print_r($response);
		$this->assertEquals($response->url, 'localhost/test');
		$this->assertEquals($response->content->type, 'text/html');
	}



	/**
	 *	Price
	 */
	public function testCreateRequestEmtpy()
	{
		$client = new Client\HttpSession();
		$request = $client->createRequest('http://ds.cz');
#		print_r($request);
		try {
			$response = $request->get();
		}
		catch (Client\ResponseException $e) {
			$this->assertEquals($e->info['url'], 'http://ds.cz');
#			print_r($e);
		}
	}



}
