<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\PostVoteForm as PostVoteFormEntity;
use PlaygroundGameTest\Bootstrap;

class PostVoteFormTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_postvoteform_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindById()
    {
        $postvoteform = new PostVoteFormEntity();
        $postvoteform->setTitle('Ceci est un titre');
        $this->tm->insert($postvoteform);
        $this->assertEquals($postvoteform, $this->tm->findById($postvoteform->getId()));
    }

    public function testUpdate()
    {
        $postvoteform = new PostVoteFormEntity();
        $postvoteform->setTitle('Ceci est un titre');
        $postvoteform = $this->tm->insert($postvoteform);
        $postvoteform->setTitle('Ceci est un titre 2');
        $postvoteform = $this->tm->update($postvoteform);
        $this->assertEquals('Ceci est un titre 2', $postvoteform->getTitle());
    }

    public function testRemove()
    {
        $postvoteform = new PostVoteFormEntity();
        $postvoteform->setTitle('Ceci est un titre');
        $postvoteform = $this->tm->insert($postvoteform);
        $id = $postvoteform->getId();
        $this->tm->remove($postvoteform);
        $this->assertNull($this->tm->findById($id));
    }

    public function testFindAll()
    {
        $titles = array('Ceci est un titre 1', 'Ceci est un titre 2', 'Ceci est un titre 3');

        $postvoteform = new PostVoteFormEntity();
        $postvoteform->setTitle($titles[0]);
        $postvoteform = $this->tm->insert($postvoteform);
        $postvoteform = new PostVoteFormEntity();
        $postvoteform->setTitle($titles[0]);
        $postvoteform = $this->tm->insert($postvoteform);
        $postvoteform = new PostVoteFormEntity();
        $postvoteform->setTitle($titles[0]);
        $postvoteform = $this->tm->insert($postvoteform);

        $postvoteforms = $this->tm->findAll();
        $this->assertTrue(in_array($postvoteforms[0]->getTitle(), $titles));
        $this->assertTrue(in_array($postvoteforms[1]->getTitle(), $titles));
        $this->assertTrue(in_array($postvoteforms[2]->getTitle(), $titles));
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
