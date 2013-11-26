<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\InstantWin;
use PlaygroundGameTest\Bootstrap;

class InstantWinTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_instantwinoccurrence_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindPlayedByGame()
    {
        $game = new InstantWin();
        $game->setTitle('CeciEstUnTitre');
        $game->setId(1);
        $game->setIdentifier('gameid');


        $entry = new \PlaygroundGame\Entity\Entry();
        $entry->setPoints('points');
        $entryMapper = $this->sm->get('playgroundgame_entry_mapper');
        $entryMapper->insert($entry);

        $occurrence = new \PlaygroundGame\Entity\InstantWinOccurrence();
        $occurrence->setValue("PLAYED");
        $occurrence->setInstantwin($game);
        $occurrence->setEntry($entry);
        $occurrence = $this->tm->insert($occurrence);

        $this->assertEquals($occurrence, current($this->tm->findByGameId($game)));
        $this->assertEquals($occurrence, $this->tm->queryPlayedByGame($game)->getSingleresult());
        $this->tm->remove($occurrence);
    }

    public function testFindPlayedByGameNoPlayed()
    {
        $game = new InstantWin();
        $game->setTitle('CeciEstUnTitre');
        $game->setId(1);
        $game->setIdentifier('gameid');

        $entry = new \PlaygroundGame\Entity\Entry();
        $entry->setPoints('points');
        $entryMapper = $this->sm->get('playgroundgame_entry_mapper');
        $entryMapper->insert($entry);

        $occurrence = new \PlaygroundGame\Entity\InstantWinOccurrence();
        $occurrence->setValue("Not PLAYED");
        $occurrence->setInstantwin($game);
        $occurrence = $this->tm->insert($occurrence);

        $this->assertEquals($occurrence, current($this->tm->findByGameId($game)));
        $this->assertEmpty($this->tm->queryPlayedByGame($game)->getResult());
        $this->tm->remove($occurrence);
    }

    public function testFindPlayedByGameNoOccurrence()
    {
        $game = new InstantWin();
        $game->setId(1);
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');

        $this->assertEmpty($this->tm->findByGameId($game));
        $this->assertEmpty($this->tm->queryPlayedByGame($game)->getResult());
    }

    public function tearDown()
    {
        $dbh = $this->em->getConnection();
        unset($this->tm);
        unset($this->sm);
        unset($this->em);
        parent::tearDown();
    }
}
