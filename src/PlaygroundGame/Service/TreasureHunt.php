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
     * @var TreasurehuntStepMapper
     */
    protected $treasureHuntStepMapper;

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
    public function createStep(array $data)
    {
    
    	$step  = new \PlaygroundGame\Entity\TreasureHuntStep();
    	$form  = $this->getServiceManager()->get('playgroundgame_treasurehuntstep_form');
    	$form->bind($step);
    	$form->setData($data);
    
    	$treasurehunt = $this->getGameMapper()->findById($data['treasurehunt_id']);
    
    	if (!$form->isValid()) {
    		return false;
    	}
    
    	$step->setTreasurehunt($treasurehunt);
    
    	$this->getEventManager()->trigger(__FUNCTION__, $this, array('game' => $treasurehunt, 'data' => $data));
    	$this->getTreasureHuntStepMapper()->insert($step);
    	$this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('game' => $treasurehunt, 'data' => $data));
    
    	return $step;
    }
    
    /**
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function updateStep(array $data, $step)
    {
    
    	$form  = $this->getServiceManager()->get('playgroundgame_treasurehuntstep_form');
    	$form->bind($step);
    	$form->setData($data);
    
    	if (!$form->isValid()) {
    		return false;
    	}
    
    	$treasurehunt = $step->getTreasurehunt();
    
    	$this->getEventManager()->trigger(__FUNCTION__, $this, array('step' => $step, 'data' => $data));
    	$this->getTreasureHuntStepMapper()->update($step);
    	$this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('step' => $step, 'data' => $data));
    
    	return $step;
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
     * getTreasureHuntStepMapper
     *
     * @return TreasureHuntStepMapperInterface
     */
    public function getTreasureHuntStepMapper()
    {
    	if (null === $this->treasureHuntStepMapper) {
    		$this->treasureHuntStepMapper = $this->getServiceManager()->get('playgroundgame_treasurehuntstep_mapper');
    	}
    
    	return $this->treasureHuntStepMapper;
    }
    
    /**
     * setTreasureHuntStepMapper
     *
     * @param  TreasureHuntStepMapperInterface $quizquestionMapper
     * @return TreasureHuntStep
     */
    public function setTreasureHuntStepMapper($treasureHuntStepMapper)
    {
    	$this->treasureHuntStepMapper = $treasureHuntStepMapper;
    
    	return $this;
    }
}
