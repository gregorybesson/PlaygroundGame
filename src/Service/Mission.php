<?php

namespace PlaygroundGame\Service;

use Laminas\ServiceManager\ServiceManager;
use PlaygroundGame\Service\Game;
use PlaygroundGame\Entity\MissionGameCondition as MissionGameConditionEntity;

class Mission extends Game
{

    /**
     * @var MissionMapperInterface
     */
    protected $missionMapper;

    /**
     * @var MissionGameMapperInterface
     */
    protected $missionGameMapper;

    protected $options;

    /**
     * Find games associated to a mission and add the last entry of the user if it exists
     * @param unknown $mission
     * @return multitype:NULL
     */
    public function getMissionGames($mission, $user)
    {
        $games = array();
        $missionGames = $this->findGamesByMission($mission);
        foreach ($missionGames as $missionGame) {
            $entry = $this->checkExistingEntry($missionGame->getGame(), $user);
            $games[$missionGame->getGame()->getIdentifier()]['game'] = $missionGame;
            $games[$missionGame->getGame()->getIdentifier()]['entry'] = $entry;
        }

        return $games;
    }

    /**
     * findMissionGameByMission : find associated games to a mission
     * @param Mission $mission
     *
     * @return Collection de MissionGame $missionGames
     */
    public function findGamesByMission($mission)
    {
        return $this->getMissionGameMapper()->findBy(array('mission'=>$mission));
    }

    public function checkMissionCondition($game, $winner, $prediction, $entry)
    {
        $missionGame = $this->findMissionGameByGame($game);
        if (empty($missionGame)) {
            return false;
        }

        if ($missionGame->getMission()->getActive() === false) {
            return false;
        }

        $nextMissionGame = $this->getMissionGameMapper()->getNextGame(
            $missionGame->getMission(),
            $missionGame->getPosition()
        );

        if (empty($nextMissionGame)) {
            return false;
        }

        $missionGameConditions = $this->findMissionGameConditionByMissionGame($nextMissionGame);

        if (empty($missionGameConditions)) {
            return false;
        }

        foreach ($missionGameConditions as $missionGameCondition) {
            if ($missionGameCondition->getAttribute() == MissionGameConditionEntity::NONE) {
                continue;
            }

            // On passe au suivant si on a gagné
            if ($missionGameCondition->getAttribute() == MissionGameConditionEntity::VICTORY) {
                if (!($winner || $prediction)) {
                    return false;
                }
            }

            // On passe au suivant si on a perdu
            if ($missionGameCondition->getAttribute() == MissionGameConditionEntity::DEFEAT) {
                if ($winner || $prediction) {
                    return false;
                }
            }

            // On passe au suivant si on a perdu
            if ($missionGameCondition->getAttribute() == MissionGameConditionEntity::GREATER) {
                if (!$entry) {
                    return false;
                }
                if (!($entry->getPoints() > $missionGameCondition->getValue())) {
                    return false;
                }
            }

            // On passe au suivant si on a perdu
            if ($missionGameCondition->getAttribute() == MissionGameConditionEntity::LESS) {
                if (!$entry) {
                    return false;
                }
                if (!($entry->getPoints() < $missionGameCondition->getValue())) {
                    return false;
                }
            }
        }

        return $nextMissionGame->getGame();
    }

    public function missionWinner($game, $user, $entry, $subGame)
    {
        $entry->setStep($entry->getStep() + 1);
        $lastEntry = $this->findLastInactiveEntry($subGame, $user);
        $entry->setPoints($entry->getPoints() + $lastEntry->getPoints());

        if (
            $lastEntry->getWinner() &&
            (
                $entry->getWinner() !== false ||
                $entry->getStep() <= 1
            )
        ) {
            $entry->setWinner(true);
        }

        if ($entry->getStep() == count($game->getMissionGames())) {
            $entry->setActive(false);
        }
        $entry = $this->getEntryMapper()->update($entry);

        if (!$entry->getActive()) {
            $this->getEventManager()->trigger(
                __FUNCTION__ .'.post',
                $this,
                array('user' => $user, 'entry' => $entry, 'game' => $game)
            );
        } else {
            $this->getEventManager()->trigger(
                __FUNCTION__ .'.step',
                $this,
                array('user' => $user, 'entry' => $entry, 'game' => $game, 'subGame' => $subGame)
            );
        }

        return $entry;
    }

    public function getGameEntity()
    {
        return new \PlaygroundGame\Entity\Mission;
    }

    /**
     * getMissionMapper
     *
     * @return MissionMapperInterface
     */
    public function getMissionMapper()
    {
        if (null === $this->missionMapper) {
            $this->missionMapper = $this->serviceLocator->get('playgroundgame_mission_mapper');
        }

        return $this->missionMapper;
    }

    /**
     * setMissionMapper
     *
     * @param  MissionMapperInterface $missionMapper
     * @return Mission
     */
    public function setMissionMapper($missionMapper)
    {
        $this->missionMapper = $missionMapper;

        return $this;
    }

    /**
     * getMissionGameMapper
     *
     * @return MissionGameMapperInterface
     */
    public function getMissionGameMapper()
    {
        if (null === $this->missionGameMapper) {
            $this->missionGameMapper = $this->serviceLocator->get('playgroundgame_mission_game_mapper');
        }

        return $this->missionGameMapper;
    }

    /**
     * setMissionMapper
     *
     * @param  MissionMapperInterface $missionGameMapper
     * @return Mission
     */
    public function setMissionGameMapper($missionGameMapper)
    {
        $this->missionGameMapper = $missionGameMapper;

        return $this;
    }
}
