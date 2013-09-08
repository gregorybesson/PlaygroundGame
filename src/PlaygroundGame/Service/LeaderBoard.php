<?php

namespace PlaygroundGame\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundGame\Options\ModuleOptions;
use PlaygroundGame\Mapper\LeaderBoardInterface as LeaderBoardMapperInterface;
use Doctrine\DBAL\DBALException;

class LeaderBoard extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var LeaderBoardMapperInterface
     */
    protected $leaderBoardMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var UserServiceOptionsInterface
     */
    protected $options;

    public function create($leaderBoard)
    {

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('leaderBoard' => $leaderBoard));
        try {
            $this->getLeaderBoardMapper()->insert($leaderBoard);
        } catch (DBALException $e) {
            //$this->fail($e->getMessage());
            return null;
        }
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('leaderBoard' => $leaderBoard));

        return $leaderBoard;
    }

    public function edit($leaderBoard)
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('leaderBoard' => $leaderBoard));
        $this->getLeaderBoardMapper()->update($leaderBoard);
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('leaderBoard' => $leaderBoard));

        return $leaderBoard;
    }

    /**
     * getLeaderBoardMapper
     *
     * @return LeaderBoardMapperInterface
     */
    public function getLeaderBoardMapper()
    {
        if (null === $this->leaderBoardMapper) {
            $this->leaderBoardMapper = $this->getServiceManager()->get('playgroundgame_leaderboard_mapper');
        }

        return $this->leaderBoardMapper;
    }

    /**
     * setLeaderBoardMapper
     *
     * @param  LeaderBoardMapperInterface $leaderBoardMapper
     * @return User
     */
    public function setLeaderBoardMapper(LeaderBoardMapperInterface $leaderBoardMapper)
    {
        $this->leaderBoardMapper = $leaderBoardMapper;

        return $this;
    }

    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()->get('playgroundgame_module_options'));
        }

        return $this->options;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
