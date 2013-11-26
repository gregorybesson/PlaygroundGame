<?php

namespace PlaygroundGameTest\Controller\Admin;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class InstantWinControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../TestConfig.php'
        );

        parent::setUp();
    }

    public function testDownloadInstantWinEntryNotAnonymousAction()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminGameService = $this->getMockBuilder('PlaygroundGame\Service\InstantWin')
        ->setMethods(array('getGameMapper', 'getEntryMapper', 'getInstantWinOccurrenceMapper'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_game_service', $adminGameService);
        $serviceManager->setService('playgroundgame_instantwin_service', $adminGameService);

        $adminGameMapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWin')
        ->setMethods(array('findById'))
        ->disableOriginalConstructor()
        ->getMock();

        $adminGameOccurrenceMapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->setMethods(array('findByEntry'))
        ->disableOriginalConstructor()
        ->getMock();

        $adminGameService->expects($this->any())
        ->method('getGameMapper')
        ->will($this->returnValue($adminGameMapper));

        $adminGameService->expects($this->any())
        ->method('getInstantWinOccurrenceMapper')
        ->will($this->returnValue($adminGameOccurrenceMapper));

        $game = new \PlaygroundGame\Entity\InstantWin();
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
        $entry->setPoints('DesPoints1');
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

        $prize = new \PlaygroundGame\Entity\Prize();
        $prize->setId(1);
        $prize->setTitle("awesome prize");

        $occurrence = new \PlaygroundGame\Entity\InstantWinOccurrence();
        $occurrence->setId(1);
        $occurrence->setPrize($prize);
        $occurrence->setEntry($entry);

        $adminGameOccurrenceMapper->expects($this->any())
        ->method('findByEntry')
        ->will($this->returnValue($occurrence));

        $adminEntryMapper->expects($this->any())
        ->method('findBy')
        ->will($this->returnValue(array($entry)));

        $response = $this->dispatch('/admin/instantwin/download/1');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('playgroundgame');
        $this->assertControllerClass('InstantWinController');
        $this->assertActionName('download');
    }

    public function testDownloadInstantWinEntryAnonymousAction()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminGameService = $this->getMockBuilder('PlaygroundGame\Service\InstantWin')
        ->setMethods(array('getGameMapper', 'getEntryMapper', 'getInstantWinOccurrenceMapper'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_game_service', $adminGameService);
        $serviceManager->setService('playgroundgame_instantwin_service', $adminGameService);

        $adminGameMapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWin')
        ->setMethods(array('findById'))
        ->disableOriginalConstructor()
        ->getMock();

        $adminGameOccurrenceMapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->setMethods(array('findByEntry'))
        ->disableOriginalConstructor()
        ->getMock();

        $adminGameService->expects($this->any())
        ->method('getGameMapper')
        ->will($this->returnValue($adminGameMapper));

        $adminGameService->expects($this->any())
        ->method('getInstantWinOccurrenceMapper')
        ->will($this->returnValue($adminGameOccurrenceMapper));

        $game = new \PlaygroundGame\Entity\InstantWin();
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
        $entry->setPoints('DesPoints1');
        $entry->setWinner('Winners');
        $entry->setCreatedAt($now);
        $entry->setPlayerData('{"name":"User Name", "other":"some data"}');

        $prize = new \PlaygroundGame\Entity\Prize();
        $prize->setId(1);
        $prize->setTitle("awesome prize");

        $occurrence = new \PlaygroundGame\Entity\InstantWinOccurrence();
        $occurrence->setId(1);
        $occurrence->setPrize($prize);
        $occurrence->setEntry($entry);

        $adminGameOccurrenceMapper->expects($this->any())
        ->method('findByEntry')
        ->will($this->returnValue($occurrence));

        $adminEntryMapper->expects($this->any())
        ->method('findBy')
        ->will($this->returnValue(array($entry)));

        $response = $this->dispatch('/admin/instantwin/download/1');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('playgroundgame');
        $this->assertControllerClass('InstantWinController');
        $this->assertActionName('download');
    }

}