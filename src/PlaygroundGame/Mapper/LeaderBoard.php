<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Entity\LeaderBoard as LeaderBoardEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DBALException;
use PlaygroundGame\Options\ModuleOptions;
use Zend\Stdlib\Hydrator\HydratorInterface;

class LeaderBoard
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
        $this->em      = $em;
        $this->options = $options;
    }

    public function findAll()
    {
        $er = $this->em->getRepository($this->options->getLeaderBoardEntityClass());

        return $er->findAll();
    }

    public function findBy($array)
    {
        $er = $this->em->getRepository($this->options->getLeaderBoardEntityClass());

        return $er->findBy($array);
    }

    public function findOneBy($array)
    {
        $er = $this->em->getRepository($this->options->getLeaderBoardEntityClass());

        return $er->findOneBy($array);
    }

    public function insert(LeaderBoardEntity $entity, $tableName = null, HydratorInterface $hydrator = null)
    {
        try {
            $entity = $this->persist($entity);
        } catch (DBALException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }

        return $entity;
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        return $this->persist($entity);
    }

    protected function persist($entity)
    {
        try {
            $this->em->persist($entity);
            $this->em->flush();
        } catch (DBALException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }

        return $entity;
    }

    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }
}
