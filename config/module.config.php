<?php
return array(
    'doctrine' => array(
        'driver' => array(
            'playgroundgame_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => __DIR__ . '/../src/PlaygroundGame/Entity'
            ),

            'orm_default' => array(
                'drivers' => array(
                    'PlaygroundGame\Entity' => 'playgroundgame_entity'
                )
            )
        )
    ),
    'bjyauthorize' => array(
        'resource_providers' => array(
            'BjyAuthorize\Provider\Resource\Config' => array(
                'game' => array(),
            ),
        ),
    
        'rule_providers' => array(
            'BjyAuthorize\Provider\Rule\Config' => array(
                'allow' => array(
                    array(array('admin'), 'game', array('list','add','edit','delete')),
                    array(array('admin'), 'game', array('prizecategory_list','prizecategory_add','prizecategory_edit','prizecategory_delete')),
                ),
            ),
        ),
    
        'guards' => array(
            'BjyAuthorize\Guard\Controller' => array(
                array('controller' => Playgroundgame\Controller\Frontend\Home::class,                     'roles' => array('guest', 'user')),
                array('controller' => PlaygroundGame\Controller\Frontend\Game::class,                'roles' => array('guest', 'user')),
                array('controller' => PlaygroundGame\Controller\Frontend\Lottery::class,             'roles' => array('guest', 'user')),
                array('controller' => PlaygroundGame\Controller\Frontend\Quiz::class,                'roles' => array('guest', 'user')),
                array('controller' => PlaygroundGame\Controller\Frontend\PostVote::class,            'roles' => array('guest', 'user')),
                array('controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,          'roles' => array('guest', 'user')),
                array('controller' => PlaygroundGame\Controller\Frontend\PrizeCategory::class,       'roles' => array('guest', 'user')),
                array('controller' => PlaygroundGame\Controller\Frontend\Mission::class,             'roles' => array('guest', 'user')),
                array('controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,         'roles' => array('guest', 'user')),
    
                // Admin area
                array('controller' => Playgroundgame\Controller\Admin\Game::class,          'roles' => array('admin')),
                array('controller' => Playgroundgame\Controller\Admin\Lottery::class,       'roles' => array('admin')),
                array('controller' => Playgroundgame\Controller\Admin\InstantWin::class,    'roles' => array('admin')),
                array('controller' => Playgroundgame\Controller\Admin\Quiz::class,          'roles' => array('admin')),
                array('controller' => Playgroundgame\Controller\Admin\PostVote::class,      'roles' => array('admin')),
                array('controller' => Playgroundgame\Controller\Admin\Mission::class,       'roles' => array('admin')),
                array('controller' => Playgroundgame\Controller\Admin\TradingCard::class,   'roles' => array('admin')),
                array('controller' => 'playgroundgame_admin_prizecategory', 'roles' => array('admin')),
            ),
        ),
    ),
    'assetic_configuration' => array(
        'modules' => array(
            'lib' => array(
                'collections' => array(
                    /*'admin_areapicker_css' => array(
                        'assets' => array(
                            __DIR__ . '/../view/lib/css/playground/areapicker/style.min.css'
                        ),
                        'filters' => array(
                            'CssRewriteFilter' => array(
                                'name' => 'Assetic\Filter\CssRewriteFilter'
                            )
                        ),
                        'options' => array(
                            'output' => 'zfcadmin/css/admin_areapicker.css'
                        )
                    ),
                    /*'head_admin_areapicker_js' => array(
                        'assets' => array(
                            __DIR__ . '/../view/lib/js/playground/jquery.min.js',
                            __DIR__ . '/../view/lib/js/playground/areapicker/app.js',
                            __DIR__ . '/../view/lib/js/playground/areapicker/config.js',
                            __DIR__ . '/../view/lib/js/playground/areapicker/selection.js',
                            __DIR__ . '/../view/lib/js/easyxdm/easyxdm.min.js'
                        ),
                        'filters' => array(),
                        'options' => array(
                            'output' => 'zfcadmin/js/head_admin_areapicker.js'
                        )
                    ),*/
                    'head_areapicker_cors_js' => array(
                        'assets' => array(
                            __DIR__ . '/../view/lib/js/playground/areapicker/cors.js'
                        ),
                        'filters' => array(),
                        'options' => array(
                            'move_raw' => true,
                            'output' => 'lib/js/playground/areapicker'
                        )
                    ),
                    'head_admin_deezer_js' => array(
                        'assets' => array(
                            __DIR__ . '/../view/lib/js/deezer/dz.min.js'
                        ),
                        'filters' => array(),
                        'options' => array(
                            'output' => 'zfcadmin/js/head_deezer.js'
                        )
                    ),
                    'admin_deezer_js' => array(
                        'assets' => array(
                            __DIR__ . '/../view/lib/js/deezer/api-deezer.js'
                        ),
                        'filters' => array(),
                        'options' => array(
                            'output' => 'zfcadmin/js/deezer.js'
                        )
                    ),
                    'head_admin_fabric_js' => array(
                        'assets' => array(
                            __DIR__ . '/../view/lib/js/fabricjs/fabric.js',
                            __DIR__ . '/../view/lib/js/fabricjs/fabric_gestures.js',
                            __DIR__ . '/../view/lib/js/fabricjs/FabricHelper.js',

                        ),
                        'filters' => array(),
                        'options' => array(
                            'move_raw' => true,
                            'output' => 'lib/js/fabricjs'
                        )
                    ),
                    'head_frontend_deezer_js' => array(
                        'assets' => array(
                            __DIR__ . '/../view/lib/js/deezer/dz.min.js'
                        ),
                        'filters' => array(),
                        'options' => array(
                            'output' => 'frontend/js/head_deezer.js'
                        )
                    ),
                    'frontend_deezer_js' => array(
                        'assets' => array(
                            __DIR__ . '/../view/lib/js/deezer/api-deezer.js'
                        ),
                        'filters' => array(),
                        'options' => array(
                            'output' => 'frontend/js/deezer.js'
                        )
                    )
                )
            )
        ),

        'routes' => array(
            'admin/playgroundgame.*' => array(
                '@head_admin_deezer_js',
                '@admin_deezer_js',
            ),
            'frontend/quiz.*' => array(
                '@head_frontend_deezer_js',
                '@frontend_deezer_js',
            )
        )
    ),
    'core_layout' => array(
        'frontend' => array(
            'modules' => array(
                Playgroundgame\Controller\Frontend\Home::class => array(
                    'layout' => 'layout/game-2columns-right.phtml',
                    'controllers' => array(
                        PlaygroundGame\Controller\Frontend\Lottery::class => array(
                            'children_views' => array(
                                'col_right' => 'playground-game/lottery/col-lottery.phtml'
                            )
                        ),
                        PlaygroundGame\Controller\Frontend\Quiz::class => array(
                            // 'layout'
                            // =>
                            // 'layout/game-2columns-right.phtml',
                            'children_views' => array(
                                'col_right' => 'playground-game/quiz/col-quiz.phtml'
                            )
                        ),
                        PlaygroundGame\Controller\Frontend\InstantWin::class => array(
                            'children_views' => array(
                                'col_right' => 'playground-game/instant-win/col-instantwin.phtml'
                            )
                        ),
                        PlaygroundGame\Controller\Frontend\PostVote::class => array(
                            'children_views' => array(
                                'col_right' => 'playground-game/post-vote/col-postvote.phtml'
                            )
                        ),
                        PlaygroundGame\Controller\Frontend\PrizeCategory::class => array(
                            'actions' => array(
                                'index' => array(
                                    'children_views' => array(
                                        'col_right' => 'application/common/column_right.phtml'
                                    )
                                )
                            )
                        ),
                        Playgroundgame\Controller\Frontend\Home::class => array(
                            'layout' => 'layout/2columns-right',
                        )
                    )
                )
            )
        )
    ),

    'view_manager' => array(
        'template_map' => array(),
        'template_path_stack' => array(
            __DIR__ . '/../view/admin',
            __DIR__ . '/../view/frontend'
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),

    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type' => 'phpArray',
                'base_dir' => __DIR__ . '/../../../../language',
                'pattern' => '%s.php',
                'text_domain' => 'playgroundgame'
            ),
            array(
                'type' => 'phpArray',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.php',
                'text_domain' => 'playgroundgame'
            )
        )
    ),

    'controllers' => array(
        'factories' => array(
            Playgroundgame\Controller\Frontend\Home::class => PlaygroundGame\Service\Factory\FrontendHomeControllerFactory::class,
            PlaygroundGame\Controller\Frontend\Game::class => PlaygroundGame\Service\Factory\FrontendGameControllerFactory::class,
            Playgroundgame\Controller\Frontend\Lottery::class => PlaygroundGame\Service\Factory\FrontendLotteryControllerFactory::class,
            PlaygroundGame\Controller\Frontend\Quiz::class => PlaygroundGame\Service\Factory\FrontendQuizControllerFactory::class,
            PlaygroundGame\Controller\Frontend\InstantWin::class => PlaygroundGame\Service\Factory\FrontendInstantWinControllerFactory::class,
            PlaygroundGame\Controller\Frontend\PostVote::class => PlaygroundGame\Service\Factory\FrontendPostVoteControllerFactory::class,
            PlaygroundGame\Controller\Frontend\Mission::class => PlaygroundGame\Service\Factory\FrontendMissionControllerFactory::class,
            PlaygroundGame\Controller\Frontend\TradingCard::class => PlaygroundGame\Service\Factory\FrontendTradingCardControllerFactory::class,
            PlaygroundGame\Controller\Frontend\PrizeCategory::class => PlaygroundGame\Service\Factory\FrontendPrizeCategoryControllerFactory::class,

            PlaygroundGame\Controller\Admin\Game::class => PlaygroundGame\Service\Factory\AdminGameControllerFactory::class,
            PlaygroundGame\Controller\Admin\Lottery::class => PlaygroundGame\Service\Factory\AdminLotteryControllerFactory::class,
            PlaygroundGame\Controller\Admin\InstantWin::class => PlaygroundGame\Service\Factory\AdminInstantWinControllerFactory::class,
            PlaygroundGame\Controller\Admin\PostVote::class => PlaygroundGame\Service\Factory\AdminPostVoteControllerFactory::class,
            PlaygroundGame\Controller\Admin\Quiz::class => PlaygroundGame\Service\Factory\AdminQuizControllerFactory::class,
            PlaygroundGame\Controller\Admin\Mission::class => PlaygroundGame\Service\Factory\AdminMissionControllerFactory::class,
            PlaygroundGame\Controller\Admin\TradingCard::class => PlaygroundGame\Service\Factory\AdminTradingCardControllerFactory::class,
            PlaygroundGame\Controller\Admin\PrizeCategory::class => PlaygroundGame\Service\Factory\AdminPrizeCategoryControllerFactory::class,
        ),
    ),

    'service_manager' => array(
        'aliases' => array(
            'playgroundgame_partner_service' => 'playgroundpartnership_partner_service',
            'playgroundgame_message'         => 'playgroundcore_message',
        ),
        'factories' => array(
            'playgroundgame_game_service'              => 'PlaygroundGame\Service\Factory\GameFactory',
            'playgroundgame_lottery_service'           => 'PlaygroundGame\Service\Factory\LotteryFactory',
            'playgroundgame_postvote_service'          => 'PlaygroundGame\Service\Factory\PostVoteFactory',
            'playgroundgame_quiz_service'              => 'PlaygroundGame\Service\Factory\QuizFactory',
            'playgroundgame_instantwin_service'        => 'PlaygroundGame\Service\Factory\InstantWinFactory',
            'playgroundgame_mission_service'           => 'PlaygroundGame\Service\Factory\MissionFactory',
            'playgroundgame_mission_game_service'      => 'PlaygroundGame\Service\Factory\MissionGameFactory',
            'playgroundgame_tradingcard_service'       => 'PlaygroundGame\Service\Factory\TradingCardFactory',
            'playgroundgame_prize_service'             => 'PlaygroundGame\Service\Factory\PrizeFactory',
            'playgroundgame_prizecategory_service'     => 'PlaygroundGame\Service\Factory\PrizeCategoryFactory',
            'playgroundgame_prizecategoryuser_service' => 'PlaygroundGame\Service\Factory\PrizeCategoryUserFactory',
        ),
    ),

    'router' => array(
        'routes' => array(
            'frontend' => array(
                'options' => array(
                    'defaults' => array(
                        'controller' => Playgroundgame\Controller\Frontend\Home::class,
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'pagination' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[:p]',
                            'defaults' => array(
                                'controller' => Playgroundgame\Controller\Frontend\Home::class,
                                'action'     => 'index',
                            ),
                            'constraints' => array('p' => '[0-9]*'),
                        ),
                    ),
                    'gameslist' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => 'gameslist',
                            'defaults' => array(
                                'controller' => PlaygroundGame\Controller\Frontend\Game::class,
                                'action' => 'gameslist'
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'pagination' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '[/:p]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Game::class,
                                        'action' => 'gameslist'
                                    ),
                                    'constraints' => array(
                                        'p' => '[0-9]*'
                                    )
                                )
                            )
                        )
                    ),
                    'tradingcard' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'trading-card[/:id]',
                            'defaults' => array(
                                'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                'action' => 'home'
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'index' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/index',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'index'
                                    )
                                )
                            ),
                            'play' => array(
                                'type' => 'Segment', 
                                'options' => array(
                                    'route' => '/jouer',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'play'
                                    )
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'ajaxupload' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/ajaxupload',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                                'action' => 'ajaxupload'
                                            )
                                        )
                                    ),
                                    'ajaxdelete' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/ajaxdelete',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                                'action' => 'ajaxdelete'
                                            )
                                        )
                                    )
                                )
                            ),
                            'ajaxforgotpassword' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/ajax-mot-passe-oublie',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action'     => 'ajaxforgot',
                                    ),
                                ),
                            ),
                            'resetpassword' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/reset-password/:userId/:token',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action'     => 'userreset',
                                    ),
                                    'constraints' => array(
                                        'userId'  => '[0-9]+',
                                        'token' => '[A-F0-9]+',
                                    ),
                                ),
                            ),
                            'login' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/connexion',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'login'
                                    )
                                )
                            ),
                            'logout' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/logout',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action'     => 'logout',
                                    ),
                                ),
                            ),
                            'user-register' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/inscription',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'userregister'
                                    )
                                )
                            ),
                            'verification' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/verification',
		                            'defaults' => array(
		                                'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
		                                'action'     => 'check-token',
		                            ),
		                        ),
		                    ),
                            'optin' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/optin',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'optin'
                                    )
                                )
                            ),
                            'result' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/resultat',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'result'
                                    )
                                )
                            ),
                            'register' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/register',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'register'
                                    )
                                )
                            ),
                            'profile' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/profil',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'userProfile'
                                    )
                                )
                            ),
                            'autocomplete' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => 'autocomplete',
                                ),
                                'child_routes' => array(
                                    'user' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/user/:field/:value',
                                            'defaults' => array(
                                                'controller' => 'playgrounduser_user',
                                                'action'     => 'autoCompleteUser',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'fbshare' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbshare',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'fbshare'
                                    )
                                )
                            ),
                            'fbrequest' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbrequest',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'fbrequest'
                                    )
                                )
                            ),
                            'tweet' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/tweet',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'tweet'
                                    )
                                )
                            ),
                            'google' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/google',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'google'
                                    )
                                )
                            ),
                            'share' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/partager',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'share'
                                    )
                                )
                            ),
                            'invite-to-team' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/rejoins-ma-team',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'inviteToTeam',
                                    )
                                )
                            ),
                            'bounce' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/essayez-aussi',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'bounce'
                                    )
                                )
                            ),
                            'terms' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/reglement',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'terms'
                                    )
                                )
                            ),
                            'conditions' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/mentions-legales',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'conditions'
                                    )
                                )
                            ),
                            'fangate' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fangate',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'fangate'
                                    )
                                )
                            ),
                            'prizes' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/lots',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'prizes'
                                    )
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'prize' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/:prize',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                                'action' => 'prize'
                                            )
                                        )
                                    )
                                )
                            ),
                            'leaderboard' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/leaderboard[/:filter][/:p]',
                                    'constraints' => array(
                                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action'     => 'leaderboard'
                                    ),
                                ),
                            ),
                            'cms' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route'    => 'page',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action'     => 'cmsPage',
                                    ),
                                ),
                                'child_routes' =>array(
                                    'detail' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/:pid',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                                'action'     => 'cmsPage',
                                            ),
                                        ),
                                    ),
                                    'list' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/liste[/:p]',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                                'action'     => 'cmsList',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'other-routes' => array(
                                'type' => '\Zend\Mvc\Router\Http\Regex',
                                'priority' => -1000,
                                'options' => array(
                                    'regex' => '.*',
                                    'spec' => '%url%',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                                        'action' => 'not-found'
                                    )
                                )
                            )
                        )
                    ),
                    'mission' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'mission[/:id]',
                            'defaults' => array(
                                'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                'action' => 'home'
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'index' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/index',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'index'
                                    )
                                )
                            ),
                            'play' => array(
                                'type' => 'Segment', 
                                'options' => array(
                                    'route' => '/jouer[/:gameId]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'play'
                                    )
                                )
                            ),
                            'ajaxforgotpassword' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/ajax-mot-passe-oublie[/:gameId]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action'     => 'ajaxforgot',
                                    ),
                                ),
                            ),
                            'resetpassword' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/reset-password/:userId/:token',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action'     => 'userreset',
                                    ),
                                    'constraints' => array(
                                        'userId'  => '[0-9]+',
                                        'token' => '[A-F0-9]+',
                                    ),
                                ),
                            ),
                            'login' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/connexion[/:gameId]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'login'
                                    )
                                )
                            ),
                            'logout' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/logout',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action'     => 'logout',
                                    ),
                                ),
                            ),
                            'user-register' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/inscription[/:gameId]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'userregister'
                                    )
                                )
                            ),
                            'verification' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/verification',
		                            'defaults' => array(
		                                'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
		                                'action'     => 'check-token',
		                            ),
		                        ),
		                    ),
                            'optin' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/optin[/:gameId]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'optin'
                                    )
                                )
                            ),
                            'result' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/resultat[/:gameId]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'result'
                                    )
                                )
                            ),
                            'register' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/register',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'register'
                                    )
                                )
                            ),
                            'profile' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/profil',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'userProfile'
                                    )
                                )
                            ),
                            'autocomplete' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => 'autocomplete',
                                ),
                                'child_routes' => array(
                                    'user' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/user/:field/:value',
                                            'defaults' => array(
                                                'controller' => 'playgrounduser_user',
                                                'action'     => 'autoCompleteUser',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'fbshare' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbshare',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'fbshare'
                                    )
                                )
                            ),
                            'fbrequest' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbrequest',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'fbrequest'
                                    )
                                )
                            ),
                            'tweet' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/tweet',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'tweet'
                                    )
                                )
                            ),
                            'google' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/google',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'google'
                                    )
                                )
                            ),
                            'share' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/partager',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'share'
                                    )
                                )
                            ),
                            'invite-to-team' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/rejoins-ma-team',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'inviteToTeam',
                                    )
                                )
                            ),
                            'bounce' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/essayez-aussi',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'bounce'
                                    )
                                )
                            ),
                            'terms' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/reglement',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'terms'
                                    )
                                )
                            ),
                            'conditions' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/mentions-legales',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'conditions'
                                    )
                                )
                            ),
                            'fangate' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fangate',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'fangate'
                                    )
                                )
                            ),
                            'prizes' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/lots',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'prizes'
                                    )
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'prize' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/:prize',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                                'action' => 'prize'
                                            )
                                        )
                                    )
                                )
                            ),
                            'leaderboard' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/leaderboard[/:filter][/:p]',
                                    'constraints' => array(
                                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action'     => 'leaderboard'
                                    ),
                                ),
                            ),
                            'cms' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route'    => 'page',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action'     => 'cmsPage',
                                    ),
                                ),
                                'child_routes' =>array(
                                    'detail' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/:pid',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                                'action'     => 'cmsPage',
                                            ),
                                        ),
                                    ),
                                    'list' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/liste[/:p]',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                                'action'     => 'cmsList',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'other-routes' => array(
                                'type' => '\Zend\Mvc\Router\Http\Regex',
                                'priority' => -1000,
                                'options' => array(
                                    'regex' => '.*',
                                    'spec' => '%url%',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                                        'action' => 'not-found'
                                    )
                                )
                            )
                        )
                    ),
                    
                    'quiz' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'quiz[/:id]',
                            'defaults' => array(
                                'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                'action' => 'home'
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'index' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/index',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'index'
                                    )
                                )
                            ),
                            'ajaxforgotpassword' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/ajax-mot-passe-oublie',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action'     => 'ajaxforgot',
                                    ),
                                ),
                            ),
                            'resetpassword' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/reset-password/:userId/:token',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action'     => 'userreset',
                                    ),
                                    'constraints' => array(
                                        'userId'  => '[0-9]+',
                                        'token' => '[A-F0-9]+',
                                    ),
                                ),
                            ),
                            'login' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/connexion',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'login'
                                    )
                                )
                            ),
                            'user-register' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/inscription[/:socialnetwork]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'userregister'
                                    )
                                )
                            ),
                            'verification' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/verification',
		                            'defaults' => array(
		                                'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
		                                'action'     => 'check-token',
		                            ),
		                        ),
		                    ),
                            'optin' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/optin[/:gameId]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'optin'
                                    )
                                )
                            ),
                            'play' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/jouer',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'play'
                                    )
                                )
                            ),
                            'result' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/resultat',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'result'
                                    )
                                )
                            ),
                            'register' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/register',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'register'
                                    )
                                )
                            ),
                            'fbshare' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbshare',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'fbshare'
                                    )
                                )
                            ),
                            'fbrequest' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbrequest',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'fbrequest'
                                    )
                                )
                            ),
                            'tweet' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/tweet',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'tweet'
                                    )
                                )
                            ),
                            'google' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/google',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'google'
                                    )
                                )
                            ),
                            'bounce' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/essayez-aussi',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'bounce'
                                    )
                                )
                            ),
                            'terms' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/reglement',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'terms'
                                    )
                                )
                            ),
                            'conditions' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/mentions-legales',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'conditions'
                                    )
                                )
                            ),
                            'fangate' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fangate',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'fangate'
                                    )
                                )
                            ),
                            'prizes' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/lots',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'prizes'
                                    )
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'prize' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/:prize',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                                'action' => 'prize'
                                            )
                                        )
                                    )
                                )
                            ),
                            'share' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/partager',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'share'
                                    )
                                )
                            ),
                            'leaderboard' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/leaderboard[/:filter][/:p]',
                                    'constraints' => array(
                                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action'     => 'leaderboard'
                                    ),
                                ),
                            ),
                            'other-routes' => array(
                                'type' => '\Zend\Mvc\Router\Http\Regex',
                                'priority' => -1000,
                                'options' => array(
                                    'regex' => '.*',
                                    'spec' => '%url%',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                                        'action' => 'not-found'
                                    )
                                )
                            )
                        )
                    ),

                    'lottery' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'loterie[/:id]',
                            'defaults' => array(
                                'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                'action' => 'home'
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'index' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/index',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'index'
                                    )
                                )
                            ),
                            'ajaxforgotpassword' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/ajax-mot-passe-oublie',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action'     => 'ajaxforgot',
                                    ),
                                ),
                            ),
                            'resetpassword' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/reset-password/:userId/:token',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action'     => 'userreset',
                                    ),
                                    'constraints' => array(
                                        'userId'  => '[0-9]+',
                                        'token' => '[A-F0-9]+',
                                    ),
                                ),
                            ),
                            'login' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/connexion',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'login'
                                    )
                                )
                            ),
                            'user-register' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/inscription[/:socialnetwork]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'userregister'
                                    )
                                )
                            ),
                            'verification' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/verification',
		                            'defaults' => array(
		                                'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
		                                'action'     => 'check-token',
		                            ),
		                        ),
		                    ),
                            'optin' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/optin[/:gameId]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'optin'
                                    )
                                )
                            ),
                            'play' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/jouer',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'play'
                                    )
                                )
                            ),
                            'result' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/resultat',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'result'
                                    )
                                )
                            ),
                            'register' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/register',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'register'
                                    )
                                )
                            ),
                            'fbshare' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbshare',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'fbshare'
                                    )
                                )
                            ),
                            'fbrequest' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbrequest',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'fbrequest'
                                    )
                                )
                            ),
                            'tweet' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/tweet',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'tweet'
                                    )
                                )
                            ),
                            'google' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/google',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'google'
                                    )
                                )
                            ),
                            'bounce' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/essayez-aussi',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'bounce'
                                    )
                                )
                            ),
                            'terms' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/reglement',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'terms'
                                    )
                                )
                            ),
                            'conditions' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/mentions-legales',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'conditions'
                                    )
                                )
                            ),
                            'fangate' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fangate',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'fangate'
                                    )
                                )
                            ),
                            'prizes' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/lots',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'prizes'
                                    )
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'prize' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/:prize',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                                'action' => 'prize'
                                            )
                                        )
                                    )
                                )
                            ),
                            'share' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/partager',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'share'
                                    )
                                )
                            ),
                            'leaderboard' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/leaderboard[/:filter][/:p]',
                                    'constraints' => array(
                                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action'     => 'leaderboard'
                                    ),
                                ),
                            ),
                            'other-routes' => array(
                                'type' => '\Zend\Mvc\Router\Http\Regex',
                                'priority' => -1000,
                                'options' => array(
                                    'regex' => '.*',
                                    'spec' => '%url%',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                                        'action' => 'not-found'
                                    )
                                )
                            )
                        )
                    ),

                    'instantwin' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'instant-gagnant[/:id]',
                            'defaults' => array(
                                'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                'action' => 'home'
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'index' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/index',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'index'
                                    )
                                )
                            ),
                            'ajaxforgotpassword' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/ajax-mot-passe-oublie',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action'     => 'ajaxforgot',
                                    ),
                                ),
                            ),
                            'resetpassword' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/reset-password/:userId/:token',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action'     => 'userreset',
                                    ),
                                    'constraints' => array(
                                        'userId'  => '[0-9]+',
                                        'token' => '[A-F0-9]+',
                                    ),
                                ),
                            ),
                            'login' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/connexion',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'login'
                                    )
                                )
                            ),
                            'user-register' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/inscription[/:socialnetwork]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'userregister'
                                    )
                                )
                            ),
                            'verification' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/verification',
		                            'defaults' => array(
		                                'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
		                                'action'     => 'check-token',
		                            ),
		                        ),
		                    ),
                            'optin' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/optin[/:gameId]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'optin'
                                    )
                                )
                            ),
                            'play' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/jouer',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'play'
                                    )
                                )
                            ),
                            'result' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/resultat',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'result'
                                    )
                                )
                            ),
                            'register' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/register',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'register'
                                    )
                                )
                            ),
                            'fbshare' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbshare',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'fbshare'
                                    )
                                )
                            ),
                            'fbrequest' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbrequest',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'fbrequest'
                                    )
                                )
                            ),
                            'tweet' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/tweet',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'tweet'
                                    )
                                )
                            ),
                            'google' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/google',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'google'
                                    )
                                )
                            ),
                            'bounce' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/essayez-aussi',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'bounce'
                                    )
                                )
                            ),
                            'terms' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/reglement',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'terms'
                                    )
                                )
                            ),
                            'conditions' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/mentions-legales',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'conditions'
                                    )
                                )
                            ),
                            'fangate' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fangate',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'fangate'
                                    )
                                )
                            ),
                            'prizes' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/lots',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'prizes'
                                    )
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'prize' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/:prize',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                                'action' => 'prize'
                                            )
                                        )
                                    )
                                )
                            ),
                            'share' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/partager',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'share'
                                    )
                                )
                            ),
                            'leaderboard' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/leaderboard[/:filter][/:p]',
                                    'constraints' => array(
                                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action'     => 'leaderboard'
                                    ),
                                ),
                            ),
                            'other-routes' => array(
                                'type' => '\Zend\Mvc\Router\Http\Regex',
                                'priority' => -1000,
                                'options' => array(
                                    'regex' => '.*',
                                    'spec' => '%url%',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                                        'action' => 'not-found'
                                    )
                                )
                            )
                        )
                    ),

                    'postvote' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'post-vote[/:id]',
                            'defaults' => array(
                                'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                'action' => 'home'
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'index' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/index',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'index'
                                    )
                                )
                            ),
                            'list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/liste/:filter',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'list',
                                        'filter' => 0
                                    )
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'pagination' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '[/:p]',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                                'action' => 'list'
                                            )
                                        )
                                    )
                                )
                            ),
                            'ajaxforgotpassword' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/ajax-mot-passe-oublie',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action'     => 'ajaxforgot',
                                    ),
                                ),
                            ),
                            'resetpassword' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/reset-password/:userId/:token',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action'     => 'userreset',
                                    ),
                                    'constraints' => array(
                                        'userId'  => '[0-9]+',
                                        'token' => '[A-F0-9]+',
                                    ),
                                ),
                            ),
                            'login' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/connexion',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'login'
                                    )
                                )
                            ),
                            'user-register' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/inscription[/:socialnetwork]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'userregister'
                                    )
                                )
                            ),
                            'verification' => array(
		                        'type' => 'Literal',
		                        'options' => array(
		                            'route' => '/verification',
		                            'defaults' => array(
		                                'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
		                                'action'     => 'check-token',
		                            ),
		                        ),
		                    ),
                            'optin' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/optin[/:gameId]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'optin'
                                    )
                                )
                            ),
                            'play' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/jouer',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'play'
                                    )
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'ajaxupload' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/ajaxupload',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                                'action' => 'ajaxupload'
                                            )
                                        )
                                    ),
                                    'ajaxdelete' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/ajaxdelete',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                                'action' => 'ajaxdelete'
                                            )
                                        )
                                    )
                                )
                            ),
                            'preview' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/previsualiser',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'preview'
                                    )
                                )
                            ),
                            'post' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/post/:post',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'post'
                                    )
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'captcha' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/captcha/[:id]',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                                'action' => 'captcha'
                                            )
                                        )
                                    )
                                )
                            ),
                            'vote' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/vote[/:post]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'ajaxVote'
                                    )
                                ),
                                'may_terminate' => true,
                            ),
                            'comments' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/comments[/:p]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'comments'
                                    )
                                ),
                                'may_terminate' => true,
                            ),
                            'postcomments' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/comments/:post[/:p]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'comments'
                                    )
                                ),
                                'may_terminate' => true,
                            ),
                            'comment' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/comment[/:post]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'comment'
                                    )
                                )
                            ),
                            'removecomment' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/remove-comment[/:comment]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'ajaxRemoveComment'
                                    )
                                )
                            ),
                            'sharecomment' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/share-comment/:comment',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'shareComment'
                                    )
                                )
                            ),
                            'sharepost' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/share-post/:post',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'sharePost'
                                    )
                                )
                            ),
                            'reject-post' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/reject/:post',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'ajaxrejectPost'
                                    )
                                )
                            ),
                            'result' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/resultat',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'result'
                                    )
                                )
                            ),
                            'register' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/register',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'register'
                                    )
                                )
                            ),
                            'fbshare' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbshare',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'fbshare'
                                    )
                                )
                            ),
                            'fbrequest' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbrequest',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'fbrequest'
                                    )
                                )
                            ),
                            'tweet' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/tweet',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'tweet'
                                    )
                                )
                            ),
                            'google' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/google',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'google'
                                    )
                                )
                            ),
                            'bounce' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/essayez-aussi',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'bounce'
                                    )
                                )
                            ),
                            'terms' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/reglement',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'terms'
                                    )
                                )
                            ),
                            'conditions' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/mentions-legales',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'conditions'
                                    )
                                )
                            ),
                            'fangate' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fangate',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'fangate'
                                    )
                                )
                            ),
                            'prizes' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/lots',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'prizes'
                                    )
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'prize' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/:prize',
                                            'defaults' => array(
                                                'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                                'action' => 'prize'
                                            )
                                        )
                                    )
                                )
                            ),
                            'share' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/partager',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'share'
                                    )
                                )
                            ),
                            'leaderboard' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/leaderboard[/:filter][/:p]',
                                    'constraints' => array(
                                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action'     => 'leaderboard'
                                    ),
                                ),
                            ),
                            'other-routes' => array(
                                'type' => '\Zend\Mvc\Router\Http\Regex',
                                'priority' => -1000,
                                'options' => array(
                                    'regex' => '.*',
                                    'spec' => '%url%',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                                        'action' => 'not-found'
                                    )
                                )
                            )
                        )
                    ),
                    'prizecategories' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'thematiques/:id',
                            'constraints' => array(
                                'id' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => PlaygroundGame\Controller\Frontend\PrizeCategory::class,
                                'action' => 'index',
                                'id' => ''
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'pagination' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '[/:p]',
                                    'defaults' => array(
                                        'controller' => PlaygroundGame\Controller\Frontend\PrizeCategory::class,
                                        'action' => 'index'
                                    )
                                )
                            )
                        )
                    ),
                )
            ),

            'admin' => array(
                'child_routes' => array(
                    'mission' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/mission',
                            'defaults' => array(
                                'controller' => Playgroundgame\Controller\Admin\Mission::class,
                                'action' => 'list'
                            )
                        ),
                        'child_routes' => array(
                            'entry' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/:gameId/entries[/:p]',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Mission::class,
                                        'action' => 'entry',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'invitation' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/:gameId/invitation[/:p]',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Mission::class,
                                        'action' => 'invitation',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'removeInvitation' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/:gameId/removeInvitation/:invitationId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Mission::class,
                                        'action' => 'removeInvitation',
                                        'gameId' => 0,
                                        'invitationId' => 0
                                    )
                                )
                            ),
                            'download' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/download/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Mission::class,
                                        'action' => 'download'
                                    )
                                )
                            ),
                            'list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/list[/:p]',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Mission::class,
                                        'action' => 'list'
                                    )
                                )
                            ),
                            'create' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/create',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Mission::class,
                                        'action' => 'create'
                                    )
                                )
                            ),
                            'edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit/:missionId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Mission::class,
                                        'action' => 'edit',
                                        'missionId' => 0
                                    )
                                )
                            ),
                            'delete' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/delete/:missionId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Mission::class,
                                        'action' => 'delete',
                                        'missionId' => 0
                                    )
                                )
                            ),
                            'associate' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/associate/:missionId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Mission::class,
                                        'action' => 'associate',
                                        'missionId' => 0
                                    )
                                )
                            ),
                            'activate' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/activate/:missionId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Mission::class,
                                        'action' => 'activate',
                                        'missionId' => 0
                                    )
                                )
                            ),
                            'desactivate' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/desactivate/:missionId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Mission::class,
                                        'action' => 'desactivate',
                                        'missionId' => 0
                                    )
                                )
                            )
                        )
                    ),
                    'quiz' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/quiz',
                            'defaults' => array(
                                'controller' => Playgroundgame\Controller\Admin\Quiz::class,
                                'action' => 'index'
                            )
                        ),
                        'child_routes' => array(
                            'entry' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/:gameId/entries[/:p]',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Quiz::class,
                                        'action' => 'entry',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'download' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/download/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Quiz::class,
                                        'action' => 'download'
                                    )
                                )
                            ),
                            'draw' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/draw/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Quiz::class,
                                        'action' => 'draw'
                                    )
                                )
                            ),
                            'sortquestion' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/sortquestion/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Quiz::class,
                                        'action' => 'sortQuestion'
                                    )
                                )
                            )
                        )
                    ),
                    'lottery' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/lottery',
                            'defaults' => array(
                                'controller' => Playgroundgame\Controller\Admin\Lottery::class,
                                'action' => 'index'
                            )
                        ),
                        'child_routes' => array(
                            'entry' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/entry/:gameId[/:p]',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Lottery::class,
                                        'action' => 'entry',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'download' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/download/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Lottery::class,
                                        'action' => 'download'
                                    )
                                )
                            ),
                            'draw' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/draw/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Lottery::class,
                                        'action' => 'draw'
                                    )
                                )
                            )
                        )
                    ),
                    'instantwin' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/instantwin',
                            'defaults' => array(
                                'controller' => Playgroundgame\Controller\Admin\InstantWin::class,
                                'action' => 'index'
                            )
                        ),
                        'child_routes' => array(
                            'entry' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/entry/:gameId[/:p]',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\InstantWin::class,
                                        'action' => 'entry',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'download' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/download/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\InstantWin::class,
                                        'action' => 'download'
                                    )
                                )
                            )
                        )
                    ),
                    'postvote' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/postvote',
                            'defaults' => array(
                                'controller' => Playgroundgame\Controller\Admin\PostVote::class,
                                'action' => 'index'
                            )
                        ),
                        'child_routes' => array(
                            'entry' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/entry/:gameId[/:p]',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\PostVote::class,
                                        'action' => 'entry',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'download' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/download/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\PostVote::class,
                                        'action' => 'download'
                                    )
                                )
                            )
                        )
                    ),
                    'tradingcard' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/tradingcard',
                            'defaults' => array(
                                'controller' => Playgroundgame\Controller\Admin\TradingCard::class,
                                'action' => 'index'
                            )
                        ),
                        'child_routes' => array(
                            'entry' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/entry/:gameId[/:p]',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\TradingCard::class,
                                        'action' => 'entry',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'download' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/download/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\TradingCard::class,
                                        'action' => 'download'
                                    )
                                )
                            ),
                            'coo' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/coo/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\TradingCard::class,
                                        'action' => 'coo',
                                        'gameId' => 0
                                    )
                                )
                            ),
                        )
                    ),
                    'playgroundgame' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/game',
                            'defaults' => array(
                                'controller' => Playgroundgame\Controller\Admin\Game::class,
                                'action' => 'index'
                            )
                        ),
                        'child_routes' => array(
                            'list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/list/:type/:filter[/:p]',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Game::class,
                                        'action' => 'list',
                                        'type' => 'createdAt',
                                        'filter' => 'DESC'
                                    )
                                )
                            ),
                            'create' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/create',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Game::class,
                                        'action' => 'create'
                                    )
                                )
                            ),
                            'player-form' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/player-form/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Game::class,
                                        'action' => 'form',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'export' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/export/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Game::class,
                                        'action' => 'export',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'import' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/import',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Game::class,
                                        'action' => 'import'
                                    )
                                )
                            ),
                            'create-tradingcard' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/create-tradingcard',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\TradingCard::class,
                                        'action' => 'createTradingcard'
                                    )
                                )
                            ),
                            'edit-tradingcard' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit-tradingcard/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\TradingCard::class,
                                        'action' => 'editTradingcard',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'tradingcard-model-list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/tradingcard-model-list/:gameId[/:filter][/:p]',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\TradingCard::class,
                                        'action' => 'listModel',
                                        'gameId' => 0,
                                        'filter' => 'DESC'
                                    ),
                                    'constraints' => array(
                                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                    )
                                )
                            ),
                            'tradingcard-models-import' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/tradingcard-models-import/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\TradingCard::class,
                                        'action' => 'importModels',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'tradingcard-models-export' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/tradingcard-models-export/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\TradingCard::class,
                                        'action' => 'exportModels',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'tradingcard-model-add' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/tradingcard-model-add/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\TradingCard::class,
                                        'action' => 'addModel',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'tradingcard-model-edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/tradingcard-model-edit/:gameId/:modelId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\TradingCard::class,
                                        'action' => 'editModel',
                                        'gameId' => 0,
                                        'modelId' => 0
                                    )
                                )
                            ),
                            'tradingcard-model-remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/tradingcard-model-remove/:modelId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\TradingCard::class,
                                        'action' => 'removeModel',
                                        'modelId' => 0
                                    )
                                )
                            ),
                            'create-lottery' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/create-lottery',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Lottery::class,
                                        'action' => 'createLottery'
                                    )
                                )
                            ),
                            'edit-lottery' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit-lottery/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Lottery::class,
                                        'action' => 'editLottery',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'create-instantwin' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/create-instantwin',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\InstantWin::class,
                                        'action' => 'createInstantWin'
                                    )
                                )
                            ),
                            'edit-instantwin' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit-instantwin/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\InstantWin::class,
                                        'action' => 'editInstantWin',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'instantwin-occurrence-list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/instantwin-occurrence-list/:gameId[/:filter][/:p]',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\InstantWin::class,
                                        'action' => 'listOccurrence',
                                        'gameId' => 0,
                                        'filter' => 'DESC'
                                    ),
                                    'constraints' => array(
                                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                    )
                                )
                            ),
                            'instantwin-occurrences-import' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/instantwin-occurrences-import/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\InstantWin::class,
                                        'action' => 'importOccurrences',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'instantwin-occurrences-export' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/instantwin-occurrences-export/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\InstantWin::class,
                                        'action' => 'exportOccurrences',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'instantwin-occurrence-add' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/instantwin-occurrence-add/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\InstantWin::class,
                                        'action' => 'addOccurrence',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'instantwin-occurrence-edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/instantwin-occurrence-edit/:gameId/:occurrenceId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\InstantWin::class,
                                        'action' => 'editOccurrence',
                                        'gameId' => 0,
                                        'occurrenceId' => 0
                                    )
                                )
                            ),
                            'instantwin-occurrence-remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/instantwin-occurrence-remove/:occurrenceId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\InstantWin::class,
                                        'action' => 'removeOccurrence',
                                        'occurrenceId' => 0
                                    )
                                )
                            ),
                            'create-quiz' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/create-quiz',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Quiz::class,
                                        'action' => 'createQuiz'
                                    )
                                )
                            ),
                            'edit-quiz' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit-quiz/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Quiz::class,
                                        'action' => 'editQuiz',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'create-postvote' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/create-postvote',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\PostVote::class,
                                        'action' => 'createPostVote'
                                    )
                                )
                            ),
                            'edit-postvote' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit-postvote/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\PostVote::class,
                                        'action' => 'editPostVote',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'postvote-form' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/postvote-form/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\PostVote::class,
                                        'action' => 'form',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'postvote-mod-list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/postvote-mod-list',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\PostVote::class,
                                        'action' => 'modList'
                                    )
                                )
                            ),
                            'postvote-moderation-edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/postvote-moderation-edit/:postId[/:status]',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\PostVote::class,
                                        'action' => 'moderationEdit'
                                    )
                                )
                            ),
                            'postvote-push' => array(
                                 'type' => 'Segment',
                                'options' => array(
                                    'route' => '/postvote-push/:postId[/:pushed]',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\PostVote::class,
                                        'action' => 'push'
                                    )
                                )
                            ),

                            'entry' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/entry/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Game::class,
                                        'action' => 'entry',
                                        'gameId' => 0
                                    )
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'pagination' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '[:p]',
                                            'defaults' => array(
                                                'controller' => Playgroundgame\Controller\Admin\Game::class,
                                                'action' => 'entry'
                                            )
                                        )
                                    )
                                )
                            ),
                            'quiz-question-list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/quiz-question-list/:quizId[/:p]',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Quiz::class,
                                        'action' => 'listQuestion',
                                        'quizId' => 0
                                    )
                                ),
                            ),
                            'quiz-question-add' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/quiz-question-add/:quizId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Quiz::class,
                                        'action' => 'addQuestion',
                                        'quizId' => 0
                                    )
                                )
                            ),
                            'quiz-question-edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/quiz-question-edit/:questionId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Quiz::class,
                                        'action' => 'editQuestion',
                                        'questionId' => 0
                                    )
                                )
                            ),
                            'quiz-question-remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/quiz-question-remove/:questionId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Quiz::class,
                                        'action' => 'removeQuestion',
                                        'questionId' => 0
                                    )
                                )
                            ),
                            'download' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/download/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Game::class,
                                        'action' => 'download',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Game::class,
                                        'action' => 'edit',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/remove/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Game::class,
                                        'action' => 'remove',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'set-active' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/set-active/:gameId',
                                    'constraints' => array(
                                        'gameId' => '[0-9]+'
                                    ),
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Game::class,
                                        'action' => 'setActive',
                                        'gameId' => 0
                                    )
                                )
                            ),

                            'prize-category-list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/prize-category-list[/:p]',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_prizecategory',
                                        'action' => 'list'
                                    )
                                )
                            ),

                            'prize-category-add' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/prize-category-add/:prizeCategoryId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_prizecategory',
                                        'action' => 'add',
                                        'prizeCategoryId' => 0
                                    )
                                )
                            ),

                            'prize-category-edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/prize-category-edit/:prizeCategoryId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_prizecategory',
                                        'action' => 'edit',
                                        'prizeCategoryId' => 0
                                    )
                                )
                            ),
                            'create-mission' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/create-mission/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Mission::class,
                                        'action' => 'createMission',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'edit-mission' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit-mission/:gameId',
                                    'defaults' => array(
                                        'controller' => Playgroundgame\Controller\Admin\Mission::class,
                                        'action' => 'editMission',
                                        'gameId' => 0
                                    )
                                )
                            ),
                        )
                    )
                )
            )
        )
    ),

    'navigation' => array(
        'default' => array(
            'playgroundgame' => array(
                'label' => 'Jeux concours',
                'route' => 'gameslist',
                'pages' => array(
                    'quiz' => array(
                        'label' => 'Quiz',
                        'route' => 'quiz'
                    ),
                    'quiz_play' => array(
                        'label' => 'Quiz',
                        'route' => 'quiz/play'
                    ),
                    'quiz_result' => array(
                        'label' => 'Quiz',
                        'route' => 'quiz/result'
                    ),
                    'quiz_bounce' => array(
                        'label' => 'Quiz',
                        'route' => 'quiz/bounce'
                    ),
                    'quiz_terms' => array(
                        'label' => 'Quiz',
                        'route' => 'quiz/terms'
                    ),
                    'quiz_conditions' => array(
                        'label' => 'Quiz',
                        'route' => 'quiz/conditions'
                    ),
                    'lottery' => array(
                        'label' => 'Tirage au sort',
                        'route' => 'lottery'
                    ),
                    'lottery_result' => array(
                        'label' => 'Tirage au sort',
                        'route' => 'lottery/result'
                    ),
                    'lottery_bounce' => array(
                        'label' => 'Tirage au sort',
                        'route' => 'lottery/bounce'
                    ),
                    'lottery_terms' => array(
                        'label' => 'Tirage au sort',
                        'route' => 'lottery/terms'
                    ),
                    'lottery_conditions' => array(
                        'label' => 'Tirage au sort',
                        'route' => 'lottery/conditions'
                    ),
                    'instanwin' => array(
                        'label' => 'Instant gagnant',
                        'route' => 'instantwin'
                    ),
                    'instanwin_play' => array(
                        'label' => 'Instant gagnant',
                        'route' => 'instantwin/play'
                    ),
                    'instanwin_result' => array(
                        'label' => 'Instant gagnant',
                        'route' => 'instantwin/result'
                    ),
                    'instanwin_bounce' => array(
                        'label' => 'Instant gagnant',
                        'route' => 'instantwin/bounce'
                    ),
                    'instanwin_terms' => array(
                        'label' => 'Instant gagnant',
                        'route' => 'instantwin/terms'
                    ),
                    'instanwin_conditions' => array(
                        'label' => 'Instant gagnant',
                        'route' => 'instantwin/conditions'
                    ),
                    'postvote' => array(
                        'label' => 'Post & vote',
                        'route' => 'postvote'
                    ),
                    'postvote_play' => array(
                        'label' => 'Post & vote',
                        'route' => 'postvote/play'
                    ),
                    'postvote_preview' => array(
                        'label' => 'Post & vote',
                        'route' => 'postvote/preview'
                    ),
                    'postvote_result' => array(
                        'label' => 'Post & vote',
                        'route' => 'postvote/result'
                    ),
                    'postvote_post' => array(
                        'label' => 'Post & vote',
                        'route' => 'postvote/post'
                    ),
                    'postvote_list' => array(
                        'label' => 'Post & vote',
                        'route' => 'postvote/list'
                    ),
                    'postvote_bounce' => array(
                        'label' => 'Post & vote',
                        'route' => 'postvote/bounce'
                    )
                )
            ),
            array(
                'label' => 'Thématiques',
                'route' => 'thematiques/:id',
                'controller' => 'playgroundgame_prizecategories',
                'action' => 'index'
            )
        ),
        'admin' => array(
            'playgroundgame' => array(
                'label' => 'Games',
                'route' => 'admin/playgroundgame/list',
                'resource' => 'game',
                'privilege' => 'list',
                'pages' => array(
                    'list' => array(
                        'label' => 'Games list',
                        'route' => 'admin/playgroundgame/list',
                        'resource' => 'game',
                        'privilege' => 'list'
                    ),
                    'create-lottery' => array(
                        'label' => 'Add new lottery',
                        'route' => 'admin/playgroundgame/create-lottery',
                        'resource' => 'game',
                        'privilege' => 'add'
                    ),
                    'edit-lottery' => array(
                        'label' => 'Editer un tirage au sort',
                        'route' => 'admin/playgroundgame/edit-lottery',
                        'privilege' => 'edit'
                    ),
                    'entry-lottery' => array(
                        'label' => 'Participants',
                        'route' => 'admin/lottery/entry',
                        'privilege' => 'list'
                    ),
                    'create-quiz' => array(
                        'label' => 'Add new quiz',
                        'route' => 'admin/playgroundgame/create-quiz',
                        'resource' => 'game',
                        'privilege' => 'add'
                    ),
                    'edit-quiz' => array(
                        'label' => 'Editer un quiz',
                        'route' => 'admin/playgroundgame/edit-quiz',
                        'privilege' => 'edit'
                    ),
                    'entry-quiz' => array(
                        'label' => 'Participants',
                        'route' => 'admin/quiz/entry',
                        'privilege' => 'list'
                    ),
                    'create-postvote' => array(
                        'label' => 'Add new post & vote',
                        'route' => 'admin/playgroundgame/create-postvote',
                        'resource' => 'game',
                        'privilege' => 'add'
                    ),
                    'edit-postvote' => array(
                        'label' => 'Editer un Post & Vote',
                        'route' => 'admin/playgroundgame/edit-postvote',
                        'privilege' => 'edit'
                    ),
                    'entry-postvote' => array(
                        'label' => 'Participants',
                        'route' => 'admin/postvote/entry',
                        'privilege' => 'list'
                    ),
                    'create-instantwin' => array(
                        'label' => 'Add new instant win',
                        'route' => 'admin/playgroundgame/create-instantwin',
                        'resource' => 'game',
                        'privilege' => 'add'
                    ),
                    'edit-instantwin' => array(
                        'label' => 'Editer un instant gagnant',
                        'route' => 'admin/playgroundgame/edit-instantwin',
                        'privilege' => 'edit'
                    ),
                    'entry-instantwin' => array(
                        'label' => 'Participants',
                        'route' => 'admin/instantwin/entry',
                        'privilege' => 'list'
                    ),
                    'quiz-question-list' => array(
                        'label' => 'Liste des questions',
                        'route' => 'admin/playgroundgame/quiz-question-list',
                        'privilege' => 'list',
                        'pages' => array(
                            'quiz-question-add' => array(
                                'label' => 'Ajouter des questions',
                                'route' => 'admin/playgroundgame/quiz-question-add',
                                'privilege' => 'add'
                            ),
                            'quiz-question-edit' => array(
                                'label' => 'Editer une question',
                                'route' => 'admin/playgroundgame/quiz-question-edit',
                                'privilege' => 'edit'
                            )
                        )
                    ),
                    'list-prizecategory' => array(
                        'label' => 'Manage categories gain',
                        'route' => 'admin/playgroundgame/prize-category-list',
                        'resource' => 'game',
                        'privilege' => 'prizecategory_list'
                    ),
                    /*
                    'list-postvotemod' => array(
                        'label'     => 'Posts en attente de modération',
                        'route'     => 'admin/playgroundgame/postvote-mod-list',
                        'resource'  => 'game',
                        'privilege' => 'list',
                    ),
                    */
                    'instantwin-occurence-list' => array(
                        'label' => 'Liste des instant gagnants',
                        'route' => 'admin/playgroundgame/instantwin-occurrence-list',
                        'privilege' => 'list',
                        'pages' => array(
                            'instantwin-occurrence-add' => array(
                                'label' => 'Add new instant win',
                                'route' => 'admin/playgroundgame/instantwin-occurrence-add',
                                'privilege' => 'add'
                            ),
                            'instantwin-occurrence-edit' => array(
                                'label' => 'Editer un instant gagnant',
                                'route' => 'admin/playgroundgame/instantwin-occurrence-edit',
                                'privilege' => 'edit'
                            ),
                            'instantwin-code-occurrences-add' => array(
                                'label' => 'Add new instant win',
                                'route' => 'admin/playgroundgame/instantwin-code-occurrences-add',
                                'privilege' => 'add'
                            ),
                            'instantwin-occurrences-export' => array(
                                'route' => 'admin/playgroundgame/instantwin-occurrences-export',
                                'label' => 'Export to csv',
                                'privilege' => 'download'
                            )
                        )
                    ),
                    'postvote-form' => array(
                        'label' => 'Options du Post & vote',
                        'route' => 'admin/playgroundgame/postvote-form',
                        'privilege' => 'list'
                    ),
                    'create-mission' => array(
                        'label' => 'Add new mission',
                        'route' => 'admin/playgroundgame/create-mission',
                        'resource' => 'game',
                        'privilege' => 'add'
                    ),
                    'create-tradingcard' => array(
                        'label' => 'Add new trading card',
                        'route' => 'admin/playgroundgame/create-tradingcard',
                        'resource' => 'game',
                        'privilege' => 'add'
                    ),
                )
            )
        )
    )
);
