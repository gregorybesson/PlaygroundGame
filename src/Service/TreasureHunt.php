<?php

namespace PlaygroundGame\Service;

use DoctrineModule\Validator\NoObjectExists as NoObjectExistsValidator;
use PlaygroundGame\Mapper\GameInterface as GameMapperInterface;
use PlaygroundGame\Service\Game;
use Laminas\Stdlib\ErrorHandler;

class TreasureHunt extends Game
{

    /**
     * @var TreasureHuntMapperInterface
     */
    protected $treasurehuntMapper;

    /**
     * @var TreasurehuntScoreMapper
     */
    protected $treasurehuntScoreMapper;

    /**
     * @var TreasurehuntPuzzleMapper
     */
    protected $treasureHuntPuzzleMapper;

    /**
     * @var TreasurehuntPuzzlePieceMapper
     */
    protected $treasureHuntPuzzlePieceMapper;

    protected $options;

    public function getGameEntity()
    {
        return new \PlaygroundGame\Entity\TreasureHunt;
    }

    /**
     * getGameMapper
     *
     * @return GameMapperInterface
     */
    public function getGameMapper()
    {
        if (null === $this->gameMapper) {
            $this->gameMapper = $this->serviceLocator->get('playgroundgame_treasurehunt_mapper');
        }

        return $this->gameMapper;
    }

    public function analyzeClue($game, $data, $user)
    {
      $entryMapper = $this->getEntryMapper();
      $entry = $this->findLastActiveEntry($game, $user);
      $success = false;
      $nextPuzzle = false;

      if (!$entry) {
        return false;
      }

      // I get the score or create a new one if it's the first try
      $score = $this->getTreasureHuntScoreMapper()->findOneBy(array('entry' => $entry));
      $puzzles = $game->getPuzzles();

      if (! $score) {
          $score = new \PlaygroundGame\Entity\TreasureHuntScore();
          $score->setEntry($entry);
          $jsonPuzzles = array();
          foreach ($puzzles as $puzzle) {
            $jsonPuzzles[$puzzle->getId()] = array(
              'id' => $puzzle->getId(),
              'solved' => false,
              'mistakes' => 0,
              'pieces' => array()
            );
            $pieces = $puzzle->getPieces();
            foreach ($pieces as $piece) {
              $jsonPuzzles[$puzzle->getId()]['pieces'][$piece->getId()] = array(
                'id' => $piece->getId(),
                'found' => false
              );
            }
          }
          $score->setJsonPuzzles(json_encode($jsonPuzzles));
          $score = $this->getTreasureHuntScoreMapper()->insert($score);
      } else {
        $jsonPuzzles = json_decode($score->getJsonPuzzles(), true);
      }

      $puzzle = $puzzles[$entry->getStep()];
      $puzzleId = $puzzle->getId();

      // We check that the player is allowed to play this puzzle
      if (
        $game->getLimitErrorsAllowed() &&
          $jsonPuzzles[$puzzleId]['mistakes'] >= $game->getErrorsAllowed()
      ) {
        $entry->setWinner(false);
        $entry->setDrawable(false);
        $entry->setActive(false);
        $entry = $entryMapper->update($entry);

        return ['success' => $success, 'entry' => $entry, 'nextPuzzle' => $nextPuzzle ];
      } else if ($jsonPuzzles[$puzzleId]['solved']) {
        if(count($puzzles) > $entry->getStep()+1){
          $nextPuzzle = true;
          $entry->setStep($entry->getStep()+1);
        } else {
          $entry->setActive(false);
        }
        $entry = $entryMapper->update($entry);

        return ['success' => $success, 'entry' => $entry, 'nextPuzzle' => $nextPuzzle ];
      }

      // We check if the player has found the piece
      $result = $this->isWinner($puzzle, $data, $jsonPuzzles[$puzzleId]);
      if ($result['winner']) {
        $success = true;
        $jsonPuzzles[$puzzleId]['pieces'][$result['pieceId']]['found'] = true;
        // If all pieces are found, then the puzzle is solved
        $allPiecesFound = true;
        foreach ($jsonPuzzles[$puzzleId]['pieces'] as $piece) {
          if (!$piece['found']) {
            $allPiecesFound = false;
            break;
          }
        }
        if ($allPiecesFound) {
          $jsonPuzzles[$puzzleId]['solved'] = true;
          if(count($puzzles) > $entry->getStep()+1){
            $nextPuzzle = true;
            $entry->setStep($entry->getStep()+1);
          } else {
            $allSolved = $this->isAllPuzzleSolved($jsonPuzzles);
            $entry->setWinner($allSolved);
            $entry->setDrawable($allSolved);
            $entry->setActive(false);
          }
        }
      } else {
        $jsonPuzzles[$puzzleId]['mistakes']++;
        if (
          $game->getLimitErrorsAllowed() &&
          $jsonPuzzles[$puzzleId]['mistakes'] >= $game->getErrorsAllowed()
        ) {
          $entry->setWinner(false);
          $entry->setDrawable(false);
          $entry->setActive(false);
        }
      }

      // I update the score
      $score->setJsonPuzzles(json_encode($jsonPuzzles));
      $this->getTreasureHuntScoreMapper()->update($score);
      $entry = $entryMapper->update($entry);

      $this->getEventManager()->trigger(
        'analyze_clue.post',
        $this,
        array(
          'user' => $user,
          'entry' => $entry,
          'score' => $score,
          'success' => $success,
          'game' => $game
        )
      );

      return [ 'success' => $success, 'entry' => $entry, 'nextPuzzle' => $nextPuzzle];
    }

    public function isAllPuzzleSolved($jsonPuzzles)
    {
      foreach ($jsonPuzzles as $puzzle) {
        if (!$puzzle['solved']) {
          return false;
        }
      }

      return true;
    }

    /**
     *
     * @param unknown $game
     * @param unknown $data
     * @return boolean
     */
    public function isWinner($puzzle, $data=array(), $jsonPuzzle)
    {
      $winner = false;
      $pieceId = null;
      foreach ($puzzle->getPieces() as $piece) {
        if ($jsonPuzzle['pieces'][$piece->getId()]['found'] == true) {
          continue;
        }
        $json = json_decode($piece->getArea(), true);
        if(isset($json['area'])) {
          $area = $json['area'];
          if ($data['x'] >= $area['x'] &&
            $data['x'] <= ($area['x'] + $area['width']) &&
            $data['y'] >= $area['y'] &&
            $data['y'] <= ($area['y'] + $area['height'])
          ) {
            $winner = true;
            $pieceId = $piece->getId();
            break;
          }
        }
      }

      return ['winner' => $winner, 'pieceId' => $pieceId];
    }

    /**
     *
     * @param unknown $game
     * @param unknown $data
     * @return boolean
     */
    public function isWinnerOld($game, $entry, $data=array())
    {
        $winner = false;
        $json = json_decode($game->getPuzzle($entry->getStep())->getArea(), true);

        if(isset($json['area'])){
            $area = $json['area'];
            if ($data['x'] >= $area['x'] &&
                $data['x'] <= ($area['x']+$area['width']) &&
                $data['y'] >= $area['y'] &&
                $data['y'] <= ($area['y']+$area['height'])
            ) {
                $winner = true;
                //echo "WINNER !!! x : ".$data['x']." - y : ".$data['y']. " - zone x : ".$json['area']['x']. " -y : ".$json['area']['y']. " width : ".$json['area']['width']. " height : ".$json['area']['height'];
            }
        }

        return $winner;
    }

    public function uploadImages($puzzle, $data)
    {
        $path      = $this->getOptions()->getMediaPath().DIRECTORY_SEPARATOR;
        $media_url = $this->getOptions()->getMediaUrl().'/';

        foreach ($data['uploadImage'] as $uploadImage) {
            if (!empty($uploadImage['tmp_name'])) {
                ErrorHandler::start();
                if (!is_dir($path)) {
                    mkdir($path,0777, true);
                }
                $uploadImage['name'] = $this->fileNewname(
                    $path,
                    $puzzle->getTreasurehunt()->getId()."-".$puzzle->getId()."-".$uploadImage['name']
                );
                move_uploaded_file($uploadImage['tmp_name'], $path.$uploadImage['name']);
                $images = $puzzle->getImage();
                if (!empty($images)) {
                    $images = (array) json_decode($images);
                } else {
                    $images = [];
                }
                $images[] = $media_url.$uploadImage['name'];
                $puzzle->setImage(json_encode($images));
                $this->getTreasureHuntMapper()->update($puzzle);
                ErrorHandler::stop(true);
            }
        }

        foreach ($data['uploadReferenceImage'] as $uploadImage) {
          if (!empty($uploadImage['tmp_name'])) {
            ErrorHandler::start();
            if (!is_dir($path)) {
              mkdir($path,0777, true);
            }
            $uploadImage['name'] = $this->fileNewname(
              $path,
              $puzzle->getTreasurehunt()->getId()."-".$puzzle->getId()."-ref-".$uploadImage['name']
            );
            move_uploaded_file($uploadImage['tmp_name'], $path.$uploadImage['name']);
            $images = $puzzle->getReferenceImage();
            if (!empty($images)) {
              $images = (array) json_decode($images);
            } else {
              $images = [];
            }
            $images[] = $media_url.$uploadImage['name'];

            $puzzle->setReferenceImage(json_encode($images));
            $this->getTreasureHuntMapper()->update($puzzle);
            ErrorHandler::stop(true);
          }
        }

        return $puzzle;
    }

    /**
     *
     *
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function createPuzzle(array $data)
    {
    	$puzzle  = new \PlaygroundGame\Entity\TreasureHuntPuzzle();
    	$form  = $this->serviceLocator->get('playgroundgame_treasurehuntpuzzle_form');
    	$entityManager = $this->serviceLocator->get('doctrine.entitymanager.orm_default');

    	$identifierInput = $form->getInputFilter()->get('identifier');
    	$noObjectExistsValidator = new NoObjectExistsValidator(array(
        'object_repository' => $entityManager->getRepository('PlaygroundGame\Entity\TreasureHunt'),
        'fields' => 'identifier',
        'messages' => array(
          'objectFound' => 'This url already exists !'
        )
    	));

    	$identifierInput->getValidatorChain()->addValidator($noObjectExistsValidator);

    	$form->bind($puzzle);

    	// If the identifier has not been set, I use the title to create one.
    	if (empty($data['identifier']) && ! empty($data['title'])) {
    	    $data['identifier'] = $data['title'];
    	}

    	$form->setData($data);

    	$treasurehunt = $this->getGameMapper()->findById($data['treasurehunt_id']);

    	if (!$form->isValid()) {
            // foreach ($form->getMessages() as $el => $errors) {
            //     foreach ($errors as $key => $message) {
            //         echo $el . " - " . $key . " : " . $this->serviceLocator->get('MvcTranslator')->translate($message);
            //     }
            // }
    		return false;
    	}

    	$puzzle->setTreasurehunt($treasurehunt);

    	$this->getEventManager()->trigger(__FUNCTION__, $this, array('game' => $treasurehunt, 'data' => $data));
    	$this->getTreasureHuntPuzzleMapper()->insert($puzzle);
    	$this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('game' => $treasurehunt, 'data' => $data));

    	return $puzzle;
    }

    /**
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function updatePuzzle(array $data, $puzzle)
    {

    	$form  = $this->serviceLocator->get('playgroundgame_treasurehuntpuzzle_form');
    	$entityManager = $this->serviceLocator->get('doctrine.entitymanager.orm_default');

    	$identifierInput = $form->getInputFilter()->get('identifier');
    	$noObjectExistsValidator = new NoObjectExistsValidator(array(
        'object_repository' => $entityManager->getRepository('PlaygroundGame\Entity\TreasureHunt'),
        'fields' => 'identifier',
        'messages' => array(
          'objectFound' => 'This url already exists !'
        )
    	));

    	if ($puzzle->getIdentifier() != $data['identifier']) {
    	    $identifierInput->getValidatorChain()->addValidator($noObjectExistsValidator);
    	}
    	$form->bind($puzzle);

    	if (! isset($data['identifier']) && isset($data['title'])) {
    	    $data['identifier'] = $data['title'];
    	}

    	$form->setData($data);

    	if (!$form->isValid()) {
    		return false;
    	}

    	$this->getEventManager()->trigger(__FUNCTION__, $this, array('puzzle' => $puzzle, 'data' => $data));
    	$this->getTreasureHuntPuzzleMapper()->update($puzzle);
    	$this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('puzzle' => $puzzle, 'data' => $data));

    	return $puzzle;
    }

    public function createPiece(array $data)
    {

    	$piece  = new \PlaygroundGame\Entity\TreasureHuntPuzzlePiece();
    	$form  = $this->serviceLocator->get('playgroundgame_treasurehuntpuzzle_piece_form');
    	$entityManager = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
    	$form->bind($piece);
    	$form->setData($data);

    	$treasurehuntPuzzle = $this->getTreasureHuntPuzzleMapper()->findById($data['puzzle_id']);

    	if (!$form->isValid()) {
            // foreach ($form->getMessages() as $el => $errors) {
            //     foreach ($errors as $key => $message) {
            //         echo $el . " - " . $key . " : " . $this->serviceLocator->get('MvcTranslator')->translate($message);
            //     }
            // }
    		return false;
    	}

    	$piece->setPuzzle($treasurehuntPuzzle);

    	$this->getEventManager()->trigger(__FUNCTION__, $this, array('puzzle' => $treasurehuntPuzzle, 'data' => $data));
    	$this->getTreasureHuntPuzzlePieceMapper()->insert($piece);
    	$this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('puzzle' => $treasurehuntPuzzle, 'data' => $data));

    	return $piece;
    }

    /**
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function updatePiece(array $data, $piece)
    {
    	$form  = $this->serviceLocator->get('playgroundgame_treasurehuntpuzzle_piece_form');
    	$entityManager = $this->serviceLocator->get('doctrine.entitymanager.orm_default');

    	$form->bind($piece);
    	$form->setData($data);

    	if (!$form->isValid()) {
    		return false;
    	}

    	$this->getEventManager()->trigger(__FUNCTION__, $this, array('piece' => $piece, 'data' => $data));
    	$this->getTreasureHuntPuzzlePieceMapper()->update($piece);
    	$this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('piece' => $piece, 'data' => $data));

    	return $piece;
    }

    /**
     * getTreasureHuntMapper
     *
     * @return TreasureHuntMapperInterface
     */
    public function getTreasureHuntMapper()
    {
        if (null === $this->treasurehuntMapper) {
            $this->treasurehuntMapper = $this->serviceLocator->get('playgroundgame_treasurehunt_mapper');
        }

        return $this->treasurehuntMapper;
    }

    /**
     * setTreasureHuntMapper
     *
     * @param  TreasureHuntMapperInterface $treasurehuntMapper
     * @return User
     */
    public function setTreasureHuntMapper(GameMapperInterface $treasurehuntMapper)
    {
        $this->treasurehuntMapper = $treasurehuntMapper;

        return $this;
    }

    /**
     * getTreasureHuntPuzzleMapper
     *
     * @return TreasureHuntPuzzleMapperInterface
     */
    public function getTreasureHuntPuzzleMapper()
    {
    	if (null === $this->treasureHuntPuzzleMapper) {
    		$this->treasureHuntPuzzleMapper = $this->serviceLocator->get('playgroundgame_treasurehuntpuzzle_mapper');
    	}

    	return $this->treasureHuntPuzzleMapper;
    }

    /**
     * setTreasureHuntPuzzleMapper
     *
     * @param  TreasureHuntPuzzleMapperInterface $quizquestionMapper
     * @return TreasureHuntPuzzle
     */
    public function setTreasureHuntPuzzleMapper($treasureHuntPuzzleMapper)
    {
    	$this->treasureHuntPuzzleMapper = $treasureHuntPuzzleMapper;

    	return $this;
    }

    public function getTreasureHuntPuzzlePieceMapper()
    {
    	if (null === $this->treasureHuntPuzzlePieceMapper) {
    		$this->treasureHuntPuzzlePieceMapper = $this->serviceLocator->get('playgroundgame_treasurehuntpuzzle_piece_mapper');
    	}

    	return $this->treasureHuntPuzzlePieceMapper;
    }

    public function setTreasureHuntPuzzlePieceMapper($treasureHuntPuzzlePieceMapper)
    {
    	$this->treasureHuntPuzzlePieceMapper = $treasureHuntPuzzlePieceMapper;

    	return $this;
    }

    public function getTreasureHuntScoreMapper()
    {
        if (null === $this->treasurehuntScoreMapper) {
            $this->treasurehuntScoreMapper = $this->serviceLocator->get('playgroundgame_treasurehunt_score_mapper');
        }

        return $this->treasurehuntScoreMapper;
    }
}
