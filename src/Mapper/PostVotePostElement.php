<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class PostVotePostElement extends AbstractMapper
{
    public function removeAll($post)
    {
        $elements = $this->findBy(array('post' => $post));
        foreach ($elements as $element) {
            $this->em->remove($element);
        }
        $this->em->flush();
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\PostVotePostElement');
        }

        return $this->er;
    }
}
