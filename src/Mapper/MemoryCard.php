<?php
namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class MemoryCard extends AbstractMapper
{
    public function queryByGame($tradingcard)
    {
        $query = $this->em->createQuery(
            'SELECT mc FROM PlaygroundGame\Entity\MemoryCard mc
                WHERE mc.game = :game
                ORDER BY mc.id ASC'
        );
        $query->setParameter('game', $tradingcard);
        return $query;
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\MemoryCard');
        }

        return $this->er;
    }
}
