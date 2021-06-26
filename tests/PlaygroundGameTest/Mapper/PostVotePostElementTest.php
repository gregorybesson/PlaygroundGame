<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\PostVotePostElement as PostVotePostElementEntity;
use \PlaygroundGame\Entity\PostVotePost as PostVotePostEntity;
use PlaygroundGameTest\Bootstrap;

class PostVotePostElementTest extends \PHPUnit\Framework\TestCase
{
    protected $traceError = true;

    protected function setUp(): void
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
        $postvotePost = new PostVotePostEntity();
        $postvotePost = $this->tm->insert($postvotePost);
        $postvotepostelement = new PostVotePostElementEntity();
        $postvotepostelement->setName('Name');
        $postvotepostelement->setPost($postvotePost);
        $p = $this->tm->insert($postvotepostelement);
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
        $postvotepostelement->setId(10);
        $postvotepostelement = $this->tm->update($postvotepostelement);
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
        $postvotepostelement->setName('Name 3');
        $postvotepostelement = $this->tm->insert($postvotepostelement);

        $postvotepostelements = $this->tm->findAll();
        $this->assertEquals(3, count($postvotepostelements));
    }

    protected function tearDown(): void
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $this->sm = null;
        $this->em = null;
        unset($this->sm);
        unset($this->em);
        parent::tearDown();
    }
}
