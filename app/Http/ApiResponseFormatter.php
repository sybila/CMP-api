<?php

declare(strict_types=1);

namespace App\Http;


final class ApiResponseFormatter
{
	public function formatMessage(string $message): array
	{
		return [
			'status' => 'ok',
			'payload' => [
				'message' => $message,
			],
		];
	}


	public function formatPayload(array $payload): array
	{
		return [
			'status' => 'ok',
			'payload' => $payload,
		];
	}


	public function formatError(string $message, int $code = 0): array
	{
		$ret = [
			'status' => 'error',
			'message' => $message,
		];

		if ($code)
			$ret['code'] = $code;

		return $ret;
	}


	public function formatException(\Exception $e): array
	{
		return [
			'status' => 'error-internal',
			'code' => $e->getCode(),
			'message' => $e->getMessage(),
		];
	}
}
