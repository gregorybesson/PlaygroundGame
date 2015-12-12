<?php
namespace PlaygroundGame\Mapper;

use Doctrine\ORM\EntityManager;
use PlaygroundGame\Options\ModuleOptions;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZfcBase\EventManager\EventProvider;
use PlaygroundGame\Mapper\AbstractMapper;

class Invitation extends AbstractMapper
{
    public function findByUser($user)
    {
        return $this->getEntityRepository()->findBy(array('user'=>$user));
    }

    public function findByRequestKey($key)
    {
        return $this->getEntityRepository()->findBy(array('requestKey'=>$key));
    }

    /**
     * @param string $id
     * @return \Heineken\Entity\Invitation
     */
    public function findByGame($game)
    {
        return $this->getEntityRepository()->findBy(array('game'=>$game));
    }

    public function getEntityRepository()
    {
        return $this->em->getRepository('PlaygroundGame\Entity\Invitation');
    }
}
