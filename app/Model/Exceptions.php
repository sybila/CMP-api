<?php

namespace App\Model;

use App\Http\ErrorException;
use Throwable;

abstract class ApiException extends \Exception
{
	abstract protected function _code() : int;
	public function __construct(string $message = "", Throwable $previous = null)
	{
		parent::__construct($message, static::_code(), $previous);
	}
}

class InvalidTypeException extends ApiException
{
	protected function _code(): int
	{
		return 701;
	}
}

class NonExistingObjectException extends ApiException
{
	public function __construct(int $id, Throwable $previous = null)
	{
		parent::__construct("Non-existing object with ID " . $id, $previous);
	}

	protected function _code(): int
	{
		return 700;
	}
}
