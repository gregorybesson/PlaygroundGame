<?php

namespace PlaygroundGame\Controller\Admin;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use PlaygroundCore\ORM\Pagination\LargeTablePaginator;
use PlaygroundGame\Service\Game as AdminGameService;
use PlaygroundGame\Entity\Memory;
use PlaygroundGame\Entity\MemoryCard;
use PlaygroundGame\Controller\Admin\GameController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;

class MemoryController extends GameController
{
    /**
     * @var \PlaygroundGame\Service\Game
     */
    protected $adminGameService;

    public function createMemoryAction()
    {
        $service = $this->getAdminGameService();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/memory/memory');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/game/game-form');

        $memory = new Memory();

        $form = $this->getServiceLocator()->get('playgroundgame_memory_form');
        $form->bind($memory);
        $form->get('submit')->setAttribute('label', 'Add');
        $form->setAttribute(
            'action',
            $this->adminUrl()->fromRoute(
                'playgroundgame/create-memory',
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
            $game = $service->createOrUpdate($data, $memory, 'playgroundgame_memory_form');
            if ($game) {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The game was created');

                return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
            }
        }
        $gameForm->setVariables(array('form' => $form, 'game' => $memory));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Create memory'));
    }

    public function editMemoryAction()
    {
        $this->checkGame();

        return $this->editGame(
            'playground-game/memory/memory',
            'playgroundgame_memory_form'
        );
    }

    public function listCardAction()
    {
        $this->checkGame();

        $adapter = new DoctrineAdapter(
            new LargeTablePaginator(
                $this->getAdminGameService()->getMemoryCardMapper()->queryByGame($this->game)
            )
        );
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(16);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        return new ViewModel(
            array(
                'cards' => $paginator,
                'gameId' => $this->game->getId(),
                'game'   => $this->game,
            )
        );
    }

    public function addCardAction()
    {
        $this->checkGame();

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/memory/card');

        $form = $this->getServiceLocator()->get('playgroundgame_memorycard_form');
        $form->get('submit')->setAttribute('label', 'Add');

        $form->setAttribute(
            'action',
            $this->adminUrl()->fromRoute(
                'playgroundgame/memory-card-add',
                array('gameId' => $this->game->getId())
            )
        );
        $form->setAttribute('method', 'post');
        $form->get('memory_id')->setAttribute('value', $this->game->getId());
        $card = new MemoryCard();
        $form->bind($card);

        if ($this->getRequest()->isPost()) {
            $data = array_merge(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $card = $this->getAdminGameService()->updateCard($data, $card);
            if ($card) {
                // Redirect to list of games
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The card has been created');
                return $this->redirect()->toUrl(
                    $this->adminUrl()->fromRoute('playgroundgame/memory-card-list', array('gameId' => $this->game->getId()))
                );
            }
        }
        return $viewModel->setVariables(
            array(
                'form'     => $form,
                'game'     => $this->game,
                'card_id' => 0,
                'title'    => 'Add card',
            )
        );
    }

    public function editCardAction()
    {
        $this->checkGame();

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/memory/card');
        $service = $this->getAdminGameService();

        $cardId = $this->getEvent()->getRouteMatch()->getParam('cardId');
        $card   = $service->getMemoryCardMapper()->findById($cardId);

        $form = $this->getServiceLocator()->get('playgroundgame_memorycard_form');
        $form->remove('cards_file');

        $form->get('submit')->setAttribute('label', 'Edit');
        $form->setAttribute('action', '');

        $form->get('memory_id')->setAttribute('value', $this->game->getId());

        $form->bind($card);

        if ($this->getRequest()->isPost()) {
            $data = array_merge(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $card = $service->updateCard($data, $card);

            if ($card) {
                // Redirect to list of games
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The card has been edited');
                return $this->redirect()->toUrl(
                    $this->adminUrl()->fromRoute('playgroundgame/memory-card-list', array('gameId' => $this->game->getId()))
                );
            }
        }
        return $viewModel->setVariables(
            array(
                'form'     => $form,
                'game'     => $this->game,
                'card_id'  => $cardId,
                'title'    => 'Edit card',
            )
        );
    }

    public function removeCardAction()
    {
        $service = $this->getAdminGameService();
        $cardId = $this->getEvent()->getRouteMatch()->getParam('cardId');
        if (!$cardId) {
            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
        }
        $card = $service->getMemoryCardMapper()->findById($cardId);
        $memoryId = $card->getGame()->getId();

        $service->getMemoryCardMapper()->remove($card);
        $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The card has been deleted');

        return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/memory-card-list', array('gameId' => $memoryId)));
    }

    public function getAdminGameService()
    {
        if (!$this->adminGameService) {
            $this->adminGameService = $this->getServiceLocator()->get('playgroundgame_memory_service');
        }

        return $this->adminGameService;
    }

    public function setAdminGameService(AdminGameService $adminGameService)
    {
        $this->adminGameService = $adminGameService;

        return $this;
    }
}
