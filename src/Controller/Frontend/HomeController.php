<?php

namespace PlaygroundGame\Controller\Frontend;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\ServiceManager\ServiceLocatorInterface;

class HomeController extends AbstractActionController
{
    /**
     * @var \PlaygroundGame\Service\GameService
     */
    protected $gameService;
    
    /**
     * @var \PlaygroundCms\Service\Page
     */
    protected $pageService;

    /**
     * @var \PlaygroundDesign\Service\Settings
     */
    protected $settingsService;

    protected $options;
    
    /**
     *
     * @var ServiceManager
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceLocator = $locator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    public function indexAction()
    {
        $paginationSettings = $this->getSettingsService()->getSettingsMapper()->findById(1);
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

        $games = $this->getGameService()->getActiveGames(true, '', '');

        $pages = $this->getPageService()->getActivePages();

        // I merge both types of articles and sort them in reverse order of their key
        // And as their key is some sort of... date !, It means I sort it in date reverse order ;)
        $items = array_merge($games, $pages);
        krsort($items);

        if (is_array($items)
            && $paginationSettings
            && $paginationSettings->getHomePagination() !== null
            && $paginationSettings->getHomePagination() > 0
        ) {
            $paginator = new \Laminas\Paginator\Paginator(new \Laminas\Paginator\Adapter\ArrayAdapter($items));
            $paginator->setItemCountPerPage($paginationSettings->getHomePagination());
            $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
        } else {
            $paginator = $items;
        }

        $this->layout()->setVariables(['sliderItems' => $sliderItems]);

        return new ViewModel(['items' => $paginator]);
    }

    public function shareAction()
    {
        $statusMail = null;

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        // buildView must be before sendMail because it adds the game template path to the templateStack
        if ($this->getRequest()->isXmlHttpRequest()) {
            $viewModel = new JsonModel();
        } else {
            $viewModel = new ViewModel();
        }

        $user = $this->zfcUserAuthentication()->getIdentity();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $subject = $this->serviceLocator->get('MvcTranslator')->translate(
                    $this->getOptions()->getDefaultSubjectLine(),
                    'playgroundgame'
                );
                $result = $this->getGameService()->sendShareMail($data, null, $user, null, 'share_game', $subject);
                if ($result) {
                    $statusMail = true;
                }
            }
        }

        $viewModel->setVariables(
            [
                'statusMail' => $statusMail,
                'form'       => $form,
            ]
        );

        return $viewModel;
    }

    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions($this->getServiceLocator()->get('playgroundgame_module_options'));
        }

        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }
    
    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_game_service');
        }
    
        return $this->gameService;
    }
    
    public function setGameService(\PlaygroundGame\Service\Game $gameService)
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

    public function getSettingsService()
    {
        if (!$this->settingsService) {
            $this->settingsService = $this->getServiceLocator()->get('playgrounddesign_settings_service');
        }
    
        return $this->settingsService;
    }
    
    public function setPageService(\PlaygroundCms\Service\Page $pageService)
    {
        $this->pageService = $pageService;
    
        return $this;
    }
}
