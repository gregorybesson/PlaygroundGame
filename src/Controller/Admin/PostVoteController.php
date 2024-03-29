<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Service\Game as AdminGameService;
use PlaygroundGame\Entity\PostVote;
use Laminas\View\Model\ViewModel;

class PostVoteController extends GameController
{
    /**
     * @var \PlaygroundGame\Service\Game
     */
    protected $adminGameService;


    public function formAction()
    {
        $this->checkGame();

        $navigation = $this->getServiceLocator()->get('ViewHelperManager')->get('navigation');
        $page = $navigation('admin_navigation')->findOneBy('route', 'admin/playgroundgame/edit-postvote');
        $page->setParams(['gameId' => $this->game->getId()]);
        $page->setLabel($this->game->getTitle());

        $form = $this->game->getForm();

        return $this->createForm($form);
    }

    public function createPostVoteAction()
    {
        $service = $this->getAdminGameService();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/post-vote/postvote');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/game/game-form');

        $postVote = new PostVote();

        $form = $this->getServiceLocator()->get('playgroundgame_postvote_form');
        $form->bind($postVote);
        $form->get('submit')->setAttribute('label', 'Add');
        $form->setAttribute(
            'action',
            $this->adminUrl()->fromRoute(
                'playgroundgame/create-postvote',
                array('gameId' => 0)
            )
        );
        $form->setAttribute('method', 'post');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            if (empty($data['prizes'])) {
                $data['prizes'] = array();
            }
            $game = $service->createOrUpdate($data, $postVote, 'playgroundgame_postvote_form');
            if ($game) {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The game has been created');

                return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
            }
        }
        $gameForm->setVariables(array('form' => $form, 'game' => $postVote));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Create Post & Vote'));
    }

    public function editPostVoteAction()
    {
        $this->checkGame();

        $navigation = $this->getServiceLocator()->get('ViewHelperManager')->get('navigation');
        $page = $navigation('admin_navigation')->findOneBy('route', 'admin/playgroundgame/edit-postvote');
        $page->setParams(['gameId' => $this->game->getId()]);
        $page->setLabel($this->game->getTitle());

        return $this->editGame(
            'playground-game/post-vote/postvote',
            'playgroundgame_postvote_form'
        );
    }

    public function modListAction()
    {
        $service = $this->getAdminGameService();
        $posts = $service->getPostVotePostMapper()->findBy(array('status' => 1));

        if (is_array($posts)) {
            $paginator = new \Laminas\Paginator\Paginator(new \Laminas\Paginator\Adapter\ArrayAdapter($posts));
            $paginator->setItemCountPerPage(10);
            $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
        } else {
            $paginator = $posts;
        }

        return array('posts' => $paginator);
    }

    public function moderationEditAction()
    {
        $service = $this->getAdminGameService();
        $postId = $this->getEvent()->getRouteMatch()->getParam('postId');
        $status = $this->getEvent()->getRouteMatch()->getParam('status');

        if (!$postId) {
            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('postvote/entry', array('gameId' => 0)));
        }
        $post = $service->getPostVotePostMapper()->findById($postId);

        if (! $post) {
            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('postvote/entry', array('gameId' => 0)));
        }
        $game = $post->getPostvote();

        if ($status) {
            $service->moderatePost($post, $status);

            return $this->redirect()->toUrl(
                $this->adminUrl()->fromRoute(
                    'postvote/entry',
                    array('gameId' => $game->getId())
                )
            );
        }

        return array('game' => $game, 'post' => $post);
    }

    public function pushAction()
    {
        $service = $this->getAdminGameService();
        $postId = $this->getEvent()->getRouteMatch()->getParam('postId');
        $pushed = $this->getEvent()->getRouteMatch()->getParam('pushed');

        if (!$postId) {
            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('postvote/entry', array('gameId' => 0)));
        }
        $post = $service->getPostVotePostMapper()->findById($postId);

        if (! $post) {
            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('postvote/entry', array('gameId' => 0)));
        }
        $game = $post->getPostvote();

        $post->setPushed($pushed);
        $service->getPostVotePostMapper()->update($post);

        return $this->redirect()->toUrl(
            $this->adminUrl()->fromRoute(
                'postvote/entry',
                array('gameId' => $game->getId())
            )
        );
    }

    public function getAdminGameService()
    {
        if (!$this->adminGameService) {
            $this->adminGameService = $this->getServiceLocator()->get('playgroundgame_postvote_service');
        }

        return $this->adminGameService;
    }

    public function setAdminGameService(AdminGameService $adminGameService)
    {
        $this->adminGameService = $adminGameService;

        return $this;
    }
}
