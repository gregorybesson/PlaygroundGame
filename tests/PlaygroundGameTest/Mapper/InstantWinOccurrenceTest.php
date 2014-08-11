<?php

namespace PlaygroundGameTest\Mapper;

use PlaygroundGame\Entity\InstantWin;
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

    public function testQueryPlayedByGame()
    {
        $game = new InstantWin();
        $game->setTitle('CeciEstUnTitre');
        $game->setId(1);
        $game->setIdentifier('gameid');


        $entry = new \PlaygroundGame\Entity\Entry();
        $entry->setPoints(0);
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

    public function testQuery1Winning1PlayedByGame()
    {
        $game = new InstantWin();
        $game->setTitle('CeciEstUnTitre');
        $game->setId(1);
        $game->setIdentifier('gameid');

        $entry = new \PlaygroundGame\Entity\Entry();
        $entry->setPoints(0);
        $entryMapper = $this->sm->get('playgroundgame_entry_mapper');
        $entryMapper->insert($entry);

//         One winning played occurrence
        $occurrence = new \PlaygroundGame\Entity\InstantWinOccurrence();
        $occurrence->setValue("PLAYED");
        $occurrence->setInstantwin($game);
        $occurrence->setEntry($entry);
        $occurrence->setWinning(1);
        $occurrence = $this->tm->insert($occurrence);

        $this->assertEquals($occurrence, current($this->tm->findByGameId($game)));
        $this->assertEquals($occurrence, $this->tm->queryPlayedByGame($game)->getSingleresult());
        $this->assertEquals($occurrence, $this->tm->queryWinningPlayedByGame($game)->getSingleresult());
        $this->tm->remove($occurrence);
    }

    public function testQuery0Winning1PlayedByGame()
    {
        $game = new InstantWin();
        $game->setTitle('CeciEstUnTitre');
        $game->setId(1);
        $game->setIdentifier('gameid');

        $entry = new \PlaygroundGame\Entity\Entry();
        $entry->setPoints(0);
        $entryMapper = $this->sm->get('playgroundgame_entry_mapper');
        $entryMapper->insert($entry);

//         One loosing played occurrence
        $occurrence = new \PlaygroundGame\Entity\InstantWinOccurrence();
        $occurrence->setValue("PLAYED");
        $occurrence->setInstantwin($game);
        $occurrence->setEntry($entry);
        $occurrence->setWinning(0);
        $occurrence = $this->tm->insert($occurrence);

        $this->assertEquals($occurrence, current($this->tm->findByGameId($game)));
        $this->assertEquals($occurrence, $this->tm->queryPlayedByGame($game)->getSingleresult());
        $this->assertEmpty($this->tm->queryWinningPlayedByGame($game)->getResult());
        $this->tm->remove($occurrence);
    }

    public function testQueryPlayedByGameNoPlayed()
    {
        $game = new InstantWin();
        $game->setTitle('CeciEstUnTitre');
        $game->setId(1);
        $game->setIdentifier('gameid');

        $entry = new \PlaygroundGame\Entity\Entry();
        $entry->setPoints(0);
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

    public function testQueryPlayedByGameNoOccurrence()
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
        parent::tearDown();
    }
}
