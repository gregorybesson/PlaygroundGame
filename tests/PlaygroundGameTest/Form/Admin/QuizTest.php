<?php

namespace PlaygroundGameTest\Form\Admin;

use PlaygroundGameTest\Bootstrap;
use PlaygroundGame\Form\Admin\Quiz;
use PlaygroundGame\Entity\Quiz as QuizEntity;

class quizTest extends \PHPUnit_Framework_TestCase
{
    protected $sm;

    protected $translator;

    protected $form;

    protected $quizData;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->getForm();
        $now = new \DateTime('now');
        $date = new \DateTime('tomorrow');
        $this->quizData = array(
            'id' => 0,
            'active' => '1',
            'drawAuto' => '1',
            'winners' => '3',
            'substitutes' => '0',
            'title' => 'Super Quiz',
            'identifier' => 'super-quiz',
            'publicationDate' => $now->format('d/m/Y'),
            'startDate' => $now->format('d/m/Y'),
            'endDate' => $date->format('d/m/Y'),
            'closeDate' => $date->format('d/m/Y'),
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
        $quiz = new QuizEntity();
        $this->form->setInputFilter($quiz->getInputFilter());

        $this->form->bind($quiz);
        $this->form->setData($this->quizData);
        $this->assertTrue($this->form->isValid());
    }

    /**
     * Test pour vérifier si le formulaire est valide si le champ VictoryConditions est vide (car uniquement pour pronostique)
     */
    public function testCanInsertNewRecordWithVictoryConditionsNull()
    {
        $this->quizData['victoryConditions'] = null;

        $quiz = new QuizEntity();
        $this->form->setInputFilter($quiz->getInputFilter());
        $this->form->bind($quiz);
        $this->form->setData($this->quizData);
        $this->assertTrue($this->form->isValid());
    }

    /**
     * Test pour vérifier si le formulaire n'est pas valide dans le cas où il n'y a pas de winners
     */
    public function testCannotInsertNewRecordWithNoWinners()
    {
        $this->quizData['winners'] = '';

        $quiz = new QuizEntity();
        $this->form->setInputFilter($quiz->getInputFilter());
        $this->form->bind($quiz);
        $this->form->setData($this->quizData);
        $this->assertFalse($this->form->isValid());
        $this->assertEquals(1, count($this->form->getMessages()));
    }

    /**
     * Test pour vérifier si le formulaire n'est pas valide ans le cas où il n'y a pas de substitutes
     */
    public function testCannotInsertNewRecordWithNoSubstitutes()
    {
        $this->quizData['substitutes'] = '';

        $quiz = new QuizEntity();
        $this->form->setInputFilter($quiz->getInputFilter());

        $this->form->bind($quiz);
        $this->form->setData($this->quizData);
        $this->assertFalse($this->form->isValid());
        $this->assertEquals(2, count($this->form->getMessages()));
    }

    public function getForm()
    {
        if (null === $this->form) {
            $this->form = $this->sm->get('playgroundgame_quiz_form');
        }

        return $this->form;
    }
}