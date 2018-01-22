<?php

namespace App\Model;

use Ublaboo\ApiRouter\ApiRoute;

class AppRoute extends ApiRoute
{
	public function __construct($path, $method, $presenter = null, array $data = [])
	{
		$defaults = [
			'format' => 'json',
			'methods' => [$method],
			'description' => '',
		];

		if (isset($data['desc-file']))
		{
			$file = __DIR__ . '/../docs/' . ((string)$data['desc-file']) . '.txt';
			if (file_exists($file))
				$data['description'] = file_get_contents($file);

			unset($data['desc-file']);
		}

		parent::__construct($path, $presenter, array_merge($defaults, $data));
	}
}
