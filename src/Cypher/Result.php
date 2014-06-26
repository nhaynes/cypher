<?php namespace EndyJasmi\Cypher;

use ArrayAccess;
use Countable;
use Exception;
use Iterator;

class Result implements ArrayAccess, Countable, Iterator
{
	protected $results;

	public function __construct(array $results)
	{
		$this->results = $results;
	}

	public function toArray()
	{
		return array_map(function($result)
			{
				return array_combine($this->results['columns'], $result['row']);
			}, $this->results['data']);
	}

	public function toJson()
	{
		return json_encode($this->toArray());
	}

	/**
	 * Implement array access interface
	 */
	public function offsetExists($offset)
	{
		return isset($this->results['data'][$offset]);
	}

	public function offsetGet($offset)
	{
		if (!isset($this->results['data'][$offset])) {
			return null;
		}

		return array_combine($this->results['columns'], $this->results['data'][$offset]['row']);
	}

	public function offsetSet($offset, $value)
	{
		throw new Exception('Invalid method');
	}

	public function offsetUnset($offset)
	{
		throw new Exception('Invalid method');
	}

	/**
	 * Implement countable interface
	 */
	public function count()
	{
		return count($this->results['data']);
	}

	/**
	 * Implement iterator interface
	 */
	protected $cursor = 0;

	public function current()
	{
		return $this[$this->cursor];
	}

	public function key()
	{
		return $this->cursor;
	}

	public function next()
	{
		$this->cursor++;
	}

	public function rewind()
	{
		$this->cursor = 0;
	}

	public function valid()
	{
		return isset($this[$this->cursor]);
	}
}