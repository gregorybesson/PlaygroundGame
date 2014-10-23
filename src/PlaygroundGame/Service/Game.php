<?php
namespace PlaygroundGame\Service;

use PlaygroundGame\Entity\Entry;
use Zend\Session\Container;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundGame\Options\ModuleOptions;
use PlaygroundGame\Mapper\GameInterface as GameMapperInterface;
use DoctrineModule\Validator\NoObjectExists as NoObjectExistsValidator;
use Zend\Validator\File\Size;
use Zend\Validator\File\IsImage;
use Zend\Stdlib\ErrorHandler;
use PlaygroundCore\Filter\Sanitize;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;

class Game extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     *
     * @var GameMapperInterface
     */
    protected $gameMapper;

    /**
     *
     * @var EntryMapperInterface
     */
    protected $entryMapper;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     *
     * @var UserServiceOptionsInterface
     */
    protected $options;

    protected $playerformMapper;
    
    protected $anonymousIdentifier = null;

    /**
     *
     *
     * This service is ready for all types of games
     *
     * @param array $data
     * @param string $entity
     * @param string $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function create(array $data, $entity, $formClass)
    {
        $game = new $entity();
        $entityManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        $form = $this->getServiceManager()->get($formClass);
        // I force the following format because this is the only one accepted by new DateTime($value) used by Doctrine when persisting
        $form->get('publicationDate')->setOptions(array(
            'format' => 'Y-m-d'
        ));
        $form->get('startDate')->setOptions(array(
            'format' => 'Y-m-d'
        ));
        $form->get('endDate')->setOptions(array(
            'format' => 'Y-m-d'
        ));
        $form->get('closeDate')->setOptions(array(
            'format' => 'Y-m-d'
        ));

        $form->bind($game);

        $path = $this->getOptions()->getMediaPath() . '/';
        $media_url = $this->getOptions()->getMediaUrl() . '/';

        $identifierInput = $form->getInputFilter()->get('identifier');
        $noObjectExistsValidator = new NoObjectExistsValidator(array(
            'object_repository' => $entityManager->getRepository('PlaygroundGame\Entity\Game'),
            'fields' => 'identifier',
            'messages' => array(
                'objectFound' => 'This url already exists !'
            )
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
        if (! isset($data['publicationDate']) && isset($data['startDate'])) {
            $data['publicationDate'] = $data['startDate'];
        }

        // If the identifier has not been set, I use the title to create one.
        if (empty($data['identifier']) && ! empty($data['title'])) {
            $data['identifier'] = $data['title'];
        }

        $form->setData($data);

        if (! $form->isValid()) {

            if (isset($data['publicationDate']) && $data['publicationDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['publicationDate']);
                $data['publicationDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array(
                    'publicationDate' => $data['publicationDate']
                ));
            }
            if (isset($data['startDate']) && $data['startDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['startDate']);
                $data['startDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array(
                    'startDate' => $data['startDate']
                ));
            }
            if (isset($data['endDate']) && $data['endDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['endDate']);
                $data['endDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array(
                    'endDate' => $data['endDate']
                ));
            }
            if (isset($data['closeDate']) && $data['closeDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['closeDate']);
                $data['closeDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array(
                    'closeDate' => $data['closeDate']
                ));
            }
            return false;
        }

        $game = $form->getData();
        $game = $this->getGameMapper()->insert($game);

        // If I receive false, it means that the FB Id was not available anymore
        $result = $this->getEventManager()->trigger(__FUNCTION__, $this, array(
            'game' => $game
        ));
        if (! $result)
            return false;

            // I wait for the game to be saved to obtain its ID.
        if (! empty($data['uploadStylesheet']['tmp_name'])) {
            ErrorHandler::start();
            move_uploaded_file($data['uploadStylesheet']['tmp_name'], $path . 'stylesheet_' . $game->getId() . '.css');
            $game->setStylesheet($media_url . 'stylesheet_' . $game->getId() . '.css');
            ErrorHandler::stop(true);
        }

        if (! empty($data['uploadMainImage']['tmp_name'])) {
            ErrorHandler::start();
            $data['uploadMainImage']['name'] = $this->fileNewname($path, $game->getId() . "-" . $data['uploadMainImage']['name']);
            move_uploaded_file($data['uploadMainImage']['tmp_name'], $path . $data['uploadMainImage']['name']);
            $game->setMainImage($media_url . $data['uploadMainImage']['name']);
            ErrorHandler::stop(true);
        }

        if (! empty($data['uploadSecondImage']['tmp_name'])) {
            ErrorHandler::start();
            $data['uploadSecondImage']['name'] = $this->fileNewname($path, $game->getId() . "-" . $data['uploadSecondImage']['name']);
            move_uploaded_file($data['uploadSecondImage']['tmp_name'], $path . $data['uploadSecondImage']['name']);
            $game->setSecondImage($media_url . $data['uploadSecondImage']['name']);
            ErrorHandler::stop(true);
        }

        if (! empty($data['uploadFbShareImage']['tmp_name'])) {
            ErrorHandler::start();
            $data['uploadFbShareImage']['name'] = $this->fileNewname($path, $game->getId() . "-" . $data['uploadFbShareImage']['name']);
            move_uploaded_file($data['uploadFbShareImage']['tmp_name'], $path . $data['uploadFbShareImage']['name']);
            $game->setFbShareImage($media_url . $data['uploadFbShareImage']['name']);
            ErrorHandler::stop(true);
        }

        if (! empty($data['uploadFbPageTabImage']['tmp_name'])) {
            ErrorHandler::start();
            $extension = $this->getExtension(strtolower($data['uploadFbPageTabImage']['name']));
            $src = $this->getSrc($extension, $data['uploadFbPageTabImage']['tmp_name']);
            $this->resize($data['uploadFbPageTabImage']['tmp_name'], $extension, $path . $game->getId() . "-" . $data['uploadFbPageTabImage']['name'], $src, 111, 74);

            $game->setFbPageTabImage($media_url . $game->getId() . "-" . $data['uploadFbPageTabImage']['name']);
            ErrorHandler::stop(true);
        }
        $game = $this->getGameMapper()->update($game);

        $prize_mapper = $this->getServiceManager()->get('playgroundgame_prize_mapper');
        if (isset($data['prizes'])) {
            foreach ($data['prizes'] as $prize_data) {
                if (! empty($prize_data['picture']['tmp_name'])) {
                    if ($prize_data['id']) {
                        $prize = $prize_mapper->findById($prize_data['id']);
                    } else {
                        $some_prizes = $prize_mapper->findBy(array(
                            'game' => $game,
                            'title' => $prize_data['title']
                        ));
                        if (count($some_prizes) == 1) {
                            $prize = $some_prizes[0];
                        } else {
                            return false;
                        }
                    }
                    ErrorHandler::start();
                    $filename = "game-" . $game->getId() . "-prize-" . $prize->getId() . "-" . $prize_data['picture']['name'];
                    move_uploaded_file($prize_data['picture']['tmp_name'], $path . $filename);
                    $prize->setPicture($media_url . $filename);
                    ErrorHandler::stop(true);
                    $prize = $prize_mapper->update($prize);
                }
            }
        }

        return $game;
    }

    /**
     *
     *
     * This service is ready for all types of games
     *
     * @param array $data
     * @param string $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function edit(array $data, $game, $formClass)
    {
        $entityManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $form = $this->getServiceManager()->get($formClass);
        $form->get('publicationDate')->setOptions(array(
            'format' => 'Y-m-d'
        ));
        $form->get('startDate')->setOptions(array(
            'format' => 'Y-m-d'
        ));
        $form->get('endDate')->setOptions(array(
            'format' => 'Y-m-d'
        ));
        $form->get('closeDate')->setOptions(array(
            'format' => 'Y-m-d'
        ));

        $form->bind($game);

        $path = $this->getOptions()->getMediaPath() . '/';
        $media_url = $this->getOptions()->getMediaUrl() . '/';

        $identifierInput = $form->getInputFilter()->get('identifier');
        $noObjectExistsValidator = new NoObjectExistsValidator(array(
            'object_repository' => $entityManager->getRepository('PlaygroundGame\Entity\Game'),
            'fields' => 'identifier',
            'messages' => array(
                'objectFound' => 'This url already exists !'
            )
        ));

        if ($game->getIdentifier() != $data['identifier']) {
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
        if ((! isset($data['publicationDate']) || $data['publicationDate'] == '') && (isset($data['startDate']) && $data['startDate'] != '')) {
            $data['publicationDate'] = $data['startDate'];
        }

        if (! isset($data['identifier']) && isset($data['title'])) {
            $data['identifier'] = $data['title'];
        }

        $form->setData($data);

        // If someone want to claim... It's time to do it ! used for exemple by PlaygroundFacebook Module
        $result = $this->getEventManager()->trigger(__FUNCTION__ . '.validate', $this, array(
            'game' => $game,
            'data' => $data
        ));
        if (is_array($result) && ! $result[0]) {
            $form->get('fbAppId')->setMessages(array(
                'Vous devez d\'abord désinstaller l\'appli Facebook'
            ));

            return false;
        }

        if (! $form->isValid()) {
            if (isset($data['publicationDate']) && $data['publicationDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['publicationDate']);
                $data['publicationDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array(
                    'publicationDate' => $data['publicationDate']
                ));
            }
            if (isset($data['startDate']) && $data['startDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['startDate']);
                $data['startDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array(
                    'startDate' => $data['startDate']
                ));
            }
            if (isset($data['endDate']) && $data['endDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['endDate']);
                $data['endDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array(
                    'endDate' => $data['endDate']
                ));
            }
            if (isset($data['closeDate']) && $data['closeDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d', $data['closeDate']);
                $data['closeDate'] = $tmpDate->format('d/m/Y');
                $form->setData(array(
                    'closeDate' => $data['closeDate']
                ));
            }
            return false;
        }

        if (! empty($data['uploadMainImage']['tmp_name'])) {
            ErrorHandler::start();
            $data['uploadMainImage']['name'] = $this->fileNewname($path, $game->getId() . "-" . $data['uploadMainImage']['name']);
            move_uploaded_file($data['uploadMainImage']['tmp_name'], $path . $data['uploadMainImage']['name']);
            $game->setMainImage($media_url . $data['uploadMainImage']['name']);
            ErrorHandler::stop(true);
        }

        if (isset($data['deleteMainImage']) && $data['deleteMainImage'] && empty($data['uploadMainImage']['tmp_name'])) {
            ErrorHandler::start();
            $image = $game->getMainImage();
            $image = str_replace($media_url, '', $image);
            unlink($path . $image);
            $game->setMainImage(null);
            ErrorHandler::stop(true);
        }

        if (! empty($data['uploadSecondImage']['tmp_name'])) {
            ErrorHandler::start();
            $data['uploadSecondImage']['name'] = $this->fileNewname($path, $game->getId() . "-" . $data['uploadSecondImage']['name']);
            move_uploaded_file($data['uploadSecondImage']['tmp_name'], $path . $data['uploadSecondImage']['name']);
            $game->setSecondImage($media_url . $data['uploadSecondImage']['name']);
            ErrorHandler::stop(true);
        }

        if (isset($data['deleteSecondImage']) && $data['deleteSecondImage'] && empty($data['uploadSecondImage']['tmp_name'])) {
            ErrorHandler::start();
            $image = $game->getSecondImage();
            $image = str_replace($media_url, '', $image);
            unlink($path . $image);
            $game->setSecondImage(null);
            ErrorHandler::stop(true);
        }

        if (! empty($data['uploadStylesheet']['tmp_name'])) {
            ErrorHandler::start();
            move_uploaded_file($data['uploadStylesheet']['tmp_name'], $path . 'stylesheet_' . $game->getId() . '.css');
            $game->setStylesheet($media_url . 'stylesheet_' . $game->getId() . '.css');
            ErrorHandler::stop(true);
        }

        if (! empty($data['uploadFbShareImage']['tmp_name'])) {
            ErrorHandler::start();
            $data['uploadFbShareImage']['name'] = $this->fileNewname($path, $game->getId() . "-" . $data['uploadFbShareImage']['name']);
            move_uploaded_file($data['uploadFbShareImage']['tmp_name'], $path . $data['uploadFbShareImage']['name']);
            $game->setFbShareImage($media_url . $data['uploadFbShareImage']['name']);
            ErrorHandler::stop(true);
        }

        if (isset($data['deleteFbShareImage']) && $data['deleteFbShareImage'] && empty($data['uploadFbShareImage']['tmp_name'])) {
            ErrorHandler::start();
            $image = $game->getFbShareImage();
            $image = str_replace($media_url, '', $image);
            unlink($path . $image);
            $game->setFbShareImage(null);
            ErrorHandler::stop(true);
        }

        if (! empty($data['uploadFbPageTabImage']['tmp_name'])) {
            ErrorHandler::start();

            $extension = $this->getExtension(strtolower($data['uploadFbPageTabImage']['name']));
            $src = $this->getSrc($extension, $data['uploadFbPageTabImage']['tmp_name']);
            $this->resize($data['uploadFbPageTabImage']['tmp_name'], $extension, $path . $game->getId() . "-" . $data['uploadFbPageTabImage']['name'], $src, 111, 74);

            $game->setFbPageTabImage($media_url . $game->getId() . "-" . $data['uploadFbPageTabImage']['name']);
            ErrorHandler::stop(true);
        }

        if (isset($data['deleteFbPageTabImage']) && $data['deleteFbPageTabImage'] && empty($data['uploadFbPageTabImage']['tmp_name'])) {
            ErrorHandler::start();
            $image = $game->getFbPageTabImage();
            $image = str_replace($media_url, '', $image);
            unlink($path . $image);
            $game->setFbPageTabImage(null);
            ErrorHandler::stop(true);
        }

        $game = $this->getGameMapper()->update($game);

        $prize_mapper = $this->getServiceManager()->get('playgroundgame_prize_mapper');
        if (isset($data['prizes'])) {
            foreach ($data['prizes'] as $prize_data) {
                if (! empty($prize_data['picture_file']['tmp_name']) && ! $prize_data['picture_file']['error']) {
                    if ($prize_data['id']) {
                        $prize = $prize_mapper->findById($prize_data['id']);
                    } else {
                        $some_prizes = $prize_mapper->findBy(array(
                            'game' => $game,
                            'title' => $prize_data['title']
                        ));
                        if (count($some_prizes) == 1) {
                            $prize = $some_prizes[0];
                        } else {
                            return false;
                        }
                    }
                    // Remove if existing image
                    if ($prize->getPicture() && file_exists($prize->getPicture())) {
                        unlink($prize->getPicture());
                        $prize->getPicture(null);
                    }
                    // Upload and set new
                    ErrorHandler::start();
                    $filename = "game-" . $game->getId() . "-prize-" . $prize->getId() . "-" . $prize_data['picture_file']['name'];
                    move_uploaded_file($prize_data['picture_file']['tmp_name'], $path . $filename);
                    $prize->setPicture($media_url . $filename);
                    ErrorHandler::stop(true);
                    $prize = $prize_mapper->update($prize);
                }
            }
        }
        // If I receive false, it means that the FB Id was not available anymore
        $result = $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array(
            'game' => $game
        ));

        return $game;
    }

    /**
     * getActiveGames
     *
     * @return Array of PlaygroundGame\Entity\Game
     */
    public function getActiveGames($displayHome = true, $classType = '', $order = '', $withoutGameInMission = false)
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $today = new \DateTime("now");
        $today = $today->format('Y-m-d') . ' 23:59:59';
        $orderBy = 'g.publicationDate';
        if ($order != '') {
            $orderBy = 'g.'.$order;
        }

        $qb = $em->createQueryBuilder();
        $and = $qb->expr()->andx();
        $and->add(
            $qb->expr()->orX(
                $qb->expr()->lte('g.publicationDate', ':date'),
                $qb->expr()->isNull('g.publicationDate')
            )
        );
        $and->add(
            $qb->expr()->orX(
                $qb->expr()->gte('g.closeDate', ':date'),
                $qb->expr()->isNull('g.closeDate')
            )
        );
        $qb->setParameter('date', $today);
        
        $and->add($qb->expr()->eq('g.active', '1'));
        $and->add($qb->expr()->eq('g.broadcastPlatform', '1'));
        
        if ($classType != '') {
            $and->add($qb->expr()->eq('g.classType', ':classType'));
            $qb->setParameter('classType', $classType);
        }
        
        if ($displayHome) {
            $and->add($qb->expr()->eq('g.displayHome', true));
        }
        
        if ($withoutGameInMission) {
            
            $qb2 = $em->createQueryBuilder();
            $qb2->select('IDENTITY(mg.game)')
            ->from('PlaygroundGame\Entity\Mission', 'm')
            ->innerJoin('PlaygroundGame\Entity\MissionGame', 'mg', 'WITH', $qb->expr()->eq('mg.mission', 'm.id'))
            ->where($qb->expr()->eq('m.active', 1));
            
            $and->add($qb->expr()->notIn('g.id', $qb2->getDQL()));
        }
        
        $qb->select('g')
        ->from('PlaygroundGame\Entity\Game', 'g')
        ->where($and)
        ->orderBy($orderBy, 'DESC');
        
        $query = $qb->getQuery();
        $games = $query->getResult();
        
        // je les classe par date de publication (date comme clé dans le tableau afin de pouvoir merger les objets
        // de type article avec le même procédé en les classant naturellement par date asc ou desc
        $arrayGames = array();
        foreach ($games as $game) {
            if ($game->getPublicationDate()) {
                $key = $game->getPublicationDate()->format('Ymd') . $game->getUpdatedAt()->format('Ymd') . '-' . $game->getId();
            } elseif ($game->getStartDate()) {
                $key = $game->getStartDate()->format('Ymd') . $game->getUpdatedAt()->format('Ymd') . '-' . $game->getId();
            } else {
                $key = $game->getUpdatedAt()->format('Ymd') . $game->getUpdatedAt()->format('Ymd') . '-' . $game->getId();
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
        $today = $today->format('Y-m-d') . ' 23:59:59';

        // Game active with a start_date before today (or without start_date) and end_date after today (or without end-date)
        $query = $em->createQuery('SELECT g FROM PlaygroundGame\Entity\Game g
                WHERE NOT EXISTS (SELECT l FROM PlaygroundGame\Entity\Entry l WHERE l.game = g AND l.user = :user)
                AND (g.startDate <= :date OR g.startDate IS NULL)
                AND (g.endDate >= :date OR g.endDate IS NULL)
                AND g.active = 1 AND g.broadcastPlatform = 1
                ORDER BY g.startDate ASC');
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
        $today = $today->format('Y-m-d') . ' 23:59:59';

        // Game active with a start_date before today (or without start_date) and end_date after today (or without end-date)
        $query = $em->createQuery('SELECT g FROM PlaygroundGame\Entity\Game g
            WHERE (g.publicationDate <= :date OR g.publicationDate IS NULL)
            AND (g.closeDate >= :date OR g.closeDate IS NULL)
            AND g.active = true AND g.broadcastPlatform = 1 AND g.pushHome = true');
        $query->setParameter('date', $today);
        $games = $query->getResult();

        // je les classe par date de publication (date comme clé dans le tableau afin de pouvoir merger les objets
        // de type article avec le même procédé en les classant naturellement par date asc ou desc
        $arrayGames = array();
        foreach ($games as $game) {
            if ($game->getPublicationDate()) {
                $key = $game->getPublicationDate()->format('Ymd') . $game->getUpdatedAt()->format('Ymd') . '-' . $game->getId();
            } elseif ($game->getStartDate()) {
                $key = $game->getStartDate()->format('Ymd') . $game->getUpdatedAt()->format('Ymd') . '-' . $game->getId();
            } else {
                $key = $game->getUpdatedAt()->format('Ymd') . $game->getUpdatedAt()->format('Ymd') . '-' . $game->getId();
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

        $query = $em->createQuery('SELECT g FROM PlaygroundGame\Entity\Game g
            WHERE (g.prizeCategory = :categoryid AND g.broadcastPlatform = 1)
            ORDER BY g.publicationDate DESC');
        $query->setParameter('categoryid', $categoryid);
        $games = $query->getResult();

        return $games;
    }

    public function checkGame($identifier, $checkIfStarted = true)
    {
        $gameMapper = $this->getGameMapper();

        if (! $identifier) {
            return false;
        }

        $game = $gameMapper->findByIdentifier($identifier);

        // the game has not been found
        if (! $game) {
            return false;
        }

        if ($this->getServiceManager()
            ->get('Application')
            ->getMvcEvent()
            ->getRouteMatch()
            ->getParam('channel') === 'preview' && $this->isAllowed('game', 'edit')) {

            $game->setActive(true);
            $game->setStartDate(null);
            $game->setEndDate(null);
            $game->setPublicationDate(null);
            $game->setBroadcastPlatform(true);

            // I don't want the game to be updated through any update during the preview mode. I mark it as readonly for Doctrine
            $this->getServiceManager()
                ->get('doctrine.entitymanager.orm_default')
                ->getUnitOfWork()
                ->markReadOnly($game);
            return $game;
        }

        // The game is inactive
        if (! $game->getActive()) {
            return false;
        }

        // the game has not begun yet
        if (! $game->isOpen()) {
            return false;
        }

        // the game is finished and closed
        if (! $game->isStarted() && $checkIfStarted) {
            return false;
        }

        return $game;
    }

    /**
     * Return the last entry of the user on this game, if it exists.
     * An entry can be associated to :
     * - A user account (a real one, linked to PlaygroundUser
     * - An anonymous Identifier (based on one value of playerData (generally email))
     * - A cookie set on the player device (the less secure)
     * If the active param is set, it can check if the entry is active or not.
     * If the bonus param is set, it can check if the entry is a bonus or not.
     *
     * @param unknown $game
     * @param string $user
     * @param boolean $active
     * @param boolean $bonus
     * @return boolean
     */
    public function checkExistingEntry($game, $user = null, $active = null, $bonus = null)
    {
        $entry = false;
        $search = array('game'  => $game);

        if ($user) {
            $search['user'] = $user;
        } elseif($this->getAnonymousIdentifier()){
            $search['anonymousIdentifier'] = $this->getAnonymousIdentifier();
            $search['user'] = Null;
        } else {
            $search['anonymousId'] = $this->getAnonymousId();
            $search['user'] = Null;
        }
        
        if (! is_null($active)) $search['active'] = $active;
        if (! is_null($bonus)) $search['bonus'] = $bonus;

        $entry = $this->getEntryMapper()->findOneBy($search);

        return $entry;
    }

    public function checkIsFan($game)
    {
        // If on Facebook, check if you have to be a FB fan to play the game
        $session = new Container('facebook');

        if ($session->offsetExists('signed_request')) {
            // I'm on Facebook
            $sr = $session->offsetGet('signed_request');
            if ($sr['page']['liked'] == 1) {

                return true;
            }
        } else {
            // I'm not on Facebook
            return true;
        }

        return false;
    }
    
    public function getAnonymousIdentifier()
    {
        if(is_null($this->anonymousIdentifier)){
            // If on Facebook, check if you have to be a FB fan to play the game
            $session = new Container('anonymous_identifier');
            
            if ($session->offsetExists('anonymous_identifier')) {
                $this->anonymousIdentifier = $session->offsetGet('anonymous_identifier');
                
            } else{

                $this->anonymousIdentifier = false;
            }
        }
    
        return $this->anonymousIdentifier;
    }

    /**
     * errors :
     * -1 : user not connected
     * -2 : limit entry games for this user reached
     *
     * @param \PlaygroundGame\Entity\Game $game
     * @param \PlaygroundUser\Entity\UserInterface $user
     * @return number unknown
     */
    public function play($game, $user)
    {

        // certaines participations peuvent rester ouvertes. On autorise alors le joueur à reprendre là ou il en était
        // par exemple les postvote...
        $entry = $this->checkExistingEntry($game, $user, true);

        if (! $entry) {

            if ($this->hasReachedPlayLimit($game, $user)){

                return false;
            }

            $entry = new Entry();
            $entry->setGame($game);
            $entry->setUser($user);
            $entry->setPoints(0);
            $entry->setIp($this->getIp());
            $entry->setAnonymousId($this->getAnonymousId());
            if($this->getAnonymousIdentifier()) $entry->setAnonymousIdentifier($this->getAnonymousIdentifier());

            $entry = $this->getEntryMapper()->insert($entry);
            $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array(
                'user' => $user,
                'game' => $game,
                'entry' => $entry
            ));
        }

        return $entry;
    }

    public function hasReachedPlayLimit($game, $user)
    {
        // Is there a limitation on the game ?
        $limitAmount = $game->getPlayLimit();
        if ($limitAmount) {
            $limitScale = $game->getPlayLimitScale();
            $userEntries = $this->findLastEntries($game, $user, $limitScale);

            // player has reached the game limit
            if ($userEntries >= $limitAmount) {
                return true;
            }
        }
        return false;
    }
    
    public function findLastEntries($game, $user, $limitScale)
    {
        if ($user) {

            return $this->getEntryMapper()->findLastEntriesByUser($game, $user, $limitScale);
        } elseif($this->getAnonymousIdentifier()) {

            return $this->getEntryMapper()->findLastEntriesByAnonymousIdentifier($game, $this->getAnonymousIdentifier(), $limitScale);
        } else {
            return $this->getEntryMapper()->findLastEntriesByIp($game, $this->getIp(), $limitScale);
        }
    }

    public function findLastActiveEntry($game, $user)
    {

        return $this->checkExistingEntry($game, $user, true);
    }

    public function findLastInactiveEntry($game, $user)
    {
        
        return $this->checkExistingEntry($game, $user, false, false);
    }

    public function findLastEntry($game, $user)
    {

        return $this->checkExistingEntry($game, $user, null, false);
    }

    public function sendShareMail($data, $game, $user, $entry, $template = 'share_game', $topic = NULL, $userTimer = array())
    {
        $mailService = $this->getServiceManager()->get('playgroundgame_message');
        $mailSent = false;
        $from = $this->getOptions()->getEmailFromAddress();
        $subject = $this->getOptions()->getShareSubjectLine();
        $renderer = $this->getServiceManager()->get('Zend\View\Renderer\RendererInterface');
        if($user){
            $email = $user->getEmail();
        } elseif($entry->getAnonymousIdentifier()){
            $email = $entry->getAnonymousIdentifier();
        }else{
            $email = $from;
        }
        $skinUrl = $renderer->url(
            'frontend', 
            array('channel' => $this->getServiceManager()
                ->get('Application')
                ->getMvcEvent()
                ->getRouteMatch()
                ->getParam('channel')
            ), array(
                'force_canonical' => true
            )
        );
        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true) . '####' . time()), 0, 15));

        if (! $topic) {
            $topic = $game->getTitle();
        }

        $shares = json_decode($entry->getSocialShares(), true);
        
        if ($data['email1']) {
            $mailSent = true;
            $message = $mailService->createHtmlMessage($from, $data['email1'], $subject, 'playground-game/email/' . $template, array(
                'game' => $game,
                'email' => $email,
                'secretKey' => $secretKey,
                'skinUrl' => $skinUrl,
                'userTimer' => $userTimer
            ));
            $mailService->send($message);
            
            if(!isset($shares['mail'])){
                $shares['mail'] = 1;
            } else{
                $shares['mail'] += 1;
            }
            
        }
        if ($data['email2'] && $data['email2'] != $data['email1']) {
            $mailSent = true;
            $message = $mailService->createHtmlMessage($from, $data['email2'], $subject, 'playground-game/email/' . $template, array(
                'game' => $game,
                'email' => $email,
                'secretKey' => $secretKey,
                'skinUrl' => $skinUrl,
                'userTimer' => $userTimer
            ));
            $mailService->send($message);
            
            if(!isset($shares['mail'])){
                $shares['mail'] = 1;
            } else{
                $shares['mail'] += 1;
            }
        }
        if ($data['email3'] && $data['email3'] != $data['email2'] && $data['email3'] != $data['email1']) {
            $mailSent = true;
            $message = $mailService->createHtmlMessage($from, $data['email3'], $subject, 'playground-game/email/' . $template, array(
                'game' => $game,
                'email' => $email,
                'secretKey' => $secretKey,
                'skinUrl' => $skinUrl,
                'userTimer' => $userTimer
            ));
            $mailService->send($message);
            
            if(!isset($shares['mail'])){
                $shares['mail'] = 1;
            } else{
                $shares['mail'] += 1;
            }
        }
        if ($data['email4'] && $data['email4'] != $data['email3'] && $data['email4'] != $data['email2'] && $data['email4'] != $data['email1']) {
            $mailSent = true;
            $message = $mailService->createHtmlMessage($from, $data['email4'], $subject, 'playground-game/email/' . $template, array(
                'game' => $game,
                'email' => $email,
                'secretKey' => $secretKey,
                'skinUrl' => $skinUrl,
                'userTimer' => $userTimer
            ));
            $mailService->send($message);
        
            if(!isset($shares['mail'])){
                $shares['mail'] = 1;
            } else{
                $shares['mail'] += 1;
            }
        }
        if ($data['email5'] && $data['email5'] != $data['email4'] && $data['email5'] != $data['email3'] && $data['email5'] != $data['email2'] && $data['email5'] != $data['email1']) {
            $mailSent = true;
            $message = $mailService->createHtmlMessage($from, $data['email5'], $subject, 'playground-game/email/' . $template, array(
                'game' => $game,
                'email' => $email,
                'secretKey' => $secretKey,
                'skinUrl' => $skinUrl,
                'userTimer' => $userTimer
            ));
            $mailService->send($message);
        
            if(!isset($shares['mail'])){
                $shares['mail'] = 1;
            } else{
                $shares['mail'] += 1;
            }
        }
        if ($mailSent) {
            
            $sharesJson = json_encode($shares);
            $entry->setSocialShares($sharesJson);
            $entry = $this->getEntryMapper()->update($entry);
            
            $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array(
                'user' => $user,
                'topic' => $topic,
                'secretKey' => $secretKey,
                'game' => $game,
                'entry' => $entry
            ));

            return true;
        }

        return false;
    }

    public function sendResultMail($game, $user, $entry, $template = 'entry', $prize = NULL)
    {

        $mailService = $this->getServiceManager()->get('playgroundgame_message');
        $from = $this->getOptions()->getEmailFromAddress();
        if($user){
            $to = $user->getEmail();
        } elseif($entry->getAnonymousIdentifier()){
            $to = $entry->getAnonymousIdentifier();
        }else{
            return false;
        }
        $subject = $game->getTitle();
        $renderer = $this->getServiceManager()->get('Zend\View\Renderer\RendererInterface');
        $skinUrl = $renderer->url(
            'frontend', 
            array('channel' => $this->getServiceManager()
                ->get('Application')
                ->getMvcEvent()
                ->getRouteMatch()
                ->getParam('channel')
            ), array(
                'force_canonical' => true
            )
        );
        $message = $mailService->createHtmlMessage($from, $to, $subject, 'playground-game/email/' . $template, array(
            'game' => $game,
            'entry' => $entry,
            'skinUrl' => $skinUrl,
            'prize' => $prize
        ));
        $mailService->send($message);
    }

    public function sendGameMail($game, $user, $post, $template = 'postvote')
    {
        $mailService = $this->getServiceManager()->get('playgroundgame_message');
        $from = $this->getOptions()->getEmailFromAddress();
        $to = $user->getEmail();
        $subject = $this->getOptions()->getParticipationSubjectLine();
        $renderer = $this->getServiceManager()->get('Zend\View\Renderer\RendererInterface');
        $skinUrl = $renderer->url(
            'frontend', 
            array('channel' => $this->getServiceManager()
                ->get('Application')
                ->getMvcEvent()
                ->getRouteMatch()
                ->getParam('channel')
            ), array(
                'force_canonical' => true
            )
        );

        $message = $mailService->createHtmlMessage($from, $to, $subject, 'playground-game/email/' . $template, array(
            'game' => $game,
            'post' => $post,
            'skinUrl' => $skinUrl
        ));
        $mailService->send($message);
    }

    public function postFbWall($secretKey, $game, $user, $entry)
    {
        $topic = $game->getTitle();
        
        $shares = json_decode($entry->getSocialShares(), true);
        if(!isset($shares['fbwall'])){
            $shares['fbwall'] = 1;
        } else{
            $shares['fbwall'] += 1;
        }
        $sharesJson = json_encode($shares);
        $entry->setSocialShares($sharesJson);
        $entry = $this->getEntryMapper()->update($entry);
        
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array(
            'user' => $user,
            'game' => $game,
            'secretKey' => $secretKey,
            'topic' => $topic,
            'entry' => $entry
        ));

        return true;
    }

    public function postFbRequest($secretKey, $game, $user, $entry, $to)
    {
        $shares = json_decode($entry->getSocialShares(), true);
        $to = explode(',', $to);
        if(!isset($shares['fbrequest'])){
            $shares['fbrequest'] = count($to);
        } else{
            $shares['fbrequest'] += count($to);
        }
        $sharesJson = json_encode($shares);
        $entry->setSocialShares($sharesJson);
        $entry = $this->getEntryMapper()->update($entry);
        
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array(
            'user' => $user,
            'game' => $game,
            'secretKey' => $secretKey,
            'entry' => $entry,
            'invites' => count($to)
        ));

        return true;
    }

    public function postTwitter($secretKey, $game, $user, $entry)
    {
        $topic = $game->getTitle();

        $shares = json_decode($entry->getSocialShares(), true);
        if(!isset($shares['fbrequest'])){
            $shares['tweet'] = 1;
        } else{
            $shares['tweet'] += 1;
        }
        $sharesJson = json_encode($shares);
        $entry->setSocialShares($sharesJson);
        $entry = $this->getEntryMapper()->update($entry);
        
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array(
            'user' => $user,
            'game' => $game,
            'secretKey' => $secretKey,
            'topic' => $topic,
            'entry' => $entry
        ));

        return true;
    }

    public function postGoogle($secretKey, $game, $user, $entry)
    {
        $topic = $game->getTitle();
        
        $shares = json_decode($entry->getSocialShares(), true);
        if(!isset($shares['fbrequest'])){
            $shares['google'] = 1;
        } else{
            $shares['google'] += 1;
        }
        $sharesJson = json_encode($shares);
        $entry->setSocialShares($sharesJson);
        $entry = $this->getEntryMapper()->update($entry);

        $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array(
            'user' => $user,
            'game' => $game,
            'secretKey' => $secretKey,
            'topic' => $topic,
            'entry' => $entry
        ));

        return true;
    }

    /**
     * Is it possible to trigger a bonus entry ?
     *
     * @param unknown_type $game
     * @param unknown_type $user
     */
    public function allowBonus($game, $user)
    {
        if (! $game->getPlayBonus() || $game->getPlayBonus() == 'none') {
            return false;
        } elseif ($game->getPlayBonus() == 'one') {
            if ($this->getEntryMapper()->findOneBy(array(
                'game' => $game,
                'user' => $user,
                'bonus' => 1
            ))) {
                return false;
            } else {
                return true;
            }
        } elseif ($game->getPlayBonus() == 'per_entry') {
            return $this->getEntryMapper()->checkBonusEntry($game, $user);
        }

        return false;
    }

    public function addAnotherEntry($game, $user, $winner = 0)
    {
        $entry = new Entry();
        $entry->setGame($game);
        $entry->setUser($user);
        $entry->setPoints(0);
        $entry->setIp($this->getIp());
        $entry->setActive(0);
        $entry->setBonus(1);
        $entry->setWinner($winner);
        $entry = $this->getEntryMapper()->insert($entry);

        return $entry;
    }

    /**
     * This bonus entry doesn't give points nor badges
     * It's just there to increase the chances during the Draw
     * Old Name playBonus 
     * 
     * @param PlaygroundGame\Entity\Game $game
     * @param unknown $user
     * @return boolean unknown
     */
    public function addAnotherChance($game, $user, $winner = 0)
    {
        if ($this->allowBonus($game, $user)) {
            $this->addAnotherEntry($game, $user, $winner);

            return true;
        }

        return false;
    }

    /**
     * This bonus entry doesn't give points nor badges but can play again
     *
     * @param PlaygroundGame\Entity\Game $game
     * @param user $user
     * @return boolean unknown
     */
    public function playAgain($game, $user, $winner = 0)
    {
         if ($this->allowBonus($game, $user)) {
            $entry = $this->addAnotherEntry($game, $user, $winner);
            $entry->setActive(1);
            $entry = $this->getEntryMapper()->update($entry);
            if($entry->getActive() == 1) {
                return true;
            }
        }

        return false;
    }

    public static function cronMail()
    {
        $configuration = require 'config/application.config.php';
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
        $sm = new \Zend\ServiceManager\ServiceManager(new \Zend\Mvc\Service\ServiceManagerConfig($smConfig));
        $sm->setService('ApplicationConfig', $configuration);
        $sm->get('ModuleManager')->loadModules();
        $sm->get('Application')->bootstrap();

        $mailService = $sm->get('playgrounduser_message');
        $gameService = $sm->get('playgroundgame_quiz_service');

        $from = "admin@playground.fr";
        $subject = "sujet game";

        $to = "gbesson@test.com";

        $game = $gameService->checkGame('qooqo');

        $message = $mailService->createTextMessage($from, $to, $subject, 'playground-game/email/share_reminder', array(
            'game' => $game
        ));

        $mailService->send($message);
    }

    /**
     * @param string $path
     */
    public function uploadFile($path, $file)
    {
        $err = $file["error"];
        $message = '';
        if ($err > 0) {
            switch ($err) {
                case '1':
                    $message .= 'Max file size exceeded. (php.ini)';
                    break;
                case '2':
                    $message .= 'Max file size exceeded.';
                    break;
                case '3':
                    $message .= 'File upload was only partial.';
                    break;
                case '4':
                    $message .= 'No file was attached.';
                    break;
                case '7':
                    $message .= 'File permission denied.';
                    break;
                default:
                    $message .= 'Unexpected error occurs.';
            }

            return $err;
        } else {

            $fileNewname = $this->fileNewname($path, $file['name'], true);
            $adapter = new \Zend\File\Transfer\Adapter\Http();
            // 500ko
            $size = new Size(array(
                'max' => 512000
            ));
            $is_image = new IsImage('jpeg,png,gif,jpg');
            $adapter->setValidators(array(
                $size,
                $is_image
            ), $fileNewname);

            if (! $adapter->isValid()) {

                return false;
            }

            @move_uploaded_file($file["tmp_name"], $path . $fileNewname);
            
            if( class_exists("Imagick") ){
                $ext = pathinfo($fileNewname, PATHINFO_EXTENSION);
                $img = new \Imagick($path . $fileNewname);
                $img->cropThumbnailImage( 100, 100 );
                $img->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $img->setImageCompressionQuality(75);
                // Strip out unneeded meta data
                $img->stripImage();
                $img->writeImage($path . str_replace('.'.$ext, '-thumbnail.'.$ext, $fileNewname));
                ErrorHandler::stop(true);
            
            }
        }

        return $fileNewname;
    }

    public function fileNewname($path, $filename, $generate = false)
    {
        $sanitize = new Sanitize();
        $name = $sanitize->filter($filename);
        $newpath = $path . $name;

        if ($generate) {
            if (file_exists($newpath)) {
                $filename = pathinfo($name, PATHINFO_FILENAME);
                $ext = pathinfo($name, PATHINFO_EXTENSION);

                $name = $filename . '_' . rand(0, 99) . '.' . $ext;
            }
        }

        unset($sanitize);

        return $name;
    }

    /**
     * This function returns the list of games, order by $type
     */
    public function getQueryGamesOrderBy($type = 'createdAt', $order = 'DESC')
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

        $qb = $em->createQueryBuilder();
        $qb->select('g')->from('PlaygroundGame\Entity\Game', 'g');
        
        switch ($type) {
            case 'startDate':
                $qb->orderBy('g.startDate', $order);
                break;
            case 'activeGames':
                $qb->orderBy('g.active', $order);
                break;
            case 'onlineGames':
                $qb->orderBy($onlineGames, $order);
                $qb->setParameter('date', $today);
                break;
            case 'createdAt':
                $qb->orderBy('g.createdAt', $order);
                break;
        }

        $query = $qb->getQuery();

        return $query;
    }

    public function draw($game)
    {
        $total = $game->getWinners();

        // I Have to know what is the User Class used
        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
        $userClass = $zfcUserOptions->getUserEntityClass();

        $result = $this->getEntryMapper()->draw($game, $userClass, $total);

        foreach ($result as $e) {
            $e->setWinner(1);
            $e = $this->getEntryMapper()->update($e);
            $this->getEventManager()->trigger('win_lottery.post', $this, array(
                'user' => $e->getUser(),
                'game' => $game,
                'entry' => $e
            ));
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
     * @param GameMapperInterface $gameMapper
     * @return Game
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
     * @param EntryMapperInterface $entryMapper
     * @return Game
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
        if (! $this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()
                ->get('playgroundgame_module_options'));
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
     * @param ServiceManager $serviceManager
     * @return Game
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    /**
     * @param string $str
     */
    public function getExtension($str)
    {
        $i = strrpos($str, '.');

        $l = strlen($str) - $i;
        $ext = substr($str, $i + 1, $l);

        return $ext;
    }

    /**
     * @param string $extension
     */
    public function getSrc($extension, $temp_path)
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

    /**
     * @param string $extension
     * @param string $rep
     * @param integer $mini_width
     * @param integer $mini_height
     */
    public function resize($tmp_file, $extension, $rep, $src, $mini_width, $mini_height)
    {
        list ($src_width, $src_height) = getimagesize($tmp_file);

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

        imagecopyresampled($new_image_mini, $src, 0 - ($new_width_mini - $mini_width) / 2, 0 - ($new_height_mini - $mini_height) / 2, 0, 0, $new_width_mini, $new_height_mini, $src_width, $src_height);
        imagejpeg($new_image_mini, $rep);

        imagedestroy($new_image_mini);
    }

    public function getGameEntity()
    {
        return new \PlaygroundGame\Entity\Game();
    }

    /**
     * @param string $resource
     * @param string $privilege
     */
    public function isAllowed($resource, $privilege = null)
    {
        $auth = $this->getServiceManager()->get('BjyAuthorize\Service\Authorize');

        return $auth->isAllowed($resource, $privilege);
    }

    public function getIp()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            else
                if (isset($_SERVER['HTTP_X_FORWARDED']))
                    $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
                else
                    if (isset($_SERVER['HTTP_FORWARDED_FOR']))
                        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
                    else
                        if (isset($_SERVER['HTTP_FORWARDED']))
                            $ipaddress = $_SERVER['HTTP_FORWARDED'];
                        else
                            if (isset($_SERVER['REMOTE_ADDR']))
                                $ipaddress = $_SERVER['REMOTE_ADDR'];
                            else
                                $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

    public function getAnonymousId()
    {
        $anonymousId = '';
        if ($_COOKIE && array_key_exists('pg_anonymous', $_COOKIE)) {
            $anonymousId = $_COOKIE['pg_anonymous'];
        }

        return $anonymousId;
    }

    /**
     *
     *
     * This service is ready for all types of games
     *
     * @param array $data
     * @return \PlaygroundGame\Entity\Game
     */
    public function createForm(array $data, $game, $form = null)
    {
        $title = '';
        $description = '';

        if ($data['form_jsonified']) {
            $jsonPV = json_decode($data['form_jsonified']);
            foreach ($jsonPV as $element) {
                if ($element->form_properties) {
                    $attributes = $element->form_properties[0];
                    $title = $attributes->title;
                    $description = $attributes->description;

                    break;
                }
            }
        }
        if (! $form) {
            $form = new \PlaygroundGame\Entity\PlayerForm();
        }
        $form->setGame($game);
        $form->setTitle($title);
        $form->setDescription($description);
        $form->setForm($data['form_jsonified']);
        $form->setFormTemplate($data['form_template']);

        $form = $this->getPlayerFormMapper()->insert($form);

        return $form;
    }
    
    /**
     * Create a ZF2 Form from json data
     * @return Form
     */
    public function createFormFromJson($jsonForm){
        $formPV = json_decode($jsonForm);
        
        $form = new Form();
        $form->setAttribute('id', 'playerForm');
        $form->setAttribute('enctype', 'multipart/form-data');
        
        $inputFilter = new \Zend\InputFilter\InputFilter();
        $factory = new InputFactory();
        
        foreach ($formPV as $element) {
            if (isset($element->line_text)) {
                $attributes  = $element->line_text[0];
                $name        = isset($attributes->name)? $attributes->name : '';
                $placeholder = isset($attributes->data->placeholder)? $attributes->data->placeholder : '';
                $label       = isset($attributes->data->label)? $attributes->data->label : '';
                $required    = ($attributes->data->required == 'true') ? true : false ;
                $class       = isset($attributes->data->class)? $attributes->data->class : '';
                $id          = isset($attributes->data->id)? $attributes->data->id : '';
                $lengthMin   = isset($attributes->data->length)? $attributes->data->length->min : '';
                $lengthMax   = isset($attributes->data->length)? $attributes->data->length->max : '';
        
                $element = new Element\Text($name);
                $element->setLabel($label);
                $element->setAttributes(
                    array(
                        'placeholder' 	=> $placeholder,
                        'required' 		=> $required,
                        'class' 		=> $class,
                        'id' 			=> $id
                    )
                );
                $form->add($element);
        
                $options = array();
                $options['encoding'] = 'UTF-8';
                if ($lengthMin && $lengthMin > 0) {
                    $options['min'] = $lengthMin;
                }
                if ($lengthMax && $lengthMax > $lengthMin) {
                    $options['max'] = $lengthMax;
                    $element->setAttribute('maxlength', $lengthMax);
                    $options['messages'] = array(\Zend\Validator\StringLength::TOO_LONG => sprintf($this->getServiceLocator()->get('translator')->translate('This field contains more than %s characters', 'playgroundgame'), $lengthMax));
                }
                $inputFilter->add($factory->createInput(array(
                    'name'     => $name,
                    'required' => $required,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'StringLength',
                            'options' => $options,
                        ),
                    ),
                )));
        
            }
            if (isset($element->line_email)) {
                $attributes  = $element->line_email[0];
                $name        = isset($attributes->name)? $attributes->name : '';
                $placeholder = isset($attributes->data->placeholder)? $attributes->data->placeholder : '';
                $label       = isset($attributes->data->label)? $attributes->data->label : '';
                $class       = isset($attributes->data->class)? $attributes->data->class : '';
                $id          = isset($attributes->data->id)? $attributes->data->id : '';
                $lengthMin   = isset($attributes->data->length)? $attributes->data->length->min : '';
                $lengthMax   = isset($attributes->data->length)? $attributes->data->length->max : '';
        
                $element = new Element\Email($name);
                $element->setLabel($label);
                $element->setAttributes(
                    array(
                        'placeholder' 	=> $placeholder,
                        'required' 		=> $required,
                        'class' 		=> $class,
                        'id' 			=> $id
                    )
                );
                $form->add($element);
        
                $options = array();
                $options['encoding'] = 'UTF-8';
                if ($lengthMin && $lengthMin > 0) {
                    $options['min'] = $lengthMin;
                }
                if ($lengthMax && $lengthMax > $lengthMin) {
                    $options['max'] = $lengthMax;
                    $element->setAttribute('maxlength', $lengthMax);
                    $options['messages'] = array(\Zend\Validator\StringLength::TOO_LONG => sprintf($this->getServiceLocator()->get('translator')->translate('This field contains more than %s characters', 'playgroundgame'), $lengthMax));
                }
                $inputFilter->add($factory->createInput(array(
                    'name'     => $name,
                    'required' => $required,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'StringLength',
                            'options' => $options,
                        ),
                    ),
                )));
        
            }
            if (isset($element->line_radio)) {
                $attributes  = $element->line_radio[0];
                $name        = isset($attributes->name)? $attributes->name : '';
                $label       = isset($attributes->data->label)? $attributes->data->label : '';
        
                $required = false;
                $class       = isset($attributes->data->class)? $attributes->data->class : '';
                $id          = isset($attributes->data->id)? $attributes->data->id : '';
                $lengthMin   = isset($attributes->data->length)? $attributes->data->length->min : '';
                $lengthMax   = isset($attributes->data->length)? $attributes->data->length->max : '';
                $innerData   = isset($attributes->data->innerData)? $attributes->data->innerData : array();
        
                $element = new Element\Radio($name);
                $element->setLabel($label);
                $element->setAttributes(
                    array(
                        'name'          => $name,
                        'required' 		=> $required,
                        'allowEmpty'    => !$required,
                        'class' 		=> $class,
                        'id' 			=> $id
                    )
                );
                $values = array();
                foreach($innerData as $value){
                    $values[] = $value->label;
                }
                $element->setValueOptions($values);
        
                $options = array();
                $options['encoding'] = 'UTF-8';
                $options['disable_inarray_validator'] = true;
        
                $element->setOptions($options);
        
                $form->add($element);
        
                $inputFilter->add($factory->createInput(array(
                    'name'     => $name,
                    'required' => $required,
                    'allowEmpty' => !$required,
                )));
            }
            if (isset($element->line_checkbox)) {
                $attributes  = $element->line_checkbox[0];
                $name        = isset($attributes->name)? $attributes->name : '';
                $label       = isset($attributes->data->label)? $attributes->data->label : '';
        
                $required = false;
                $class       = isset($attributes->data->class)? $attributes->data->class : '';
                $id          = isset($attributes->data->id)? $attributes->data->id : '';
                $lengthMin   = isset($attributes->data->length)? $attributes->data->length->min : '';
                $lengthMax   = isset($attributes->data->length)? $attributes->data->length->max : '';
                $innerData   = isset($attributes->data->innerData)? $attributes->data->innerData : array();
        
                $element = new Element\MultiCheckbox($name);
                $element->setLabel($label);
                $element->setAttributes(
                    array(
                        'name'     => $name,
                        'required' 		=> $required,
                        'allowEmpty'    => !$required,
                        'class' 		=> $class,
                        'id' 			=> $id
                    )
                );
                $values = array();
                foreach($innerData as $value){
                    $values[] = $value->label;
                }
                $element->setValueOptions($values);
                $form->add($element);
        
                $options = array();
                $options['encoding'] = 'UTF-8';
                $options['disable_inarray_validator'] = true;
        
                $element->setOptions($options);
        
                $inputFilter->add($factory->createInput(array(
                    'name'     => $name,
                    'required' => $required,
                    'allowEmpty' => !$required,
                )));
        
            }
            if (isset($element->line_paragraph)) {
                $attributes  = $element->line_paragraph[0];
                $name        = isset($attributes->name)? $attributes->name : '';
                $placeholder = isset($attributes->data->placeholder)? $attributes->data->placeholder : '';
                $label       = isset($attributes->data->label)? $attributes->data->label : '';
                $required    = ($attributes->data->required == 'true') ? true : false ;
                $class       = isset($attributes->data->class)? $attributes->data->class : '';
                $id          = isset($attributes->data->id)? $attributes->data->id : '';
        
                $element = new Element\Textarea($name);
                $element->setLabel($label);
                $element->setAttributes(
                    array(
                        'placeholder' 	=> $placeholder,
                        'required' 		=> $required,
                        'class' 		=> $class,
                        'id' 			=> $id
                    )
                );
                $form->add($element);
        
                $options = array();
                $options['encoding'] = 'UTF-8';
                if ($lengthMin && $lengthMin > 0) {
                    $options['min'] = $lengthMin;
                }
                if ($lengthMax && $lengthMax > $lengthMin) {
                    $options['max'] = $lengthMax;
                    $element->setAttribute('maxlength', $lengthMax);
                }
                $inputFilter->add($factory->createInput(array(
                    'name'     => $name,
                    'required' => $required,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'StringLength',
                            'options' => $options,
                        ),
                    ),
                )));
            }
            if (isset($element->line_upload)) {
                $attributes  = $element->line_upload[0];
                $name        = isset($attributes->name)? $attributes->name : '';
                $label       = isset($attributes->data->label)? $attributes->data->label : '';
                $required    = ($attributes->data->required == 'true') ? true : false ;
                $class       = isset($attributes->data->class)? $attributes->data->class : '';
                $id          = isset($attributes->data->id)? $attributes->data->id : '';
                $filesizeMin = isset($attributes->data->filesize)? $attributes->data->filesize->min : 0;
                $filesizeMax = isset($attributes->data->filesize)? $attributes->data->filesize->max : 10*1024*1024;
                $element = new Element\File($name);
                $element->setLabel($label);
                $element->setAttributes(
                    array(
                        'required' 	=> $required,
                        'class' 	=> $class,
                        'id' 		=> $id
                    )
                );
                $form->add($element);
        
                $inputFilter->add($factory->createInput(array(
                    'name'     => $name,
                    'required' => $required,
                    'validators' => array(
                        array('name' => '\Zend\Validator\File\Size', 'options' => array('min' => $filesizeMin, 'max' => $filesizeMax)),
                        array('name' => '\Zend\Validator\File\Extension', 'options'  => array('png,PNG,jpg,JPG,jpeg,JPEG,gif,GIF', 'messages' => array(
                            \Zend\Validator\File\Extension::FALSE_EXTENSION => 'Veuillez télécharger une image' ))
                        ),
                    ),
                )));
        
            }
        }
        
        $form->setInputFilter($inputFilter);
        
        return $form;
    }

    public function getPlayerFormMapper()
    {
        if (null === $this->playerformMapper) {
            $this->playerformMapper = $this->getServiceManager()->get('playgroundgame_playerform_mapper');
        }

        return $this->playerformMapper;
    }

    public function setPlayerFormMapper($playerformMapper)
    {
        $this->playerformMapper = $playerformMapper;

        return $this;
    }
}
