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
	public function __construct(string $key, ?int $id, ?string $object, Throwable $previous = null)
	{
	    if ($id==null)
            parent::__construct($previous)
                ->setMessage('Some %s with given %s already exists.', $object, $key);
        else {
            parent::__construct($previous)
                ->setMessage('Some %s with given %s already exists with ID %d', $object, $key, $id);
            $this->additional = ['id' => $id, 'key' => $key];
	    }
	}

	public function getHttpCode(): int
	{
		return self::CODE;
	}
}

class DependentResourcesBoundException extends ApiException
{
	const CODE = 409;
	public function __construct(string $key, Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage('Object of type %s is bound', $key);

		$this->additional = ['key' => $key];
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

class EmptyArraySelection extends ApiException
{
    const CODE = 703;
    public function __construct(array $filter, ?Throwable $previous = null)
    {
        $filters = '';
        foreach ($filter as $by=>$expr) {
            if ($filters != null) {
                $filters .= ', ';
            }
            $filters .= "{$by} LIKE {$expr}";
        }
        parent::__construct($previous)
            ->setMessage('Empty list of objects returned. %s', $filters);
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

class RuleEquationException extends ApiException
{
	const CODE = 720;

	public function __construct(\stdClass $result, Throwable $previous = null)
	{
		$expected = null;
		if (!empty($result->expected))
			$expected = implode('", "', $result->expected);

		$expectedMessage = '';
		if ($expected)
			$expectedMessage = ', expected one of: "' . $expected . '"';

		parent::__construct($previous)
			->setMessage('Invalid equation specified, unexpected token: "%s"%s', $result->unexpected, $expectedMessage);

		$this->additional['unexpected'] = $result->unexpected;
		$this->additional['expected'] = $result->expected ?? null;
		$this->additional['position'] = $result->start;
	}
}

class RuleClassificationException extends ApiException
{
	const CODE = 721;

	public function __construct(Throwable $previous = null)
	{
		parent::__construct($previous)
			->setMessage('Rules must have classification with type "rule"');
	}
}

class InvalidAuthenticationException extends ApiException
{
    const CODE = 401;
    public function __construct(string $field, string $hint, ?Throwable $previous = null)
    {
        parent::__construct($previous)
            ->setMessage('%s %s.', $field, $hint);
    }

    public function getHttpCode(): int
    {
        return self::CODE;
    }
}

class InvalidRoleException extends ApiException
{
    const CODE = 403;
    public function __construct(string $action, string $api_action,
                                ?string $uri, ?Throwable $previous = null)
    {
        parent::__construct($previous)
            ->setMessage('Denied. Cannot %s resources on this uri: %s via %s method', $action, $uri, $api_action);
    }

    public function getHttpCode(): int
    {
        return self::CODE;
    }
}

class ActionConflictException extends ApiException
{
    const CODE = 409;
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($previous)
            ->setMessage('Conflict. %s', $message);
    }

    public function getHttpCode(): int
    {
        return self::CODE;
    }
}

class WrongParentException extends ApiException
{
    const CODE = 403;
    public function __construct(string $parent, ?int $pId,
                                string $child, ?int $chId, ?Throwable $previous = null)
    {
        parent::__construct($previous)
        ->setMessage('Cannot access %s with id %s from %s of id %s.', $child, $chId, $parent, $pId);
    }

    public function getHttpCode(): int
    {
        return self::CODE;
    }
}
class NonUniqueModelVariableAlias extends ApiException
{
    const CODE = 711;
    public function __construct(string $varType, string $alias,
                                string $existing, ?Throwable $previous = null)
    {
        parent::__construct($previous)
            ->setMessage('Cannot create %s with unique alias %s, because %s with such alias already exists.',
                $varType, $alias, $existing);
    }

    public function getHttpCode(): int
    {
        return self::CODE;
    }
}

class DependentAttributeException extends ApiException
{
    const CODE = 409;
    public function __construct(string $key, Throwable $previous = null)
    {
        parent::__construct($previous)
            ->setMessage('Attribute %s needs to be defined.', $key);

        $this->additional = ['key' => $key];
    }

    public function getHttpCode(): int
    {
        return self::CODE;
    }
}

class ConstantVariableException extends ApiException
{
    const CODE = 711;
    public function __construct(string $id, string $type, string $alias, Throwable $previous = null)
    {
        parent::__construct($previous)
            ->setMessage('%s with ID %s and alias %s is constant.', $type, $id, $alias);
    }

}