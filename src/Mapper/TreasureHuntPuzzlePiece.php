<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class TreasureHuntPuzzlePiece extends AbstractMapper
{
    public function findByGameId($treasurehuntPuzzle, $sortArray = array())
    {
        return $this->getEntityRepository()->findBy(array('treasurehuntPuzzle' => $treasurehuntPuzzle), $sortArray);
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\TreasureHuntPuzzlePiece');
        }

        return $this->er;
    }
}
