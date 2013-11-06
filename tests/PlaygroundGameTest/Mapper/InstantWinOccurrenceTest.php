<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\InstantWin;
use \PlaygroundGame\Entity\InstantWinOccurrence;
use PlaygroundGameTest\Bootstrap;

class InstantWinOccurrenceTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_instantwinoccurrence_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testAssertNoOtherFalse(){
        $occurrence_value = 'samevalue';
        $instantwin = new InstantWin();

        $instantwinoccurrence = new InstantWinOccurrence();
        $instantwinoccurrence->setOccurrenceValue($occurrence_value);
        $instantwinoccurrence->setInstantwin($instantwin);
        $instantwinoccurrence = $this->tm->insert($instantwinoccurrence);   

        $this->assertFalse($this->tm->assertNoOther($instantwin, $occurrence_value), "Already one occurrence with this value for this game");
    }

    public function testAssertNoOtherTrue(){
        $occurrence_value = 'samevalue';
        $instantwin = new InstantWin();

        $this->assertTrue($this->tm->assertNoOther($instantwin, $occurrence_value), "No occurrence registered with this value for this game");
    }

    public function tearDown()
    {
        $dbh = $this->em->getConnection();
        unset($this->tm);
        unset($this->sm);
        unset($this->em);
        parent::tearDown();
    }
}
