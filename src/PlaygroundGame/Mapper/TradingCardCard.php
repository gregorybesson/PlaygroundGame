<?php
namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class TradingCardCard extends AbstractMapper
{

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\TradingCardCard');
        }

        return $this->er;
    }
}
