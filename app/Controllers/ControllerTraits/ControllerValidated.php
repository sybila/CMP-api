<?php

namespace App\Controllers;

use App\Exceptions\MalformedInputException;
use App\Helpers\ArgumentParser;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

trait ControllerValidated
{
	protected static function validate(ArgumentParser $data, ?Assert\Collection $rules): void
	{
		if ($rules === null)
			return;

		$validator = Validation::createValidator();

		$rules->allowExtraFields = true;
		$rules->allowMissingFields = true;

		$errors = $validator->validate($data, $rules);
		if (count($errors) > 0)
			throw new MalformedInputException('Invalid input data', $errors);
	}
}
