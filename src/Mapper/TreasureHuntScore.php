<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class TreasureHuntScore extends AbstractMapper
{
    public function findByEntry($entry)
    {
        return $this->getEntityRepository()->findBy(array('entry' => $entry));
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\TreasureHuntScore');
        }

        return $this->er;
    }
}
