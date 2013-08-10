<?php

require_once 'PHPUnit/Framework.php';

/**
 *
 * @call phpunit --bootstrap ../../bootstrap.php DibiFunctions.php tests_libs_taco_DibiFunctions
 */
class tests_libs_taco_DibiFunctions extends PHPUnit_Framework_TestCase
{


	/**
	 *	Pripojeni
	 */
	public function connection($engine)
	{
		switch ($engine) {
			case 'sqlite3':
				return dibi::connect(array(
						'driver' => 'sqlite3',
						'file' => ':memory:'
					));
			case 'postgre':
				return dibi::connect(array(
						'driver'   => 'postgre',
						'host'     => 'localhost',
						'dbname'   => 'taco_blog',
						'user'     => 'tacoberu',
						'password' => 'tacoberu'
					));
#			case 'mysql':
#				return dibi::connect(array(
#						'driver'   => 'mysql',
#						'host'     => 'localhost',
#						'username' => 'root',
#						'password' => '***',
#						'database' => 'test',
#						'charset'  => 'utf8',
#					));
			default:
				throw new InvalidArgumentException('Neimplementováno');
		}
	}



	/**
	 *	Zkrácení data na úroveň měsíce.
	 */
	public function testDateTrunc()
	{
		$this->assertEquals(
				\DibiFunctions::dateTrunc($this->connection('sqlite3'), 'a.created', 'month'),
				'DATETIME(a.created, \'start of month\')'
			);
		$this->assertEquals(
				\DibiFunctions::dateTrunc($this->connection('postgre'), 'a.created', 'month'),
				'DATE_TRUNC(\'month\', a.created)'
			);
	}



	/**
	 *	Vygenerování tabulky.
	 */
	public function testGenerateTable()
	{
		$dataWithKeys = array (
				'namex' => array('dva', 'tri', 'osm', 4),
				'namey' => array('Dva', 'Tri', 'Osm', 9)
			);
		$this->assertEquals(
				\DibiFunctions::generateTable($this->connection('postgre'), 'tb', $dataWithKeys),
				'(VALUES (\'dva\', \'Dva\'), (\'tri\', \'Tri\'), (\'osm\', \'Osm\'), (4, 9) ) tb(namex, namey)'
			);
		$this->assertEquals(
				\DibiFunctions::generateTable($this->connection('sqlite3'), 'x', $dataWithKeys),
				"(SELECT 'dva' AS [namex], 'Dva' AS [namey] UNION ALL "
				. "SELECT 'tri', 'Tri' UNION ALL "
				. "SELECT 'osm', 'Osm' UNION ALL "
				. "SELECT 4, 9) AS x"
			);

		$dataWithKeys2 = array (
				'namex' => array('dva', 'tri', 'osm', 4),
			);
		$this->assertEquals(
				\DibiFunctions::generateTable($this->connection('postgre'), 'x', $dataWithKeys2),
				'(VALUES (\'dva\'), (\'tri\'), (\'osm\'), (4) ) x(namex)'
			);
		$this->assertEquals(
				\DibiFunctions::generateTable($this->connection('sqlite3'), 'tb', $dataWithKeys2),
				"(SELECT 'dva' AS [namex] UNION ALL "
				. "SELECT 'tri' UNION ALL "
				. "SELECT 'osm' UNION ALL "
				. "SELECT 4) AS tb"
			);
	}



}
