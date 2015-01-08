<?php

namespace Application\Bundle\FrontBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * SoundsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SoundsRepository extends EntityRepository
{
    public function getAllAsArray()
    {
        $names = $this->getEntityManager()->createQuery('SELECT distinct(sounds.name)'
                . ' from ApplicationFrontBundle:Sounds sounds'
                )->getScalarResult();
        $sounds = array_map("current",$names);

        return $sounds;
    }
}
