<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class PostVotePost extends AbstractMapper
{

    public function findByGameId($post_vote)
    {
        return $this->getEntityRepository()->findBy(array('post_vote' => $post_vote));
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\PostVotePost');
        }

        return $this->er;
    }
}
