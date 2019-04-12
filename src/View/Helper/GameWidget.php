<?php

namespace PlaygroundGame\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Interop\Container\ContainerInterface;

class GameWidget extends AbstractHelper
{
    /**
     * @var GameService
     */
    protected $gameService;

    public function __construct(\PlaygroundGame\Service\Game $gameService) 
    {
        return $this->gameService = $gameService;
    }

    /**
     * __invoke
     *
     * @access public
     * @param  array  $options array of options
     * @return string
     */
    public function __invoke($displayHome = null, $classType = '', $order = '', $nbItems = 5)
    {
        $games = $this->getGameService()->getActiveGames($displayHome, $classType, $order);

        return $games;
    }

    /**
     * Get gameService.
     *
     * @return GameService
     */
    public function getGameService()
    {
        return $this->gameService;
    }
}
