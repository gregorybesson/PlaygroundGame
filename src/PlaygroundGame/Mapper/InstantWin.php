<?php

namespace PlaygroundGame\Mapper;

use Doctrine\ORM\EntityManager;
use PlaygroundGame\Options\ModuleOptions;

class InstantWin extends Game
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \PlaygroundGame\Options\ModuleOptions
     */
    protected $options;

    public function __construct(EntityManager $em, ModuleOptions $options)
    {
        parent::__construct($em, $options);
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\InstantWin');
        }

        return $this->er;
    }
}
