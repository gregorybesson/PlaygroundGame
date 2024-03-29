<?php
return array(
  'doctrine' => array(
    'driver' => array(
      'playgroundgame_entity' => array(
        'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
        'cache' => 'array',
        'paths' => [__DIR__ . '/../src/Entity']
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
          array(array('game-manager', 'admin'), 'game', array('list', 'add', 'edit', 'delete')),
          array(array('game-manager', 'admin'), 'game', array('prizecategory_list', 'prizecategory_add', 'prizecategory_edit', 'prizecategory_delete')),
          array(array('supervisor'), 'core', array('dashboard')),
          array(array('supervisor'), 'game', array('list')),
        ),
      ),
    ),

    'guards' => array(
      'BjyAuthorize\Guard\Controller' => array(
        array('controller' => PlaygroundGame\Controller\Frontend\Home::class,           'roles' => array('guest', 'user')),
        array('controller' => PlaygroundGame\Controller\Frontend\Game::class,           'roles' => array('guest', 'user')),
        array('controller' => PlaygroundGame\Controller\Frontend\Lottery::class,        'roles' => array('guest', 'user')),
        array('controller' => PlaygroundGame\Controller\Frontend\Quiz::class,           'roles' => array('guest', 'user')),
        array('controller' => PlaygroundGame\Controller\Frontend\PostVote::class,       'roles' => array('guest', 'user')),
        array('controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,     'roles' => array('guest', 'user')),
        array('controller' => PlaygroundGame\Controller\Frontend\PrizeCategory::class,  'roles' => array('guest', 'user')),
        array('controller' => PlaygroundGame\Controller\Frontend\Mission::class,        'roles' => array('guest', 'user')),
        array('controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,    'roles' => array('guest', 'user')),
        array('controller' => PlaygroundGame\Controller\Frontend\Memory::class,         'roles' => array('guest', 'user')),
        array('controller' => PlaygroundGame\Controller\Frontend\Crossword::class,      'roles' => array('guest', 'user')),
        array('controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,   'roles' => array('guest', 'user')),
        array('controller' => PlaygroundGame\Controller\Frontend\Webhook::class,        'roles' => array('guest', 'user')),

        // Admin area admin
        array('controller' => PlaygroundGame\Controller\Admin\Game::class,              'roles' => array('game-manager', 'admin')),
        array('controller' => PlaygroundGame\Controller\Admin\Lottery::class,           'roles' => array('game-manager', 'admin')),
        array('controller' => PlaygroundGame\Controller\Admin\InstantWin::class,        'roles' => array('game-manager', 'admin')),
        array('controller' => PlaygroundGame\Controller\Admin\Quiz::class,              'roles' => array('game-manager', 'admin')),
        array('controller' => PlaygroundGame\Controller\Admin\PostVote::class,          'roles' => array('game-manager', 'admin')),
        array('controller' => PlaygroundGame\Controller\Admin\Mission::class,           'roles' => array('game-manager', 'admin')),
        array('controller' => PlaygroundGame\Controller\Admin\TradingCard::class,       'roles' => array('game-manager', 'admin')),
        array('controller' => PlaygroundGame\Controller\Admin\Memory::class,            'roles' => array('game-manager', 'admin')),
        array('controller' => PlaygroundGame\Controller\Admin\Crossword::class,         'roles' => array('game-manager', 'admin')),
        array('controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,      'roles' => array('game-manager', 'admin')),
        array('controller' => PlaygroundGame\Controller\Admin\PrizeCategory::class,     'roles' => array('game-manager', 'admin')),

        // Admin area supervisor
        array('controller' => PlaygroundGame\Controller\Admin\Game::class,              'action' => ['list'], 'roles' => array('supervisor')),
        array('controller' => PlaygroundGame\Controller\Admin\Lottery::class,           'action' => ['entry', 'download'], 'roles' => array('supervisor')),
        array('controller' => PlaygroundGame\Controller\Admin\InstantWin::class,        'action' => ['entry', 'download'], 'roles' => array('supervisor')),
        array('controller' => PlaygroundGame\Controller\Admin\Quiz::class,              'action' => ['entry', 'download'], 'roles' => array('supervisor')),
        array('controller' => PlaygroundGame\Controller\Admin\PostVote::class,          'action' => ['entry', 'download', 'moderationEdit', 'modList'], 'roles' => array('supervisor')),
        array('controller' => PlaygroundGame\Controller\Admin\Mission::class,           'action' => ['entry', 'download'], 'roles' => array('supervisor')),
        array('controller' => PlaygroundGame\Controller\Admin\TradingCard::class,       'action' => ['entry', 'download'], 'roles' => array('supervisor')),
        array('controller' => PlaygroundGame\Controller\Admin\Memory::class,            'action' => ['entry', 'download'], 'roles' => array('supervisor')),
        array('controller' => PlaygroundGame\Controller\Admin\Crossword::class,         'action' => ['entry', 'download'], 'roles' => array('supervisor')),
        array('controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,      'action' => ['entry', 'download'], 'roles' => array('supervisor')),
      ),
    ),
  ),

  'core_layout' => array(
    'frontend' => array(
      'modules' => array(
        PlaygroundGame\Controller\Frontend\Home::class => array(
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
            PlaygroundGame\Controller\Frontend\Home::class => array(
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
        'base_dir' => __DIR__ . '/../language',
        'pattern' => '%s.php',
        'text_domain' => 'playgroundgame'
      ),
      array(
        'type' => 'phpArray',
        'base_dir' => __DIR__ . '/../../../../language',
        'pattern' => '%s.php',
        'text_domain' => 'playgroundgame'
      ),
    )
  ),

  'controllers' => array(
    'aliases' => array(
      PlaygroundGame\Controller\Frontend\Instantwin::class => PlaygroundGame\Controller\Frontend\InstantWin::class,
      PlaygroundGame\Controller\Frontend\Postvote::class => PlaygroundGame\Controller\Frontend\PostVote::class,
      PlaygroundGame\Controller\Frontend\Tradingcard::class => PlaygroundGame\Controller\Frontend\TradingCard::class,
      PlaygroundGame\Controller\Frontend\Treasurehunt::class => PlaygroundGame\Controller\Frontend\TreasureHunt::class,

      PlaygroundGame\Controller\Admin\Instantwin::class => PlaygroundGame\Controller\Admin\InstantWin::class,
      PlaygroundGame\Controller\Admin\Postvote::class => PlaygroundGame\Controller\Admin\PostVote::class,
      PlaygroundGame\Controller\Admin\Tradingcard::class => PlaygroundGame\Controller\Admin\TradingCard::class,
      PlaygroundGame\Controller\Admin\Treasurehunt::class => PlaygroundGame\Controller\Admin\TreasureHunt::class,
    ),
    'factories' => array(
      PlaygroundGame\Controller\Frontend\Home::class => PlaygroundGame\Service\Factory\FrontendHomeControllerFactory::class,
      PlaygroundGame\Controller\Frontend\Game::class => PlaygroundGame\Service\Factory\FrontendGameControllerFactory::class,
      PlaygroundGame\Controller\Frontend\Lottery::class => PlaygroundGame\Service\Factory\FrontendLotteryControllerFactory::class,
      PlaygroundGame\Controller\Frontend\Quiz::class => PlaygroundGame\Service\Factory\FrontendQuizControllerFactory::class,
      PlaygroundGame\Controller\Frontend\InstantWin::class => PlaygroundGame\Service\Factory\FrontendInstantWinControllerFactory::class,
      PlaygroundGame\Controller\Frontend\PostVote::class => PlaygroundGame\Service\Factory\FrontendPostVoteControllerFactory::class,
      PlaygroundGame\Controller\Frontend\Mission::class => PlaygroundGame\Service\Factory\FrontendMissionControllerFactory::class,
      PlaygroundGame\Controller\Frontend\TradingCard::class => PlaygroundGame\Service\Factory\FrontendTradingCardControllerFactory::class,
      PlaygroundGame\Controller\Frontend\Memory::class => PlaygroundGame\Service\Factory\FrontendMemoryControllerFactory::class,
      PlaygroundGame\Controller\Frontend\Crossword::class => PlaygroundGame\Service\Factory\FrontendCrosswordControllerFactory::class,
      PlaygroundGame\Controller\Frontend\TreasureHunt::class => PlaygroundGame\Service\Factory\FrontendTreasureHuntControllerFactory::class,
      PlaygroundGame\Controller\Frontend\PrizeCategory::class => PlaygroundGame\Service\Factory\FrontendPrizeCategoryControllerFactory::class,
      PlaygroundGame\Controller\Frontend\Webhook::class => PlaygroundGame\Service\Factory\FrontendWebhookControllerFactory::class,

      PlaygroundGame\Controller\Admin\Game::class => PlaygroundGame\Service\Factory\AdminGameControllerFactory::class,
      PlaygroundGame\Controller\Admin\Lottery::class => PlaygroundGame\Service\Factory\AdminLotteryControllerFactory::class,
      PlaygroundGame\Controller\Admin\InstantWin::class => PlaygroundGame\Service\Factory\AdminInstantWinControllerFactory::class,
      PlaygroundGame\Controller\Admin\PostVote::class => PlaygroundGame\Service\Factory\AdminPostVoteControllerFactory::class,
      PlaygroundGame\Controller\Admin\Quiz::class => PlaygroundGame\Service\Factory\AdminQuizControllerFactory::class,
      PlaygroundGame\Controller\Admin\Mission::class => PlaygroundGame\Service\Factory\AdminMissionControllerFactory::class,
      PlaygroundGame\Controller\Admin\TradingCard::class => PlaygroundGame\Service\Factory\AdminTradingCardControllerFactory::class,
      PlaygroundGame\Controller\Admin\Memory::class => PlaygroundGame\Service\Factory\AdminMemoryControllerFactory::class,
      PlaygroundGame\Controller\Admin\Crossword::class => PlaygroundGame\Service\Factory\AdminCrosswordControllerFactory::class,
      PlaygroundGame\Controller\Admin\TreasureHunt::class => PlaygroundGame\Service\Factory\AdminTreasureHuntControllerFactory::class,
      PlaygroundGame\Controller\Admin\PrizeCategory::class => PlaygroundGame\Service\Factory\AdminPrizeCategoryControllerFactory::class,
    ),
  ),

  'service_manager' => [
    'aliases' => [
      'playgroundgame_partner_service'           => 'playgroundpartnership_partner_service',
      'playgroundgame_message'                   => 'playgroundcore_message',
      'playgroundgame_game_service'              => PlaygroundGame\Service\Game::class,
      'playgroundgame_lottery_service'           => PlaygroundGame\Service\Lottery::class,
      'playgroundgame_postvote_service'          => PlaygroundGame\Service\PostVote::class,
      'playgroundgame_quiz_service'              => PlaygroundGame\Service\Quiz::class,
      'playgroundgame_instantwin_service'        => PlaygroundGame\Service\InstantWin::class,
      'playgroundgame_mission_service'           => PlaygroundGame\Service\Mission::class,
      'playgroundgame_mission_game_service'      => PlaygroundGame\Service\MissionGame::class,
      'playgroundgame_tradingcard_service'       => PlaygroundGame\Service\TradingCard::class,
      'playgroundgame_memory_service'            => PlaygroundGame\Service\Memory::class,
      'playgroundgame_crossword_service'         => PlaygroundGame\Service\Crossword::class,
      'playgroundgame_treasurehunt_service'      => PlaygroundGame\Service\TreasureHunt::class,
      'playgroundgame_prize_service'             => PlaygroundGame\Service\Prize::class,
      'playgroundgame_prizecategory_service'     => PlaygroundGame\Service\PrizeCategory::class,
      'playgroundgame_prizecategoryuser_service' => PlaygroundGame\Service\PrizeCategoryUser::class,
    ],
    'factories' => [
      PlaygroundGame\Service\Game::class              => PlaygroundGame\Service\Factory\GameFactory::class,
      PlaygroundGame\Service\Lottery::class           => PlaygroundGame\Service\Factory\LotteryFactory::class,
      PlaygroundGame\Service\PostVote::class          => PlaygroundGame\Service\Factory\PostVoteFactory::class,
      PlaygroundGame\Service\Quiz::class              => PlaygroundGame\Service\Factory\QuizFactory::class,
      PlaygroundGame\Service\InstantWin::class        => PlaygroundGame\Service\Factory\InstantWinFactory::class,
      PlaygroundGame\Service\Mission::class           => PlaygroundGame\Service\Factory\MissionFactory::class,
      PlaygroundGame\Service\MissionGame::class       => PlaygroundGame\Service\Factory\MissionGameFactory::class,
      PlaygroundGame\Service\TradingCard::class       => PlaygroundGame\Service\Factory\TradingCardFactory::class,
      PlaygroundGame\Service\Memory::class            => PlaygroundGame\Service\Factory\MemoryFactory::class,
      PlaygroundGame\Service\Crossword::class         => PlaygroundGame\Service\Factory\CrosswordFactory::class,
      PlaygroundGame\Service\TreasureHunt::class      => PlaygroundGame\Service\Factory\TreasureHuntFactory::class,
      PlaygroundGame\Service\Prize::class             => PlaygroundGame\Service\Factory\PrizeFactory::class,
      PlaygroundGame\Service\PrizeCategory::class     => PlaygroundGame\Service\Factory\PrizeCategoryFactory::class,
      PlaygroundGame\Service\PrizeCategoryUser::class => PlaygroundGame\Service\Factory\PrizeCategoryUserFactory::class,
    ],
  ],

  'router' => array(
    'routes' => array(
      'frontend' => array(
        'options' => array(
          'defaults' => array(
            'controller' => PlaygroundGame\Controller\Frontend\Home::class,
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
                'controller' => PlaygroundGame\Controller\Frontend\Home::class,
                'action'     => 'index',
              ),
              'constraints' => array('p' => '[0-9]*'),
            ),
          ),
          'share' => array(
            'type' => 'Laminas\Router\Http\Literal',
            'options' => array(
              'route' => 'partager',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Frontend\Home::class,
                'action' => 'share'
              )
            )
          ),
          'gameslist' => array(
            'type' => 'Laminas\Router\Http\Literal',
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
          'webhook' => array(
            'type' => 'Segment',
            'options' => array(
              'route' => 'webhook',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Frontend\Webhook::class,
                'action' => 'index'
              )
            ),
            'may_terminate' => true,
            'child_routes' => array(
              'facebook' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/facebook',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Webhook::class,
                    'action' => 'facebook'
                  )
                )
              ),
              'instagram' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/instagram',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Webhook::class,
                    'action' => 'instagram'
                  )
                )
              ),
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
                'type' => 'Laminas\Router\Http\Literal',
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
                    'type' => 'Laminas\Router\Http\Literal',
                    'options' => array(
                      'route' => '/ajaxupload',
                      'defaults' => array(
                        'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                        'action' => 'ajaxupload'
                      )
                    )
                  ),
                  'ajaxdelete' => array(
                    'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/register',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                    'action' => 'register'
                  )
                )
              ),
              'profile' => array(
                'type' => 'Laminas\Router\Http\Literal',
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
                        'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
                        'action'     => 'autoCompleteUser',
                      ),
                    ),
                  ),
                ),
              ),
              'fbshare' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbshare',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                    'action' => 'fbshare'
                  )
                )
              ),
              'fbrequest' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbrequest',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                    'action' => 'fbrequest'
                  )
                )
              ),
              'tweet' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/tweet',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                    'action' => 'tweet'
                  )
                )
              ),
              'google' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/google',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                    'action' => 'google'
                  )
                )
              ),
              'share' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/partager',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                    'action' => 'share'
                  )
                )
              ),
              'join-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/join-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                    'action' => 'joinTeam',
                  )
                )
              ),
              'create-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/creation-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                    'action' => 'createTeam',
                  )
                )
              ),
              'delete-invitation-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/delete-invitation[/:email]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                    'action' => 'deleteInvitationToTeam',
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
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/essayez-aussi',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                    'action' => 'bounce'
                  )
                )
              ),
              'terms' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/reglement',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                    'action' => 'terms'
                  )
                )
              ),
              'conditions' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/mentions-legales',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                    'action' => 'conditions'
                  )
                )
              ),
              'fangate' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fangate',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TradingCard::class,
                    'action' => 'fangate'
                  )
                )
              ),
              'prizes' => array(
                'type' => 'Laminas\Router\Http\Literal',
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
                'child_routes' => array(
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
                'type' => 'Laminas\Router\Http\Regex',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/register',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                    'action' => 'register'
                  )
                )
              ),
              'profile' => array(
                'type' => 'Laminas\Router\Http\Literal',
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
                        'controller' => \PlaygroundUser\Controller\Frontend\UserController::class,
                        'action'     => 'autoCompleteUser',
                      ),
                    ),
                  ),
                ),
              ),
              'fbshare' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbshare',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                    'action' => 'fbshare'
                  )
                )
              ),
              'fbrequest' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbrequest',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                    'action' => 'fbrequest'
                  )
                )
              ),
              'tweet' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/tweet',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                    'action' => 'tweet'
                  )
                )
              ),
              'google' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/google',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                    'action' => 'google'
                  )
                )
              ),
              'share' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/partager',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                    'action' => 'share'
                  )
                )
              ),
              'join-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/join-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                    'action' => 'joinTeam',
                  )
                )
              ),
              'create-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/creation-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                    'action' => 'createTeam',
                  )
                )
              ),
              'delete-invitation-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/delete-invitation[/:email]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                    'action' => 'deleteInvitationToTeam',
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
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/essayez-aussi',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                    'action' => 'bounce'
                  )
                )
              ),
              'terms' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/reglement',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                    'action' => 'terms'
                  )
                )
              ),
              'conditions' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/mentions-legales',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                    'action' => 'conditions'
                  )
                )
              ),
              'fangate' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fangate',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Mission::class,
                    'action' => 'fangate'
                  )
                )
              ),
              'prizes' => array(
                'type' => 'Laminas\Router\Http\Literal',
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
                'child_routes' => array(
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
                'type' => 'Laminas\Router\Http\Regex',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/jouer',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'play'
                  )
                )
              ),
              'result' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/resultat',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'result'
                  )
                )
              ),
              'register' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/register',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'register'
                  )
                )
              ),
              'fbshare' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbshare',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'fbshare'
                  )
                )
              ),
              'fbrequest' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbrequest',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'fbrequest'
                  )
                )
              ),
              'tweet' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/tweet',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'tweet'
                  )
                )
              ),
              'google' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/google',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'google'
                  )
                )
              ),
              'bounce' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/essayez-aussi',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'bounce'
                  )
                )
              ),
              'terms' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/reglement',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'terms'
                  )
                )
              ),
              'conditions' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/mentions-legales',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'conditions'
                  )
                )
              ),
              'fangate' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fangate',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'fangate'
                  )
                )
              ),
              'prizes' => array(
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/partager',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'share'
                  )
                )
              ),
              'join-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/join-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'joinTeam',
                  )
                )
              ),
              'create-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/creation-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'createTeam',
                  )
                )
              ),
              'delete-invitation-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/delete-invitation[/:email]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'deleteInvitationToTeam',
                  )
                )
              ),
              'invite-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/rejoins-ma-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Quiz::class,
                    'action' => 'inviteToTeam',
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
                'type' => 'Laminas\Router\Http\Regex',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
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
              'terms-optin' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/terms-optin',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'termsOptin'
                  )
                )
              ),
              'play' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/jouer',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'play'
                  )
                )
              ),
              'result' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/resultat',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'result'
                  )
                )
              ),
              'register' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/register',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'register'
                  )
                )
              ),
              'fbshare' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbshare',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'fbshare'
                  )
                )
              ),
              'fbrequest' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbrequest',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'fbrequest'
                  )
                )
              ),
              'tweet' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/tweet',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'tweet'
                  )
                )
              ),
              'google' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/google',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'google'
                  )
                )
              ),
              'bounce' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/essayez-aussi',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'bounce'
                  )
                )
              ),
              'terms' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/reglement',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'terms'
                  )
                )
              ),
              'conditions' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/mentions-legales',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'conditions'
                  )
                )
              ),
              'fangate' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fangate',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'fangate'
                  )
                )
              ),
              'prizes' => array(
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/partager',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'share'
                  )
                )
              ),
              'join-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/join-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'joinTeam',
                  )
                )
              ),
              'create-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/creation-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'createTeam',
                  )
                )
              ),
              'delete-invitation-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/delete-invitation[/:email]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'deleteInvitationToTeam',
                  )
                )
              ),
              'invite-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/rejoins-ma-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Lottery::class,
                    'action' => 'inviteToTeam',
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
                'type' => 'Laminas\Router\Http\Regex',
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
          'memory' => array(
            'type' => 'Segment',
            'options' => array(
              'route' => 'memory[/:id]',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                'action' => 'home'
              )
            ),
            'may_terminate' => true,
            'child_routes' => array(
              'index' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/index',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'index'
                  )
                )
              ),
              'ajaxforgotpassword' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/ajax-mot-passe-oublie',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action'     => 'ajaxforgot',
                  ),
                ),
              ),
              'resetpassword' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/reset-password/:userId/:token',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action'     => 'userreset',
                  ),
                  'constraints' => array(
                    'userId'  => '[0-9]+',
                    'token' => '[A-F0-9]+',
                  ),
                ),
              ),
              'login' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/connexion',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'login'
                  )
                )
              ),
              'user-register' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/inscription[/:socialnetwork]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'userregister'
                  )
                )
              ),
              'verification' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/verification',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action'     => 'check-token',
                  ),
                ),
              ),
              'optin' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/optin[/:gameId]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'optin'
                  )
                )
              ),
              'terms-optin' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/terms-optin',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'termsOptin'
                  )
                )
              ),
              'play' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/jouer',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'play'
                  )
                )
              ),
              'result' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/resultat',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'result'
                  )
                )
              ),
              'register' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/register',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'register'
                  )
                )
              ),
              'fbshare' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbshare',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'fbshare'
                  )
                )
              ),
              'fbrequest' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbrequest',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'fbrequest'
                  )
                )
              ),
              'tweet' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/tweet',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'tweet'
                  )
                )
              ),
              'google' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/google',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'google'
                  )
                )
              ),
              'bounce' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/essayez-aussi',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'bounce'
                  )
                )
              ),
              'terms' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/reglement',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'terms'
                  )
                )
              ),
              'conditions' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/mentions-legales',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'conditions'
                  )
                )
              ),
              'fangate' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fangate',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'fangate'
                  )
                )
              ),
              'prizes' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/lots',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
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
                        'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                        'action' => 'prize'
                      )
                    )
                  )
                )
              ),
              'share' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/partager',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'share'
                  )
                )
              ),
              'join-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/join-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'joinTeam',
                  )
                )
              ),
              'create-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/creation-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'createTeam',
                  )
                )
              ),
              'delete-invitation-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/delete-invitation[/:email]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'deleteInvitationToTeam',
                  )
                )
              ),
              'invite-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/rejoins-ma-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'inviteToTeam',
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
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action'     => 'leaderboard'
                  ),
                ),
              ),
              'other-routes' => array(
                'type' => 'Laminas\Router\Http\Regex',
                'priority' => -1000,
                'options' => array(
                  'regex' => '.*',
                  'spec' => '%url%',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Memory::class,
                    'action' => 'not-found'
                  )
                )
              )
            )
          ),
          'crossword' => array(
            'type' => 'Segment',
            'options' => array(
              'route' => 'crossword[/:id]',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                'action' => 'home'
              )
            ),
            'may_terminate' => true,
            'child_routes' => array(
              'index' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/index',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'index'
                  )
                )
              ),
              'ajaxforgotpassword' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/ajax-mot-passe-oublie',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action'     => 'ajaxforgot',
                  ),
                ),
              ),
              'resetpassword' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/reset-password/:userId/:token',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action'     => 'userreset',
                  ),
                  'constraints' => array(
                    'userId'  => '[0-9]+',
                    'token' => '[A-F0-9]+',
                  ),
                ),
              ),
              'login' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/connexion',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'login'
                  )
                )
              ),
              'user-register' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/inscription[/:socialnetwork]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'userregister'
                  )
                )
              ),
              'verification' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/verification',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action'     => 'check-token',
                  ),
                ),
              ),
              'optin' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/optin[/:gameId]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'optin'
                  )
                )
              ),
              'terms-optin' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/terms-optin',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'termsOptin'
                  )
                )
              ),
              'play' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/jouer',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'play'
                  )
                )
              ),
              'hint' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/hint',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'hint'
                  )
                )
              ),
              'words' => array(
                'type' => 'Laminas\Router\Http\Segment',
                'options' => array(
                  'route' => '/words/:length',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'getAllWords'
                  ),
                  'constraints' => array(
                    'locale'  => '[a-zA-Z]+',
                    'length' => '[0-9]+',
                  ),
                )
              ),
              'result' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/resultat',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'result'
                  )
                )
              ),
              'register' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/register',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'register'
                  )
                )
              ),
              'fbshare' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbshare',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'fbshare'
                  )
                )
              ),
              'fbrequest' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbrequest',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'fbrequest'
                  )
                )
              ),
              'tweet' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/tweet',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'tweet'
                  )
                )
              ),
              'google' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/google',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'google'
                  )
                )
              ),
              'bounce' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/essayez-aussi',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'bounce'
                  )
                )
              ),
              'terms' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/reglement',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'terms'
                  )
                )
              ),
              'conditions' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/mentions-legales',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'conditions'
                  )
                )
              ),
              'fangate' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fangate',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'fangate'
                  )
                )
              ),
              'prizes' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/lots',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
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
                        'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                        'action' => 'prize'
                      )
                    )
                  )
                )
              ),
              'share' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/partager',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'share'
                  )
                )
              ),
              'join-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/join-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'joinTeam',
                  )
                )
              ),
              'create-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/creation-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'createTeam',
                  )
                )
              ),
              'delete-invitation-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/delete-invitation[/:email]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'deleteInvitationToTeam',
                  )
                )
              ),
              'invite-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/rejoins-ma-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'inviteToTeam',
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
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action'     => 'leaderboard'
                  ),
                ),
              ),
              'other-routes' => array(
                'type' => 'Laminas\Router\Http\Regex',
                'priority' => -1000,
                'options' => array(
                  'regex' => '.*',
                  'spec' => '%url%',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\Crossword::class,
                    'action' => 'not-found'
                  )
                )
              )
            )
          ),
          'treasurehunt' => array(
            'type' => 'Segment',
            'options' => array(
              'route' => 'treasure-hunt[/:id]',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                'action' => 'home'
              )
            ),
            'may_terminate' => true,
            'child_routes' => array(
              'index' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/index',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'index'
                  )
                )
              ),
              'ajaxforgotpassword' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/ajax-mot-passe-oublie',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action'     => 'ajaxforgot',
                  ),
                ),
              ),
              'resetpassword' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/reset-password/:userId/:token',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action'     => 'userreset',
                  ),
                  'constraints' => array(
                    'userId'  => '[0-9]+',
                    'token' => '[A-F0-9]+',
                  ),
                ),
              ),
              'login' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/connexion',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'login'
                  )
                )
              ),
              'user-register' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/inscription[/:socialnetwork]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'userregister'
                  )
                )
              ),
              'verification' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/verification',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action'     => 'check-token',
                  ),
                ),
              ),
              'optin' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/optin[/:gameId]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'optin'
                  )
                )
              ),
              'terms-optin' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/terms-optin',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'termsOptin'
                  )
                )
              ),
              'play' => array(
                'type' => 'Laminas\Router\Http\Segment',
                'options' => array(
                  'route' => '/jouer[/:x][/:y]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'play'
                  )
                )
              ),
              'result' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/resultat',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'result'
                  )
                )
              ),
              'register' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/register',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'register'
                  )
                )
              ),
              'fbshare' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbshare',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'fbshare'
                  )
                )
              ),
              'fbrequest' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbrequest',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'fbrequest'
                  )
                )
              ),
              'tweet' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/tweet',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'tweet'
                  )
                )
              ),
              'google' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/google',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'google'
                  )
                )
              ),
              'bounce' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/essayez-aussi',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'bounce'
                  )
                )
              ),
              'terms' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/reglement',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'terms'
                  )
                )
              ),
              'conditions' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/mentions-legales',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'conditions'
                  )
                )
              ),
              'fangate' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fangate',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'fangate'
                  )
                )
              ),
              'prizes' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/lots',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
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
                        'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                        'action' => 'prize'
                      )
                    )
                  )
                )
              ),
              'share' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/partager',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'share'
                  )
                )
              ),
              'join-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/join-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'joinTeam',
                  )
                )
              ),
              'create-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/creation-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'createTeam',
                  )
                )
              ),
              'delete-invitation-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/delete-invitation[/:email]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'deleteInvitationToTeam',
                  )
                )
              ),
              'invite-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/rejoins-ma-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action' => 'inviteToTeam',
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
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
                    'action'     => 'leaderboard'
                  ),
                ),
              ),
              'other-routes' => array(
                'type' => 'Laminas\Router\Http\Regex',
                'priority' => -1000,
                'options' => array(
                  'regex' => '.*',
                  'spec' => '%url%',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\TreasureHunt::class,
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/jouer',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'play'
                  )
                )
              ),
              'result' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/resultat',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'result'
                  )
                )
              ),
              'register' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/register',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'register'
                  )
                )
              ),
              'fbshare' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbshare',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'fbshare'
                  )
                )
              ),
              'fbrequest' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbrequest',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'fbrequest'
                  )
                )
              ),
              'tweet' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/tweet',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'tweet'
                  )
                )
              ),
              'google' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/google',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'google'
                  )
                )
              ),
              'bounce' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/essayez-aussi',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'bounce'
                  )
                )
              ),
              'terms' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/reglement',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'terms'
                  )
                )
              ),
              'conditions' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/mentions-legales',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'conditions'
                  )
                )
              ),
              'fangate' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fangate',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'fangate'
                  )
                )
              ),
              'prizes' => array(
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/partager',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'share'
                  )
                )
              ),
              'join-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/join-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'joinTeam',
                  )
                )
              ),
              'create-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/creation-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'createTeam',
                  )
                )
              ),
              'delete-invitation-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/delete-invitation[/:email]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'deleteInvitationToTeam',
                  )
                )
              ),
              'invite-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/rejoins-ma-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\InstantWin::class,
                    'action' => 'inviteToTeam',
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
                'type' => 'Laminas\Router\Http\Regex',
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
                'type' => 'Laminas\Router\Http\Literal',
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
              'moderation' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/moderation/:postId[/:status]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'ajaxModeration'
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                    'type' => 'Laminas\Router\Http\Literal',
                    'options' => array(
                      'route' => '/ajaxupload',
                      'defaults' => array(
                        'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                        'action' => 'ajaxupload'
                      )
                    )
                  ),
                  'ajaxdelete' => array(
                    'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
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
                  'route' => '/vote[/:post][/:note][/:comment]',
                  // 'constraints' => array(
                  //     'note' => '[0-9]+'
                  // ),
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'ajaxVote',
                    'note' => 1,
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
              'unsharepost' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/unshare-post/:post',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'unsharePost'
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
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/resultat',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'result'
                  )
                )
              ),
              'register' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/register',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'register'
                  )
                )
              ),
              'fbshare' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbshare',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'fbshare'
                  )
                )
              ),
              'fbrequest' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fbrequest',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'fbrequest'
                  )
                )
              ),
              'tweet' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/tweet',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'tweet'
                  )
                )
              ),
              'google' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/google',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'google'
                  )
                )
              ),
              'bounce' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/essayez-aussi',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'bounce'
                  )
                )
              ),
              'terms' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/reglement',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'terms'
                  )
                )
              ),
              'conditions' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/mentions-legales',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'conditions'
                  )
                )
              ),
              'fangate' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/fangate',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'fangate'
                  )
                )
              ),
              'prizes' => array(
                'type' => 'Laminas\Router\Http\Literal',
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
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/partager',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'share'
                  )
                )
              ),
              'join-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/join-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'joinTeam',
                  )
                )
              ),
              'create-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/creation-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'createTeam',
                  )
                )
              ),
              'delete-invitation-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/delete-invitation[/:email]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'deleteInvitationToTeam',
                  )
                )
              ),
              'invite-to-team' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/rejoins-ma-team',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Frontend\PostVote::class,
                    'action' => 'inviteToTeam',
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
                'type' => 'Laminas\Router\Http\Regex',
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
          'other-routes' => array(
            'type' => 'Laminas\Router\Http\Regex',
            'priority' => -1000,
            'options' => array(
              'regex' => '.*',
              'spec' => '%url%',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Frontend\Game::class,
                'action' => 'not-found'
              )
            )
          )
        )
      ),

      'admin' => array(
        'child_routes' => array(
          'mission' => array(
            'type' => 'Laminas\Router\Http\Literal',
            'options' => array(
              'route' => '/mission',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Admin\Mission::class,
                'action' => 'list'
              )
            ),
            'child_routes' => array(
              'entry' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/:gameId/entries[/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Mission::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Mission::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Mission::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Mission::class,
                    'action' => 'download'
                  )
                )
              ),
              'draw' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/draw/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Mission::class,
                    'action' => 'draw'
                  )
                )
              ),
              'list' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/list[/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Mission::class,
                    'action' => 'list'
                  )
                )
              ),
              'create' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/create',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Mission::class,
                    'action' => 'create'
                  )
                )
              ),
              'edit' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/edit/:missionId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Mission::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Mission::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Mission::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Mission::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Mission::class,
                    'action' => 'desactivate',
                    'missionId' => 0
                  )
                )
              )
            )
          ),
          'quiz' => array(
            'type' => 'Laminas\Router\Http\Literal',
            'priority' => 1000,
            'options' => array(
              'route' => '/quiz',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Admin\Quiz::class,
                'action' => 'index'
              )
            ),
            'child_routes' => array(
              'entry' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/:gameId/entries[/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Quiz::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Quiz::class,
                    'action' => 'download'
                  )
                )
              ),
              'draw' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/draw/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Quiz::class,
                    'action' => 'draw'
                  )
                )
              ),
              'sortquestion' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/sortquestion/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Quiz::class,
                    'action' => 'sortQuestion'
                  )
                )
              ),
              'sortanswer' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/sortanswer/:gameId/:questionId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Quiz::class,
                    'action' => 'sortAnswer'
                  )
                )
              )
            )
          ),
          'lottery' => array(
            'type' => 'Laminas\Router\Http\Literal',
            'priority' => 1000,
            'options' => array(
              'route' => '/lottery',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Admin\Lottery::class,
                'action' => 'index'
              )
            ),
            'child_routes' => array(
              'entry' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/entry/:gameId[/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Lottery::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Lottery::class,
                    'action' => 'download'
                  )
                )
              ),
              'draw' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/draw/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Lottery::class,
                    'action' => 'draw'
                  )
                )
              )
            )
          ),
          'instantwin' => array(
            'type' => 'Laminas\Router\Http\Literal',
            'priority' => 1000,
            'options' => array(
              'route' => '/instantwin',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Admin\InstantWin::class,
                'action' => 'index'
              )
            ),
            'child_routes' => array(
              'entry' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/entry/:gameId[/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\InstantWin::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\InstantWin::class,
                    'action' => 'download'
                  )
                )
              )
            )
          ),
          'postvote' => array(
            'type' => 'Laminas\Router\Http\Literal',
            'priority' => 1000,
            'options' => array(
              'route' => '/postvote',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Admin\PostVote::class,
                'action' => 'index'
              )
            ),
            'child_routes' => array(
              'entry' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/entry/:gameId[/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\PostVote::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\PostVote::class,
                    'action' => 'download'
                  )
                )
              )
            )
          ),
          'tradingcard' => array(
            'type' => 'Laminas\Router\Http\Literal',
            'priority' => 1000,
            'options' => array(
              'route' => '/tradingcard',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Admin\TradingCard::class,
                'action' => 'index'
              )
            ),
            'child_routes' => array(
              'entry' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/entry/:gameId[/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TradingCard::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\TradingCard::class,
                    'action' => 'download'
                  )
                )
              ),
              'coo' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/coo/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TradingCard::class,
                    'action' => 'coo',
                    'gameId' => 0
                  )
                )
              ),
            )
          ),
          'memory' => array(
            'type' => 'Laminas\Router\Http\Literal',
            'priority' => 1000,
            'options' => array(
              'route' => '/memory',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Admin\Memory::class,
                'action' => 'index'
              )
            ),
            'child_routes' => array(
              'entry' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/entry/:gameId[/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Memory::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Memory::class,
                    'action' => 'download'
                  )
                )
              ),
            )
          ),
          'crossword' => array(
            'type' => 'Laminas\Router\Http\Literal',
            'priority' => 1000,
            'options' => array(
              'route' => '/crossword',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Admin\Crossword::class,
                'action' => 'index'
              )
            ),
            'child_routes' => array(
              'entry' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/entry/:gameId[/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Crossword::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Crossword::class,
                    'action' => 'download'
                  )
                )
              ),
            )
          ),
          'treasurehunt' => array(
            'type' => 'Laminas\Router\Http\Literal',
            'priority' => 1000,
            'options' => array(
              'route' => '/treasurehunt',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
                'action' => 'index'
              )
            ),
            'child_routes' => array(
              'cors' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/cors',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
                    'action' => 'cors'
                  )
                )
              ),
              'entry' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/entry/:gameId[/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
                    'action' => 'download'
                  )
                )
              ),
            )
          ),
          'playgroundgame' => array(
            'type' => 'Laminas\Router\Http\Literal',
            'priority' => 1000,
            'options' => array(
              'route' => '/game',
              'defaults' => array(
                'controller' => PlaygroundGame\Controller\Admin\Game::class,
                'action' => 'index'
              )
            ),
            'child_routes' => array(
              'list' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/list/:type/:filter[/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Game::class,
                    'action' => 'list',
                    'type' => 'createdAt',
                    'filter' => 'DESC'
                  )
                )
              ),
              'create' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/create',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Game::class,
                    'action' => 'create'
                  )
                )
              ),
              'player-form' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/player-form/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Game::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Game::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Game::class,
                    'action' => 'import'
                  )
                )
              ),
              'create-tradingcard' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/create-tradingcard',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TradingCard::class,
                    'action' => 'createTradingcard'
                  )
                )
              ),
              'edit-tradingcard' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/edit-tradingcard/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TradingCard::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\TradingCard::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\TradingCard::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\TradingCard::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\TradingCard::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\TradingCard::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\TradingCard::class,
                    'action' => 'removeModel',
                    'modelId' => 0
                  )
                )
              ),
              'create-lottery' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/create-lottery',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Lottery::class,
                    'action' => 'createLottery'
                  )
                )
              ),
              'edit-lottery' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/edit-lottery/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Lottery::class,
                    'action' => 'editLottery',
                    'gameId' => 0
                  )
                )
              ),
              'create-treasurehunt' => array(
                'type' => 'Laminas\Router\Http\Segment',
                'options' => array(
                  'route' => '/create-treasurehunt/:treasureHuntId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
                    'action' => 'createTreasurehunt',
                    'treasureHuntId' => 0
                  )
                )
              ),
              'edit-treasurehunt' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/edit-treasurehunt/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
                    'action' => 'editTreasurehunt',
                    'gameId' => 0
                  )
                )
              ),
              'treasurehunt-puzzle-list' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/treasurehunt-puzzle-list/:gameId[/:filter][/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
                    'action' => 'editPuzzle',
                    'gameId' => 0,
                    'puzzleId' => 0
                  )
                )
              ),
              'treasurehunt-puzzle-delete-image' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/treasurehunt-puzzle-delete-image/:gameId/:puzzleId/:imageId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
                    'action' => 'puzzleDeleteImage',
                    'gameId' => 0,
                    'puzzleId' => 0,
                    'imageId' => 0,
                  )
                )
              ),
              'treasurehunt-puzzle-delete-refimage' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/treasurehunt-puzzle-delete-refimage/:gameId/:puzzleId/:imageId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
                    'action' => 'puzzleDeleteRefImage',
                    'gameId' => 0,
                    'puzzleId' => 0,
                    'imageId' => 0,
                  )
                )
              ),
              'treasurehunt-puzzle-remove' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/treasurehunt-puzzle-remove/:puzzleId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
                    'action' => 'removePuzzle',
                    'puzzleId' => 0
                  )
                )
              ),
              'treasurehunt-piece-list' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/treasurehunt-piece-list/:puzzleId[/:filter][/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
                    'action' => 'listPieces',
                    'puzzleId' => 0,
                    'filter' => 'DESC'
                  ),
                  'constraints' => array(
                    'filter' => '[a-zA-Z][a-zA-Z0-9_-]*'
                  )
                )
              ),
              'treasurehunt-piece-add' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/treasurehunt-piece-add/:puzzleId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
                    'action' => 'addPiece',
                    'puzzleId' => 0
                  )
                )
              ),
              'treasurehunt-piece-edit' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/treasurehunt-piece-edit/:puzzleId/:pieceId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
                    'action' => 'editPiece',
                    'puzzleId' => 0,
                    'pieceId' => 0
                  )
                )
              ),
              'treasurehunt-piece-remove' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/treasurehunt-puzzle-remove/:pieceId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
                    'action' => 'removePiece',
                    'pieceId' => 0
                  )
                )
              ),
              'treasure-hunt-areapicker' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/treasure-hunt-areapicker',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\TreasureHunt::class,
                    'action' => 'areapicker'
                  )
                )
              ),
              'create-instantwin' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/create-instantwin',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\InstantWin::class,
                    'action' => 'createInstantWin'
                  )
                )
              ),
              'edit-instantwin' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/edit-instantwin/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\InstantWin::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\InstantWin::class,
                    'action' => 'listOccurrence',
                    'gameId' => 0,
                    'filter' => 'DESC'
                  ),
                  'constraints' => array(
                    'filter' => '[a-zA-Z][a-zA-Z0-9_-]*'
                  )
                )
              ),
              'instantwin-occurrence-remove-all' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/instantwin-occurrence-remove-all/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\InstantWin::class,
                    'action' => 'removeAllOccurrences',
                    'gameId' => 0
                  )
                )
              ),
              'instantwin-occurrences-import' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/instantwin-occurrences-import/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\InstantWin::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\InstantWin::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\InstantWin::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\InstantWin::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\InstantWin::class,
                    'action' => 'removeOccurrence',
                    'occurrenceId' => 0
                  )
                )
              ),
              'create-quiz' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/create-quiz',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Quiz::class,
                    'action' => 'createQuiz'
                  )
                )
              ),
              'edit-quiz' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/edit-quiz/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Quiz::class,
                    'action' => 'editQuiz',
                    'gameId' => 0
                  )
                )
              ),
              'create-postvote' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/create-postvote',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\PostVote::class,
                    'action' => 'createPostVote'
                  )
                )
              ),
              'edit-postvote' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/edit-postvote/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\PostVote::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\PostVote::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\PostVote::class,
                    'action' => 'modList'
                  )
                )
              ),
              'postvote-moderation-edit' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/postvote-moderation-edit/:postId[/:status]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\PostVote::class,
                    'action' => 'moderationEdit'
                  )
                )
              ),
              'postvote-push' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/postvote-push/:postId[/:pushed]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\PostVote::class,
                    'action' => 'push'
                  )
                )
              ),
              'create-memory' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/create-memory',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Memory::class,
                    'action' => 'createMemory'
                  )
                )
              ),
              'edit-memory' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/edit-memory/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Memory::class,
                    'action' => 'editMemory',
                    'gameId' => 0
                  )
                )
              ),
              'memory-card-list' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/memory-card-list/:gameId[/:filter][/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Memory::class,
                    'action' => 'listCard',
                    'gameId' => 0,
                    'filter' => 'DESC'
                  ),
                  'constraints' => array(
                    'filter' => '[a-zA-Z][a-zA-Z0-9_-]*'
                  )
                )
              ),
              'memory-card-add' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/memory-card-add/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Memory::class,
                    'action' => 'addCard',
                    'gameId' => 0
                  )
                )
              ),
              'memory-card-edit' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/memory-card-edit/:gameId/:cardId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Memory::class,
                    'action' => 'editCard',
                    'gameId' => 0,
                    'cardId' => 0
                  )
                )
              ),
              'memory-card-remove' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/memory-card-remove/:cardId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Memory::class,
                    'action' => 'removeCard',
                    'cardId' => 0
                  )
                )
              ),
              'create-crossword' => array(
                'type' => 'Laminas\Router\Http\Literal',
                'options' => array(
                  'route' => '/create-crossword',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Crossword::class,
                    'action' => 'createCrossword'
                  )
                )
              ),
              'edit-crossword' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/edit-crossword/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Crossword::class,
                    'action' => 'editCrossword',
                    'gameId' => 0
                  )
                )
              ),
              'crossword-word-list' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/crossword-word-list/:gameId[/:filter][/:p]',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Crossword::class,
                    'action' => 'listWord',
                    'gameId' => 0,
                    'filter' => 'DESC'
                  ),
                  'constraints' => array(
                    'filter' => '[a-zA-Z][a-zA-Z0-9_-]*'
                  )
                )
              ),
              'crossword-word-add' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/crossword-word-add/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Crossword::class,
                    'action' => 'addWord',
                    'gameId' => 0
                  )
                )
              ),
              'crossword-word-edit' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/crossword-word-edit/:gameId/:wordId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Crossword::class,
                    'action' => 'editWord',
                    'gameId' => 0,
                    'wordId' => 0
                  )
                )
              ),
              'crossword-word-remove' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/crossword-word-remove/:wordId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Crossword::class,
                    'action' => 'removeWord',
                    'wordId' => 0
                  )
                )
              ),
              'entry' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/entry/:gameId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Game::class,
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
                        'controller' => PlaygroundGame\Controller\Admin\Game::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Quiz::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Quiz::class,
                    'action' => 'addQuestion',
                    'quizId' => 0
                  )
                )
              ),
              'quiz-question-edit' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/quiz-question-edit/:quizId/:questionId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Quiz::class,
                    'action' => 'editQuestion',
                    'questionId' => 0,
                    'quizId' => 0
                  )
                )
              ),
              'quiz-question-remove' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/quiz-question-remove/:questionId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\Quiz::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Game::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Game::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Game::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Game::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\PrizeCategory::class,
                    'action' => 'list'
                  )
                )
              ),

              'prize-category-add' => array(
                'type' => 'Segment',
                'options' => array(
                  'route' => '/prize-category-add/:prizeCategoryId',
                  'defaults' => array(
                    'controller' => PlaygroundGame\Controller\Admin\PrizeCategory::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\PrizeCategory::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Mission::class,
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
                    'controller' => PlaygroundGame\Controller\Admin\Mission::class,
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
        'use_route_match' => true,
        'pages' => array(
          'quiz' => array(
            'label' => 'Quiz',
            'route' => 'quiz',
            'use_route_match' => true,
          ),
          'quiz_play' => array(
            'label' => 'Quiz',
            'route' => 'quiz/play',
            'use_route_match' => true,
          ),
          'quiz_result' => array(
            'label' => 'Quiz',
            'route' => 'quiz/result',
            'use_route_match' => true,
          ),
          'quiz_bounce' => array(
            'label' => 'Quiz',
            'route' => 'quiz/bounce',
            'use_route_match' => true,
          ),
          'quiz_terms' => array(
            'label' => 'Quiz',
            'route' => 'quiz/terms',
            'use_route_match' => true,
          ),
          'quiz_conditions' => array(
            'label' => 'Quiz',
            'route' => 'quiz/conditions',
            'use_route_match' => true,
          ),
          'lottery' => array(
            'label' => 'Tirage au sort',
            'route' => 'lottery',
            'use_route_match' => true,
          ),
          'lottery_result' => array(
            'label' => 'Tirage au sort',
            'route' => 'lottery/result',
            'use_route_match' => true,
          ),
          'lottery_bounce' => array(
            'label' => 'Tirage au sort',
            'route' => 'lottery/bounce',
            'use_route_match' => true,
          ),
          'lottery_terms' => array(
            'label' => 'Tirage au sort',
            'route' => 'lottery/terms',
            'use_route_match' => true,
          ),
          'lottery_conditions' => array(
            'label' => 'Tirage au sort',
            'route' => 'lottery/conditions',
            'use_route_match' => true,
          ),
          'instanwin' => array(
            'label' => 'Instant gagnant',
            'route' => 'instantwin',
            'use_route_match' => true,
          ),
          'instanwin_play' => array(
            'label' => 'Instant gagnant',
            'route' => 'instantwin/play',
            'use_route_match' => true,
          ),
          'instanwin_result' => array(
            'label' => 'Instant gagnant',
            'route' => 'instantwin/result',
            'use_route_match' => true,
          ),
          'instanwin_bounce' => array(
            'label' => 'Instant gagnant',
            'route' => 'instantwin/bounce',
            'use_route_match' => true,
          ),
          'instanwin_terms' => array(
            'label' => 'Instant gagnant',
            'route' => 'instantwin/terms',
            'use_route_match' => true,
          ),
          'instanwin_conditions' => array(
            'label' => 'Instant gagnant',
            'route' => 'instantwin/conditions',
            'use_route_match' => true,
          ),
          'postvote' => array(
            'label' => 'Post & vote',
            'route' => 'postvote',
            'use_route_match' => true,
          ),
          'postvote_play' => array(
            'label' => 'Post & vote',
            'route' => 'postvote/play',
            'use_route_match' => true,
          ),
          'postvote_preview' => array(
            'label' => 'Post & vote',
            'route' => 'postvote/preview',
            'use_route_match' => true,
          ),
          'postvote_result' => array(
            'label' => 'Post & vote',
            'route' => 'postvote/result',
            'use_route_match' => true,
          ),
          'postvote_post' => array(
            'label' => 'Post & vote',
            'route' => 'postvote/post',
            'use_route_match' => true,
          ),
          'postvote_list' => array(
            'label' => 'Post & vote',
            'route' => 'postvote/list'
          ),
          'postvote_bounce' => array(
            'label' => 'Post & vote',
            'route' => 'postvote/bounce',
            'use_route_match' => true,
          )
        )
      ),
      array(
        'label' => 'Thématiques',
        'route' => 'thematiques/:id',
        'controller' => 'playgroundgame_prizecategories',
        'action' => 'index',
        'use_route_match' => true,
      )
    ),
    'admin' => array(
      'playgroundgame' => array(
        'label' => 'Games',
        'route' => 'admin/playgroundgame/list',
        'resource' => 'game',
        'privilege' => 'list',
        'target' => 'nav-icon icon-game-controller',
        'use_route_match' => true,
        'pages' => array(
          'list' => array(
            'label' => 'Games list',
            'route' => 'admin/playgroundgame/list',
            'resource' => 'game',
            'privilege' => 'list',
            'use_route_match' => true,
            'pages' => [
              'edit-instantwin' => array(
                'label' => 'Editer un instant gagnant',
                'route' => 'admin/playgroundgame/edit-instantwin',
                'privilege' => 'edit',
                'use_route_match' => true,
                'pages' => [
                  'instantwin-occurence-list' => array(
                    'label' => 'Liste des instant gagnants',
                    'route' => 'admin/playgroundgame/instantwin-occurrence-list',
                    'privilege' => 'list',
                    'use_route_match' => true,
                    'pages' => array(
                      'instantwin-occurrence-add' => array(
                        'label' => 'Add new instant win',
                        'route' => 'admin/playgroundgame/instantwin-occurrence-add',
                        'privilege' => 'add',
                        'use_route_match' => true,
                      ),
                      'instantwin-occurrence-edit' => array(
                        'label' => 'Editer un instant gagnant',
                        'route' => 'admin/playgroundgame/instantwin-occurrence-edit',
                        'privilege' => 'edit',
                        'use_route_match' => true,
                      ),
                      'instantwin-code-occurrences-add' => array(
                        'label' => 'Add new instant win',
                        'route' => 'admin/playgroundgame/instantwin-code-occurrences-add',
                        'privilege' => 'add',
                        'use_route_match' => true,
                      ),
                      'instantwin-occurrences-export' => array(
                        'route' => 'admin/playgroundgame/instantwin-occurrences-export',
                        'label' => 'Export to csv',
                        'privilege' => 'download',
                        'use_route_match' => true,
                      )
                    )
                  ),
                  'entry-instantwin' => array(
                    'label' => 'Participants',
                    'route' => 'admin/instantwin/entry',
                    'privilege' => 'list',
                    'use_route_match' => true,
                  ),
                ]
              ),
              'edit-lottery' => array(
                'label' => 'Editer un tirage au sort',
                'route' => 'admin/playgroundgame/edit-lottery',
                'privilege' => 'edit',
                'use_route_match' => true,
                'pages' => [
                  'entry-lottery' => array(
                    'label' => 'Participants',
                    'route' => 'admin/lottery/entry',
                    'privilege' => 'list',
                    'use_route_match' => true,
                  ),
                ]
              ),
              'edit-quiz' => array(
                'label' => 'Editer un quiz',
                'route' => 'admin/playgroundgame/edit-quiz',
                'privilege' => 'edit',
                'use_route_match' => true,
                'pages' => [
                  'quiz-question-list' => array(
                    'label' => 'Liste des questions',
                    'route' => 'admin/playgroundgame/quiz-question-list',
                    'privilege' => 'list',
                    'use_route_match' => true,
                    'pages' => array(
                      'quiz-question-add' => array(
                        'label' => 'Ajouter des questions',
                        'route' => 'admin/playgroundgame/quiz-question-add',
                        'privilege' => 'add',
                        'use_route_match' => true,
                      ),
                      'quiz-question-edit' => array(
                        'label' => 'Editer une question',
                        'route' => 'admin/playgroundgame/quiz-question-edit',
                        'privilege' => 'edit',
                        'use_route_match' => true,
                      )
                    )
                  ),
                  'entry-quiz' => array(
                    'label' => 'Participants',
                    'route' => 'admin/quiz/entry',
                    'privilege' => 'list',
                    'use_route_match' => true,
                  ),
                ]
              ),
              'edit-postvote' => array(
                'label' => 'Editer un Post & Vote',
                'route' => 'admin/playgroundgame/edit-postvote',
                'privilege' => 'edit',
                'use_route_match' => true,
                'pages' => [
                  'postvote-form' => array(
                    'label' => 'Formulaire du Post & vote',
                    'route' => 'admin/playgroundgame/postvote-form',
                    'privilege' => 'list',
                    'use_route_match' => true,
                  ),
                  'entry-postvote' => array(
                    'label' => 'Participants',
                    'route' => 'admin/postvote/entry',
                    'privilege' => 'list',
                    'use_route_match' => true,
                  ),
                ]
              ),
              'edit-tradingcard' => array(
                'label' => 'Add a new tradingcard game',
                'route' => 'admin/playgroundgame/edit-tradingcard',
                'resource' => 'game',
                'privilege' => 'add',
                'use_route_match' => true,
                'pages' => [
                  'tradingcard-model-list' => [
                    'label' => 'Liste des modèles',
                    'route' => 'admin/playgroundgame/tradingcard-model-list',
                    'privilege' => 'list',
                    'use_route_match' => true,
                    'pages' => [
                      'tradingcard-model-add' => [
                        'label' => 'Ajouter un modèle',
                        'route' => 'admin/playgroundgame/tradingcard-model-add',
                        'privilege' => 'add',
                        'use_route_match' => true,
                      ],
                      'tradingcard-model-edit' => [
                        'label' => 'Editer un modèle',
                        'route' => 'admin/playgroundgame/tradingcard-model-edit',
                        'privilege' => 'edit',
                        'use_route_match' => true,
                      ]
                    ]
                  ],
                  'entry-tradingcard' => array(
                    'label' => 'Participants',
                    'route' => 'admin/tradingcard/entry',
                    'privilege' => 'list',
                    'use_route_match' => true,
                  ),
                ]
              ),
              'edit-memory' => array(
                'label' => 'Add a new memory game',
                'route' => 'admin/playgroundgame/edit-memory',
                'resource' => 'game',
                'privilege' => 'add',
                'use_route_match' => true,
                'pages' => [
                  'memory-card-list' => [
                    'label' => 'Liste des cartes',
                    'route' => 'admin/playgroundgame/memory-card-list',
                    'privilege' => 'list',
                    'use_route_match' => true,
                    'pages' => [
                      'memory-card-add' => [
                        'label' => 'Ajouter une carte',
                        'route' => 'admin/playgroundgame/memory-card-add',
                        'privilege' => 'add',
                        'use_route_match' => true,
                      ],
                      'memory-card-edit' => [
                        'label' => 'Editer une carte',
                        'route' => 'admin/playgroundgame/memory-card-edit',
                        'privilege' => 'edit',
                        'use_route_match' => true,
                      ]
                    ]
                  ],
                  'entry-memory' => array(
                    'label' => 'Participants',
                    'route' => 'admin/memory/entry',
                    'privilege' => 'list',
                    'use_route_match' => true,
                  ),
                ]
              ),
              'edit-crossword' => array(
                'label' => 'Edit the crossword',
                'route' => 'admin/playgroundgame/edit-crossword',
                'resource' => 'game',
                'privilege' => 'edit',
                'use_route_match' => true,
                'pages' => [
                  'crossword-word-list' => [
                    'label' => 'Liste des mots',
                    'route' => 'admin/playgroundgame/crossword-word-list',
                    'privilege' => 'list',
                    'use_route_match' => true,
                    'pages' => [
                      'crossword-word-add' => [
                        'label' => 'Ajouter un mot',
                        'route' => 'admin/playgroundgame/crossword-word-add',
                        'privilege' => 'add',
                        'use_route_match' => true,
                      ],
                      'crossword-word-edit' => [
                        'label' => 'Editer un mot',
                        'route' => 'admin/playgroundgame/crossword-word-edit',
                        'privilege' => 'edit',
                        'use_route_match' => true,
                      ]
                    ]
                  ],
                  'entry-crossword' => array(
                    'label' => 'Participants',
                    'route' => 'admin/crossword/entry',
                    'privilege' => 'list',
                    'use_route_match' => true,
                  ),
                ]
              ),
              'edit-treasurehunt' => array(
                'label' => 'Add a new treasure hunt game',
                'route' => 'admin/playgroundgame/edit-treasurehunt',
                'resource' => 'game',
                'privilege' => 'add',
                'use_route_match' => true,
                'pages' => [
                  'treasurehunt-puzzle-list' => [
                    'label' => 'Liste des puzzles',
                    'route' => 'admin/playgroundgame/treasurehunt-puzzle-list',
                    'privilege' => 'list',
                    'use_route_match' => true,
                    'pages' => [
                      'treasurehunt-puzzle-add' => [
                        'label' => 'Ajouter un puzzle',
                        'route' => 'admin/playgroundgame/treasurehunt-puzzle-add',
                        'privilege' => 'add',
                        'use_route_match' => true,
                      ],
                      'treasurehunt-puzzle-edit' => [
                        'label' => 'Editer un puzzle',
                        'route' => 'admin/playgroundgame/treasurehunt-puzzle-edit',
                        'privilege' => 'edit',
                        'use_route_match' => true,
                      ]
                    ]
                  ],
                  'entry-treasurehunt' => array(
                    'label' => 'Participants',
                    'route' => 'admin/treasurehunt/entry',
                    'privilege' => 'list',
                    'use_route_match' => true,
                  ),
                ]
              ),
              'edit-mission' => array(
                'label' => 'Edit the mission',
                'route' => 'admin/playgroundgame/edit-mission',
                'resource' => 'game',
                'privilege' => 'add',
                'use_route_match' => true,
                'pages' => [
                  'entry-mission' => array(
                    'label' => 'Participants',
                    'route' => 'admin/mission/entry',
                    'privilege' => 'list',
                    'use_route_match' => true,
                  ),
                ]
              ),
              'list-prizecategory' => array(
                'label' => 'Manage categories gain',
                'route' => 'admin/playgroundgame/prize-category-list',
                'resource' => 'game',
                'privilege' => 'prizecategory_list',
                'use_route_match' => true,
              ),
              // 'list-postvotemod' => array(
              //     'label'     => 'Posts en attente de modération',
              //     'route'     => 'admin/playgroundgame/postvote-mod-list',
              //     'resource'  => 'game',
              //     'privilege' => 'moderation',
              //     'use_route_match' => true,
              // ),
            ]
          ),
          'create-lottery' => array(
            'label' => 'Add new lottery',
            'route' => 'admin/playgroundgame/create-lottery',
            'resource' => 'game',
            'privilege' => 'add',
            'use_route_match' => true,
          ),
          'create-instantwin' => array(
            'label' => 'Add new instant win',
            'route' => 'admin/playgroundgame/create-instantwin',
            'resource' => 'game',
            'privilege' => 'add',
            'use_route_match' => true,
          ),
          'create-quiz' => array(
            'label' => 'Add new quiz',
            'route' => 'admin/playgroundgame/create-quiz',
            'resource' => 'game',
            'privilege' => 'add',
            'use_route_match' => true,
          ),
          'create-postvote' => array(
            'label' => 'Add new post & vote',
            'route' => 'admin/playgroundgame/create-postvote',
            'resource' => 'game',
            'privilege' => 'add',
            'use_route_match' => true,
          ),
          'create-tradingcard' => array(
            'label' => 'Add new trading card',
            'route' => 'admin/playgroundgame/create-tradingcard',
            'resource' => 'game',
            'privilege' => 'add',
            'use_route_match' => true,
          ),
          'create-treasurehunt' => array(
            'label' => 'Add a new treasure hunt game',
            'route' => 'admin/playgroundgame/create-treasurehunt',
            'resource' => 'game',
            'privilege' => 'add',
            'use_route_match' => true,
          ),
          'create-memory' => array(
            'label' => 'Add a new memory game',
            'route' => 'admin/playgroundgame/create-memory',
            'resource' => 'game',
            'privilege' => 'add',
            'use_route_match' => true,
          ),
          'create-crossword' => array(
            'label' => 'Add a new crossword game',
            'route' => 'admin/playgroundgame/create-crossword',
            'resource' => 'game',
            'privilege' => 'add',
            'use_route_match' => true,
          ),
          'create-mission' => array(
            'label' => 'Add new mission',
            'route' => 'admin/playgroundgame/create-mission',
            'resource' => 'game',
            'privilege' => 'add',
            'use_route_match' => true,
          ),
        )
      )
    )
  )
);
