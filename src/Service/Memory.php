<?php

namespace PlaygroundGame\Service;

use Zend\Stdlib\ErrorHandler;

class Memory extends Game
{
    protected $memoryMapper;
    protected $memoryCardMapper;
    protected $memoryScoreMapper;

    public function getCardPath($card)
    {
        $path = $this->getOptions()->getMediaPath().DIRECTORY_SEPARATOR;
        $path .= 'game'.$card->getGame()->getId().DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $path .= 'cards'.DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    public function getCardMediaUrl($card)
    {
        $media_url = $this->getOptions()->getMediaUrl().'/';
        $media_url .= 'game'.$card->getGame()->getId().'/cards/';

        return $media_url;
    }

    /**
     *
     * saving a memory image if any
     *
     * @param  array $data
     * @param  string $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function createOrUpdate(array $data, $game, $formClass)
    {
        $game = parent::createOrUpdate($data, $game, $formClass);

        if ($game) {
            $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
            $media_url = $this->getOptions()->getMediaUrl() . '/';

            if (!empty($data['uploadBackImage']['tmp_name'])) {
                ErrorHandler::start();
                $data['uploadBackImage']['name'] = $this->fileNewname(
                    $path,
                    $game->getId() . "-" . $data['uploadBackImage']['name']
                );
                move_uploaded_file(
                    $data['uploadBackImage']['tmp_name'],
                    $path . $data['uploadBackImage']['name']
                );
                $game->setBackImage($media_url . $data['uploadBackImage']['name']);
                ErrorHandler::stop(true);

                $game = $this->getGameMapper()->update($game);
            }

            if (isset($data['deleteBackImage']) &&
                $data['deleteBackImage'] &&
                empty($data['uploadBackImage']['tmp_name'])
            ) {
                ErrorHandler::start();
                $image = $game->getBackImage();
                $image = str_replace($media_url, '', $image);
                unlink($path .$image);
                $game->setBackImage(null);
                ErrorHandler::stop(true);

                $game = $this->getGameMapper()->update($game);
            }
        }

        return $game;
    }

    /**
     * @param  array $data
     * @return \PlaygroundGame\Entity\Game
     */
    public function updateCard(array $data, $card)
    {
        $form        = $this->serviceLocator->get('playgroundgame_memorycard_form');
        $memory = $this->getGameMapper()->findById($data['memory_id']);
        $card->setGame($memory);
        $path      = $this->getCardPath($card);
        $media_url = $this->getCardMediaUrl($card);

        $form->bind($card);
        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }
        if (!empty($data['upload_image']['tmp_name'])) {
            ErrorHandler::start();
            $data['upload_image']['name'] = $this->fileNewname(
                $path,
                $card->getId()."-".$data['upload_image']['name']
            );
            move_uploaded_file($data['upload_image']['tmp_name'], $path.$data['upload_image']['name']);
            $card->setImage($media_url.$data['upload_image']['name']);
            ErrorHandler::stop(true);
        }

        $this->getMemoryCardMapper()->update($card);

        return $card;
    }

    public function memoryScore($game, $user, array $data)
    {
        $result = false;
        $entry = $this->findLastActiveEntry($game, $user);

        if (!$entry) {
            return $result;
        }
        $score = $this->getMemoryScoreMapper()->findOneBy(array('entry' => $entry));

        if (! $score) {
            $score = new \PlaygroundGame\Entity\MemoryScore();
            $score->setEntry($entry);
            $score->setAttempts($data['attempts']);
            $score->setMistakes($data['mistakes']);
            $score->setDuration($data['duration']);
            $score->setTotalCards(count($game->getCards()));
            $score = $this->getMemoryScoreMapper()->insert($score);
        } else {
            $score->setAttempts($data['attempts']);
            $score->setMistakes($data['mistakes']);
            $score->setDuration($data['duration']);
            $score->setTotalCards(count($game->getCards()));
            $this->getMemoryScoreMapper()->update($score);
        }
        
        $entry->setActive(0);
        $entry = $this->isMemoryWinner($game, $user, $score, $entry);
        $this->getEntryMapper()->update($entry);

        $this->getEventManager()->trigger(
            __FUNCTION__ .'.post',
            $this,
            array('user' => $user, 'game' => $game, 'entry' => $entry, 'score' => $score)
        );

        return $card;
    }

    /**
     * return entry after checking victory conditions.
     *
     * @param \PlaygroundGame\Entity\Game $game
     * @param \PlaygroundUser\Entity\User $user
     * @param \PlaygroundUser\Entity\MemoryScore $score
     * @param \PlaygroundUser\Entity\Entry $entry
     *
     * @return \PlaygroundUser\Entity\Entry $entry
     */
    public function isMemoryWinner($game, $user, $score, $entry)
    {
        $victoryCondition = $game->getVictoryConditions()/100;
        $scoreRatio = ($score->getAttempts() - $score->getMistakes()) / $score->getAttempts();
        if ($scoreRatio >= $victoryCondition) {
            $entry->setWinner(true);
        }

        $this->getEventManager()->trigger(
            __FUNCTION__ . '.post',
            $this,
            [
                'user'  => $user,
                'game'  => $game,
                'entry' => $entry,
                'score' => $score,
            ]
        );

        return $entry;
    }

    public function getMemoryMapper()
    {
        if (null === $this->memoryMapper) {
            $this->memoryMapper = $this->serviceLocator->get('playgroundgame_memory_mapper');
        }

        return $this->memoryMapper;
    }

    public function getMemoryCardMapper()
    {
        if (null === $this->memoryCardMapper) {
            $this->memoryCardMapper = $this->serviceLocator->get('playgroundgame_memory_card_mapper');
        }

        return $this->memoryCardMapper;
    }

    public function getMemoryScoreMapper()
    {
        if (null === $this->memoryScoreMapper) {
            $this->memoryScoreMapper = $this->serviceLocator->get('playgroundgame_memory_score_mapper');
        }

        return $this->memoryScoreMapper;
    }
}
