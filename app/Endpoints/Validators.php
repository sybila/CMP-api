<?php

namespace App\Controllers;

use App\Exceptions\MalformedInputDataException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class Validators
{
	public static $code;
	public static $identifierList;
	public static $states;
	public static $compartment;
	public static $complex;
	public static $structure;
	public static $atomic;
	public static $entity;

	public static function validate($data, $rules, $message)
	{
		$validator = Validation::createValidator();
		if (count($validator->validate($data, self::$$rules)) > 0)
			throw new MalformedInputDataException($message);
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
	'allowMissingFields' => true,
]);

Validators::$complex = new Assert\Collection([
	'fields' => [
		'compartments' => Validators::$identifierList,
		'children' => Validators::$identifierList,
	],
	'allowMissingFields' => true,
]);

Validators::$structure = new Assert\Collection([
	'fields' => [
		'parents' => Validators::$identifierList,
		'children' => Validators::$identifierList,
	],
	'allowMissingFields' => true,
]);

Validators::$atomic = new Assert\Collection([
	'fields' => [
		'parents' => Validators::$identifierList,
		'states' => Validators::$states,
	],
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
