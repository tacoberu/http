<?php
require_once 'PHPUnit/Framework.php';


/*
 *	Test v podrizenenm adresari.
 */
#require_once __dir__ . '/util/Suite.php';

/*
 *	Testy v tomto adresari.
 */
require_once __dir__ . '/DibiFunctions.php';


/**
 * Testování knihoven. Skupina jednotkových testů.
 *
 * @call phpunit --bootstrap ../../bootstrap.php Suite.php tests_libs_taco_Suite
 * @author Martin Takáč <taco@taco-beru.name>
 */
class tests_libs_taco_Suite extends PHPUnit_Framework_TestSuite
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
		$suite->addTestSuite('tests_libs_taco_DibiFunctions');

		//	Test v podrizenenm adresari.
#		$suite->addTest(tests_app_models_util_Suite::suite());

		return $suite;
	}

}
