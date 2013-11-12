<?php

namespace PlaygroundGame\Service;

use PlaygroundGame\Entity\Entry;

use Zend\Session\Container;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundGame\Options\ModuleOptions;
use PlaygroundGame\Mapper\GameInterface as GameMapperInterface;
use DoctrineModule\Validator\NoObjectExists as NoObjectExistsValidator;
use Zend\File\Transfer\Adapter\Http;
use Zend\Validator\File\Size;
use Zend\Validator\File\IsImage;
use Zend\Stdlib\ErrorHandler;
use PlaygroundCore\Filter\Sanitize;

class Game extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var GameMapperInterface
     */
    protected $gameMapper;

    /**
     * @var EntryMapperInterface
     */
    protected $entryMapper;

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
     * This service is ready for all types of games
     *
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function create(array $data, $entity, $formClass)
    {
        $game  = new $entity;
        $entityManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        $form  = $this->getServiceManager()->get($formClass);
        // I force the following format because this is the only one accepted by new DateTime($value) used by Doctrine when persisting
        $form->get('publicationDate')->setOptions(array('format' => 'Y-m-d'));
        $form->get('startDate')->setOptions(array('format' => 'Y-m-d'));
        $form->get('endDate')->setOptions(array('format' => 'Y-m-d'));
        $form->get('closeDate')->setOptions(array('format' => 'Y-m-d'));
        $count = isset($data['prizes'])?count($data['prizes']):0;
        if($form->get('prizes')){
            $form->get('prizes')->setCount($count)->prepareFieldset();
        }
        $form->bind($game);

        $path = $this->getOptions()->getMediaPath() . '/';
        $media_url = $this->getOptions()->getMediaUrl() . '/';

        $identifierInput = $form->getInputFilter()->get('identifier');
        $noObjectExistsValidator = new NoObjectExistsValidator(array(
            'object_repository' => $entityManager->getRepository('PlaygroundGame\Entity\Game'),
            'fields'            => 'identifier',
            'messages'          => array('objectFound' => 'This url already exists !')
        ));

        $identifierInput->getValidatorChain()->addValidator($noObjectExistsValidator);

        // I must switch from original format to the Y-m-d format because this is the only one accepted by new DateTime($value)
        if (isset($data['publicationDate']) && $data['publicationDate']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y', $data['publicationDate']);
            $data['publicationDate'] = $tmpDate->format('Y-m-d');
        }
        if (isset($data['startDate']) && $data['startDate']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y', $data['startDate']);
            $data['startDate'] = $tmpDate->format('Y-m-d');
        }
        if (isset($data['endDate']) && $data['endDate']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y', $data['endDate']);
            $data['endDate'] = $tmpDate->format('Y-m-d');
        }
        if (isset($data['closeDate']) && $data['closeDate']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y', $data['closeDate']);
            $data['closeDate'] = $tmpDate->format('Y-m-d');
        }

        // If publicationDate is null, I update it with the startDate if not null neither
        if (!isset($data['publicationDate']) && isset($data['startDate'])) {
            $data['publicationDate'] = $data['startDate'];
        }

        // If the identifier has not been set, I use the title to create one.
        if (empty($data['identifier']) && !empty($data['title'])) {
            $data['identifier'] = $data['title'];
        }

        $form->setData($data);

        if (!$form->isValid()) {
            if (isset($data['publicationDate']) && $data['publicationDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['publicationDate']);
                $data['publicationDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array('publicationDate' => $data['publicationDate']));
            }
            if (isset($data['startDate']) && $data['startDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['startDate']);
                $data['startDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array('startDate' => $data['startDate']));
            }
            if (isset($data['endDate']) && $data['endDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['endDate']);
                $data['endDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array('endDate' => $data['endDate']));
            }
            if (isset($data['closeDate']) && $data['closeDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['closeDate']);
                $data['closeDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array('closeDate' => $data['closeDate']));
            }
            return false;
        }
        
        $game = $this->getGameMapper()->insert($game);

        // If I receive false, it means that the FB Id was not available anymore
        $result = $this->getEventManager()->trigger(__FUNCTION__, $this, array('game' => $game));
        if(!$result) return false;


        // I wait for the game to be saved to obtain its ID.
        if (!empty($data['uploadStylesheet']['tmp_name'])) {
            ErrorHandler::start();
            move_uploaded_file($data['uploadStylesheet']['tmp_name'], $path . 'stylesheet_'. $game->getId() .'.css');
            $game->setStylesheet($media_url . 'stylesheet_'. $game->getId() .'.css');
            ErrorHandler::stop(true);
        }

        if (!empty($data['uploadMainImage']['tmp_name'])) {
            ErrorHandler::start();
			$data['uploadMainImage']['name'] = $this->fileNewname($path, $game->getId() . "-" . $data['uploadMainImage']['name']);
            move_uploaded_file($data['uploadMainImage']['tmp_name'], $path . $data['uploadMainImage']['name']);
            $game->setMainImage($media_url . $data['uploadMainImage']['name']);
            ErrorHandler::stop(true);
        }

        if (!empty($data['uploadSecondImage']['tmp_name'])) {
            ErrorHandler::start();
			$data['uploadSecondImage']['name'] = $this->fileNewname($path, $game->getId() . "-" . $data['uploadSecondImage']['name']);
            move_uploaded_file($data['uploadSecondImage']['tmp_name'], $path . $data['uploadSecondImage']['name']);
            $game->setSecondImage($media_url . $data['uploadSecondImage']['name']);
            ErrorHandler::stop(true);
        }

        if (!empty($data['uploadFbShareImage']['tmp_name'])) {
            ErrorHandler::start();
			$data['uploadFbShareImage']['name'] = $this->fileNewname($path, $game->getId() . "-" . $data['uploadFbShareImage']['name']);
            move_uploaded_file($data['uploadFbShareImage']['tmp_name'], $path . $data['uploadFbShareImage']['name']);
            $game->setFbShareImage($media_url . $data['uploadFbShareImage']['name']);
            ErrorHandler::stop(true);
        }

        if (!empty($data['uploadFbPageTabImage']['tmp_name'])) {
            ErrorHandler::start();
            $extension = $this->getExtension ( strtolower ( $data['uploadFbPageTabImage']['name'] ) );
            $src = $this->get_src ($extension, $data['uploadFbPageTabImage']['tmp_name']);
            $this->resize($data['uploadFbPageTabImage']['tmp_name'],$extension, $path . $game->getId() . "-" . $data['uploadFbPageTabImage']['name'], $src,  111, 74);

            //move_uploaded_file($data['uploadFbPageTabImage']['tmp_name'], $path . $game->getId() . "-" . $data['uploadFbPageTabImage']['name']);

            $game->setFbPageTabImage($media_url . $game->getId() . "-" . $data['uploadFbPageTabImage']['name']);
            ErrorHandler::stop(true);
        }
        $game = $this->getGameMapper()->update($game);

        $prize_mapper = $this->getServiceManager()->get('playgroundgame_prize_mapper');
        if(isset($data['prizes'])){
            foreach($data['prizes'] as $prize_data ){          
                if (!empty($prize_data['picture']['tmp_name'])){
                    if ($prize_data['id']){
                        $prize = $prize_mapper->findById($prize_data['id']);
                    }else{
                        $some_prizes = $prize_mapper->findBy(array(
                                'game' => $game,
                                'title' => $prize_data['title'],
                            ));
                        if (count($some_prizes)==1){
                            $prize=$some_prizes[0];
                        }else{
                            return false;
                        }               
                    }
                    ErrorHandler::start();
                    $filename = "game-".$game->getId()."-prize-" . $prize->getId()."-".$prize_data['picture']['name'];
                    $filepath = $this->fileNewname($path, $filename);
                    move_uploaded_file($prize_data['picture']['tmp_name'], $path.$filename);
                    $prize->setPicture($media_url.$filename);
                    ErrorHandler::stop(true);
                    $prize = $prize_mapper->update($prize);
                }
            }
        }

        return $game;
    }

    /**
     *
     * This service is ready for all types of games
     *
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function edit(array $data, $game, $formClass)
    {
        $entityManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $form  = $this->getServiceManager()->get($formClass);
        $form->get('publicationDate')->setOptions(array('format' => 'Y-m-d'));
        $form->get('startDate')->setOptions(array('format' => 'Y-m-d'));
        $form->get('endDate')->setOptions(array('format' => 'Y-m-d'));
        $form->get('closeDate')->setOptions(array('format' => 'Y-m-d'));
        $count = isset($data['prizes'])?count($data['prizes']):0;
        if($form->get('prizes')){
            $form->get('prizes')->setCount($count)->prepareFieldset();
        }
        $form->bind($game);

        $path = $this->getOptions()->getMediaPath() . '/';
        $media_url = $this->getOptions()->getMediaUrl() . '/';
        
        $identifierInput = $form->getInputFilter()->get('identifier');
        $noObjectExistsValidator = new NoObjectExistsValidator(array(
            'object_repository' => $entityManager->getRepository('PlaygroundGame\Entity\Game'),
            'fields'            => 'identifier',
            'messages'          => array('objectFound' => 'This url already exists !')
        ));
        
        if($game->getIdentifier() != $data['identifier']){
            $identifierInput->getValidatorChain()->addValidator($noObjectExistsValidator);
        }
    
        // I must switch from original format to the Y-m-d format because this is the only one accepted by new DateTime($value)
        if (isset($data['publicationDate']) && $data['publicationDate']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y', $data['publicationDate']);
            $data['publicationDate'] = $tmpDate->format('Y-m-d');
        }
        if (isset($data['startDate']) && $data['startDate']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y', $data['startDate']);
            $data['startDate'] = $tmpDate->format('Y-m-d');
        }
        if (isset($data['endDate']) && $data['endDate']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y', $data['endDate']);
            $data['endDate'] = $tmpDate->format('Y-m-d');
        }
        if (isset($data['closeDate']) && $data['closeDate']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y', $data['closeDate']);
            $data['closeDate'] = $tmpDate->format('Y-m-d');
        }

        // If publicationDate is null, I update it with the startDate if not nul neither
        if ((!isset($data['publicationDate']) || $data['publicationDate'] == '') && (isset($data['startDate']) && $data['startDate'] != '')) {
            $data['publicationDate'] = $data['startDate'];
        }

        if (!isset($data['identifier']) && isset($data['title'])) {
            $data['identifier'] = $data['title'];
        }

        $form->setData($data);

        // If someone want to claim... It's time to do it ! used for exemple by PlaygroundFacebook Module
        $result = $this->getEventManager()->trigger(__FUNCTION__.'.validate', $this, array('game' => $game, 'data' => $data));
        if (is_array($result) && !$result[0]) {
            $form->get('fbAppId')->setMessages(array('Vous devez d\'abord désinstaller l\'appli Facebook'));

            return false;
        }

        if (!$form->isValid()) {            
            if (isset($data['publicationDate']) && $data['publicationDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['publicationDate']);
                $data['publicationDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array('publicationDate' => $data['publicationDate']));
            }
            if (isset($data['startDate']) && $data['startDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['startDate']);
                $data['startDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array('startDate' => $data['startDate']));
            }
            if (isset($data['endDate']) && $data['endDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['endDate']);
                $data['endDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array('endDate' => $data['endDate']));
            }
            if (isset($data['closeDate']) && $data['closeDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['closeDate']);
                $data['closeDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array('closeDate' => $data['closeDate']));
            }
            return false;
        }

        if (!empty($data['uploadMainImage']['tmp_name'])) {
            ErrorHandler::start();
			$data['uploadMainImage']['name'] = $this->fileNewname($path, $game->getId() . "-" . $data['uploadMainImage']['name']);
            move_uploaded_file($data['uploadMainImage']['tmp_name'], $path . $data['uploadMainImage']['name']);
            $game->setMainImage($media_url . $data['uploadMainImage']['name']);
            ErrorHandler::stop(true);
        }

        if (!empty($data['uploadSecondImage']['tmp_name'])) {
            ErrorHandler::start();
			$data['uploadSecondImage']['name'] = $this->fileNewname($path, $game->getId() . "-" . $data['uploadSecondImage']['name']);
            move_uploaded_file($data['uploadSecondImage']['tmp_name'], $path . $data['uploadSecondImage']['name']);
            $game->setSecondImage($media_url . $data['uploadSecondImage']['name']);
            ErrorHandler::stop(true);
        }

        if (!empty($data['uploadStylesheet']['tmp_name'])) {
            ErrorHandler::start();
            move_uploaded_file($data['uploadStylesheet']['tmp_name'], $path . 'stylesheet_'. $game->getId() .'.css');
            $game->setStylesheet($media_url . 'stylesheet_'. $game->getId() .'.css');
            ErrorHandler::stop(true);
        }

        if (!empty($data['uploadFbShareImage']['tmp_name'])) {
            ErrorHandler::start();
			$data['uploadFbShareImage']['name'] = $this->fileNewname($path, $game->getId() . "-" . $data['uploadFbShareImage']['name']);
            move_uploaded_file($data['uploadFbShareImage']['tmp_name'], $path . $data['uploadFbShareImage']['name']);
            $game->setFbShareImage($media_url . $data['uploadFbShareImage']['name']);
            ErrorHandler::stop(true);
        }

        if (!empty($data['uploadFbPageTabImage']['tmp_name'])) {
            ErrorHandler::start();

            $extension = $this->getExtension ( strtolower ( $data['uploadFbPageTabImage']['name'] ) );
            $src = $this->get_src ($extension, $data['uploadFbPageTabImage']['tmp_name']);
            $this->resize($data['uploadFbPageTabImage']['tmp_name'],$extension, $path . $game->getId() . "-" . $data['uploadFbPageTabImage']['name'], $src,  111, 74);
            //move_uploaded_file($data['uploadFbPageTabImage']['tmp_name'], $path . $game->getId() . "-" . $data['uploadFbPageTabImage']['name']);

            $game->setFbPageTabImage($media_url . $game->getId() . "-" . $data['uploadFbPageTabImage']['name']);
            ErrorHandler::stop(true);
        }

        /*if ($fileName) {
            $adapter = new \Zend\File\Transfer\Adapter\Http();
            $size = new Size(array('max'=>2000000));
            $adapter->setValidators(array($size), $fileName);

            if (!$adapter->isValid()) {
                $dataError = $adapter->getMessages();
                $error = array();
                foreach ($dataError as $key=>$row) {
                    $error[] = $row;
                }
                $form->setMessages(array('main_image'=>$error ));
            } else {
                $adapter->setDestination($path);
                if ($adapter->receive($fileName)) {
                    $game = $this->getGameMapper()->update($game);

                    return $game;
                }
            }
        } else {
            $game = $this->getGameMapper()->update($game);

            return $game;
        }*/

        $game = $this->getGameMapper()->update($game);
        
        $prize_mapper = $this->getServiceManager()->get('playgroundgame_prize_mapper');
        if(isset($data['prizes'])){
            foreach($data['prizes'] as $prize_data ){  
                if (!empty($prize_data['picture_file']['tmp_name']) && !$prize_data['picture_file']['error']){
                    if ($prize_data['id']){
                        $prize = $prize_mapper->findById($prize_data['id']);
                    }else{
                        $some_prizes = $prize_mapper->findBy(array(
                                'game' => $game,
                                'title' => $prize_data['title'],
                            ));
                        if (count($some_prizes)==1){
                            $prize=$some_prizes[0];
                        }else{
                            return false;
                        }               
                    }
                    // Remove if existing image
                    if ($prize->getPicture() && file_exists($prize->getPicture())){
                        unlink($prize->getPicture());
                        $prize->getPicture(null);
                    }
                    // Upload and set new 
                    ErrorHandler::start();
                    $filename = "game-".$game->getId()."-prize-" . $prize->getId()."-".$prize_data['picture_file']['name'];
                    $filepath = $this->fileNewname($path, $filename);
                    move_uploaded_file($prize_data['picture_file']['tmp_name'], $path.$filename);
                    $prize->setPicture($media_url.$filename);
                    ErrorHandler::stop(true);
                    $prize = $prize_mapper->update($prize);
                }
            }
        }
        // If I receive false, it means that the FB Id was not available anymore
        $result = $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('game' => $game));

        return $game;
    }

    /**
     * getActiveGames
     *
     * @return Array of PlaygroundGame\Entity\Game
     */
    public function getActiveGames($displayHome = true, $classType='', $order='', $withoutGameInMission = false)
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $today = new \DateTime("now");
        //$today->format('Y-m-d H:i:s');
        $today = $today->format('Y-m-d') . ' 23:59:59';

        $classClause='';
        $displayHomeClause='';
        $displayWithoutMission='';
        $orderBy ='publicationDate';

        if ($classType != '') {
            $classClause = " AND g.classType = '" . $classType . "'";
        }
        if ($displayHome) {
            $displayHomeClause = " AND g.displayHome = true";
        }

        if ($withoutGameInMission){
            $displayWithoutMission = " AND g.id NOT IN (SELECT IDENTITY(mg.game) 
                                 FROM PlaygroundGame\Entity\MissionGame mg, PlaygroundGame\Entity\Mission m 
                                 WHERE mg.mission = m.id
                                 AND m.active = 1 ) "; 
        }

        if ($order != '') {
            $orderBy = $order;
        }

        // Game active with a startDate before today (or without startDate) and closeDate after today (or without closeDate)
        $query = $em->createQuery(
            'SELECT g FROM PlaygroundGame\Entity\Game g
                WHERE (g.publicationDate <= :date OR g.publicationDate IS NULL)
                AND (g.closeDate >= :date OR g.closeDate IS NULL)
                AND g.active = 1
                AND g.broadcastPlatform = 1'
                . $displayHomeClause
                . $classClause
                . $displayWithoutMission
                .' ORDER BY g.'
                . $orderBy
                . ' DESC'
        );
        $query->setParameter('date', $today);
        $games = $query->getResult();

        //je les classe par date de publication (date comme clé dans le tableau afin de pouvoir merger les objets
        // de type article avec le même procédé en les classant naturellement par date asc ou desc
       $arrayGames = array();
       foreach ($games as $game) {
           if ($game->getPublicationDate()) {
               $key = $game->getPublicationDate()->format('Ymd').$game->getUpdatedAt()->format('Ymd').'-'.$game->getId();
           } elseif ($game->getStartDate()) {
               $key = $game->getStartDate()->format('Ymd') . $game->getUpdatedAt()->format('Ymd').'-'.$game->getId();
           } else {
               $key = $game->getUpdatedAt()->format('Ymd') . $game->getUpdatedAt()->format('Ymd').'-'.$game->getId();
           }
           $arrayGames[$key] = $game;
       }

        return $arrayGames;
    }



    /**
     * getAvailableGames : Games OnLine and not already played by $user
     *
     * @return Array of PlaygroundGame\Entity\Game
     */
    public function getAvailableGames($user, $maxResults = 2)
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $today = new \DateTime("now");
        //$today->format('Y-m-d H:i:s');
        $today = $today->format('Y-m-d') . ' 23:59:59';

        // Game active with a start_date before today (or without start_date) and end_date after today (or without end-date)
        $query = $em->createQuery(
            'SELECT g FROM PlaygroundGame\Entity\Game g
                WHERE NOT EXISTS (SELECT l FROM PlaygroundGame\Entity\Entry l WHERE l.game = g AND l.user = :user)
                AND (g.startDate <= :date OR g.startDate IS NULL)
                AND (g.endDate >= :date OR g.endDate IS NULL)
                AND g.active = 1 AND g.broadcastPlatform = 1
                ORDER BY g.startDate ASC'
        );
        $query->setParameter('date', $today);
        $query->setParameter('user', $user);
        $query->setMaxResults($maxResults);
        $games = $query->getResult();

        return $games;
    }

    /**
     * getActiveSliderGames
     *
     * @return Array of PlaygroundGame\Entity\Game
     */
    public function getActiveSliderGames()
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $today = new \DateTime("now");
        //$today->format('Y-m-d H:i:s');
        $today = $today->format('Y-m-d') . ' 23:59:59';

        // Game active with a start_date before today (or without start_date) and end_date after today (or without end-date)
        $query = $em->createQuery(
            'SELECT g FROM PlaygroundGame\Entity\Game g
            WHERE (g.publicationDate <= :date OR g.publicationDate IS NULL)
            AND (g.closeDate >= :date OR g.closeDate IS NULL)
            AND g.active = true AND g.broadcastPlatform = 1 AND g.pushHome = true'
        );
        $query->setParameter('date', $today);
        $games = $query->getResult();

        //je les classe par date de publication (date comme clé dans le tableau afin de pouvoir merger les objets
        // de type article avec le même procédé en les classant naturellement par date asc ou desc
        $arrayGames = array();
        foreach ($games as $game) {
            if ($game->getPublicationDate()) {
                $key = $game->getPublicationDate()->format('Ymd').$game->getUpdatedAt()->format('Ymd').'-'.$game->getId();
            } elseif ($game->getStartDate()) {
                $key = $game->getStartDate()->format('Ymd') . $game->getUpdatedAt()->format('Ymd').'-'.$game->getId();
            } else {
                $key = $game->getUpdatedAt()->format('Ymd') . $game->getUpdatedAt()->format('Ymd').'-'.$game->getId();
            }
            $arrayGames[$key] = $game;
        }

        return $arrayGames;
    }

    /**
     * getPrizeCategoryGames
     *
     * @return Array of PlaygroundGame\Entity\Game
     */
    public function getPrizeCategoryGames($categoryid)
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        $query = $em->createQuery(
            'SELECT g FROM PlaygroundGame\Entity\Game g
            WHERE (g.prizeCategory = :categoryid AND g.broadcastPlatform = 1)
            ORDER BY g.publicationDate DESC'
        );
        $query->setParameter('categoryid', $categoryid);
        $games = $query->getResult();

        return $games;
    }

    public function checkGame($identifier, $checkIfStarted=true)
    {
        $gameMapper = $this->getGameMapper();
        $gameEntity = $this->getGameEntity();
        $today      = new \Datetime('now');

        if (!$identifier) {
            return false;
        }

        $game = $gameMapper->findByIdentifier($identifier);

        // the game has not been found
        if (!$game) {
            return false;
        }
        
        // the game is not of the right type
        if (!$game instanceof $gameEntity) {
            return false;
        }

        if ( $this->getServiceManager()->get('Application')->getMvcEvent()->getRouteMatch()->getParam('channel') === 'preview' 
             && $this->isAllowed('game', 'edit')){
            
            $game->setActive(true);
            $game->setStartDate(null);
            $game->setEndDate(null);
            $game->setPublicationDate(null);
            $game->setBroadcastPlatform(true);
            
            // I don't want the game to be updated through any update during the preview mode. I mark it as readonly for Doctrine
            $this->getServiceManager()->get('doctrine.entitymanager.orm_default')->getUnitOfWork()->markReadOnly($game);
            return $game;
        }
   
        // The game is inactive
        if (!$game->getActive()) {
            return false;
        }

        // the game has not begun yet
        if (!$game->isOpen()) {
            return false;
        }

        // the game is finished and closed
        if (!$game->isStarted() && $checkIfStarted) {
            return false;
        }

        return $game;
    }

    /**
     * Return the last entry of the user on this game, if it exists
     * If the param active is set, it can check if the entry is active or not.
     * @param  unknown $game
     * @param  string  $user
     * @return boolean
     */
    public function checkExistingEntry($game, $user=null, $active=null)
    {
        $entry = false;

        if (! is_null($active)) {
            $search = array('game' => $game, 'user' => $user, 'active' => $active);
        } else {
            $search = array('game' => $game, 'user' => $user);
        }

        if ($user) {
            $entry = $this->getEntryMapper()->findOneBy($search);
        }

        return $entry;
    }
    
    public function checkIsFan($game)
    {
        // If on Facebook, check if you have to be a FB fan to play the game
        $session = new Container('facebook');
        
        if ($session->offsetExists('signed_request')) {
            // I'm on Facebook
            $sr = $session->offsetGet('signed_request');
            if($sr['page']['liked'] == 1){
    
                return true;
            }
        } else{
            // I'm not on Facebook
            return true;
        }
    
        return false;
    }

    /**
     * errors :
     *     -1 : user not connected
     *     -2 : limit entry games for this user reached
     *
     * @param  PlaygroundGame\Entity\Game $game
     * @param  PlaygroundUser\Entity\UserInterface $user
     * @return number|unknown
     */
    public function play($game, $user)
    {

        // certaines participations peuvent rester ouvertes. On autorise alors le joueur à reprendre là ou il en était
        // par exemple les postvote...
        $entry = $this->checkExistingEntry($game, $user, true);

        if (! $entry) {
            // je regarde s'il y a une limitation sur le jeu
            $limitAmount = $game->getPlayLimit();
            if ($limitAmount) {
                $limitScale  = $game->getPlayLimitScale();
                $userEntries = $this->getEntryMapper()->findLastEntriesBy($game, $user, $limitScale);

                // player has reached the game limit
                if ($userEntries >= $limitAmount) {
                    return false;
                }
            }

            $entry = new Entry();
            $entry->setGame($game);
            $entry->setUser($user);
            $entry->setPoints(0);

            $entry = $this->getEntryMapper()->insert($entry);
            $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'game' => $game));
        }

        return $entry;
    }

    public function sendShareMail($data, $game, $user, $template = 'share_game', $topic = NULL, $userTimer = array())
    {

        $mailService = $this->getServiceManager()->get('playgroundgame_message');
        $mailSent    = false;
        $from        = $this->getOptions()->getEmailFromAddress();
        $subject     = $this->getOptions()->getShareSubjectLine();
        $renderer    = $this->getServiceManager()->get('Zend\View\Renderer\RendererInterface');
        $skinUrl     = $renderer->url('frontend', array(), array('force_canonical' => true));
        $secretKey   = strtoupper(substr(sha1($user->getId().'####'.time()),0,15));


        if (!$topic) {
            $topic = $game->getTitle();
        }

        if ($data['email1']) {
            $mailSent = true;
            $message = $mailService->createHtmlMessage($from, $data['email1'], $subject, 'playground-game/email/'.$template, array('game' => $game, 'email' => $user->getEmail(), 'secretKey' => $secretKey, 'skinUrl' => $skinUrl, 'userTimer' => $userTimer));
            $mailService->send($message);
        }
        if ($data['email2'] && $data['email2'] != $data['email1']) {
            $mailSent = true;
            $message = $mailService->createHtmlMessage($from, $data['email2'], $subject, 'playground-game/email/'.$template, array('game' => $game, 'email' => $user->getEmail(), 'secretKey' => $secretKey, 'skinUrl' => $skinUrl, 'userTimer' => $userTimer));
            $mailService->send($message);
        }
        if ($data['email3'] && $data['email3'] != $data['email2'] && $data['email3'] != $data['email1']) {
            $mailSent = true;
            $message = $mailService->createHtmlMessage($from, $data['email3'], $subject, 'playground-game/email/'.$template, array('game' => $game, 'email' => $user->getEmail(), 'secretKey' => $secretKey, 'skinUrl' => $skinUrl, 'userTimer' => $userTimer));
            $mailService->send($message);
        }
        if ($mailSent) {
            $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'topic' => $topic, 'secretKey' => $secretKey, 'game' => $game));
            
            return true;
        }

        return false;
    }

    public function sendGameMail($game, $user, $post, $template = 'postvote')
    {

        $mailService = $this->getServiceManager()->get('playgroundgame_message');
        $from        = $this->getOptions()->getEmailFromAddress();
        $to          = $user->getEmail();
        $subject     = $this->getOptions()->getParticipationSubjectLine();
        $renderer    = $this->getServiceManager()->get('Zend\View\Renderer\RendererInterface');
        $skinUrl     = $renderer->url('frontend', array(), array('force_canonical' => true));

        $message = $mailService->createHtmlMessage($from, $to, $subject, 'playground-game/email/'.$template, array('game' => $game, 'post' => $post, 'skinUrl' => $skinUrl));
        $mailService->send($message);
    }

    public function postFbWall($secretKey, $game, $user, $topic = NULL)
    {
        if (!$topic) {
            $topic = $game->getTitle();
        }

        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'game' => $game, 'secretKey' => $secretKey, 'topic' => $topic));

        return true;
    }

    public function postFbRequest($secretKey, $game, $user)
    {
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'game' => $game, 'secretKey' => $secretKey));

        return true;
    }

    public function postTwitter($secretKey, $game, $user, $topic = NULL)
    {
        if (!$topic) {
            $topic = $game->getTitle();
        }

        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'game' => $game, 'secretKey' => $secretKey, 'topic' => $topic));

        return true;
    }

    public function postGoogle($secretKey, $game, $user, $topic = NULL)
    {
        if (!$topic) {
            $topic = $game->getTitle();
        }

        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'game' => $game, 'secretKey' => $secretKey, 'topic' => $topic));

        return true;
    }

    /**
     * Is it possible to trigger a bonus entry ?
     * @param unknown_type $game
     * @param unknown_type $user
     */
    public function allowBonus($game, $user)
    {

        if (!$game->getPlayBonus() || $game->getPlayBonus() == 'none') {
            return false;
        } elseif ($game->getPlayBonus() == 'one') {
            if ($this->getEntryMapper()->findOneBy(array('game' => $game, 'user' => $user, 'bonus' => 1))) {
                return false;
            } else {
                return true;
            }
        } elseif ($game->getPlayBonus() == 'per_entry') {
            return $this->getEntryMapper()->checkBonusEntry($game,$user);
        }

        return false;
    }

    /**
     * This bonus entry doesn't give points nor badges
     * It's just there to increase the chances during the Draw
     *
     * @param  PlaygroundGame\Entity\Game $game
     * @param  unknown               $user
     * @return number|unknown
     */
    public function playBonus($game, $user, $winner = 0)
    {

        if ($this->allowBonus($game, $user)) {
            $entry = new Entry();
            $entry->setGame($game);
            $entry->setUser($user);
            $entry->setPoints(0);
            $entry->setActive(0);
            $entry->setBonus(1);
            $entry->setWinner($winner);

            $entry = $this->getEntryMapper()->insert($entry);

            return true;
        }

        return false;
    }

    //TODO : Terminer et Refactorer afin de le mettre dans PlaygroundCore
    public static function cronMail()
    {
        //TODO : factoriser la config
        $configuration = require 'config/application.config.php';
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
        $sm = new \Zend\ServiceManager\ServiceManager(new \Zend\Mvc\Service\ServiceManagerConfig($smConfig));
        $sm->setService('ApplicationConfig', $configuration);
        $sm->get('ModuleManager')->loadModules();
        $sm->get('Application')->bootstrap();

        $mailService = $sm->get('playgrounduser_message');
        $gameService = $sm->get('playgroundgame_quiz_service');
        $options = $sm->get('playgroundgame_module_options');

        $from    = "admin@playground.fr";//$options->getEmailFromAddress();
        $subject = "sujet game"; //$options->getResetEmailSubjectLine();

        $to = "gbesson@test.com";

        $game = $gameService->checkGame('qooqo');

        // On recherche les joueurs qui n'ont pas partagé leur qquiz après avoir joué
        // entry join user join game : distinct user et game et game_entry = 0 et updated_at <= jour-1 et > jour - 2
        //$contacts = getQuizUsersNotSharing();

        //foreach ($contacts as $contact) {
            //$message = $mailService->createTextMessage('titi@test.com', 'gbesson@test.com', 'sujetcron', 'playground-user/email/forgot', array());
            $message = $mailService->createTextMessage($from, $to, $subject, 'playground-game/email/share_reminder', array('game' => $game));

            $mailService->send($message);
        //}

    }

    public function uploadFile($path, $file)
    {
        $err = $file["error"];
        $message='';
        if ($err > 0) {
            switch ($err) {
                case '1':
                    $message.='Max file size exceeded. (php.ini)';
                    break;
                case '2':
                    $message.='Max file size exceeded.';
                    break;
                case '3':
                    $message.='File upload was only partial.';
                    break;
                case '4':
                    $message.='No file was attached.';
                    break;
                case '7':
                    $message.='File permission denied.';
                    break;
                default :
                    $message.='Unexpected error occurs.';
            }

            return $err;
        } else {

			$fileNewname = $this->fileNewname($path, $file['name'], true);
            $adapter = new \Zend\File\Transfer\Adapter\Http();
            // 500ko
            $size = new Size(array('max'=>512000));
            $is_image = new IsImage('jpeg,png,gif,jpg');
            $adapter->setValidators(array($size, $is_image), $fileNewname);

            if (!$adapter->isValid()) {
                $dataError = $adapter->getMessages();
                $error = array();
                foreach ($dataError as $key=>$row) {
                    $error[] = $row;
                }

                return false;
            }
			
			@move_uploaded_file($file["tmp_name"],$path.$fileNewname);

            
           
        }

        return $fileNewname;
    }

	public function fileNewname($path, $filename, $generate = false){
		$sanitize = new Sanitize();
		$name = $sanitize->filter($filename);
		$newpath = $path.$name;
		
		if($generate){
		    if(file_exists($newpath)) {
		    	$filename = pathinfo($name, PATHINFO_FILENAME);
				$ext = pathinfo($name, PATHINFO_EXTENSION);
		    	
		        $name = $filename .'_'. rand(0, 99) .'.'. $ext;
		    }
		}
		
		unset($sanitize);
		
	    return $name;
	}

    /**
     * TODO : Remove this method from the service
     */
    public function findBy($array, $sort)
    {
         return $this->getGameMapper()->findBy($array, $sort);
    }

    /**
     * TODO : Remove this method from the service
     */
    public function findAll()
    {
        return $this->getGameMapper()->findAll();
    }

    /**
     * TODO : Remove this method from the service
     */
    public function findAllEntry()
    {
        return $this->getEntryMapper()->findAll();
    }
    
    /**
     * This function returns the list of games, order by $type
     */
    public function getQueryGamesOrderBy($type='createdAt', $order='DESC')
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $today = new \DateTime("now");
        $today = $today->format('Y-m-d') . ' 23:59:59';
        
        $onlineGames = '(
            (
                CASE WHEN (
                    g.active = 1
                    AND g.broadcastPlatform = 1
                    AND (g.startDate <= :date OR g.startDate IS NULL)
                    AND (g.closeDate >= :date OR g.closeDate IS NULL)
                ) THEN 1 ELSE 0 END
            ) +
            (
                CASE WHEN (
                    g.active = 0
                    AND (g.broadcastPlatform = 0 OR g.broadcastPlatform IS NULL)
                    AND g.startDate > :date
                    AND g.closeDate < :date
                ) THEN 1 ELSE 0 END
            )
        )';
        
        switch ($type) {
            case 'startDate' :
                $filter = 'g.startDate';
                break;
            case 'activeGames' :
                $filter = 'g.active';
                break;
            case 'onlineGames' :
                $filter = $onlineGames;
                break;
            case 'createdAt' :
                $filter = 'g.createdAt';
                break;
        }
        
        $query = $em->createQuery('
            SELECT g FROM PlaygroundGame\Entity\Game g
            ORDER BY '.$filter.' '.$order.'
        ');
        if($filter == $onlineGames) {
            $query->setParameter('date', $today);
        }
        return $query;
    }
    
    /**
     * This function returns the list of games, order by $type
     */
    public function getGamesOrderBy($type='createdAt', $order='DESC')
    {
        return $this->getQueryGamesOrderBy($type,$order)->getResult();
    }
    
    /**
     * This function returns the user's first entry if it's his first participation in $game
     * @param  unknown_type $game
     */
    public function findFirstEntries($game)
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        
        $query = $em->createQuery('
            SELECT e
            FROM PlaygroundGame\Entity\Entry e
            WHERE e.id IN (SELECT l.id FROM PlaygroundGame\Entity\Entry l GROUP BY l.user)
            AND e.game = :game
            ORDER BY e.created_at ASC
        ');
                
        $query->setParameter('game', $game);
        $result = $query->getResult();
        return $result;
    }
    
    public function draw($game)
    {
        $total = $game->getWinners();
    
        // I Have to know what is the User Class used
        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
        $userClass = $zfcUserOptions->getUserEntityClass();
    
        //$entry = $this->getEntryMapper()->findById(180);
        //echo 'updated : ' . $entry->getId() . $entry->getWinner() . " p : " . $entry->getPoints();
        
        $result = $this->getEntryMapper()->draw($game, $userClass, $total);

        $entries=array();
        foreach($result as $e){
            $e->setWinner(1);
            $e = $this->getEntryMapper()->update($e);
            $this->getEventManager()->trigger('win_lottery.post', $this, array('user' => $e->getUser(), 'game' => $game, 'entry' =>$e));
        }

        return $result;
    }

    /**
     * getGameMapper
     *
     * @return GameMapperInterface
     */
    public function getGameMapper()
    {
        if (null === $this->gameMapper) {
            $this->gameMapper = $this->getServiceManager()->get('playgroundgame_game_mapper');
        }

        return $this->gameMapper;
    }

    /**
     * setGameMapper
     *
     * @param  GameMapperInterface $gameMapper
     * @return User
     */
    public function setGameMapper(GameMapperInterface $gameMapper)
    {
        $this->gameMapper = $gameMapper;

        return $this;
    }

    /**
     * getEntryMapper
     *
     * @return EntryMapperInterface
     */
    public function getEntryMapper()
    {
        if (null === $this->entryMapper) {
            $this->entryMapper = $this->getServiceManager()->get('playgroundgame_entry_mapper');
        }

        return $this->entryMapper;
    }

    /**
     * setEntryMapper
     *
     * @param  EntryMapperInterface $entryMapper
     * @return Entry
     */
    public function setEntryMapper($entryMapper)
    {
        $this->entryMapper = $entryMapper;

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
     * @return Game
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    public function getExtension($str)
    {
        $i = strrpos($str,'.');

        $l = strlen($str) - $i;
        $ext = substr($str,$i+1,$l);

        return $ext;
    }

    public function get_src($extension,$temp_path)
    {
         $image_src = '';
         switch ($extension) {
             case 'jpg':
                 $image_src = imagecreatefromjpeg($temp_path);
                 break;
             case 'jpeg':
                 $image_src = imagecreatefromjpeg($temp_path);
                 break;
             case 'png':
                 $image_src = imagecreatefrompng($temp_path);
                 break;
             case 'gif':
                 $image_src = imagecreatefromgif($temp_path);
                 break;
         }

         return $image_src;
     }

    public function resize( $tmp_file, $extension, $rep, $src,$mini_width, $mini_height )
    {
        list( $src_width,$src_height ) = getimagesize($tmp_file);

        $ratio_src = $src_width / $src_height;
        $ratio_mini = $mini_width / $mini_height;

        if ($ratio_src >= $ratio_mini) {
           $new_height_mini = $mini_height;
           $new_width_mini = $src_width / ($src_height / $mini_height);

        } else {
           $new_width_mini = $mini_width;
           $new_height_mini = $src_height / ($src_width / $mini_width);
        }

        $new_image_mini = imagecreatetruecolor($mini_width, $mini_height);

        imagecopyresampled($new_image_mini, $src,
                           0 - ($new_width_mini - $mini_width) / 2,
                           0 - ($new_height_mini - $mini_height) / 2,
                           0, 0,
                           $new_width_mini, $new_height_mini,
                           $src_width, $src_height);
        imagejpeg($new_image_mini, $rep);

        imagedestroy($new_image_mini);

    }
    
    public function getGameEntity()
    {
        return new \PlaygroundGame\Entity\Game;
    }
    
    public function isAllowed($resource, $privilege = null){
        
        $auth = $this->getServiceManager()->get('BjyAuthorize\Service\Authorize');
        
        return $auth->isAllowed($resource, $privilege);
    }
}