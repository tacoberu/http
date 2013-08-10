<?php
require_once 'PHPUnit/Framework.php';


/*
 *	Test v podrizenenm adresari.
 */
require_once __dir__ . '/app/Suite.php';
require_once __dir__ . '/libs/Suite.php';
require_once __dir__ . '/persistence/Suite.php';
require_once __dir__ . '/public/Suite.php';


/*
 *	Testy v tomto adresari.
 */
//	---


/**
 * Testování knihoven. Skupina jednotkových testů.
 *
 * @call phpunit --bootstrap bootstrap.php Suite.php tests_Suite
 * @author Martin Takáč <taco@taco-beru.name>
 */
class tests_Suite extends PHPUnit_Framework_TestSuite
{

	/**
	 * Ktere testy poustet,
	 *
	 * @return PHPUnit_Framework_TestSuite
	 */
	public static function suite()
	{
		$suite = new self('Vsechny testy/suite.');

		//	Testy v tomto adresari.
		//	--

		//	Test v podrizenenm adresari.
		$suite->addTest(tests_app_Suite::suite());
		$suite->addTest(tests_libs_Suite::suite());
		$suite->addTest(tests_persistence_Suite::suite());
		$suite->addTest(tests_public_Suite::suite());

		return $suite;
	}

}
