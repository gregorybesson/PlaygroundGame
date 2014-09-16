<?php

namespace PlaygroundGame\Controller\Frontend;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class HomeController extends AbstractActionController
{
    /**
     * @var gameService
     */
    protected $gameService;
    
    /**
     * @var pageService
     */
    protected $pageService;
    
    public function indexAction()
    {

        $layoutViewModel = $this->layout();

        $slider = new ViewModel();
        $slider->setTemplate('playground-game/common/top_promo');

        $sliderGames = $this->getGameService()->getActiveSliderGames();
        $sliderPages = $this->getPageService()->getActiveSliderPages();

        // I merge both types of articles and sort them in reverse order of their key
        // And as their key is some sort of... date !, It means I sort it in date reverse order ;)
        $sliderItems = array_merge($sliderGames, $sliderPages);

        krsort($sliderItems);

        $slider->setVariables(array('sliderItems' => $sliderItems));

        $layoutViewModel->addChild($slider, 'slider');

        $games = $this->getGameService()->getActiveGames(true, '', '', true);
        $pages = $this->getPageService()->getActivePages();
        //$missions = $this->getMissionService()->getActiveMissions();

        // I merge both types of articles and sort them in reverse order of their key
        // And as their key is some sort of... date !, It means I sort it in date reverse order ;)
        $items = array_merge($games,$pages);
        krsort($items);

        if (is_array($items)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($items));
            $paginator->setItemCountPerPage(7);
            $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
        } else {
            $paginator = $items;
        }

        $this->layout()->setVariables(
            array(
                'sliderItems'	=> $sliderItems,
            )
        );

        return new ViewModel(
            array(
                'items'	=> $paginator,
                //'missions' => $missions,
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
    
    public function getPageService()
    {
        if (!$this->pageService) {
            $this->pageService = $this->getServiceLocator()->get('playgroundcms_page_service');
        }
    
        return $this->pageService;
    }
    
    public function setPageService(\PlaygroundCms\Service\Page $pageService)
    {
        $this->pageService = $pageService;
    
        return $this;
    }
}
