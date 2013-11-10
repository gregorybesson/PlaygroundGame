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

        $this->getForm();
        $this->quizQuestionData = array(
            'type' => '1',
            'question' => 'Ceci est une question ?',
            'position'  => '0',
            'audio' => '123456789',
            'autoplay' => '1',
            'weight' => '1',
            'timer' => '',
            'timer_duration' => '',
            'submit' => '',
            'quiz_id' => '2',
            'prediction' => '0',
            'hint' => '',
            'timer_duration' => ''
        );
        
        parent::setUp();
        
    }
    
    public function testCanInsertNewRecord()
    {
        $question = new QuizQuestionEntity();
        $this->form->setInputFilter($question->getInputFilter());
        
        $this->form->bind($question);
        $this->form->setData($this->quizQuestionData);
        $this->assertTrue($this->form->isValid());
        
    }
    
    /**
     * Test pour vérifier si le formulaire est valide si le champ Timer est vide
     */
    public function testCanInsertNewRecordWithTimerNull()
    {
        $this->quizQuestionData['timer'] = null;
        
        $entity = new QuizQuestionEntity();
        $this->form->setInputFilter($entity->getInputFilter());
        
        $this->form->bind($entity);
        $this->form->setData($this->quizQuestionData);
        $this->assertTrue($this->form->isValid());
    }
    
    /**
     * Test pour vérifier si le formulaire est valide si le champ Autoplay est vide
     */
    public function testCanInsertNewRecordWithAutoplayNull()
    {
        $this->quizQuestionData['autoplay'] = null;
        
        $entity = new QuizQuestionEntity();
        $this->form->setInputFilter($entity->getInputFilter());
        
        $this->form->bind($entity);
        $this->form->setData($this->quizQuestionData);
        $this->assertTrue($this->form->isValid());
    }
    
    /**
     * Test pour vérifier si le formulaire est valide si le champ Audio est vide
     */
    public function testCanInsertNewRecordWithAudioNull()
    {
        $this->quizQuestionData['audio'] = null;
    
        $entity = new QuizQuestionEntity();
        $this->form->setInputFilter($entity->getInputFilter());
    
        $this->form->bind($entity);
        $this->form->setData($this->quizQuestionData);
        $this->assertTrue($this->form->isValid());
    }

    /**
     * Test pour vérifier si le formulaire n'est pas valide dans le cas où il n'y a pas de question
     */
    public function testCannotInsertNewRecordWithNoQuestion()
    {
        $this->quizQuestionData['question'] = null;
       
        $entity = new QuizQuestionEntity();
        $this->form->setInputFilter($entity->getInputFilter());
        
        $this->form->bind($entity);
        $this->form->setData($this->quizQuestionData);
        $this->assertFalse($this->form->isValid());
        $this->assertEquals(1, count($this->form->getMessages()));
    }
    
    /**
     * Test pour vérifier si le formulaire n'est pas valide si il y a une réponse mais pas de position
     */
    public function testCannotInsertNewRecordWithAnswerAndNoPosition()
    {
        $this->quizQuestionData['answers'][0] = array(
        	'id' => '1',
            'answer' => 'Ceci est une réponse',
            'correct' => '1'
        );
        
        $entity = new QuizQuestionEntity();
        $this->form->setInputFilter($entity->getInputFilter());
        
        $this->form->bind($entity);
        $this->form->setData($this->quizQuestionData);
        $this->assertFalse($this->form->isValid());
    }
    
    public function getForm()
    {
        if (null === $this->form) {
            $this->form = $this->sm->get('playgroundgame_quizquestion_form');
        }
    
        return $this->form;
    }
}