<?php

namespace App\Controllers;

use App\Entity\{Attribute,
    IdentifiedObject,
    Repositories\AttributeRepository,
    Repositories\IEndpointRepository,
    Repositories\PhysicalQuantityRepository,
    Repositories\UnitRepository,
    Repositories\UnitsAllRepository};
use App\Exceptions\{
    DependentResourcesBoundException,
    MissingRequiredKeyException
};
use App\Helpers\ArgumentParser;
use Slim\Container;
use Slim\Http\{
    Request, Response
};
use Symfony\Component\Validator\Constraints as Assert;


final class ConvertUnitsController extends AbstractController
{
    /** @var AttributeRepository */
    private $attributeRepository;
    /** @var UnitsAllRepository */
    private $unitAllRepository;

    public function __construct(Container $v)
    {
        $this->attributeRepository = $v->get(AttributeRepository::class);
        $this->unitAllRepository = $v->get(UnitsAllRepository::class);
    }

    public function getAttributesUnits($attributeId){}

    public function convertUnit(Request $request, Response $response, $args){
        $unitFromId = $args['unitFrom'];
        $unitToId = $args['unitTo'];
        /*$unitFrom = $this->unitRepository->get($unitFromId);
        $unitTo = $this->unitRepository->get($unitToId);
        if($unitFrom->getQuantityId() != $unitTo->getQuantityId()){
            return self::formatError($response, 500, "Units ar not in same quantity.");;
        }
        return self::formatOk($response, ['coefficient' => ($unitTo->getCoefficient() / $unitFrom->getCoefficient())]);
        dump($this->attributeRepository->get($attributeId)); exit;*/
    }
}