<?php

namespace PlaygroundGame\Mapper;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PlaygroundGame\Options\ModuleOptions;

class Entry implements ServiceLocatorAwareInterface
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
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

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

    public function findBy($array)
    {
        return $this->getEntityRepository()->findBy($array);
    }

    public function countByGame(\PlaygroundGame\Entity\Game $game)
    {
        $query = $this->em->createQuery('SELECT COUNT(e.id) FROM PlaygroundGame\Entity\Entry e WHERE e.game = :game');
        $query->setParameter('game', $game);
        return $query->getSingleScalarResult();
    }

    public function draw($game, $userClass, $total)
    {
       /* 
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping;
        //$rsm->addEntityResult('\PlaygroundGame\Entity\Entry', 'e');
        $rsm->addEntityResult($userClass, 'u');
        $rsm->addFieldResult('u', 'user_id', 'id');
        $rsm->addFieldResult('u', 'username', 'username');
        $rsm->addFieldResult('u', 'lastname', 'lastname');
        $rsm->addFieldResult('u', 'firstname', 'firstname');
        $rsm->addFieldResult('u', 'email', 'email');
        //$rsm->addFieldResult('u', 'optin_partner', 'optin_partner');

        $query = $this->em->createNativeQuery(
            'SELECT distinct u.user_id, u.username, u.firstname, u.lastname, u.email, u.optin_partner FROM game_entry as e
            INNER JOIN user AS u ON e.user_id = u.user_id
            WHERE e.game_id = :game_id
            #AND e.winner = 1
            ORDER BY RAND()
            LIMIT ' . $total,
            $rsm
        );
        $query->setParameter('game_id', $game->getId());

        $result = $query->getResult();

        return $result;*/
        
        $sql ='SELECT u.user_id uid, u.username, u.firstname, u.lastname, u.email, u.optin_partner, e.created_at ecreated_at, e.updated_at eupdated_at, e.* FROM game_entry as e
            INNER JOIN user AS u ON e.user_id = u.user_id
            WHERE e.game_id = :game_id
            AND e.drawable = 1
            GROUP BY u.user_id
            ORDER BY RAND()
            LIMIT ' . $total;
        
        $rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder($this->em);
        $rsm->addRootEntityFromClassMetadata('\PlaygroundGame\Entity\Entry',  'e',  array('id' => 'id', 'created_at' => 'ecreated_at', 'updated_at' => 'eupdated_at'));
        $query = $this->em->createNativeQuery($sql,  $rsm);
        $query->setParameter('game_id', $game->getId());
        
        return $query->getResult();
    }

    public function queryByGame(\PlaygroundGame\Entity\Game $game)
    {
        $query = $this->em->createQuery('SELECT e FROM PlaygroundGame\Entity\Entry e WHERE e.game = :game');
        $query->setParameter('game', $game);
        return $query;
    }

    public function findByGameId($game)
    {
        return $this->getEntityRepository()->findBy(array('game' => $game));
    }

    /**
     * Get all the entries of the player except those offered as bonus
     *
     * @param unknown_type $game
     * @param unknown_type $user
     * @param unknown_type $limitScale
     */
    public function findLastEntriesBy($game, $user, $limitScale)
    {
        $now = new \DateTime("now");
        switch ($limitScale) {
            case 'always':
                $interval = 'P100Y';
                $now->sub(new \DateInterval($interval));
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            case 'day':
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            case 'week':
                $interval = 'P7D';
                $now->sub(new \DateInterval($interval));
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            case 'month':
                $interval = 'P1M';
                $now->sub(new \DateInterval($interval));
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            case 'year':
                $interval = 'P1Y';
                $now->sub(new \DateInterval($interval));
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            default:
                $interval = 'P100Y';
                $now->sub(new \DateInterval($interval));
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
        }

        $query = $this->em->createQuery('SELECT COUNT(e.id) FROM PlaygroundGame\Entity\Entry e WHERE e.user = :user AND e.game = :game AND (e.bonus = 0 OR e.bonus IS NULL) AND e.created_at >= :date');
        $query->setParameter('user', $user);
        $query->setParameter('game', $game);
        $query->setParameter('date', $dateLimit);

        $total = $query->getSingleScalarResult();

        return $total;
    }

    /**
     * get users with only one participation able to
     * replay the game in the timeframe (I except offered entries marked as bonus)
     *
     * @param unknown_type $games
     * @param unknown_type $limitScale
     */
    public function findPlayersWithOneEntryBy($game)
    {
        $now = new \DateTime("now");
        $limitScale = $game->getPlayLimitScale();
        switch ($limitScale) {
            case 'always':
                $interval = 'P100Y';
                $now->sub(new \DateInterval($interval));
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            case 'day':
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            case 'week':
                $interval = 'P7D';
                $now->sub(new \DateInterval($interval));
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            case 'month':
                $interval = 'P1M';
                $now->sub(new \DateInterval($interval));
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            case 'year':
                $interval = 'P1Y';
                $now->sub(new \DateInterval($interval));
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            default:
                $interval = 'P100Y';
            $now->sub(new \DateInterval($interval));
            $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
        }

        $query = $this->em->createQuery(
            'SELECT e, u FROM PlaygroundGame\Entity\Entry e
            JOIN e.user u
            WHERE e.game = :game
                AND (e.bonus = 0 OR e.bonus IS NULL)
            GROUP BY e.user
            HAVING COUNT(e.id) = 1
            AND e.created_at <= :date '
        );
        $query->setParameter('game', $game);
        $query->setParameter('date', $dateLimit);

        $result = $query->getResult();

        return $result;
    }

    /**
     * Compte les nombre de participations bonus
     * @param unknown_type $game
     * @param unknown_type $user
     */
    public function checkBonusEntry($game, $user)
    {
        $er = $this->getEntityRepository();

        $query = $this->em->createQuery('SELECT COUNT(e.id) FROM PlaygroundGame\Entity\Entry e WHERE e.user = :user AND e.game = :game AND (e.bonus = 0 OR e.bonus IS NULL)');
        $query->setParameter('user', $user);
        $query->setParameter('game', $game);
        $nbEntries = $query->getSingleScalarResult();

        $query = $this->em->createQuery('SELECT COUNT(e.id) FROM PlaygroundGame\Entity\Entry e WHERE e.user = :user AND e.game = :game AND e.bonus = 1');
        $query->setParameter('user', $user);
        $query->setParameter('game', $game);
        $nbBonusEntries = $query->getSingleScalarResult();

        if (($nbEntries - $nbBonusEntries) <= 0) {
            return false;
        }

        return true;
    }

    public function findOneBy($array=array(), $sortBy = array('updated_at' => 'desc'))
    {
        $er = $this->getEntityRepository();

        return $er->findOneBy($array, $sortBy);
    }

    public function findLastActiveEntryById($game, $user)
    {
        $er = $this->getEntityRepository();

        return $er->findOneBy(array('game' => $game , 'user'=> $user, 'active' => true), array('updated_at' => 'desc'));
    }

    /**
     * I remove the bonus entries
     *
     * @param unknown_type $game
     * @param unknown_type $user
     */
    public function findLastInactiveEntryById($game, $user)
    {
        $er = $this->getEntityRepository();

        return $er->findOneBy(array('game' => $game , 'user'=> $user, 'active' => false, 'bonus' => false), array('updated_at' => 'desc'));
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
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\Entry');
        }

        return $this->er;
    }
    
    /**
     * Set serviceManager instance
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return void
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Retrieve serviceManager instance
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
