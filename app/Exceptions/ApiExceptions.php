<?php

namespace App\Exceptions;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
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
		return 400;
	}

	public function getAdditionalData(): array
	{
		return $this->additional;
	}
}

class UniqueKeyViolationException extends ApiException
{
	const CODE = 409;
	public function __construct(string $key, int $id, Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage('Object with given %s already exists with ID %d', $key, $id);

		$this->additional = ['id' => $id, 'key' => $key];
	}

	public function getHttpCode(): int
	{
		return self::CODE;
	}
}

/**
 * thrown when some argument/key is invalid for reason which can not be explained by other exceptions
 */
class InvalidArgumentException extends ApiException
{
	const CODE = 702;
	public function __construct(string $name, ?string $argument, string $message, Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage('Invalid argument "%s" for %s: %s', $argument, $name, $message);

		$this->additional['key'] = $name;
		$this->additional['message'] = $message;
	}
}

class MissingRequiredKeyException extends ApiException
{
	const CODE = 705;
	public function __construct(string $key, ?Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage('Missing or empty required key %s', $key);

		$this->additional['key'] = $key;
	}
}

/**
 * thrown when input validation by Symfony Validator fails
 */
class MalformedInputException extends ApiException
{
	const CODE = 704;
	public function __construct(string $message, ConstraintViolationListInterface $errors, Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage($message);

		$this->additional['errors'] = [];
		/** @var ConstraintViolationInterface $error */
		foreach ($errors as $error)
			$this->additional['errors'][] = ['key' => $error->getPropertyPath(), 'message' => $error->getMessage()];
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

class InvalidEnumValueException extends ApiException
{
	const CODE = 706;
	public function __construct(string $key, string $value, array $allowed, ?Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage(
				'Invalid value %s for field %s (must be one of %s)',
				$value, $key, implode(', ', $allowed)
			);

		$this->additional['key'] = $key;
		$this->additional['allowed'] = $allowed;
	}
}

class InvalidTypeException extends ApiException
{
	const CODE = 701;
	public function __construct(string $key, string $type, ?Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage('Value of "%s" can\' be converted to %s.', $key, $type);

		$this->additional['type'] = $type;
		$this->additional['key'] = $key;
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

class CompartmentLocationException extends ApiException
{
	const CODE = 712;

	public function __construct(Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage('Entity location cannot be specified for Compartment');
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

class EntityClassificationException extends ApiException
{
	const CODE = 712;

	public function __construct(Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage('Entities must have classification with type "entity"');
	}
}
