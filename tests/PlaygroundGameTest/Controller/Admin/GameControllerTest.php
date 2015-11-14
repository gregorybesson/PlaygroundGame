<?php

namespace PlaygroundGameTest\Controller\Admin;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use \PlaygroundGame\Entity\Game as GameEntity;

class GameControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../TestConfig.php'
        );

        parent::setUp();
    }

    public function testDownloadAction()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminGameService = $this->getMockBuilder('PlaygroundGame\Service\Game')
            ->setMethods(array('getGameMapper', 'getEntryMapper'))
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

        $adminEntryMapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
            ->setMethods(array('findBy'))
            ->disableOriginalConstructor()
            ->getMock();

        $adminGameService->expects($this->any())
            ->method('getEntryMapper')
            ->will($this->returnValue($adminEntryMapper));

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

        $adminEntryMapper->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue(array($entry)));

        $this->dispatch('/admin/game/download/1');
        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame_admin_game');
        $this->assertControllerClass('GameController');
        $this->assertActionName('download');
        //$this->assertMatchedRouteName('frontend/lottery');
    }

    public function testDownloadGameEntryNotAnonymousAction()
    {
        foreach (array('Lottery') as $type) {

            $serviceManager = $this->getApplicationServiceLocator();
            $serviceManager->setAllowOverride(true);

            $adminGameService = $this->getMockBuilder('PlaygroundGame\Service\\'.$type)
            ->setMethods(array('getGameMapper', 'getEntryMapper'))
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

            $adminEntryMapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
            ->setMethods(array('findBy'))
            ->disableOriginalConstructor()
            ->getMock();

            $adminGameService->expects($this->any())
            ->method('getEntryMapper')
            ->will($this->returnValue($adminEntryMapper));

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

            $adminEntryMapper->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue(array($entry)));

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
            ->setMethods(array('getGameMapper', 'getEntryMapper'))
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

            $adminEntryMapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
            ->setMethods(array('findBy'))
            ->disableOriginalConstructor()
            ->getMock();

            $adminGameService->expects($this->any())
            ->method('getEntryMapper')
            ->will($this->returnValue($adminEntryMapper));

            $now = new \DateTime();
            $entry = new \PlaygroundGame\Entity\Entry();
            $entry->setPoints(1);
            $entry->setWinner('Winners');
            $entry->setCreatedAt($now);
            $entry->setPlayerData('{"name":"User Name", "other":"some data"}');

            $adminEntryMapper->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue(array($entry)));

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
        $this->assertControllerName('playgroundgame_admin_game');
        $this->assertControllerClass('GameController');
        $this->assertActionName('remove');

        $this->assertRedirectTo('/admin/game/list/createdAt/DESC');

    }

    public function testSetActiveAction()
    {
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

    }


}