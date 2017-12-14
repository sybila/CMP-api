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
 * API for managing Organisms
 *
 * <json>
 * {
 *  "id": Numeric ID, unique among all organisms
 *  "name": Name of organism
 * }
 * </json>
 *
 * @ApiRoute(
 * 	"/organisms[/<id>]",
 *  parameters={
 * 		"id"={
 * 			"requirement": "\d+"
 * 		}
 * 	},
 *  presenter="Organism",
 *  format="json",
 *  methods={"GET"}
 * )
 */
final class OrganismController extends AbstractController
{
	use DataEndpoint;

	public function actionRead(int $id = null)
	{
		$where = [];
		if ($id)
			$where = ['id' => $id];

		$this->payload->data = $this->db->fetchAll("SELECT SQL_NO_CACHE ? FROM ep_organism WHERE ?", self::getSqlKeys(true), $where);
	}

	protected static function getKeys(): array
	{
		return [
			'name' => 'string',
			'code' => 'string',
		];
	}
}
