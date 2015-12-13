<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class MissionGame extends AbstractMapper
{
    /**
    * refresh : supprimer une entite missionGame
    * @param PlaygroundGame\Entity\MissionGame $entity missionGame
    *
    */
    public function refresh($entity)
    {
        $this->em->refresh($entity);
    }

    /**
    * getEntityRepository : recupere l'entite missionGame
    *
    * @return \Doctrine\ORM\EntityRepository $missionGame
    */
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\MissionGame');
        }

        return $this->er;
    }

    public function getNextGame($mission, $currentPosition)
    {
        if (!is_object($mission)) {
            return false;
        }

        if (!is_integer($currentPosition)) {
            return false;
        }


        $select = "SELECT mg.id";
        $from = "FROM PlaygroundGame\Entity\MissionGame mg";
        $where = "WHERE mg.mission = :mission";
        $where .= " AND mg.position > :currentPosition";
        $order = "ORDER BY mg.position ASC";

        $query = $select.' '.$from.' '.$where.' '.$order;

        $query = $this->em->createQuery($query);


        $query->setParameter('mission', (int) $mission->getId());
        $query->setParameter('currentPosition', (int) $currentPosition);
        $query->setMaxResults(1);

        $nextGame =  $query->getResult();
        
        if(empty($nextGame)) {
            return false;
        }

        return $this->findById($nextGame[0]['id']);
    }
}
