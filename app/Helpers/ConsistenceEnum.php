<?php

namespace App\Helpers;

use Consistence\Enum\Enum;
use App\Exceptions\InvalidEnumValueException as ApiException;
use Consistence\Enum\InvalidEnumValueException as EnumException;

abstract class ConsistenceEnum extends Enum implements \JsonSerializable
{
	public function __toString()
	{
		return $this->getValue();
	}

	public function jsonSerialize()
	{
		return $this->getValue();
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return static
	 */
	public static function tryGet(string $key, $value)
	{
		try {
			return static::get($value);
		}
		catch (EnumException $e) {
			throw new ApiException($key, $value, array_values(static::getAvailableValues()));
		}
	}
}
