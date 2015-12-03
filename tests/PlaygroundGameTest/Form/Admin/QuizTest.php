<?php

namespace PlaygroundGameTest\Form\Admin;

use PlaygroundGameTest\Bootstrap;
use PlaygroundGame\Form\Admin\Quiz;
use PlaygroundGame\Entity\Quiz as QuizEntity;

class QuizTest extends \PHPUnit_Framework_TestCase
{
    protected $sm;

    protected $translator;

    protected $form;

    protected $quizData;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->sm->setAllowOverride(true);
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_game_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);

        //$this->getForm();
        $now = new \DateTime('today');
        $date = new \DateTime('tomorrow');
        $this->quizData = array(
            'id' => 0,
            'active' => '1',
            'drawAuto' => '1',
            'winners' => '3',
            'substitutes' => '0',
            'title' => 'Super Quiz',
            'identifier' => 'super-quiz',
            'publicationDate' => null,
            'startDate' => null,
            'endDate' => null,
            'closeDate' => null,
            'playLimit' => '0',
            'timer' => '0',
            'victoryConditions' => '100',
            'broadcastPlatform' => '0',
            'broadcastEmbed' => '0',
            'displayHome' => 0,
            'anonymousAllowed' => 0,
            'mailWinner' => 0,
            'mailLooser' => 0,
        );

        parent::setUp();
    }

    public function testCanInsertNewRecord()
    {
        $adminGameQuizForm = $this->getMockBuilder('PlaygroundGame\Service\PrizeCategory')
        ->setMethods(array('getActivePrizeCategories'))
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->sm->setService('playgroundgame_prizecategory_service', $adminGameQuizForm);
        
        $adminGameQuizForm->expects($this->any())
        ->method('getActivePrizeCategories')
        ->will($this->returnValue(array()));
        
        $quiz = new QuizEntity();
        $form = $this->sm->get('playgroundgame_quiz_form');
        $form->setInputFilter($quiz->getInputFilter());

        $form->bind($quiz);
        $form->setData($this->quizData);
        $this->assertTrue($form->isValid());
    }

    /**
     * Test to check if the form is valid and victoryConditions field
     * is empty (only for bet type)
     */
    public function testCanInsertNewRecordWithVictoryConditionsNull()
    {
        $adminGameQuizForm = $this->getMockBuilder('PlaygroundGame\Service\PrizeCategory')
        ->setMethods(array('getActivePrizeCategories'))
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->sm->setService('playgroundgame_prizecategory_service', $adminGameQuizForm);
        
        $adminGameQuizForm->expects($this->any())
        ->method('getActivePrizeCategories')
        ->will($this->returnValue(array()));
        
        $this->quizData['victoryConditions'] = null;

        $quiz = new QuizEntity();
        $form = $this->sm->get('playgroundgame_quiz_form');
        $form->setInputFilter($quiz->getInputFilter());
        $form->bind($quiz);
        $form->setData($this->quizData);
        $this->assertTrue($form->isValid());
    }

    /**
     * Test pour vérifier si le formulaire n'est pas valide dans le cas où il n'y a pas de winners
     */
    public function testCannotInsertNewRecordWithNoWinners()
    {
        $adminGameQuizForm = $this->getMockBuilder('PlaygroundGame\Service\PrizeCategory')
        ->setMethods(array('getActivePrizeCategories'))
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->sm->setService('playgroundgame_prizecategory_service', $adminGameQuizForm);
        
        $adminGameQuizForm->expects($this->any())
        ->method('getActivePrizeCategories')
        ->will($this->returnValue(array()));
        
        $this->quizData['winners'] = '';

        $quiz = new QuizEntity();
        $form = $this->sm->get('playgroundgame_quiz_form');
        $form->setInputFilter($quiz->getInputFilter());
        $form->bind($quiz);
        $form->setData($this->quizData);
        $this->assertFalse($form->isValid());
        $this->assertEquals(1, count($form->getMessages()));
    }

    /**
     * Test pour vérifier si le formulaire n'est pas valide ans le cas où il n'y a pas de substitutes
     */
    public function testCannotInsertNewRecordWithNoSubstitutes()
    {
        $adminGameQuizForm = $this->getMockBuilder('PlaygroundGame\Service\PrizeCategory')
        ->setMethods(array('getActivePrizeCategories'))
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->sm->setService('playgroundgame_prizecategory_service', $adminGameQuizForm);
        
        $adminGameQuizForm->expects($this->any())
        ->method('getActivePrizeCategories')
        ->will($this->returnValue(array()));
        
        $this->quizData['substitutes'] = '';

        $quiz = new QuizEntity();
        $form = $this->sm->get('playgroundgame_quiz_form');
        $form->setInputFilter($quiz->getInputFilter());

        $form->bind($quiz);
        $form->setData($this->quizData);
        $this->assertFalse($form->isValid());
        $this->assertEquals(2, count($form->getMessages()));
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
