<?php
namespace PlaygroundGame\Controller\Frontend;

use Zend\ServiceManager\ServiceLocatorInterface;

class TradingCardController extends GameController
{
    /**
     * @var gameService
     */
    protected $gameService;

    public function __construct(ServiceLocatorInterface $locator)
    {
        parent::__construct($locator);
    }

    /**
     * Example of AJAX File Upload with Session Progress and partial validation.
     * It's now possible to send a base64 image in this case the call is the form :
     * this._ajax(
     * {
     *   url: url.dataset.url,
     *    method: 'post',
     *    body: 'photo=' + image
     * },
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function ajaxuploadAction()
    {
        // Call this for the session lock to be released (other ajax calls can then be made)
        session_write_close();

        if (! $this->game) {
            $this->getResponse()->setContent(\Zend\Json\Json::encode(array(
                'success' => 0
            )));

            return $this->getResponse();
        }

        $entry = $this->getGameService()->play($this->game, $this->user);
        if (!$entry) {
            // the user has already taken part to this game and the participation limit has been reached
            $this->getResponse()->setContent(
                \Zend\Json\Json::encode(
                    array(
                        'success' => 0
                    )
                )
            );

            return $this->getResponse();
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getFiles()->toArray();

            if (empty($data)) {
                $data = $this->getRequest()->getPost()->toArray();
                $key = key($data);
                $uploadImage = array(
                    'name' => $key.'.png',
                    'error' => 0,
                    'base64' => $data[$key]
                );
                $data = array($key => $uploadImage);
            }
            $path = $this->getGameService()->getGameUserPath($this->game, $this->user);
            $media_url = '/'.$this->getGameService()->getGameUserMediaUrl($this->game, $this->user);
            $uploadFile = $this->getGameService()->uploadFile(
                $path,
                $data[$key],
                false
            );
            $result = $media_url.$uploadFile;
        }

        $this->getResponse()->setContent(\Zend\Json\Json::encode(array(
            'success' => true,
            'fileUrl' => $result
        )));

        return $this->getResponse();
    }

    public function playAction()
    {
        $entry = $this->getGameService()->play($this->game, $this->user);
        if (!$entry) {
            // the user has already taken part to this game and the participation limit has been reached
            $this->flashMessenger()->addMessage('Vous avez déjà participé');

            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'tradingcard/result',
                    array('id' => $this->game->getIdentifier())
                ) .'?playLimitReached=1'
            );
        }
        $viewModel = $this->buildView($this->game);
        $booster = null;
        if ($entry) {
            $booster = $this->getGameService()->getBooster($this->game, $this->user, $entry);
        }
        
        $album = $this->getGameService()->getAlbum($this->game, $this->user);
        $viewModel->setVariables(
            array(
                'booster' => $booster,
                'album' => $album
            )
        );

        return $viewModel;
    }

    public function resultAction()
    {
        $playLimitReached = false;
        if ($this->getRequest()->getQuery()->get('playLimitReached')) {
            $playLimitReached = true;
        }
        $lastEntry = $this->getGameService()->findLastInactiveEntry($this->game, $this->user);
        if (!$lastEntry) {
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'tradingcard',
                    array('id' => $this->game->getIdentifier()),
                    array('force_canonical' => true)
                )
            );
        }
        $album = $this->getGameService()->getAlbum($this->game, $this->user);
        $viewModel = $this->buildView($this->game);
        $viewModel->setVariables(
            array(
                'album' => $album,
                'playLimitReached' => $playLimitReached,
                'entry' => $lastEntry
            )
        );

        return $viewModel;
    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_tradingcard_service');
        }

        return $this->gameService;
    }
}
