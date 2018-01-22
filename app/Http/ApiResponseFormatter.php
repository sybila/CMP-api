<?php

declare(strict_types=1);

namespace App\Http;

use App\Model\ApiException;
use Tracy\Debugger;

final class ApiResponseFormatter
{
	public function formatPayload(array $payload): array
	{
		return [
			'status' => 'ok',
			'data' => $payload,
		];
	}

	public function formatError(ApiException $e): array
	{
		return [
			'status' => 'error',
			'message' => $e->getMessage(),
			'code' => $e->getCode(),
		];
	}

	public function formatHttpError(string $message, int $code): array
	{
		return [
			'status' => 'error',
			'message' => $message,
			'code' => $code,
		];
	}

	public function formatInternalError(\Exception $e): array
	{
		$ret = [
			'status' => 'error',
			'code' => $e->getCode(),
			'message' => $e->getMessage(),
		];
		if (Debugger::$productionMode)
			$ret['message'] = null;

		return $ret;
	}
}
