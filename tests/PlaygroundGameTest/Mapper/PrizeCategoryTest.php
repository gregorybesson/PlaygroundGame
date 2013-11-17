<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\PrizeCategory as PrizeCategoryEntity;
use PlaygroundGameTest\Bootstrap;

class PrizeCategoryTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_prizeCategory_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindById()
    {
        $prizeCategory = new PrizeCategoryEntity();
        $prizeCategory->setIdentifier('iden');
        $prizeCategory->setTitle('Un Titre');
        $prizeCategory = $this->tm->insert($prizeCategory);
        $this->assertEquals($prizeCategory, $this->tm->findById($prizeCategory->getId()));
    }

    public function testUpdate()
    {
        $prizeCategory = new PrizeCategoryEntity();
        $prizeCategory->setIdentifier('iden');
        $prizeCategory->setTitle('Un Titre');
        $prizeCategory = $this->tm->insert($prizeCategory);
        $prizeCategory->setIdentifier('iden 2');
        $prizeCategory = $this->tm->update($prizeCategory);
        $this->assertEquals('iden 2', $prizeCategory->getIdentifier());
    }

    public function testRemove()
    {
        $prizeCategory = new PrizeCategoryEntity();
        $prizeCategory->setIdentifier('iden');
        $prizeCategory->setTitle('Un Titre');
        $prizeCategory = $this->tm->insert($prizeCategory);
        $id = $prizeCategory->getId();
        $this->tm->remove($prizeCategory);
        $this->assertNull($this->tm->findById($id));
    }

    public function testFindAll()
    {
        $prizeCategory = new PrizeCategoryEntity();
        $prizeCategory->setIdentifier('iden 1');
        $prizeCategory->setTitle('Un Titre');
        $prizeCategory = $this->tm->insert($prizeCategory);
        $prizeCategory = new PrizeCategoryEntity();
        $prizeCategory->setIdentifier('iden 2');
        $prizeCategory->setTitle('Un Titre');
        $prizeCategory = $this->tm->insert($prizeCategory);
        $prizeCategory = new PrizeCategoryEntity();
        $prizeCategory->setIdentifier('iden 3');
        $prizeCategory->setTitle('Un Titre');
        $prizeCategory = $this->tm->insert($prizeCategory);

        $prizeCategories = $this->tm->findAll();
        $this->assertEquals(3, count($prizeCategories));

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
