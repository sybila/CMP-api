<?php

namespace App\Http;

use Nette\Application\IResponse;

class ResponseException extends \Exception
{
	/** @var IResponse */
	private $response;

	public function __construct(IResponse $response)
	{
		parent::__construct('', 0, null);
		$this->response = $response;
	}

	public function getResponse() : IResponse
	{
		return $this->response;
	}
}
