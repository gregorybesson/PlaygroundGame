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
     * @var TreasurehuntPuzzleMapper
     */
    protected $treasureHuntPuzzleMapper;

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

        if (!$entry) {
            return false;
        }

        // TODO : Replace with stepWinner
        $winner = $this->isWinner($game, $entry, $data);
        $entry->setWinner($winner);
        $entry->setDrawable($winner);

        if(($game->getReplayPuzzle() && $winner) || !$game->getReplayPuzzle()){
            if(count($game->getPuzzles()) > $entry->getStep()+1){
                $entry->setStep($entry->getStep()+1);
            } else {
                $entry->setActive(false);
            }
        }

        $entry = $entryMapper->update($entry);
        $this->getEventManager()->trigger('analyze_clue.post', $this, array('user' => $user, 'entry' => $entry, 'game' => $game));

        return $entry;
    }

    /**
     *
     * @param unknown $game
     * @param unknown $data
     * @return boolean
     */
    public function isWinner($game, $entry, $data=array())
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

    	$treasurehunt = $puzzle->getTreasurehunt();

    	$this->getEventManager()->trigger(__FUNCTION__, $this, array('puzzle' => $puzzle, 'data' => $data));
    	$this->getTreasureHuntPuzzleMapper()->update($puzzle);
    	$this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('puzzle' => $puzzle, 'data' => $data));

    	return $puzzle;
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
}
