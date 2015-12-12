<?php
namespace PlaygroundGame\Mapper;

use Doctrine\ORM\EntityManager;
use PlaygroundGame\Options\ModuleOptions;
use PlaygroundUser\Entity\EmailVerification as Model;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZfcBase\EventManager\EventProvider;

class Invitation
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \PlaygroundUser\Options\ModuleOptions
     */
    protected $options;

    public function __construct(EntityManager $em, ModuleOptions $options)
    {
        $this->em      = $em;
        $this->options = $options;
    }

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

    /**
     * @param string $id
     * @return \Heineken\Entity\Invitation
     */
    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    public function findBy($filter, $order = null, $limit = null, $offset = null)
    {
        return $this->getEntityRepository()->findBy($filter, $order, $limit, $offset);
    }
    
    /**
     * @return \Heineken\Entity\Invitation
     */
    public function findOneBy($array, $sortBy = array())
    {
        return $this->getEntityRepository()->findOneBy($array, $sortBy);
    }

    /**
     * @return \Heineken\Entity\Invitation
     */
    public function insert($entity, $tableName = null, \Zend\Stdlib\Hydrator\HydratorInterface $hydrator = null)
    {
        return $this->persist($entity);
    }

    /**
     * @return \Heineken\Entity\Invitation
     */
    public function update($entity, $tableName = null, \Zend\Stdlib\Hydrator\HydratorInterface $hydrator = null)
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
        return $this->findBy(array(), array('createdAt' => 'DESC'));
    }

    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function getEntityRepository()
    {
        return $this->em->getRepository('PlaygroundGame\Entity\Invitation');
    }
}
