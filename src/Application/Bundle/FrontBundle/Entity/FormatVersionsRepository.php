<?php

namespace Application\Bundle\FrontBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * FormatVersionsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FormatVersionsRepository extends EntityRepository
{
    public function getAllAsArray()
    {
        $names = $this->getEntityManager()->createQuery('SELECT distinct(formatVersions.name)'
                . ' from ApplicationFrontBundle:FormatVersions formatVersions'
                )->getScalarResult();
        $fv = array_map("current",$names);

        return $fv;
    }
}
