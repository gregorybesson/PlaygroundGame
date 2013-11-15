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
    'assetic_configuration' => array(
        'modules' => array(
            'lib_game' => array(
                // module root path for your css and js files
                'root_path' => __DIR__ . '/../view/lib',
                // collection of assets
                'collections' => array(
                    'admin_areapicker_css' => array(
                        'assets' => array(
                            'css/playground/areapicker/style.min.css'
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
                    'head_admin_areapicker_js' => array(
                        'assets' => array(
                            'js/playground/jquery.min.js',
                            'js/playground/areapicker/app.js',
                            'js/playground/areapicker/config.js',
                            'js/playground/areapicker/selection.js',
                            'js/easyxdm/easyxdm.min.js'
                        ),
                        'filters' => array(),
                        'options' => array(
                            'output' => 'zfcadmin/js/head_admin_areapicker.js'
                        )
                    ),
                    'head_areapicker_cors_js' => array(
                        'assets' => array(
                            'js/playground/areapicker/cors.js'
                        ),
                        'filters' => array(),
                        'options' => array(
                            'move_raw' => true,
                            'output' => 'lib'
                        )
                    ),
                    'head_admin_deezer_js' => array(
                        'assets' => array(
                            'js/deezer/dz.min.js'
                        ),
                        'filters' => array(),
                        'options' => array(
                            'output' => 'zfcadmin/js/head_deezer.js'
                        )
                    ),
                    'admin_deezer_js' => array(
                        'assets' => array(
                            'js/deezer/api-deezer.js'
                        ),
                        'filters' => array(),
                        'options' => array(
                            'output' => 'zfcadmin/js/deezer.js'
                        )
                    ),

                    'head_frontend_deezer_js' => array(
                        'assets' => array(
                            'js/deezer/dz.min.js'
                        ),
                        'filters' => array(),
                        'options' => array(
                            'output' => 'frontend/js/head_deezer.js'
                        )
                    ),
                    'frontend_deezer_js' => array(
                        'assets' => array(
                            'js/deezer/api-deezer.js'
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
            'admin/playgroundgame/treasure-hunt-areapicker' => array(
                '@admin_areapicker_css',
                '@head_admin_areapicker_js'
            ),
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
                'playgroundgame' => array(
                    'layout' => 'layout/game-2columns-right.phtml',
                    'channel' => array(
                        'facebook' => array(
                            'layout' => 'layout/1column-facebook.phtml'
                        ),
                        'embed' => array(
                            'layout' => 'layout/1column-embed.phtml'
                        )
                    ),
                    'controllers' => array(
                        'playgroundgame_lottery' => array(
                            'children_views' => array(
                                'col_right' => 'playground-game/lottery/col-lottery.phtml'
                            )
                        ),
                        'playgroundgame_quiz' => array(
                            // 'layout'
                            // =>
                            // 'layout/game-2columns-right.phtml',
                            'children_views' => array(
                                'col_right' => 'playground-game/quiz/col-quiz.phtml'
                            )
                        ),
                        'playgroundgame_instantwin' => array(
                            'children_views' => array(
                                'col_right' => 'playground-game/instant-win/col-instantwin.phtml'
                            )
                        ),
                        'playgroundgame_postvote' => array(
                            'children_views' => array(
                                'col_right' => 'playground-game/post-vote/col-postvote.phtml'
                            )
                        ),
                        'playgroundgame_treasurehunt' => array(
                            'children_views' => array(
                                'col_right' => 'playground-game/lottery/col-lottery.phtml'
                            )
                        ),
                        'playgroundgame_prizecategory' => array(
                            'actions' => array(
                                'index' => array(
                                    'children_views' => array(
                                        'col_right' => 'application/common/column_right.phtml'
                                    )
                                )
                            )
                        ),
                        'playgroundgame' => array(
                            'layout' => 'layout/1column-custom.phtml',
                            'children_views' => array(
                                'col_right' => 'playground-game/quiz/col-quiz.phtml.phtml'
                            ),
                            'actions' => array(
                                'index' => array(
                                    'layout' => 'layout/game-2columns-right.phtml'
                                )
                            )
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
        )
    ),

    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type' => 'phpArray',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.php',
                'text_domain' => 'playgroundgame'
            )
        )
    ),

    'controllers' => array(
        'invokables' => array(
            'playgroundgame' => 'PlaygroundGame\Controller\IndexController',
            'playgroundgame_game' => 'PlaygroundGame\Controller\Frontend\GameController',
            'playgroundgame_lottery' => 'PlaygroundGame\Controller\Frontend\LotteryController',
            'playgroundgame_quiz' => 'PlaygroundGame\Controller\Frontend\QuizController',
            'playgroundgame_instantwin' => 'PlaygroundGame\Controller\Frontend\InstantWinController',
            'playgroundgame_postvote' => 'PlaygroundGame\Controller\Frontend\PostVoteController',
            'playgroundgame_treasurehunt' => 'PlaygroundGame\Controller\Frontend\TreasureHuntController',
            'playgroundgame_prizecategory' => 'PlaygroundGame\Controller\Frontend\PrizeCategoryController',
            'playgroundgameadmin' => 'PlaygroundGame\Controller\Admin\AdminController',
            'playgroundgame_admin_lottery' => 'PlaygroundGame\Controller\Admin\LotteryController',
            'playgroundgame_admin_instantwin' => 'PlaygroundGame\Controller\Admin\InstantWinController',
            'playgroundgame_admin_postvote' => 'PlaygroundGame\Controller\Admin\PostVoteController',
            'playgroundgame_admin_quiz' => 'PlaygroundGame\Controller\Admin\QuizController',
            'playgroundgame_admin_treasurehunt' => 'PlaygroundGame\Controller\Admin\TreasureHuntController',
            'playgroundgame_admin_prizecategory' => 'PlaygroundGame\Controller\Admin\PrizeCategoryController',
            'playgroundgame_admin_mission' => 'PlaygroundGame\Controller\Admin\MissionController'
        )
    ),
    'router' => array(
        'routes' => array(
            'frontend' => array(
                'child_routes' => array(
                    'jeuxconcours' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => 'jeux-concours',
                            'defaults' => array(
                                'controller' => 'playgroundgame_game',
                                'action' => 'jeuxconcours'
                            )
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'pagination' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '[/:p]',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_game',
                                        'action' => 'jeuxconcours'
                                    ),
                                    'constraints' => array(
                                        'p' => '[0-9]*'
                                    )
                                )
                            )
                        )
                    ),

     				/*'game' => array(
       					'type' => 'Zend\Mvc\Router\Http\Regex',
       					'options' => array(
       						'regex'    => 'game/(?<controller>[a-zA-Z0-9-]+)_(?<id>[a-zA-Z0-9-]+)(_)?(?<action>[a-zA-Z0-9-]+)?(_)?(?<channel>[embed|facebook|platform|mobile]+)?(\.html)?',
       						'defaults' => array(
      							'controller' => 'quiz',
      							'action'     => 'index',
       						),
       						'spec' => 'game/%controller%_%id%_%channel%_%action%.html',
       					),
       				),*/

		            'quiz' => array(
                        'type' => 'Segment',
                        'options' => array(
                            // 'regex'
                            // =>
                            // 'quiz/(?<id>[a-zA-Z0-9-]+)(\/)?(?<action>[a-zA-Z0-9-]+)?(_)?(?<channel>[embed|facebook|platform|mobile]+)?(\.html)?',
                            'route' => 'quiz[/:id]',
                            'defaults' => array(
                                'controller' => 'playgroundgame_quiz',
                                'action' => 'home'
                            )
                        // 'spec'
                        // =>
                        // 'quiz/%id%/%action%%channel%.html',
                                                ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'index' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/index',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_quiz',
                                        'action' => 'index'
                                    )
                                )
                            ),
                            'play' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/jouer',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_quiz',
                                        'action' => 'play'
                                    )
                                )
                            ),
                            'result' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/resultat',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_quiz',
                                        'action' => 'result'
                                    )
                                )
                            ),
                            'register' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/register',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_quiz',
                                        'action' => 'register'
                                    )
                                )
                            ),
                            'fbshare' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbshare',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_quiz',
                                        'action' => 'fbshare'
                                    )
                                )
                            ),
                            'tweet' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/tweet',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_quiz',
                                        'action' => 'tweet'
                                    )
                                )
                            ),
                            'google' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/google',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_quiz',
                                        'action' => 'google'
                                    )
                                )
                            ),
                            'bounce' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/essayez-aussi',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_quiz',
                                        'action' => 'bounce'
                                    )
                                )
                            ),
                            'terms' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/reglement',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_quiz',
                                        'action' => 'terms'
                                    )
                                )
                            ),
                            'conditions' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/mentions-legales',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_quiz',
                                        'action' => 'conditions'
                                    )
                                )
                            ),
                            'fangate' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fangate',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_quiz',
                                        'action' => 'fangate'
                                    )
                                )
                            ),
                            'prizes' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/lots',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_quiz',
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
                                                'controller' => 'playgroundgame_quiz',
                                                'action' => 'prize'
                                            )
                                        )
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
                                'controller' => 'playgroundgame_lottery',
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
                                        'controller' => 'playgroundgame_lottery',
                                        'action' => 'index'
                                    )
                                )
                            ),
                            'play' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/jouer',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_lottery',
                                        'action' => 'play'
                                    )
                                )
                            ),
                            'result' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/resultat',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_lottery',
                                        'action' => 'result'
                                    )
                                )
                            ),
                            'register' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/register',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_lottery',
                                        'action' => 'register'
                                    )
                                )
                            ),
                            'fbshare' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbshare',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_lottery',
                                        'action' => 'fbshare'
                                    )
                                )
                            ),
                            'tweet' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/tweet',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_lottery',
                                        'action' => 'tweet'
                                    )
                                )
                            ),
                            'google' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/google',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_lottery',
                                        'action' => 'google'
                                    )
                                )
                            ),
                            'bounce' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/essayez-aussi',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_lottery',
                                        'action' => 'bounce'
                                    )
                                )
                            ),
                            'terms' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/reglement',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_lottery',
                                        'action' => 'terms'
                                    )
                                )
                            ),
                            'conditions' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/mentions-legales',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_lottery',
                                        'action' => 'conditions'
                                    )
                                )
                            ),
                            'fangate' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fangate',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_lottery',
                                        'action' => 'fangate'
                                    )
                                )
                            ),
                            'prizes' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/lots',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_lottery',
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
                                                'controller' => 'playgroundgame_lottery',
                                                'action' => 'prize'
                                            )
                                        )
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
                                'controller' => 'playgroundgame_instantwin',
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
                                        'controller' => 'playgroundgame_instantwin',
                                        'action' => 'index'
                                    )
                                )
                            ),
                            'play' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/jouer',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_instantwin',
                                        'action' => 'play'
                                    )
                                )
                            ),
                            'result' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/resultat',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_instantwin',
                                        'action' => 'result'
                                    )
                                )
                            ),
                            'register' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/register',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_instantwin',
                                        'action' => 'register'
                                    )
                                )
                            ),
                            'fbshare' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbshare',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_instantwin',
                                        'action' => 'fbshare'
                                    )
                                )
                            ),
                            'tweet' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/tweet',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_instantwin',
                                        'action' => 'tweet'
                                    )
                                )
                            ),
                            'google' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/google',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_instantwin',
                                        'action' => 'google'
                                    )
                                )
                            ),
                            'bounce' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/essayez-aussi',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_instantwin',
                                        'action' => 'bounce'
                                    )
                                )
                            ),
                            'terms' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/reglement',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_instantwin',
                                        'action' => 'terms'
                                    )
                                )
                            ),
                            'conditions' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/mentions-legales',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_instantwin',
                                        'action' => 'conditions'
                                    )
                                )
                            ),
                            'fangate' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fangate',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_instantwin',
                                        'action' => 'fangate'
                                    )
                                )
                            ),
                            'prizes' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/lots',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_instantwin',
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
                                                'controller' => 'playgroundgame_instantwin',
                                                'action' => 'prize'
                                            )
                                        )
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
                                'controller' => 'playgroundgame_postvote',
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
                                        'controller' => 'playgroundgame_postvote',
                                        'action' => 'index'
                                    )
                                )
                            ),
                            'list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/liste/:filter',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_postvote',
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
                                                'controller' => 'playgroundgame_postvote',
                                                'action' => 'list'
                                            )
                                        )
                                    )
                                )
                            ),
                            'play' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/jouer',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_postvote',
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
                                                'controller' => 'playgroundgame_postvote',
                                                'action' => 'ajaxupload'
                                            )
                                        )
                                    ),
                                    'ajaxdelete' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/ajaxdelete',
                                            'defaults' => array(
                                                'controller' => 'playgroundgame_postvote',
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
                                        'controller' => 'playgroundgame_postvote',
                                        'action' => 'preview'
                                    )
                                )
                            ),
                            'post' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/post/:post',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_postvote',
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
                                                'controller' => 'playgroundgame_postvote',
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
                                        'controller' => 'playgroundgame_postvote',
                                        'action' => 'ajaxVote'
                                    )
                                )
                            ),
                            'result' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/resultat',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_postvote',
                                        'action' => 'result'
                                    )
                                )
                            ),
                            'register' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/register',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_postvote',
                                        'action' => 'register'
                                    )
                                )
                            ),
                            'fbshare' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbshare',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_postvote',
                                        'action' => 'fbshare'
                                    )
                                )
                            ),
                            'tweet' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/tweet',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_postvote',
                                        'action' => 'tweet'
                                    )
                                )
                            ),
                            'google' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/google',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_postvote',
                                        'action' => 'google'
                                    )
                                )
                            ),
                            'bounce' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/essayez-aussi',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_postvote',
                                        'action' => 'bounce'
                                    )
                                )
                            ),
                            'terms' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/reglement',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_postvote',
                                        'action' => 'terms'
                                    )
                                )
                            ),
                            'conditions' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/mentions-legales',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_postvote',
                                        'action' => 'conditions'
                                    )
                                )
                            ),
                            'fangate' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fangate',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_postvote',
                                        'action' => 'fangate'
                                    )
                                )
                            ),
                            'prizes' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/lots',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_postvote',
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
                                                'controller' => 'playgroundgame_postvote',
                                                'action' => 'prize'
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    ),
                    'treasurehunt' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'chasse-au-tresor[/:id]',
                            'defaults' => array(
                                'controller' => 'playgroundgame_treasurehunt',
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
                                        'controller' => 'playgroundgame_treasurehunt',
                                        'action' => 'index'
                                    )
                                )
                            ),
                            'play' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/jouer',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_treasurehunt',
                                        'action' => 'play'
                                    )
                                )
                            ),
                            'result' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/resultat',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_treasurehunt',
                                        'action' => 'result'
                                    )
                                )
                            ),
                            'register' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/register',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_treasurehunt',
                                        'action' => 'register'
                                    )
                                )
                            ),
                            'fbshare' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fbshare',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_treasurehunt',
                                        'action' => 'fbshare'
                                    )
                                )
                            ),
                            'tweet' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/tweet',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_treasurehunt',
                                        'action' => 'tweet'
                                    )
                                )
                            ),
                            'google' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/google',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_treasurehunt',
                                        'action' => 'google'
                                    )
                                )
                            ),
                            'bounce' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/essayez-aussi',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_treasurehunt',
                                        'action' => 'bounce'
                                    )
                                )
                            ),
                            'terms' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/reglement',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_treasurehunt',
                                        'action' => 'terms'
                                    )
                                )
                            ),
                            'conditions' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/mentions-legales',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_treasurehunt',
                                        'action' => 'conditions'
                                    )
                                )
                            ),
                            'fangate' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/fangate',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_treasurehunt',
                                        'action' => 'fangate'
                                    )
                                )
                            ),
                            'prizes' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/lots',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_treasurehunt',
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
                                                'controller' => 'playgroundgame_treasurehunt',
                                                'action' => 'prize'
                                            )
                                        )
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
                                'controller' => 'playgroundgame_prizecategory',
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
                                        'controller' => 'playgroundgame_prizecategory',
                                        'action' => 'index'
                                    )
                                )
                            )
                        )
                    ),
                    'photocontestconsultation' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/photo-contest-consultation',
                            'defaults' => array(
                                'controller' => 'adfabgame',
                                'action' => 'photocontestconsultation'
                            )
                        )
                    ),
                    'photocontestcreate' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/photo-contest-create',
                            'defaults' => array(
                                'controller' => 'adfabgame',
                                'action' => 'photocontestcreate'
                            )
                        )
                    ),
                    'photocontestoverview' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/photo-contest-overview',
                            'defaults' => array(
                                'controller' => 'adfabgame',
                                'action' => 'photocontestoverview'
                            )
                        )
                    ),
                    'photokitchenconsultation' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/photo-kitchen-consultation',
                            'defaults' => array(
                                'controller' => 'adfabgame',
                                'action' => 'photokitchenconsultation'
                            )
                        )
                    ),
                    'photokitchenparticipate' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/photo-kitchen-participate',
                            'defaults' => array(
                                'controller' => 'adfabgame',
                                'action' => 'photokitchenparticipate'
                            )
                        )
                    ),
                    'postvoteconsultation' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/post-vote-consultation',
                            'defaults' => array(
                                'controller' => 'adfabgame',
                                'action' => 'postvoteconsultation'
                            )
                        )
                    ),
                    'postvotenotlogged' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/post-vote-not-logged',
                            'defaults' => array(
                                'controller' => 'adfabgame',
                                'action' => 'postvotenotlogged'
                            )
                        )
                    )
                )
            ),

            'admin' => array(
                'child_routes' => array(
                    'quiz' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/quiz',
                            'defaults' => array(
                                'controller' => 'playgroundgame_admin_quiz',
                                'action' => 'index'
                            )
                        ),
                        'child_routes' => array(
                            'entry' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/entry/:gameId[/:p]',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_quiz',
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
                                        'controller' => 'playgroundgame_admin_quiz',
                                        'action' => 'download'
                                    )
                                )
                            ),
                            'draw' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/draw/:gameId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_lottery',
                                        'action' => 'draw'
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
                                'controller' => 'playgroundgame_admin_lottery',
                                'action' => 'index'
                            )
                        ),
                        'child_routes' => array(
                            'entry' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/entry/:gameId[/:p]',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_lottery',
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
                                        'controller' => 'playgroundgame_admin_lottery',
                                        'action' => 'download'
                                    )
                                )
                            ),
                            'draw' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/draw/:gameId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_lottery',
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
                                'controller' => 'playgroundgame_admin_instantwin',
                                'action' => 'index'
                            )
                        ),
                        'child_routes' => array(
                            'entry' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/entry/:gameId[/:p]',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_instantwin',
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
                                        'controller' => 'playgroundgame_admin_instantwin',
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
                                'controller' => 'playgroundgame_admin_postvote',
                                'action' => 'index'
                            )
                        ),
                        'child_routes' => array(
                            'entry' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/entry/:gameId[/:p]',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_postvote',
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
                                        'controller' => 'playgroundgame_admin_postvote',
                                        'action' => 'download'
                                    )
                                )
                            )
                        )
                    ),
                    'treasurehunt' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/treasurehunt',
                            'defaults' => array(
                                'controller' => 'playgroundgame_admin_treasurehunt',
                                'action' => 'index'
                            )
                        ),
                        'child_routes' => array(
                            'entry' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/entry/:gameId[/:p]',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_treasurehunt',
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
                                        'controller' => 'playgroundgame_admin_treasurehunt',
                                        'action' => 'download'
                                    )
                                )
                            )
                        )
                    ),
                    'mission' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/mission',
                            'defaults' => array(
                                'controller' => 'playgroundgame_admin_mission',
                                'action' => 'list'
                            )
                        ),
                        'child_routes' => array(
                            'list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/list[/:p]',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_mission',
                                        'action' => 'list'
                                    )
                                )
                            ),
                            'create' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/create',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_mission',
                                        'action' => 'create'
                                    )
                                )
                            ),
                            'edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit/:missionId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_mission',
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
                                        'controller' => 'playgroundgame_admin_mission',
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
                                        'controller' => 'playgroundgame_admin_mission',
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
                                        'controller' => 'playgroundgame_admin_mission',
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
                                        'controller' => 'playgroundgame_admin_mission',
                                        'action' => 'desactivate',
                                        'missionId' => 0
                                    )
                                )
                            )
                        )
                    ),

                    'playgroundgame' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/game',
                            'defaults' => array(
                                'controller' => 'playgroundgameadmin',
                                'action' => 'index'
                            )
                        ),
                        'child_routes' => array(
                            'list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/list/:type/:filter[/:p]',
                                    'defaults' => array(
                                        'controller' => 'playgroundgameadmin',
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
                                        'controller' => 'playgroundgameadmin',
                                        'action' => 'create'
                                    )
                                )
                            ),
                            'create-lottery' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/create-lottery',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_lottery',
                                        'action' => 'createLottery'
                                    )
                                )
                            ),
                            'edit-lottery' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit-lottery/:gameId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_lottery',
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
                                        'controller' => 'playgroundgame_admin_instantwin',
                                        'action' => 'createInstantWin'
                                    )
                                )
                            ),
                            'edit-instantwin' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit-instantwin/:gameId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_instantwin',
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
                                        'controller' => 'playgroundgame_admin_instantwin',
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
                                        'controller' => 'playgroundgame_admin_instantwin',
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
                                        'controller' => 'playgroundgame_admin_instantwin',
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
                                        'controller' => 'playgroundgame_admin_instantwin',
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
                                        'controller' => 'playgroundgame_admin_instantwin',
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
                                        'controller' => 'playgroundgame_admin_instantwin',
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
                                        'controller' => 'playgroundgame_admin_quiz',
                                        'action' => 'createQuiz'
                                    )
                                )
                            ),
                            'edit-quiz' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit-quiz/:gameId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_quiz',
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
                                        'controller' => 'playgroundgame_admin_postvote',
                                        'action' => 'createPostVote'
                                    )
                                )
                            ),
                            'edit-postvote' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit-postvote/:gameId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_postvote',
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
                                        'controller' => 'playgroundgame_admin_postvote',
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
                                        'controller' => 'playgroundgame_admin_postvote',
                                        'action' => 'modList'
                                    )
                                )
                            ),
                            'postvote-moderation-edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/postvote-moderation-edit/:postId[/:status]',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_postvote',
                                        'action' => 'moderationEdit'
                                    )
                                )
                            ),

                            'entry' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/entry/:gameId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgameadmin',
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
                                                'controller' => 'playgroundgameadmin',
                                                'action' => 'entry'
                                            )
                                        )
                                    )
                                )
                            ),
                            'quiz-question-list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/quiz-question-list/:quizId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_quiz',
                                        'action' => 'listQuestion',
                                        'quizId' => 0
                                    )
                                )
                            ),
                            'quiz-question-add' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/quiz-question-add/:quizId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_quiz',
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
                                        'controller' => 'playgroundgame_admin_quiz',
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
                                        'controller' => 'playgroundgame_admin_quiz',
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
                                        'controller' => 'playgroundgameadmin',
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
                                        'controller' => 'playgroundgameadmin',
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
                                        'controller' => 'playgroundgameadmin',
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
                                        'controller' => 'playgroundgameadmin',
                                        'action' => 'setActive',
                                        'gameId' => 0
                                    )
                                )
                            ),

                            'prize-category-list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/prize-category-list',
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

                            'create-treasurehunt' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/create-treasurehunt/:treasureHuntId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_treasurehunt',
                                        'action' => 'createTreasureHunt',
                                        'treasureHuntId' => 0
                                    )
                                )
                            ),
                            'edit-treasurehunt' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/edit-treasurehunt/:gameId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_treasurehunt',
                                        'action' => 'editTreasureHunt',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'treasurehunt-puzzle-list' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/treasurehpuzzlepuzzle-list/:gameId[/:filter][/:p]',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_treasurehunt',
                                        'action' => 'listPuzzle',
                                        'gameId' => 0,
                                        'filter' => 'DESC'
                                    ),
                                    'constraints' => array(
                                        'filter' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                    )
                                )
                            ),
                            'treasurehunt-puzzle-add' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/treasurehunt-puzzle-add/:gameId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_treasurehunt',
                                        'action' => 'addPuzzle',
                                        'gameId' => 0
                                    )
                                )
                            ),
                            'treasurehunt-puzzle-edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/treasurehunt-puzzle-edit/:gameId/:puzzleId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_treasurehunt',
                                        'action' => 'editPuzzle',
                                        'gameId' => 0,
                                        'puzzleId' => 0
                                    )
                                )
                            ),
                            'treasurehunt-puzzle-remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/treasurehunt-puzzle-remove/:puzzleId',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_treasurehunt',
                                        'action' => 'removePuzzle',
                                        'puzzleId' => 0
                                    )
                                )
                            ),
                            'treasure-hunt-areapicker' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/treasure-hunt-areapicker',
                                    'defaults' => array(
                                        'controller' => 'playgroundgame_admin_treasurehunt',
                                        'action' => 'areapicker'
                                    )
                                )
                            )
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
                'route' => 'jeuxconcours',
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

                    'create-treasurehunt' => array(
                        'label' => 'Add new treasure hunt',
                        'route' => 'admin/playgroundgame/create-treasurehunt',
                        'resource' => 'game',
                        'privilege' => 'add'
                    ),

                    'mission_list' => array(
                        'label' => 'Mission Management',
                        'route' => 'admin/mission/list',
                        'resource' => 'game',
                        'privilege' => 'list'
                    )
                )
            )
        )
    )
);
