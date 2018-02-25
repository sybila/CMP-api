<?php

declare(strict_types=1);

namespace App\Routing;

use App\Model\AppRoute;
use Nette\Application\Routers\RouteList;

final class RouterFactory
{
	public function create(): RouteList
	{
		$router = new RouteList;

		// Default
		$router[] = new AppRoute('/', 'GET', 'Default', [
			'desc-file' => 'main',
		]);

		// Version
		$router[] = new AppRoute('/version', 'GET', 'Version', [
			'description' => 'Returns API version information',
		]);

		// Entities
		$router[] = new AppRoute('/entities[/<id>]', 'GET', 'Entity', [
			'parameters' => [
				'id' => ['requirement' => '\\d+'],
			],
			'desc-file' => 'entity',
		]);
		$router[] = new AppRoute('/entities', 'POST', 'Entity', [
			'desc-file' => 'entity-save',
		]);
		$router[] = new AppRoute('/entities/<id>', 'PUT', 'Entity', [
			'parameters' => ['id' => ['requirement' => '\\d+']],
			'desc-file' => 'entity-save',
		]);
		$router[] = new AppRoute('/entities/<id>', 'DELETE', 'Entity', [
			'parameters' => ['id' => ['requirement' => '\\d+']],
		]);

		// Rules
		$router[] = new AppRoute('/rules[/<id>]', 'GET', 'Rule', [
			'parameters' => ['id' => ['requirement' => '\\d+']],
			'desc-file' => 'rule',
		]);
		$router[] = new AppRoute('/rules', 'POST', 'Rule', [
			'desc-file' => 'rule-save',
		]);
		$router[] = new AppRoute('/rules/<id>', 'PUT', 'Rule', [
			'parameters' => ['id' => ['requirement' => '\\d+']],
			'desc-file' => 'rule-save',
		]);
		$router[] = new AppRoute('/rules/<id>', 'DELETE', 'Rule', [
			'parameters' => ['id' => ['requirement' => '\\d+']],
		]);

		// Classifications
		$router[] = new AppRoute('/classifications[/<type>]', 'GET', 'Classification', [
			'parameters' => ['type' => ['requirement' => '\\s+']],
			'desc-file' => 'classification',
		]);

		// Organisms
		$router[] = new AppRoute('/organisms', 'GET', 'Organism', [
			'desc-file' => 'organism',
		]);

		return $router;
	}
}
