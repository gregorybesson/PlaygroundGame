<?php

namespace PlaygroundGame\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundGame\Options\ModuleOptions;
use PlaygroundGame\Entity\PrizeCategoryUser as PrizeCategoryUserEntity;
use PlaygroundGame\Mapper\PrizeCategoryUser as PrizeCategoryUserMapper;

class PrizeCategoryUser extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var prizeCategoryUserMapper
     */
    protected $prizeCategoryUserMapper;
	
	/**
     * @var prizeCategoryMapper
     */
    protected $prizeCategoryMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var UserServiceOptionsInterface
     */
    protected $options;

    public function edit(array $data, $user, $formClass)
    {

        $this->getPrizeCategoryUserMapper()->removeAll($user);
		if(isset($data['prizeCategory']) && $data['prizeCategory']){
	        foreach ($data['prizeCategory'] as $k => $v) {
	            $category = $this->getPrizeCategoryMapper()->findById($v);
	            $userCategory = new PrizeCategoryUserEntity($user, $category);
	            $this->getPrizeCategoryUserMapper()->insert($userCategory);
	        }
        	$this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'data' => $data));
		}

        return true;
    }

    /**
     * getPrizeCategoryUserMapper
     *
     * @return PrizeCategoryUserMapper
     */
    public function getPrizeCategoryUserMapper()
    {
        if (null === $this->prizeCategoryUserMapper) {
            $this->prizeCategoryUserMapper = $this->getServiceManager()->get('playgroundgame_prizecategoryuser_mapper');
        }

        return $this->prizeCategoryUserMapper;
    }

    /**
     * setPrizeCategoryUserMapper
     *
     * @param PrizeCategoryUserMapper $prizeCategoryUserMapper
     *
     */
    public function setPrizeCategoryUserMapper(PrizeCategoryUserMapper $prizeCategoryUserMapper)
    {
        $this->prizeCategoryUserMapper = $prizeCategoryUserMapper;

        return $this;
    }

    /**
     * getPrizeCategoryMapper
     *
     * @return PrizeCategoryMapper
     */
    public function getPrizeCategoryMapper()
    {
        if (null === $this->prizeCategoryMapper) {
            $this->prizeCategoryMapper = $this->getServiceManager()->get('playgroundgame_prizecategory_mapper');
        }

        return $this->prizeCategoryMapper;
    }

    /**
     * setPrizeCategoryMapper
     *
     * @param  PrizeCategoryMapper $prizeCategoryMapper
     * @return User
     */
    public function setPrizeCategoryMapper(PrizeCategoryMapper $prizeCategoryMapper)
    {
        $this->prizeCategoryMapper = $prizeCategoryMapper;

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
