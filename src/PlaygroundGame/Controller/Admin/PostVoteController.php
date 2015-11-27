<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Service\Game as AdminGameService;
use PlaygroundGame\Entity\PostVote;
use Zend\View\Model\ViewModel;

class PostVoteController extends GameController
{
    /**
     * @var \PlaygroundGame\Service\Game
     */
    protected $adminGameService;

    
    public function formAction()
    {
        $this->checkGame();
        
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
            $this->url()->fromRoute(
                'admin/playgroundgame/create-postvote',
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
            $game = $service->create($data, $postVote, 'playgroundgame_postvote_form');
            if ($game) {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The game was created');

                return $this->redirect()->toRoute('admin/playgroundgame/list');
            }
        }
        $gameForm->setVariables(array('form' => $form, 'game' => $postVote));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Create Post & Vote'));
    }

    public function editPostVoteAction()
    {
        $this->checkGame();

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
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($posts));
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
            return $this->redirect()->toUrl($this->url()->fromRoute('admin/postvote/entry', array('gameId' => 0)));
        }
        $post = $service->getPostVotePostMapper()->findById($postId);

        if (! $post) {
            return $this->redirect()->toUrl($this->url()->fromRoute('admin/postvote/entry', array('gameId' => 0)));
        }
        $game = $post->getPostvote();

        if ($status && $status=='validation') {
            $post->setStatus(2);
            $service->getPostVotePostMapper()->update($post);

            return $this->redirect()->toUrl(
                $this->url()->fromRoute(
                    'admin/postvote/entry',
                    array('gameId' => $game->getId())
                )
            );
        } elseif ($status && $status=='rejection') {
            $post->setStatus(9);
            $service->getPostVotePostMapper()->update($post);

            return $this->redirect()->toUrl(
                $this->url()->fromRoute(
                    'admin/postvote/entry',
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
            return $this->redirect()->toUrl($this->url()->fromRoute('admin/postvote/entry', array('gameId' => 0)));
        }
        $post = $service->getPostVotePostMapper()->findById($postId);
    
        if (! $post) {
            return $this->redirect()->toUrl($this->url()->fromRoute('admin/postvote/entry', array('gameId' => 0)));
        }
        $game = $post->getPostvote();
    
        $post->setPushed($pushed);
        $service->getPostVotePostMapper()->update($post);
    
        return $this->redirect()->toUrl(
            $this->url()->fromRoute(
                'admin/postvote/entry',
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
