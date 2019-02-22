<?php

namespace App\Repositories\Authorization;

use App\Entity\Authorization\Scope;
use App\Entity\Repositories\IRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface, IRepository
{
	private static $initialized = false;

	private static $scopes = [
		'all',
	];

	private static function init()
	{
		if (self::$initialized)
			return;

		$scopes = [];
		foreach (self::$scopes as $id => $name)
			$scopes[$name] = new Scope($name);

		self::$scopes = $scopes;
		self::$initialized = true;
	}

	public static function getById(string $identifier): ?Scope
	{
		self::init();
		return self::$scopes[$identifier] ?? null;
	}

	public function getScopeEntityByIdentifier($identifier)
	{
		return self::getById((string)$identifier);
	}

	public function finalizeScopes(array $scopes, $grantType, ClientEntityInterface $client, $userIdentifier = null)
	{
		return $scopes;
	}
}
