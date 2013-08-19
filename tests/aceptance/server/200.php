<?php

echo 'Status 200' . PHP_EOL;

echo 'HTTP_HOST: ' . $_SERVER['HTTP_HOST'] . PHP_EOL;
echo 'HTTP_ACCEPT: ' . $_SERVER['HTTP_ACCEPT'] . PHP_EOL;
if (isset($_SERVER['CONTENT_LENGTH'])) {
	echo 'CONTENT_LENGTH: ' . $_SERVER['CONTENT_LENGTH'] . PHP_EOL;
}

if (isset($_SERVER['CONTENT_TYPE'])) {
	echo 'CONTENT_TYPE: ' . $_SERVER['CONTENT_TYPE'] . PHP_EOL;
}
echo 'REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD'] . PHP_EOL;


if (count($_GET)) {
	echo 'GET:' . PHP_EOL;
	print_r($_GET);
}

if (count($_POST)) {
	echo 'POST:' . PHP_EOL;
	print_r($_POST);
}

if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
//	$GLOBALS['HTTP_RAW_POST_DATA'] = fopen('php://input', 'rb');
	if ($c = file_get_contents('php://input')) {
		$GLOBALS['HTTP_RAW_POST_DATA'] = $c;
	}
}
if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
	echo 'HTTP_RAW_POST_DATA:' . PHP_EOL;
	print_r($GLOBALS['HTTP_RAW_POST_DATA']);
	echo PHP_EOL;
}


#print_r($_SERVER);
