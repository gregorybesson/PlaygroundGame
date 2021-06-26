<?php

namespace PlaygroundGame\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Interop\Container\ContainerInterface;

class NextGameWidget extends AbstractHelper
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
    public function __invoke($dateStart = null, $dateEnd = null, $classType = null, $cost = null, $order = null, $dir = 'DESC')
    {
        $games = $this->getGameService()->getNextGames($dateStart, $dateEnd, $classType, $cost, $order, $dir);

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
