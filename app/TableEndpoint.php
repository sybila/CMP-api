<?php

declare(strict_types=1);

namespace App;

use Nette\Database\Connection;
use Nette\Database\ResultSet;
use Nette\Database\SqlLiteral;

trait TableEndpoint
{
	abstract protected function getConnection(): Connection;
	abstract protected function getTable(): string;
	abstract protected function getKeys(): array;

	protected function getData(int $id = 0): ResultSet
	{
		$where = [];
		if ($id)
			$where = ['id' => $id];

		$q = $this->getConnection()->query("SELECT ? FROM ? WHERE ?", new SqlLiteral(self::getKeys()), new SqlLiteral(self::getTable()), $where);
		return $q;
	}
}
