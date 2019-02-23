<?php

namespace App\Repositories\Authorization;

use App\Entity\Authorization\AuthCode;
use App\Entity\Repositories\IRepository;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface, IRepository
{
	const CODE_SAVE_BONUS = 5 * 60;

	/** @var \Memcached */
	private $cache;

	public function __construct(\Memcached $cache)
	{
		$this->cache = $cache;
	}

	private function keyExists(string $key): bool
	{
		$this->cache->get($key);
		return $this->cache->getResultCode() !== \Memcached::RES_NOTFOUND;
	}

	public function getNewAuthCode()
	{
		do {
			$id = uniqid();
		} while ($this->keyExists($id));

		return new AuthCode($id);
	}

	/**
	 * @param AuthCode|AuthCodeEntityInterface $code
     * @inheritDoc
	 */
	public function persistNewAuthCode(AuthCodeEntityInterface $code)
	{
		$saveTime = $code->getExpiryDateTime()->getTimestamp() + self::CODE_SAVE_BONUS;
		if (!$this->cache->add($code->getIdentifier(), $code->saveToString(), $saveTime))
			throw UniqueTokenIdentifierConstraintViolationException::create();
	}

	public function revokeAuthCode($codeId)
	{
		$this->cache->delete($codeId);
	}

	public function isAuthCodeRevoked($codeId)
	{
		return !$this->keyExists($codeId);
	}
}
