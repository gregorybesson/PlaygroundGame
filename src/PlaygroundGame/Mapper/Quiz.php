<?php

namespace PlaygroundGame\Mapper;

use Doctrine\ORM\EntityManager;
use PlaygroundGame\Options\ModuleOptions;

class Quiz extends Game
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $er;

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
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\Quiz');
        }

        return $this->er;
    }
}
