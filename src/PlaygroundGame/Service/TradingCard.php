<?php

namespace PlaygroundGame\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use PlaygroundGame\Mapper\GameInterface as GameMapperInterface;
use Zend\Stdlib\ErrorHandler;

class TradingCard extends Game implements ServiceManagerAwareInterface
{
    protected $tradingcardMapper;
    protected $tradingcardmodelMapper;
    protected $tradingcardcardMapper;

    public function getPath($model)
    {
        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        $path .= 'game' . $model->getGame()->getId() . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $path .= 'model'. $model->getId() . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    public function getMediaUrl($model)
    {
        $media_url = $this->getOptions()->getMediaUrl() . '/';
        $media_url .= 'game' . $model->getGame()->getId() . '/' . 'model'. $model->getId() . '/';

        return $media_url;
    }

    /**
     * @param  array $data
     * @return \PlaygroundGame\Entity\Game
     */
    public function updateModel(array $data, $model)
    {
        $path = $this->getPath($model);
        $media_url = $this->getMediaUrl($model);
        $form  = $this->getServiceManager()->get('playgroundgame_tradingcardmodel_form');
        $tradingcard = $this->getGameMapper()->findById($data['trading_card_id']);

        $model->setGame($tradingcard);
        $form->bind($model);
        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }

        if (!empty($data['upload_image']['tmp_name'])) {
            ErrorHandler::start();
            $data['upload_image']['name'] = $this->fileNewname(
                $path,
                $model->getId() . "-" . $data['upload_image']['name']
            );
            move_uploaded_file($data['upload_image']['tmp_name'], $path . $data['upload_image']['name']);
            $model->setImage($media_url . $data['upload_image']['name']);
            ErrorHandler::stop(true);
        }

        // if (isset($data['delete_image']) && empty($data['upload_image']['tmp_name'])) {
        //     ErrorHandler::start();
        //     $image = $model->getImage();
        //     $image = str_replace($media_url, '', $image);
        //     if (file_exists($path .$image)) {
        //         unlink($path .$image);
        //     }
        //     $model->setImage(null);
        //     ErrorHandler::stop(true);
        // }

        $this->getTradingCardModelMapper()->update($model);
        $this->getEventManager()->trigger(
            __FUNCTION__.'.post',
            $this,
            array('model' => $model, 'data' => $data)
        );

        return $model;
    }

    public function getBooster($game, $user, $entry)
    {
        // get booster config from $game
        $nb = $game->getBoosterCardNumber();
        $booster = [];
        $models = $this->getTradingCardModelMapper()->findBy(array('game' => $game));
        shuffle($models);

        $eventModels = $this->getEventManager()->trigger(
            __FUNCTION__.'.pre',
            $this,
            array(
            'game' => $game,
            'user' => $user,
            'entry' => $entry,
            'models' => $models
            )
        )->last();

        if ($eventModels) {
            $models = $eventModels;
        }

        for ($i=1; $i<=$nb; $i++) {
            $model = $models[$i];
            $card = new \PlaygroundGame\Entity\TradingCardCard();
            $card->setUser($user);
            $card->setModel($model);
            $card->setGame($game);
            $card->setEntry($entry);
            $card = $this->getTradingCardCardMapper()->insert($card);

            $booster[] = $card;
        }

        $eventBooster = $this->getEventManager()->trigger(
            __FUNCTION__.'.post',
            $this,
            array(
            'game' => $game,
            'user' => $user,
            'entry' => $entry,
            'booster' => $booster
            )
        )->last();

        if ($eventBooster) {
            $booster = $eventBooster;
        }

        // sending a booster represents an entry. We close the entry after that
        $entry->setActive(0);
        $entry = $this->getEntryMapper()->update($entry);
        
        return $booster;
    }

    public function getAlbum($game, $user)
    {
        // get collection of cards from the user for this game
        $album = $this->getTradingCardCardMapper()->findBy(array('game' => $game, 'user' => $user));
        $eventAlbum = $this->getEventManager()->trigger(
            __FUNCTION__.'.post',
            $this,
            array(
            'game' => $game,
            'user' => $user,
            'album' => $album
            )
        )->last();

        if ($eventAlbum) {
            $album = $eventAlbum;
        }
        
        return $album;
    }

    public function getGameEntity()
    {
        return new \PlaygroundGame\Entity\TradingCard;
    }

    public function getTradingCardMapper()
    {
        if (null === $this->tradingcardMapper) {
            $this->tradingcardMapper = $this->getServiceManager()->get('playgroundgame_tradingcard_mapper');
        }

        return $this->tradingcardMapper;
    }

    public function getTradingCardCardMapper()
    {
        if (null === $this->tradingcardcardMapper) {
            $this->tradingcardcardMapper = $this->getServiceManager()->get('playgroundgame_tradingcardcard_mapper');
        }

        return $this->tradingcardcardMapper;
    }

    public function getTradingCardModelMapper()
    {
        if (null === $this->tradingcardmodelMapper) {
            $this->tradingcardmodelMapper = $this->getServiceManager()->get('playgroundgame_tradingcardmodel_mapper');
        }

        return $this->tradingcardmodelMapper;
    }
}
