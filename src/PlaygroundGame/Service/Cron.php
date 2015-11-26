<?php

namespace PlaygroundGame\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundGame\Options\ModuleOptions;
use PlaygroundGame\Mapper\GameInterface as GameMapperInterface;

class Cron extends EventProvider implements ServiceManagerAwareInterface
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
            $entries = $gameService->getEntryMapper()->findPlayersWithOneEntryBy($game);
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

    /**
     * getGameMapper
     *
     * @return GameMapperInterface
     */
    public function getGameMapper()
    {
        if (null === $this->gameMapper) {
            $this->gameMapper = $this->getServiceManager()->get('playgroundgame_game_mapper');
        }

        return $this->gameMapper;
    }

    /**
     * setGameMapper
     *
     * @param  GameMapperInterface $gameMapper
     * @return Cron
     */
    public function setGameMapper(GameMapperInterface $gameMapper)
    {
        $this->gameMapper = $gameMapper;

        return $this;
    }

    /**
     * getEntryMapper
     *
     * @return EntryMapperInterface
     */
    public function getEntryMapper()
    {
        if (null === $this->entryMapper) {
            $this->entryMapper = $this->getServiceManager()->get('playgroundgame_entry_mapper');
        }

        return $this->entryMapper;
    }

    /**
     * setEntryMapper
     *
     * @param  EntryMapperInterface $entryMapper
     * @return Cron
     */
    public function setEntryMapper($entryMapper)
    {
        $this->entryMapper = $entryMapper;

        return $this;
    }

    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()->get('playgroundgame_module_options'));
        }

        return $this->options;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return Cron
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
