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
        $quizreply1 = new QuizReplyEntity();
        $quizreply1->setTotalQuestions(1);
        $quizreply1->setMaxCorrectAnswers(1);
        $quizreply1->setTotalCorrectAnswers(1);
        $quizreply1 = $this->tm->insert($quizreply1);

        $quizreplies = $this->tm->findAll();
        $this->assertEquals(1, count($quizreplies));

        $quizreply2 = new QuizReplyEntity();
        $quizreply2->setTotalQuestions(2);
        $quizreply2->setMaxCorrectAnswers(2);
        $quizreply2->setTotalCorrectAnswers(2);
        $quizreply2 = $this->tm->insert($quizreply2);

        $quizreplies = $this->tm->findAll();
        $this->assertEquals(2, count($quizreplies));


        $quizreply3 = new QuizReplyEntity();
        $quizreply3->setTotalQuestions(3);
        $quizreply3->setMaxCorrectAnswers(3);
        $quizreply3->setTotalCorrectAnswers(3);
        $quizreply3 = $this->tm->insert($quizreply3);

        $quizreplies = $this->tm->findAll();
        $this->assertEquals(3, count($quizreplies));


        $quizreply4 = new QuizReplyEntity();
        $quizreply4->setTotalQuestions(4);
        $quizreply4->setMaxCorrectAnswers(4);
        $quizreply4->setTotalCorrectAnswers(4);
        $quizreply4 = $this->tm->insert($quizreply4);
        $this->em->flush();

        $quizreplies = $this->tm->findAll();
        $this->assertEquals(4, count($quizreplies));
    }

    public function tearDown()
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
