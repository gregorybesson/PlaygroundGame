<?php

namespace PlaygroundGame\Mapper;

use Doctrine\ORM\EntityManager;
use ZfcBase\Mapper\AbstractDbMapper;
use PlaygroundGame\Options\ModuleOptions;

class Mission
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
     * @var \PlaygroundDesign\Options\ModuleOptions
     */
    protected $options;


    /**
    * __construct
    * @param Doctrine\ORM\EntityManager $em
    * @param PlaygroundDesign\Options\ModuleOptions $options
    *
    */
    public function __construct(EntityManager $em, ModuleOptions $options)
    {
        $this->em      = $em;
        $this->options = $options;
    }

    /**
    * findById : recupere l'entite en fonction de son id
    * @param int $id id de mission
    *
    * @return PlaygroundGame\Entity\Mission $mission
    */
    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    /**
    * findOneBy : recupere l'entite en fonction de filtres
    * @param array $filters tableau de filtres
    *
    * @return PlaygroundGame\Entity\Mission $mission
    */
    public function findOneBy($filters)
    {
         return $this->getEntityRepository()->findOneBy($filters);
    }

    /**
    * findBy : recupere des entites en fonction de filtre
    * @param array $array tableau de filtre
    *
    * @return collection $missions collection de PlaygroundGame\Entity\Mission 
    */
    public function findBy($array)
    {
        return $this->getEntityRepository()->findBy($array);
    }

    /**
    * insert : insert en base une entité mission
    * @param PlaygroundGame\Entity\Mission  $entity mission
    *
    * @return PlaygroundGame\Entity\Mission  $mission
    */
    public function insert($entity)
    {
        return $this->persist($entity);
    }

    /**
    * update : met a jour en base une entité mission
    * @param PlaygroundGame\Entity\Mission $entity mission
    *
    * @return PlaygroundGame\Entity\Mission $mission
    */
    public function update($entity)
    {
        return $this->persist($entity);
    }

    /**
    * persist : met a jour en base une entité mission et persiste en base
    * @param PlaygroundGame\Entity\Mission $entity mission
    *
    * @return PlaygroundGame\Entity\Mission $mission
    */
    public function persist($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    /**
    * findAll : recupere toutes les entites 
    *
    * @return collection $missions collections de PlaygroundGame\Entity\Mission 
    */
    public function findAll()
    {
        return $this->getEntityRepository()->findAll();
    }

     /**
    * remove : supprimer une entite mission
    * @param PlaygroundGame\Entity\Mission  $entity mission
    *
    */
    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
    * refresh : supprimer une entite mission
    * @param PlaygroundGame\Entity\Mission  $entity mission
    *
    */
    public function refresh($entity)
    {
        $this->em->refresh($entity);
    }

    /**
    * getEntityRepository : recupere l'entite mission
    *
    * @return PlaygroundGame\Entity\Mission mission
    */
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\Mission');
        }

        return $this->er;
    }
}
