<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\quizreply as QuizReplyEntity;
use PlaygroundGameTest\Bootstrap;

class QuizReplyTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_quizreply_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindById()
    {
        $quizreply = new QuizReplyEntity();
        $quizreply->setTotalQuestions(1);
        $quizreply->setMaxCorrectAnswers(1);
        $quizreply->setTotalCorrectAnswers(1);
        $quizreply = $this->tm->insert($quizreply);
        $this->assertEquals($quizreply, $this->tm->findById($quizreply->getId()));
    }

    public function testUpdate()
    {
        $quizreply = new QuizReplyEntity();
        $quizreply->setTotalQuestions(1);
        $quizreply->setMaxCorrectAnswers(1);
        $quizreply->setTotalCorrectAnswers(1);
        $quizreply = $this->tm->insert($quizreply);
        $quizreply->setTotalQuestions(3);
        $quizreply = $this->tm->update($quizreply);
        $this->assertEquals(3, $quizreply->getTotalQuestions(3));
    }

    public function testRemove()
    {
        $quizreply = new QuizReplyEntity();
        $quizreply->setTotalQuestions(1);
        $quizreply->setMaxCorrectAnswers(1);
        $quizreply->setTotalCorrectAnswers(1);
        $quizreply = $this->tm->insert($quizreply);
        $id = $quizreply->getId();
        $this->tm->remove($quizreply);
        $this->assertNull($this->tm->findById($id));
    }

    public function testFindAll()
    {
        $quizreply = new QuizReplyEntity();
        $quizreply->setTotalQuestions(1);
        $quizreply->setMaxCorrectAnswers(1);
        $quizreply->setTotalCorrectAnswers(1);
        $quizreply = $this->tm->insert($quizreply);
        $quizreply = new QuizReplyEntity();
        $quizreply->setTotalQuestions(2);
        $quizreply->setMaxCorrectAnswers(1);
        $quizreply->setTotalCorrectAnswers(1);
        $quizreply = $this->tm->insert($quizreply);
        $quizreply = new QuizReplyEntity();
        $quizreply->setTotalQuestions(3);
        $quizreply->setMaxCorrectAnswers(1);
        $quizreply->setTotalCorrectAnswers(1);
        $quizreply = $this->tm->insert($quizreply);

        $quizreplies = $this->tm->findAll();
        $this->assertEquals(3, count($quizreplies));

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
