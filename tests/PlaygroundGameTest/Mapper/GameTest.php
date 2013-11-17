<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\Game as GameEntity;
use PlaygroundGameTest\Bootstrap;

class GameTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_game_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindByIdentifier()
    {
        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);
        $game = $this->tm->insert($game);
        $this->assertEquals($game->getIdentifier(), $this->tm->findByIdentifier($game->getIdentifier())->getIdentifier());
    }

    public function testFindById()
    {
        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);
        $game = $this->tm->insert($game);
        $this->assertEquals($game->getId(), $this->tm->findById($game->getId())->getId());
    }

    public function testUpdate()
    {
        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);
        $game = $this->tm->insert($game);
        $game->setTitle('CeciEstUnNouveauTitre');
        $game = $this->tm->update($game);
        $this->assertEquals('CeciEstUnNouveauTitre', $game->getTitle());
    }

    

    public function testFindBy()
    {
        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);
        $game = $this->tm->insert($game);
        $id = $game->getId();
        $games = $this->tm->findBy(array('id' => $id), array('id' => 'ASC'));
        $this->assertEquals($id, $games[0]->getId());
    }

    public function testRemove()
    {
        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);
        $game = $this->tm->insert($game);
        $id = $game->getId();
        $this->tm->remove($game);
        $this->assertNull($this->tm->findById($id));
    }

    /*public function testFindAll()
    {
        $games = $this->tm->findAll();
        foreach ($games as $game) {
            $this->tm->remove($game);
        }

        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre1');
        $game->setIdentifier('gameid1');
        $game->setWinners(2);
        $game->setSubstitutes(2);
        $game = $this->tm->insert($game);
        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre2');
        $game->setIdentifier('gameid2');
        $game->setWinners(2);
        $game->setSubstitutes(2);
        $game = $this->tm->insert($game);
        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre3');
        $game->setIdentifier('gameid3');
        $game->setWinners(2);
        $game->setSubstitutes(2);
        $game = $this->tm->insert($game);

        $games = $this->tm->findAll();
        $this->assertEquals('CeciEstUnTitre1', $games[0]->getTitle());
        $this->assertEquals('CeciEstUnTitre2', $games[1]->getTitle());
        $this->assertEquals('CeciEstUnTitre3', $games[2]->getTitle());
    }*/

    public function tearDown()
    {
        $dbh = $this->em->getConnection();
        unset($this->tm);
        unset($this->sm);
        unset($this->em);
        parent::tearDown();
    }
}
