<?php

namespace App\Controllers;

use App\Entity\Classification;
use App\Entity\EntityClassification;
use App\Entity\RuleClassification;
use App\Exceptions\
{
	ApiException, InternalErrorException, InvalidArgumentException, NonExistingObjectException
};
use App\Helpers\ArgumentParser;
use Doctrine\ORM\ORMException;
use Slim\Http\{Request, Response};

final class ClassificationController extends ReadableController
{
	use SortableController;

	protected static function getAllowedSort(): array
	{
		return ['id', 'name'];
	}

	public function read(Request $request, Response $response, ArgumentParser $args)
	{
		$data = [];
		$className = Classification::class;
		if ($args->hasKey('type'))
		{
			$type = $args->getString('type');
			if ($type === 'entity')
				$className = EntityClassification::class;
			elseif ($type === 'rule')
				$className = RuleClassification::class;
			else
				throw new InvalidArgumentException('type', $type);
		}

		foreach ($this->orm->getRepository($className)->findBy([], self::getSort($args)) as $ent)
			$data[] = $this->getData($ent);

		return self::formatOk($response, $data);
	}

	/**
	 * @param int $id
	 * @return Classification
	 * @throws ApiException
	 */
	protected function getEntity(int $id)
	{
		try {
			$ent = $this->orm->find(Classification::class, $id);
			if (!$ent)
				throw new NonExistingObjectException($id, 'classification');
		}
		catch (ORMException $e) {
			throw new InternalErrorException('Failed getting classification ID ' . $id, $e);
		}

		return $ent;
	}

	/**
	 * @param Classification $entity
	 * @return array
	 */
	protected function getData($entity): array
	{
		return [
			'id' => $entity->getId(),
			'name' => $entity->getName(),
			'type' => Classification::$classToType[get_class($entity)],
		];
	}

	/**
	 * @param Classification $entity
	 * @return array
	 */
	protected function getSingleData($entity): array
	{
		return [];
	}
}
