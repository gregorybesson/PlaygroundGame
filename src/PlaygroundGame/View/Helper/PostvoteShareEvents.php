<?php

namespace PlaygroundGame\View\Helper;

use Zend\View\Helper\AbstractHelper;

class PostvoteShareEvents extends AbstractHelper
{
    protected $service = null;

    public function __construct(\PlaygroundGame\Service\PostVote $service)
    {
        $this->service = $service;
    }

    public function __invoke($identifier)
    {
        // this is possible to create a specific game design in /design/frontend/default/custom.
        //It will precede all others templates.
        $templatePathResolver = $this->service->getServiceManager()->get('Zend\View\Resolver\TemplatePathStack');
        $l = $templatePathResolver->getPaths();
        $templatePathResolver->addPath($l[0].'custom/'.$identifier);
        $game = $this->service->checkGame($identifier, false);
        $comments = $this->service->getCommentsForPostvote($game);

        return $this->getView()->render('playground-game/post-vote/widget/share-events', array('comments' => $comments));
    }
}