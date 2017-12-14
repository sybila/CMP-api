<?php

declare(strict_types=1);

namespace App;

use App\Http\ErrorException;
use App\Model\InvalidTypeException;
use Nette\Database\SqlLiteral;
use Nette\Application\Request;

trait DataEndpoint
{
	abstract protected function getRequest() : Request;
	abstract protected static function getKeys(): array;

	/**
	 * Check value by type, if it can be represented, return value in given type, else throw
	 * @param string $type
	 * @param string $value
	 * @param string $field field name for error message
	 * @return mixed
	 * @throws InvalidTypeException
	 */
	protected function getTypedValue($type, $value, $field)
	{
		if (is_array($type))
		{
			$value = $this->getTypedValue($type['type'], $value, $field);
			if (!in_array($value, $type['data'], true))
				throw new InvalidTypeException('Value of "' . $field . '" has to be one of: ' . (implode(', ', $type)) . '.');

			return $value;
		}

		switch ($type)
		{
			case 'int':
				if (is_numeric($value))
					return (int)$value;
				break;

			case 'string':
				if (is_scalar($value))
					return $value;

				break;

			case 'bool':
				if ($value == 'true' || $value == 'false')
					return $value == 'true';
				elseif ($value == '1' || $value == '0')
					return (bool)((int)$value);

				break;

			default:
				break;
		}

		throw new InvalidTypeException('Value of "' . $field . '" can\' be converted to ' . $type . '.');
	}

	protected function buildData(array $savedData = null): array
	{
		$data = array_fill_keys(self::getKeys(), null);

		foreach (self::getKeys() as $key => $type)
		{
			if ($value = $this->getRequest()->getPost($key))
			{
				$value = $this->getTypedValue($type, $value, $key);
				$this->checkData($key, $value);
				$data[$key] = $value;
			}
			else if ($savedData)
				$data[$key] = $savedData[$key];
		}

		foreach (self::getAddKeys() as $key)
		{
			if ($value = $this->getRequest()->getPost($key))
			{
				$this->checkData($key, $value);
				$data[$key] = $value;
			}
		}

		return $data;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @throws ErrorException on invalid data
	 */
	protected function checkData(string $key, $value): void
	{
	}

	protected static function getSqlKeys(bool $withId): SqlLiteral
	{
		return new SqlLiteral(
			implode(', ', array_merge(
				$withId ? ['id'] : [],
				array_keys(self::getKeys())
			))
		);
	}

	protected static function getAddKeys(): array
	{
		return [];
	}
}
