<?php

namespace PlaygroundGame\Mapper;

use Doctrine\ORM\EntityManager;
use PlaygroundGame\Options\ModuleOptions;
use PlaygroundGame\Mapper\Game;

class Mission extends Game
{
    public function findByIdentifier($identifier)
    {
        return $this->getEntityRepository()->findOneBy(array('identifier' => $identifier));
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\Mission');
        }

        return $this->er;
    }
}
