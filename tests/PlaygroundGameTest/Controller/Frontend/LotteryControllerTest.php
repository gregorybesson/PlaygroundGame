<?php

namespace PlaygroundGameTest\Controller\Frontend;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use \PlaygroundGame\Entity\Game as GameEntity;

class LotteryControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../TestConfig.php'
        );

        parent::setUp();
    }

    public function testIndexActionNonExistentGame()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

        $game = new GameEntity();
        $game->setFbFan(true);

        //mocking the method checkExistingEntry
        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(array('checkGame', 'checkIsFan'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_lottery_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue(false));

         $ZfcUserMock = $this->getMock('ZfcUser\Entity\User');

         $ZfcUserMock->expects($this->any())
         ->method('getId')
         ->will($this->returnValue('1'));

         $authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

         $authMock->expects($this->any())
         ->method('hasIdentity')
         -> will($this->returnValue(true));

         $authMock->expects($this->any())
         ->method('getIdentity')
         ->will($this->returnValue($ZfcUserMock));

         $pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/fake');
    	$this->assertResponseStatusCode(404);
    }

    public function testIndexActionNotFanOnFacebook()
    {

    	$serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $pluginManager = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

        $game = new GameEntity();
        $game->setFbFan(true);
        $game->setIdentifier('gameid');
        $game->setClassType('lottery');

        //mocking the method checkExistingEntry
        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(array('checkGame', 'checkIsFan'))
        //->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_lottery_service', $f);

        $ZfcUserMock = $this->getMock('ZfcUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->exactly(2))
    	->method('checkGame')
    	->will($this->returnValue($game));

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkIsFan')
    	->will($this->returnValue(false));

    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);
    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	$this->dispatch('/loterie/gameid');


    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('home');
    	$this->assertMatchedRouteName('frontend/lottery');

    	$this->assertRedirectTo('/loterie/gameid/fangate');

    	//$postData = array('title' => 'Led Zeppelin III', 'artist' => 'Led Zeppelin');
    	//$this->dispatch('/album/add', 'POST', $postData);
    	//$this->assertResponseStatusCode(302);
    }

    public function testIndexActionNoEntry()
    {

    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setFbFan(true);
    	$game->setIdentifier('gameid');
    	$game->setClassType('lottery');

    	//mocking the method checkExistingEntry
    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
    	//->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	$ZfcUserMock = $this->getMock('ZfcUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->exactly(2))
    	->method('checkGame')
    	->will($this->returnValue($game));

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkIsFan')
    	->will($this->returnValue(true));

    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue(false));

    	$this->dispatch('/loterie/gameid');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('home');
    	$this->assertMatchedRouteName('frontend/lottery');
    }

    public function testIndexActionCustomizedLayoutWithEntry()
    {

    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setFbFan(true);
    	$game->setIdentifier('gameid');
    	$game->setClassType('lottery');
    	$game->setStylesheet('skin');
    	$game->setLayout('playground-game/layout/2columns-right.phtml');

    	$entry = new \PlaygroundGame\Entity\Entry();

    	//mocking the method checkExistingEntry
    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
    	//->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	$ZfcUserMock = $this->getMock('ZfcUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->exactly(2))
    	->method('checkGame')
    	->will($this->returnValue($game));

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkIsFan')
    	->will($this->returnValue(true));

    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue($entry));

    	$this->dispatch('/loterie/gameid');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('home');
    	$this->assertMatchedRouteName('frontend/lottery');
    }

    public function testResultActionNonExistentGame()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
    	//->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
     	$f->expects($this->once())
     	->method('checkGame')
     	->will($this->returnValue(false));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/resultat');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('not-found');
    	$this->assertMatchedRouteName('frontend/lottery/result');
    	$this->assertResponseStatusCode(404);
    }

    public function testResultActionExistentGameNoEntry()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'getEntryMapper', 'getServiceManager'))
    	//->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('ZfcUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$bitlyMock = $this->getMock('PlaygroundCore\Controller\Plugin\ShortenUrl');

    	$bitlyMock->expects($this->any())
    	->method('shortenUrl')
    	->will($this->returnValue('http://shorturl.com/shurl'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$entryMock = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
    		->disableOriginalConstructor()
    		->getMock();

    	$f->expects($this->once())
    	->method('getEntryMapper')
    	->will($this->returnValue($entryMock));

    	$entryMock->expects($this->once())
    	->method('findLastInactiveEntryById')
    	->will($this->returnValue(false));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);
    	$pluginManager->setService('shortenUrl', $bitlyMock);

    	$this->dispatch('/loterie/gameid/resultat');

    	$this->assertModuleName('playgroundgame');
    	$this->assertActionName('result');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertMatchedRouteName('frontend/lottery/result');

    	$this->assertRedirectTo('/loterie/gameid');
    }

    public function testPlayActionNonExistentGame()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
     	$f->expects($this->once())
     	->method('checkGame')
     	->will($this->returnValue(false));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/jouer');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('not-found');
    	$this->assertMatchedRouteName('frontend/lottery/play');
    	$this->assertResponseStatusCode(404);
    }

    public function testPlayActionClosedGame()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue(false));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/jouer');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('not-found');
    	$this->assertMatchedRouteName('frontend/lottery/play');
    	$this->assertResponseStatusCode(404);
    }

    public function testPlayActionNoUser()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue(false));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/jouer');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('play');
    	$this->assertMatchedRouteName('frontend/lottery/play');

    	$this->assertRedirectTo('/mon-compte/inscription?redirect=%2Floterie%2Fgameid%2Fjouer');
    }

    public function testPlayActionNoEntry()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$f->expects($this->once())
    	->method('play')
    	->will($this->returnValue(false));

    	$this->dispatch('/loterie/gameid/jouer');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('play');
    	$this->assertMatchedRouteName('frontend/lottery/play');

    	$this->assertRedirectTo('/loterie/gameid/resultat');
    }

    public function testPlayActionEntry()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setClassType('lottery');
    	$game->setIdentifier('gameid');

    	$entry = new \PlaygroundGame\Entity\Entry();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getEntryMapper', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$f->expects($this->once())
    	->method('play')
    	->will($this->returnValue($entry));

    	$entryMock = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
    	->disableOriginalConstructor()
    	->getMock();

    	$f->expects($this->once())
    	->method('getEntryMapper')
    	->will($this->returnValue($entryMock));

    	$entryMock->expects($this->once())
    	->method('update')
    	->will($this->returnValue($entry));

    	$this->dispatch('/loterie/gameid/jouer');

    	$this->assertEquals(false, $entry->getWinner());
    	$this->assertEquals(false, $entry->getActive());
    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('play');
    	$this->assertMatchedRouteName('frontend/lottery/play');

    	$this->assertRedirectTo('/loterie/gameid/resultat');
    }

    public function testFbshareActionNoGame()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$entry = new \PlaygroundGame\Entity\Entry();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'postFbWall', 'checkExistingEntry', 'getEntryMapper', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue(false));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$f->expects($this->never())
    	->method('postFbWall')
    	->will($this->returnValue(true));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/fbshare');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('fbshare');
    	$this->assertMatchedRouteName('frontend/lottery/fbshare');

    	//TODO : check content of json response
    }

    public function testFbshareActionNoSubscription()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$entry = new \PlaygroundGame\Entity\Entry();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'postFbWall', 'checkExistingEntry', 'getEntryMapper', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue(false));

    	$f->expects($this->never())
    	->method('postFbWall')
    	->will($this->returnValue(true));

    	$this->dispatch('/loterie/gameid/fbshare');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('fbshare');
    	$this->assertMatchedRouteName('frontend/lottery/fbshare');

    	//TODO : check content of json response
    }

    public function testFbshareActionNoFbid()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$entry = new \PlaygroundGame\Entity\Entry();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'postFbWall', 'checkExistingEntry', 'getEntryMapper', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue($entry));

    	$f->expects($this->never())
    	->method('postFbWall')
    	->will($this->returnValue(true));

    	$this->dispatch('/loterie/gameid/fbshare');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('fbshare');
    	$this->assertMatchedRouteName('frontend/lottery/fbshare');

    	//TODO : check content of json response
    }

    public function testFbshareActionPostFbWall()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$entry = new \PlaygroundGame\Entity\Entry();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'playBonus', 'checkExistingEntry', 'postFbWall', 'getEntryMapper', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->exactly(2))
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue($entry));

    	$f->expects($this->once())
    	->method('postFbWall')
    	->will($this->returnValue(true));

    	$f->expects($this->once())
    	->method('playBonus')
    	->will($this->returnValue($entry));

    	$getData = array('fbId' => 'xx-0000-xx');
    	$this->dispatch('/loterie/gameid/fbshare', 'GET', $getData );

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('fbshare');
    	$this->assertMatchedRouteName('frontend/lottery/fbshare');

    	//TODO : check content of json response
    }

    public function testTweetActionNoGame()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$entry = new \PlaygroundGame\Entity\Entry();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'postTwitter', 'checkExistingEntry', 'getEntryMapper', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue(false));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$f->expects($this->never())
    	->method('postTwitter')
    	->will($this->returnValue(true));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/tweet');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('tweet');
    	$this->assertMatchedRouteName('frontend/lottery/tweet');

    	//TODO : check content of json response
    }

    public function testTweetActionNoSubscription()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$entry = new \PlaygroundGame\Entity\Entry();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'postTwitter', 'checkExistingEntry', 'getEntryMapper', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue(false));

    	$f->expects($this->never())
    	->method('postTwitter')
    	->will($this->returnValue(true));

    	$this->dispatch('/loterie/gameid/tweet');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('tweet');
    	$this->assertMatchedRouteName('frontend/lottery/tweet');

    	//TODO : check content of json response
    }

    public function testTweetActionNoFbid()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$entry = new \PlaygroundGame\Entity\Entry();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'postTwitter', 'checkExistingEntry', 'getEntryMapper', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue($entry));

    	$f->expects($this->never())
    	->method('postTwitter')
    	->will($this->returnValue(true));

    	$this->dispatch('/loterie/gameid/tweet');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('tweet');
    	$this->assertMatchedRouteName('frontend/lottery/tweet');

    	//TODO : check content of json response
    }

    public function testTweetActionPostTweet()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$entry = new \PlaygroundGame\Entity\Entry();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'playBonus', 'checkExistingEntry', 'postTwitter', 'getEntryMapper', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->exactly(2))
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue($entry));

    	$f->expects($this->once())
    	->method('postTwitter')
    	->will($this->returnValue(true));

    	$f->expects($this->once())
    	->method('playBonus')
    	->will($this->returnValue($entry));

    	$getData = array('tweetId' => 'xx-0000-xx');
    	$this->dispatch('/loterie/gameid/tweet', 'GET', $getData );

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('tweet');
    	$this->assertMatchedRouteName('frontend/lottery/tweet');

    	//TODO : check content of json response
    }

    public function testGoogleActionNoGame()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$entry = new \PlaygroundGame\Entity\Entry();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'postGoogle', 'checkExistingEntry', 'getEntryMapper', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue(false));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$f->expects($this->never())
    	->method('postGoogle')
    	->will($this->returnValue(true));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/google');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('google');
    	$this->assertMatchedRouteName('frontend/lottery/google');

    	//TODO : check content of json response
    }

    public function testGoogleActionNoSubscription()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$entry = new \PlaygroundGame\Entity\Entry();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'postGoogle', 'checkExistingEntry', 'getEntryMapper', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue(false));

    	$f->expects($this->never())
    	->method('postGoogle')
    	->will($this->returnValue(true));

    	$this->dispatch('/loterie/gameid/google');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('google');
    	$this->assertMatchedRouteName('frontend/lottery/google');

    	//TODO : check content of json response
    }

    public function testGoogleActionNoFbid()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$entry = new \PlaygroundGame\Entity\Entry();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'postGoogle', 'checkExistingEntry', 'getEntryMapper', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue($entry));

    	$f->expects($this->never())
    	->method('postGoogle')
    	->will($this->returnValue(true));

    	$this->dispatch('/loterie/gameid/google');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('google');
    	$this->assertMatchedRouteName('frontend/lottery/google');

    	//TODO : check content of json response
    }

    public function testGoogleActionPostGoogle()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$entry = new \PlaygroundGame\Entity\Entry();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'playBonus', 'checkExistingEntry', 'postGoogle', 'getEntryMapper', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->exactly(2))
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue($entry));

    	$f->expects($this->once())
    	->method('postGoogle')
    	->will($this->returnValue(true));

    	$f->expects($this->once())
    	->method('playBonus')
    	->will($this->returnValue($entry));

    	$getData = array('googleId' => 'xx-0000-xx');
    	$this->dispatch('/loterie/gameid/google', 'GET', $getData );

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('google');
    	$this->assertMatchedRouteName('frontend/lottery/google');

    	//TODO : check content of json response
    }

    public function testBounceActionNonExistentGame()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue(false));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/essayez-aussi');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('not-found');
    	$this->assertMatchedRouteName('frontend/lottery/bounce');
    	$this->assertResponseStatusCode(404);
    }

    public function testBounceActionClosedGame()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue(false));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/essayez-aussi');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('not-found');
    	$this->assertMatchedRouteName('frontend/lottery/bounce');
    	$this->assertResponseStatusCode(404);
    }

    public function testBounceActionCustomLayout()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager     = $this->getApplicationServiceLocator()->get('ControllerPluginManager');
    	$viewHelperManager = $this->getApplicationServiceLocator()->get('ViewHelperManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');
    	$game->setClassType('lottery');
    	$game->setStylesheet('skin');
    	$game->setLayout('playground-game/layout/2columns-right.phtml');
    	$game->setTitle('title');


    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'getAvailableGames', 'checkExistingEntry', 'getServiceManager'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$f->expects($this->once())
    	->method('getAvailableGames')
    	->will($this->returnValue(array()));

    	$f->expects($this->once())
    	->method('getServiceManager')
    	->will($this->returnValue($serviceManager));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$partnerMock = $this->getMock('PlaygroundPartnership\View\Helper\PartnerSubscriber');
    	$partnerMock->expects($this->any())
    	->method('__invoke')
    	->will($this->returnValue(false));

    	$viewHelperManager->setService('partnerSubscriber', $partnerMock);

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/essayez-aussi');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('bounce');
    	$this->assertMatchedRouteName('frontend/lottery/bounce');
    }

    public function testTermsActionNonExistentGame()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue(false));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/reglement');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('not-found');
    	$this->assertMatchedRouteName('frontend/lottery/terms');
    	$this->assertResponseStatusCode(404);
    }

    public function testTermsActionCustomLayout()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');
    	$game->setClassType('lottery');
    	$game->setStylesheet('skin');
    	$game->setLayout('playground-game/layout/2columns-right.phtml');
    	$game->setTitle('title');


    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'getAvailableGames', 'checkExistingEntry', 'getServiceManager'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/reglement');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('terms');
    	$this->assertMatchedRouteName('frontend/lottery/terms');
    }

    public function testConditionsActionNonExistentGame()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

        $game = new GameEntity();

        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_lottery_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue(false));

        $ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $this->dispatch('/loterie/gameid/mentions-legales');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame_lottery');
        $this->assertControllerClass('LotteryController');
        $this->assertActionName('not-found');
        $this->assertMatchedRouteName('frontend/lottery/conditions');
        $this->assertResponseStatusCode(404);
    }

    public function testConditionsActionRender()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

        $game = new GameEntity();
        $game->setBroadcastPlatform(true);
        $game->setActive(true);
        $game->setIdentifier('gameid');
        $game->setClassType('lottery');
        $game->setStylesheet('skin');
        $game->setConditionsBlock('<h2 id="subtitle-test">Ut placerat lorem sed quam luctus, in egestas diam pretium.</h2><p>Pellentesque pellentesque eros sit amet mi egestas malesuada. Nunc pellentesque libero in felis tristique molestie. Aliquam dolor libero, consectetur at neque quis, egestas tempor nulla. Pellentesque massa ante, imperdiet vulputate mauris id, dignissim dapibus urna.</p>');
        $game->setTitle('title');


        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(array('checkGame', 'checkIsFan', 'getAvailableGames', 'checkExistingEntry', 'getServiceManager'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_lottery_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $this->dispatch('/loterie/gameid/mentions-legales');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame_lottery');
        $this->assertControllerClass('LotteryController');
        $this->assertActionName('conditions');
        $this->assertMatchedRouteName('frontend/lottery/conditions');
        $this->assertQuery('#subtitle-test');
    }

    public function testFangateAction()
    {

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        
        $pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');
        
        $game = new GameEntity();
        
        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
        ->disableOriginalConstructor()
        ->getMock();
        
        $serviceManager->setService('playgroundgame_lottery_service', $f);
        
        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));
        
    	$this->dispatch('/loterie/gameid/fangate');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('fangate');
    	$this->assertMatchedRouteName('frontend/lottery/fangate');
    }

    public function testPrizesActionNonExistentGame()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue(false));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/lots');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('not-found');
    	$this->assertMatchedRouteName('frontend/lottery/prizes');
    	$this->assertResponseStatusCode(404);
    }

    public function testPrizesActionNoPrizes()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/lots');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('not-found');
    	$this->assertMatchedRouteName('frontend/lottery/prizes');
    	$this->assertResponseStatusCode(404);
    }

    public function testPrizesActionPrizes()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');
    	$game->setClassType('lottery');
    	$game->setStylesheet('skin');
    	$game->setLayout('playground-game/layout/2columns-right.phtml');
    	$game->setTitle('title');

    	$prize = new \PlaygroundGame\Entity\Prize();

    	$game->addPrize($prize);

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getEntryMapper', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/lots');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('prizes');
    	$this->assertMatchedRouteName('frontend/lottery/prizes');

    }

    public function testPrizeActionNonExistentGame()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
    	->disableOriginalConstructor()
    	->getMock();

    	$serviceManager->setService('playgroundgame_lottery_service', $f);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue(false));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/lots/prizeid');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('not-found');
    	$this->assertMatchedRouteName('frontend/lottery/prizes/prize');
    	$this->assertResponseStatusCode(404);
    }

    public function testPrizeActionNoPrize()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$p = $this->getMockBuilder('PlaygroundGame\Service\Prize')
    	->setMethods(array('getPrizeMapper'))
    	->disableOriginalConstructor()
    	->getMock();

    	$mapperMock = $this->getMockBuilder('PlaygroundGame\Mapper\Prize')
    	->disableOriginalConstructor()
    	->getMock();

    	$p->expects($this->once())
    	->method('getPrizeMapper')
    	->will($this->returnValue($mapperMock));

    	$mapperMock->expects($this->once())
    	->method('findByIdentifier')
    	->will($this->returnValue(false));

    	$serviceManager->setService('playgroundgame_lottery_service', $f);
    	$serviceManager->setService('playgroundgame_prize_service', $p);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/lots/prize');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('not-found');
    	$this->assertMatchedRouteName('frontend/lottery/prizes/prize');
    	$this->assertResponseStatusCode(404);
    }

    public function testPrizeActionWithPrize()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$serviceManager->setAllowOverride(true);

    	$pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    	$game = new GameEntity();
    	$game->setBroadcastPlatform(true);
    	$game->setActive(true);
    	$game->setIdentifier('gameid');

    	$prize = new \PlaygroundGame\Entity\Prize();

    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager', 'play'))
    	->disableOriginalConstructor()
    	->getMock();

    	$p = $this->getMockBuilder('PlaygroundGame\Service\Prize')
    	->setMethods(array('getPrizeMapper'))
    	->disableOriginalConstructor()
    	->getMock();

    	$mapperMock = $this->getMockBuilder('PlaygroundGame\Mapper\Prize')
    	->disableOriginalConstructor()
    	->getMock();

    	$p->expects($this->once())
    	->method('getPrizeMapper')
    	->will($this->returnValue($mapperMock));

    	$mapperMock->expects($this->once())
    	->method('findByIdentifier')
    	->will($this->returnValue($prize));

    	$serviceManager->setService('playgroundgame_lottery_service', $f);
    	$serviceManager->setService('playgroundgame_prize_service', $p);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkGame')
    	->will($this->returnValue($game));

    	$ZfcUserMock = $this->getMock('PlaygroundUser\Entity\User');

    	$ZfcUserMock->expects($this->any())
    	->method('getId')
    	->will($this->returnValue('1'));

    	$authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    	$authMock->expects($this->any())
    	->method('hasIdentity')
    	-> will($this->returnValue(true));

    	$authMock->expects($this->any())
    	->method('getIdentity')
    	->will($this->returnValue($ZfcUserMock));

    	$pluginManager->setService('zfcUserAuthentication', $authMock);

    	$this->dispatch('/loterie/gameid/lots/prize');

    	$this->assertModuleName('playgroundgame');
    	$this->assertControllerName('playgroundgame_lottery');
    	$this->assertControllerClass('LotteryController');
    	$this->assertActionName('prize');
    	$this->assertMatchedRouteName('frontend/lottery/prizes/prize');
    }
}
