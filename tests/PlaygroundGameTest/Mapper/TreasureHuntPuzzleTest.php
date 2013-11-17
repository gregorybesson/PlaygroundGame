<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\TreasureHuntPuzzle as TreasureHuntPuzzleEntity;
use PlaygroundGameTest\Bootstrap;

class TreasureHuntPuzzleTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_treasurehuntpuzzle_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindById()
    {
        $treasurehuntpuzzle = new TreasureHuntPuzzleEntity();
        $treasurehuntpuzzle->setDomain('domain');
        $treasurehuntpuzzle = $this->tm->insert($treasurehuntpuzzle);
        $this->assertEquals($treasurehuntpuzzle, $this->tm->findById($treasurehuntpuzzle->getId()));
    }

    public function testUpdate()
    {
        $treasurehuntpuzzle = new TreasureHuntPuzzleEntity();
        $treasurehuntpuzzle->setDomain('domain');
        $treasurehuntpuzzle = $this->tm->insert($treasurehuntpuzzle);
        $treasurehuntpuzzle->setDomain('domain 2');
        $treasurehuntpuzzle = $this->tm->update($treasurehuntpuzzle);
        $this->assertEquals('domain 2', $treasurehuntpuzzle->getDomain());
    }

    public function testRemove()
    {
        $treasurehuntpuzzle = new TreasureHuntPuzzleEntity();
        $treasurehuntpuzzle->setDomain('domain');
        $treasurehuntpuzzle = $this->tm->insert($treasurehuntpuzzle);
        $id = $treasurehuntpuzzle->getId();
        $this->tm->remove($treasurehuntpuzzle);
        $this->assertNull($this->tm->findById($id));
    }

    public function testFindAll()
    {
        $treasurehuntpuzzle = new TreasureHuntPuzzleEntity();
        $treasurehuntpuzzle->setDomain('domain 1');
        $treasurehuntpuzzle = $this->tm->insert($treasurehuntpuzzle);
        $treasurehuntpuzzle = new TreasureHuntPuzzleEntity();
        $treasurehuntpuzzle->setDomain('domain 2');
        $treasurehuntpuzzle = $this->tm->insert($treasurehuntpuzzle);
        $treasurehuntpuzzle = new TreasureHuntPuzzleEntity();
        $treasurehuntpuzzle->setDomain('domain 3');
        $treasurehuntpuzzle = $this->tm->insert($treasurehuntpuzzle);

        $treasurehuntpuzzles = $this->tm->findAll();
        $this->assertEquals(3, count($treasurehuntpuzzles));

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
