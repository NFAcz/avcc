<?php
/**
 * AVCC
 * 
 * @category AVCC
 * @package  Application
 * @author   Nouman Tayyab <nouman@weareavp.com>
 * @author   Rimsha Khalid <rimsha@weareavp.com>
 * @license  AGPLv3 http://www.gnu.org/licenses/agpl-3.0.txt
 * @copyright Audio Visual Preservation Solutions, Inc
 * @link     http://avcc.weareavp.com
 */
namespace Application\Bundle\FrontBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * MediaTypesRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MediaTypesRepository extends EntityRepository
{
    public function getAllAsArray()
    {
        $names = $this->getEntityManager()->createQuery('SELECT mediaTypes.name'
                . ' from ApplicationFrontBundle:MediaTypes mediaTypes'
                )->getScalarResult();
        $mediatypes = array_map("current",$names);

        return $mediatypes;
    }
}
