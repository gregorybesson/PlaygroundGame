<?php

namespace PlaygroundGame\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use PlaygroundGame\Mapper\GameInterface as GameMapperInterface;

class TreasureHunt extends Game implements ServiceManagerAwareInterface
{

    /**
     * @var TreasureHuntMapperInterface
     */
    protected $treasurehuntMapper;
    
    /**
     * @var TreasurehuntPuzzleMapper
     */
    protected $treasureHuntPuzzleMapper;

    public function getGameEntity()
    {
        return new \PlaygroundGame\Entity\TreasureHunt;
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
    	$form  = $this->getServiceManager()->get('playgroundgame_treasurehuntpuzzle_form');
    	$form->bind($puzzle);
    	$form->setData($data);
    
    	$treasurehunt = $this->getGameMapper()->findById($data['treasurehunt_id']);
    
    	if (!$form->isValid()) {
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
    
    	$form  = $this->getServiceManager()->get('playgroundgame_treasurehuntpuzzle_form');
    	$form->bind($puzzle);
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
            $this->treasurehuntMapper = $this->getServiceManager()->get('playgroundgame_treasurehunt_mapper');
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
    		$this->treasureHuntPuzzleMapper = $this->getServiceManager()->get('playgroundgame_treasurehuntpuzzle_mapper');
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
