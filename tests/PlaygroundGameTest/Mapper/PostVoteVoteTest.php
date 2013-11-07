<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\PostVoteVote as PostVoteVoteEntity;
use PlaygroundGameTest\Bootstrap;

class postvotevoteTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_postvotevote_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindById()
    {
        $postvotevote = new PostVoteVoteEntity();
        $postvotevote->setUserId(1);
        $this->tm->insert($postvotevote);
        $this->assertEquals($postvotevote, $this->tm->findById($postvotevote->getId()));
    }

    public function testUpdate()
    {
        $postvotevote = new PostVoteVoteEntity();
        $postvotevote->setUserId(1);
        $postvotevote = $this->tm->insert($postvotevote);
        $postvotevote->setUserId(2);
        $postvotevote = $this->tm->update($postvotevote);
        $this->assertEquals(2, $postvotevote->getUserId());
    }

    public function testRemove()
    {
        $postvotevote = new PostVoteVoteEntity();
        $postvotevote->setUserId(1);
        $postvotevote = $this->tm->insert($postvotevote);
        $id = $postvotevote->getId();
        $this->tm->remove($postvotevote);
        $this->assertNull($this->tm->findById($id));
    }

    public function testFindAll()
    {
        $postvotevote = new PostVoteVoteEntity();
        $postvotevote->setUserId(1);
        $postvotevote = $this->tm->insert($postvotevote);
        $postvotevote = new PostVoteVoteEntity();
        $postvotevote->setUserId(2);
        $postvotevote = $this->tm->insert($postvotevote);
        $postvotevote = new PostVoteVoteEntity();
        $postvotevote->setUserId(3);
        $postvotevote = $this->tm->insert($postvotevote);

        $postvotevotes = $this->tm->findAll();
        $this->assertEquals(3, count($postvotevotes));

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
