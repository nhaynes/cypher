<?php namespace EndyJasmi;

use Mockery;
use PHPUnit_Framework_TestCase as TestCase;

class CypherTest extends TestCase
{
	protected $cypher;

	protected $host = 'https://localhost:7473';

	public function setUp()
	{
		$this->cypher = new Cypher($this->host);
	}

	public function tearDown()
	{
		Mockery::close();
	}

	public function testCase()
	{
		$cypher = new Cypher('http://localhost:7474');

		$result = $cypher->statement('CREATE (n {node}) RETURN id(n) as id, n.name as name, n.born as born', array(
			'node' => array(
				'name' => 'Endy Jasmi',
				'born' => 1990
				)
			))
			->execute();

		// $cypher->rollback();

		var_dump($result['results']->toJson());
	}
}