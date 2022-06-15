<?php

namespace PlaygroundGame\Service;

use Laminas\Stdlib\ErrorHandler;

class Crossword extends Game
{
    protected $crosswordMapper;
    protected $crosswordWordMapper;

    /**
     * @param  array $data
     * @return \PlaygroundGame\Entity\Game
     */
    public function updateWord(array $data, $word)
    {
        $form        = $this->serviceLocator->get('playgroundgame_crosswordword_form');
        $crossword = $this->getGameMapper()->findById($data['crossword_id']);
        $word->setGame($crossword);

        $form->bind($word);
        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }

        // TODO: Solve the error on binding. The following properties are not binded thru the form
        $word->setLayoutColumn($data['layoutColumn']);
        $word->setLayoutRow($data['layoutRow']);
        $word->setPosition($data['position']);
        $word->setOrientation($data['orientation']);

        $this->getCrosswordWordMapper()->update($word);

        return $word;
    }

    /**
     * @param  array $data
     * @return \PlaygroundGame\Entity\Game
     */
    public function updateWords(array $data, $crossword)
    {
        $crossword->setLayoutRows($data['rows']);
        $crossword->setLayoutColumns($data['cols']);
        $this->getGameMapper()->update($crossword);

        $words = $data['result'];
        foreach($words as $wordAr) {
            if (isset($wordAr['startx']) && isset($wordAr['starty'])) {
                $word = $this->getCrosswordWordMapper()->findById($wordAr['id']);
                $word->setLayoutRow($wordAr['starty']);
                $word->setLayoutColumn($wordAr['startx']);
                $word->setOrientation($wordAr['orientation']);
                $word->setPosition($wordAr['position']);
                $this->getCrosswordWordMapper()->update($word);
            }
        }

        return true;
    }


    /**
     * return entry after checking victory conditions.
     *
     * @param \PlaygroundGame\Entity\Game $game
     * @param \PlaygroundUser\Entity\User $user
     * @param \PlaygroundUser\Entity\CrosswordScore $score
     * @param \PlaygroundUser\Entity\Entry $entry
     *
     * @return \PlaygroundUser\Entity\Entry $entry
     */
    public function isCrosswordWinner($game, $user, $score, $entry)
    {
        $victoryCondition = $game->getVictoryConditions()/100;
        // $scoreRatio = ($score->getAttempts() - $score->getMistakes()) / $score->getAttempts();
        // if ($scoreRatio >= $victoryCondition) {
        //     $entry->setWinner(true);
        // }

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

    public function getCrosswordMapper()
    {
        if (null === $this->crosswordMapper) {
            $this->crosswordMapper = $this->serviceLocator->get('playgroundgame_crossword_mapper');
        }

        return $this->crosswordMapper;
    }

    public function getCrosswordWordMapper()
    {
        if (null === $this->crosswordWordMapper) {
            $this->crosswordWordMapper = $this->serviceLocator->get('playgroundgame_crossword_word_mapper');
        }

        return $this->crosswordWordMapper;
    }
}
