<?php

namespace PlaygroundGameTest\Service;

use PlaygroundGame\Entity\InstantWin as InstantWinEntity;
use PlaygroundGameTest\Bootstrap;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class InstantWinTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;
    protected $sm = null;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../TestConfig.php'
        );

        $this->sm = $this->getApplicationServiceLocator();
        $this->sm->setAllowOverride(true);
        parent::setUp();
    }

    public function testschedule2CodeOccurrences()
    {
        $game = new InstantWinEntity();
        $startDate = new \DateTime("now");
        $game->setOccurrenceType('code');
        $game->setOccurrenceDrawFrequency('game');
        $game->setStartDate($startDate);
        $game->setOccurrenceNumber(2);
        $game->setWinningOccurrenceNumber(1);

        $mapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $this->getServiceManager()->setService('playgroundgame_instantwinoccurrence_mapper', $mapper);

        $mapper->expects($this->exactly(2))
        ->method('insert')
        ->will($this->returnValue(true));

        $gs = $this->getServiceManager()->get('playgroundgame_instantwin_service');
        $result = $gs->scheduleOccurrences($game, array());
        $this->assertTrue($result);
    }

   public function testscheduleOccurrencesToday1Game()
    {

		$game = new InstantWinEntity();

		$startDate = new \DateTime("now");
		$endDate = new \DateTime("now");
		$endDate->add(new \DateInterval('PT1H'));

		$game->setOccurrenceDrawFrequency('game');
		$game->setStartDate($startDate);
		$game->setEndDate($endDate);
		$game->setOccurrenceNumber(2);

		$mapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
		->disableOriginalConstructor()
		->getMock();

		$this->getServiceManager()->setService('playgroundgame_instantwinoccurrence_mapper', $mapper);

		$mapper->expects($this->once())
		->method('findBy')
		->will($this->returnValue(array()));

		$mapper->expects($this->exactly(2))
		->method('insert')
		->will($this->returnValue(true));

		$gs = $this->getServiceManager()->get('playgroundgame_instantwin_service');
	    $result = $gs->scheduleOccurrences($game, array());

		$this->assertTrue($result);

		// 3j 1j game
		// 3j 5j game
		// 3j 1j day
		// 3j 5j day
		// 3j 1j hour
		// 3j 5j hour
    }

    public function testscheduleOccurrencesToday5Game()
    {

        $game = new InstantWinEntity();

        $startDate = new \DateTime("now");
        $endDate = new \DateTime("now");
        $endDate->add(new \DateInterval('P5D'));

        $game->setOccurrenceDrawFrequency('game');
        $game->setStartDate($startDate);
        $game->setEndDate($endDate);
        $game->setOccurrenceNumber(2);

        $mapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $this->getServiceManager()->setService('playgroundgame_instantwinoccurrence_mapper', $mapper);

        $mapper->expects($this->once())
        ->method('findBy')
        ->will($this->returnValue(array()));

        $mapper->expects($this->exactly(2))
        ->method('insert')
        ->will($this->returnValue(true));

        $gs = $this->getServiceManager()->get('playgroundgame_instantwin_service');
        $result = $gs->scheduleOccurrences($game, array());

        $this->assertTrue($result);

    }

    public function testscheduleOccurrencesToday1Day()
    {

        $game = new InstantWinEntity();

        $startDate = new \DateTime("now");
        $endDate = new \DateTime("now");
        $endDate->add(new \DateInterval('PT2H'));

        $game->setOccurrenceDrawFrequency('day');
        $game->setStartDate($startDate);
        $game->setEndDate($endDate);
        $game->setOccurrenceNumber(2);

        $mapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $this->getServiceManager()->setService('playgroundgame_instantwinoccurrence_mapper', $mapper);

        $mapper->expects($this->once())
        ->method('findBy')
        ->will($this->returnValue(array()));

        $mapper->expects($this->exactly(2))
        ->method('insert')
        ->will($this->returnValue(true));

        $gs = $this->getServiceManager()->get('playgroundgame_instantwin_service');
        $result = $gs->scheduleOccurrences($game, array());

        $this->assertTrue($result);
    }

  public function testscheduleOccurrencesToday5Day()
    {

        $game = new InstantWinEntity();

        $startDate = new \DateTime("now");
        $endDate = new \DateTime("now");
        $endDate->add(new \DateInterval('P5D'));

        $game->setOccurrenceDrawFrequency('day');
        $game->setStartDate($startDate);
        $game->setEndDate($endDate);
        $game->setOccurrenceNumber(2);

        $mapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $this->getServiceManager()->setService('playgroundgame_instantwinoccurrence_mapper', $mapper);

        $mapper->expects($this->once())
        ->method('findBy')
        ->will($this->returnValue(array()));

        $mapper->expects($this->exactly(10))
        ->method('insert')
        ->will($this->returnValue(true));

        $gs = $this->getServiceManager()->get('playgroundgame_instantwin_service');
        $result = $gs->scheduleOccurrences($game, array());

        $this->assertTrue($result);

    }

    public function testscheduleOccurrencesToday1Hour()
    {

        $game = new InstantWinEntity();

        $startDate = new \DateTime("now");
        $startDate = \DateTime::createFromFormat('m/d/Y H:i:s', $startDate->format('m/d/Y H'). ':00:00');

        $endDate = new \DateTime("now");
        $endDate->add(new \DateInterval('PT1H'));
        $endDate = \DateTime::createFromFormat('m/d/Y H:i:s', $endDate->format('m/d/Y H'). ':00:00');

        $game->setOccurrenceDrawFrequency('hour');
        $game->setStartDate($startDate);
        $game->setEndDate($endDate);
        $game->setOccurrenceNumber(2);

        $mapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $this->getServiceManager()->setService('playgroundgame_instantwinoccurrence_mapper', $mapper);

        $mapper->expects($this->once())
        ->method('findBy')
        ->will($this->returnValue(array()));

        $mapper->expects($this->exactly(2))
        ->method('insert')
        ->will($this->returnValue(true));

        $gs = $this->getServiceManager()->get('playgroundgame_instantwin_service');
        $result = $gs->scheduleOccurrences($game, array());

        $this->assertTrue($result);
    }

    public function testscheduleOccurrencesToday48Hours()
    {

        $game = new InstantWinEntity();

        $startDate = new \DateTime("now");

        $endDate = new \DateTime("now");
        $endDate->add(new \DateInterval('PT36H'));

        $game->setOccurrenceDrawFrequency('hour');
        $game->setStartDate($startDate);
        $game->setEndDate($endDate);
        $game->setOccurrenceNumber(1);

        $mapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $this->getServiceManager()->setService('playgroundgame_instantwinoccurrence_mapper', $mapper);

        $mapper->expects($this->once())
        ->method('findBy')
        ->will($this->returnValue(array()));

        $mapper->expects($this->exactly(36))
        ->method('insert')
        ->will($this->returnValue(true));

        $gs = $this->getServiceManager()->get('playgroundgame_instantwin_service');
        $result = $gs->scheduleOccurrences($game, array());

        $this->assertTrue($result);

    }

    public function testscheduleOccurrences3Days1Game()
    {

        $game = new InstantWinEntity();

        $startDate = new \DateTime("now");
        $startDate->add(new \DateInterval('P3D'));
        $endDate = new \DateTime("now");
        $endDate->add(new \DateInterval('P3DT1H'));

        $game->setOccurrenceDrawFrequency('game');
        $game->setStartDate($startDate);
        $game->setEndDate($endDate);
        $game->setOccurrenceNumber(2);

        $mapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $this->getServiceManager()->setService('playgroundgame_instantwinoccurrence_mapper', $mapper);

        $mapper->expects($this->once())
        ->method('findBy')
        ->will($this->returnValue(array()));

        $mapper->expects($this->exactly(2))
        ->method('insert')
        ->will($this->returnValue(true));

        $gs = $this->getServiceManager()->get('playgroundgame_instantwin_service');
        $result = $gs->scheduleOccurrences($game, array());

        $this->assertTrue($result);

        // 3j 1j game
        // 3j 5j game
        // 3j 1j day
        // 3j 5j day
        // 3j 1j hour
        // 3j 5j hour
    }

    public function testscheduleOccurrences3Days5Game()
    {

        $game = new InstantWinEntity();

        $startDate = new \DateTime("now");
        $startDate->add(new \DateInterval('P3D'));
        $endDate = new \DateTime("now");
        $endDate->add(new \DateInterval('P8D'));

        $game->setOccurrenceDrawFrequency('game');
        $game->setStartDate($startDate);
        $game->setEndDate($endDate);
        $game->setOccurrenceNumber(2);

        $mapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $this->getServiceManager()->setService('playgroundgame_instantwinoccurrence_mapper', $mapper);

        $mapper->expects($this->once())
        ->method('findBy')
        ->will($this->returnValue(array()));

        $mapper->expects($this->exactly(2))
        ->method('insert')
        ->will($this->returnValue(true));

        $gs = $this->getServiceManager()->get('playgroundgame_instantwin_service');
        $result = $gs->scheduleOccurrences($game, array());

        $this->assertTrue($result);

    }

    public function testscheduleOccurrences3Days1Day()
    {

        $game = new InstantWinEntity();

        $startDate = new \DateTime("now");
        $startDate->add(new \DateInterval('P3D'));
        $endDate = new \DateTime("now");
        $endDate->add(new \DateInterval('P3DT2H'));

        $game->setOccurrenceDrawFrequency('day');
        $game->setStartDate($startDate);
        $game->setEndDate($endDate);
        $game->setOccurrenceNumber(2);

        $mapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $this->getServiceManager()->setService('playgroundgame_instantwinoccurrence_mapper', $mapper);

        $mapper->expects($this->once())
        ->method('findBy')
        ->will($this->returnValue(array()));

        $mapper->expects($this->exactly(2))
        ->method('insert')
        ->will($this->returnValue(true));

        $gs = $this->getServiceManager()->get('playgroundgame_instantwin_service');
        $result = $gs->scheduleOccurrences($game, array());

        $this->assertTrue($result);
    }

    public function testscheduleOccurrences3Days5Day()
    {
        $game = new InstantWinEntity();

        $startDate = new \DateTime("now");
        $startDate->add(new \DateInterval('P3D'));
        $endDate = new \DateTime("now");
        $endDate->add(new \DateInterval('P8D'));


        $game->setOccurrenceDrawFrequency('day');
        $game->setStartDate($startDate);
        $game->setEndDate($endDate);
        $game->setOccurrenceNumber(2);

        $mapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $this->getServiceManager()->setService('playgroundgame_instantwinoccurrence_mapper', $mapper);

        $mapper->expects($this->once())
        ->method('findBy')
        ->will($this->returnValue(array()));

        $mapper->expects($this->exactly(10))
        ->method('insert')
        ->will($this->returnValue(true));

        $gs = $this->getServiceManager()->get('playgroundgame_instantwin_service');
        $result = $gs->scheduleOccurrences($game, array());

        $this->assertTrue($result);

    }

    /**
    * Gestion des instants gagnants avec les changements d'horaires
    */
    public function testscheduleOccurrencesDaysInTransitions()
    {
        $game = new InstantWinEntity();
        $startDate = new \DateTime((date('Y')+1)."-10-21 00:00:00");
        $startDate->add(new \DateInterval('P3D'));
        $endDate = new \DateTime((date('Y')+1)."-10-21 00:00:00");
        $endDate->add(new \DateInterval('P8D'));


        $game->setOccurrenceDrawFrequency('day');
        $game->setStartDate($startDate);
        $game->setEndDate($endDate);
        $game->setOccurrenceNumber(2);

        $mapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $this->getServiceManager()->setService('playgroundgame_instantwinoccurrence_mapper', $mapper);

        $mapper->expects($this->once())
        ->method('findBy')
        ->will($this->returnValue(array()));

        $mapper->expects($this->exactly(10))
        ->method('insert')
        ->will($this->returnValue(true));

        $gs = $this->getServiceManager()->get('playgroundgame_instantwin_service');
        $result = $gs->scheduleOccurrences($game, array());

        $this->assertTrue($result);

    }

    public function testscheduleOccurrences3Days1Hour()
    {

        $game = new InstantWinEntity();

        $startDate = new \DateTime("now");
        $startDate->add(new \DateInterval('P3D'));
        $startDate = \DateTime::createFromFormat('m/d/Y H:i:s', $startDate->format('m/d/Y H'). ':00:00');

        $endDate = new \DateTime("now");
        $endDate->add(new \DateInterval('P3DT1H'));
        $endDate = \DateTime::createFromFormat('m/d/Y H:i:s', $endDate->format('m/d/Y H'). ':00:00');

        $game->setOccurrenceDrawFrequency('hour');
        $game->setStartDate($startDate);
        $game->setEndDate($endDate);
        $game->setOccurrenceNumber(2);

        $mapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $this->getServiceManager()->setService('playgroundgame_instantwinoccurrence_mapper', $mapper);

        $mapper->expects($this->once())
        ->method('findBy')
        ->will($this->returnValue(array()));

        $mapper->expects($this->exactly(2))
        ->method('insert')
        ->will($this->returnValue(true));

        $gs = $this->getServiceManager()->get('playgroundgame_instantwin_service');
        $result = $gs->scheduleOccurrences($game, array());

        $this->assertTrue($result);
    }

    public function testscheduleOccurrences3Days48Hours()
    {

        $game = new InstantWinEntity();

        $startDate = new \DateTime("now");
        $startDate->add(new \DateInterval('P3D'));

        $endDate = new \DateTime("now");
        $endDate->add(new \DateInterval('P3DT36H'));

        $game->setOccurrenceDrawFrequency('hour');
        $game->setStartDate($startDate);
        $game->setEndDate($endDate);
        $game->setOccurrenceNumber(1);

        $mapper = $this->getMockBuilder('PlaygroundGame\Mapper\InstantWinOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $this->getServiceManager()->setService('playgroundgame_instantwinoccurrence_mapper', $mapper);

        $mapper->expects($this->once())
        ->method('findBy')
        ->will($this->returnValue(array()));

        $mapper->expects($this->exactly(36))
        ->method('insert')
        ->will($this->returnValue(true));

        $gs = $this->getServiceManager()->get('playgroundgame_instantwin_service');
        $result = $gs->scheduleOccurrences($game, array());

        $this->assertTrue($result);

    }

    public function getServiceManager()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        return $this->sm;
    }

    public function tearDown()
    {
        $this->sm = null;
        unset($this->sm);

        parent::tearDown();
    }

}