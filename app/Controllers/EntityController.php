<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DataEndpoint;
use App\Model\
{
	InvalidTypeException, NonExistingObjectException
};
use Nette\Caching\Cache;
use Nette\Database\ResultSet;

final class EntityController extends AbstractController
{
	use DataEndpoint;

	private static $types = [
		1 => 'compartment',
		2 => 'complex',
		3 => 'structure',
		4 => 'atomic',
	];

	private static $statuses = [
		0 => 'pending',
		1 => 'active',
		2 => 'inactive',
	];

	/** @var Cache */
	private $cache;

	public function startup()
	{
		$this->cache = new Cache($this->cacheStorage, 'entities');
	}

	public function actionRead(int $id = 0)
	{
		if ($id)
			$this->payload->data = $this->loadEntity($id);
		else
			$this->payload->data = $this->loadEntities();
	}

	protected function loadEntities() : array
	{
		$res = [];

		foreach ($this->db->query("SELECT SQL_NO_CACHE id, name, code, hierarchy_type AS type, active AS status FROM ep_entity WHERE parentId IS NULL") as $row)
		{
			$row = (array)$row;
			$row['type'] = self::$types[$row['type']];
			$row['status'] = self::$statuses[$row['status'] ?: 1];
			$res[] = $row;
		}

		return $res;
	}

	protected function loadEntity(int $id) : array
	{
		$row = $this->db->fetch("SELECT SQL_NO_CACHE id, name, description, code, hierarchy_type AS type, active AS status FROM ep_entity WHERE id = ? AND parentId IS NULL", $id);
		if (!$row)
			throw new NonExistingObjectException($id);

		$row = (array)$row;
		$row['type'] = self::$types[$row['type']];
		$row['status'] = self::$statuses[$row['status'] ?: 1];

		$row['classifications'] = $this->db->fetchPairs("SELECT SQL_NO_CACHE c.id
									FROM ep_classification AS c
									INNER JOIN ep_entity_classification AS ec ON ec.classificationId = c.id
									WHERE ec.entityId = ? AND c.type = 'entity'", $row['id']);

		$row['organisms'] = $this->db->fetchPairs("SELECT SQL_NO_CACHE o.id
									FROM ep_organism AS o
									INNER JOIN ep_entity_organism AS eo ON eo.organismId = o.id
									WHERE eo.entityId = ?", $row['id']);

		switch ($row['type'])
		{
			case 'compartment':
				$row['parent'] = $this->db->fetchField("SELECT SQL_NO_CACHE parentEntityId FROM ep_entity_location WHERE childEntityId = ?", $row['id']) ?: null;
				$row['children'] = $this->db->fetchPairs("SELECT SQL_NO_CACHE childEntityId AS id FROM ep_entity_location WHERE parentEntityId = ?", $row['id']);
				break;
			case 'complex':
				$row['children'] = $this->db->fetchPairs("SELECT SQL_NO_CACHE childEntityId AS id FROM ep_entity_composition WHERE parentEntityId = ?", $row['id']);
				break;
			case 'structure':
				$row['parents'] = $this->db->fetchPairs("SELECT SQL_NO_CACHE parentEntityId AS id FROM ep_entity_composition WHERE childEntityId = ?", $row['id']);
				$row['children'] = $this->db->fetchPairs("SELECT SQL_NO_CACHE childEntityId AS id FROM ep_entity_composition WHERE parentEntityId = ?", $row['id']);
				break;
			case 'atomic':
				$row['parents'] = $this->db->fetchPairs("SELECT SQL_NO_CACHE parentEntityId AS id FROM ep_entity_composition WHERE childEntityId = ?", $row['id']);
				$row['states'] = $this->db->fetchAll("SELECT SQL_NO_CACHE code, description FROM ep_entity WHERE parentId = ?", $row['id']);
				break;
		}

		$row['compartments'] = $this->db->fetchPairs("SELECT SQL_NO_CACHE parentEntityId AS id FROM ep_entity_location WHERE childEntityId = ?", $row['id']);

		return $row;
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
//			'type' => ['type' => 'string', self::$types],
			'status' => ['type' => 'int', 'data' => array_values(self::$statuses)],
		];
	}
}
