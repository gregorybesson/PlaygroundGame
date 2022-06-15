<?php
namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class CrosswordWord extends AbstractMapper
{
    public function queryByGame($word)
    {
        $query = $this->em->createQuery(
            'SELECT mc FROM PlaygroundGame\Entity\CrosswordWord mc
                WHERE mc.game = :game
                ORDER BY mc.id ASC'
        );
        $query->setParameter('game', $word);
        return $query;
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\CrosswordWord');
        }

        return $this->er;
    }
}
