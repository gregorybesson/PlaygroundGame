<?php

namespace PlaygroundGame\Mapper;

use Doctrine\ORM\EntityManager;
use PlaygroundGame\Options\ModuleOptions;

class QuizQuestion
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
        $this->em      = $em;
        $this->options = $options;
    }

    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    public function findByGameId($quiz)
    {
        return $this->getEntityRepository()->findBy(array('quiz' => $quiz));
    }

    public function insert($entity)
    {
        return $this->persist($entity);
    }

    public function update($entity)
    {
        return $this->persist($entity);
    }

    protected function persist($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    public function findAll()
    {
        return $this->getEntityRepository()->findAll();
    }

    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\QuizQuestion');
        }

        return $this->er;
    }
}
