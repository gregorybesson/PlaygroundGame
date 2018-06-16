<?php
namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class Invitation extends AbstractMapper
{
    public function findByUser($user)
    {
        return $this->getEntityRepository()->findBy(array('user'=>$user));
    }

    public function findByHost($user)
    {
        return $this->getEntityRepository()->findBy(array('host'=>$user));
    }

    public function findByRequestKey($key)
    {
        return $this->getEntityRepository()->findBy(array('requestKey'=>$key));
    }

    /**
     * @return \Heineken\Entity\Invitation
     */
    public function findByGame($game)
    {
        return $this->getEntityRepository()->findBy(array('game'=>$game));
    }

    public function getEntityRepository()
    {
        return $this->em->getRepository('PlaygroundGame\Entity\Invitation');
    }

    public function queryByGame($game)
    {
        $query = $this->em->createQuery(
            'SELECT i FROM PlaygroundGame\Entity\Invitation i
                WHERE i.game = :game
                ORDER BY i.requestKey ASC'
        );
        $query->setParameter('game', $game);
        return $query;
    }
}
