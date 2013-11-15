<?php
/**
 * dependency Core
 * @author gbesson
 *
 */
namespace PlaygroundGame;

use Zend\Session\Container;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Validator\AbstractValidator;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();

        $options = $serviceManager->get('playgroundcore_module_options');
        $locale = $options->getLocale();
        $translator = $serviceManager->get('translator');
        if (!empty($locale)) {
            //translator
            $translator->setLocale($locale);

            // plugins
            $translate = $serviceManager->get('viewhelpermanager')->get('translate');
            $translate->getTranslator()->setLocale($locale);
        }

        AbstractValidator::setDefaultTranslator($translator,'playgroundcore');

        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // If PlaygroundCms is installed, I can add my own dynareas to benefit from this feature
        $e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Application','getDynareas', array($this, 'updateDynareas'));

        // I can post cron tasks to be scheduled by the core cron service
        $e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Application','getCronjobs', array($this, 'addCronjob'));

        // If cron is called, the $e->getRequest()->getPost() produces an error so I protect it with
        // this test
        if ((get_class($e->getRequest()) == 'Zend\Console\Request')) {
            return;
        }

        // the Facebook Container is created and updated through PlaygroundCore which detects a call from Facebook
        // DEPRECATED : REPLACED BY channel route !
        /*$eventManager->attach("dispatch", function($e) {
        	$session = new Container('facebook');
        	if ($session->offsetExists('signed_request')){
        		$viewModel = $e->getViewModel()->setTemplate('layout/facebook');
        		$viewModel->facebooktemplate = true;
        	}
        });*/

    }

    /**
     * This method get the games and add them as Dynareas to PlaygroundCms so that blocks can be dynamically added to the games.
     *
     * @param  EventManager $e
     * @return array
     */
    public function updateDynareas($e)
    {
        $dynareas = $e->getParam('dynareas');
        //$dynareas = array_merge($dynareas, array('column_left' => array('title' => 'yeah', 'description' => 'bum rush it', 'location'=>'et hop')));

        $gameService = $e->getTarget()->getServiceManager()->get('playgroundgame_game_service');

        $games = $gameService->getActiveGames();

        foreach ($games as $game) {
           $array = array('game'.$game->getId() => array('title' => $game->getTitle(), 'description' => $game->getClassType(), 'location'=>'pages du jeu'));
           $dynareas = array_merge($dynareas, $array);
        }

        return $dynareas;
    }

    /**
     * This method get the cron config for this module an add them to the listener
     * TODO : déporter la def des cron dans la config.
     *
     * @param  EventManager $e
     * @return array
     */
    public function addCronjob($e)
    {
        $cronjobs = $e->getParam('cronjobs');

        $cronjobs['adfagame_email'] = array(
            'frequency' => '*/15 * * * *',
            'callback'  => '\PlaygroundGame\Service\Game::cronMail',
            'args'      => array('bar', 'baz'),
        );

        // tous les jours à 5:00 AM
        $cronjobs['adfagame_instantwin_email'] = array(
                'frequency' => '* 5 * * *',
                'callback'  => '\PlaygroundGame\Service\Cron::instantWinEmail',
                'args'      => array(),
        );

        return $cronjobs;
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * @return array
     */
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'playgroundPrizeCategory' => function($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\PrizeCategory;
                    $viewHelper->setPrizeCategoryService($locator->get('playgroundgame_prizecategory_service'));

                    return $viewHelper;
                },
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
                // An alias for linking a partner service with PlaygroundGame without adherence
                'playgroundgame_partner_service' => 'playgroundpartnership_partner_service',
                'playgroundgame_message'         => 'playgroundcore_message',

            ),

            'invokables' => array(
                'playgroundgame_game_service'              => 'PlaygroundGame\Service\Game',
                'playgroundgame_lottery_service'           => 'PlaygroundGame\Service\Lottery',
                'playgroundgame_postvote_service'          => 'PlaygroundGame\Service\PostVote',
                'playgroundgame_quiz_service'              => 'PlaygroundGame\Service\Quiz',
            	'playgroundgame_treasurehunt_service'      => 'PlaygroundGame\Service\TreasureHunt',
                'playgroundgame_instantwin_service'        => 'PlaygroundGame\Service\InstantWin',
            	'playgroundgame_prize_service'     		   => 'PlaygroundGame\Service\Prize',
            	'playgroundgame_prizecategory_service'     => 'PlaygroundGame\Service\PrizeCategory',
                'playgroundgame_prizecategoryuser_service' => 'PlaygroundGame\Service\PrizeCategoryUser',
                'playgroundgame_mission_service'           => 'PlaygroundGame\Service\Mission',
                'playgroundgame_mission_game_service'      => 'PlaygroundGame\Service\MissionGame',
            ),

            'factories' => array(
                'playgroundgame_module_options' => function ($sm) {
                        $config = $sm->get('Configuration');

                        return new Options\ModuleOptions(isset($config['playgroundgame']) ? $config['playgroundgame'] : array()
                    );
                },

                'playgroundgame_game_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\Game(
                            $sm->get('doctrine.entitymanager.orm_default'),
                            $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_mission_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\Mission(
                            $sm->get('doctrine.entitymanager.orm_default'),
                            $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_mission_game_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\MissionGame(
                            $sm->get('doctrine.entitymanager.orm_default'),
                            $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_mission_game_condition_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\MissionGameCondition(
                            $sm->get('doctrine.entitymanager.orm_default'),
                            $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_lottery_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\Lottery(
                            $sm->get('doctrine.entitymanager.orm_default'),
                            $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_instantwin_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\InstantWin(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_instantwinoccurrence_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\InstantWinOccurrence(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_quiz_mapper' => function ($sm) {
                $mapper = new \PlaygroundGame\Mapper\Quiz(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgroundgame_module_options')
                );

                return $mapper;
                },

                'playgroundgame_quizquestion_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\QuizQuestion(
                            $sm->get('doctrine.entitymanager.orm_default'),
                            $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_quizanswer_mapper' => function ($sm) {
                $mapper = new \PlaygroundGame\Mapper\QuizAnswer(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgroundgame_module_options')
                );

                return $mapper;
                },

                'playgroundgame_quizreply_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\QuizReply(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_quizreplyanswer_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\QuizReplyAnswer(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },


                'playgroundgame_entry_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\Entry(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_postvote_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\PostVote(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_postvoteform_mapper' => function ($sm) {
                $mapper = new \PlaygroundGame\Mapper\PostVoteForm(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgroundgame_module_options')
                );

                return $mapper;
                },

                'playgroundgame_postvotepost_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\PostVotePost(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_postvotepostelement_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\PostVotePostElement(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_postvotevote_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\PostVoteVote(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_prize_mapper' => function ($sm) {
                	$mapper = new \PlaygroundGame\Mapper\Prize(
                		$sm->get('doctrine.entitymanager.orm_default'),
                		$sm->get('playgroundgame_module_options')
                	);

                	return $mapper;
                },

                'playgroundgame_prizecategory_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\PrizeCategory(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_prizecategoryuser_mapper' => function ($sm) {
                    $mapper = new \PlaygroundGame\Mapper\PrizeCategoryUser(
                        $sm->get('doctrine.entitymanager.orm_default'),
                        $sm->get('playgroundgame_module_options')
                    );

                    return $mapper;
                },

                'playgroundgame_treasurehunt_mapper' => function ($sm) {
                	$mapper = new \PlaygroundGame\Mapper\TreasureHunt(
                			$sm->get('doctrine.entitymanager.orm_default'),
                			$sm->get('playgroundgame_module_options')
                	);

                	return $mapper;
                },

                'playgroundgame_treasurehuntPuzzle_mapper' => function ($sm) {
                	$mapper = new \PlaygroundGame\Mapper\TreasureHuntPuzzle(
                			$sm->get('doctrine.entitymanager.orm_default'),
                			$sm->get('playgroundgame_module_options')
                	);

                	return $mapper;
                },

                'playgroundgame_game_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\Game(null, $sm, $translator);
                    $game = new Entity\Game();
                    $form->setInputFilter($game->getInputFilter());

                    return $form;
                },

                'playgroundgame_mission_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\Mission(null, $sm, $translator);
                    $mission = new Entity\Mission();
                    $form->setInputFilter($mission->getInputFilter());
                    return $form;
                },

                'playgroundgame_mission_game_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\MissionGame(null, $sm, $translator);
                    $missionGame = new Entity\MissionGame();
                    $form->setInputFilter($missionGame->getInputFilter());
                    return $form;
                },

                'playgroundgame_lottery_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\Lottery(null, $sm, $translator);
                    $lottery = new Entity\Lottery();
                    $form->setInputFilter($lottery->getInputFilter());

                    return $form;
                },

                'playgroundgame_quiz_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\Quiz(null, $sm, $translator);
                    $quiz = new Entity\Quiz();
                    $form->setInputFilter($quiz->getInputFilter());

                    return $form;
                },

                'playgroundgame_instantwin_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\InstantWin(null, $sm, $translator);
                    $instantwin = new Entity\InstantWin();
                    $form->setInputFilter($instantwin->getInputFilter());

                    return $form;
                },

                'playgroundgame_quizquestion_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\QuizQuestion(null, $sm, $translator);
                    $quizQuestion = new Entity\QuizQuestion();
                    $form->setInputFilter($quizQuestion->getInputFilter());

                    return $form;
                },

                'playgroundgame_instantwinoccurrence_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\InstantWinOccurrence(null, $sm, $translator);
                    $instantwinOccurrence = new Entity\InstantWinOccurrence();
                    $form->setInputFilter($instantwinOccurrence->getInputFilter());

                    return $form;
                },

                'playgroundgame_instantwinoccurrenceimport_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\InstantWinOccurrenceImport(null, $sm, $translator);
                    return $form;
                },

                'playgroundgame_instantwinoccurrencecode_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Frontend\InstantWinOccurrenceCode(null, $translator);

                    return $form;
                },

                'playgroundgame_postvote_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\PostVote(null, $sm, $translator);
                    $postVote = new Entity\PostVote();
                    $form->setInputFilter($postVote->getInputFilter());

                    return $form;
                },

                'playgroundgame_prizecategory_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\PrizeCategory(null, $sm, $translator);
                    $prizeCategory = new Entity\PrizeCategory();
                    $form->setInputFilter($prizeCategory->getInputFilter());

                    return $form;
                },

                'playgroundgame_prizecategoryuser_form' => function($sm) {
                $translator = $sm->get('translator');
                $form = new Form\Frontend\PrizeCategoryUser(null, $sm, $translator);

                return $form;
                },

                'playgroundgame_sharemail_form' => function($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Frontend\ShareMail(null, $sm, $translator);
                    $form->setInputFilter(new Form\Frontend\ShareMailFilter());

                    return $form;
                },

                'playgroundgame_treasurehunt_form' => function($sm) {
                	$translator = $sm->get('translator');
                	$form = new Form\Admin\TreasureHunt(null, $sm, $translator);
                	$treasurehunt = new Entity\TreasureHunt();
                	$form->setInputFilter($treasurehunt->getInputFilter());

                	return $form;
                },

                'playgroundgame_treasurehuntpuzzle_form' => function($sm) {
                	$translator = $sm->get('translator');
                	$form = new Form\Admin\TreasureHuntPuzzle(null, $sm, $translator);
                	$treasurehuntPuzzle = new Entity\TreasureHuntPuzzle();
                	$form->setInputFilter($treasurehuntPuzzle->getInputFilter());

                	return $form;
                },
            ),
        );
    }
}

?>
