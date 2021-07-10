<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class TreasureHuntPuzzle extends AbstractMapper
{
    public function findByGameId($treasurehunt, $sortArray = array())
    {
        return $this->getEntityRepository()->findBy(array('treasurehunt' => $treasurehunt), $sortArray);
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\TreasureHuntPuzzle');
        }

        return $this->er;
    }
}
