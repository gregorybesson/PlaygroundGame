<?php

namespace PlaygroundGame\Service;

use Laminas\Stdlib\ErrorHandler;
use ZfcDatagrid\Column;
use ZfcDatagrid\Action;
use ZfcDatagrid\Column\Formatter;
use ZfcDatagrid\Column\Type;
use ZfcDatagrid\Column\Style;
use ZfcDatagrid\Filter;
use Doctrine\ORM\Query\Expr;

class InstantWin extends Game
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
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function createOrUpdate(array $data, $game, $formClass)
    {
        $game = parent::createOrUpdate($data, $game, $formClass);

        if ($game) {
            $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
            $media_url = $this->getOptions()->getMediaUrl() . '/';

            if (!empty($data['uploadScratchcardImage']['tmp_name'])) {
                ErrorHandler::start();
                $data['uploadScratchcardImage']['name'] = $this->fileNewname(
                    $path,
                    $game->getId() . "-" . $data['uploadScratchcardImage']['name']
                );
                move_uploaded_file(
                    $data['uploadScratchcardImage']['tmp_name'],
                    $path . $data['uploadScratchcardImage']['name']
                );
                $game->setScratchcardImage($media_url . $data['uploadScratchcardImage']['name']);
                ErrorHandler::stop(true);

                $game = $this->getGameMapper()->update($game);
            }

            if (isset($data['deleteScratchcardImage']) &&
                $data['deleteScratchcardImage'] &&
                empty($data['uploadScratchcardImage']['tmp_name'])
            ) {
                ErrorHandler::start();
                $image = $game->getScratchcardImage();
                $image = str_replace($media_url, '', $image);
                unlink($path .$image);
                $game->setScratchcardImage(null);
                ErrorHandler::stop(true);
            }

            if ($game->getScheduleOccurrenceAuto()) {
                $this->scheduleOccurrences($game, $data);
            }
        }

        return $game;
    }

    /**
     * We can create Instant win occurrences dynamically
     *
     *
     * @param  array                  $data
     * @return boolean|null
     */
    public function scheduleOccurrences($game, array $data)
    {
        // It will be quite long to create these occurrences !
        set_time_limit(0);
        if ($game->getOccurrenceType() === 'code') {
            return $this->scheduleCodeOccurrences($game, $data);
        } elseif ($game->getOccurrenceType() === 'datetime') {
            return $this->scheduleDateOccurrences($game);
        }
    }

    public function getOccurrencesGrid($game = null)
    {
        $qb = $this->getOccurrencesQuery($game);
        $adminUrl = $this->serviceLocator->get('ControllerPluginManager')->get('adminUrl');

        /* @var $grid \ZfcDatagrid\Datagrid */
        $grid = $this->serviceLocator->get('ZfcDatagrid\Datagrid');
        $grid->setTitle('Occurrences');
        $grid->setDataSource($qb);
        $grid->setDefaultItemsPerPage(50);

        $col = new Column\Select('id', 'i');
        $col->setLabel('Id');
        $col->setIdentity(true);
        $grid->addColumn($col);

        $col = new Column\Select('value', 'i');
        $col->setLabel('Value');
        $grid->addColumn($col);

        $col = new Column\Select('email', 'u');
        $col->setLabel('Email');
        $grid->addColumn($col);

        $col = new Column\Select('title', 'p');
        $col->setLabel('Prize');
        $grid->addColumn($col);

        $col = new Column\Select('winning', 'i');
        $col->setLabel('Status');
        $col->setReplaceValues(
            [
                0 => 'not winning',
                1 => 'winning',
            ]
        );
        $grid->addColumn($col);

        $actions = new Column\Action();
        $actions->setLabel('');

        $viewAction = new Column\Action\Button();
        $viewAction->setLabel('Edit');
        $rowId = $viewAction->getRowIdPlaceholder();
        $viewAction->setLink($adminUrl->fromRoute('playgroundgame/instantwin-occurrence-edit', array('gameId' => $game->getId(), 'occurrenceId' => $rowId)));
        $actions->addAction($viewAction);

        $viewAction = new Column\Action\Button();
        $viewAction->setLabel('Delete');
        $rowId = $viewAction->getRowIdPlaceholder();
        $viewAction->setLink($adminUrl->fromRoute('playgroundgame/instantwin-occurrence-remove', array('occurrenceId' => $rowId)));
        $actions->addAction($viewAction);

        $grid->addColumn($actions);

        return $grid;
    }

    public function getOccurrencesQuery($game)
    {
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');

        $qb = $em->createQueryBuilder();
        $selectString = 'i, e, u, p, g';
        $qb->select($selectString)
            ->from('PlaygroundGame\Entity\InstantWinOccurrence', 'i')
            ->leftJoin('i.entry', 'e')
            ->leftJoin('i.prize', 'p')
            ->innerJoin('i.instantwin', 'g')
            ->leftJoin('i.user', 'u')
            ->where($qb->expr()->eq('i.instantwin', ':game'))
            ->orderBy('i.id');

        $qb->setParameter('game', $game);

        return $qb;
    }

    public function scheduleCodeOccurrences($game, $data)
    {
        $available_characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-';
        $last_character_index = strlen($available_characters)-1;
        if (!$game->getWinningOccurrenceNumber()) {
            $game->setWinningOccurrenceNumber($game->getOccurrenceNumber());
        }
        if (empty($data['occurrenceValueSize'])) {
            $data['occurrenceValueSize'] = 8;
        }
        $created = 0;
        $numOccurrences = $game->getOccurrenceNumber();
        
        for ($i=0; $i < $numOccurrences; $i++) {
            $code = '';
            while (strlen($code)<$data['occurrenceValueSize']) {
                $code .= $available_characters[rand(0, $last_character_index)];
            }
            $occurrence = new \PlaygroundGame\Entity\InstantWinOccurrence();
            $occurrence->setInstantwin($game);
            $occurrence->setValue($code);
            $occurrence->setActive(1);
            $occurrence->setWinning($created < $game->getWinningOccurrenceNumber());
            if ($this->getInstantWinOccurrenceMapper()->insert($occurrence)) {
                $created++;
            }
        }
        return true;
    }

    public function createRandomOccurrences($game, $beginning, $end, $quantity)
    {
        $prizes = $game->getPrizes();
        $randomPrizes = [];
        $insertPrize = false;
        $exactCount = false;
        foreach ($prizes as $prize) {
            $qty = $prize->getQty();
            if ($qty > 0) {
                for ($i = 0; $i < $qty; $i++) {
                    $randomPrizes[] = $prize;
                }
            }
        }
        $min = 0;
        $max = count($randomPrizes);
        if ($max > $min) {
            $insertPrize = true;
            if ($quantity === $max) {
                $exactCount = true;
            }
        }

        for ($i=1; $i<=$quantity; $i++) {
            $randomDate = $this->getRandomDate($beginning->format('U'), $end->format('U'));
            $randomDate = \DateTime::createFromFormat('Y-m-d H:i:s', $randomDate);
            $occurrence  = new \PlaygroundGame\Entity\InstantWinOccurrence();
            $occurrence->setInstantwin($game);
            $occurrence->setValue($randomDate->format('Y-m-d H:i:s'));
            $occurrence->setActive(1);
            if ($insertPrize) {
                $toPick = rand($min, $max - 1);
                $occurrence->setPrize($randomPrizes[$toPick]);
                if ($exactCount) {
                    array_splice($randomPrizes, $toPick, 1);
                    --$max;
                }
            }

            $this->getInstantWinOccurrenceMapper()->insert($occurrence);
        }
    }

    /**
     * Get the number of occurrences to create for the instantwin
     */
    public function getOccurrenceNumber($game)
    {
        $nb = 0;
        if ($game->getOccurrenceNumber() > 0) {
            $nb = $game->getOccurrenceNumber();
        } else {
            $prizes = $game->getPrizes();
            foreach ($prizes as $prize) {
                $nb += $prize->getQty();
            }
        }

        return $nb;
    }

    public function scheduleDateOccurrences($game)
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

        // Summertimes timezone management
        $timezone = $today->getTimezone();
        $transitions = $timezone->getTransitions($beginning->getTimestamp(), $end->getTimestamp());

        // There is a time transition between these datetimes()
        if (count($transitions) == 2) {
            $shift = $transitions[0]['offset'] - $transitions[1]['offset'];
            if ($shift > 0) {
                $end->sub(new \DateInterval('PT'.abs($shift).'S'));
            } else {
                $end->add(new \DateInterval('PT'.abs($shift).'S'));
            }
        }

        // DateInterval takes the day @ 00:00 to calculate the difference between the dates, so 1 day is always missing
        // as we consider the last day @ 23:59:59 in Playground :)
        if ($end->format('His') == 0) {
            $end->add(new \DateInterval('P1D'));
        }

        $dateInterval = (int)(($end->getTimestamp() - $beginning->getTimestamp())/60);

        // Je recherche tous les IG non gagnés
        $occurrences = $this->getInstantWinOccurrenceMapper()->findBy(array('instantwin' => $game));
        $nbExistingOccurrences = count($occurrences);
        $nbOccurencesToCreate = 0;

        switch ($f) {
            case null:
            case 'game':
                $nbOccurencesToCreate = $this->getOccurrenceNumber($game) - $nbExistingOccurrences;
                if ($nbOccurencesToCreate > 0) {
                    $this->createRandomOccurrences($game, $beginning, $end, $nbOccurencesToCreate);
                }

                break;
            case 'hour':
                $nbInterval = (int) ($dateInterval/60);

                // If a hour don't last 60min, I consider it as a hour anyway.
                if ($dateInterval%60 > 0) {
                    ++$nbInterval;
                }
                if ($nbInterval > 0) {
                    $nbOccurencesToCreate = $game->getOccurrenceNumber() - floor($nbExistingOccurrences/$nbInterval);
                }

                $beginningDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y H:i:s'));
                $endDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y H'). ':59:59');
                
                if ($nbOccurencesToCreate > 0) {
                    for ($d=1; $d<=$nbInterval; $d++) {
                        $this->createRandomOccurrences(
                            $game,
                            $beginningDrawDate,
                            $endDrawDate,
                            $nbOccurencesToCreate
                        );
                        $beginningDrawDate = \DateTime::createFromFormat(
                            'm/d/Y H:i:s',
                            $beginningDrawDate->format('m/d/Y H'). ':00:00'
                        );
                        $beginningDrawDate->add(new \DateInterval('PT1H'));
                        $endDrawDate->add(new \DateInterval('PT1H'));
                    }
                }

                break;
            case 'day':
                $nbInterval = (int) ($dateInterval/(60*24));

                // Prise en compte des changements d'horaires
                // If a day don't last 24h, I consider it as a day anyway

                if ($dateInterval%(60*24) > 0) {
                    ++$nbInterval;
                }

                if ($nbInterval > 0) {
                    $nbOccurencesToCreate = $game->getOccurrenceNumber() - floor($nbExistingOccurrences/$nbInterval);
                }

                $beginningDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y H:i:s'));
                $endDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y'). ' 23:59:59');
                
                if ($nbOccurencesToCreate > 0) {
                    for ($d=1; $d<=$nbInterval; $d++) {
                        $this->createRandomOccurrences(
                            $game,
                            $beginningDrawDate,
                            $endDrawDate,
                            $nbOccurencesToCreate
                        );
                        // As the first beginning date was not @ midnight,
                        // I recreate the beginning date
                        $beginningDrawDate = \DateTime::createFromFormat(
                            'm/d/Y H:i:s',
                            $beginningDrawDate->format('m/d/Y'). ' 00:00:00'
                        );
                        $beginningDrawDate->add(new \DateInterval('P1D'));
                        $endDrawDate->add(new \DateInterval('P1D'));
                    }
                }

                break;
            case 'week':
                $nbOccurencesToCreate = $game->getOccurrenceNumber() - $nbExistingOccurrences;
                $nbWeeksInterval = (int) ($dateInterval/(60*24*7));
                // If a week don't last 7d, I consider it as a week anyway.
                if ($dateInterval%(60*24*7) > 0) {
                    ++$nbWeeksInterval;
                }
                $beginningDrawDate = \DateTime::createFromFormat(
                    'm/d/Y H:i:s',
                    $beginning->format('m/d/Y'). ' 00:00:00'
                );
                $endDrawDate = \DateTime::createFromFormat(
                    'm/d/Y H:i:s',
                    $beginning->format('m/d/Y'). ' 23:59:59'
                );
                $endDrawDate->add(new \DateInterval('P6D'));
                if ($endDrawDate > $end) {
                    $endDrawDate = $end;
                }

                if ($nbOccurencesToCreate > 0) {
                    for ($d=1; $d<=$nbWeeksInterval; $d++) {
                        $this->createRandomOccurrences(
                            $game,
                            $beginningDrawDate,
                            $endDrawDate,
                            $nbOccurencesToCreate
                        );
                        $beginningDrawDate->add(new \DateInterval('P1W'));
                        $endDrawDate->add(new \DateInterval('P1W'));
                        if ($endDrawDate > $end) {
                            $endDrawDate = $end;
                        }
                    }
                }

                break;
            case 'month':
                $nbOccurencesToCreate = $game->getOccurrenceNumber() - $nbExistingOccurrences;
                $nbMonthsInterval = (int) ($dateInterval/(60*24*30));
                // If a week don't last 30d, I consider it as a month anyway.
                if ($dateInterval%(60*24*30) > 0) {
                    ++$nbMonthsInterval;
                }
                $beginningDrawDate = \DateTime::createFromFormat(
                    'm/d/Y H:i:s',
                    $beginning->format('m/d/Y'). ' 00:00:00'
                );
                $endDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y'). ' 23:59:59');
                $endDrawDate->add(new \DateInterval('P1M'));
                $endDrawDate->sub(new \DateInterval('P1D'));
                if ($endDrawDate > $end) {
                    $endDrawDate = $end;
                }

                if ($nbOccurencesToCreate > 0) {
                    for ($d=1; $d<=$nbMonthsInterval; $d++) {
                        $this->createRandomOccurrences(
                            $game,
                            $beginningDrawDate,
                            $endDrawDate,
                            $nbOccurencesToCreate
                        );
                        $beginningDrawDate->add(new \DateInterval('P1M'));
                        $endDrawDate->add(new \DateInterval('P1M'));
                        if ($endDrawDate > $end) {
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
     * @param string $fileName
     */
    public function getOccurencesFromCSV($fileName)
    {
        if (file_exists($fileName)) {
            $csvFile = fopen($fileName, 'r');
            if ($csvFile) {
                while (!feof($csvFile)) {
                    $csvContent[] = fgetcsv($csvFile);
                }
                fclose($csvFile);
                return $csvContent;
            }
        }
        return false;
    }

    public function setOccurencesToCSV($game)
    {
        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        $fileName = $path.'occurences-'.$game->getId().'.csv';
        $csvFile = fopen($fileName, 'w');
        if ($csvFile) {
            $occurrences = $this->getInstantWinOccurrenceMapper()->findByGameId($game);
            foreach ($occurrences as $occurrence) {
                fputcsv($csvFile, array($occurrence->getValue(), $occurrence->getWinning()));
            }
            fclose($csvFile);
            return $fileName;
        }
        return false;
    }

    public function importOccurrences($data)
    {
        if (!empty($data['file']['tmp_name'])) {
            $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
            $real_media_path = realpath($path) . DIRECTORY_SEPARATOR;

            // upload the csv file
            ErrorHandler::start();
            $data['file']['name'] = $this->fileNewname($path, $data['instant_win_id'] . "-" . $data['file']['name']);
            move_uploaded_file($data['file']['tmp_name'], $path . $data['file']['name']);
            ErrorHandler::stop(true);
            $csv_content = $this->getOccurencesFromCSV($real_media_path.$data['file']['name']);
            if ($csv_content) {
                $created = 0;
                foreach ($csv_content as $line) {
                    if ($line) {
                        $occurrence = $this->updateOccurrence(array(
                            'id' => '',
                            'instant_win_id' => $data['instant_win_id'],
                            'value' => $line[0],
                            'active' => $data['active'],
                            'winning' => ((bool) $line[1]) ? 1 : 0,
                            'prize_id' => $data['prize'],
                        ), null);
                        if ($occurrence) {
                            $created++;
                        }
                    }
                }
                // remove the csv file from folder
                unlink($real_media_path.$data['file']['name']);
                return $created;
            }
        }
        return false;
    }

    /**
     *
     *
     * @param  array                  $data
     * @return \PlaygroundGame\Entity\Game
     */
    public function updateOccurrence(array $data, $occurrence_id = null)
    {
        if (!$occurrence_id) {
            $occurrence  = new \PlaygroundGame\Entity\InstantWinOccurrence();
        } else {
            $occurrence = $this->getInstantWinOccurrenceMapper()->findById($occurrence_id);
        }
        $form  = $this->serviceLocator->get('playgroundgame_instantwinoccurrence_form');
        $form->bind($occurrence);

        $form->setData($data);

        $instantwin = $this->getGameMapper()->findById($data['instant_win_id']);
        $prize = null;
        if (isset($data['prize'])) {
            $prize = $this->getPrizeMapper()->findById($data['prize']);
        }

        if (!$form->isValid()) {
            return false;
        }

        $occurrence->setInstantWin($instantwin);
        $occurrence->setPrize($prize);
        $occurrence->populate($data);

        if ($occurrence_id) {
            $this->getInstantWinOccurrenceMapper()->insert($occurrence);
        } else {
            $this->getInstantWinOccurrenceMapper()->update($occurrence);
        }

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
    public function isInstantWinner($game, $user, $value = null)
    {
        $occurrenceMapper = $this->getInstantWinOccurrenceMapper();

        if ($game->getOccurrenceType()=='datetime') {
            $entry = $this->findLastActiveEntry($game, $user);

            // si date après date de gain et date de gain encore active alors desactive date de gain, et winner !
            $occurrence = $occurrenceMapper->checkDateOccurrenceByGameId($game);
        } elseif ($game->getOccurrenceType()=='code') {
            $occurrence = $occurrenceMapper->checkCodeOccurrenceByGameId($game, $value);
            if (!$occurrence) {
                return false;
            }
            $entry = $this->play($game, $user);
        }

        if (!$entry) {
            return false;
        }
        $this->getEventManager()->trigger(
            __FUNCTION__ . '.pre',
            $this,
            array('user' => $user, 'game' => $game, 'entry' => $entry)
        );
        $occurrence = $this->setOccurrenceEntry($game, $user, $entry, $occurrence);
        $this->getEventManager()->trigger(
            __FUNCTION__ . '.post',
            $this,
            array('user' => $user, 'game' => $game, 'entry' => $entry, 'occurrence' => $occurrence)
        );
        return $occurrence;
    }

    /**
     * @param \PlaygroundGame\Entity\Game $game
     * @param \PlaygroundUser\Entity\UserInterface $user
     */
    public function setOccurrenceEntry($game, $user, $entry, $occurrence = null)
    {
        $entryMapper = $this->getEntryMapper();
        $occurrenceMapper = $this->getInstantWinOccurrenceMapper();

        $entry->setActive(0);
        $entry->setWinner(false);
        $entry->setPoints(0);
        if ($occurrence) {
            $occurrence->setEntry($entry);
            $occurrence->setUser($user);
            $occurrence->setActive(0);
            $occurrence = $occurrenceMapper->update($occurrence);
            if ($occurrence->getWinning()) {
                $entry->setWinner(true);
            }
            if ($occurrence->getPrize()) {
                $entry->setPoints($occurrence->getPrize()->getPoints());
            }
        }
        $entry = $entryMapper->update($entry);

        return $occurrence;
    }

    /**
     * DEPRECATED
     */
    public function getEntriesHeader($game)
    {
        $header = parent::getEntriesHeader($game);
        $header['value'] = 1;
        $header['prize'] = 1;

        return $header;
    }

    /**
     * DEPRECATED
     * getGameEntries : All entries of a game
     *
     * @return Array of PlaygroundGame\Entity\Game
     */
    public function getGameEntries($header, $entries, $game)
    {
        $results = array();

        foreach ($entries as $k => $entry) {
            $entryData = json_decode($entry['playerData'], true);
            $winner = $entry['winner'];

            foreach ($header as $key => $v) {
                if (isset($entryData[$key]) && $key !=='id') {
                    $results[$k][$key] = (is_array($entryData[$key]))?implode(', ', $entryData[$key]):$entryData[$key];
                } elseif (array_key_exists($key, $entry)) {
                    $results[$k][$key] = ($entry[$key] instanceof \DateTime)?
                        $entry[$key]->format('Y-m-d'):
                        $entry[$key];
                } else {
                    $results[$k][$key] = '';
                }
            }
            // If the occurrenceType is code, this will be triggered for every entry. To be improved.
            if ($game->getOccurrenceType() === 'code' || ($game->getOccurrenceType() === 'datetime' && $winner)) {
                $entry = $this->getEntryMapper()->findById($entry['id']);
                $occurrence = $this->getInstantWinOccurrenceMapper()->findByEntry($entry);
                if ($occurrence !== null) {
                    $results[$k]['value'] = $occurrence->getValue();
                    if ($occurrence->getPrize()) {
                        $results[$k]['prize'] = $occurrence->getPrize()->getTitle();
                    }
                }
            }
        }

        return $results;
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
            $this->instantWinOccurrenceMapper = $this->serviceLocator->get(
                'playgroundgame_instantwinoccurrence_mapper'
            );
        }

        return $this->instantWinOccurrenceMapper;
    }

    /**
     * setInstantWinOccurrenceMapper
     *
     * @return InstantWin
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
            $this->prizeMapper = $this->serviceLocator->get('playgroundgame_prize_mapper');
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
