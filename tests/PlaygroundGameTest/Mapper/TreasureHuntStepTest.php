<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\TreasureHuntStep as TreasureHuntStepEntity;
use PlaygroundGameTest\Bootstrap;

class TreasureHuntStepTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_treasurehuntstep_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindById()
    {
        $treasurehuntstep = new TreasureHuntStepEntity();
        $treasurehuntstep->setDomain('domain');
        $this->tm->insert($treasurehuntstep);
        $this->assertEquals($treasurehuntstep, $this->tm->findById($treasurehuntstep->getId()));
    }

    public function testUpdate()
    {
        $treasurehuntstep = new TreasureHuntStepEntity();
        $treasurehuntstep->setDomain('domain');
        $treasurehuntstep = $this->tm->insert($treasurehuntstep);
        $treasurehuntstep->setDomain('domain 2');
        $treasurehuntstep = $this->tm->update($treasurehuntstep);
        $this->assertEquals('domain 2', $treasurehuntstep->getDomain());
    }

    public function testRemove()
    {
        $treasurehuntstep = new TreasureHuntStepEntity();
        $treasurehuntstep->setDomain('domain');
        $treasurehuntstep = $this->tm->insert($treasurehuntstep);
        $id = $treasurehuntstep->getId();
        $this->tm->remove($treasurehuntstep);
        $this->assertNull($this->tm->findById($id));
    }

    public function testFindAll()
    {
        $treasurehuntstep = new TreasureHuntStepEntity();
        $treasurehuntstep->setDomain('domain 1');
        $treasurehuntstep = $this->tm->insert($treasurehuntstep);
        $treasurehuntstep = new TreasureHuntStepEntity();
        $treasurehuntstep->setDomain('domain 2');
        $treasurehuntstep = $this->tm->insert($treasurehuntstep);
        $treasurehuntstep = new TreasureHuntStepEntity();
        $treasurehuntstep->setDomain('domain 3');
        $treasurehuntstep = $this->tm->insert($treasurehuntstep);

        $treasurehuntsteps = $this->tm->findAll();
        $this->assertEquals('domain 1', $treasurehuntsteps[0]->getDomain());
        $this->assertEquals('domain 2', $treasurehuntsteps[1]->getDomain());
        $this->assertEquals('domain 3', $treasurehuntsteps[2]->getDomain());

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
