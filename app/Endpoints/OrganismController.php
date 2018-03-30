<?php

namespace App\Controllers;

use App\Entity\Organism;
use App\Exceptions\
{
	ApiException, InternalErrorException, NonExistingObjectException
};
use App\Helpers\ArgumentParser;
use Doctrine\ORM\ORMException;
use Slim\Http\{Request, Response};

final class OrganismController extends ReadableController
{
	use SortableController;

	protected static function getAllowedSort(): array
	{
		return ['name' => 'name'];
	}

	public function read(Request $request, Response $response, ArgumentParser $args)
	{
		$data = [];

		foreach ($this->orm->getRepository(Organism::class)->findBy([], self::getSort($args)) as $ent)
			$data[] = $this->getData($ent);

		return self::formatOk($response, $data);
	}

	/**
	 * @param int $id
	 * @return Organism
	 * @throws ApiException
	 */
	protected function getEntity(int $id)
	{
		try {
			$ent = $this->orm->find(Organism::class, $id);
			if (!$ent)
				throw new NonExistingObjectException($id, 'organism');
		}
		catch (ORMException $e) {
			throw new InternalErrorException('Failed getting organism ID ' . $id, $e);
		}

		return $ent;
	}

	/**
	 * @param Organism $entity
	 * @return array
	 */
	protected function getData($entity): array
	{
		return [
			'id' => $entity->getId(),
			'name' => $entity->getName(),
		];
	}

	/**
	 * @param Organism $entity
	 * @return array
	 */
	protected function getSingleData($entity): array
	{
		return [];
	}
}
