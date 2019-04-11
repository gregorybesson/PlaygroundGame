<?php
namespace PlaygroundGame\View\Helper;

use PlaygroundGame\View\Helper\GameWidget;
use Interop\Container\ContainerInterface;

class GameWidgetFactory
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ContainerInterface $container)
    {
        $gameService = $container->get(\PlaygroundGame\Service\Game::class);
        return new GameWidget($gameService);
    }
}