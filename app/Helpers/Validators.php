<?php

namespace App\Helpers;

use Symfony\Component\Validator\Constraints as Assert;

class Validators
{
	public static $identifier;
	public static $code;
	public static $identifierList;
}

Validators::$identifier = new Assert\Range(['min' => 0]);

Validators::$code = new Assert\NotBlank;

Validators::$identifierList = new Assert\All([
	'constraints' => [
		new Assert\NotBlank,
		Validators::$identifier
	]
]);
