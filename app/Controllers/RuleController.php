<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DataEndpoint;
use App\Model\NonExistingObjectException;
use Nette\Caching\Cache;

final class RuleController extends AbstractController
{
	use DataEndpoint;

	private static $statuses = [
		0 => 'pending',
		1 => 'active',
		2 => 'inactive',
	];

	/** @var Cache */
	private $cache;

	public function startup()
	{
		$this->cache = new Cache($this->cacheStorage, 'rules');
	}

	public function actionRead(int $id = 0)
	{
		if ($id)
			$this->payload->data = $this->loadRule($id);
		else
			$this->payload->data = $this->loadRules();
	}

	protected function loadRules()
	{
		$res = [];

		foreach ($this->db->query("SELECT SQL_NO_CACHE id, name, equation, active AS status FROM ep_reaction") as $row)
		{
			$row = (array)$row;
			$row['status'] = self::$statuses[$row['status']];
			$res[] = $row;
		}

		return $res;
	}

	protected function loadRule(int $id) : array
	{
		$row = $this->db->fetch("SELECT SQL_NO_CACHE id, name, equation, modifier, description, active AS status FROM ep_reaction WHERE id = ?", $id);
		if (!$row)
			throw new NonExistingObjectException($id);

		$row = (array)$row;

		$row['status'] = self::$statuses[$row['status']];
		$row['classifications'] = $this->db->fetchPairs("SELECT SQL_NO_CACHE c.id
									FROM ep_classification AS c
									INNER JOIN ep_reaction_classification AS rc ON rc.classificationId = c.id
									WHERE rc.reactionId = ? AND c.type = 'reaction'", $row['id']);

		$row['organisms'] = $this->db->fetchPairs("SELECT SQL_NO_CACHE o.id
									FROM ep_organism AS o
									INNER JOIN ep_reaction_organism AS ro ON ro.organismId = o.id
									WHERE ro.reactionId = ?", $row['id']);

		return $row;
	}

	public function actionCreate()
	{
		$this->db->beginTransaction();

		$this->db->query("INSERT INTO ep_reaction (?) VALUES ?", self::getSqlKeys(false), array_values($this->buildData()));
		$id = $this->db->getInsertId();

		$classf = $this->request->getPost('classifications');
		if (empty($classf) || !is_array($classf))
			$classf = [];

		$classfVals = [];
		foreach ($classf as $row)
			$classfVals[] = [$id, $this->getTypedValue('int', $row, 'classification id')];

		$this->db->query("INSERT INTO ep_reaction_classification (reactionId, classificationId) VALUES ?", $classfVals);

		$orgVals = [];
		foreach ($classf as $row)
			$orgVals[] = [$id, $this->getTypedValue('int', $row, 'organism id')];

		$this->db->query("INSERT INTO ep_reaction_organism (reactionId, organismId) VALUES ?", $orgVals);

		$this->db->commit();
		$this->cache->remove('data');
	}

	public function actionUpdate($id)
	{
		$this->db->beginTransaction();

		$this->db->query("UPDATE ep_reaction SET ? WHERE id = ?", $this->buildData(), $id);

		$classf = $this->request->getPost('classifications');
		if (empty($classf) || !is_array($classf))
			$classf = [];

		$classfVals = [];
		foreach ($classf as $row)
			$classfVals[] = [$id, $this->getTypedValue('int', $row, 'classification id')];

		$this->db->query("DELETE FROM ep_reaction_classification WHERE reactionId = ?", $id);
		$this->db->query("INSERT INTO ep_reaction_classification (reactionId, classificationId) VALUES ?", $classfVals);

		$orgVals = [];
		foreach ($classf as $row)
			$orgVals[] = [$id, $this->getTypedValue('int', $row, 'organism id')];

		$this->db->query("DELETE FROM ep_reaction_organism WHERE reactionId = ?", $id);
		$this->db->query("INSERT INTO ep_reaction_organism (reactionId, organismId) VALUES ?", $orgVals);

		$this->db->commit();
		$this->cache->remove('data');
	}

	public function actionDelete($id)
	{
		$this->db->beginTransaction();
		$this->db->query("DELETE FROM ep_reaction WHERE id = ?", $id);
		$this->db->query("DELETE FROM ep_reaction_classification WHERE reactionId = ?", $id);
		$this->db->query("DELETE FROM ep_reaction_organism WHERE reactionId = ?", $id);
		$this->db->query("DELETE FROM ep_reaction_equation_entity WHERE reactionId = ?", $id);
		$this->db->query("DELETE FROM ep_reaction_equation_variable WHERE reactionId = ?", $id);
		$this->db->query("DELETE FROM ep_reaction_note WHERE reactionId = ?", $id);
		$this->db->commit();
		$this->cache->remove('data');
	}

	protected function getDb(): \Nette\Database\Connection
	{
		return $this->db;
	}

	protected static function getKeys(): array
	{
		return [
			'name' => 'string',
			'description' => 'string',
			'equation' => 'string',
			'modifier' => 'string',
		];
	}
}
