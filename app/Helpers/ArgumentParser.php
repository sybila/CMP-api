<?php

namespace App\Helpers;

use App\Exceptions\InvalidTypeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;
class ArgumentParser implements \ArrayAccess
{
	/** @var array */
	protected $data;

	public function __construct(array $args)
	{
		$this->data = $args;
	}

	public function hasKey(string $key): bool
	{
		return array_key_exists($key, $this->data);
	}

	public function hasValue(string $key): bool
	{
		return $this->get($key) !== null;
	}

	public function checkBy(string $key, string $regex): bool
	{
		return preg_match('@' . $regex . '@', $this->get($key));
	}

	public function get(string $key)
	{
		if (!$this->hasKey($key))
			throw new \Exception('Invalid key ' . $key);

		return $this->data[$key];
	}

	public function getInt(string $key): int
	{
		$value = $this->get($key);
		if (is_numeric($value))
			return $value;
		else
			$this->doThrow($key, 'int');
	}

	public function getString(string $key): string
	{
		return $this->get($key);
	}

	public function getFloat(string $key): float
	{
		$value = $this->get($key);
		if ((string)((float)$value) === (string)$value)
			return $value;
		else
			$this->doThrow($key, 'float');
	}

	public function getBool(string $key): bool
	{
		$value = $this->get($key);
		if ($value === 'true' || $value === 'false')
			return $value == 'true';
		elseif ($value === '1' || $value === '0')
			return (bool)((int)$value);
		else
			$this->doThrow($key, 'bool');
	}

	protected function doThrow(string $key, string $type): void
	{
		throw new InvalidTypeException('Value of "' . $key . '" can\' be converted to ' . $type . '.');
	}

	// ============================== ArrayAccess

	public function offsetExists($offset)
	{
		return $this->hasKey($offset);
	}

	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetSet($offset, $value)
	{
	}

	public function offsetUnset($offset)
	{
	}
}

class RequestResponseParsedArgs implements InvocationStrategyInterface
{

	/**
	 * Invoke a route callable with request, response and all route parameters
	 * as individual arguments.
	 *
	 * @param array|callable         $callable
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface      $response
	 * @param array                  $routeArguments
	 *
	 * @return mixed
	 */
	public function __invoke(
		callable $callable,
		ServerRequestInterface $request,
		ResponseInterface $response,
		array $routeArguments
	) {
		return call_user_func_array($callable, [$request, $response, new ArgumentParser($routeArguments + $request->getQueryParams())]);
	}
}
