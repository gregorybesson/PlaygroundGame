<?php

namespace PlaygroundGameTest\Controller\Admin;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use PlaygroundGameTest\Bootstrap;
use PlaygroundGame\Entity\Game as GameEntity;
use PlaygroundGame\Controller\Admin\GameController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Router\Http\RouteMatch;

class GameControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../TestConfig.php'
        );

        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_entry_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);

        $this->controller = new GameController(Bootstrap::getServiceManager());
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => PlaygroundGame\Controller\Admin\Game::class));
        $this->event      = new MvcEvent();

        parent::setUp();
    }

    public function testListAction()
    {
        $this->routeMatch->setParam('type', 'value');
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);

        $result = $this->controller->listAction();
        $this->assertInternalType('array', $result);
        //$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
 
        // Test the parameters contained in the View model
        //$vars = $result->getVariables();
        $this->assertTrue(isset($result['type']));
    }

    public function testDownloadAction()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminGameService = $this->getMockBuilder('PlaygroundGame\Service\Game')
            ->setMethods(array('getGameMapper', 'getEntriesHeader', 'getEntriesQuery', 'getGameEntries', 'getCSV'))
            ->disableOriginalConstructor()
            ->getMock();

        $serviceManager->setService('playgroundgame_game_service', $adminGameService);

        $adminGameMapper = $this->getMockBuilder('PlaygroundGame\Mapper\Game')
            ->setMethods(array('findById'))
            ->disableOriginalConstructor()
            ->getMock();

        $adminGameService->expects($this->any())
            ->method('getGameMapper')
            ->will($this->returnValue($adminGameMapper));

        $now = new \DateTime();
        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);
        $game->setCreatedAt($now);

        $adminGameMapper->expects($this->any())
            ->method('findById')
            ->will($this->returnValue($game));

        $adminGameService->expects($this->any())
            ->method('getEntriesHeader')
            ->will($this->returnValue(array()));

        $query = $this->getMockBuilder('Query')
            ->setMethods(array('getResult'))
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->any())
            ->method('getResult')
            ->will($this->returnValue(true));

        $adminGameService->expects($this->any())
            ->method('getEntriesQuery')
            ->will($this->returnValue($query));

        $adminGameService->expects($this->any())
            ->method('getGameEntries')
            ->will($this->returnValue(array()));

        $adminGameService->expects($this->any())
            ->method('getCSV')
            ->will($this->returnValue(''));


        $entry = new \PlaygroundGame\Entity\Entry();
        $entry->setPoints(1);
        $user = new \PlaygroundUser\Entity\User();
        $user->setId(1);
        $user->setUsername('Username');
        $user->setLastname('Lastname');
        $user->setFirstname('Firstname');
        $user->setEmail('mail@mail.fr');
        $user->setOptinPartner('OptinPartner');
        $user->setCreatedAt($now);
        $entry->setUser($user);
        $entry->setWinner('Winners');
        $entry->setCreatedAt($now);

        $this->dispatch('/admin/game/download/1');
        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\admin\game');
        $this->assertControllerClass('GameController');
        $this->assertActionName('download');
    }

    public function testDownloadGameEntryNotAnonymousAction()
    {
        foreach (array('Lottery') as $type) {
            $serviceManager = $this->getApplicationServiceLocator();
            $serviceManager->setAllowOverride(true);

            $adminGameService = $this->getMockBuilder('PlaygroundGame\Service\\'.$type)
            ->setMethods(array('getGameMapper', 'getEntriesHeader', 'getEntriesQuery', 'getGameEntries', 'getCSV'))
            ->disableOriginalConstructor()
            ->getMock();

            $serviceManager->setService('playgroundgame_game_service', $adminGameService);
            $serviceManager->setService('playgroundgame_'.strtolower($type).'_service', $adminGameService);

            $adminGameMapper = $this->getMockBuilder('PlaygroundGame\Mapper\\'.$type)
            ->setMethods(array('findById'))
            ->disableOriginalConstructor()
            ->getMock();

            $adminGameService->expects($this->any())
            ->method('getGameMapper')
            ->will($this->returnValue($adminGameMapper));

            $entityClassName = '\PlaygroundGame\Entity\\'.$type;
            $game = new $entityClassName();
            $game->setTitle('CeciEstUnTitre');
            $game->setIdentifier('gameid');
            $game->setId(1);
            $game->setAnonymousAllowed(0);

            $adminGameMapper->expects($this->any())
            ->method('findById')
            ->will($this->returnValue($game));

            $adminGameService->expects($this->any())
            ->method('getEntriesHeader')
            ->will($this->returnValue(array()));

            $query = $this->getMockBuilder('Query')
                ->setMethods(array('getResult'))
                ->disableOriginalConstructor()
                ->getMock();

            $query->expects($this->any())
                ->method('getResult')
                ->will($this->returnValue(true));

            $adminGameService->expects($this->any())
                ->method('getEntriesQuery')
                ->will($this->returnValue($query));

            $adminGameService->expects($this->any())
                ->method('getGameEntries')
                ->will($this->returnValue(array()));

            $adminGameService->expects($this->any())
                ->method('getCSV')
                ->will($this->returnValue(''));

            $now = new \DateTime();
            $entry = new \PlaygroundGame\Entity\Entry();
            $entry->setPoints(1);
            $user = new \PlaygroundUser\Entity\User();
            $user->setId(1);
            $user->setUsername('Username');
            $user->setLastname('Lastname');
            $user->setFirstname('Firstname');
            $user->setEmail('mail@mail.fr');
            $user->setOptinPartner('OptinPartner');
            $user->setCreatedAt($now);
            $entry->setUser($user);
            $entry->setWinner('Winners');
            $entry->setCreatedAt($now);

            $response = $this->dispatch('/admin/'.strtolower($type).'/download/1');
            $this->assertResponseStatusCode(200);
            $this->assertModuleName('playgroundgame');
            $this->assertControllerClass($type.'Controller');
            $this->assertActionName('download');
        }
    }

    public function testDownloadGameEntryAnonymousAction()
    {
        foreach (array('Lottery') as $type) {
            $serviceManager = $this->getApplicationServiceLocator();
            $serviceManager->setAllowOverride(true);

            $adminGameService = $this->getMockBuilder('PlaygroundGame\Service\\'.$type)
            ->setMethods(array('getGameMapper', 'getEntriesHeader', 'getEntriesQuery', 'getGameEntries', 'getCSV'))
            ->disableOriginalConstructor()
            ->getMock();

            $serviceManager->setService('playgroundgame_'.strtolower($type).'_service', $adminGameService);

            $adminGameMapper = $this->getMockBuilder('PlaygroundGame\Mapper\\'.$type)
            ->setMethods(array('findById'))
            ->disableOriginalConstructor()
            ->getMock();

            $adminGameService->expects($this->any())
            ->method('getGameMapper')
            ->will($this->returnValue($adminGameMapper));

            $entityClassName = '\PlaygroundGame\Entity\\'.$type;
            $game = new $entityClassName();
            $game->setTitle('CeciEstUnTitre');
            $game->setIdentifier('gameid');
            $game->setId(1);
            $game->setAnonymousAllowed(1);

            $adminGameMapper->expects($this->any())
            ->method('findById')
            ->will($this->returnValue($game));

            $adminGameService->expects($this->any())
            ->method('getEntriesHeader')
            ->will($this->returnValue(array()));

            $query = $this->getMockBuilder('Query')
                ->setMethods(array('getResult'))
                ->disableOriginalConstructor()
                ->getMock();

            $query->expects($this->any())
                ->method('getResult')
                ->will($this->returnValue(true));

            $adminGameService->expects($this->any())
                ->method('getEntriesQuery')
                ->will($this->returnValue($query));

            $adminGameService->expects($this->any())
                ->method('getGameEntries')
                ->will($this->returnValue(array()));

            $adminGameService->expects($this->any())
                ->method('getCSV')
                ->will($this->returnValue(''));

            $now = new \DateTime();
            $entry = new \PlaygroundGame\Entity\Entry();
            $entry->setPoints(1);
            $entry->setWinner('Winners');
            $entry->setCreatedAt($now);
            $entry->setPlayerData('{"name":"User Name", "other":"some data"}');

            $response = $this->dispatch('/admin/'.strtolower($type).'/download/1');
            $this->assertResponseStatusCode(200);
            $this->assertModuleName('playgroundgame');
            $this->assertControllerClass($type.'Controller');
            $this->assertActionName('download');
        }
    }

    public function testRemoveAction()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminGameService = $this->getMockBuilder('PlaygroundGame\Service\Game')
            ->setMethods(array('getGameMapper', 'getEntryMapper'))
            ->disableOriginalConstructor()
            ->getMock();

        $serviceManager->setService('playgroundgame_game_service', $adminGameService);

        $adminGameMapper = $this->getMockBuilder('PlaygroundGame\Mapper\Game')
            ->setMethods(array('findById', 'remove'))
            ->disableOriginalConstructor()
            ->getMock();

        $adminGameService->expects($this->any())
            ->method('getGameMapper')
            ->will($this->returnValue($adminGameMapper));

        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);


        $adminGameMapper->expects($this->any())
            ->method('findById')
            ->will($this->returnValue($game));

        $this->dispatch('/admin/game/remove/1');
        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\admin\game');
        $this->assertControllerClass('GameController');
        $this->assertActionName('remove');

        $this->assertRedirectTo('/fr_FR/admin/game/list/createdAt/DESC');
    }

    // public function testSetActiveAction()
    // {
        /*$serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminGameService = $this->getMockBuilder('PlaygroundGame\Service\Game')
            ->setMethods(array('getGameMapper'))
            ->disableOriginalConstructor()
            ->getMock();

        $serviceManager->setService('playgroundgame_game_service', $adminGameService);

        $adminGameMapper = $this->getMockBuilder('PlaygroundGame\Mapper\Game')
            ->setMethods(array('findById', 'update'))
            ->disableOriginalConstructor()
            ->getMock();

        $adminGameService->expects($this->any())
            ->method('getGameMapper')
            ->will($this->returnValue($adminGameMapper));

        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);

        $adminGameMapper->expects($this->any())
            ->method('findById')
            ->will($this->returnValue($game));

        $adminGameMapper->expects($this->any())
            ->method('update');

        $this->dispatch('/admin/game/set-active/1');
        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgameadmin');
        $this->assertControllerClass('AdminController');
        $this->assertActionName('set-active');

        $this->assertRedirectTo('/admin/game/list/createdAt/DESC');*/
    //}

    public function tearDown()
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $this->sm = null;
        $this->em = null;
        unset($this->sm);
        unset($this->em);
        parent::tearDown();
    }
}
