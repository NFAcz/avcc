<?php

namespace Application\Bundle\FrontBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * RecordingSpeedRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RecordingSpeedRepository extends EntityRepository
{
    public function getAllAsArray()
    {
        $names= $this->getEntityManager()->createQuery('SELECT distinct(recordingSpeed.name)'
                . ' from ApplicationFrontBundle:RecordingSpeed recordingSpeed'
                )->getScalarResult();
        $rs = array_map("current",$names);

        return $rs;
    }
}
