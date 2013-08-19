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


require_once __dir__ . '/../../lib/Taco/Http/Client/HttpSession.php';
require_once __dir__ . '/../../lib/Taco/Http/Client/HttpRequest.php';
require_once __dir__ . '/../../lib/Taco/Http/Client/HttpResponse.php';


use Taco\Http\Client;


/**
 * @call phpunit Tests_Aceptance_Taco_Http_Client_HttpRequestTest HttpRequestTest.php 
 */
class Tests_Aceptance_Taco_Http_Client_HttpRequestTest extends PHPUnit_Framework_TestCase
{


	private function getConfig()
	{
		$host = 'http.lc';
		return (object) array(
				'status200' => (object) array(
						'host' => $host, 
						'url' => 'http://' . $host . '/200',
						),
				'status303' => (object) array(
						'host' => $host, 
						'url' => "http://$host/303",
						),
				'status404' => (object) array(
						'host' => $host, 
						'url' => "http://$host/404",
						),
				'status500' => (object) array(
						'host' => $host, 
						'url' => "http://ds.cz",
						),
				);
	}



	/**
	 *	GET Status 200
	 */
	public function testGetStatus200()
	{
		$config = $this->getConfig()->status200;
		$client = new Client\HttpSession();
		$request = $client->createRequest($config->url);
		$response = $request->get();
		$this->assertEquals($config->url, $response->url);
		$this->assertEquals('200', $response->status->code);
		$this->assertEquals('OK', $response->status->message);
		$this->assertEquals('text/html', $response->content->type);
		$this->assertEquals('UTF-8', $response->content->charset);
		$this->assertEquals("Status 200\n"
				. "HTTP_HOST: {$config->host}\n"
				. "HTTP_ACCEPT: */*\n"
				. "REQUEST_METHOD: GET",
				$response->content->content);
	}



	/**
	 *	GET Status 200 with params
	 */
	public function testGetStatus200WithParams()
	{
		$config = $this->getConfig()->status200;
		$client = new Client\HttpSession();
		$response = $client->createRequest($config->url)
				->addGetParam('foo', 42)
				->get();
		$this->assertEquals($config->url . '?foo=42', $response->url);
		$this->assertEquals('200', $response->status->code);
		$this->assertEquals('OK', $response->status->message);
		$this->assertEquals('text/html', $response->content->type);
		$this->assertEquals('UTF-8', $response->content->charset);
		$this->assertEquals("Status 200\n"
				. "HTTP_HOST: {$config->host}\n"
				. "HTTP_ACCEPT: */*\n"
				. "REQUEST_METHOD: GET\n"
				. "GET:\nArray\n(\n    [foo] => 42\n)", $response->content->content);
	}



	/**
	 *	GET Status 303
	 */
	public function testGetStatus303()
	{
		$config = $this->getConfig()->status303;
		$config200 = $this->getConfig()->status200;
		$client = new Client\HttpSession();
		$request = $client->createRequest($config->url);
		$response = $request->get();
		$this->assertEquals($config200->url, $response->url, 'Cilova adresa.');
		$this->assertEquals(200, $response->status->code);
		$this->assertEquals('OK', $response->status->message);
		$this->assertEquals('text/html', $response->content->type);
		$this->assertEquals("Status 200\n"
				. "HTTP_HOST: {$config->host}\n"
				. "HTTP_ACCEPT: */*\n"
				. "REQUEST_METHOD: GET",
				$response->content->content);
	}



	/**
	 *	GET Status 404
	 */
	public function testGetStatus404()
	{
		$config = $this->getConfig()->status404;
		$client = new Client\HttpSession();
		$request = $client->createRequest($config->url);
		$response = $request->get();
		$this->assertEquals($config->url, $response->url);
		$this->assertEquals(404, $response->status->code);
		$this->assertEquals('Not Found', $response->status->message);
		$this->assertEquals('text/html', $response->content->type);
	}



	/**
	 *	POST Status 201
	 */
	public function testPostStatus201()
	{
		$config = $this->getConfig()->status200;
		$client = new Client\HttpSession();
		$response = $client->createRequest($config->url)
				->setPostData('abc')
				->post();
		$this->assertEquals($config->url, $response->url);
		$this->assertEquals('200', $response->status->code);
		$this->assertEquals('OK', $response->status->message);
		$this->assertEquals('text/html', $response->content->type);
		$this->assertEquals('UTF-8', $response->content->charset);
		$this->assertEquals("Status 200\n"
				. "HTTP_HOST: {$config->host}\n"
				. "HTTP_ACCEPT: */*\n"
				. "CONTENT_LENGTH: 3\n"
				. "CONTENT_TYPE: application/x-www-form-urlencoded\n"
				. "REQUEST_METHOD: POST\n"
				. "HTTP_RAW_POST_DATA:\nabc",
				$response->content->content);
	}



	/**
	 *	POST Status 201 x-www-form-urlencoded
	 */
	public function testPostStatus201Urlencode()
	{
		$config = $this->getConfig()->status200;
		$client = new Client\HttpSession();
		$response = $client->createRequest($config->url)
				->addPostParam('abc', 42)
				->addPostParam('foo', 'hmmm')
				->post();
		$this->assertEquals($config->url, $response->url);
		$this->assertEquals('200', $response->status->code);
		$this->assertEquals('OK', $response->status->message);
		$this->assertEquals('text/html', $response->content->type);
		$this->assertEquals('UTF-8', $response->content->charset);
		$this->assertEquals("Status 200\n"
				. "HTTP_HOST: {$config->host}\n"
				. "HTTP_ACCEPT: */*\n"
				. "CONTENT_LENGTH: 15\n"
				. "CONTENT_TYPE: application/x-www-form-urlencoded\n"
				. "REQUEST_METHOD: POST\n"
				. "POST:\n"
				. "Array\n(\n"
				. "    [abc] => 42\n"
				. "    [foo] => hmmm\n"
				. ")\n"
				. "HTTP_RAW_POST_DATA:\nabc=42&foo=hmmm",
				$response->content->content);
	}



	/**
	 *	DELETE Status 200 with params
	 */
	public function testDeleteStatus200()
	{
		$config = $this->getConfig()->status200;
		$client = new Client\HttpSession();
		$response = $client->createRequest($config->url)
				->addGetParam('foo', 42)
				->delete();
		$this->assertEquals($config->url . '?foo=42', $response->url);
		$this->assertEquals('200', $response->status->code);
		$this->assertEquals('OK', $response->status->message);
		$this->assertEquals('text/html', $response->content->type);
		$this->assertEquals('UTF-8', $response->content->charset);
		$this->assertEquals("Status 200\n"
				. "HTTP_HOST: {$config->host}\n"
				. "HTTP_ACCEPT: */*\n"
				. "REQUEST_METHOD: DELETE\n"
				. "GET:\nArray\n(\n    [foo] => 42\n)", $response->content->content);
	}




	/**
	 *	PUT Status 200 with params
	 */
	public function testPutStatus200()
	{
		$config = $this->getConfig()->status200;
		$client = new Client\HttpSession();
		$response = $client->createRequest($config->url)
				->addGetParam('foo', 42)
				->setPostData('Lorem ipsum doler ist.')
				->put();
		$this->assertEquals($config->url . '?foo=42', $response->url);
		$this->assertEquals('200', $response->status->code);
		$this->assertEquals('OK', $response->status->message);
		$this->assertEquals('text/html', $response->content->type);
		$this->assertEquals('UTF-8', $response->content->charset);
		$this->assertEquals("Status 200\n"
				. "HTTP_HOST: {$config->host}\n"
				. "HTTP_ACCEPT: */*\n"
				. "CONTENT_LENGTH: 22\n"
				. "CONTENT_TYPE: application/x-www-form-urlencoded\n"
				. "REQUEST_METHOD: PUT\n"
				. "GET:\nArray\n(\n    [foo] => 42\n)\n"
				. "HTTP_RAW_POST_DATA:\n"
				. "Lorem ipsum doler ist.",
				$response->content->content);
	}




	/**
	 *	500
	 */
	public function testCreateRequestEmtpy()
	{
		$config = $this->getConfig()->status500;
		$client = new Client\HttpSession();
		$client->timeout = 1;
		$request = $client->createRequest($config->url);
		try {
			$response = $request->get();
		}
		catch (Client\FailedRequestException $e) {
			$this->assertEquals($e->info['url'], 'http://ds.cz');
			$this->assertEquals('Couldn\'t resolve host \'ds.cz\'', $e->getMessage());
			$this->assertEquals('GET', $e->method);
			$this->assertEquals('http://ds.cz', $e->url);
			$this->assertFalse($e->data);
		}
	}



}
