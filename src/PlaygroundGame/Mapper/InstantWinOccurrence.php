<?php

namespace PlaygroundGame\Mapper;

use Doctrine\ORM\EntityManager;
use PlaygroundGame\Options\ModuleOptions;

class InstantWinOccurrence
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

    public function findByEntry($entry)
    {
    	return $this->getEntityRepository()->findOneBy(array('entry' => $entry));
    }

    public function findByGameId($instant_win, $sortArray = array())
    {
        return $this->getEntityRepository()->findBy(array('instantwin' => $instant_win), $sortArray);
    }

    public function queryByGame($instant_win, $sortArray = array())
    {
        $query = $this->em->createQuery(
            'SELECT i FROM PlaygroundGame\Entity\InstantWinOccurrence i
                WHERE i.instantwin = :game
            '.( ! empty($sortArray) ? 'ORDER BY i.'.key($sortArray).' '.current($sortArray) : '' )
        );
        $query->setParameter('game', $instant_win);
        return $query;
    }

    public function findBy($array = array(), $sortArray = array())
    {
        return $this->getEntityRepository()->findBy($array, $sortArray);
    }

    /**
     *  DEPRECATED : The $user is replaced by its entry from now on
     */
    public function checkInstantWinByGameId($instant_win, $user, $entry)
    {
        $now = new \DateTime("now");
        $now = $now->format('Y-m-d H:i:s');

        $query = $this->em->createQuery(
                'SELECT i FROM PlaygroundGame\Entity\InstantWinOccurrence i
                WHERE i.instantwin = :game
                AND i.active = 1
                AND i.value <= :now
                ORDER BY i.value DESC
                '
        );
        $query->setParameter('game', $instant_win);
        $query->setParameter('now', $now);
        $query->setMaxResults(1);
        $result = $query->getResult();

        if (count($result) == 1) {
            $winOccurrence = $result[0];
            $winOccurrence->setUser($user);
            $winOccurrence->setEntry($entry);
            $winOccurrence->setActive(0);
            $this->update($winOccurrence);

            return $winOccurrence;
        } else {
            return null;
        }
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
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\InstantWinOccurrence');
        }

        return $this->er;
    }

    public function assertNoOther($instantwin, $value){
        $query = $this->em->createQuery(
                'SELECT i FROM PlaygroundGame\Entity\InstantWinOccurrence i
                WHERE i.instantwin = :game
                AND i.value = :value'
        );
        $query->setParameter('game', $instantwin);
        $query->setParameter('value', $value);
        if ($query->getResult())
            return false;
        return true;
    }
}
