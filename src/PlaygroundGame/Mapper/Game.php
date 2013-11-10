<?php

namespace PlaygroundGame\Mapper;

use Doctrine\ORM\EntityManager;
use PlaygroundGame\Options\ModuleOptions;

class Game
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

    public function findByIdentifier($identifier)
    {
        return $this->getEntityRepository()->findOneBy(array('identifier' => $identifier));
    }

    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    public function findBy($array, $sort)
    {
        return $this->getEntityRepository()->findBy($array, $sort);
    }

    public function insert($entity, $tableName = NULL, Zend\Stdlib\Hydrator\HydratorInterface $hydrator = NULL)
    {
        return $this->persist($entity);
    }

    public function update($entity, $tableName = NULL, Zend\Stdlib\Hydrator\HydratorInterface $hydrator = NULL)
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
        return $this->findBy(array(),array('createdAt' => 'DESC'));
    }

    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository($this->options->getGameEntityClass());
        }

        return $this->er;
    }
}
