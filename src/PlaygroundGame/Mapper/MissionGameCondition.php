<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class MissionGameCondition extends AbstractMapper
{
    /**
    * refresh : supprimer une entite missionGameCondition
    * @param PlaygroundGame\Entity\MissionGameCondition $entity missionGameCondition
    *
    */
    public function refresh($entity)
    {
        $this->em->refresh($entity);
    }

    /**
    * getEntityRepository : recupere l'entite missionGameCondition
    *
    * @return \Doctrine\ORM\EntityRepository $missionGameCondition
    */
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\MissionGameCondition');
        }

        return $this->er;
    }
}
