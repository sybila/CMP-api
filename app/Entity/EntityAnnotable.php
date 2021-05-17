<?php

use App\Entity\AnnotationToResource;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;

/**
 * Trait EntityAnnotable
 * @author Radoslav Doktor & Marek Havlik
 */
trait EntityAnnotable
{
    /**
     * @param EntityManager $orm
     * @return Collection
     */
    public function getAnnotations(EntityManager &$orm)
    {
        return $orm->getRepository(AnnotationToResource::class)
            ->matching(Criteria::create()
                ->where(Criteria::expr()->eq('resourceId', $this->getId()))
                ->andWhere(Criteria::expr()->eq('resourceType', get_class())))
            ->map(function (AnnotationToResource $annLink){
                return $annLink->getAnnotation();
            });
    }
}