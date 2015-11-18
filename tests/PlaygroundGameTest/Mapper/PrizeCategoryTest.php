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
        $this->assertEquals($prizeCategory, current($this->tm->findBy(array('identifier' => 'iden'))));
        $this->tm->remove($prizeCategory);
        $this->assertEmpty($this->tm->findBy(array('identifier' => 'iden')));
    }

    public function testInsertTranslation()
    {
        $translator = $this->sm->get('translator');
        $prizeCategory = new PrizeCategoryEntity();
        $translator->setLocale('en_US');
        $prizeCategory->setTitle('Anglais')
        ->setIdentifier('anglais');
        $prizeCategory = $this->tm->insert($prizeCategory);
        $translator->setLocale('fr_FR');
        $prizeCategory->setTitle('Francais')
        ->setIdentifier('francais');
        $prizeCategory = $this->tm->insert($prizeCategory);
        $this->assertCount(1, $this->tm->findAll());
        $this->assertInstanceOf('\PlaygroundGame\Entity\PrizeCategory', current($this->tm->findAll()));

        $prizeCategory = current($this->tm->findAll());
        $this->assertEquals("Francais", $prizeCategory->getTitle());
        $this->assertEquals("francais", $prizeCategory->getIdentifier());
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
        $this->tm->remove($prizeCategory);
        $this->assertEmpty($this->tm->findAll());
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
        $self = $this;
        $this->em->transactional(function($em) use ($self) {
            $prizeCategory = new PrizeCategoryEntity();
            $prizeCategory->setIdentifier('iden 1');
            $prizeCategory->setTitle('Un Titre');
            $prizeCategory = $self->tm->insert($prizeCategory);
        });
        
        $this->em->transactional(function($em) use ($self) {
            $prizeCategory = new PrizeCategoryEntity();
            $prizeCategory->setIdentifier('iden 2');
            $prizeCategory->setTitle('Un Titre');
            $prizeCategory = $self->tm->insert($prizeCategory);
        });
            
        $this->em->transactional(function($em) use ($self) {
            $prizeCategory = new PrizeCategoryEntity();
            $prizeCategory->setIdentifier('iden 3');
            $prizeCategory->setTitle('Un Titre');
            $prizeCategory = $self->tm->insert($prizeCategory);
        });

        $prizeCategories = $this->tm->findAll();
        $this->assertEquals(3, count($prizeCategories));
        
        foreach ($this->tm->findAll() as $prizeCategory) {
            $this->tm->remove($prizeCategory);
        }
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
