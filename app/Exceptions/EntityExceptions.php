<?php

namespace App\Exceptions;

use Throwable;

class EntityException extends \Exception
{}

class InvalidFieldValueException extends EntityException
{}

class InvalidEnumFieldValueException extends InvalidFieldValueException
{
	public function __construct($field, $value, $oneOf)
	{
		parent::__construct(sprintf(
			'Invalid value "%s" for %s, must be one of %s',
			$value, $field, $oneOf
		), 0, null);
	}
}
