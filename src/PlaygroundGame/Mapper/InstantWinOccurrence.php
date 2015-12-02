<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class InstantWinOccurrence extends AbstractMapper
{

    public function findByEntry($entry)
    {
        return $this->getEntityRepository()->findOneBy(array('entry' => $entry));
    }

    public function findByGameId($instant_win, $sortArray = array())
    {
        return $this->getEntityRepository()->findBy(array('instantwin' => $instant_win), $sortArray);
    }

    public function queryByGame($instant_win)
    {
        $query = $this->em->createQuery(
            'SELECT i FROM PlaygroundGame\Entity\InstantWinOccurrence i
                WHERE i.instantwin = :game
                ORDER BY i.value ASC'
        );
        $query->setParameter('game', $instant_win);
        return $query;
    }

    public function queryPlayedByGame($instant_win)
    {
        $query = $this->em->createQuery(
            'SELECT i FROM PlaygroundGame\Entity\InstantWinOccurrence i
                WHERE i.instantwin = :game
                AND i.entry IS NOT NULL'
        );
        $query->setParameter('game', $instant_win);
        return $query;
    }

    public function queryWinningPlayedByGame($instant_win)
    {
        $query = $this->em->createQuery(
            'SELECT i FROM PlaygroundGame\Entity\InstantWinOccurrence i
                WHERE i.instantwin = :game
                AND i.entry IS NOT NULL
                AND i.winning = 1'
        );
        $query->setParameter('game', $instant_win);
        return $query;
    }

    /**
     *  DEPRECATED : The $user is replaced by its entry from now on
     */
    public function checkDateOccurrenceByGameId($instant_win)
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
            return current($result);
        } else {
            return null;
        }
    }

    public function checkCodeOccurrenceByGameId($instant_win, $value)
    {
        $query = $this->em->createQuery(
            'SELECT i FROM PlaygroundGame\Entity\InstantWinOccurrence i
                WHERE i.instantwin = :game
                AND i.active = 1
                AND i.value = :value
                AND i.entry IS NULL
                ORDER BY i.value DESC
                '
        );
        $query->setParameter('game', $instant_win);
        $query->setParameter('value', $value);
        $query->setMaxResults(1);
        $result = $query->getResult();

        if (count($result) == 1) {
            return current($result);
        } else {
            return null;
        }
    }

    public function assertNoOther($instantwin, $value)
    {
        $query = $this->em->createQuery(
            'SELECT i FROM PlaygroundGame\Entity\InstantWinOccurrence i
                WHERE i.instantwin = :game
                AND i.value = :value'
        );
        $query->setParameter('game', $instantwin);
        $query->setParameter('value', $value);
        if ($query->getResult()) {
            return false;
        }
        return true;
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\InstantWinOccurrence');
        }

        return $this->er;
    }
}
