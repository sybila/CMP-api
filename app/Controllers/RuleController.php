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
 * API for managing Rules
 * Each rule has the following attributes
 * <json>
 * {
 *  "id": Numeric ID, unique among the rules
 *  "name": Name of the rule
 *  "description": Description of the rule
 *  "equation": Rule's equation in BCSL
 *  "modifier": String - mostly list of modifiers, separated by comma, but can be any string basically
 *  "active": Boolean value
 *  "classifications": List of classifications (objects with id and name properties)
 *  "organisms": List of organisms (objects with id and name properties)
 * }
 * </json>
 *
 * When saving (POST, PUT) rules, input for classifications and organisms is expected to be array of IDs.
 *
 * @ApiRoute(
 * 	"/rules[/<id>]",
 *  parameters={
 * 		"id"={
 * 			"requirement": "\d+"
 * 		}
 * 	},
 *  presenter="Rule",
 *  format="json"
 * )
 */
final class RuleController extends AbstractController
{
	use DataEndpoint;

	public function actionRead(int $id = 0)
	{
		$where = [];
		if ($id)
			$where = ['id' => $id];

		$this->payload->data = [];
		foreach ($this->fetchAll($where) as $row)
		{
			$row = (array)$row;

			$cq = $this->db->query("SELECT SQL_NO_CACHE c.id, c.name
										FROM ep_classification AS c
										INNER JOIN ep_reaction_classification AS rc ON rc.classificationId = c.id
										WHERE rc.reactionId = ? AND c.type = 'reaction'", $row['id']);
			$row['classifications'] = $cq->fetchAll();

			$oq = $this->db->query("SELECT SQL_NO_CACHE o.id, o.name
										FROM ep_organism AS o
										INNER JOIN ep_reaction_organism AS ro ON ro.organismId = o.id
										WHERE ro.reactionId = ?", $row['id']);
			$row['organisms'] = $oq->fetchAll();

			$this->payload->data[] = $row;
		}
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
	}

	public function actionDelete($id)
	{
		$this->db->beginTransaction();
		$this->db->query("DELETE FROM ep_reaction WHERE id = ?", $id);
		$this->db->query("DELETE FROM ep_reaction_classification WHERE reactionId = ?", $id);
		$this->db->query("DELETE FROM ep_reaction_organism WHERE reactionId = ?", $id);
		$this->db->commit();
	}

	protected function fetchAll(array $where = null): ResultSet
	{
		return $this->db->query("SELECT SQL_NO_CACHE ? FROM ep_reaction WHERE ?", self::getSqlKeys(true), $where);
	}

	protected static function getKeys(): array
	{
		return [
			'name' => 'string',
			'description' => 'string',
			'equation' => 'string',
			'modifier' => 'string',
			'active' => 'bool',
		];
	}
}
