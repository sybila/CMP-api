<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DataEndpoint;
use App\Http\ErrorException;

final class ClassificationController extends AbstractController
{
	use DataEndpoint;

	public function actionRead(string $type = null)
	{
		if ($type !== null && $type !== 'entity' && $type !== 'rule')
			throw new ErrorException('Invalid type parameter');

		$where = [];
		if ($type)
			$where = ['type' => $type];

		$this->payload->data = $this->db->fetchAll("SELECT SQL_NO_CACHE ? FROM ep_classification WHERE ?", self::getSqlKeys(true), $where);
	}

	protected function getDb(): \Nette\Database\Connection
	{
		return $this->db;
	}

	protected static function getKeys(): array
	{
		return [
			'name' => 'string',
			'type' => ['type' => 'string', 'data' => ['entity', 'rule']],
		];
	}
}
