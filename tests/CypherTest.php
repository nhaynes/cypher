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
		$cypher = new Cypher;

		$result = $cypher->statement('CREATE (person {endy}) RETURN person', array(
			'endy' => array(
				'name' => 'Endy Jasmi',
				'born' => 1990
				)
			))
			->execute();

		if (!$result) {
			var_dump($cypher->errors());
		} else {
			var_dump($result[0]['person']);
		}
	}
}