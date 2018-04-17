<?php

namespace App\Exceptions;

use Throwable;

abstract class ApiException extends \Exception
{
	const CODE = 700;

	protected $additional = [];

	/**
	 * ApiException constructor.
	 * @param Throwable|null $previous
	 * @return self
	 */
	public function __construct(?Throwable $previous)
	{
		parent::__construct(null, static::CODE, $previous);
		return $this;
	}

	final protected function setMessage(string $message, ...$args): void
	{
		$this->message = sprintf($message, ...$args);
	}

	public function getHttpCode(): int
	{
		return 402;
	}

	public function getAdditionalData(): array
	{
		return $this->additional;
	}
}

class InvalidArgumentException extends ApiException
{
	const CODE = 702;
	public function __construct(string $name, string $argument, string $message = "", Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage('Invalid argument "%s" for %s%s', $argument, $name, ($message ? (': ' . $message) : ''));
	}
}

class MalformedInputException extends ApiException
{
	const CODE = 704;
	public function __construct(string $message, Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage($message);
	}
}

class InvalidSortFieldException extends ApiException
{
	const CODE = 703;
	public function __construct(string $field, ?Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage('Field %s is not sortable', $field);
	}
}

class InvalidTypeException extends ApiException
{
	const CODE = 701;
	public function __construct(string $message, ?Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage($message);
	}
}

class NonExistingObjectException extends ApiException
{
	const CODE = 700;

	public function __construct(int $id, string $name, Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage('Non-existing %s with ID %d', $name, $id);
		$this->additional = [
			'object' => $name,
			'id' => $id,
		];
	}
}

class InternalErrorException extends ApiException
{
	const CODE = 500;
	public function __construct(string $message, Throwable $previous)
	{
		parent::__construct($previous)
			->setMessage($message);
	}

	public function getHttpCode(): int
	{
		return self::CODE;
	}
}

class EntityLocationException extends ApiException
{
	const CODE = 710;

	public function __construct(string $given, Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage('Entity location must be Compartment, %s given', $given);
		$this->additional = [
			'given' => $given,
		];
	}
}


class EntityHierarchyException extends ApiException
{
	const CODE = 711;

	public function __construct(string $parent, string $child, Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage('Entity type %s can\'t have %s as parent', $child, $parent);
		$this->additional = [
			'parent' => $parent,
			'child' => $child,
		];
	}
}
