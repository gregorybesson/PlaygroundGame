<?php

namespace PlaygroundGame\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use PlaygroundGame\Options\ModuleOptions;
use PlaygroundGame\Mapper\GameInterface as GameMapperInterface;

class Cron extends Game implements ServiceManagerAwareInterface
{
    /**
     * @var GameMapperInterface
     */
    protected $gameMapper;

    /**
     * @var EntryMapperInterface
     */
    protected $entryMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var UserServiceOptionsInterface
     */
    protected $options;

    public static function cronMail()
    {
        $configuration = require 'config/application.config.php';
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
        $sm = new \Zend\ServiceManager\ServiceManager(new \Zend\Mvc\Service\ServiceManagerConfig($smConfig));
        $sm->setService('ApplicationConfig', $configuration);
        $sm->get('ModuleManager')->loadModules();
        $sm->get('Application')->bootstrap();

        $mailService = $sm->get('playgrounduser_message');
        $gameService = $sm->get('playgroundgame_quiz_service');

        $from    = "admin@playground.fr";
        $subject = "sujet game";

        $to = "gbesson@test.com";

        $game = $gameService->checkGame('qooqo');

        // On recherche les joueurs qui n'ont pas partagé leur qquiz après avoir joué
        // entry join user join game : distinct user et game et game_entry = 0 et updated_at <= jour-1 et > jour - 2

        $message = $mailService->createTextMessage(
            $from,
            $to,
            $subject,
            'playground-game/email/share_reminder',
            array('game' => $game)
        );

        $mailService->send($message);
    }

    public static function instantWinEmail()
    {
        $configuration = require 'config/application.config.php';
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
        $sm = new \Zend\ServiceManager\ServiceManager(new \Zend\Mvc\Service\ServiceManagerConfig($smConfig));
        $sm->setService('ApplicationConfig', $configuration);
        $sm->get('ModuleManager')->loadModules();
        $sm->get('Application')->bootstrap();

        $mailService = $sm->get('playgrounduser_message');
        $gameService = $sm->get('playgroundgame_instantwin_service');

        $from    = "admin@playground.fr";
        $subject = "Votre jeu Instant gagnant";

        // Je recherche les jeux instantwin en cours
        $games = $gameService->getActiveGames(false, 'instantwin');

        // Je recherche les joueurs qui ont deja joué une seule fois au jeu mais pas rejoué dans le laps autorisé
        $arrayUsers = array();
        foreach ($games as $game) {
            $limitScale = $game->getPlayLimitScale();
            $limitDate = $this->getLimitDate($limitScale);
            $entries = $gameService->getEntryMapper()->findPlayersWithOneEntryBy($game, $limitDate);
            foreach ($entries as $e) {
                $arrayUsers[$e->getUser()->getId()]['user'] = $e->getUser();
                $arrayUsers[$e->getUser()->getId()]['game'] = $game;
            }
        }

        // J'envoie un mail de relance
        foreach ($arrayUsers as $k => $entry) {
            $user = $entry['user'];
            $game = $entry['game'];
            $message = $mailService->createHtmlMessage(
                $from,
                $user->getEmail(),
                $subject,
                'playground-game/email/game_instantwin_reminder',
                array('game' => $game, 'user' => $user)
            );
            $mailService->send($message);
        }
    }
}
