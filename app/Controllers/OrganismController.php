<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DataEndpoint;
use App\Http\ErrorException;
use App\Model\InvalidTypeException;
use App\TableEndpoint;
use Nette\Database\IRow;
use Nette\Database\ResultSet;
use Nette\Database\SqlLiteral;
use Tracy\Debugger;
use Ublaboo\ApiRouter\ApiRoute;

final class OrganismController extends AbstractController
{
	use DataEndpoint;

	public function actionRead()
	{
		$this->payload->data = $this->db->fetchAll("SELECT SQL_NO_CACHE ? FROM ep_organism", self::getSqlKeys(true));
	}

	protected function getDb(): \Nette\Database\Connection
	{
		return $this->db;
	}

	protected static function getKeys(): array
	{
		return [
			'name' => 'string',
			'code' => 'string',
		];
	}
}
