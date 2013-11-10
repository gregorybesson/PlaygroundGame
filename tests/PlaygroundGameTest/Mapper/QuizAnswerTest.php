<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\QuizAnswer as QuizAnswerEntity;
use PlaygroundGameTest\Bootstrap;

class QuizAnswerTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_quizanswer_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindById()
    {
        $quizanswer = new QuizAnswerEntity();
        $quizanswer->setAnswer('Ceci est une réponse.');
        $quizanswer = $this->tm->insert($quizanswer);
        $this->assertEquals($quizanswer, $this->tm->findById($quizanswer->getId()));
    }

    public function testUpdate()
    {
        $quizanswer = new QuizAnswerEntity();
        $quizanswer->setAnswer('Ceci est une réponse.');
        $quizanswer = $this->tm->insert($quizanswer);
        $quizanswer->setAnswer('Ceci est une nouvelle réponse.');
        $quizanswer = $this->tm->update($quizanswer);
        $this->assertEquals('Ceci est une nouvelle réponse.', $quizanswer->getAnswer());
    }

    public function testRemove()
    {
        $quizanswer = new QuizAnswerEntity();
        $quizanswer->setAnswer('Ceci est une réponse.');
        $quizanswer = $this->tm->insert($quizanswer);
        $id = $quizanswer->getId();
        $this->tm->remove($quizanswer);
        $this->assertNull($this->tm->findById($id));
    }

    public function testFindAll()
    {
        $quizanswer = new QuizAnswerEntity();
        $quizanswer->setAnswer('Ceci est une réponse. 1');
        $quizanswer = $this->tm->insert($quizanswer);
        $quizanswer = new QuizAnswerEntity();
        $quizanswer->setAnswer('Ceci est une réponse. 2');
        $quizanswer = $this->tm->insert($quizanswer);
        $quizanswer = new QuizAnswerEntity();
        $quizanswer->setAnswer('Ceci est une réponse. 3');
        $quizanswer = $this->tm->insert($quizanswer);

        $quizanswers = $this->tm->findAll();
        $this->assertEquals(3, count($quizanswers));

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
