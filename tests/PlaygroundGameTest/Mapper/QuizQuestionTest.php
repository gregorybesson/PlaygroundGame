<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\QuizQuestion as QuizQuestionEntity;
use PlaygroundGameTest\Bootstrap;

class QuizQuestionTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
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
    }

    public function testUpdate()
    {
        $quizquestion = new QuizQuestionEntity();
        $quizquestion->setQuestion('Ceci est une question ?');
        $quizquestion = $this->tm->insert($quizquestion);
        $quizquestion->setQuestion('Ceci est une nouvelle question ?');
        $quizquestion = $this->tm->update($quizquestion);
        $this->assertEquals('Ceci est une nouvelle question ?', $quizquestion->getQuestion());
    }

    public function testRemove()
    {
        $quizquestion = new QuizQuestionEntity();
        $quizquestion->setQuestion('Ceci est une question ?');
        $quizquestion = $this->tm->insert($quizquestion);
        $id = $quizquestion->getId();
        $this->tm->remove($quizquestion);
        $this->assertNull($this->tm->findById($id));
    }

    public function testFindAll()
    {
        $quizquestion = new QuizQuestionEntity();
        $quizquestion->setQuestion('Ceci est une question ? 1');
        $quizquestion = $this->tm->insert($quizquestion);
        $quizquestion = new QuizQuestionEntity();
        $quizquestion->setQuestion('Ceci est une question ? 2');
        $quizquestion = $this->tm->insert($quizquestion);
        $quizquestion = new QuizQuestionEntity();
        $quizquestion->setQuestion('Ceci est une question ? 3');
        $quizquestion = $this->tm->insert($quizquestion);

        $quizquestions = $this->tm->findAll();
        $this->assertEquals(3, count($quizquestions));

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
