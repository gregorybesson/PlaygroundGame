<?php

namespace PlaygroundGame\Service;

use DoctrineModule\Validator\NoObjectExists as NoObjectExistsValidator;
use Zend\ServiceManager\ServiceManager;
use Zend\EventManager\EventManagerAwareTrait;
use PlaygroundGame\Options\ModuleOptions;
use PlaygroundGame\Mapper\Prize as PrizeMapper;
use Zend\ServiceManager\ServiceLocatorInterface;

class Prize
{
    use EventManagerAwareTrait;

    /**
     * @var prizeMapper
     */
    protected $prizeMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var UserServiceOptionsInterface
     */
    protected $options;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceLocator = $locator;
    }

    /**
     *
     * This service is ready for all types of games
     *
     * @param  array                  $data
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function create(array $data, $prize, $formClass)
    {
        $form  = $this->serviceLocator->get($formClass);
        $form->bind($prize);

        // If the identifier has not been set, I use the title to create one.
        if (empty($data['identifier']) && !empty($data['title'])) {
            $data['identifier'] = $data['title'];
        }
        
        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }

        $prize = $this->getPrizeMapper()->insert($prize);

        return $prize;
    }

    /**
     *
     * @param  array                  $data
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function edit(array $data, $prize, $formClass)
    {
        $entityManager = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
        $form  = $this->serviceLocator->get($formClass);
        $form->bind($prize);
        
        $identifierInput = $form->getInputFilter()->get('identifier');
        $noObjectExistsValidator = new NoObjectExistsValidator(array(
            'object_repository' => $entityManager->getRepository('PlaygroundGame\Entity\Prize'),
            'fields'            => 'identifier',
            'messages'          => array('objectFound' => 'This url already exists !')
        ));
        
        if ($prize->getIdentifier() != $data['identifier']) {
            $identifierInput->getValidatorChain()->addValidator($noObjectExistsValidator);
        }

        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }

        $prize = $this->getPrizeMapper()->update($prize);

        return $prize;
    }


    /**
     * getPrizeMapper
     *
     * @return PrizeMapper
     */
    public function getPrizeMapper()
    {
        if (null === $this->prizeMapper) {
            $this->prizeMapper = $this->serviceLocator->get('playgroundgame_prize_mapper');
        }

        return $this->prizeMapper;
    }

    /**
     * setPrizeMapper
     *
     * @param  PrizeMapper $prizeMapper
     * @return Prize
     */
    public function setPrizeMapper(PrizeMapper $prizeMapper)
    {
        $this->prizeMapper = $prizeMapper;

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
            $this->setOptions($this->serviceLocator->get('playgroundgame_module_options'));
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
     * @return Prize
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
