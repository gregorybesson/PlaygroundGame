<?php

namespace PlaygroundGame\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

class GameController extends AbstractActionController
{
    public function entryAction()
    {
        $gameId         = $this->getEvent()->getRouteMatch()->getParam('gameId');
        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        $game           = $this->getAdminGameService()->getGameMapper()->findById($gameId);
        $adapter = new DoctrineAdapter(new ORMPaginator( $this->getAdminGameService()->getEntryMapper()->queryByGame($game)));
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        return array(
            'entries' => $paginator,
            'game' => $game,
            'gameId' => $gameId
        );
    }

    // Used for Lottery, TreasureHunt and redifined for Quiz and InstantWin because it's slightly different
    public function downloadAction()
    {
        // magically create $content as a string containing CSV data
        $gameId         = $this->getEvent()->getRouteMatch()->getParam('gameId');
        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        $game           = $this->getAdminGameService()->getGameMapper()->findById($gameId);

        $entries = $this->getAdminGameService()->getEntryMapper()->findBy(array('game' => $game));

        $content        = "\xEF\xBB\xBF"; // UTF-8 BOM
        if (! $game->getAnonymousAllowed()) {
            $content       .= "ID;Pseudo;Civilité;Nom;Prénom;E-mail;Optin Newsletter;Optin partenaire;Adresse;CP;Ville;Téléphone;Mobile;Date d'inscription;Date de naissance;";
        }
        
        $header = array();
        $formPV = json_decode($game->getPlayerForm()->getForm(),true);
        foreach ($formPV as $element) {
            if (isset($element['line_text'][0]['name'])) {
                $header[$element['line_text'][0]['name']] = 1;
            }
            if (isset($element['line_password'][0]['name'])) {
                $header[$element['line_password'][0]['name']] = 1;
            }
            if (isset($element['line_hidden'][0]['name'])) {
                $header[$element['line_hidden'][0]['name']] = 1;
            }
            if (isset($element['line_email'][0]['name'])) {
                $header[$element['line_email'][0]['name']] = 1;
            }
            if (isset($element['line_radio'][0]['name'])) {
                $header[$element['line_radio'][0]['name']] = 1;
            }
            if (isset($element['line_checkbox'][0]['name'])) {
                $header[$element['line_checkbox'][0]['name']] = 1;
            }
            if (isset($element['line_dropdown'][0]['name'])) {
                $header[$element['line_dropdown'][0]['name']] = 1;
            }
            if (isset($element['line_paragraph'][0]['name'])) {
                $header[$element['line_paragraph'][0]['name']] = 1;
            }
            if (isset($element['line_upload'][0]['name'])) {
                $header[$element['line_upload'][0]['name']] = 1;
            }
        }

        if (count($entries)) {

            foreach ($header as $key => $data) {
                $content .= $key.';';
            }
            $content .= 'A Gagné ?;Date - H'
                ."\n";
            foreach ($entries as $e) {
                if (!$game->getAnonymousAllowed()) {
                    if($e->getUser()->getAddress2() != '') {
                        $adress2 = ' - ' . $e->getUser()->getAddress2();
                    } else {
                        $adress2 = '';
                    }
                    if($e->getUser()->getDob() != NULL) {
                        $dob = $e->getUser()->getDob()->format('Y-m-d');
                    } else {
                        $dob = '';
                    }
    
                    $content   .= $e->getUser()->getId()
                    . ";" . $e->getUser()->getUsername()
                    . ";" . $e->getUser()->getTitle()
                    . ";" . $e->getUser()->getLastname()
                    . ";" . $e->getUser()->getFirstname()
                    . ";" . $e->getUser()->getEmail()
                    . ";" . $e->getUser()->getOptin()
                    . ";" . $e->getUser()->getOptinPartner()
                    . ";" . $e->getUser()->getAddress() . $adress2
                    . ";" . $e->getUser()->getPostalCode()
                    . ";" . $e->getUser()->getCity()
                    . ";" . $e->getUser()->getTelephone()
                    . ";" . $e->getUser()->getMobile()
                    . ";" . $e->getUser()->getCreatedAt()->format('Y-m-d')
                    . ";" . $dob
                    . ";" ;
                }
                if ($e->getPlayerData()) {
                    $entryData = json_decode($e->getPlayerData(),true);
                    foreach($header as $k=>$v){
                        if(isset($entryData[$k])){
                            if (is_array($entryData[$k])) {
                                $content .= implode(', ', $entryData[$k]).';';
                            } else {
                                $content .= $entryData[$k].';';
                            }
                        } else{
                            $content .= ';';
                        }
                    }
                } else {
                    foreach($header as $k=>$v){
                        $content .= ';';
                    }
                }
                $content   .= $e->getWinner()
                . ";" . $e->getCreatedAt()->format('Y-m-d H:i:s')
                . "\n";
            }
        }

        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Encoding: UTF-8');
        $headers->addHeaderLine('Content-Type', 'text/csv; charset=UTF-8');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"entry.csv\"");
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Length', strlen($content));

        $response->setContent($content);

        return $response;
    }

    // Only used for Quiz and Lottery
    public function drawAction()
    {
        // magically create $content as a string containing CSV data
        $gameId         = $this->getEvent()->getRouteMatch()->getParam('gameId');
        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        $game           = $this->getAdminGameService()->getGameMapper()->findById($gameId);

        $winningEntries = $this->getAdminGameService()->draw($game);

        $content        = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content       .= "ID;Pseudo;Nom;Prenom;E-mail;Etat\n";

        foreach ($winningEntries as $e) {
            $etat = 'gagnant';

            $content   .= $e->getUser()->getId()
            . ";" . $e->getUser()->getUsername()
            . ";" . $e->getUser()->getLastname()
            . ";" . $e->getUser()->getFirstname()
            . ";" . $e->getUser()->getEmail()
            . ";" . $etat
            ."\n";
        }

        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Encoding: UTF-8');
        $headers->addHeaderLine('Content-Type', 'text/csv; charset=UTF-8');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"gagnants.csv\"");
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Length', strlen($content));

        $response->setContent($content);

        return $response;
    }
}
