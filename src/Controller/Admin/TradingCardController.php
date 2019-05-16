<?php

namespace PlaygroundGame\Controller\Admin;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use PlaygroundCore\ORM\Pagination\LargeTablePaginator;
use PlaygroundGame\Controller\Admin\GameController;
use PlaygroundGame\Entity\TradingCard;
use PlaygroundGame\Entity\TradingCardModel;
use PlaygroundGame\Service\Game as AdminGameService;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;

class TradingCardController extends GameController
{
    /**
     * @var \PlaygroundGame\Service\Game
     */
    protected $adminGameService;

    public function createTradingcardAction()
    {
        $service   = $this->getAdminGameService();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/trading-card/tradingcard');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/game/game-form');

        $tradingcard = new TradingCard();

        $form = $this->getServiceLocator()->get('playgroundgame_tradingcard_form');
        $form->bind($tradingcard);
        $form->get('submit')->setAttribute('label', 'Add');
        $form->setAttribute(
            'action',
            $this->adminUrl()->fromRoute(
                'playgroundgame/create-tradingcard',
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
            $game = $service->createOrUpdate($data, $tradingcard, 'playgroundgame_tradingcard_form');
            if ($game) {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The game has been created');

                return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
            }
        }
        $gameForm->setVariables(array('form' => $form, 'game' => $tradingcard));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Create trading card'));
    }

    public function editTradingcardAction()
    {
        $this->checkGame();

        return $this->editGame(
            'playground-game/trading-card/tradingcard',
            'playgroundgame_tradingcard_form'
        );
    }

    public function listModelAction()
    {
        $this->checkGame();

        $adapter = new DoctrineAdapter(
            new LargeTablePaginator(
                $this->getAdminGameService()->getTradingCardModelMapper()->queryByGame($this->game)
            )
        );
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(25);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        return new ViewModel(
            array(
                'models' => $paginator,
                'gameId' => $this->game->getId(),
                'game'   => $this->game,
            )
        );
    }

    public function addModelAction()
    {
        $this->checkGame();

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/trading-card/model');

        $form = $this->getServiceLocator()->get('playgroundgame_tradingcardmodel_form');
        $form->get('submit')->setAttribute('label', 'Add');

        // $form->get('availability')->setOptions(array(
        //     'format' => 'Y-m-d H:i:s'
        // ));

        $form->setAttribute(
            'action',
            $this->adminUrl()->fromRoute(
                'playgroundgame/tradingcard-model-add',
                array('gameId' => $this->game->getId())
            )
        );
        $form->setAttribute('method', 'post');
        $form->get('trading_card_id')->setAttribute('value', $this->game->getId());
        $model = new TradingCardModel();
        $form->bind($model);

        if ($this->getRequest()->isPost()) {
            $data = array_merge(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $model = $this->getAdminGameService()->updateModel($data, $model);
            if ($model) {
                // Redirect to list of games
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The model has been created');
                return $this->redirect()->toUrl(
                    $this->adminUrl()->fromRoute('playgroundgame/tradingcard-model-list', array('gameId' => $this->game->getId()))
                );
            }
        }
        return $viewModel->setVariables(
            array(
                'form'     => $form,
                'game'     => $this->game,
                'model_id' => 0,
                'title'    => 'Add model',
            )
        );
    }

    public function editModelAction()
    {
        $this->checkGame();

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/trading-card/model');
        $service = $this->getAdminGameService();

        $modelId = $this->getEvent()->getRouteMatch()->getParam('modelId');
        $model   = $service->getTradingCardModelMapper()->findById($modelId);

        $form = $this->getServiceLocator()->get('playgroundgame_tradingcardmodel_form');
        $form->remove('models_file');

        $form->get('submit')->setAttribute('label', 'Edit');
        $form->setAttribute('action', '');

        $form->get('trading_card_id')->setAttribute('value', $this->game->getId());

        $form->bind($model);

        if ($this->getRequest()->isPost()) {
            $data = array_merge(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $model = $service->updateModel($data, $model);

            if ($model) {
                // Redirect to list of games
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The model has been edited');
                return $this->redirect()->toUrl(
                    $this->adminUrl()->fromRoute('playgroundgame/tradingcard-model-list', array('gameId' => $this->game->getId()))
                );
            }
        }
        return $viewModel->setVariables(
            array(
                'form'     => $form,
                'game'     => $this->game,
                'model_id' => $modelId,
                'title'    => 'Edit model',
            )
        );
    }

    /**
     * This function helps the admin to position UGC image on a card model
     */
    public function cooAction()
    {
        // get models
        $sg = $this->getAdminGameService();
        $this->checkGame();

        $models = $sg->getTradingCardModelMapper()->findBy(array('game' => $this->game));

        if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
            $position = json_decode($this->getRequest()->getPost()->get('position'));
            $i        = 0;
            foreach ($models as $model) {
                $jsonData = json_decode($model->getJsonData());
                if (isset($jsonData->coo)) {
                    $jsonData->coo = $position[$i];
                } else {
                    $jsonData = array('coo' => $position[$i]);
                }

                $model->setJsonData(json_encode($jsonData));
                $models = $sg->getTradingCardModelMapper()->update($model);
                ++$i;
            }

            // update coo of Model
            $jsonModel = new \Zend\View\Model\JsonModel();
            $jsonModel->setVariables(array(
                    'success' => true,
                ));

            return $jsonModel;
        }

        // ajout des images au tableau
        $bgs = [];
        $coo = [];
        foreach ($models as $model) {
            $json     = json_decode($model->getJsonData());
            $modelCoo = (isset($json->coo))?$json->coo:'';
            $bgs[]    = '/'.$model->getImage();
            $coo[]    = $modelCoo;
        }

        return array(
            'backgrounds' => $bgs,
            'face'        => 'img-test/photo.png',
            'coo'         => $coo,
        );
    }

    public function removeOccurrenceAction()
    {
        $service = $this->getAdminGameService();
        $modelId = $this->getEvent()->getRouteMatch()->getParam('modelId');
        if (!$modelId) {
            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
        }
        $model         = $service->getTradingCardModelMapper()->findById($modelId);
        $tradingcardId = $model->getTradingCard()->getId();

        if ($model->getActive()) {
            $service->getTradingCardModelMapper()->remove($model);
            $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The model has been deleted');
        } else {
            $this->flashMessenger()->setNamespace('playgroundgame')->addMessage(
                'cards have already been created with this model'
            );
        }

        return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/tradingcard-model-list', array('gameId' => $tradingcardId)));
    }

    public function getAdminGameService()
    {
        if (!$this->adminGameService) {
            $this->adminGameService = $this->getServiceLocator()->get('playgroundgame_tradingcard_service');
        }

        return $this->adminGameService;
    }

    public function setAdminGameService(AdminGameService $adminGameService)
    {
        $this->adminGameService = $adminGameService;

        return $this;
    }
}
