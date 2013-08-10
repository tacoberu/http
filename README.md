http
====

Simple http client for PHP wrapered Curl extension.


Sample using
------------


### Getting data by GET method
	$client = new Taco\Http\Client\HttpSession();
	$request = $client->createRequest('http://example.org/foo/sample.html');
	$response = $request->get();


### Sending data by POST method
	$client = new Taco\Http\Client\HttpSession();
	$request = $client->createRequest('http://example.org/foo/sample.html')
			->addGetParam('do', 'signin-submit')
			->addPostParam('username', 'test')
			->addPostParam('password', 'blabla')
	$response = $request->post();

### Sending data by PUT method
	$client = new Taco\Http\Client\HttpSession();
	$response = $client->createRequest('http://example.org/foo/sample.html')
			->addGetParam('do', 'signin')
			->addPostParam('username', 'test')
			->addPostParam('password', 'jhg59n5mbptq56irv5ao48m4s4')
			->put();

### Delete data by DELETE method
	$client = new Taco\Http\Client\HttpSession();
	$response = $client->createRequest('http://example.org/foo/sample.html')
			->addGetParam('do', 'signin')
			->addPostParam('username', 'test')
			->addPostParam('password', 'jhg59n5mbptq56irv5ao48m4s4')
			->delete();
