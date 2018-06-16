<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class Game extends AbstractMapper
{
    public function findByIdentifier($identifier)
    {
        return $this->getEntityRepository()->findOneBy(array('identifier' => $identifier));
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository($this->options->getGameEntityClass());
        }

        return $this->er;
    }
}
