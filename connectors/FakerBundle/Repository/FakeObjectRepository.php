<?php

namespace Splash\Connectors\FakerBundle\Repository;

class FakeObjectRepository extends \Doctrine\ORM\EntityRepository
{
    
    public function getTypeCount($type, $filter = Null) 
    {
        $QB = $this->createQueryBuilder("o");
        
        $QB
            ->select('COUNT(o.id)')
            ->where('o.type = :type')
            ->setParameter('type', $type)
            ;
        
        if ($filter) {
            $QB
              ->where('identifier = :filter')
              ->setParameter('filter', $filter);
        }

        return $QB->getQuery()->getSingleScalarResult();
    }
    
}
