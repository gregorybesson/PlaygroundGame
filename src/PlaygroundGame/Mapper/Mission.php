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
    * @param int $id id du leaderBoardType
    *
    * @return PlaygroundReward\Entity\LeaderBoardType $leaderBoardType
    */
    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    /**
    * findOneBy : recupere l'entite en fonction de filtres
    * @param array $filters tableau de filtres
    *
    * @return PlaygroundReward\Entity\LeaderBoardType $leaderBoardType
    */
    public function findOneBy($filters)
    {
         return $this->getEntityRepository()->findOneBy($filters);
    }

    /**
    * findBy : recupere des entites en fonction de filtre
    * @param array $array tableau de filtre
    *
    * @return collection $leaderBoardTypes collection de PlaygroundReward\Entity\LeaderBoardType
    */
    public function findBy($array)
    {
        return $this->getEntityRepository()->findBy($array);
    }

    /**
    * insert : insert en base une entité leaderBoardType
    * @param PlaygroundReward\Entity\LeaderBoardType $entity leaderBoardType
    *
    * @return PlaygroundReward\Entity\LeaderBoardType $leaderBoardType
    */
    public function insert($entity)
    {
        return $this->persist($entity);
    }

    /**
    * insert : met a jour en base une entité leaderBoardType
    * @param PlaygroundReward\Entity\LeaderBoardType $entity leaderBoardType
    *
    * @return PlaygroundReward\Entity\LeaderBoardType $leaderBoardType
    */
    public function update($entity)
    {
        return $this->persist($entity);
    }

    /**
    * insert : met a jour en base une entité leaderBoardType et persiste en base
    * @param PlaygroundDesign\Entity\Theme $entity leaderBoardType
    *
    * @return PlaygroundReward\Entity\LeaderBoardType $leaderBoardType
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
    * @return collection $leaderBoardTypes collection de PlaygroundReward\Entity\LeaderBoardType
    */
    public function findAll()
    {
        return $this->getEntityRepository()->findAll();
    }

     /**
    * remove : supprimer une entite theme
    * @param PlaygroundReward\Entity\LeaderBoardType $entity leaderBoardType
    *
    */
    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
    * refresh : supprimer une entite theme
    * @param PlaygroundReward\Entity\LeaderBoardType $entity leaderBoardType
    *
    */
    public function refresh($entity)
    {
        $this->em->refresh($entity);
    }

    /**
    * getEntityRepository : recupere l'entite leaderBoardType
    *
    * @return PlaygroundReward\Entity\LeaderBoardType $leaderBoardType
    */
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\Mission');
        }

        return $this->er;
    }
}
