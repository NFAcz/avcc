<?php

namespace Application\Bundle\FrontBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * RecordsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RecordsRepository extends EntityRepository
{

    public function findOrganizationRecords($organizationID)
    {
        $query = $this->getEntityManager()
        ->createQuery("SELECT r from ApplicationFrontBundle:Records r "
        . "JOIN r.user u "
        . "JOIN u.organizations o "
        . "WHERE o.id =  :organization");
        $query->setParameter('organization', $organizationID);

        return $query->getResult();
    }

    public function findAudioRecordById($id)
    {
        return $this->getEntityManager()->createQuery("SELECT r as record, ar as audio, m.name as mediaType, p.name as projectTitle"
        . " FROM ApplicationFrontBundle:Records r"
        . " JOIN ApplicationFrontBundle:MediaTypes m WITH r.mediaType = m.id"
        . " JOIN ApplicationFrontBundle:Projects p WITH r.project = p.id"
        . " JOIN ApplicationFrontBundle:AudioRecords ar WITH ar.record = r.id "
        . " Where r.id = $id"
        )
        ->getArrayResult();
    }

    public function findRecordsByType($typeRecordId, $typeId)
    {
        $where = "";
        $join = '';
        if ($typeId == 1) {
           $join =  "JOIN r.audioRecord a ";
           $where = "WHERE a.id =  :typeRecordId";
        } elseif ($typeId == 2) {
           $join =  "JOIN r.filmRecord f ";
           $where = "WHERE f.id =  :typeRecordId";
        } else {
           $join =  "JOIN r.videoRecord v ";
           $where = "WHERE v.id =  :typeRecordId";
        }
        $query = $this->getEntityManager()
        ->createQuery("SELECT r from ApplicationFrontBundle:Records r "
        . "JOIN r.user u "
        . "JOIN u.organizations o "
        . $join
//        . $join
        . $where);
        $query->setParameter('typeRecordId', $typeRecordId);

        return $query->getSingleResult();
    }

}
