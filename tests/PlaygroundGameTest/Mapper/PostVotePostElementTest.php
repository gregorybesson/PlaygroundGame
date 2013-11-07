<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\PostVotePostElement as PostVotePostElementEntity;
use PlaygroundGameTest\Bootstrap;

class PostVotePostElementTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_postvotepostelement_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindById()
    {
        $postvotepostelement = new PostVotePostElementEntity();
        $postvotepostelement->setName('Name');
        $this->tm->insert($postvotepostelement);
        $this->assertEquals($postvotepostelement, $this->tm->findById($postvotepostelement->getId()));
    }

    public function testUpdate()
    {
        $postvotepostelement = new PostVotePostElementEntity();
        $postvotepostelement->setName('Name');
        $postvotepostelement = $this->tm->insert($postvotepostelement);
        $postvotepostelement->setName('Name 2');
        $postvotepostelement = $this->tm->update($postvotepostelement);
        $this->assertEquals('Name 2', $postvotepostelement->getName());
    }

    public function testRemove()
    {
        $postvotepostelement = new PostVotePostElementEntity();
        $postvotepostelement->setName('Name');
        $postvotepostelement = $this->tm->insert($postvotepostelement);
        $id = $postvotepostelement->getId();
        $this->tm->remove($postvotepostelement);
        $this->assertNull($this->tm->findById($id));
    }

    public function testFindAll()
    {
        $postvotepostelement = new PostVotePostElementEntity();
        $postvotepostelement->setName('Name 1');
        $postvotepostelement = $this->tm->insert($postvotepostelement);
        $postvotepostelement = new PostVotePostElementEntity();
        $postvotepostelement->setName('Name 2');
        $postvotepostelement = $this->tm->insert($postvotepostelement);
        $postvotepostelement = new PostVotePostElementEntity();
        $postvotepostelement->setName('Name 3');;
        $postvotepostelement = $this->tm->insert($postvotepostelement);

        $postvotepostelements = $this->tm->findAll();
        $this->assertEquals(3, count($postvotepostelements));

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
