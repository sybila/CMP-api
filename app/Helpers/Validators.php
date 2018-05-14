<?php

namespace App\Helpers;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueIntegerCollection extends Constraint
{
	public static $nonValidValueMessage = 'This value is not positive integer';
	public static $nonUniqueMessage = 'This value is not unique';
}

class UniqueIntegerCollectionValidator extends ConstraintValidator
{
	private function check($key, $element): bool
	{
		if (!is_numeric($element) || $element < 1)
		{
			$this->context->buildViolation(UniqueIntegerCollection::$nonValidValueMessage)
				->atPath('[' . $key . ']')
				->addViolation();

			return false;
		}
		else
			return true;
	}

	public function validate($value, Constraint $constraint)
	{
		if (!$constraint instanceof UniqueIntegerCollection)
			throw new UnexpectedTypeException($constraint, UniqueIntegerCollection::class);

		if (null === $value)
			return;

		if (!is_iterable($value))
			throw new UnexpectedTypeException($value, 'iterable');

		if ($value instanceof \Traversable)
		{
			$valueArray = [];
			foreach ($value as $key => $element)
			{
				if (!$this->check($key, $element))
					return;

				$valueArray[$key] = $element;
			}
		}
		else
		{
			foreach ($value as $key => $element)
				if (!$this->check($key, $element))
					return;

			$valueArray = $value;
		}

		asort($valueArray);

		$prev = null;
		foreach ($valueArray as $key => $element)
		{
			if ($prev !== null && $element == $prev)
			{
				$this->context->buildViolation(UniqueIntegerCollection::$nonUniqueMessage)
					->atPath('[' . $key . ']')
					->addViolation();

				return;
			}

			$prev = $element;
		}
	}
}

class Validators
{
	public static $identifier;
	public static $code;
	public static $identifierList;
}

Validators::$identifier = new Assert\Range(['min' => 1]);

Validators::$code = new Assert\NotBlank;

Validators::$identifierList = new UniqueIntegerCollection;
