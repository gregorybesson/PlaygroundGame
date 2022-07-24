<?php
namespace PlaygroundGame\Controller\Frontend;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Model\JsonModel;

class CrosswordController extends GameController
{
    /**
     * @var gameService
     */
    protected $gameService;

    public function __construct(ServiceLocatorInterface $locator)
    {
        parent::__construct($locator);
    }

    public function playAction()
    {
        $playError = null;
        $locale = $this->getServiceLocator()->get('MvcTranslator')->getLocale();
        $entry = $this->getGameService()->play($this->game, $this->user, $playError);
        if (!$entry) {
          $reason = "";
          if ($playError === -1) {
            // the user has already taken part to this game and the participation limit has been reached
            $this->flashMessenger()->addMessage('Vous avez déjà participé');
            $reason = '?playLimitReached=1';
            $noEntryRedirect = $this->frontendUrl()->fromRoute(
              $this->game->getClassType().'/result',
              array(
                'id' => $this->game->getIdentifier(),
              )
            ) .$reason;
          } elseif ($playError === -2) {
            // the user has not accepted the mandatory rules of the game
            $this->flashMessenger()->addMessage('Vous devez accepter le réglement');
            $reason = '?NoOptin=1';
            $noEntryRedirect = $this->frontendUrl()->fromRoute(
              $this->game->getClassType(),
              array(
                'id' => $this->game->getIdentifier(),
              )
            ) .$reason;
          } elseif ($playError === -3) {
            // the user has not enough points to buy an entry to this game
            $this->flashMessenger()->addMessage("Vous ne pouvez pas acheter la partie");
            $reason = '?NotPaid=1';
            $noEntryRedirect = $this->frontendUrl()->fromRoute(
              $this->game->getClassType(),
              array(
                'id' => $this->game->getIdentifier(),
              )
            ) .$reason;
          }

          return $this->redirect()->toUrl($noEntryRedirect);
        }

        if ($this->getRequest()->isPost()) {
          $data = $this->getRequest()->getPost()->toArray();
          if ($this->game->getGameType() == "crossword") {
            $entry = $this->getGameService()->crosswordScore($this->game, $this->user, $entry, $data);
            return $this->redirect()->toUrl(
              $this->frontendUrl()->fromRoute(
                'crossword/result',
                array('id' => $this->game->getIdentifier())
              )
            );
          } else if ($this->game->getGameType() == "wordle" || $this->game->getGameType() == "hangman") {
            $entry = $this->getGameService()->wordScore($this->game, $this->user, $entry, $data);
            if (! $entry->getActive()) {
              return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                  'crossword/result',
                  array('id' => $this->game->getIdentifier())
                )
              );
            }
          } else if ($this->game->getGameType() == "word_search") {
            $entry = $this->getGameService()->wordsearchScore($this->game, $this->user, $entry, $data);
            if (! $entry->getActive()) {
              return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                  'crossword/result',
                  array('id' => $this->game->getIdentifier())
                )
              );
            }
          }
        }

        $viewModel = $this->buildView($this->game);
        $words = $this->game->getWords();
        switch ($this->game->getGameType()) {
          case "crossword":
            $rows = $this->game->getLayoutRows();
            $columns = $this->game->getLayoutColumns();
            $acrossClues = [];
            $downClues = [];

            foreach ($words as $word) {
              if ($word->getOrientation() == "across"){
                $acrossClues[] = [
                  "x" => $word->getLayoutColumn(),
                  "y" => $word->getLayoutRow(),
                  "clue" => $word->getPosition() . '. ' . trim($word->getClue()) . ' (' . strlen($word->getSolution()) . ')',
                ];
              } else if ($word->getOrientation() == "down"){
                $downClues[] = [
                  "x" => $word->getLayoutColumn(),
                  "y" => $word->getLayoutRow(),
                  "clue" => $word->getPosition() . '. ' . trim($word->getClue()) . ' (' .strlen($word->getSolution()) . ')',
                ];
              }
            }

            $crosswordDefinition = [
              "width" => $columns,
              "height" => $rows,
              "acrossClues" => $acrossClues,
              "downClues" => $downClues,
            ];

            $viewModel->setVariables(array(
              'crosswordDefinition' => json_encode($crosswordDefinition),
              'acrossClues' => $acrossClues,
              'downClues' => $downClues,
            ));
            break;
          case "word_search":
            $wordsAr = [];
            $grid = [];
            $rows = $this->game->getLayoutRows();
            $columns = $this->game->getLayoutColumns();

            for ($i = 0; $i <= $rows + 1; $i++) {
              $grid[$i] = [];
              for ($j = 0; $j <= $columns + 1; $j++) {
                if ($i > 0 && $i < $rows + 1 && $j > 0 && $j < $columns + 1) {
                  $grid[$i][] = chr(rand(65,90));
                } else {
                  $grid[$i][] = "-";
                }
              }
            }

            foreach ($words as $word) {
              $wordAr = [
                "word" => strtoupper($word->getSolution()),
                "rowi" => $word->getLayoutRow() - 1,
                "coli" => $word->getLayoutColumn() - 1,
                "colf" => ($word->getOrientation() == "across") ? $word->getLayoutColumn() + strlen($word->getSolution()) - 2 : $word->getLayoutColumn() - 1,
                "rowf" => ($word->getOrientation() == "down") ? $word->getLayoutRow() + strlen($word->getSolution()) - 2 : $word->getLayoutRow() - 1,
                "found" => false,
              ];
              $wordsAr[] = $wordAr;
              $position = 0;
              $letters = str_split($word->getSolution());
              foreach($letters as $letter) {
                if ($word->getOrientation() == "across") {
                  $grid[$word->getLayoutRow()][$word->getLayoutColumn() + $position] = strtolower($letter);
                } else {
                  $grid[$word->getLayoutRow() + $position][$word->getLayoutColumn()] = strtolower($letter);
                }
                $position++;
              }
            }

            $crosswordDefinition = [
              "COLS" => $columns,
              "ROWS" => $rows,
              "grid" => $grid,
              "words" => $wordsAr,
            ];

            $viewModel->setVariables(array(
              'puzzle' => json_encode($crosswordDefinition),
            ));
            break;
          case "hangman":
          case "wordle":
            $index = $entry->getStep();
            if (!isset($words[$index])) {
              $word = $words[0]->getSolution();
              $clue = $words[0]->getClue();
            } else {
              $word = $words[$index]->getSolution();
              $clue = $words[$index]->getClue();
            }

            $viewModel->setVariables([
              'word' => $word,
              'clue' => $clue,
              'locale' => $locale,
            ]);
            break;
        }

        return $viewModel;
    }

    public function hintAction()
    {
      $arWordsFound = [];
      if ($this->getRequest()->isPost()) {
        $payload = $this->getRequest()->getPost()->toArray();
        $arWordsFound = $this->getGameService()->crosswordHint($this->game, $payload['data']);
      }

      $model = new JsonModel(array(
          'success' => false,
          'words' => $arWordsFound,
      ));

      return $model->setTerminal(true);
    }

    public function resultAction()
    {
      $statusMail = null;
      $playLimitReached = false;
      if ($this->getRequest()->getQuery()->get('playLimitReached')) {
        $playLimitReached = true;
      }

      $lastEntry = $this->getGameService()->findLastInactiveEntry($this->game, $this->user);
      if (!$lastEntry) {
        return $this->redirect()->toUrl(
          $this->frontendUrl()->fromRoute(
            $this->game->getClassType(),
            array('id' => $this->game->getIdentifier()),
            array('force_canonical' => true)
          )
        );
      }

      $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
      $form->setAttribute('method', 'post');

      if ($this->getRequest()->isPost()) {
        $data = $this->getRequest()->getPost()->toArray();
        $form->setData($data);
        if ($form->isValid()) {
          $result = $this->getGameService()->sendShareMail($data, $this->game, $this->user, $lastEntry);
          if ($result) {
            $statusMail = true;
            $this->getGameService()->addAnotherChance($this->game, $this->user, 1);
          }
        }
      }

      // buildView must be before sendMail because it adds the game template path to the templateStack
      $viewModel = $this->buildView($this->game);

      if (!$playLimitReached) {
        //$this->getGameService()->sendMail($this->game, $this->user, $lastEntry);
      }

      $viewModel->setVariables(array(
        'statusMail'    => $statusMail,
        'form'          => $form,
        'playLimitReached' => $playLimitReached,
        'entry' => $lastEntry
      ));

      return $viewModel;
    }

    public function getAllWordsAction()
    {
      $length = $this->getEvent()->getRouteMatch()->getParam('length');
      $locale = $this->getServiceLocator()->get('MvcTranslator')->getLocale();
      $arWordsFound = $this->getGameService()->getAllWords($locale, $length);
      $model = new JsonModel(array(
        'success' => false,
        'words' => $arWordsFound,
      ));

      return $model->setTerminal(true);
    }

    public function getGameService()
    {
      if (!$this->gameService) {
        $this->gameService = $this->getServiceLocator()->get('playgroundgame_crossword_service');
      }

      return $this->gameService;
    }
}
