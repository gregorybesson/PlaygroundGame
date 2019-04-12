<?php
namespace PlaygroundGame\Service;

use PlaygroundGame\Entity\Entry;
use Zend\Session\Container;
use Zend\ServiceManager\ServiceManager;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManager;
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
use Zend\ServiceManager\ServiceLocatorInterface;

class Game
{
    use EventManagerAwareTrait;

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
     * @var UserServiceOptionsInterface
     */
    protected $options;

    protected $playerformMapper;

    protected $invitationMapper;

    protected $userMapper;
    
    protected $anonymousIdentifier = null;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceLocator;

    protected $event;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceLocator = $locator;
    }

    public function getEventManager() {
        if (null === $this->event) {
            $this->event = new EventManager($this->serviceLocator->get('SharedEventManager'), [get_class($this)]);
        }

        return $this->event;
    }

    public function getGameUserPath($game, $user)
    {
        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        $path .= 'game' . $game->getId() . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $path .= 'user'. $user->getId() . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    public function getGameUserMediaUrl($game, $user)
    {
        $media_url = $this->getOptions()->getMediaUrl() . '/';
        $media_url .= 'game' . $game->getId() . '/' . 'user'. $user->getId() . '/';

        return $media_url;
    }

    /**
     *
     * This service is ready for all types of games
     *
     * @param array $data
     * @param string $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function createOrUpdate(array $data, $game, $formClass)
    {
        $entityManager = $this->serviceLocator->get('doctrine.entitymanager.orm_default');

        $form = $this->serviceLocator->get($formClass);
        $form->get('publicationDate')->setOptions(array(
            'format' => 'Y-m-d H:i:s'
        ));
        $form->get('startDate')->setOptions(array(
            'format' => 'Y-m-d H:i:s'
        ));
        $form->get('endDate')->setOptions(array(
            'format' => 'Y-m-d H:i:s'
        ));
        $form->get('closeDate')->setOptions(array(
            'format' => 'Y-m-d H:i:s'
        ));

        $form->bind($game);

        $path = $this->getOptions()->getMediaPath() . '/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
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

        // I must switch from original format to the Y-m-d format because
        // this is the only one accepted by new DateTime($value)
        if (isset($data['publicationDate']) && $data['publicationDate']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y H:i:s', $data['publicationDate']);
            $data['publicationDate'] = $tmpDate->format('Y-m-d H:i:s');
        }
        if (isset($data['startDate']) && $data['startDate']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y H:i:s', $data['startDate']);
            $data['startDate'] = $tmpDate->format('Y-m-d H:i:s');
        }
        if (isset($data['endDate']) && $data['endDate']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y H:i:s', $data['endDate']);
            $data['endDate'] = $tmpDate->format('Y-m-d H:i:s');
        }
        if (isset($data['closeDate']) && $data['closeDate']) {
            $tmpDate = \DateTime::createFromFormat('d/m/Y H:i:s', $data['closeDate']);
            $data['closeDate'] = $tmpDate->format('Y-m-d H:i:s');
        }

        // If publicationDate is null, I update it with the startDate if not null neither
        if ((! isset($data['publicationDate']) || $data['publicationDate'] == '') &&
            (isset($data['startDate']) && $data['startDate'] != '')
        ) {
            $data['publicationDate'] = $data['startDate'];
        }

        // If the identifier has not been set, I use the title to create one.
        if ((! isset($data['identifier']) || empty($data['identifier'])) && isset($data['title'])) {
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
                $tmpDate = \DateTime::createFromFormat('Y-m-d H:i:s', $data['publicationDate']);
                $data['publicationDate'] = $tmpDate->format('d/m/Y H:i:s');
                $form->setData(array(
                    'publicationDate' => $data['publicationDate']
                ));
            }
            if (isset($data['startDate']) && $data['startDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d H:i:s', $data['startDate']);
                $data['startDate'] = $tmpDate->format('d/m/Y H:i:s');
                $form->setData(array(
                    'startDate' => $data['startDate']
                ));
            }
            if (isset($data['endDate']) && $data['endDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d H:i:s', $data['endDate']);
                $data['endDate'] = $tmpDate->format('d/m/Y H:i:s');
                $form->setData(array(
                    'endDate' => $data['endDate']
                ));
            }
            if (isset($data['closeDate']) && $data['closeDate']) {
                $tmpDate = \DateTime::createFromFormat('Y-m-d H:i:s', $data['closeDate']);
                $data['closeDate'] = $tmpDate->format('d/m/Y H:i:s');
                $form->setData(array(
                    'closeDate' => $data['closeDate']
                ));
            }
            return false;
        }

        $game = $form->getData();
        $game = $this->getGameMapper()->insert($game);

        // I wait for the game to be saved to obtain its ID.
        if (! empty($data['uploadMainImage']['tmp_name'])) {
            ErrorHandler::start();
            $data['uploadMainImage']['name'] = $this->fileNewname(
                $path,
                $game->getId() . "-" . $data['uploadMainImage']['name']
            );
            move_uploaded_file($data['uploadMainImage']['tmp_name'], $path . $data['uploadMainImage']['name']);
            $game->setMainImage($media_url . $data['uploadMainImage']['name']);
            ErrorHandler::stop(true);
        }

        if (isset($data['deleteMainImage']) &&
            $data['deleteMainImage'] &&
            empty($data['uploadMainImage']['tmp_name'])
        ) {
            ErrorHandler::start();
            $image = $game->getMainImage();
            $image = str_replace($media_url, '', $image);
            unlink($path . $image);
            $game->setMainImage(null);
            ErrorHandler::stop(true);
        }

        if (! empty($data['uploadSecondImage']['tmp_name'])) {
            ErrorHandler::start();
            $data['uploadSecondImage']['name'] = $this->fileNewname(
                $path,
                $game->getId() . "-" . $data['uploadSecondImage']['name']
            );
            move_uploaded_file($data['uploadSecondImage']['tmp_name'], $path . $data['uploadSecondImage']['name']);
            $game->setSecondImage($media_url . $data['uploadSecondImage']['name']);
            ErrorHandler::stop(true);
        }

        if (isset($data['deleteSecondImage']) &&
            $data['deleteSecondImage'] &&
            empty($data['uploadSecondImage']['tmp_name'])
        ) {
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
            $data['uploadFbShareImage']['name'] = $this->fileNewname(
                $path,
                $game->getId() . "-" . $data['uploadFbShareImage']['name']
            );
            move_uploaded_file($data['uploadFbShareImage']['tmp_name'], $path . $data['uploadFbShareImage']['name']);
            $game->setFbShareImage($media_url . $data['uploadFbShareImage']['name']);
            ErrorHandler::stop(true);
        }

        if (isset($data['deleteFbShareImage']) &&
            $data['deleteFbShareImage'] &&
            empty($data['uploadFbShareImage']['tmp_name'])
        ) {
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
            $this->resize(
                $data['uploadFbPageTabImage']['tmp_name'],
                $extension,
                $path . $game->getId() . "-" . $data['uploadFbPageTabImage']['name'],
                $src,
                111,
                74
            );

            $game->setFbPageTabImage($media_url . $game->getId() . "-" . $data['uploadFbPageTabImage']['name']);
            ErrorHandler::stop(true);
        }

        if (isset($data['deleteFbPageTabImage']) &&
            $data['deleteFbPageTabImage'] &&
            empty($data['uploadFbPageTabImage']['tmp_name'])
        ) {
            ErrorHandler::start();
            $image = $game->getFbPageTabImage();
            $image = str_replace($media_url, '', $image);
            unlink($path . $image);
            $game->setFbPageTabImage(null);
            ErrorHandler::stop(true);
        }

        // Let's remove the fbPostId if there is no post to send anymore
        if ($data['broadcastPostFacebook'] == 0) {
            $game->setFbPostId(null);
        }

        $game = $this->getGameMapper()->update($game);

        $prize_mapper = $this->serviceLocator->get('playgroundgame_prize_mapper');
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
                    $filename = "game-" . $game->getId() . "-prize-";
                    $filename .= $prize->getId() . "-" . $prize_data['picture_file']['name'];
                    move_uploaded_file($prize_data['picture_file']['tmp_name'], $path . $filename);
                    $prize->setPicture($media_url . $filename);
                    ErrorHandler::stop(true);
                    $prize_mapper->update($prize);
                }
            }
        }
        // If I receive false, it means that the FB Id was not available anymore
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array(
            'game' => $game
        ));

        return $game;
    }
    
    /**
     * getActiveGames
     *
     * @return Array of PlaygroundGame\Entity\Game
     */
    public function getActiveGames($displayHome = null, $classType = '', $order = '')
    {
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
        $today = new \DateTime("now");
        $today = $today->format('Y-m-d H:i:s');
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
        
        if ($displayHome !== null) {
            $boolVal = ($displayHome) ? 1 : 0;
            $and->add($qb->expr()->eq('g.displayHome', $boolVal));
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
                $key = $game->getPublicationDate()->format('Ymd');
            } elseif ($game->getStartDate()) {
                $key = $game->getStartDate()->format('Ymd');
            } else {
                $key = $game->getUpdatedAt()->format('Ymd');
            }
            $key .= $game->getUpdatedAt()->format('Ymd') . '-' . $game->getId();
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
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
        $today = new \DateTime("now");
        $today = $today->format('Y-m-d H:i:s');

        // Game active with a start_date before today (or without start_date)
        // and end_date after today (or without end-date)
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

    public function getEntriesQuery($game)
    {
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');

        $qb = $em->createQueryBuilder();
        $qb->select('
            e.id,
            u.username,
            u.title,
            u.firstname,
            u.lastname,
            u.email,
            u.optin,
            u.optinPartner,
            u.address,
            u.address2,
            u.postalCode,
            u.city,
            u.country,
            u.telephone,
            u.mobile,
            u.created_at,
            u.dob,
            e.winner,
            e.socialShares,
            e.playerData,
            e.geoloc,
            e.updated_at
            ')
            ->from('PlaygroundGame\Entity\Entry', 'e')
            ->leftJoin('e.user', 'u')
            ->where($qb->expr()->eq('e.game', ':game'));
        
        $qb->setParameter('game', $game);

        return $qb->getQuery();
    }

    public function getEntriesHeader($game)
    {
        if ($game->getAnonymousAllowed() && $game->getPlayerForm()) {
            $formPV = json_decode($game->getPlayerForm()->getForm(), true);
            $header = array('id'=> 1);
            foreach ($formPV as $element) {
                foreach ($element as $k => $v) {
                    if ($k !== 'form_properties') {
                        $header[$v[0]['name']] = 1;
                    }
                }
            }
        } elseif ($game->getAnonymousAllowed()) {
            $header = array(
                'id' => 1,
                'ip' => 1,
                'geoloc' => 1,
                'winner' => 1,
                'socialShares' => 1,
                'updated_at' => 1,
            );
        } else {
            $header = array(
                'id' => 1,
                'username' => 1,
                'title' => 1,
                'firstname' => 1,
                'lastname' => 1,
                'email' => 1,
                'optin' => 1,
                'optinPartner' => 1,
                'address' => 1,
                'address2' => 1,
                'postalCode' => 1,
                'city' => 1,
                'country' => 1,
                'telephone' => 1,
                'mobile' => 1,
                'created_at' => 1,
                'dob' => 1,
                'winner' => 1
            );
        }
        $header['geoloc'] = 1;
        $header['winner'] = 1;
        $header['socialShares'] = 1;
        $header['updated_at'] = 1;

        return $header;
    }

    /**
    * getGameEntries : I create an array of entries based on playerData + header
    *
    * @return Array of PlaygroundGame\Entity\Game
    */
    public function getGameEntries($header, $entries, $game)
    {
        $header = $this->getEntriesHeader($game);

        $results = array();

        foreach ($entries as $k => $entry) {
            $entryData = json_decode($entry['playerData'], true);
            foreach ($header as $key => $v) {
                if (isset($entryData[$key])) {
                    $results[$k][$key] = (is_array($entryData[$key]))?implode(', ', $entryData[$key]):$entryData[$key];
                } elseif (array_key_exists($key, $entry)) {
                    $results[$k][$key] = ($entry[$key] instanceof \DateTime)?
                        $entry[$key]->format('Y-m-d H:i:s'):
                        $entry[$key];
                } else {
                    $results[$k][$key] = '';
                }
            }
        }

        return $results;
    }

    /**
     * getActiveSliderGames
     *
     * @return Array of PlaygroundGame\Entity\Game
     */
    public function getActiveSliderGames()
    {
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
        $today = new \DateTime("now");
        $today = $today->format('Y-m-d H:i:s');

        // Game active with a start_date before today (or without start_date)
        // and end_date after today (or without end-date)
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
                $key = $game->getPublicationDate()->format('Ymd');
            } elseif ($game->getStartDate()) {
                $key = $game->getStartDate()->format('Ymd');
            } else {
                $key = $game->getUpdatedAt()->format('Ymd');
            }
            $key .= $game->getUpdatedAt()->format('Ymd') . '-' . $game->getId();
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
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');

        $query = $em->createQuery('SELECT g FROM PlaygroundGame\Entity\Game g
            WHERE (g.prizeCategory = :categoryid AND g.broadcastPlatform = 1)
            ORDER BY g.publicationDate DESC');
        $query->setParameter('categoryid', $categoryid);
        $games = $query->getResult();

        return $games;
    }

    public function getGameIdentifierFromFacebook($fbPageId)
    {
        $identifier = null;
        $game = $this->getGameMapper()->findOneBy(array('fbPageId' => $fbPageId, 'broadcastFacebook' => 1));

        if($game && $game->getIdentifier() !== null) {
            $identifier = $game->getIdentifier();
        }

        return $identifier;
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

        // for preview stuff as admin
        if ($this->isAllowed('game', 'edit')) {
            $r =$this->serviceLocator->get('request');
            if ($r->getQuery()->get('preview')) {
                $game->setActive(true);
                $game->setStartDate(null);
                $game->setEndDate(null);
                $game->setPublicationDate(null);
                $game->setBroadcastPlatform(true);

                // I don't want the game to be updated through any update during the preview mode.
                // I mark it as readonly for Doctrine
                $this->serviceLocator
                    ->get('doctrine.entitymanager.orm_default')
                    ->getUnitOfWork()
                    ->markReadOnly($game);
                    
                return $game;
            }
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
        $search = array('game'  => $game);

        if ($user) {
            $search['user'] = $user;
        } elseif ($this->getAnonymousIdentifier()) {
            $search['anonymousIdentifier'] = $this->getAnonymousIdentifier();
            $search['user'] = null;
        } else {
            $search['anonymousId'] = $this->getAnonymousId();
            $search['user'] = null;
        }
        
        if (! is_null($active)) {
            $search['active'] = $active;
        }
        if (! is_null($bonus)) {
            $search['bonus'] = $bonus;
        }

        $entry = $this->getEntryMapper()->findOneBy($search, array('updated_at' => 'desc'));

        return $entry;
    }

    /*
    * This function updates the entry with the player data after checking
    * that the data are compliant with the formUser Game attribute
    *
    * The $data has to be a json object
    */
    public function updateEntryPlayerForm($data, $game, $user, $entry, $mandatory = true)
    {
        $form = $this->createFormFromJson($game->getPlayerForm()->getForm(), 'playerForm');
        $form->setData($data);

        if (!$mandatory) {
            $filter = $form->getInputFilter();
            foreach ($form->getElements() as $element) {
                try {
                    $elementInput = $filter->get($element->getName());
                    $elementInput->setRequired(false);
                    $form->get($element->getName())->setAttribute('required', false);
                } catch (\Zend\Form\Exception\InvalidElementException $e) {
                }
            }
        }

        if ($form->isValid()) {
            $dataJson = json_encode($form->getData());

            if ($game->getAnonymousAllowed() &&
                $game->getAnonymousIdentifier() &&
                isset($data[$game->getAnonymousIdentifier()])
            ) {
                $session = new \Zend\Session\Container('anonymous_identifier');
                $anonymousIdentifier = $data[$game->getAnonymousIdentifier()];
                $entry->setAnonymousIdentifier($anonymousIdentifier);
                if (empty($session->offsetGet('anonymous_identifier'))) {
                    $session->offsetSet('anonymous_identifier', $anonymousIdentifier);
                }
            }

            $entry->setPlayerData($dataJson);
            $this->getEntryMapper()->update($entry);
        } else {
            return false;
        }

        return true;
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
        if (is_null($this->anonymousIdentifier) || $this->anonymousIdentifier === false) {
            $session = new Container('anonymous_identifier');
            
            if ($session->offsetExists('anonymous_identifier')) {
                $this->anonymousIdentifier = $session->offsetGet('anonymous_identifier');
            } else {
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

        // certaines participations peuvent rester ouvertes.
        // On autorise alors le joueur à reprendre là ou il en était
        // par exemple les postvote...
        $entry = $this->checkExistingEntry($game, $user, true);

        if (! $entry) {
            if ($this->hasReachedPlayLimit($game, $user)) {
                return false;
            }
            if (!$this->payToPlay($game, $user)) {
                return false;
            }

            $ip = $this->getIp();
            $geoloc = $this->getGeoloc($ip);
            $entry = new Entry();
            $entry->setGame($game);
            $entry->setUser($user);
            $entry->setPoints(0);
            $entry->setIp($ip);
            $entry->setGeoloc($geoloc);
            $entry->setAnonymousId($this->getAnonymousId());
            if ($this->getAnonymousIdentifier()) {
                $entry->setAnonymousIdentifier($this->getAnonymousIdentifier());
            }

            $entry = $this->getEntryMapper()->insert($entry);
            $this->getEventManager()->trigger(
                __FUNCTION__ . '.post',
                $this,
                [
                    'user' => $user,
                    'game' => $game,
                    'entry' => $entry,
                ]
            );
        }

        return $entry;
    }

    /**
     * @param \PlaygroundGame\Entity\Game $game
     * @param \PlaygroundUser\Entity\UserInterface $user
     */
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

    /**
     * If the game has a cost to be played (costToPlay>0)
     * I check and decrement the price from the leaderboard all of the user
     * 
     * @param \PlaygroundGame\Entity\Game $game
     * @param \PlaygroundUser\Entity\UserInterface $user
     */
    public function payToPlay($game, $user)
    {
        // Is there a limitation on the game ?
        $cost = $game->getCostToPlay();
        if ($cost && $cost > 0) {
            $availableAmount = $this->getEventManager()->trigger(
                'leaderboardUserTotal',
                $this,
                [
                    'user' => $user,
                ]
            )->last();
            if ($availableAmount && $availableAmount >= $cost) {
                $leaderboard = $this->getEventManager()->trigger(
                    'leaderboardUserUpdate',
                    $this,
                    [
                        'user' => $user,
                        'points' => -$cost,
                    ]
                )->last();

                if ($leaderboard->getTotalPoints() === ($availableAmount - $cost)) {
                    return true;
                }
            }
        } else {
            return true;
        }

        return false;
    }
    
    /**
     * @param \PlaygroundGame\Entity\Game $game
     * @param \PlaygroundUser\Entity\UserInterface $user
     */
    public function findLastEntries($game, $user, $limitScale)
    {
        $limitDate = $this->getLimitDate($limitScale);

        if ($user) {
            return $this->getEntryMapper()->countLastEntriesByUser($game, $user, $limitDate);
        } elseif ($this->getAnonymousIdentifier()) {
            $entries = $this->getEntryMapper()->countLastEntriesByAnonymousIdentifier(
                $game,
                $this->getAnonymousIdentifier(),
                $limitDate
            );

            return $entries;
        } else {
            // If the game is supposed to be a reguler user game or an anonymous identified game,
            // it means that the registration/login is at the end of the game
            if((!$user &&  !$game->getAnonymousAllowed()) || ($game->getAnonymousAllowed() && $game->getAnonymousIdentifier())) {
                return 0;
            }
            return $this->getEntryMapper()->countLastEntriesByIp($game, $this->getIp(), $limitDate);
        }
    }

    /**
    *
    *
    */
    public function getLimitDate($limitScale)
    {
        $now = new \DateTime("now");
        switch ($limitScale) {
            case 'always':
                $interval = 'P100Y';
                $now->sub(new \DateInterval($interval));
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            case 'day':
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            case 'week':
                $interval = 'P7D';
                $now->sub(new \DateInterval($interval));
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            case 'month':
                $interval = 'P1M';
                $now->sub(new \DateInterval($interval));
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            case 'year':
                $interval = 'P1Y';
                $now->sub(new \DateInterval($interval));
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
                break;
            default:
                $interval = 'P100Y';
                $now->sub(new \DateInterval($interval));
                $dateLimit = $now->format('Y-m-d') . ' 0:0:0';
        }

        return $dateLimit;
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

    public function inviteToTeam($data, $game, $user)
    {
        $mailService = $this->serviceLocator->get('playgroundgame_message');
        $invitationMapper = $this->serviceLocator->get('playgroundgame_invitation_mapper');

        $sentInvitations = $invitationMapper->findBy(array('host' => $user, 'game' => $game));
        $nbInvitations = count($sentInvitations);
        $to = $data['email'];
        if (empty($to)) {
            return ['result'=>false, 'message'=>'no email'];
        }

        if ($nbInvitations < 20) {
            $alreadyInvited = $invitationMapper->findBy(array('requestKey' => $to, 'game' => $game));
            if (!$alreadyInvited) {
                $alreadyInvited = $this->getUserMapper()->findByEmail($to);
            }

            if (empty($alreadyInvited)) {
                $invitation = new \PlaygroundGame\Entity\Invitation();
                $invitation->setRequestKey($to);
                $invitation->setGame($game);
                $invitation->setHost($user);
                $invitationMapper->insert($invitation);

                $from = $this->getOptions()->getEmailFromAddress();
                $subject = $this->serviceLocator->get('MvcTranslator')->translate(
                    $this->getOptions()->getInviteToTeamSubjectLine(),
                    'playgroundgame'
                );
                $message = $mailService->createHtmlMessage(
                    $from,
                    $to,
                    $subject,
                    'playground-game/email/invite_team',
                    array(
                        'game' => $game,
                        'user' => $user,
                        'data' => $data,
                        'from' => $from
                    )
                );
                try {
                    $mailService->send($message);
                } catch (\Zend\Mail\Protocol\Exception\RuntimeException $e) {
                    return ['result' => true, 'message' => $this->serviceLocator->get('MvcTranslator')->translate(
                        'mail error'
                    )];
                }

                return ['result' => true, 'message' => ''];
            } else {
                return ['result' => false, 'message' => 'already invited'];
            }
        } else {
            return [
                'result' => false,
                'message' => $this->serviceLocator->get('MvcTranslator')->translate(
                    'Too many invitations for this user'
                )
            ];
        }
    }

    public function sendShareMail(
        $data,
        $game,
        $user,
        $entry,
        $template = 'share_game',
        $subject = ''
    ) {
        $mailService = $this->serviceLocator->get('playgroundgame_message');
        $mailSent = false;
        $from = $this->getOptions()->getEmailFromAddress();

        if (empty($subject)) {
            $subject = $game->getEmailShareSubject();
        }

        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $skinUrl = $renderer->url(
            'frontend',
            array(),
            array('force_canonical' => true)
        );
        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true) . '####' . time()), 0, 15));

        if (isset($data['email']) && !is_array($data['email'])) {
            $data['email'] = array($data['email']);
        }
        
        foreach ($data['email'] as $to) {
            $mailSent = true;
            if (!empty($to)) {
                $message = $mailService->createHtmlMessage(
                    $from,
                    $to,
                    $subject,
                    'playground-game/email/' . $template,
                    array(
                        'game' => $game,
                        'data' => $data,
                        'from' => $from,
                        'to' => $to,
                        'secretKey' => $secretKey,
                        'skinUrl' => $skinUrl,
                        'message' => $game->getEmailShareMessage()
                    )
                );
                try {
                    $mailService->send($message);
                } catch (\Zend\Mail\Protocol\Exception\RuntimeException $e) {
                }
                
                if ($entry) {
                    $shares = json_decode($entry->getSocialShares(), true);
                    (!isset($shares['mail']))? $shares['mail'] = 1:$shares['mail'] += 1;
                }
            }
        }

        if ($mailSent) {
            if ($entry) {
                $sharesJson = json_encode($shares);
                $entry->setSocialShares($sharesJson);
                $entry = $this->getEntryMapper()->update($entry);
            }
            
            $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array(
                'user' => $user,
                'secretKey' => $secretKey,
                'data' => $data,
                'game' => $game,
                'entry' => $entry,
                'message' => $game->getEmailShareMessage()
            ));

            return true;
        }

        return false;
    }

    /**
     * @param \PlaygroundGame\Entity\Game $game
     * @param \PlaygroundUser\Entity\User $user
     * @param Entry $entry
     * @param \PlaygroundGame\Entity\Prize $prize
     */
    public function sendResultMail($game, $user, $entry, $template = 'entry', $prize = null)
    {
        $mailService = $this->serviceLocator->get('playgroundgame_message');
        $from = $this->getOptions()->getEmailFromAddress();
        if ($entry->getAnonymousIdentifier()) {
            $to = $entry->getAnonymousIdentifier();
        } elseif ($user) {
            $to = $user->getEmail();
        } else {
            return false;
        }
        $subject = $this->serviceLocator->get('MvcTranslator')->translate(
            $this->getOptions()->getParticipationSubjectLine(),
            'playgroundgame'
        );
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $skinUrl = $renderer->url(
            'frontend',
            array(),
            array('force_canonical' => true)
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
        $mailService = $this->serviceLocator->get('playgroundgame_message');
        $from = $this->getOptions()->getEmailFromAddress();
        $to = $user->getEmail();
        $subject = $this->serviceLocator->get('MvcTranslator')->translate(
            $this->getOptions()->getParticipationSubjectLine(),
            'playgroundgame'
        );
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $skinUrl = $renderer->url(
            'frontend',
            array(),
            array('force_canonical' => true)
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
        if (!isset($shares['fbwall'])) {
            $shares['fbwall'] = 1;
        } else {
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
        if (!isset($shares['fbrequest'])) {
            $shares['fbrequest'] = count($to);
        } else {
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
        if (!isset($shares['fbrequest'])) {
            $shares['tweet'] = 1;
        } else {
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
        if (!isset($shares['fbrequest'])) {
            $shares['google'] = 1;
        } else {
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
            if ($entry->getActive() == 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $path
     * @param boolean $noReplace : If the image name already exist, don't replace it but change the name
     */
    public function uploadFile($path, $file, $noReplace = true)
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
            $fileNewname = $this->fileNewname($path, $file['name'], $noReplace);

            if (isset($file["base64"])) {
                list(, $img) = explode(',', $file["base64"]);
                $img = str_replace(' ', '+', $img);
                $im = base64_decode($img);
                if ($im !== false) {
                    // getimagesizefromstring
                    file_put_contents($path . $fileNewname, $im);
                } else {
                    return 1;
                }

                return $fileNewname;
            } else {
                $adapter = new \Zend\File\Transfer\Adapter\Http();
                // 1Mo
                $size = new Size(array(
                    'max' => 1024000
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
            }

            
            if (class_exists("Imagick")) {
                $ext = pathinfo($fileNewname, PATHINFO_EXTENSION);
                $img = new \Imagick($path . $fileNewname);
                $img->cropThumbnailImage(100, 100);
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

    /**
     * @param string $path
     */
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
     * This function returns the list of games, ordered by $type
     */
    public function getQueryGamesOrderBy($type = 'createdAt', $order = 'DESC')
    {
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
        $today = new \DateTime("now");
        $today = $today->format('Y-m-d H:i:s');

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
        $zfcUserOptions = $this->serviceLocator->get('zfcuser_module_options');
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
            $this->gameMapper = $this->serviceLocator->get('playgroundgame_game_mapper');
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
            $this->entryMapper = $this->serviceLocator->get('playgroundgame_entry_mapper');
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
            $this->setOptions($this->serviceLocator
                ->get('playgroundgame_module_options'));
        }

        return $this->options;
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
        list($src_width, $src_height) = getimagesize($tmp_file);

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

        imagecopyresampled(
            $new_image_mini,
            $src,
            0 - ($new_width_mini - $mini_width) / 2,
            0 - ($new_height_mini - $mini_height) / 2,
            0,
            0,
            $new_width_mini,
            $new_height_mini,
            $src_width,
            $src_height
        );
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
        $auth = $this->serviceLocator->get('BjyAuthorize\Service\Authorize');

        return $auth->isAllowed($resource, $privilege);
    }

    public function getIp()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    public function getGeoloc($ip)
    {
        $geoloc = '';
        try {
            $res = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$ip));
            if($res['geoplugin_latitude'] != '') {
                $geoloc = $res['geoplugin_latitude'] . ',' . $res['geoplugin_longitude'];
            }
        } catch (\Exception $e) {

        } 

        return $geoloc;
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
     *  getCSV creates lines of CSV and returns it.
     */
    public function getCSV($array)
    {
        ob_start(); // buffer the output ...
        $out = fopen('php://output', 'w');
        fputcsv($out, array_keys($array[0]), ";");
        foreach ($array as $line) {
            fputcsv($out, $line, ";");
        }
        fclose($out);
        return ob_get_clean(); // ... then return it as a string!
    }
    
    public function getAttributes($attributes)
    {
        $a = array();

        $a['name']          = isset($attributes->name)? $attributes->name : '';
        $a['placeholder']   = isset($attributes->data->placeholder)? $attributes->data->placeholder : '';
        $a['label']         = isset($attributes->data->label)? $attributes->data->label : '';
        $a['required']      = (isset($attributes->data->required) && $attributes->data->required == 'true')?
            true:
            false;
        $a['class']         = isset($attributes->data->class)? $attributes->data->class : '';
        $a['id']            = isset($attributes->data->id)? $attributes->data->id : '';
        $a['lengthMin']     = isset($attributes->data->length)? $attributes->data->length->min : '';
        $a['lengthMax']     = isset($attributes->data->length)? $attributes->data->length->max : '';
        $a['validator']     = isset($attributes->data->validator)? $attributes->data->validator : '';
        $a['innerData']     = isset($attributes->data->innerData)? $attributes->data->innerData : array();
        $a['dropdownValues']= isset($attributes->data->dropdownValues)?
            $attributes->data->dropdownValues :
            array();
        $a['filesizeMin']   = isset($attributes->data->filesize)? $attributes->data->filesize->min : 0;
        $a['filesizeMax']   = isset($attributes->data->filesize)? $attributes->data->filesize->max : 10*1024*1024;
        $a['fileextension']   = isset($attributes->data->fileextension)?
            str_replace(', ', ',', $attributes->data->fileextension) :
            'png,jpg,jpeg,gif';

        // hiddenRequired('fileexcludeextension', '').appendTo(li);
        // hiddenRequired('filemimetype', '').appendTo(li);
        // hiddenRequired('fileexcludemimetype', '').appendTo(li);
        // hiddenRequired('fileexists', '').appendTo(li);
        // hiddenRequired('fileimagesize_minheight', '').appendTo(li);
        // hiddenRequired('fileimagesize_maxheight', '').appendTo(li);
        // hiddenRequired('fileimagesize_minwidth', '').appendTo(li);
        // hiddenRequired('fileimagesize_maxwidth', '').appendTo(li);
        // hiddenRequired('fileiscompressed', '').appendTo(li);
        // hiddenRequired('fileisimage', '').appendTo(li);
        // hiddenRequired('filewordcount_min', '').appendTo(li);
        // hiddenRequired('filewordcount_max', '').appendTo(li);

        return $a;
    }

    /**
     * @param \Zend\InputFilter\InputFilter $inputFilter
     */
    public function decorate($element, $attr, $inputFilter)
    {
        $factory = new InputFactory();
        $element->setAttributes(
            array(
                'placeholder'   => $attr['placeholder'],
                'required'      => $attr['required'],
                'class'         => $attr['class'],
                'id'            => $attr['id']
            )
        );

        $options = array();
        $options['encoding'] = 'UTF-8';
        if ($attr['lengthMin'] && $attr['lengthMin'] > 0) {
            $options['min'] = $attr['lengthMin'];
        }
        if ($attr['lengthMax'] && $attr['lengthMax'] > $attr['lengthMin']) {
            $options['max'] = $attr['lengthMax'];
            $element->setAttribute('maxlength', $attr['lengthMax']);
            $options['messages'] = array(
                \Zend\Validator\StringLength::TOO_LONG => sprintf(
                    $this->serviceLocator->get('MvcTranslator')->translate(
                        'This field contains more than %s characters',
                        'playgroundgame'
                    ),
                    $attr['lengthMax']
                )
            );
        }

        $validators = array(
            array(
                'name'    => 'StringLength',
                'options' => $options,
            ),
        );
        if ($attr['validator']) {
            $regex = "/.*\(([^)]*)\)/";
            preg_match($regex, $attr['validator'], $matches);
            $valArray = array(
                'name' => str_replace(
                    '('.$matches[1].')',
                    '',
                    $attr['validator']
                ),
                'options' => array($matches[1])
            );
            $validators[] = $valArray;
        }

        $inputFilter->add($factory->createInput(array(
            'name'     => $attr['name'],
            'required' => $attr['required'],
            'filters'  => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => $validators,
        )));

        return $element;
    }
    /**
     * Create a ZF2 Form from json data
     * @return Form
     */
    public function createFormFromJson($jsonForm, $id = 'jsonForm')
    {
        $formPV = json_decode($jsonForm);
        
        $form = new Form();
        $form->setAttribute('id', $id);
        $form->setAttribute('enctype', 'multipart/form-data');
        
        $inputFilter = new \Zend\InputFilter\InputFilter();
        $factory = new InputFactory();
        
        foreach ($formPV as $element) {
            if (isset($element->line_text)) {
                $attr  = $this->getAttributes($element->line_text[0]);
                $element = new Element\Text($attr['name']);
                $element = $this->decorate($element, $attr, $inputFilter);
                $form->add($element);
            }
            if (isset($element->line_password)) {
                $attr = $this->getAttributes($element->line_password[0]);
                $element = new Element\Password($attr['name']);
                $element = $this->decorate($element, $attr, $inputFilter);
                $form->add($element);
            }
            if (isset($element->line_hidden)) {
                $attr = $this->getAttributes($element->line_hidden[0]);
                $element = new Element\Hidden($attr['name']);
                $element = $this->decorate($element, $attr, $inputFilter);
                $form->add($element);
            }
            if (isset($element->line_email)) {
                $attr = $this->getAttributes($element->line_email[0]);
                $element = new Element\Email($attr['name']);
                $element = $this->decorate($element, $attr, $inputFilter);
                $form->add($element);
            }
            if (isset($element->line_radio)) {
                $attr = $this->getAttributes($element->line_radio[0]);
                $element = new Element\Radio($attr['name']);

                $element->setLabel($attr['label']);
                $element->setAttributes(
                    array(
                        'name'      => $attr['name'],
                        'required'  => $attr['required'],
                        'allowEmpty'=> !$attr['required'],
                        'class'     => $attr['class'],
                        'id'        => $attr['id']
                    )
                );
                $values = array();
                foreach ($attr['innerData'] as $value) {
                    $values[] = $value->label;
                }
                $element->setValueOptions($values);
        
                $options = array();
                $options['encoding'] = 'UTF-8';
                $options['disable_inarray_validator'] = true;
        
                $element->setOptions($options);
        
                $form->add($element);
        
                $inputFilter->add($factory->createInput(array(
                    'name'     => $attr['name'],
                    'required' => $attr['required'],
                    'allowEmpty' => !$attr['required'],
                )));
            }
            if (isset($element->line_checkbox)) {
                $attr = $this->getAttributes($element->line_checkbox[0]);
                $element = new Element\MultiCheckbox($attr['name']);
        
                $element->setLabel($attr['label']);
                $element->setAttributes(
                    array(
                        'name'      => $attr['name'],
                        'required'  => $attr['required'],
                        'allowEmpty'=> !$attr['required'],
                        'class'     => $attr['class'],
                        'id'        => $attr['id']
                    )
                );

                $values = array();
                foreach ($attr['innerData'] as $value) {
                    $values[] = $value->label;
                }
                $element->setValueOptions($values);
                $form->add($element);
        
                $options = array();
                $options['encoding'] = 'UTF-8';
                $options['disable_inarray_validator'] = true;
        
                $element->setOptions($options);
        
                $inputFilter->add($factory->createInput(array(
                    'name'      => $attr['name'],
                    'required'  => $attr['required'],
                    'allowEmpty'=> !$attr['required'],
                )));
            }
            if (isset($element->line_dropdown)) {
                $attr = $this->getAttributes($element->line_dropdown[0]);
                $element = new Element\Select($attr['name']);

                $element->setLabel($attr['label']);
                $element->setAttributes(
                    array(
                        'name'      => $attr['name'],
                        'required'  => $attr['required'],
                        'allowEmpty'=> !$attr['required'],
                        'class'     => $attr['class'],
                        'id'        => $attr['id']
                    )
                );
                $values = array();
                foreach ($attr['dropdownValues'] as $value) {
                    $values[] = $value->dropdown_label;
                }
                $element->setValueOptions($values);
                $form->add($element);
        
                $options = array();
                $options['encoding'] = 'UTF-8';
                $options['disable_inarray_validator'] = true;
        
                $element->setOptions($options);
        
                $inputFilter->add($factory->createInput(array(
                    'name'     => $attr['name'],
                    'required' => $attr['required'],
                    'allowEmpty' => !$attr['required'],
                )));
            }
            if (isset($element->line_paragraph)) {
                $attr = $this->getAttributes($element->line_paragraph[0]);
                $element = new Element\Textarea($attr['name']);
                $element = $this->decorate($element, $attr, $inputFilter);
                $form->add($element);
            }
            if (isset($element->line_upload)) {
                $attr = $this->getAttributes($element->line_upload[0]);
                $element = new Element\File($attr['name']);

                $element->setLabel($attr['label']);
                $element->setAttributes(
                    array(
                        'name'      => $attr['name'],
                        'required'  => $attr['required'],
                        'class'     => $attr['class'],
                        'id'        => $attr['id']
                    )
                );
                $form->add($element);

                $inputFilter->add($factory->createInput(array(
                    'name'     => $attr['name'],
                    'required' => $attr['required'],
                    'validators' => array(
                        array(
                            'name' => '\Zend\Validator\File\Size',
                            'options' => array('min' => $attr['filesizeMin'], 'max' => $attr['filesizeMax'])
                        ),
                        array(
                            'name' => '\Zend\Validator\File\Extension',
                            'options'  => array(
                                $attr['fileextension'],
                                'messages' => array(
                                    \Zend\Validator\File\Extension::FALSE_EXTENSION =>'Veuillez télécharger un fichier avec la bonne extension'
                                )
                            )
                        ),
                    ),
                )));
            }
        }
        
        $form->setInputFilter($inputFilter);
        
        return $form;
    }

    /**
     * Send mail for winner and/or loser
     * @param \PlaygroundGame\Entity\Game $game
     * @param \PlaygroundUser\Entity\User $user
     * @param \PlaygroundGame\Entity\Entry $lastEntry
     * @param \PlaygroundGame\Entity\Prize $prize
     */
    public function sendMail($game, $user, $lastEntry, $prize = null)
    {
        if (($user || ($game->getAnonymousAllowed() && $game->getAnonymousIdentifier())) &&
            $game->getMailWinner() &&
            $lastEntry->getWinner()
        ) {
            $this->sendResultMail($game, $user, $lastEntry, 'winner', $prize);
        }

        if (($user || ($game->getAnonymousAllowed() && $game->getAnonymousIdentifier())) &&
            $game->getMailLooser() &&
            !$lastEntry->getWinner()
        ) {
            $this->sendResultMail($game, $user, $lastEntry, 'looser');
        }

        if (($user || ($game->getAnonymousAllowed() && $game->getAnonymousIdentifier())) &&
            $game->getMailEntry()
        ) {
            $this->sendResultMail($game, $user, $lastEntry);
        }
    }

    public function getPlayerFormMapper()
    {
        if (null === $this->playerformMapper) {
            $this->playerformMapper = $this->serviceLocator->get('playgroundgame_playerform_mapper');
        }

        return $this->playerformMapper;
    }

    public function setPlayerFormMapper($playerformMapper)
    {
        $this->playerformMapper = $playerformMapper;

        return $this;
    }

    public function getInvitationMapper()
    {
        if (null === $this->invitationMapper) {
            $this->invitationMapper = $this->serviceLocator->get('playgroundgame_invitation_mapper');
        }

        return $this->invitationMapper;
    }

    public function setInvitationMapper($invitationMapper)
    {
        $this->invitationMapper = $invitationMapper;

        return $this;
    }

    /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getUserMapper()
    {
        if (null === $this->userMapper) {
            $this->userMapper = $this->serviceLocator->get('zfcuser_user_mapper');
        }

        return $this->userMapper;
    }

    /**
     * getUserMapper
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceLocator;
    }
}
