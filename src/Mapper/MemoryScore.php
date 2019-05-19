<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class MemoryScore extends AbstractMapper
{
    public function findByEntry($entry)
    {
        return $this->getEntityRepository()->findBy(array('entry' => $entry));
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\MemoryScore');
        }

        return $this->er;
    }
}
