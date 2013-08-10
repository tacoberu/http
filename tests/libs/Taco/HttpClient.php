<?php

require_once 'PHPUnit/Framework.php';



/**
 * Kontrola verze.
 *
 * @call phpunit --bootstrap ../../bootstrap.php HttpClient.php Tests_Libs_Taco_HttpClient
 */
class Tests_Libs_Taco_HttpClient extends PHPUnit_Framework_TestCase
{

	const BASE_URL = 'http://localhost/~taco/studie/curl';


	/**
	 *	Pokusné volání. Testujeme co všechno vrátí. Z tohoto by následně měl vzniknou
	 *	framework pro curl volání služeb.
	 */
	public function testClientRead()
	{
		$client = new \Taco\Http\Client\HttpSession();
		$response = $client->createRequest(self::BASE_URL . '/index.php')
			->get();

		$this->assertEquals($response->url, 'http://localhost/~taco/studie/curl/index.php');
		$this->assertEquals($response->content->content, "Ahoj\nGET:Array\n(\n)\nPOST:Array\n(\n)\nCOOKIE:Array\n(\n)");
#dump($res);
	}




	/**
	 *	Poslani a ziskani stejneho id session.
	 *	Hmm, odpověd posílá hlavičky, které má klient zohlednit. Tedy nastavit si 
	 *	cookies a tak. To se netýká serveru.
	 */
	public function ________testCurlReadWithSessionCookiesExchange()
	{
		$client = new \Taco\Http\Client\HttpSession();
dump('#Prvé volání:');
		$res = $this->call(self::BASE_URL . '/curl/post.php');
		$this->assertEquals($res->url, self::BASE_URL . '/post.php');
#dump($res->content);
dump($res->headers);
		$SID = $res->cookies['PHPSESSID'];
dump('#Druhe volání:');
		$res = $this->call(
#				self::BASE_URL . '/post.php?D=cookies',
				self::BASE_URL . '/post.php',
				$res->cookies
			);
dump($res->content);
dump($res->headers);
dump($res->cookies);
#		$this->assertEquals($res->cookies['PHPSESSID'], $SID);
	}



	/**
	 *	Client jako Post
	 */
	public function testClientPost()
	{
		$client = new \Taco\Http\Client\HttpSession();
		//	Prvni volani
		$response = $client->createRequest(self::BASE_URL . '/index.php')
			->addGetParam('T', 'none')
			->get();

		$this->assertType('\Taco\Http\Client\HttpResponse', $response);
		$this->assertEquals($response->status->code, 200);
		$this->assertEquals($response->status->message, 'OK');
		$this->assertEquals($response->url, self::BASE_URL . '/index.php');
		$this->assertEquals($response->content->type, 'text/html');
		$this->assertNull($response->content->charset);

		$this->assertEquals($response->content->content, "Ahoj\nGET:Array\n(\n    [T] => none\n)\nPOST:Array\n(\n)\nCOOKIE:Array\n(\n)");

		$headers = $response->headers;
		$headers[1] = substr($headers[1], 0, 6);
		$this->assertEquals(
				$headers,
				array(
						"HTTP/1.1 200 OK",
						"Date: ",
						"Server: Apache/2.2.15 (Linux/SUSE)",
						"X-Powered-By: PHP/5.3.3",
						"Content-Length: 67",
						"Content-Type: text/html"
					)
			);
		$this->assertEquals($client->cookies, array());

		//	Druhe volani - zalozime si cookie pro session.
		$response = $client->createRequest(self::BASE_URL . '/')
			->addGetParam('T', 'none')
			->addPostParam('ahoj', 'jedna')
			->post();
		$this->assertType('\Taco\Http\Client\HttpResponse', $response);
		$this->assertEquals($response->status->code, 200);
		$this->assertEquals($response->status->message, 'OK');
		$this->assertEquals($response->url, self::BASE_URL . '/');
		$this->assertEquals($response->content->type, 'text/html');
		$this->assertNull($response->content->charset);
		$this->assertEquals($response->content->content, "Ahoj\nGET:Array\n(\n    [T] => none\n)\nPOST:Array\n(\n    [ahoj] => jedna\n)\nCOOKIE:Array\n(\n)");
#dump($response->headers);
	}



	/**
	 *	Client.
	 *	Mezi jednotlivími sezeními si pamatuje uložené cookies a tak.
	 */
	public function testClientPostCookies()
	{
		$client = new \Taco\Http\Client\HttpSession();
		//	Druhe volani - zalozime si cookie pro session.
		$response = $client->createRequest(self::BASE_URL . '/post.php')
			->addGetParam('T', 'none')
			->addPostParam('action', 'connect')
			->post();
		$this->assertType('\Taco\Http\Client\HttpResponse', $response);
		$this->assertEquals($response->status->code, 200);
		$this->assertEquals($response->status->message, 'OK');
		$this->assertEquals($response->url, self::BASE_URL . '/post.php');
		$this->assertEquals($response->content->type, 'text/html');
		$this->assertNull($response->content->charset);
		$this->assertEquals($response->content->content, "postak\nGET:Array\n(\n    [T] => none\n)\nPOST:Array\n(\n    [action] => connect\n)\nCOOKIE:Array\n(\n)");
		$headers = $response->headers;
#		dump($headers);
		$headers[1] = substr($headers[1], 0, 6);
		$headers[4] = substr($headers[4], 0, 22);
		$this->assertEquals(
				$headers,
				array(
						"HTTP/1.1 200 OK",
						"Date: ",
						"Server: Apache/2.2.15 (Linux/SUSE)",
						"X-Powered-By: PHP/5.3.3",
						"Set-Cookie: PHPSESSID=",
						"Expires: Thu, 19 Nov 1981 08:52:00 GMT",
						"Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0",
						"Pragma: no-cache",
						"Content-Length: 93",
						"Content-Type: text/html"
					)
			);
		$this->assertTrue(isset($client->cookies['PHPSESSID']));
		$SID = $client->cookies['PHPSESSID'];
#		dump($SID);
#		$this->assertEquals($client->cookies, array('PHPSESSID' => 'ouu9u2c8e5jnupfe5t4jebf033u8grfs'));

		//	Třetí volani - už máme založené cookie.
		$response = $client->createRequest(self::BASE_URL . '/post.php')
			->addGetParam('D', 'none')
			->addPostParam('ahoj', 'dva')
			->post();
		$this->assertType('\Taco\Http\Client\HttpResponse', $response);
		$this->assertEquals($response->status->code, 200);
		$this->assertEquals($response->status->message, 'OK');
		$this->assertEquals($response->url, self::BASE_URL . '/post.php');
		$this->assertEquals($response->content->type, 'text/html');
		$this->assertNull($response->content->charset);
		$this->assertEquals($response->content->content, "postak\nGET:Array\n(\n    [D] => none\n)\nPOST:Array\n(\n    [ahoj] => dva\n)\nCOOKIE:Array\n(\n    [PHPSESSID] => $SID\n)");

		$this->assertTrue(isset($client->cookies['PHPSESSID']));
		$this->assertEquals($client->cookies, array('PHPSESSID' => $SID));

		$headers = $response->headers;
		$headers[1] = substr($headers[1], 0, 6);
		$headers[4] = substr($headers[4], 0, 22);
		$this->assertEquals(
				$headers,
				array(
						"HTTP/1.1 200 OK",
						"Date: ",
						"Server: Apache/2.2.15 (Linux/SUSE)",
						"X-Powered-By: PHP/5.3.3",
						"Content-Length: 139",
						"Content-Type: text/html"
					)
			);

		//	Zda posila cookies i get
		$response = $client->createRequest(self::BASE_URL . '/index.php')
			->addGetParam('T', 'none')
			->get();

		$this->assertType('\Taco\Http\Client\HttpResponse', $response);
		$this->assertEquals($response->status->code, 200);
		$this->assertEquals($response->status->message, 'OK');
		$this->assertEquals($response->url, self::BASE_URL . '/index.php');
		$this->assertEquals($response->content->type, 'text/html');
		$this->assertNull($response->content->charset);
		$this->assertEquals($response->content->content, "Ahoj\nGET:Array\n(\n    [T] => none\n)\nPOST:Array\n(\n)\nCOOKIE:Array\n(\n    [PHPSESSID] => $SID\n)");

		$headers = $response->headers;
		$headers[1] = substr($headers[1], 0, 6);
		$this->assertEquals(
				$headers,
				array(
						"HTTP/1.1 200 OK",
						"Date: ",
						"Server: Apache/2.2.15 (Linux/SUSE)",
						"X-Powered-By: PHP/5.3.3",
						"Content-Length: 119",
						"Content-Type: text/html"
					)
			);
		$this->assertEquals($client->cookies, array('PHPSESSID' => $SID));
	}



	/**
	 *	Client.
	 */
	public function testNotFound()
	{
		$client = new \Taco\Http\Client\HttpSession();
		//	Prvni volani
		$response = $client->createRequest(self::BASE_URL . '/notfound.php')
			->addGetParam('T', 'none')
			->get();

		$this->assertType('\Taco\Http\Client\HttpResponse', $response);
		$this->assertEquals($response->status->code, 404);
		$this->assertEquals($response->status->message, 'Not Found');
		$this->assertEquals($response->url, self::BASE_URL . '/notfound.php');

#dump($response->headers);
		$this->assertEquals($response->headers[0], 'HTTP/1.1 404 Not Found');
		$this->assertEquals($client->cookies, array());
		$this->assertEquals($response->content->type, 'text/html');
		$this->assertEquals($response->content->charset, 'iso-8859-1');
		$this->assertEquals(substr($response->content->content, 0, 263), '<?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>Object not found!</title>');
	}



	/**
	 *	Client.
	 */
	public function testServerError()
	{
		$client = new \Taco\Http\Client\HttpSession();
		//	Prvni volani
		$response = $client->createRequest(self::BASE_URL . '/server-error.php')
			->addGetParam('T', 'none')
			->get();

		$this->assertType('\Taco\Http\Client\HttpResponse', $response);
		$this->assertEquals($response->status->code, 500);
		$this->assertEquals($response->status->message, 'Server Error');
		$this->assertEquals($response->url, self::BASE_URL . '/server-error.php');

#dump($response->headers);
		$this->assertEquals($response->headers[0], 'HTTP/1.1 500 Server Error');
		$this->assertEquals($client->cookies, array());
		$this->assertEquals($response->content->type, 'text/html');
		$this->assertNull($response->content->charset);
		$this->assertEquals($response->content->content, '<!DOCTYPE html>
<html>
<head>
<meta name=robots content=noindex>
<meta name=generator content=\'Taco\'> 
 
<style>body{color:#333;background:white;width:500px;margin:100px auto}h1{font:bold 47px/1.5 sans-serif;margin:.6em 0}p{font:21px/1.5 Georgia,serif;margin:1.5em 0}small{font-size:70%;color:gray}</style> 
 
<title>Server Error</title> 
 
</head>
<body>
<h1>Server Error</h1> 
 
<p>We\'re sorry! The server encountered an internal error and was unable to complete your request. Please try again later.</p> 
 
<p><small>error 500</small></p>
</body>
</html>');
	}



	/**
	 *	Testování obsahu.
	 */
	public function testContentPlainText()
	{
		$client = new \Taco\Http\Client\HttpSession();
		//	Prvni volani
		$response = $client->createRequest(self::BASE_URL . '/content.txt')
			->addGetParam('T', 'none')
			->get();

		$this->assertEquals($client->cookies, array());
		$this->assertType('\Taco\Http\Client\HttpResponse', $response);
		$this->assertEquals($response->status->code, 200);
		$this->assertEquals($response->status->message, 'OK');
		$this->assertEquals($response->url, self::BASE_URL . '/content.txt');
		$this->assertEquals($response->headers[0], 'HTTP/1.1 200 OK');
		$this->assertEquals($response->content->type, 'text/plain');
		$this->assertNull($response->content->charset);
		$this->assertEquals($response->content->content, 'Lorem ipsum doler ist.');
	}



	/**
	 *	Testování obsahu.
	 */
	public function testContentPdf()
	{
		$client = new \Taco\Http\Client\HttpSession();
		//	Prvni volani
		$response = $client->createRequest(self::BASE_URL . urlencode('/Getting Started') . '.pdf')
#		$response = $client->createRequest(self::BASE_URL . '/Getting Started.pdf')
			->addGetParam('T', 'none')
			->get();

		$this->assertEquals($client->cookies, array());
		$this->assertType('\Taco\Http\Client\HttpResponse', $response);
		$this->assertEquals($response->status->code, 200);
		$this->assertEquals($response->status->message, 'OK');
		$this->assertEquals($response->url, self::BASE_URL . '/Getting Started.pdf');
		$this->assertEquals($response->headers[0], 'HTTP/1.1 200 OK');
		$this->assertEquals($response->content->type, 'application/pdf');
		$this->assertNull($response->content->charset);
		$this->assertEquals(substr($response->content->content, 0, 8), '%PDF-1.4');
	}



	/**
	 *	Testování obsahu.
	 */
	public function __testContentMp3()
	{
		$client = new \Taco\Http\Client\HttpSession();
		//	Prvni volani
		$response = $client->createRequest(self::BASE_URL . '/04. Petite chansons grivoise.mp3')
			->addGetParam('T', 'none')
			->get();

		$this->assertEquals($client->cookies, array());
		$this->assertType('\Taco\Http\Client\HttpResponse', $response);
		$this->assertEquals($response->status->code, 200);
		$this->assertEquals($response->status->message, 'OK');
		$this->assertEquals($response->url, self::BASE_URL . '/04. Petite chansons grivoise.mp3');
		$this->assertEquals($response->headers[0], 'HTTP/1.1 200 OK');
#dump($response->content);
		$this->assertEquals($response->content->type, 'audio/mpeg');
		$this->assertNull($response->content->charset);
		$this->assertEquals(substr($response->content->content, 0, 3), 'ID3');
	}




	/**
	 *	Testování url který není korektní.
	 */
	public function testUrlWithWhiteChars()
	{
		$client = new \Taco\Http\Client\HttpSession();
		//	Prvni volani
		$response = $client->createRequest(self::BASE_URL . '/content (kopie).txt')
			->get();

		$this->assertEquals($client->cookies, array());
		$this->assertType('\Taco\Http\Client\HttpResponse', $response);
		$this->assertEquals($response->status->code, 200);
		$this->assertEquals($response->status->message, 'OK');
		$this->assertEquals($response->url, self::BASE_URL . '/content (kopie).txt');
		$this->assertEquals($response->headers[0], 'HTTP/1.1 200 OK');
#dump($response->content);
		$this->assertEquals($response->content->type, 'text/plain');
		$this->assertNull($response->content->charset);
		$this->assertEquals($response->content->content, 'Lorem ipsum doler ist.');
	}







}
