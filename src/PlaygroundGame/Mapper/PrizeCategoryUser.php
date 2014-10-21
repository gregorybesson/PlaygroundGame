<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Entity\PrizeCategoryUser as PrizeCategoryUserEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DBALException;
use PlaygroundGame\Options\ModuleOptions;

class PrizeCategoryUser
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

    public function findAll()
    {
        $er = $this->getEntityRepository();

        return $er->findAll();
    }

    public function findBy($filter, $order = null, $limit = null, $offset = null)
    {
        return $this->getEntityRepository()->findBy($filter, $order, $limit, $offset);
    }

    public function findOneBy($array)
    {
        $er = $this->getEntityRepository();

        return $er->findOneBy($array);
    }

    public function removeAll($user)
    {
        $categories = $this->findBy(array('user' => $user));
        foreach ($categories as $category) {
            $this->em->remove($category);
        }
        $this->em->flush();
    }

    public function insert(PrizeCategoryUserEntity $entity)
    {
        try {
            $entity = $this->persist($entity);
        } catch (DBALException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }

        return $entity;
    }

    public function update($entity)
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

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\PrizeCategoryUser');
        }

        return $this->er;
    }
}
