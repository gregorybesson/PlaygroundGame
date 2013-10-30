<?php

namespace PlaygroundGameTest\Entity;

use PlaygroundGameTest\Bootstrap;
use PlaygroundGame\Entity\QuizQuestion;

class quizQuestionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Service Manager
     * @var Zend\ServiceManager\ServiceManager
     */
    protected $sm;
    
    /**
     * Doctrine Entity Manager
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;
    
    /**
     * quizQuestion sample
     * @var Array
     */
    protected $quizQuestionData;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
    
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
    
        $tool->dropSchema($classes);
    
        $tool->createSchema($classes);
        
    
        $this->quizQuestionData = array(
            'type' => '1',
            'question' => 'Ceci est une question ?',
            'position'  => '0',
            'audio' => '123456789',
            'autoplay' => 'false',
            'weight' => '1',
            'timer' => 'false'
        );
        parent::setUp();
    }
    
    public function testCanInsertNewRecord()
    {
        $quizQuestion = new QuizQuestion();
        $quizQuestion->populate($this->quizQuestionData);

        // save data
        $this->em->persist($quizQuestion);
        $this->em->flush();
    
        $this->assertEquals($this->quizQuestionData['audio'], $quizQuestion->getAudio());
        $this->assertEquals($this->quizQuestionData['autoplay'], $quizQuestion->getAutoplay());
    
        return $quizQuestion->getId();
    }
    
    /**
     * 
     * @depends testCanInsertNewRecord
     */
    public function testCanUpdateInsertedRecord($id)
    {
        $data = array(
            'id' => $id,
            'audio' => '987654321',
            'autoplay' => 'true'
        );
        $quizQuestion = $this->em->getRepository('PlaygroundGame\Entity\QuizQuestion')->find($id);
        $this->assertInstanceOf('PlaygroundGame\Entity\QuizQuestion', $quizQuestion);
        $this->assertEquals($this->quizQuestionData['type'], $quizQuestion->getType());
        $this->assertEquals($this->quizQuestionData['question'], $quizQuestion->getQuestion());
        $this->assertEquals($this->quizQuestionData['position'], $quizQuestion->getPosition());
        $this->assertEquals($this->quizQuestionData['weight'], $quizQuestion->getWeight());
        $this->assertEquals($this->quizQuestionData['timer'], $quizQuestion->getTimer());
    
        $quizQuestion->populate($data);
        $this->em->flush();
        
        $this->assertEquals($data['audio'], $quizQuestion->getAudio());
        $this->assertEquals($data['autoplay'], $quizQuestion->getAutoplay());

    }
    
    /**
     * @depends testCanInsertNewRecord
     */
    public function testCanRemoveInsertedRecord($id)
    {
        $quizQuestion = $this->em->getRepository('PlaygroundGame\Entity\QuizQuestion')->find($id);
        $this->assertInstanceOf('PlaygroundGame\Entity\QuizQuestion', $quizQuestion);
    
        $this->em->remove($quizQuestion);
        $this->em->flush();
    
        $quizQuestion = $this->em->getRepository('PlaygroundGame\Entity\QuizQuestion')->find($id);
        $this->assertEquals(false, $quizQuestion);
    }
    
    /**
     * Teste si les champs Timer, Audio et Autoplay sont null à la création d'une Entity
     */
    public function testIfFieldsAreNull()
    {
        $quizQuestion = new QuizQuestion();
        
        $this->assertEquals(null, $quizQuestion->getTimer());
        $this->assertEquals(null, $quizQuestion->getAudio());
        $this->assertEquals(null, $quizQuestion->getAutoplay());
    }
    
    
    
    public function tearDown()
    {
        $dbh = $this->em->getConnection();
        //$result = $dbh->exec("UPDATE sqlite_sequence SET seq = 10 WHERE name='album';");
    
        unset($this->sm);
        unset($this->em);
        parent::tearDown();
    }
}