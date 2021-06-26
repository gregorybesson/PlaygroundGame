<?php

namespace PlaygroundGame\View\Helper;

use Laminas\View\Helper\AbstractHelper;
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
    public function __invoke($identifier = null)
    {
        $game = $this->getGameService()->getGameMapper()->findByIdentifier($identifier);

        return $game;
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
