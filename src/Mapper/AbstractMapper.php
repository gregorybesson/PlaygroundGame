<?php

namespace PlaygroundGame\Mapper;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DBALException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PlaygroundGame\Options\ModuleOptions;

abstract class AbstractMapper
{
    abstract protected function getEntityRepository();

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $er;
    
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var \PlaygroundGame\Options\ModuleOptions
     */
    protected $options;

    public function __construct(EntityManager $em, ModuleOptions $options, ServiceLocatorInterface $locator)
    {
        $this->em      = $em;
        $this->options = $options;
        $this->serviceLocator = $locator;
    }

    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    public function findBy($filter, $order = null, $limit = null, $offset = null)
    {
        return $this->getEntityRepository()->findBy($filter, $order, $limit, $offset);
    }

    public function findOneBy($array = array(), $sortBy = array())
    {
        $er = $this->getEntityRepository();

        return $er->findOneBy($array, $sortBy);
    }

    public function findAll()
    {
        return $this->getEntityRepository()->findAll();
    }

    public function update($entity)
    {
        return $this->persist($entity);
    }

    public function insert($entity)
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
        } catch (\Exception $e) {
            throw $e;
        }

        return $entity;
    }

    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function removeAll($entities)
    {
        foreach ($entities as $entity) {
            $this->em->remove($entity);
        }
        $this->em->flush();
    }
}
