<?php

namespace App\Helpers;

use App\Exceptions\MalformedInputException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class Validators
{
	private static $validator;

	public static $code;
	public static $identifierList;
	public static $states;
	public static $compartment;
	public static $complex;
	public static $structure;
	public static $atomic;
	public static $entity;
	public static $pagination;

	public static function validate($data, $rules, $message)
	{
		if (!self::$validator)
			self::$validator = Validation::createValidator();

		if (count(self::$validator->validate($data, self::$$rules)) > 0)
			throw new MalformedInputException($message);
	}
}

$identifier = new Assert\Range(['min' => 0]);

Validators::$code = new Assert\NotBlank;

Validators::$identifierList = new Assert\All([
	'constraints' => [
		new Assert\NotBlank,
		$identifier
	]
]);

Validators::$states = new Assert\Collection([
	'fields' => [
		'code' => new Assert\NotBlank(),
		'description' => new Assert\Type(['type' => 'string']),
	],
]);

Validators::$compartment = new Assert\Collection([
	'fields' => [
		'parent' => $identifier,
	],
	'allowExtraFields' => true,
	'allowMissingFields' => true,
]);

Validators::$complex = new Assert\Collection([
	'fields' => [
		'compartments' => Validators::$identifierList,
		'children' => Validators::$identifierList,
	],
	'allowExtraFields' => true,
	'allowMissingFields' => true,
]);

Validators::$structure = new Assert\Collection([
	'fields' => [
		'parents' => Validators::$identifierList,
		'children' => Validators::$identifierList,
	],
	'allowExtraFields' => true,
	'allowMissingFields' => true,
]);

Validators::$atomic = new Assert\Collection([
	'fields' => [
		'parents' => Validators::$identifierList,
		'states' => Validators::$states,
	],
	'allowExtraFields' => true,
	'allowMissingFields' => true,
]);

Validators::$entity = new Assert\Collection([
	'fields' => [
		'name' => new Assert\Type(['type' => 'string']),
		'code' => Validators::$code,
		'description' => new Assert\Type(['type' => 'string']),
		'status' => new Assert\Type(['type' => 'string']),
	],
	'allowExtraFields' => true,
	'allowMissingFields' => true,
]);

Validators::$pagination = new Assert\Collection([
	'fields' => [
		'page' => new Assert\Range(['min' => 1]),
		'perPage' => new Assert\Range(['min' => 0]),
	],

	'allowExtraFields' => true,
	'allowMissingFields' => true,
]);
