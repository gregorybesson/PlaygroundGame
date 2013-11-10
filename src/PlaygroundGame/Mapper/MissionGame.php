<?php

namespace PlaygroundGame\Mapper;

use Doctrine\ORM\EntityManager;
use ZfcBase\Mapper\AbstractDbMapper;
use PlaygroundGame\Options\ModuleOptions;

class MissionGame
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
    * @param int $id id de la missionGame
    *
    * @return PlaygroundGame\Entity\MissionGame $missionGame
    */
    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    /**
    * findOneBy : recupere l'entite en fonction de filtres
    * @param array $filters tableau de filtres
    *
    * @return PlaygroundGame\Entity\MissionGame $missionGame
    */
    public function findOneBy($filters)
    {
         return $this->getEntityRepository()->findOneBy($filters);
    }

    /**
    * findBy : recupere des entites en fonction de filtre
    * @param array $array tableau de filtre
    *
    * @return collection $missionGames collection de PlaygroundGame\Entity\MissionGame
    */
    public function findBy($array)
    {
        return $this->getEntityRepository()->findBy($array);
    }

    /**
    * insert : insert en base une entité missionGame
    * @param PlaygroundGame\Entity\MissionGame $entity missionGame
    *
    * @return PlaygroundGame\Entity\MissionGame $missionGame
    */
    public function insert($entity)
    {
        return $this->persist($entity);
    }

    /**
    * insert : met a jour en base une entité missionGame
    * @param PlaygroundGame\Entity\MissionGame $entity missionGame
    *
    * @return PlaygroundGame\Entity\MissionGame $missionGame
    */
    public function update($entity)
    {
        return $this->persist($entity);
    }

    /**
    * insert : met a jour en base une entité missionGame et persiste en base
    * @param PlaygroundGame\Entity\MissionGame $entity missionGame
    *
    * @return PlaygroundGame\Entity\MissionGame $missionGame
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
    * @return collection $missionGames collection de PlaygroundGame\Entity\MissionGame
    */
    public function findAll()
    {
        return $this->getEntityRepository()->findAll();
    }

     /**
    * remove : supprimer une entite missionGame
    * @param PlaygroundGame\Entity\MissionGame $entity missionGame
    *
    */
    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
    * refresh : supprimer une entite missionGame
    * @param PlaygroundGame\Entity\MissionGame $entity missionGame
    *
    */
    public function refresh($entity)
    {
        $this->em->refresh($entity);
    }

    /**
    * getEntityRepository : recupere l'entite missionGame
    *
    * @return PlaygroundGame\Entity\MissionGame $missionGame
    */
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\MissionGame');
        }

        return $this->er;
    }
}
