<?php

namespace PlaygroundGameTest\Form\Admin;

use PlaygroundGameTest\Bootstrap;
use PlaygroundGame\Form\Admin\QuizQuestion;
use PlaygroundGame\Entity\QuizQuestion as QuizQuestionEntity;

class quizQuestionTest extends \PHPUnit_Framework_TestCase
{
    protected $sm;

    protected $translator;

    protected $form;

    protected $quizQuestionData;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->sm->setAllowOverride(true);

        $this->quizQuestionData = array(
            'type' => '1',
            'question' => 'Ceci est une question ?',
            'position'  => '0',
            'audio' => '123456789',
            'autoplay' => '1',
            'weight' => '1',
            'timer_duration' => '',
            'submit' => '',
            'quiz_id' => '2',
            'prediction' => '1',
            'hint' => '',
            'timer_duration' => '',
            'max_correct_answers' => '1',
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
        
        $question = new QuizQuestionEntity();
        $form = $this->sm->get('playgroundgame_quizquestion_form');
        $form->setInputFilter($question->getInputFilter());

        $form->bind($question);
        $form->setData($this->quizQuestionData);

        $this->assertTrue($form->isValid());

    }

    /**
     * Test pour vérifier si le formulaire est valide si le champ max_correct_answers est vide
     */
    public function testCanInsertNewRecordWithMaxCorrectAnswerNull()
    {
        $adminGameQuizForm = $this->getMockBuilder('PlaygroundGame\Service\PrizeCategory')
        ->setMethods(array('getActivePrizeCategories'))
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->sm->setService('playgroundgame_prizecategory_service', $adminGameQuizForm);
        
        $adminGameQuizForm->expects($this->any())
        ->method('getActivePrizeCategories')
        ->will($this->returnValue(array()));
        
        $question = new QuizQuestionEntity();
        $form = $this->sm->get('playgroundgame_quizquestion_form');
        $form->setInputFilter($question->getInputFilter());
        
        $this->quizQuestionData['max_correct_answers'] = null;

        $form->bind($question);
        $form->setData($this->quizQuestionData);
        $this->assertTrue($form->isValid());
    }

    /**
     * Test pour vérifier si le formulaire est valide si le champ prediction est vide
     */
    public function testCanInsertNewRecordWithPredictionNull()
    {
        $adminGameQuizForm = $this->getMockBuilder('PlaygroundGame\Service\PrizeCategory')
        ->setMethods(array('getActivePrizeCategories'))
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->sm->setService('playgroundgame_prizecategory_service', $adminGameQuizForm);
        
        $adminGameQuizForm->expects($this->any())
        ->method('getActivePrizeCategories')
        ->will($this->returnValue(array()));
        
        $question = new QuizQuestionEntity();
        $form = $this->sm->get('playgroundgame_quizquestion_form');
        $form->setInputFilter($question->getInputFilter());

        unset($this->quizQuestionData['prediction']);

        $form->bind($question);
        $form->setData($this->quizQuestionData);

        $this->assertTrue($form->isValid());
    }

    /**
     * Test pour vérifier si le formulaire est valide si le champ Timer est vide
     */
    public function testCanInsertNewRecordWithTimerNull()
    {
        $adminGameQuizForm = $this->getMockBuilder('PlaygroundGame\Service\PrizeCategory')
        ->setMethods(array('getActivePrizeCategories'))
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->sm->setService('playgroundgame_prizecategory_service', $adminGameQuizForm);
        
        $adminGameQuizForm->expects($this->any())
        ->method('getActivePrizeCategories')
        ->will($this->returnValue(array()));
        
        $question = new QuizQuestionEntity();
        $form = $this->sm->get('playgroundgame_quizquestion_form');
        $form->setInputFilter($question->getInputFilter());
        
        unset($this->quizQuestionData['timer']);

        $form->bind($question);
        $form->setData($this->quizQuestionData);
        $this->assertTrue($form->isValid());
    }

    /**
     * Test pour vérifier si le formulaire est valide si le champ Autoplay est vide
     */
    public function testCanInsertNewRecordWithAutoplayNull()
    {
        $adminGameQuizForm = $this->getMockBuilder('PlaygroundGame\Service\PrizeCategory')
        ->setMethods(array('getActivePrizeCategories'))
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->sm->setService('playgroundgame_prizecategory_service', $adminGameQuizForm);
        
        $adminGameQuizForm->expects($this->any())
        ->method('getActivePrizeCategories')
        ->will($this->returnValue(array()));
        
        $question = new QuizQuestionEntity();
        $form = $this->sm->get('playgroundgame_quizquestion_form');
        $form->setInputFilter($question->getInputFilter());
        
        unset($this->quizQuestionData['autoplay']);

        $form->bind($question);
        $form->setData($this->quizQuestionData);
        $this->assertTrue($form->isValid());
    }

    /**
     * Test pour vérifier si le formulaire est valide si le champ Audio est vide
     */
    public function testCanInsertNewRecordWithAudioNull()
    {
        $adminGameQuizForm = $this->getMockBuilder('PlaygroundGame\Service\PrizeCategory')
        ->setMethods(array('getActivePrizeCategories'))
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->sm->setService('playgroundgame_prizecategory_service', $adminGameQuizForm);
        
        $adminGameQuizForm->expects($this->any())
        ->method('getActivePrizeCategories')
        ->will($this->returnValue(array()));
        
        $question = new QuizQuestionEntity();
        $form = $this->sm->get('playgroundgame_quizquestion_form');
        $form->setInputFilter($question->getInputFilter());
        
        $this->quizQuestionData['audio'] = null;

        $form->bind($question);
        $form->setData($this->quizQuestionData);
        $this->assertTrue($form->isValid());
    }

    /**
     * Test pour vérifier si le formulaire n'est pas valide dans le cas où il n'y a pas de question
     */
    public function testCannotInsertNewRecordWithNoQuestion()
    {
        $adminGameQuizForm = $this->getMockBuilder('PlaygroundGame\Service\PrizeCategory')
        ->setMethods(array('getActivePrizeCategories'))
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->sm->setService('playgroundgame_prizecategory_service', $adminGameQuizForm);
        
        $adminGameQuizForm->expects($this->any())
        ->method('getActivePrizeCategories')
        ->will($this->returnValue(array()));
        
        $question = new QuizQuestionEntity();
        $form = $this->sm->get('playgroundgame_quizquestion_form');
        $form->setInputFilter($question->getInputFilter());
        
        $this->quizQuestionData['question'] = null;

        $form->bind($question);
        $form->setData($this->quizQuestionData);
        $this->assertFalse($form->isValid());
        $this->assertEquals(1, count($form->getMessages()));
    }

    /**
     * Test pour vérifier si le formulaire n'est pas valide si il y a une réponse mais pas de position
     */
    public function testCannotInsertNewRecordWithAnswerAndNoPosition()
    {
        $adminGameQuizForm = $this->getMockBuilder('PlaygroundGame\Service\PrizeCategory')
        ->setMethods(array('getActivePrizeCategories'))
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->sm->setService('playgroundgame_prizecategory_service', $adminGameQuizForm);
        
        $adminGameQuizForm->expects($this->any())
        ->method('getActivePrizeCategories')
        ->will($this->returnValue(array()));
        
        $question = new QuizQuestionEntity();
        $form = $this->sm->get('playgroundgame_quizquestion_form');
        $form->setInputFilter($question->getInputFilter());
        
        $this->quizQuestionData['answers'][0] = array(
        	'id' => '1',
            'answer' => 'Ceci est une réponse',
            'correct' => '1'
        );

        $form->bind($question);
        $form->setData($this->quizQuestionData);
        $this->assertFalse($form->isValid());
    }
}