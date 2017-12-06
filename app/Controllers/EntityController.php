<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DataEndpoint;
use App\Http\ErrorException;
use App\TableEndpoint;
use Nette\Database\IRow;
use Nette\Database\ResultSet;
use Nette\Database\SqlLiteral;
use Tracy\Debugger;
use Ublaboo\ApiRouter\ApiRoute;

/**
 * API for managing Entities
 * Properties marked as virtual are not available when saving (PUT, POST methods).
 *
 * <json>
 * {
 *  "id": Numeric ID, unique among all entities
 *  "name": Name of entity
 *  "description": Description of entity
 *  "code": Code of entity, unique among all entities
 *  "type": Type of entity, one of ["compartment", "complex", "structure", "atomic"]
 *  "modifier": String - mostly list of modifiers, separated by comma, but can be any string basically
 *  "classifications": List of classifications (objects with id and name properties)
 *  "organisms": List of organisms (objects with id and name properties)
 * }
 * </json>
 *
 * For type complex:
 * <json>
 * {
 * 	"compartments": List of compartment (entity) IDs
 * 	"children": List of children IDs (these are of type structure or atomic)
 * }
 * </json>
 *
 * For type structure:
 * <json>
 * {
 * 	"parents": IDs of parents (of type complex) {virtual}
 * 	"children": List of children IDs (these are of type atomic)
 * }
 * </json>
 *
 * For type atomic:
 * <json>
 * {
 * 	"parents": ID of parents (of type structure or complex) {virtual}
 * 	"states": List of atomic's possible states (objects with code and description properties)
 * }
 * </json>
 *
 * When saving (POST, PUT) entities, input for classifications and organisms is expected to be array of IDs.
 *
 * @ApiRoute(
 * 	"/entities[/<id>]",
 *  parameters={
 * 		"id"={
 * 			"requirement": "\d+"
 * 		}
 * 	},
 *  presenter="Entity",
 *  format="json"
 * )
 */
final class EntityController extends AbstractController
{
	use DataEndpoint;

	public function actionRead(int $id = 0)
	{
		;
	}

	public function actionCreate()
	{
		;
	}

	public function actionUpdate($id)
	{
		;
	}

	public function actionDelete($id)
	{
		;
	}

	protected function fetchAll(array $where = null): ResultSet
	{
		return null;
	}

	protected static function getKeys(): array
	{
		return [
			'name' => 'string',
			'description' => 'string',
			'code' => 'string',
			'type' => 'string',
		];
	}
}
