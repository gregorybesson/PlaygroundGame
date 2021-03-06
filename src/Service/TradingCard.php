<?php

namespace PlaygroundGame\Service;

use Laminas\Stdlib\ErrorHandler;

class TradingCard extends Game
{
    protected $tradingcardMapper;
    protected $tradingcardmodelMapper;
    protected $tradingcardcardMapper;

    public function getModelPath($model)
    {
        $path = $this->getOptions()->getMediaPath().DIRECTORY_SEPARATOR;
        $path .= 'game'.$model->getGame()->getId().DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $path .= 'models'.DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    public function getModelMediaUrl($model)
    {
        $media_url = $this->getOptions()->getMediaUrl().'/';
        $media_url .= 'game'.$model->getGame()->getId().'/models/';

        return $media_url;
    }

    /**
     * @param  array $data
     * @return \PlaygroundGame\Entity\Game
     */
    public function updateModel(array $data, $model)
    {
        $form        = $this->serviceLocator->get('playgroundgame_tradingcardmodel_form');
        $tradingcard = $this->getGameMapper()->findById($data['trading_card_id']);
        $model->setGame($tradingcard);
        $path      = $this->getModelPath($model);
        $media_url = $this->getModelMediaUrl($model);

        $form->bind($model);
        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }
        if (!empty($data['upload_image']['tmp_name'])) {
            ErrorHandler::start();
            $data['upload_image']['name'] = $this->fileNewname(
                $path,
                $model->getId()."-".$data['upload_image']['name']
            );
            move_uploaded_file($data['upload_image']['tmp_name'], $path.$data['upload_image']['name']);
            $model->setImage($media_url.$data['upload_image']['name']);
            ErrorHandler::stop(true);
        }

        $this->getTradingCardModelMapper()->update($model);
        $this->getEventManager()->trigger(
            __FUNCTION__ .'.post',
            $this,
            array('model' => $model, 'data' => $data)
        );

        return $model;
    }

    public function getBooster($game, $user, $entry)
    {
        // get booster config from $game
        $em      = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
        $nb      = $game->getBoosterCardNumber();
        $booster = [];

        $today = new \DateTime("now");
        $today = $today->format('Y-m-d H:i:s');

        $qb  = $em->createQueryBuilder();
        $and = $qb->expr()->andx();
        $and->add(
            $qb->expr()->orX(
                $qb->expr()->lte('g.availability', ':date'),
                $qb->expr()->isNull('g.availability')
            )
        );

        $qb->setParameter('date', $today);
        $qb->select('g')
           ->from('PlaygroundGame\Entity\TradingCardModel', 'g')
           ->where($and);

        $query  = $qb->getQuery();
        $models = $query->getResult();

        shuffle($models);

        $eventModels = $this->getEventManager()->trigger(
            __FUNCTION__ .'.pre',
            $this,
            array(
                'game'   => $game,
                'user'   => $user,
                'entry'  => $entry,
                'models' => $models,
            )
        )->last();

        if ($eventModels) {
            $models = $eventModels;
        }

        for ($i = 0; $i < $nb; $i++) {
            $model = $models[$i];
            $card  = new \PlaygroundGame\Entity\TradingCardCard();
            $card->setUser($user);
            $card->setModel($model);
            $card->setGame($game);
            $card->setEntry($entry);
            $card = $this->getTradingCardCardMapper()->insert($card);

            $booster[] = $card;
        }

        $eventBooster = $this->getEventManager()->trigger(
            __FUNCTION__ .'.post',
            $this,
            array(
                'game'    => $game,
                'user'    => $user,
                'entry'   => $entry,
                'booster' => $booster,
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
        // all the models of the album
        $models = $this->getTradingCardModelMapper()->findAll();
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');

        if ($user) {
            $qb = $em->createQueryBuilder();
            $and = $qb->expr()->andx();
            $and->add($qb->expr()->eq('g.id', ':game'));
            $qb->setParameter('game', $game);
            $and->add($qb->expr()->eq('u.id', ':user'));
            $qb->setParameter('user', $user);
            $qb->select('c')
                ->from('PlaygroundGame\Entity\TradingCardCard', 'c')
                ->innerJoin('c.game', 'g')
                ->innerJoin('c.model', 'm')
                ->innerJoin('c.user', 'u')
                ->where($and)
                ->orderBy('m.id', 'ASC')
                ->groupBy('c.model');
            $query = $qb->getQuery();
        } elseif ($game->getAnonymousAllowed() && $this->getAnonymousIdentifier()) {
            $limitDate = $this->getLimitDate('always');
            $entries = $this->getEntryMapper()->findLastEntriesByAnonymousIdentifier(
                $game,
                $this->getAnonymousIdentifier(),
                $limitDate
            );
            $qb = $em->createQueryBuilder();
            $qb->andWhere('e.id IN (:entries)')->setParameter('entries', $entries);
            $qb->select('c')
                ->from('PlaygroundGame\Entity\TradingCardCard', 'c')
                ->innerJoin('c.model', 'm')
                ->innerJoin('c.game', 'g')
                ->innerJoin('c.entry', 'e')
                ->orderBy('m.id', 'ASC')
                ->groupBy('c.model');
            $query = $qb->getQuery();
        } elseif ($game->getAnonymousAllowed()) {
            $limitDate = $this->getLimitDate('always');
            $entries = $this->getEntryMapper()->findLastEntriesByIp(
                $game,
                $this->getIp(),
                $limitDate
            );
            $qb = $em->createQueryBuilder();
            $qb->andWhere('e.id IN (:entries)')->setParameter('entries', $entries);
            $qb->select('c')
                ->from('PlaygroundGame\Entity\TradingCardCard', 'c')
                ->innerJoin('c.model', 'm')
                ->innerJoin('c.game', 'g')
                ->innerJoin('c.entry', 'e')
                ->orderBy('m.id', 'ASC')
                ->groupBy('c.model');
            $query = $qb->getQuery();
        } else {
            // If the game is supposed to be a regular user game or an anonymous identified game,
            // it means that the registration/login is at the end of the game
            if ((!$user &&  !$game->getAnonymousAllowed()) || ($game->getAnonymousAllowed() && $game->getAnonymousIdentifier())) {
                return 0;
            }
            return $this->getEntryMapper()->findLastEntriesByIp($game, $this->getIp(), $limitDate);
        }

        
        // all the cards of the user
        $cards = $query->getResult();
        $cardsArray = [];
        foreach ($cards as $card) {
            $cardsArray[$card->getModel()->getId()] = $card;
        }

        $album = [];

        // I create the complete album including the cards of the user
        foreach ($models as $model) {
            $sticker['model'] = $model;
            if (isset($cardsArray[$model->getId()])) {
                $sticker['card'] = $cardsArray[$model->getId()];
            } else {
                $sticker['card'] = null;
            }
            $album[] = $sticker;
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
            $this->tradingcardMapper = $this->serviceLocator->get('playgroundgame_tradingcard_mapper');
        }

        return $this->tradingcardMapper;
    }

    public function getTradingCardCardMapper()
    {
        if (null === $this->tradingcardcardMapper) {
            $this->tradingcardcardMapper = $this->serviceLocator->get('playgroundgame_tradingcard_card_mapper');
        }

        return $this->tradingcardcardMapper;
    }

    public function getTradingCardModelMapper()
    {
        if (null === $this->tradingcardmodelMapper) {
            $this->tradingcardmodelMapper = $this->serviceLocator->get('playgroundgame_tradingcard_model_mapper');
        }

        return $this->tradingcardmodelMapper;
    }
}
