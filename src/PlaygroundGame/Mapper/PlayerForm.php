<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class PlayerForm extends AbstractMapper
{

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\PlayerForm');
        }

        return $this->er;
    }
}
