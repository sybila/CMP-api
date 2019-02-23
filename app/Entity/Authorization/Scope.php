<?php

namespace App\Entity\Authorization;

use League\OAuth2\Server\Entities\ScopeEntityInterface;

class Scope implements ScopeEntityInterface
{
	/**
	 * @var string
	 */
	private $id;

	final public function getId(): string
	{
		return $this->id;
	}

	public function __clone()
	{
		$this->id = NULL;
	}

	public function __construct(string $id)
	{
		$this->id = $id;
	}

	public function getIdentifier()
	{
		return $this->getId();
	}

	public function setIdentifier($identifier)
	{
		$this->id = (int)$identifier;
	}

	public function jsonSerialize()
	{
		return $this->id;
	}
}
