<?php

namespace PlaygroundGame\Controller\Frontend;

use PlaygroundGame\Entity\PrizeCategory;
use Zend\View\Model\ViewModel;

class PrizeCategoryController extends GameController
{

    protected $gameService;

    protected $prizeCategoryService;

    public function indexAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');

        if (!$identifier) {
            return $this->notFoundAction();
        }

        $prizeCategory = $this->getPrizeCategoryService()->getPrizeCategoryMapper()->findByIdentifier($identifier);
        $idPrizeCategory = $prizeCategory->getId();

        $games = $this->getGameService()->getPrizeCategoryGames($idPrizeCategory);

        if (!$games) {
            $this->flashMessenger()->addMessage('Il n\'y a aucun jeu disponible pour cette thématique');
        }

        if (is_array($games)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($games));
            $paginator->setItemCountPerPage(7);
            $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
        } else {
            $paginator = $games;
        }

        $bitlyclient = $this->getOptions()->getBitlyUrl();
        $bitlyuser = $this->getOptions()->getBitlyUsername();
        $bitlykey = $this->getOptions()->getBitlyApiKey();

        $this->getViewHelper('HeadMeta')->setProperty('bt:client', $bitlyclient);
        $this->getViewHelper('HeadMeta')->setProperty('bt:user', $bitlyuser);
        $this->getViewHelper('HeadMeta')->setProperty('bt:key', $bitlykey);

        /*$adserving = $this->getOptions()->getAdServing();
        $adserving['cat2'] = 'game';*/
        
        $this->layout()->setVariables(
            array(
                'breadcrumbTitle' => $prizeCategory->getTitle(),
                //'adserving'       => $adserving,
                'currentPage' => array(
                    'pageGames' => 'games',
                    'pageWinners' => ''
                ),
                'headParams' => array(
                    'headTitle' => $prizeCategory->getTitle(),
                ),
            )
        );

        return new ViewModel(
            array(
                'games' 			=> $paginator,
                'prizeCategory' 	=> $prizeCategory,
                'identifier'		=> $identifier,
                'flashMessages'    	=> $this->flashMessenger()->getMessages(),
            )
        );
    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_game_service');
        }

        return $this->gameService;
    }

    public function setGameService(GameService $gameService)
    {
        $this->gameService = $gameService;

        return $this;
    }

    public function getPrizeCategoryService()
    {
        if (!$this->prizeCategoryService) {
            $this->prizeCategoryService = $this->getServiceLocator()->get('playgroundgame_prizecategory_service');
        }

        return $this->prizeCategoryService;
    }

    public function setPrizeCategoryService(PrizeCategoryService $prizeCategoryService)
    {
        $this->prizeCategoryService = $prizeCategoryService;

        return $this;
    }

}
