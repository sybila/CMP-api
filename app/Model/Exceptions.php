<?php

namespace App\Model;

use App\Http\ErrorException;
use Throwable;

abstract class ApiException extends \Exception
{
	const CODE = 500;
	public function __construct(string $message = "", Throwable $previous = null)
	{
		parent::__construct($message, static::CODE, $previous);
	}
}

class InvalidArgumentException extends ApiException
{
	const CODE = 702;
	public function __construct(string $name, string $arg, string $message = "", Throwable $previous = null)
	{
		parent::__construct('Invalid argument "' . $arg . '" for ' . $name . ($message ? (': ' . $message) : ''), $previous);
	}
}

class InvalidTypeException extends ApiException
{
	const CODE = 701;
}

class NonExistingObjectException extends ApiException
{
	const CODE = 700;
	public function __construct(int $id, Throwable $previous = null)
	{
		parent::__construct("Non-existing object with ID " . $id, $previous);
	}
}
