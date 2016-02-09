<?php

namespace PlaygroundGame\Mapper;

class TradingCard extends Game
{

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\TradingCard');
        }

        return $this->er;
    }
}
