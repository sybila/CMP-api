<?php

namespace App\Helpers;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use JsonSerializable;

class DateTimeJson extends \DateTimeImmutable implements JsonSerializable
{
	public function jsonSerialize()
	{
		return $this->format(DATE_W3C);
	}

	public static function createFromFormat($format, $time, $timezone = null)
	{
		return new static(
			\DateTimeImmutable::createFromFormat($format, $time, $timezone)->format(\DateTime::ATOM)
		);
	}

	public static function createFromDateTime(\DateTimeInterface $dateTime)
	{
		if ($dateTime instanceof DateTimeJson)
			return $dateTime;

		return static::createFromFormat(
			\DateTime::ATOM,
			$dateTime->format(\DateTime::ATOM),
			$dateTime->getTimezone()
		);
	}
}

class DateTimeJsonType extends DateTimeImmutableType
{
	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		if ($value === null || $value instanceof DateTimeJson) {
			return $value;
		}

		$dateTime = DateTimeJson::createFromFormat($platform->getDateTimeFormatString(), $value);

		if (!$dateTime)
		{
			throw ConversionException::conversionFailedFormat(
				$value,
				$this->getName(),
				$platform->getDateTimeFormatString()
			);
		}

		return $dateTime;
	}
}
