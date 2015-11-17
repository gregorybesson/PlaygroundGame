<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class PrizeCategoryUser extends AbstractMapper
{

    public function removeAll($user)
    {
        $categories = $this->findBy(array('user' => $user));
        foreach ($categories as $category) {
            $this->em->remove($category);
        }
        $this->em->flush();
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\PrizeCategoryUser');
        }

        return $this->er;
    }
}
