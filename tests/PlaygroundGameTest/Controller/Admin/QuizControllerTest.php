<?php

namespace PlaygroundGameTest\Controller\Admin;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use \PlaygroundGame\Entity\Game as GameEntity;

class QuizControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../TestConfig.php'
        );

        parent::setUp();
    }

    public function testAddQuestionAction()
    {
        /*

		La vue n'est pas mockable


        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminGameService = $this->getMockBuilder('PlaygroundGame\Service\Game')
            ->setMethods(array('getGameMapper'))
            ->disableOriginalConstructor()
            ->getMock();

        $serviceManager->setService('playgroundgame_game_service', $adminGameService);

        $adminGameMapper = $this->getMockBuilder('PlaygroundGame\Mapper\Game')
            ->setMethods(array('findById'))
            ->disableOriginalConstructor()
            ->getMock();

        $adminGameService->expects($this->any())
            ->method('getGameMapper')
            ->will($this->returnValue($adminGameMapper));

        $adminFormQuiz = $this->getMockBuilder('PlaygroundGame\Form\Admin\Quiz')
            ->setMethods(array('get', 'setAttribute', 'bind', 'prepare'))
            ->disableOriginalConstructor()
            ->getMock();

		$serviceManager->setService('playgroundgame_quizquestion_form', $adminFormQuiz);

        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);

        $adminFormQuiz->expects($this->any())
            ->method('get')
            ->will($this->returnValue($adminFormQuiz));

        $adminFormQuiz->expects($this->any())
            ->method('setAttribute')
            ->will($this->returnValue(null));
       
        $adminFormQuiz->expects($this->any())
            ->method('bind')
            ->will($this->returnValue(null));

        $this->dispatch('/admin/game/quiz-question-add/1');
        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame_admin_quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('addQuestion');
        //$this->assertMatchedRouteName('frontend/lottery');*/
    }

    public function testRemoveQuestionAction() 
    {
    	$serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminQuizService = $this->getMockBuilder('PlaygroundGame\Service\Quiz')
            ->setMethods(array('getQuizQuestionMapper'))
            ->disableOriginalConstructor()
            ->getMock();

        $serviceManager->setService('playgroundgame_quiz_service', $adminQuizService);

        $adminQuizQuestionMapper = $this->getMockBuilder('PlaygroundGame\Mapper\QuizQuestion')
            ->setMethods(array('findById', 'remove'))
            ->disableOriginalConstructor()
            ->getMock();

        $adminQuizService->expects($this->any())
            ->method('getQuizQuestionMapper')
            ->will($this->returnValue($adminQuizQuestionMapper));

        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);
        $game->setId(1);
        $question = new \PlaygroundGame\Entity\QuizQuestion();
        $question->setQuiz($game);

        $adminQuizQuestionMapper->expects($this->any())
            ->method('findById')
            ->will($this->returnValue($question));

        $adminQuizQuestionMapper->expects($this->any())
            ->method('remove')
            ->will($this->returnValue('true'));

        $this->dispatch('/admin/game/quiz-question-remove/1');
        $this->assertModuleName('playgroundgame');
        $this->assertControllerName('playgroundgame_admin_quiz');
        $this->assertControllerClass('QuizController');
        $this->assertActionName('removeQuestion');
    }


}