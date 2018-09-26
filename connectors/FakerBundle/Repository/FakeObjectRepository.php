<?php

namespace Splash\Connectors\FakerBundle\Repository;

class FakeObjectRepository extends \Doctrine\ORM\EntityRepository
{
    
    public function getTypeCount($node, $type, $filter = Null) 
    {
        $QB = $this->createQueryBuilder("o");
        
        $QB
            ->select('COUNT(o.id)')
            ->where('o.type = :type')
            ->andWhere('o.node = :node')
            ->setParameter('type', $type)
            ->setParameter('node', $node);
        
        if ($filter) {
            $QB
              ->where('identifier = :filter')
              ->setParameter('filter', $filter);
        }

        return $QB->getQuery()->getSingleScalarResult();
    }
    
}
