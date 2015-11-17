<?php

namespace PlaygroundGame\Mapper;

use Doctrine\ORM\EntityManager;
use PlaygroundGame\Options\ModuleOptions;
use PlaygroundGame\Mapper\AbstractMapper;

class PostVoteVote extends AbstractMapper
{

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\PostVoteVote');
        }

        return $this->er;
    }
}
