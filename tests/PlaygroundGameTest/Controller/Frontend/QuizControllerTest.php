<?php

namespace PlaygroundGameTest\Controller\Frontend;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use \PlaygroundGame\Entity\Quiz as GameEntity;

class QuizControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../TestConfig.php'
        );

        parent::setUp();
    }

    public function testHomeActionFacebookSafariRedirect()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
    
        $pluginManager = $this->getApplicationServiceLocator()->get('ControllerPluginManager');
   
        $game = new GameEntity();
        $game->setIdentifier('gameid');
        $game->setClassType('quiz');
        $game->setVictoryConditions(0);
        $game->setQuestions(array());
        $game->setFbAppId('fbid');
    
        //mocking the method checkExistingEntry
        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
        ->disableOriginalConstructor()
        ->getMock();
    
        $serviceManager->setService('playgroundgame_quiz_service', $f);
    
        $ZfcUserMock = $this->createMock('ZfcUser\Entity\User');
    
        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));
    
        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');
    
        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));
    
        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));
    
        $pluginManager->setService('zfcUserAuthentication', $authMock);
    
        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue($game));
    
        $this->dispatch('/quiz/gameid?redir_fb_page_id=httpredir');
    
        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('home');
        $this->assertMatchedRouteName('frontend/quiz');
        $this->assertRedirectTo('//www.facebook.com/pages/game/httpredir?sk=app_fbid');
    }
    
    public function testIndexActionNonExistentGame()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

        $game = new GameEntity();

        //mocking the method checkExistingEntry
        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(array('checkGame', 'checkIsFan'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue(false));

        $ZfcUserMock = $this->createMock('ZfcUser\Entity\User');

        $ZfcUserMock->expects($this->any())
         ->method('getId')
         ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
         ->method('hasIdentity')
         -> will($this->returnValue(true));

        $authMock->expects($this->any())
         ->method('getIdentity')
         ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $this->dispatch('/quiz/fake');
        $this->assertResponseStatusCode(404);
    }

    // public function testIndexActionNotFanOnFacebook()
    // {
    //     $serviceManager = $this->getApplicationServiceLocator();
    //     $serviceManager->setAllowOverride(true);

    //     $pluginManager = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

    //     $game = new GameEntity();
    //     $game->setIdentifier('gameid');
    //     $game->setClassType('quiz');

    //     //mocking the method checkExistingEntry
    //     $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    //     ->setMethods(array('checkGame', 'checkIsFan'))
    //     //->disableOriginalConstructor()
    //     ->getMock();

    //     $serviceManager->setService('playgroundgame_quiz_service', $f);

    //     $ZfcUserMock = $this->createMock('ZfcUser\Entity\User');

    //     $ZfcUserMock->expects($this->any())
    //     ->method('getId')
    //     ->will($this->returnValue('1'));

    //     $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    //     $authMock->expects($this->any())
    //     ->method('hasIdentity')
    //     -> will($this->returnValue(true));

    //     $authMock->expects($this->any())
    //     ->method('getIdentity')
    //     ->will($this->returnValue($ZfcUserMock));

    //     $pluginManager->setService('zfcUserAuthentication', $authMock);

    //     // I check that the array in findOneBy contains the parameter 'active' = 1
    //     $f->expects($this->exactly(2))
    //     ->method('checkGame')
    //     ->will($this->returnValue($game));

    //     // I check that the array in findOneBy contains the parameter 'active' = 1
    //     $f->expects($this->once())
    //     ->method('checkIsFan')
    //     ->will($this->returnValue(false));

    //     $serviceManager = $this->getApplicationServiceLocator();
    //     $serviceManager->setAllowOverride(true);
    //     $serviceManager->setService('playgroundgame_quiz_service', $f);

    //     $this->dispatch('/facebook/quiz/gameid');


    //     $this->assertModuleName('playgroundgame');
    //     $this->assertControllerName('playgroundgame\controller\frontend\quiz');
    //     $this->assertControllerClass('QuizController');
    //     $this->assertActionName('home');
    //     $this->assertMatchedRouteName('frontend/quiz');

    //     $this->assertRedirectTo('/facebook/quiz/gameid/fangate');
    // }

    public function testIndexActionNoEntry()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $pluginManager = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

        $game = new GameEntity();
        $game->setIdentifier('gameid');
        $game->setClassType('quiz');
        $game->setVictoryConditions(0);
        $game->setQuestions(array());

        //mocking the method checkExistingEntry
        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(array('checkGame', 'checkExistingEntry', 'getServiceManager'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        $ZfcUserMock = $this->createMock('ZfcUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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

        $f->expects($this->once())
        ->method('checkExistingEntry')
        ->will($this->returnValue(false));

        $f->expects($this->any())
        ->method('getServiceManager')
        ->will($this->returnValue($serviceManager));

        $this->dispatch('/quiz/gameid');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('home');
        $this->assertMatchedRouteName('frontend/quiz');
    }

    public function testIndexActionCustomizedLayoutWithEntry()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $pluginManager = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

        $game = new GameEntity();
        $game->setIdentifier('gameid');
        $game->setClassType('quiz');
        $game->setStylesheet('skin');
        $game->setLayout('playground-game/layout/2columns-right.phtml');
        $game->setVictoryConditions(0);
        $game->setQuestions(array());

        $entry = new \PlaygroundGame\Entity\Entry();

        //mocking the method checkExistingEntry
        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(array('checkGame', 'checkExistingEntry', 'getServiceManager'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        $ZfcUserMock = $this->createMock('ZfcUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $f->expects($this->exactly(2))
        ->method('checkGame')
        ->will($this->returnValue($game));

        $f->expects($this->once())
        ->method('checkExistingEntry')
        ->will($this->returnValue($entry));

        $f->expects($this->any())
        ->method('getServiceManager')
        ->will($this->returnValue($serviceManager));

        $this->dispatch('/quiz/gameid');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('home');
        $this->assertMatchedRouteName('frontend/quiz');
    }

    public function testResultActionNonExistentGame()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

        $game = new GameEntity();

        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
         $f->expects($this->exactly(1))
         ->method('checkGame')
         ->will($this->returnValue(false));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $this->dispatch('/quiz/gameid/resultat');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('not-found');
        $this->assertMatchedRouteName('frontend/quiz/result');
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
        ->setMethods(array('checkGame', 'checkIsFan', 'getEntryMapper', 'getServiceManager', 'findLastEntry'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));
        
        $f->expects($this->once())
        ->method('findLastEntry')
        ->will($this->returnValue(false));

        $ZfcUserMock = $this->createMock('ZfcUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $bitlyMock = $this->getMockBuilder('PlaygroundCore\Controller\Plugin\ShortenUrl')
        ->setMethods(array('ShortenUrl'))
        ->disableOriginalConstructor()
        ->getMock();

        $bitlyMock->expects($this->any())
        ->method('shortenUrl')
        ->will($this->returnValue('http://shorturl.com/shurl'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);
        $pluginManager->setService('shortenUrl', $bitlyMock);

        $this->dispatch('/quiz/gameid/resultat');

        $this->assertModuleName('playgroundgame');
        $this->assertActionName('result');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertMatchedRouteName('frontend/quiz/result');

        $this->assertRedirectTo('/quiz/gameid');
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

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
         $f->expects($this->exactly(1))
         ->method('checkGame')
         ->will($this->returnValue(false));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $this->dispatch('/quiz/gameid/jouer');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('not-found');
        $this->assertMatchedRouteName('frontend/quiz/play');
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

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue(false));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $this->dispatch('/quiz/gameid/jouer');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('not-found');
        $this->assertMatchedRouteName('frontend/quiz/play');
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

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue(false));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $this->dispatch('/quiz/gameid/jouer');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('play');
        $this->assertMatchedRouteName('frontend/quiz/play');

        $this->assertRedirectTo('/mon-compte/inscription?redirect=%2Fquiz%2Fgameid%2Fjouer');
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
        $game->setVictoryConditions(0);
        $game->setQuestions(array());

        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager', 'play'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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

        $this->dispatch('/quiz/gameid/jouer');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('play');
        $this->assertMatchedRouteName('frontend/quiz/play');

        $this->assertRedirectTo('/quiz/gameid?Error=1');
    }

//     public function testPlayActionEntry()
//     {
//      $serviceManager = $this->getApplicationServiceLocator();
//      $serviceManager->setAllowOverride(true);

//      $pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

//      $game = new GameEntity();
//      $game->setBroadcastPlatform(true);
//      $game->setActive(true);
//      $game->setIdentifier('gameid');
//      $game->setVictoryConditions(0);
//      $game->setQuestions(array());

//      $entry = new \PlaygroundGame\Entity\Entry();

//      $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
//      ->setMethods(
    //     array(
    //         'checkGame',
    //         'checkIsFan',
    //         'checkExistingEntry',
    //         'getEntryMapper',
    //         'getServiceManager',
    //         'play'
    //     )
    // )
//      ->disableOriginalConstructor()
//      ->getMock();

//      $serviceManager->setService('playgroundgame_quiz_service', $f);

//      // I check that the array in findOneBy contains the parameter 'active' = 1
//      $f->expects($this->once())
//      ->method('checkGame')
//      ->will($this->returnValue($game));

//      $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

//      $ZfcUserMock->expects($this->any())
//      ->method('getId')
//      ->will($this->returnValue('1'));

//      $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

//      $authMock->expects($this->any())
//      ->method('hasIdentity')
//      -> will($this->returnValue(true));

//      $authMock->expects($this->any())
//      ->method('getIdentity')
//      ->will($this->returnValue($ZfcUserMock));

//      $pluginManager->setService('zfcUserAuthentication', $authMock);

//      $f->expects($this->once())
//      ->method('play')
//      ->will($this->returnValue($entry));

//      $entryMock = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
//      ->disableOriginalConstructor()
//      ->getMock();

//      $f->expects($this->once())
//      ->method('getEntryMapper')
//      ->will($this->returnValue($entryMock));

//      $entryMock->expects($this->once())
//      ->method('update')
//      ->will($this->returnValue($entry));

//      $this->dispatch('/quiz/gameid/jouer');

//      $this->assertEquals(true, $entry->getWinner());
//      $this->assertEquals(false, $entry->getActive());
//      $this->assertModuleName('playgroundgame');
//      $this->assertControllerName('playgroundgame\controller\frontend\quiz');
//      $this->assertControllerClass('QuizController');
//      $this->assertActionName('play');
//      $this->assertMatchedRouteName('frontend/quiz/play');

//      $this->assertRedirectTo('/quiz/gameid/resultat');
//     }

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
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'postFbWall',
                'checkExistingEntry',
                'getEntryMapper',
                'getServiceManager',
                'play'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue(false));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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

        $this->dispatch('/quiz/gameid/fbshare');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('fbshare');
        $this->assertMatchedRouteName('frontend/quiz/fbshare');

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
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'postFbWall',
                'checkExistingEntry',
                'getEntryMapper',
                'getServiceManager',
                'play'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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

        $this->dispatch('/quiz/gameid/fbshare');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('fbshare');
        $this->assertMatchedRouteName('frontend/quiz/fbshare');

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
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'postFbWall',
                'checkExistingEntry',
                'getEntryMapper',
                'getServiceManager',
                'play'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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

        $this->dispatch('/quiz/gameid/fbshare');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('fbshare');
        $this->assertMatchedRouteName('frontend/quiz/fbshare');

        //TODO : check content of json response
    }

    public function testFbshareActionPostFbWallNoEntry()
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
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'addAnotherChance',
                'checkExistingEntry',
                'postFbWall',
                'getEntryMapper',
                'getServiceManager',
                'play',
                'findLastInactiveEntry'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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
        ->method('findLastInactiveEntry')
        ->will($this->returnValue(false));

        $getData = array('fbId' => 'xx-0000-xx');
        $this->dispatch('/quiz/gameid/fbshare', 'GET', $getData);

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('fbshare');
        $this->assertMatchedRouteName('frontend/quiz/fbshare');

        //TODO : check content of json response
    }

    public function testFbshareActionPostFbWallEntry()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

        $game = new GameEntity();
        $game->setBroadcastPlatform(true);
        $game->setActive(true);
        $game->setIdentifier('gameid');

        $entry = new \PlaygroundGame\Entity\Entry();
        $entry->setWinner(true);

        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'addAnotherChance',
                'checkExistingEntry',
                'postFbWall',
                'getEntryMapper',
                'getServiceManager',
                'play',
                'findLastInactiveEntry'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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
        ->method('addAnotherChance')
        ->will($this->returnValue($entry));
        
        $f->expects($this->once())
        ->method('findLastInactiveEntry')
        ->will($this->returnValue($entry));

        $getData = array('fbId' => 'xx-0000-xx');
        $this->dispatch('/quiz/gameid/fbshare', 'GET', $getData);

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('fbshare');
        $this->assertMatchedRouteName('frontend/quiz/fbshare');

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
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'postTwitter',
                'checkExistingEntry',
                'getEntryMapper',
                'getServiceManager',
                'play'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue(false));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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

        $this->dispatch('/quiz/gameid/tweet');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('tweet');
        $this->assertMatchedRouteName('frontend/quiz/tweet');

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
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'postTwitter',
                'checkExistingEntry',
                'getEntryMapper',
                'getServiceManager',
                'play'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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

        $this->dispatch('/quiz/gameid/tweet');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('tweet');
        $this->assertMatchedRouteName('frontend/quiz/tweet');

        //TODO : check content of json response
    }

    public function testTweetActionNoTweetid()
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
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'postTwitter',
                'checkExistingEntry',
                'getEntryMapper',
                'getServiceManager',
                'play'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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

        $this->dispatch('/quiz/gameid/tweet');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('tweet');
        $this->assertMatchedRouteName('frontend/quiz/tweet');

        //TODO : check content of json response
    }

    public function testTweetActionPostTweetNoEntry()
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
         ->setMethods(
             array(
                'checkGame',
                'checkIsFan',
                'addAnotherChance',
                'checkExistingEntry',
                'postTwitter',
                'getEntryMapper',
                'getServiceManager',
                'play',
                'findLastInactiveEntry'
             )
         )
         ->disableOriginalConstructor()
         ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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
        ->method('findLastInactiveEntry')
        ->will($this->returnValue(false));

        $getData = array('tweetId' => 'xx-0000-xx');
        $this->dispatch('/quiz/gameid/tweet', 'GET', $getData);

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('tweet');
        $this->assertMatchedRouteName('frontend/quiz/tweet');

        //TODO : check content of json response
    }

    public function testTweetActionPostTweetEntry()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

        $game = new GameEntity();
        $game->setBroadcastPlatform(true);
        $game->setActive(true);
        $game->setIdentifier('gameid');

        $entry = new \PlaygroundGame\Entity\Entry();
        $entry->setWinner(true);

        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'addAnotherChance',
                'checkExistingEntry',
                'postTwitter',
                'getEntryMapper',
                'getServiceManager',
                'play',
                'findLastInactiveEntry'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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
           ->method('addAnotherChance')
           ->will($this->returnValue($entry));
          
        $f->expects($this->once())
          ->method('findLastInactiveEntry')
          ->will($this->returnValue($entry));

        $getData = array('tweetId' => 'xx-0000-xx');
        $this->dispatch('/quiz/gameid/tweet', 'GET', $getData);

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('tweet');
        $this->assertMatchedRouteName('frontend/quiz/tweet');

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
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'postGoogle',
                'checkExistingEntry',
                'getEntryMapper',
                'getServiceManager',
                'play'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue(false));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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

        $this->dispatch('/quiz/gameid/google');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('google');
        $this->assertMatchedRouteName('frontend/quiz/google');

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
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'postGoogle',
                'checkExistingEntry',
                'getEntryMapper',
                'getServiceManager',
                'play'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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

        $this->dispatch('/quiz/gameid/google');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('google');
        $this->assertMatchedRouteName('frontend/quiz/google');

        //TODO : check content of json response
    }

    public function testGoogleActionNoGoogleid()
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
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'postGoogle',
                'checkExistingEntry',
                'getEntryMapper',
                'getServiceManager',
                'play'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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

        $this->dispatch('/quiz/gameid/google');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('google');
        $this->assertMatchedRouteName('frontend/quiz/google');

        //TODO : check content of json response
    }

    public function testGoogleActionPostGoogleNoEntry()
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
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'addAnotherChance',
                'findLastInactiveEntry',
                'checkExistingEntry',
                'postGoogle',
                'getEntryMapper',
                'getServiceManager',
                'play'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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
        ->method('findLastInactiveEntry')
        ->will($this->returnValue(false));

        $getData = array('googleId' => 'xx-0000-xx');
        $this->dispatch('/quiz/gameid/google', 'GET', $getData);

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('google');
        $this->assertMatchedRouteName('frontend/quiz/google');

        //TODO : check content of json response
    }

    public function testGoogleActionPostGoogleEntry()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');

        $game = new GameEntity();
        $game->setBroadcastPlatform(true);
        $game->setActive(true);
        $game->setIdentifier('gameid');

        $entry = new \PlaygroundGame\Entity\Entry();
        $entry->setWinner(true);

        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'addAnotherChance',
                'findLastInactiveEntry',
                'checkExistingEntry',
                'postGoogle',
                'getEntryMapper',
                'getServiceManager',
                'play'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue($game));

        $f->expects($this->once())
          ->method('addAnotherChance')
           ->will($this->returnValue($entry));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

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
        ->method('findLastInactiveEntry')
        ->will($this->returnValue($entry));

        $f->expects($this->once())
        ->method('postGoogle')
        ->will($this->returnValue(true));

        $getData = array('googleId' => 'xx-0000-xx');
        $this->dispatch('/quiz/gameid/google', 'GET', $getData);

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('google');
        $this->assertMatchedRouteName('frontend/quiz/google');

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

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue(false));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $this->dispatch('/quiz/gameid/essayez-aussi');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('not-found');
        $this->assertMatchedRouteName('frontend/quiz/bounce');
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

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue(false));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $this->dispatch('/quiz/gameid/essayez-aussi');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('not-found');
        $this->assertMatchedRouteName('frontend/quiz/bounce');
        $this->assertResponseStatusCode(404);
    }

    public function testBounceActionCustomLayout()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');
        $viewHelperManager = $this->getApplicationServiceLocator()->get('ViewHelperManager');

        $game = new GameEntity();
        $game->setBroadcastPlatform(true);
        $game->setActive(true);
        $game->setIdentifier('gameid');
        $game->setClassType('quiz');
        $game->setStylesheet('skin');
        $game->setLayout('playground-game/layout/2columns-right.phtml');
        $game->setTitle('title');


        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(array('checkGame', 'checkIsFan', 'getAvailableGames', 'checkExistingEntry', 'getServiceManager'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));

        $f->expects($this->once())
        ->method('getAvailableGames')
        ->will($this->returnValue(array()));

        $f->expects($this->any())
        ->method('getServiceManager')
        ->will($this->returnValue($serviceManager));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $partnerMock = $this->createMock('PlaygroundPartnership\View\Helper\PartnerSubscriber');
        $partnerMock->expects($this->any())
        ->method('__invoke')
        ->will($this->returnValue(false));

        $viewHelperManager->setService('partnerSubscriber', $partnerMock);

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $this->dispatch('/quiz/gameid/essayez-aussi');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('bounce');
        $this->assertMatchedRouteName('frontend/quiz/bounce');
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

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue(false));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $this->dispatch('/quiz/gameid/reglement');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('not-found');
        $this->assertMatchedRouteName('frontend/quiz/terms');
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
        $game->setClassType('quiz');
        $game->setStylesheet('skin');
        $game->setLayout('playground-game/layout/2columns-right.phtml');
        $game->setTitle('title');


        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(array('checkGame', 'checkIsFan', 'getAvailableGames', 'checkExistingEntry', 'getServiceManager'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $f->expects($this->any())
        ->method('getServiceManager')
        ->will($this->returnValue($serviceManager));

        $this->dispatch('/quiz/gameid/reglement');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('terms');
        $this->assertMatchedRouteName('frontend/quiz/terms');
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

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue(false));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $this->dispatch('/quiz/gameid/mentions-legales');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('not-found');
        $this->assertMatchedRouteName('frontend/quiz/conditions');
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
        $game->setClassType('quiz');
        $game->setStylesheet('skin');
        $game->setConditionsBlock('<h2 id="subtitle-test">condition</h2>block</p>');
        $game->setTitle('title');

        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $f->expects($this->any())
        ->method('getServiceManager')
        ->will($this->returnValue($serviceManager));

        $this->dispatch('/quiz/gameid/mentions-legales');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('conditions');
        $this->assertMatchedRouteName('frontend/quiz/conditions');
        $this->assertQuery('#subtitle-test');
    }

    // DEPRECATED
    // public function testFangateAction()
    // {
    //     $serviceManager = $this->getApplicationServiceLocator();
    //     $serviceManager->setAllowOverride(true);

    //     $pluginManager    = $this->getApplicationServiceLocator()->get('ControllerPluginManager');
        
    //     $game = new GameEntity();
        
    //     $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    //     ->setMethods(array('checkGame', 'checkIsFan', 'checkExistingEntry', 'getServiceManager'))
    //     ->disableOriginalConstructor()
    //     ->getMock();
        
    //     $serviceManager->setService('playgroundgame_quiz_service', $f);

    //     $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

    //     $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

    //     $authMock->expects($this->any())
    //     ->method('getIdentity')
    //     ->will($this->returnValue(false));

    //     $pluginManager->setService('zfcUserAuthentication', $authMock);
        
    //     // I check that the array in findOneBy contains the parameter 'active' = 1
    //     $f->expects($this->once())
    //     ->method('checkGame')
    //     ->will($this->returnValue($game));

    //     $f->expects($this->any())
    //     ->method('getServiceManager')
    //     ->will($this->returnValue($serviceManager));
        
    //     $this->dispatch('/quiz/gameid/fangate');

    //     $this->assertModuleName('playgroundgame');
    //     $this->assertControllerName('playgroundgame\controller\frontend\quiz');
    //     $this->assertControllerClass('QuizController');
    //     $this->assertActionName('fangate');
    //     $this->assertMatchedRouteName('frontend/quiz/fangate');
    // }

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

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue(false));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $this->dispatch('/quiz/gameid/lots');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('not-found');
        $this->assertMatchedRouteName('frontend/quiz/prizes');
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

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $f->expects($this->any())
        ->method('getServiceManager')
        ->will($this->returnValue($serviceManager));

        $this->dispatch('/quiz/gameid/lots');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('not-found');
        $this->assertMatchedRouteName('frontend/quiz/prizes');
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
        $game->setClassType('quiz');
        $game->setStylesheet('skin');
        $game->setLayout('playground-game/layout/2columns-right.phtml');
        $game->setTitle('title');

        $prize = new \PlaygroundGame\Entity\Prize();

        $game->addPrize($prize);

        $f = $this->getMockBuilder('PlaygroundGame\Service\Game')
        ->setMethods(
            array(
                'checkGame',
                'checkIsFan',
                'checkExistingEntry',
                'getEntryMapper',
                'getServiceManager',
                'play'
            )
        )
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $f->expects($this->any())
        ->method('getServiceManager')
        ->will($this->returnValue($serviceManager));

        $this->dispatch('/quiz/gameid/lots');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('prizes');
        $this->assertMatchedRouteName('frontend/quiz/prizes');
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

        $serviceManager->setService('playgroundgame_quiz_service', $f);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue(false));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $this->dispatch('/quiz/gameid/lots/prizeid');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('not-found');
        $this->assertMatchedRouteName('frontend/quiz/prizes/prize');
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

        $serviceManager->setService('playgroundgame_quiz_service', $f);
        $serviceManager->setService('playgroundgame_prize_service', $p);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->exactly(1))
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $f->expects($this->any())
        ->method('getServiceManager')
        ->will($this->returnValue($serviceManager));

        $this->dispatch('/quiz/gameid/lots/prize');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('not-found');
        $this->assertMatchedRouteName('frontend/quiz/prizes/prize');
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

        $serviceManager->setService('playgroundgame_quiz_service', $f);
        $serviceManager->setService('playgroundgame_prize_service', $p);

        // I check that the array in findOneBy contains the parameter 'active' = 1
        $f->expects($this->once())
        ->method('checkGame')
        ->will($this->returnValue($game));

        $ZfcUserMock = $this->createMock('PlaygroundUser\Entity\User');

        $ZfcUserMock->expects($this->any())
        ->method('getId')
        ->will($this->returnValue('1'));

        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
        ->method('hasIdentity')
        -> will($this->returnValue(true));

        $authMock->expects($this->any())
        ->method('getIdentity')
        ->will($this->returnValue($ZfcUserMock));

        $pluginManager->setService('zfcUserAuthentication', $authMock);

        $f->expects($this->any())
        ->method('getServiceManager')
        ->will($this->returnValue($serviceManager));

        $this->dispatch('/quiz/gameid/lots/prize');

        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame\controller\frontend\quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('prize');
        $this->assertMatchedRouteName('frontend/quiz/prizes/prize');
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
