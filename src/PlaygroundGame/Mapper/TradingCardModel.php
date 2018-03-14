<?php
namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class TradingCardModel extends AbstractMapper
{
    public function queryByGame($tradingcard)
    {
        $query = $this->em->createQuery(
            'SELECT tcm FROM PlaygroundGame\Entity\TradingCardModel tcm
                WHERE tcm.game = :game
                ORDER BY tcm.id ASC'
        );
        $query->setParameter('game', $tradingcard);
        return $query;
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\TradingCardModel');
        }

        return $this->er;
    }
}
