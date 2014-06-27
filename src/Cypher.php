<?php namespace EndyJasmi;

use EndyJasmi\Cypher\Result;
use GuzzleHttp\Client;

class Cypher
{
	protected $errors = array();

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

	public function __construct($host = null)
	{
		if (!is_null($host)) {
			$parse = parse_url($host);

			$this->host = "{$parse['scheme']}://{$parse['host']}:{$parse['port']}";

			if (isset($parse['user']) && isset($parse['pass'])) {
				$this->options['auth'] = array($parse['user'], $parse['pass']);
			}
		}

		$this->urls['begin'] = "{$this->host}/db/data/transaction";
		$this->urls['transaction'] = "{$this->host}/db/data/transaction/commit";
	}

	public function beginTransaction()
	{
		$result = $this->operation('post', $this->urls['begin']);

		if (!$result) {
			return false;
		}

		$this->urls['commit'] = $result['results']['commit'];
		$this->urls['transaction'] = $result['response']->getHeader('Location');

		return $result['data'];
	}

	public function commit()
	{
		$result = $this->operation('post', $this->urls['commit']);

		if (!$result) {
			return false;
		}

		$this->urls['commit'] = null;
		$this->urls['transaction'] = "{$this->host}/db/data/transaction/commit";

		return $result['data'];
	}

	public function errors()
	{
		return $this->errors;
	}

	public function execute()
	{
		$result = $this->operation('post', $this->urls['transaction']);

		if (!$result) {
			return false;
		}

		return $result['data'];
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

		if (count($results['errors']) > 0) {
			$this->errors = $results['errors'];

			return false;
		}

		if (count($results['results']) < 2) {
			$results = new Result($results['results'][0]);
		} else {
			$results = array_map(function($result)
				{
					return new Result($result);
				}, $results['results']);
		}

		return array(
			'request' => $request,
			'response' => $response,
			'data' => $results
			);
	}

	public function rollback()
	{
		$result = $this->operation('delete', $this->urls['transaction']);

		if (!$result) {
			return false;
		}

		$this->urls['commit'] = null;
		$this->urls['transaction'] = "{$this->host}/db/data/transaction/commit";

		return $result['data'];
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