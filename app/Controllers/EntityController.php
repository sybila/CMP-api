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
 * API for managing Entities
 * Properties marked as virtual are not available when saving (PUT, POST methods).
 *
 * <json>
 * {
 *  "id": Numeric ID, unique among all entities
 *  "code": Code of entity, unique among all entities
 *  "type": Type of entity, one of ["compartment", "complex", "structure", "atomic"]
 *  "name": Name of entity
 *  "description": Description of entity
 *  "classifications": List of classification IDs (see Classifications endpoint)
 *  "organisms": List of organism IDs (see Organisms endpoint)
 *  "status": Status of entity, one of ["pending", "active", "inactive"]
 * }
 * </json>
 *
 * For type compartment:
 * <json>
 * {
 *  "parent": ID of parent compartment (can be null)
 *  "children": List of children IDs (these are of type compartment) {virtual}
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

	private static $types = [
		'compartment',
		'complex',
		'structure',
		'atomic',
	];

	private static $statuses = [
		0 => 'pending',
		1 => 'active',
		2 => 'inactive',
	];

	public function actionRead(int $id = 0)
	{
		;
	}

	public function actionCreate()
	{
		if ($this->request->getPost('status') == 'active')
			$this->validateActive(0);
	}

	public function actionUpdate($id)
	{
		if ($this->request->getPost('status') == 'active')
			$this->validateActive((int)$id);
	}

	public function actionDelete($id)
	{
		$this->db->beginTransaction();
		$this->db->query("DELETE FROM ep_entity WHERE id = ?", $id);
		$this->db->query("DELETE FROM ep_entity_classification WHERE entityId = ?", $id);
		$this->db->query("DELETE FROM ep_entity_composition WHERE parentEntityId = ? OR childEntityId = ?", $id, $id);
		$this->db->query("DELETE FROM ep_entity_organism WHERE entityId = ?", $id);
		$this->db->commit();
	}

	private function validateActive(int $id): bool
	{
		$classf = [];
		if ($this->request->getPost('classifications') === null)
		{
			if ($id)
				$classf = $this->db->fetchPairs("SELECT classificationId FROM ep_entity_classification WHERE entityId = ?", $id);
		}
		else
			$classf = $this->request->getPost('classifications');

		if (!is_array($classf))
			throw new InvalidTypeException('Value of "classifications" has to be array of classification IDs.');

		array_walk($classf, function($value) {
			$this->getTypedValue('int', $value, 'classification id');
		});

		$cnt = $this->db->fetchField(
			"SELECT SQL_NO_CACHE COUNT(id) FROM ep_classification WHERE id IN ?",
			$classf
		);
		if (count($classf) != $cnt)
			throw new InvalidTypeException('Value of "classifications" has to be array of classification IDs.');

		$orgs = [];
		if ($this->request->getPost('organisms') === null)
		{
			if ($id)
				$orgs = $this->db->fetchPairs("SELECT organismId FROM ep_entity_organism WHERE entityId = ?", $id);
		}
		else
			$orgs = $this->request->getPost('organisms');

		if (!is_array($orgs))
			throw new InvalidTypeException('Value of "organisms" has to be array of organism IDs.');

		array_walk($orgs, function($value) {
			$this->getTypedValue('int', $value, 'organism id');
		});

		$cnt = $this->db->fetchField(
			"SELECT SQL_NO_CACHE COUNT(id) FROM ep_organism WHERE id IN ?",
			$orgs
		);
		if (count($orgs) != $cnt)
			throw new InvalidTypeException('Value of "organisms" has to be array of organism IDs.');
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
			'type' => ['type' => 'string', self::$types],
			'status' => ['type' => 'int', 'data' => array_values(self::$statuses)],
		];
	}
}
