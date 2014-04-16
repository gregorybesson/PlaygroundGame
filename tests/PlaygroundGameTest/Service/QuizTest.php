<?php

namespace PlaygroundGameTest\Service;

use PlaygroundGame\Entity\Quiz as QuizEntity;
use PlaygroundGameTest\Bootstrap;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class QuizTest extends AbstractHttpControllerTestCase
{
	
	protected $traceError = true;
    protected $sm = null;
    protected $data;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../TestConfig.php'
        );

        $this->sm = $this->getApplicationServiceLocator();
        $this->sm->setAllowOverride(true);
        parent::setUp();

        $this->dataTrue = array(
            'submit' => '', 
            'quiz_id' => '3', 
            'prediction' => '0', 
            'type' => '0', 
            'question' => '<p>Question ?</p>',
            'hint' => '', 
            'timer' => '', 
            'timer_duration' => '', 
            'position' => '0', 
            'video' => '', 
            'search_sound' => '', 
            'audio' => '0', 
            'autoplay' => '', 
            'answers' => array (
                0 => array (
                    'id' => '', 
                    'answer' => '<p>Reponse !</p>',
                    'correct' => '1', 
                    'position' => '0', 
                    'explanation' => ''
                )
            ),
            'upload_image' => array(
                'name' => '', 
                'type' => '', 
                'tmp_name' => '', 
                'error' => 4,
                'size' => 0
            ),
            'prizes' => array ()
        );
        $this->dataFalse = array(
            'submit' => '', 
            'quiz_id' => '3', 
            'prediction' => '0', 
            'type' => '0', 
            'question' => '<p>Question ?</p>',
            'hint' => '', 
            'timer' => '', 
            'timer_duration' => '', 
            'position' => '0', 
            'video' => '', 
            'search_sound' => '', 
            'audio' => '0', 
            'autoplay' => '', 
            'answers' => array (
                0 => array (
                    'id' => '', 
                    'answer' => '<p>Reponse !</p>',
                    'correct' => '0', 
                    'position' => '0', 
                    'explanation' => ''
                )
            ),
            'upload_image' => array(
                'name' => '', 
                'type' => '', 
                'tmp_name' => '', 
                'error' => 4,
                'size' => 0
            ),
            'prizes' => array ()
        );

    }

	public function testCreateQuestionTrue() {
		$quiz = new QuizEntity();
		$quiz->setTitle("title");
        $startDate = new \DateTime("now");
		$quiz->setStartDate($startDate);

        $gameMapper = $this->getMockBuilder('PlaygroundGame\Mapper\Game')
        ->disableOriginalConstructor()
        ->getMock();
        $gameMapper->expects($this->any())
        ->method('findById')
        ->will($this->returnValue($quiz));

        $quizMapper = $this->getMockBuilder('PlaygroundGame\Mapper\Quiz')
        ->disableOriginalConstructor()
        ->getMock();
        $quizMapper->expects($this->any())
        ->method('update')
        ->will($this->returnValue($quiz));

        $quizquestionMapper = $this->getMockBuilder('PlaygroundGame\Mapper\QuizQuestion')
        ->disableOriginalConstructor()
        ->getMock();
        $quizquestionMapper->expects($this->any())
        ->method('insert')
        ->will($this->returnValue(true));
        $quizquestionMapper->expects($this->any())
        ->method('update')
        ->will($this->returnValue(true));

        $this->getServiceManager()->setService('playgroundgame_game_mapper', $gameMapper);
        $this->getServiceManager()->setService('playgroundgame_quiz_mapper', $quizMapper);
        $this->getServiceManager()->setService('playgroundgame_quizquestion_mapper', $quizquestionMapper);
        $qs = new \PlaygroundGame\Service\Quiz();
        $qs->setServiceManager($this->getServiceManager());

        $this->assertInstanceOf('\PlaygroundGame\Entity\QuizQuestion', $qs->createQuestion($this->dataTrue));
	}

    public function testCreateQuestionFalse() {
        $quiz = new QuizEntity();
        $quiz->setTitle("title");
        $startDate = new \DateTime("now");
        $quiz->setStartDate($startDate);

        $gameMapper = $this->getMockBuilder('PlaygroundGame\Mapper\Game')
        ->disableOriginalConstructor()
        ->getMock();
        $gameMapper->expects($this->any())
        ->method('findById')
        ->will($this->returnValue($quiz));

        $this->getServiceManager()->setService('playgroundgame_game_mapper', $gameMapper);
        $qs = new \PlaygroundGame\Service\Quiz();
        $qs->setServiceManager($this->getServiceManager());

        $this->assertFalse($qs->createQuestion($this->dataFalse));
    }

	public function getServiceManager()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        return $this->sm;
    }

    public function tearDown()
    {
        $this->sm = null;
        unset($this->sm);

        parent::tearDown();
    }
}