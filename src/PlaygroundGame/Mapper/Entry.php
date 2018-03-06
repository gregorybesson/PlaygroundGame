<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class Entry extends AbstractMapper
{
    public function countByGame(\PlaygroundGame\Entity\Game $game)
    {
        $query = $this->em->createQuery('SELECT COUNT(e.id) FROM PlaygroundGame\Entity\Entry e WHERE e.game = :game');
        $query->setParameter('game', $game);
        return $query->getSingleScalarResult();
    }

    public function draw($game, $userClass, $total)
    {
        $sql ='
            SELECT 
                u.user_id uid, 
                u.username, 
                u.firstname, 
                u.lastname, 
                u.email, 
                u.optin_partner, 
                e.created_at ecreated_at, 
                e.updated_at eupdated_at, 
                e.* 
            FROM game_entry as e
            INNER JOIN user AS u ON e.user_id = u.user_id
            WHERE e.game_id = :game_id AND e.drawable = 1
            GROUP BY u.user_id
            ORDER BY RAND()
            LIMIT :total
        ';
        
        $rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder($this->em);
        $rsm->addRootEntityFromClassMetadata(
            '\PlaygroundGame\Entity\Entry',
            'e',
            array('id' => 'id', 'created_at' => 'ecreated_at', 'updated_at' => 'eupdated_at')
        );
        $query = $this->em->createNativeQuery($sql, $rsm);
        $query->setParameter('game_id', $game->getId());
        $query->setParameter('total', $total);
        
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
     */
    public function findLastEntriesByUser($game, $user, $dateLimit)
    {
        $query = $this->em->createQuery(
            'SELECT COUNT(e.id) FROM PlaygroundGame\Entity\Entry e 
             WHERE e.user = :user AND e.game = :game AND (e.bonus = 0 OR e.bonus IS NULL) AND e.created_at >= :date'
        );
        $query->setParameter('user', $user);
        $query->setParameter('game', $game);
        $query->setParameter('date', $dateLimit);

        $total = $query->getSingleScalarResult();

        return $total;
    }
    
    public function findLastEntriesByAnonymousIdentifier($game, $anonymousIdentifier, $dateLimit)
    {
        $query = $this->em->createQuery(
            'SELECT COUNT(e.id) FROM PlaygroundGame\Entity\Entry e 
             WHERE e.anonymousIdentifier = :anonymousIdentifier AND e.game = :game 
             AND (e.bonus = 0 OR e.bonus IS NULL) AND e.created_at >= :date'
        );
        $query->setParameter('anonymousIdentifier', $anonymousIdentifier);
        $query->setParameter('game', $game);
        $query->setParameter('date', $dateLimit);
    
        $total = $query->getSingleScalarResult();

        return $total;
    }

    public function findLastEntriesByIp($game, $ip, $dateLimit)
    {
        $query = $this->em->createQuery(
            'SELECT COUNT(e.id) FROM PlaygroundGame\Entity\Entry e 
             WHERE e.ip = :ip AND e.game = :game AND (e.bonus = 0 OR e.bonus IS NULL) AND e.created_at >= :date'
        );
        $query->setParameter('ip', $ip);
        $query->setParameter('game', $game);
        $query->setParameter('date', $dateLimit);
    
        $total = $query->getSingleScalarResult();
    
        return $total;
    }

    /**
     * get users with only one participation able to
     * replay the game in the timeframe (I except offered entries marked as bonus)
     *
     * @param unknown_type $game
     */
    public function findPlayersWithOneEntryBy($game, $dateLimit)
    {
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
        $query = $this->em->createQuery(
            'SELECT COUNT(e.id) FROM PlaygroundGame\Entity\Entry e 
             WHERE e.user = :user AND e.game = :game AND (e.bonus = 0 OR e.bonus IS NULL)'
        );
        $query->setParameter('user', $user);
        $query->setParameter('game', $game);
        $nbEntries = $query->getSingleScalarResult();

        $query = $this->em->createQuery(
            'SELECT COUNT(e.id) FROM PlaygroundGame\Entity\Entry e 
             WHERE e.user = :user AND e.game = :game AND e.bonus = 1'
        );
        $query->setParameter('user', $user);
        $query->setParameter('game', $game);
        $nbBonusEntries = $query->getSingleScalarResult();

        if (($nbEntries - $nbBonusEntries) <= 0) {
            return false;
        }

        return true;
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\Entry');
        }

        return $this->er;
    }
}
