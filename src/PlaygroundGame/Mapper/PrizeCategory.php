<?php

namespace PlaygroundGame\Mapper;

use Doctrine\ORM\EntityManager;
use PlaygroundGame\Options\ModuleOptions;
use PlaygroundGame\Mapper\AbstractMapper;

class PrizeCategory extends AbstractMapper
{

    public function findByIdentifier($identifier)
    {
        return $this->getEntityRepository()->findOneBy(array('identifier' => $identifier));
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\PrizeCategory');
        }

        return $this->er;
    }
}
