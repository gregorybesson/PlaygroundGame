<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\QuizQuestion as QuizQuestionEntity;
use PlaygroundGameTest\Bootstrap;

class QuizQuestionTest extends \PHPUnit\Framework\TestCase
{
    protected $traceError = true;

    protected function setUp(): void
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_quizquestion_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindById()
    {
        $quizquestion = new QuizQuestionEntity();
        $quizquestion->setQuestion('Ceci est une question ?');
        $quizquestion = $this->tm->insert($quizquestion);
        $this->assertEquals($quizquestion, $this->tm->findById($quizquestion->getId()));
        $this->tm->remove($quizquestion);
    }

    public function testUpdate()
    {
        $quizquestion = new QuizQuestionEntity();
        $quizquestion->setQuestion('Ceci est une question ?');
        $quizquestion = $this->tm->insert($quizquestion);
        $quizquestion->setQuestion('Ceci est une nouvelle question ?');
        $quizquestion = $this->tm->update($quizquestion);
        $this->assertEquals('Ceci est une nouvelle question ?', $quizquestion->getQuestion());
        $this->tm->remove($quizquestion);
    }
// test no stable on travis Oo
    // public function testRemove()
    // {
    //     $quizquestion = new QuizQuestionEntity();
    //     $quizquestion->setQuestion('Ceci est une question ?');
    //     $quizquestion = $this->tm->insert($quizquestion);
    //     $id = $quizquestion->getId();
    //     $this->tm->remove($quizquestion);
    //     $this->assertNull($this->tm->findById($id));
    // }


    // public function testFindAll()
    // {
    //     $quizquestion = new QuizQuestionEntity();
    //     $quizquestion->setQuestion('Ceci est une question ? 1');
    //     $quizquestion = $this->tm->insert($quizquestion);
    //     $this->assertCount(1, $this->tm->findAll());

    //     $quizquestion = new QuizQuestionEntity();
    //     $quizquestion->setQuestion('Ceci est une question ? 2');
    //     $quizquestion = $this->tm->insert($quizquestion);
    //     $this->assertCount(2, $this->tm->findAll());

    //     $quizquestion = new QuizQuestionEntity();
    //     $quizquestion->setQuestion('Ceci est une question ? 3');
    //     $quizquestion = $this->tm->insert($quizquestion);
    //     $this->assertCount(3, $this->tm->findAll());
    // }

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
