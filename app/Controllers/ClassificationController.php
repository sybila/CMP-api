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

/**
 * API for managing Classifications
 *
 * <json>
 * {
 *  "id": Numeric ID, unique among all classifications
 *  "name": Name of classification
 *  "type": What can this classification be used for, one of ["entity", "rule"]
 * }
 * </json>
 *
 * @ApiRoute(
 * 	"/classifications[/<type>]",
 *  presenter="Classification",
 *  format="json",
 *  methods={"GET"}
 * )
 */
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

	protected static function getKeys(): array
	{
		return [
			'name' => 'string',
			'type' => ['type' => 'string', 'data' => ['entity', 'rule']],
		];
	}
}
