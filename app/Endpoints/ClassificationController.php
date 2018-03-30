<?php

namespace App\Controllers;

use App\Entity\Classification;
use App\Exceptions\
{
	ApiException, InternalErrorException, NonExistingObjectException
};
use App\Helpers\ArgumentParser;
use Doctrine\ORM\ORMException;
use Slim\Http\{Request, Response};

final class ClassificationController extends ReadableController
{
	use SortableController;

	protected static function getAllowedSort(): array
	{
		return ['name' => 'name', 'type' => 'type'];
	}

	public function read(Request $request, Response $response, ArgumentParser $args)
	{
		$data = [];

		foreach ($this->orm->getRepository(Classification::class)->findBy([], self::getSort($args)) as $ent)
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
