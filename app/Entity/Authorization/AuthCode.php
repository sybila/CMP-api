<?php

namespace App\Entity\Authorization;

use App\Repositories\Authorization\ClientRepository;
use App\Repositories\Authorization\ScopeRepository;
use App\Helpers\DateTimeJson;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AuthCode implements AuthCodeEntityInterface
{
	use TokenEntityTrait;

	/** @var string */
	private $id;

	/**
	 * @var DateTimeJson
	 */
	protected $expiryDateTime;

	/**
	 * @var string
	 */
	private $redirectUri;

	public function __construct(string $id)
	{
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getIdentifier()
	{
		return $this->id;
	}

	/**
	 * @param int $identifier
	 */
	public function setIdentifier($identifier)
	{
		$this->id = (int)$identifier;
	}

	public function isExpired()
	{
		return $this->expiryDateTime < (new DateTimeJson);
	}

	/**
	 * @return string
	 */
	public function getRedirectUri()
	{
		return $this->redirectUri;
	}

	/**
	 * @param string $redirectUri
	 */
	public function setRedirectUri($redirectUri): void
	{
		$this->redirectUri = $redirectUri;
	}

	public function saveToString(): string
	{
		$data = [
			$this->expiryDateTime->format(DateTimeJson::ATOM),
			$this->redirectUri,
			$this->userIdentifier,
			$this->client->getIdentifier(),
			implode('|', array_keys($this->scopes)),
		];

		return implode(chr(0), $data);
	}

	public static function loadFromString(ClientRepository $clients, ScopeRepository $scopes, string $id, string $data): AuthCode
	{
		$values = explode(chr(0), $data);
		if (count($values) !== 5)
			throw new \Exception('Corrupted data!');

		$inst = new static($id);
		$inst->expiryDateTime = DateTimeJson::createFromFormat(DateTimeJson::ATOM, array_shift($values));
		$inst->redirectUri = array_shift($values);
		$inst->userIdentifier = array_shift($values);
		$inst->client = $clients->getById((int)array_shift($values));

		foreach (explode('|', array_shift($values)) as $scopeId)
			$inst->addScope($scopes->getScopeEntityByIdentifier($scopeId));

		return $inst;
	}
}
