<?php

namespace PlaygroundGame\Service;

use PlaygroundGame\Mapper\InstantWinOccurrence;

use PlaygroundGame\Entity\Entry;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ErrorHandler;

class InstantWin extends Game implements ServiceManagerAwareInterface
{

    /**
     * @var InstantWinOccurrenceMapperInterface
     */
    protected $instantWinOccurrenceMapper;

    protected $prizeMapper;

    /**
     *
     * saving an instantwin image if any
     *
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function create(array $data, $entity, $formClass)
    {
        $game = parent::create($data, $entity, $formClass);

        if ($game) {
            if (!empty($data['uploadScratchcardImage']['tmp_name'])) {

                $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
                $media_url = $this->getOptions()->getMediaUrl() . '/';

                ErrorHandler::start();
				$data['uploadScratchcardImage']['name'] = $this->fileNewname($path, $game->getId() . "-" . $data['uploadScratchcardImage']['name']);
                move_uploaded_file($data['uploadScratchcardImage']['tmp_name'], $path . $data['uploadScratchcardImage']['name']);
                $game->setScratchcardImage($media_url . $data['uploadScratchcardImage']['name']);
                ErrorHandler::stop(true);

                $game = $this->getGameMapper()->update($game);
            }

            if ($game->getOccurrenceNumber() && $game->getScheduleOccurrenceAuto()) {
                $this->scheduleOccurrences($game);
            }
        }

        return $game;
    }

    /**
     *
     * saving an instantwin image if any
     *
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function edit(array $data, $game, $formClass)
    {
        $game = parent::edit($data, $game, $formClass);

        if ($game) {
            if (!empty($data['uploadScratchcardImage']['tmp_name'])) {

                $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
                $media_url = $this->getOptions()->getMediaUrl() . '/';

                ErrorHandler::start();
                $data['uploadScratchcardImage']['name'] = $this->fileNewname($path, $game->getId() . "-" . $data['uploadScratchcardImage']['name']);
                move_uploaded_file($data['uploadScratchcardImage']['tmp_name'], $path . $data['uploadScratchcardImage']['name']);
                $game->setScratchcardImage($media_url . $data['uploadScratchcardImage']['name']);
                ErrorHandler::stop(true);

                $game = $this->getGameMapper()->update($game);
            }

            if ($game->getOccurrenceNumber() && $game->getScheduleOccurrenceAuto()) {
                $this->scheduleOccurrences($game);
            }
        }

        return $game;
    }

    /**
     * We can create Instant win occurrences dynamically
     *
     *
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function scheduleOccurrences($game)
    {
        $f = $game->getOccurrenceDrawFrequency();
        $today    = new \DateTime("now");
        $end      = new \DateTime("now");
        $interval = 'P10D';
        if ($game->getStartDate() && $game->getStartDate() > $today) {
            $beginning = $game->getStartDate();
        } else {
            $beginning = $today;
        }

        if ($game->getEndDate()) {
            $end = $game->getEndDate();
        } else {
            $end->add(new \DateInterval($interval));
        }
        $dateInterval = $end->diff($beginning);

        switch ($f) {
            case null:
            case 'game':
                // Je recherche tous les IG non gagnés
                $occurences = $this->getInstantWinOccurrenceMapper()->findBy(array('instantwin' => $game));
                $nbOccurencesToCreate = $game->getOccurrenceNumber() - count($occurences);
                if ($nbOccurencesToCreate > 0) {
                    for ($i=1;$i<=$nbOccurencesToCreate;$i++) {
                        $randomDate = $this->getRandomDate($beginning->format('U'), $end->format('U'));
                        $randomDate = \DateTime::createFromFormat('Y-m-d H:i:s', $randomDate);
                        $occurrence  = new \PlaygroundGame\Entity\InstantWinOccurrence();
                        $occurrence->setInstantwin($game);
                        $occurrence->setOccurrenceDate($randomDate);
                        $occurrence->setActive(1);

                        $this->getInstantWinOccurrenceMapper()->insert($occurrence);
                    }
                }

                break;
            case 'hour':
                // TODO : Je recherche tous les IG non gagnés pour chaque jour puis soustrais à ceux à créer
                $occurences = $this->getInstantWinOccurrenceMapper()->findBy(array('instantwin' => $game));
                $nbOccurencesToCreate = $game->getOccurrenceNumber() - count($occurences);
                $nbHoursInterval = $dateInterval->format('%a')*24 + $dateInterval->format('%h');

                $beginningDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y'). ' 00:00:00');
                $endDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y'). ' 00:59:59');

                if ($nbOccurencesToCreate > 0) {
                    for ($d=1;$d<=$nbHoursInterval;$d++){
                        for ($i=1;$i<=$nbOccurencesToCreate;$i++) {
                            $randomDate = $this->getRandomDate($beginningDrawDate->format('U'), $endDrawDate->format('U'));
                            $randomDate = \DateTime::createFromFormat('Y-m-d H:i:s', $randomDate);
                            $occurrence  = new \PlaygroundGame\Entity\InstantWinOccurrence();
                            $occurrence->setInstantwin($game);
                            $occurrence->setOccurrenceDate($randomDate);
                            $occurrence->setActive(1);

                            $this->getInstantWinOccurrenceMapper()->insert($occurrence);
                        }
                        $beginningDrawDate->add(new \DateInterval('PT1H'));
                        $endDrawDate->add(new \DateInterval('PT1H'));
                    }
                }

                break;
            case 'day':
                // TODO : Je recherche tous les IG non gagnés pour chaque jour puis soustrais à ceux à créer
                $occurences = $this->getInstantWinOccurrenceMapper()->findBy(array('instantwin' => $game));
                $nbOccurencesToCreate = $game->getOccurrenceNumber() - count($occurences);
                $nbDaysInterval = $dateInterval->format('%a');
                $beginningDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y'). ' 00:00:00');
                $endDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y'). ' 23:59:59');

                if ($nbOccurencesToCreate > 0) {
                    for ($d=1;$d<=$nbDaysInterval;$d++){
                        for ($i=1;$i<=$nbOccurencesToCreate;$i++) {
                            $randomDate = $this->getRandomDate($beginningDrawDate->format('U'), $endDrawDate->format('U'));
                            $randomDate = \DateTime::createFromFormat('Y-m-d H:i:s', $randomDate);
                            $occurrence  = new \PlaygroundGame\Entity\InstantWinOccurrence();
                            $occurrence->setInstantwin($game);
                            $occurrence->setOccurrenceDate($randomDate);
                            $occurrence->setActive(1);

                            $this->getInstantWinOccurrenceMapper()->insert($occurrence);
                        }
                        $beginningDrawDate->add(new \DateInterval('P1D'));
                        $endDrawDate->add(new \DateInterval('P1D'));
                    }
                }

                break;
            case 'week':
                // TODO : Je recherche tous les IG non gagnés pour chaque jour puis soustrais à ceux à créer
                $occurences = $this->getInstantWinOccurrenceMapper()->findBy(array('instantwin' => $game));
                $nbOccurencesToCreate = $game->getOccurrenceNumber() - count($occurences);
                $nbWeeksInterval = ceil($dateInterval->format('%a')/7);
                $beginningDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y'). ' 00:00:00');
                $endDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y'). ' 23:59:59');
                $endDrawDate->add(new \DateInterval('P6D'));
                if($endDrawDate > $end){
                    $endDrawDate = $end;
                }

                if ($nbOccurencesToCreate > 0) {
                    for ($d=1;$d<=$nbWeeksInterval;$d++){
                        //echo $beginningDrawDate->format('d/m/Y H:i:s') . " " . $endDrawDate->format('d/m/Y H:i:s') . "<br/>";
                        for ($i=1;$i<=$nbOccurencesToCreate;$i++) {
                            $randomDate = $this->getRandomDate($beginningDrawDate->format('U'), $endDrawDate->format('U'));
                            $randomDate = \DateTime::createFromFormat('Y-m-d H:i:s', $randomDate);
                            $occurrence  = new \PlaygroundGame\Entity\InstantWinOccurrence();
                            $occurrence->setInstantwin($game);
                            $occurrence->setOccurrenceDate($randomDate);
                            $occurrence->setActive(1);

                            $this->getInstantWinOccurrenceMapper()->insert($occurrence);
                        }
                        $beginningDrawDate->add(new \DateInterval('P1W'));
                        $endDrawDate->add(new \DateInterval('P1W'));
                        if($endDrawDate > $end){
                            $endDrawDate = $end;
                        }
                    }
                }

                break;
            case 'month':
                // TODO : Je recherche tous les IG non gagnés pour chaque jour puis soustrais à ceux à créer
                $occurences = $this->getInstantWinOccurrenceMapper()->findBy(array('instantwin' => $game));
                $nbOccurencesToCreate = $game->getOccurrenceNumber() - count($occurences);
                $nbMonthsInterval = $dateInterval->format('%m') + ceil($dateInterval->format('%d')/31);
                $beginningDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y'). ' 00:00:00');
                $endDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y'). ' 23:59:59');
                $endDrawDate->add(new \DateInterval('P1M'));
                $endDrawDate->sub(new \DateInterval('P1D'));
                if($endDrawDate > $end){
                    $endDrawDate = $end;
                }

                if ($nbOccurencesToCreate > 0) {
                    for ($d=1;$d<=$nbMonthsInterval;$d++){
                        //echo $beginningDrawDate->format('d/m/Y H:i:s') . " " . $endDrawDate->format('d/m/Y H:i:s') . "<br/>";
                        for ($i=1;$i<=$nbOccurencesToCreate;$i++) {
                            $randomDate = $this->getRandomDate($beginningDrawDate->format('U'), $endDrawDate->format('U'));
                            $randomDate = \DateTime::createFromFormat('Y-m-d H:i:s', $randomDate);
                            $occurrence  = new \PlaygroundGame\Entity\InstantWinOccurrence();
                            $occurrence->setInstantwin($game);
                            $occurrence->setOccurrenceDate($randomDate);
                            $occurrence->setActive(1);

                            $this->getInstantWinOccurrenceMapper()->insert($occurrence);
                        }
                        $beginningDrawDate->add(new \DateInterval('P1M'));
                        $endDrawDate->add(new \DateInterval('P1M'));
                        if($endDrawDate > $end){
                            $endDrawDate = $end;
                        }
                    }
                }

                break;
        }

        return true;
    }

    public function getRandomDate($min_date, $max_date)
    {
        $rand_epoch = rand($min_date, $max_date);

        return date('Y-m-d H:i:s', $rand_epoch);
    }

    /**
     *
     *
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function createOccurrence(array $data)
    {

        $occurrence  = new \PlaygroundGame\Entity\InstantWinOccurrence();
        $form  = $this->getServiceManager()->get('playgroundgame_instantwinoccurrence_form');
        $form->bind($occurrence);
        $form->setData($data);

        $instantwin = $this->getGameMapper()->findById($data['instant_win_id']);
        $prize = $this->getPrizeMapper()->findById($data['prize_id']);

        if (!$form->isValid()) {
            return false;
        }

        $occurrence->setInstantWin($instantwin);
        if($prize){
            $occurrence->setPrize($prize);
        } else {
            $occurrence->setPrize(null);
        }
        $this->getInstantWinOccurrenceMapper()->insert($occurrence);

        return $occurrence;
    }

    /**
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function updateOccurrence(array $data, $occurrence)
    {

        $form  = $this->getServiceManager()->get('playgroundgame_instantwinoccurrence_form');
        $form->bind($occurrence);
        $form->setData($data);

        $prize = $this->getPrizeMapper()->findById($data['prize']);

        if (!$form->isValid()) {
            return false;
        }

        if($prize){
            $occurrence->setPrize($prize);
        } else {
            $occurrence->setPrize(null);
        }

        $this->getInstantWinOccurrenceMapper()->update($occurrence);

        return $occurrence;
    }

    /**
     * return true if the player has won. False otherwise.
     *
     * @param \PlaygroundGame\Entity\Game $game
     * @param \PlaygroundUser\Entity\UserInterface $user
     *
     * @return boolean
     */
    public function isInstantWinner($game, $user)
    {

        $entryMapper = $this->getEntryMapper();
        $entry = $entryMapper->findLastActiveEntryById($game, $user);
        if (!$entry) {
            return false;
        }

        $instantWinOccurrencesMapper = $this->getInstantWinOccurrenceMapper();
        // si date après date de gain et date de gain encore active alors desactive date de gain, et winner !
        $winner = $instantWinOccurrencesMapper->checkInstantWinByGameId($game, $user, $entry);
        // On ferme la participation
        $entry->setActive(false);

        if ($winner) {
            $entry->setWinner(true);
        } else {
            $entry->setPoints(0);
            $entry->setWinner(false);
        }

        $entry = $entryMapper->update($entry);

        return $winner;
    }

    public function getGameEntity()
    {
        return new \PlaygroundGame\Entity\InstantWin;
    }

    /**
     * getInstantWinOccurrenceMapper
     *
     * @return InstantWinOccurrenceMapperInterface
     */
    public function getInstantWinOccurrenceMapper()
    {
        if (null === $this->instantWinOccurrenceMapper) {
            $this->instantWinOccurrenceMapper = $this->getServiceManager()->get('playgroundgame_instantwinoccurrence_mapper');
        }

        return $this->instantWinOccurrenceMapper;
    }

    /**
     * setInstantWinOccurrenceMapper
     *
     * @param  InstantWinOccurrenceMapperInterface $quizquestionMapper
     * @return InstantWinOccurrence
     */
    public function setInstantWinOccurrenceMapper($instantWinOccurrenceMapper)
    {
        $this->instantWinOccurrenceMapper = $instantWinOccurrenceMapper;

        return $this;
    }

    /**
     * getPrizeMapper
     *
     * @return PrizeMapperInterface
     */
    public function getPrizeMapper()
    {
        if (null === $this->prizeMapper) {
            $this->prizeMapper = $this->getServiceManager()->get('playgroundgame_prize_mapper');
        }

        return $this->prizeMapper;
    }

    /**
     * setInstantWinOccurrenceMapper
     *
     * @param  PrizeMapperInterface $prizeMapper
     * @return InstantWin
     */
    public function setPrizeMapper($prizeMapper)
    {
        $this->prizeMapper = $prizeMapper;

        return $this;
    }
}