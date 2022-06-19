<?php

namespace PlaygroundGame\Controller\Admin;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use PlaygroundCore\ORM\Pagination\LargeTablePaginator;
use PlaygroundGame\Service\Game as AdminGameService;
use PlaygroundGame\Entity\Crossword;
use PlaygroundGame\Entity\CrosswordWord;
use PlaygroundGame\Controller\Admin\GameController;
use Laminas\View\Model\ViewModel;
use Laminas\Paginator\Paginator;

class CrosswordController extends GameController
{
    /**
     * @var \PlaygroundGame\Service\Game
     */
    protected $adminGameService;

    public function createCrosswordAction()
    {
        $service = $this->getAdminGameService();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/crossword/crossword');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/game/game-form');

        $crossword = new Crossword();

        $form = $this->getServiceLocator()->get('playgroundgame_crossword_form');
        $form->bind($crossword);
        $form->get('submit')->setAttribute('label', 'Add');
        $form->setAttribute(
            'action',
            $this->adminUrl()->fromRoute(
                'playgroundgame/create-crossword',
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
            if (isset($data['drawDate']) && $data['drawDate']) {
                $data['drawDate'] = \DateTime::createFromFormat('d/m/Y', $data['drawDate']);
            }
            $game = $service->createOrUpdate($data, $crossword, 'playgroundgame_crossword_form');
            if ($game) {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The game has been created');

                return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
            }
        }
        $gameForm->setVariables(array('form' => $form, 'game' => $crossword));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Create crossword'));
    }

    public function editCrosswordAction()
    {
        $this->checkGame();

        return $this->editGame(
            'playground-game/crossword/crossword',
            'playgroundgame_crossword_form'
        );
    }

    public function listWordAction()
    {
        $this->checkGame();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray()
            );

            $this->getAdminGameService()->updateWords($data, $this->game);
        }

        $adapter = new DoctrineAdapter(
            new LargeTablePaginator(
                $this->getAdminGameService()->getCrosswordWordMapper()->queryByGame($this->game)
            )
        );
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        return new ViewModel(
            array(
                'words' => $paginator,
                'gameId' => $this->game->getId(),
                'game'   => $this->game,
            )
        );
    }

    public function addWordAction()
    {
        $this->checkGame();

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/crossword/word');

        $form = $this->getServiceLocator()->get('playgroundgame_crosswordword_form');
        $form->get('submit')->setAttribute('label', 'Add');

        $form->setAttribute(
            'action',
            $this->adminUrl()->fromRoute(
                'playgroundgame/crossword-word-add',
                array('gameId' => $this->game->getId())
            )
        );
        $form->setAttribute('method', 'post');
        $form->get('crossword_id')->setAttribute('value', $this->game->getId());
        $word = new CrosswordWord();
        $form->bind($word);

        if ($this->getRequest()->isPost()) {
            $data = array_merge(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $word = $this->getAdminGameService()->updateWord($data, $word);
            if ($word) {
                // Redirect to list of games
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The word has been created');
                return $this->redirect()->toUrl(
                    $this->adminUrl()->fromRoute('playgroundgame/crossword-word-list', array('gameId' => $this->game->getId()))
                );
            }
        }
        return $viewModel->setVariables(
            array(
                'form'     => $form,
                'game'     => $this->game,
                'word_id' => 0,
                'title'    => 'Add word',
            )
        );
    }

    public function editWordAction()
    {
        $this->checkGame();

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/crossword/word');
        $service = $this->getAdminGameService();

        $wordId = $this->getEvent()->getRouteMatch()->getParam('wordId');
        $word   = $service->getCrosswordWordMapper()->findById($wordId);

        $form = $this->getServiceLocator()->get('playgroundgame_crosswordword_form');
        $form->remove('words_file');

        $form->get('submit')->setAttribute('label', 'Edit');
        $form->setAttribute('action', '');

        $form->get('crossword_id')->setAttribute('value', $this->game->getId());

        $form->bind($word);

        if ($this->getRequest()->isPost()) {
            $data = array_merge(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $word = $service->updateWord($data, $word);

            if ($word) {
                // Redirect to list of games
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The word has been edited');
                return $this->redirect()->toUrl(
                    $this->adminUrl()->fromRoute('playgroundgame/crossword-word-list', array('gameId' => $this->game->getId()))
                );
            }
        }
        return $viewModel->setVariables(
            array(
                'form'     => $form,
                'game'     => $this->game,
                'word_id'  => $wordId,
                'title'    => 'Edit word',
            )
        );
    }

    public function removeWordAction()
    {
        $service = $this->getAdminGameService();
        $wordId = $this->getEvent()->getRouteMatch()->getParam('wordId');
        if (!$wordId) {
            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
        }
        $word = $service->getCrosswordWordMapper()->findById($wordId);
        $crosswordId = $word->getGame()->getId();

        $service->getCrosswordWordMapper()->remove($word);
        $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The word has been deleted');

        return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/crossword-word-list', array('gameId' => $crosswordId)));
    }

    public function getAdminGameService()
    {
        if (!$this->adminGameService) {
            $this->adminGameService = $this->getServiceLocator()->get('playgroundgame_crossword_service');
        }

        return $this->adminGameService;
    }

    public function setAdminGameService(AdminGameService $adminGameService)
    {
        $this->adminGameService = $adminGameService;

        return $this;
    }
}
