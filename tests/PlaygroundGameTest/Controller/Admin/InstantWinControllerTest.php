<?php

namespace PlaygroundGameTest\Controller\Admin;

use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class InstantWinControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    protected function setUp(): void
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
        ->setMethods(array('getGameMapper', 'getEntriesHeader', 'getEntriesQuery', 'getGameEntries', 'getCSV'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_game_service', $adminGameService);
        $serviceManager->setService('playgroundgame_instantwin_service', $adminGameService);

        $adminGameMapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWin')
        ->setMethods(array('findById'))
        ->disableOriginalConstructor()
        ->getMock();

        $adminGameService->expects($this->any())
        ->method('getGameMapper')
        ->will($this->returnValue($adminGameMapper));

        $game = new \PlaygroundGame\Entity\InstantWin();
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
            ->setMethods(array('getQuery'))
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->getMockBuilder('Result')
            ->setMethods(array('getResult'))
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue($result));

        $result->expects($this->any())
            ->method('getResult')
            ->will($this->returnValue(''));

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

        $prize = new \PlaygroundGame\Entity\Prize();
        $prize->setId(1);
        $prize->setTitle("awesome prize");

        $occurrence = new \PlaygroundGame\Entity\InstantWinOccurrence();
        $occurrence->setId(1);
        $occurrence->setPrize($prize);
        $occurrence->setEntry($entry);

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
        ->setMethods(array('getGameMapper', 'getEntriesHeader', 'getEntriesQuery', 'getGameEntries', 'getCSV'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_game_service', $adminGameService);
        $serviceManager->setService('playgroundgame_instantwin_service', $adminGameService);

        $adminGameMapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWin')
        ->setMethods(array('findById'))
        ->disableOriginalConstructor()
        ->getMock();

        $adminGameService->expects($this->any())
        ->method('getGameMapper')
        ->will($this->returnValue($adminGameMapper));

        $game = new \PlaygroundGame\Entity\InstantWin();
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
            ->setMethods(array('getQuery'))
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->getMockBuilder('Result')
            ->setMethods(array('getResult'))
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue($result));

        $result->expects($this->any())
            ->method('getResult')
            ->will($this->returnValue(''));

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

        $prize = new \PlaygroundGame\Entity\Prize();
        $prize->setId(1);
        $prize->setTitle("awesome prize");

        $occurrence = new \PlaygroundGame\Entity\InstantWinOccurrence();
        $occurrence->setId(1);
        $occurrence->setPrize($prize);
        $occurrence->setEntry($entry);

        $response = $this->dispatch('/admin/instantwin/download/1');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('playgroundgame');
        $this->assertControllerClass('InstantWinController');
        $this->assertActionName('download');
    }
}
