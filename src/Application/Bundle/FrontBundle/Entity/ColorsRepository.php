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
 * ColorsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ColorsRepository extends EntityRepository
{
    public function getAllAsArray()
    {
        $names = $this->getEntityManager()->createQuery('SELECT colors.name'
                . ' from ApplicationFrontBundle:Colors colors'
                )->getScalarResult();
        $colors = array_map("current",$names);

        return $colors;
    }
}
