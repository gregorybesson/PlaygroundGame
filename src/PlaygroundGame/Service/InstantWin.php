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
                if ($game->getOccurrenceType()=='datetime') {
                    $this->scheduleOccurrences($game);
                }elseif ($game->getOccurrenceType()=='code') {
                    $this->generateCodeOccurrences($game, $data);
                }
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
                if ($game->getOccurrenceType()=='datetime') {
                    $this->scheduleOccurrences($game);
                }elseif ($game->getOccurrenceType()=='code') {
                    $this->generateCodeOccurrences($game, $data);
                }
            }
        }

        return $game;
    }

    public function generateCodeOccurrences($game, $data)
    {
        $available_characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-';
        $available_characters_nb = strlen($available_characters);
        if(!$game->getWinningOccurrenceNumber())
        {
            $game->setWinningOccurrenceNumber($game->getOccurrenceNumber());
        }
        if(!$data['occurrenceValueSize'])
        {
            $data['occurrenceValueSize'] = 8;   
        }
        $created = 0;
        for ($i=0; $i < $game->getOccurrenceNumber() ; $i++) {             
            $code = '';
            while(strlen($code)<$data['occurrenceValueSize']){
                $code .= $available_characters[rand(0, $available_characters_nb)];
            }
            if($this->getInstantWinOccurrenceMapper()->assertNoOther($instantwin, $line[0])){
                $occurrence = new \PlaygroundGame\Entity\InstantWinOccurrence();
                $occurrence->setInstantwin($game);
                $occurrence->setOccurrenceValue($code);
                $occurrence->setActive(1);
                $occurrence->setWinning($created < $game->getWinningOccurrenceNumber());
                $this->getInstantWinOccurrenceMapper()->insert($occurrence);
                if($this->getInstantWinOccurrenceMapper()->insert($occurrence)){
                    $created++;
                }
            }else{
                $i--;
            } 
        }
        return true;
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
        
        // Summertimes timezone management
        $timezone = $today->getTimezone();
        $transitions = $timezone->getTransitions($beginning->getTimestamp(), $end->getTimestamp());
        
        // There is a time transition between these datetimes()
        if(count($transitions) == 2){
            $shift = $transitions[0]['offset'] - $transitions[1]['offset'];
            if($shift > 0){
                $end->sub(new \DateInterval('PT'.$shift.'S'));
            } else{
                $end->add(new \DateInterval('PT'.$shift.'S'));
            }
        }
        
        // DateInterval takes the day @ 00:00 to calculate the difference between the dates, so 1 day is always missing
        // as we consider the last day @ 23:59:59 in Playground :)
        if($end->format('His') == 0){
            $end->add(new \DateInterval('P1D'));
        }
        
        //$dateInterval = $end->diff($beginning);
        
        $dateInterval = (int)(($end->getTimestamp() - $beginning->getTimestamp())/60);
        
        switch ($f) {
            case null:
            case 'game':
                // Je recherche tous les IG non gagnés
                $occurrences = $this->getInstantWinOccurrenceMapper()->findBy(array('instantwin' => $game));
                $nbOccurencesToCreate = $game->getOccurrenceNumber() - count($occurrences);
                if ($nbOccurencesToCreate > 0) {
                    for ($i=1;$i<=$nbOccurencesToCreate;$i++) {
                        $randomDate = $this->getRandomDate($beginning->format('U'), $end->format('U'));
                        $randomDate = \DateTime::createFromFormat('Y-m-d H:i:s', $randomDate);
                        $occurrence  = new \PlaygroundGame\Entity\InstantWinOccurrence();
                        $occurrence->setInstantwin($game);
                        $occurrence->setOccurrenceValue($randomDate->format('d/m/Y H:i:s'));
                        $occurrence->setActive(1);

                        $this->getInstantWinOccurrenceMapper()->insert($occurrence);
                    }
                }

                break;
            case 'hour':
                
                $occurrences = $this->getInstantWinOccurrenceMapper()->findBy(array('instantwin' => $game));
                $nbExistingOccurrences = count($occurrences);
                $nbInterval = (int) ($dateInterval/60);
                //$dateInterval->format('%a')*24 + $dateInterval->format('%h');

                // If a hour don't last 60min, I consider it as a hour anyway.
                if($dateInterval%60 > 0){
                    ++$nbInterval;
                }
                $nbOccurencesToCreate = $game->getOccurrenceNumber() - floor($nbExistingOccurrences/$nbInterval);

                /*echo "nb d'intervalles : " . $nbInterval . "<br>";
                echo "a creer par intervalle : " . $nbOccurencesToCreate . " existe deja : " . count($occurrences) . "<br>";
                
                echo "debut du jeu : " . $beginning->format('d/m/Y') . "<br>";
                echo "fin du jeu : " . $end->format('d/m/Y') . "<br>";*/

                $beginningDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y H:i:s'));
                $endDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y H'). ':59:59');

                if ($nbOccurencesToCreate > 0) {
                    for ($d=1;$d<=$nbInterval;$d++){
                        //echo "groupe : " . $d . " du " . $beginningDrawDate->format('d/m/Y H:i:s') ." au " . $endDrawDate->format('d/m/Y H:i:s') . "<br>";
                        for ($i=1;$i<=$nbOccurencesToCreate;$i++) {
                            $randomDate = $this->getRandomDate($beginningDrawDate->format('U'), $endDrawDate->format('U'));
                            $randomDate = \DateTime::createFromFormat('Y-m-d H:i:s', $randomDate);
                            $occurrence  = new \PlaygroundGame\Entity\InstantWinOccurrence();
                            $occurrence->setInstantwin($game);

                            $occurrence->setOccurrenceValue($randomDate->format('d/m/Y H:i:s'));
                            $occurrence->setActive(1);

                            $this->getInstantWinOccurrenceMapper()->insert($occurrence);
                            //echo $randomDate->format('d/m/Y H:i:s') . "<br>";
                        }
                        $beginningDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginningDrawDate->format('m/d/Y H'). ':00:00');
                        $beginningDrawDate->add(new \DateInterval('PT1H'));
                        $endDrawDate->add(new \DateInterval('PT1H'));
                    }
                }

                break;
            case 'day':

                $occurrences = $this->getInstantWinOccurrenceMapper()->findBy(array('instantwin' => $game));
                $nbExistingOccurrences = count($occurrences);
                $nbOccurencesToCreate = 0;
                $nbInterval = (int) ($dateInterval/(60*24));
                
                // Prise en compte des changements d'horaires
                // If a day don't last 24h, I consider it as a day anyway
                
                if($dateInterval%(60*24) > 0){
                    ++$nbInterval;
                }

                if($nbInterval > 0){
                    $nbOccurencesToCreate = $game->getOccurrenceNumber() - floor($nbExistingOccurrences/$nbInterval);
                }
                
                /*echo "nb d'intervalles : " . $nbInterval . "<br>";
                echo "a creer par intervalle : " . $nbOccurencesToCreate . " existe deja : " . count($occurrences) . "<br>";
                
                echo "debut du jeu : " . $beginning->format('d/m/Y H:i:s') . "<br>";
                echo "fin du jeu : " . $end->format('d/m/Y H:i:s') . "<br>";*/
                
                $beginningDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y H:i:s'));
                $endDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginning->format('m/d/Y'). ' 23:59:59');
                if ($nbOccurencesToCreate > 0) {
                    for ($d=1;$d<=$nbInterval;$d++){
                        for ($i=1;$i<=$nbOccurencesToCreate;$i++) {
                            $randomDate = $this->getRandomDate($beginningDrawDate->format('U'), $endDrawDate->format('U'));
                            $randomDate = \DateTime::createFromFormat('Y-m-d H:i:s', $randomDate);
                            $occurrence  = new \PlaygroundGame\Entity\InstantWinOccurrence();
                            $occurrence->setInstantwin($game);
                            $occurrence->setOccurrenceValue($randomDate->format('d/m/Y H:i:s'));
                            $occurrence->setActive(1);

                            $this->getInstantWinOccurrenceMapper()->insert($occurrence);
                            //echo $randomDate->format('d/m/Y H:i:s') . "<br>";
                        }
                        // As the first beginning date was not @ midnight, 
                        // I recreate the beginning date
                        $beginningDrawDate = \DateTime::createFromFormat('m/d/Y H:i:s', $beginningDrawDate->format('m/d/Y'). ' 00:00:00');
                        $beginningDrawDate->add(new \DateInterval('P1D'));
                        $endDrawDate->add(new \DateInterval('P1D'));
                    }
                }

                break;
            case 'week':
                // TODO : Je recherche tous les IG non gagnés pour chaque jour puis soustrais à ceux à créer
                $occurences = $this->getInstantWinOccurrenceMapper()->findBy(array('instantwin' => $game));
                $nbOccurencesToCreate = $game->getOccurrenceNumber() - count($occurences);
                //$nbWeeksInterval = ceil($dateInterval->format('%a')/7);
                $nbWeeksInterval = (int) ($dateInterval/(60*24*7));
                // If a week don't last 7d, I consider it as a week anyway.
                if($dateInterval%(60*24*7) > 0){
                    ++$nbWeeksInterval;
                }
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
                            $occurrence->setOccurrenceValue($randomDate->format('d/m/Y H:i:s'));
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
                //$nbMonthsInterval = $dateInterval->format('%m') + ceil($dateInterval->format('%d')/31);
                $nbMonthsInterval = (int) ($dateInterval/(60*24*30));
                // If a week don't last 30d, I consider it as a month anyway.
                if($dateInterval%(60*24*30) > 0){
                    ++$nbMonthsInterval;
                }
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
                            $occurrence->setOccurrenceValue($randomDate->format('d/m/Y H:i:s'));
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

    public function getCodeOccurencesFromCSV($file_name)
    {
        if (file_exists($file_name)){
            $csv_file = fopen($file_name, 'r');
            if ($csv_file){
                while (!feof($csv_file) ) 
                    $csv_content[] = fgetcsv($csv_file);
                fclose($csv_file);
                return $csv_content;
            }
        }
        return false;
    }

    public function setCodeOccurencesToCSV($game)
    {
        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        $file_name = $path.'occurences-'.$game->getId().'.csv';
        $csv_file = fopen($file_name, 'w');
        if ($csv_file){
            $occurrences = $this->getInstantWinOccurrenceMapper()->findByGameId($game);
            foreach ($occurrences as $occurrence) {
                fputcsv($csv_file, array($occurrence->getOccurrenceValue(), $occurrence->getWinning()));
            }
            fclose($csv_file);
            return $file_name;
        } 
        return false;       
    }

    public function uploadCodeOccurrences($data)
    {
        if (!empty($data['occurrences_file']['tmp_name'])) {
            $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
            $media_url = $this->getOptions()->getMediaUrl() . '/';
            $instantwin = $this->getGameMapper()->findById($data['instant_win_id']);

            // upload the csv file
            ErrorHandler::start();
            $data['occurrences_file']['name'] = $this->fileNewname($path, $data['instant_win_id'] . "-" . $data['occurrences_file']['name']);
            move_uploaded_file($data['occurrences_file']['tmp_name'], $path . $data['occurrences_file']['name']);
            ErrorHandler::stop(true);

            $csv_content = $this->getCodeOccurencesFromCSV(PUBLIC_PATH.$media_url.$data['occurrences_file']['name']);
            if ($csv_content){
                $created = 0;
                $already_in = 0;
                foreach ($csv_content as $line){
                    if($line){
                        if($this->getInstantWinOccurrenceMapper()->assertNoOther($instantwin, $line[0])){
                            // set active by default, can be changed by editing
                            $occurrence = $this->createOccurrence(array(
                                'id' => $data['id'],
                                'instant_win_id' => $data['instant_win_id'],
                                'occurrence_value' => $line[0],
                                'active' => 1,
                                'winning' => $line[1],
                                'prize_id' => $data['prize'],
                            ));
                            if($occurrence){
                                $created++;
                            }
                        }  
                        else {
                            $already_in++;
                        }
                    } 
                }
                // remove the csv file from folder
                unlink(PUBLIC_PATH.$media_url.$data['occurrences_file']['name']);
                return array($created, $already_in);
            }
        }
        return false;
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

        // default for datetime instantWin
        // For the datetime type, we only create the winning occurencies
        if (!isset($data['winning']))
            $data['winning'] = 1;
        $form->setData($data);

        $instantwin = $this->getGameMapper()->findById($data['instant_win_id']);
        $prize = null;
        if(isset($data['prize_id'])){
            $prize = $this->getPrizeMapper()->findById($data['prize_id']);
        }

        if (!$form->isValid()) {
            var_dump($form->getMessages());
            return false;
        }

        $occurrence->setInstantWin($instantwin);
        $occurrence->setPrize($prize);
        $occurrence->populate($data);

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

        $prize = null;
        if(isset($data['prize'])){
        	$prize = $this->getPrizeMapper()->findById($data['prize']);
        }

        if (!$form->isValid()) {
            return false;
        }

        $occurrence->setPrize($prize);

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
        $this->getEventManager()->trigger('complete_instantwin.post', $this, array('user' => $user, 'game' => $game, 'entry' => $entry));

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