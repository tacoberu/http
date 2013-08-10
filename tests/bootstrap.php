<?php

/**
 * Test bootstrap file.
 *
 * @copyright  Copyright (c) 20!0 Martin Takáč
 * @package    ..
 */

use Nette\Environment,
	Nette\Debug;


// absolute filesystem path to the libraries
define('LIBS_DIR', realpath(dirname(__FILE__) . '/../libs'));

// absolute filesystem path to the application root
define('APP_DIR', realpath(dirname(__FILE__) . '/../app'));

// absolute filesystem path to the libraries
define('CONFIG_DIR', APP_DIR . '/config');

// absolute filesystem path to the temporary files
define('TEMP_DIR', APP_DIR . '/../temp');


// load Nette Framework
require LIBS_DIR . '/Nette/loader.php';


Environment::setName('unittests');


// Step 2: Configure environment
// 2a) enable Nette\Debug for better exception and error visualisation
// Step 2: Configure environment
// 2a) enable Nette\Debug for better exception and error visualisation
Debug::enable();
Debug::$maxDepth = 3;  // hloubka zanorení polí
Debug::$maxLen   = 1500; // maximální délka retezce


// Nastavení proměných
#Environment::setVariable('baseUri', Environment::getHttpRequest()->getUri()->getBaseUri());


// 2b) load configuration from config.ini file
Environment::loadConfig(dirname(__FILE__) . '/../app/config.ini');


// enable RobotLoader - this allows load all classes automatically
\Nette\Configurator::createRobotLoader(
		array(
				"directory" => array(
						APP_DIR,
						LIBS_DIR
					)
			)
	);

