<?php namespace EndyJasmi;

use EndyJasmi\Cypher\Result;
use GuzzleHttp\Client;

class Cypher
{
	protected $host = 'http://localhost:7474';

	protected $options = array(
		'headers' => array(
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'X-Stream' => 'true'
			),
		'config' => array(
			'curl' => array(
				CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
				)
			)
		);

	protected $statements = array(
		'statements' => array()
		);

	protected $urls = array(
		'begin' => null,
		'commit' => null,
		'transaction' => null
		);

	public function __construct($host)
	{
		$parse = parse_url($host);

		$this->host = "{$parse['scheme']}://{$parse['host']}:{$parse['port']}";

		if (isset($parse['user']) && isset($parse['pass'])) {
			$this->options['auth'] = array($parse['user'], $parse['pass']);
		}

		$this->urls['begin'] = "{$this->host}/db/data/transaction";
		$this->urls['transaction'] = "{$this->host}/db/data/transaction/commit";
	}

	public function beginTransaction()
	{
		$result = $this->operation('post', $this->urls['begin']);

		$this->urls['commit'] = $result['results']['commit'];
		$this->urls['transaction'] = $result['response']->getHeader('Location');

		return $result['results'];
	}

	public function commit()
	{
		$result = $this->operation('post', $this->urls['commit']);

		$this->urls['commit'] = null;
		$this->urls['transaction'] = "{$this->host}/db/data/transaction/commit";

		return $result['results'];
	}

	public function execute()
	{
		$result = $this->operation('post', $this->urls['transaction']);

		return $result['results'];
	}

	public function operation($method, $url)
	{
		$guzzle = new Client;

		$options = $this->options;
		$options['json'] = $this->statements;
		$this->statements['statements'] = array();

		$request = $guzzle->createRequest($method, $url, $options);
		$response = $guzzle->send($request);
		$results = $response->json();

		if (count($results['results']) < 2) {
			$results['results'] = new Result($results['results'][0]);
		} else {
			$results['results'] = array_map(function($result)
				{
					return new Result($result);
				}, $results['results']);
		}


		return array(
			'request' => $request,
			'response' => $response,
			'results' => $results
			);
	}

	public function rollback()
	{
		$result = $this->operation('delete', $this->urls['transaction']);

		$this->urls['commit'] = null;
		$this->urls['transaction'] = "{$this->host}/db/data/transaction/commit";

		return $result['results'];
	}

	public function statement($query, array $parameters = array())
	{
		$statement = array();
		$statement['statement'] = $query;
		$statement['includeStats'] = true;

		if (!empty($parameters)) {
			$statement['parameters'] = $parameters;
		}

		$this->statements['statements'][] = $statement;

		return $this;
	}
}