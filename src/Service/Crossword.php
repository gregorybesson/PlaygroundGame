<?php

namespace PlaygroundGame\Service;

use Laminas\Stdlib\ErrorHandler;

class Crossword extends Game
{
  protected $crosswordMapper;
  protected $crosswordWordMapper;
  private $locale;
  private $path;
  private $corePath;

  /**
   * @param  array $data
   * @return \PlaygroundGame\Entity\Game
   */
  public function updateWord(array $data, $word)
  {
    $form = $this->serviceLocator->get('playgroundgame_crosswordword_form');
    $crossword = $this->getGameMapper()->findById($data['crossword_id']);
    $word->setGame($crossword);

    $form->bind($word);
    $form->setData($data);

    if (!$form->isValid()) {
        return false;
    }

    // TODO: Solve the error on binding. The following properties are not binded thru the form
    if ($crossword->getGameType() == "crossword") {
      $word->setLayoutColumn($data['layoutColumn']);
      $word->setLayoutRow($data['layoutRow']);
      $word->setPosition($data['position']);
      $word->setOrientation($data['orientation']);
    }

    $this->getCrosswordWordMapper()->update($word);

    if ($crossword->getGameType() == "word_search") {
      $maxSize = max($crossword->getLayoutRows(), strlen($word->getSolution()) + 2);
      $crossword->setLayoutRows($maxSize);
      $crossword->setLayoutColumns($maxSize);

      $this->getGameMapper()->update($crossword);
    }

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

  public function crosswordScore($game, $entry, $data)
  {
    $crosswordResult = json_decode($data['crossword'], true);

    $words = $game->getWords();
    $points = 0;
    $solved = false;
    $wordsFound = 0;
    $wordsToFind = count($words);

    foreach($crosswordResult as $wordResult) {
      $id = $wordResult['id'];
      foreach($words as $word) {
        if ($word->getPosition() == $id && $word->getSolution() == $wordResult['answer']) {
          $wordsFound++;
          $points++;
        }
      }
    }

    $victoryCondition = $game->getVictoryConditions()/100;
    $scoreRatio = $wordsFound / $wordsToFind;

    if ($scoreRatio >= $victoryCondition) {
      $solved = true;
    }

    $entry->setPoints($points);
    $entry->setWinner($solved);
    $entry->setDrawable($solved);
    $entry->setActive(false);

    $entry = $this->getEntryMapper()->update($entry);

    return $entry;
  }

  public function wordScore($game, $entry, $data)
  {
    $words = $game->getWords();
    $hasWon = $data['word'];
    $entry->setStep($entry->getStep()+1);
    $activeStep = $entry->getStep();

    if (count($words) == $activeStep) {
      $entry->setActive(false);
    }

    if ($hasWon) {
      if ($entry->getStep() == 1 || $entry->getWinner()) {
        $entry->setWinner(true);
        $entry->setDrawable(true);
      }
    } else {
      $entry->setWinner(false);
      $entry->setDrawable(false);
    }

    $entry = $this->getEntryMapper()->update($entry);

    return $entry;
  }

  public function crosswordHint($game, $data)
  {
    $crosswordResult = json_decode($data, true);
    $words = $game->getWords();
    $points = 0;
    $solved = false;
    $wordsFound = 0;
    $wordsToFind = count($words);
    $arWordsFound = [];

    foreach($crosswordResult as $wordResult) {
      $id = $wordResult['id'];
      foreach($words as $word) {
        if ($word->getPosition() == $id) {
          if ($word->getSolution() == $wordResult['answer']) {
            $wordsFound++;
            $points++;
            $arWordsFound[] = ["position" => ($wordResult['clue_type'] == "across") ? "a".$id : "d".$id, "solution" => $word->getSolution()];
          }
        }
      }
    }
    if ($wordsFound == $wordsToFind) {
      $solved = true;
    }

    return $arWordsFound;
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

  public function getPath()
  {
    if (empty($this->path)) {
      $this->path = str_replace('\\', '/', getcwd()) . '/language/letters';
    }

    return $this->path;
  }

  public function getCorePath()
  {
    if (empty($this->corePath)) {
      $this->corePath = __DIR__ . '/../../language/letters';
    }

    return $this->corePath;
  }

  public function getAllWords($locale = null, $numberLetters = 5)
  {
    if (null === $locale) {
      $locale = $this->serviceLocator->get('MvcTranslator')->getLocale();
    }

    $fileName = $this->getPath() . '/' . $locale . '-' . $numberLetters . '.php';
    if (! file_exists($fileName)) {
      $fileName = $this->getCorePath() . '/' . $locale . '-' . $numberLetters . '.php';

      if (! file_exists($fileName)) {
        throw new \InvalidArgumentException("Letters $locale not found.");
      }
    }

    return include $fileName;
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
