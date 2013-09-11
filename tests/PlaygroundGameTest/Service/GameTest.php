<?php

namespace PlaygroundGameTest\Service;

use \PlaygroundGame\Entity\Game as GameEntity;

class GameTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        parent::setUp();
    }

    public function testAllowBonusNull()
    {
		$game = new GameEntity();
		$gs = new \PlaygroundGame\Service\Game();

		$this->assertFalse($gs->allowBonus($game, null));
    }

    public function testAllowBonusNonExistentValue()
    {
    	$game = new GameEntity();
    	$game->setPlayBonus('non_existent_value');

    	$gs = new \PlaygroundGame\Service\Game();

    	$this->assertFalse($gs->allowBonus($game, null));
    }

    public function testAllowBonusOnePlayed()
    {

        $mapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
        ->disableOriginalConstructor()
        ->getMock();

    	$game = new GameEntity();
    	$game->setPlayBonus('one');

    	$gs = new \PlaygroundGame\Service\Game();
    	$gs->setEntryMapper($mapper);

    	$gs->getEntryMapper()
    	->expects($this->once())
    	->method('findOneBy')
    	->will($this->returnValue(true));

    	$this->assertFalse($gs->allowBonus($game, null));
    }


    public function testAllowBonusOneNotPlayed()
    {
    	$mapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
        ->disableOriginalConstructor()
        ->getMock();

    	$game = new GameEntity();
    	$game->setPlayBonus('one');

    	$gs = new \PlaygroundGame\Service\Game();
    	$gs->setEntryMapper($mapper);

    	$gs->getEntryMapper()
    	->expects($this->once())
    	->method('findOneBy')
    	->will($this->returnValue(false));

    	$this->assertTrue($gs->allowBonus($game, null));
    }

    public function testAllowBonusPerEntryPlayed()
    {
    	$mapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
        ->disableOriginalConstructor()
        ->getMock();

    	$game = new GameEntity();
    	$game->setPlayBonus('per_entry');

    	$gs = new \PlaygroundGame\Service\Game();
    	$gs->setEntryMapper($mapper);

    	$gs->getEntryMapper()
    	->expects($this->once())
    	->method('checkBonusEntry')
    	->will($this->returnValue(false));

    	$this->assertFalse($gs->allowBonus($game, null));
    }

    public function testAllowBonusPerEntryNotPlayed()
    {
    	$mapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
        ->disableOriginalConstructor()
        ->getMock();

    	$game = new GameEntity();
    	$game->setPlayBonus('per_entry');

    	$gs = new \PlaygroundGame\Service\Game();
    	$gs->setEntryMapper($mapper);

    	$gs->getEntryMapper()
    	->expects($this->once())
    	->method('checkBonusEntry')
    	->will($this->returnValue(true));

    	$this->assertTrue($gs->allowBonus($game, null));
    }

    public function testPlayBonusAllowed()
    {
    	$mapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
    	->disableOriginalConstructor()
    	->getMock();

    	$game = new GameEntity();
    	$game->setPlayBonus('one');

    	$gs = new \PlaygroundGame\Service\Game();
    	$gs->setEntryMapper($mapper);

    	// return false : The user has not played yet the bonus game
    	$gs->getEntryMapper()
    	->expects($this->once())
    	->method('findOneBy')
    	->will($this->returnValue(false));

    	$gs->getEntryMapper()
    	->expects($this->once())
    	->method('insert')
    	->with($this->isInstanceOf('\PlaygroundGame\Entity\Entry'))
    	->will($this->returnValue(true));

    	$this->assertTrue($gs->playBonus($game, null,0));
    }

    public function testPlayBonusNotAllowed()
    {
    	$mapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
    	->disableOriginalConstructor()
    	->getMock();

    	$game = new GameEntity();
    	$game->setPlayBonus('one');

    	$gs = new \PlaygroundGame\Service\Game();
    	$gs->setEntryMapper($mapper);

    	// return false : The user has already played the bonus game
    	$gs->getEntryMapper()
    	->expects($this->once())
    	->method('findOneBy')
    	->will($this->returnValue(true));

    	$this->assertFalse($gs->playBonus($game, null,0));
    }

    public function testCheckExistingEntryNoUser()
    {
    	$mapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
    	->disableOriginalConstructor()
    	->getMock();

    	$game = new GameEntity();

    	$gs = new \PlaygroundGame\Service\Game();
    	$gs->setEntryMapper($mapper);

    	// return false : The user has already played the bonus game
    	$gs->getEntryMapper()
    	->expects($this->never())
    	->method('findOneBy')
    	->will($this->returnValue(true));

    	$this->assertFalse($gs->checkExistingEntry($game, null, null));
    }

    public function testCheckExistingEntryUserAndInactive()
    {
    	$mapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
    	->disableOriginalConstructor()
    	->getMock();

    	$game = new GameEntity();

    	$gs = new \PlaygroundGame\Service\Game();
    	$gs->setEntryMapper($mapper);

    	// I check that the array in findOneBy doesn't contain the parameter
    	$gs->getEntryMapper()
    	->expects($this->once())
    	->method('findOneBy')
    	->with($this->callback(function($o) {
	        return !isset($o['active']);
	    }))
    	->will($this->returnValue($this->getMock('PlaygroundGame\Entity\Entry')));

    	$gs->checkExistingEntry($game, true, null);
    }

    public function testCheckExistingEntryUserAndActive()
    {
    	$mapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
    	->disableOriginalConstructor()
    	->getMock();

    	$game = new GameEntity();

    	$gs = new \PlaygroundGame\Service\Game();
    	$gs->setEntryMapper($mapper);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$gs->getEntryMapper()
    	->expects($this->once())
    	->method('findOneBy')
    	->with($this->callback(function($o) {
    		return isset($o['active']) && $o['active'] === 1;
    	}))
    	->will($this->returnValue($this->getMock('PlaygroundGame\Entity\Entry')));

    	$gs->checkExistingEntry($game, true, 1);
    }

    public function testCheckIsFanNotOnFacebook()
    {
    	$game = new GameEntity();
    	$gs = new \PlaygroundGame\Service\Game();

    	$this->assertTrue($gs->checkIsFan($game));
    }

    public function testCheckIsFanOnFacebookNotFan()
    {
    	$game = new GameEntity();
    	$gs = new \PlaygroundGame\Service\Game();

    	$session = new \Zend\Session\Container('facebook');

    	$data = array('page' => array('liked' => 0));
    	$session->offsetSet('signed_request',$data);

    	$this->assertFalse($gs->checkIsFan($game));
    }

    public function testCheckIsFanOnFacebookFan()
    {
    	$game = new GameEntity();
    	$gs = new \PlaygroundGame\Service\Game();

    	$session = new \Zend\Session\Container('facebook');

    	$data = array('page' => array('liked' => 1));
    	$session->offsetSet('signed_request',$data);

    	$this->assertTrue($gs->checkIsFan($game));
    }

    public function testPlayExistingEntry()
    {
    	$mapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
    	->disableOriginalConstructor()
    	->getMock();

    	$game = new GameEntity();

    	//mocking the method checkExistingEntry
    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkExistingEntry'))
    	->disableOriginalConstructor()
    	->getMock();

    	$f->setEntryMapper($mapper);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue($this->getMock('PlaygroundGame\Entity\Entry')));

		$entry = $f->play($game, null);

    	$this->assertInstanceOf('\PlaygroundGame\Entity\Entry', $entry);
    }

    public function testPlayNonExistingEntry()
    {
    	$mapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
    	->disableOriginalConstructor()
    	->getMock();

    	$game = new GameEntity();

    	//mocking the method checkExistingEntry
    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkExistingEntry'))
    	->disableOriginalConstructor()
    	->getMock();

    	$f->setEntryMapper($mapper);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue(false));

    	$f->getEntryMapper()
    	->expects($this->once())
    	->method('insert')
    	->will($this->returnValue($this->getMock('PlaygroundGame\Entity\Entry')));

		$entry = $f->play($game, null);

    	$this->assertInstanceOf('\PlaygroundGame\Entity\Entry', $entry);
    }

    public function testPlayNonExistingEntryWithOverLimit()
    {
    	$mapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
    	->disableOriginalConstructor()
    	->getMock();

    	$game = new GameEntity();
    	$game->setPlayLimit(1);
    	$game->setPlayLimitScale('always');

    	//mocking the method checkExistingEntry
    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkExistingEntry'))
    	->disableOriginalConstructor()
    	->getMock();

    	$f->setEntryMapper($mapper);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue(false));

    	$f->getEntryMapper()
    	->expects($this->once())
    	->method('findLastEntriesBy')
    	->will($this->returnValue(2));

    	$this->assertFalse($entry = $f->play($game, null));
    }

    public function testPlayNonExistingEntryWithUnderLimit()
    {
    	$mapper = $this->getMockBuilder('PlaygroundGame\Mapper\Entry')
    	->disableOriginalConstructor()
    	->getMock();

    	$game = new GameEntity();
    	$game->setPlayLimit(1);
    	$game->setPlayLimitScale('always');

    	//mocking the method checkExistingEntry
    	$f = $this->getMockBuilder('PlaygroundGame\Service\Game')
    	->setMethods(array('checkExistingEntry'))
    	->disableOriginalConstructor()
    	->getMock();

    	$f->setEntryMapper($mapper);

    	// I check that the array in findOneBy contains the parameter 'active' = 1
    	$f->expects($this->once())
    	->method('checkExistingEntry')
    	->will($this->returnValue(false));

    	$f->getEntryMapper()
    	->expects($this->once())
    	->method('findLastEntriesBy')
    	->will($this->returnValue(0));

    	$f->getEntryMapper()
    	->expects($this->once())
    	->method('insert')
    	->will($this->returnValue($this->getMock('PlaygroundGame\Entity\Entry')));

    	$entry = $f->play($game, null);

    	$this->assertInstanceOf('\PlaygroundGame\Entity\Entry', $entry);
    }
}
