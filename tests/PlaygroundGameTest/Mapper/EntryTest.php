<?php

namespace PlaygroundGameTest\Mapper;

use \PlaygroundGame\Entity\Entry as EntryEntity;
use PlaygroundGameTest\Bootstrap;

class EntryTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundgame_entry_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindById()
    {
        $entry = new EntryEntity();
        $entry->setPoints(0);
        $this->tm->insert($entry);
        $this->assertEquals($entry, $this->tm->findById(1));
    }

    /**
     * @depends testFindById
     */
    public function testFindBy()
    {
        $entry = new EntryEntity();
        $entry->setPoints(0);
        $entry = $this->tm->insert($entry);
        $entry2 = $this->tm->findBy(array('id' => 1));
        $this->assertEquals($entry->getId(), $entry2[0]->getId());
    }

    /**
     * @depends testFindBy
     */
    public function testCountByGame()
    {
        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);

        $this->em->persist($game);
        $entry = new EntryEntity();
        $entry->setPoints(0);
        $entry->setGame($game);
        $entry = $this->tm->insert($entry);

        $this->assertEquals(1, $this->tm->countByGame($game));
    }

    /**
     * @depends testCountByGame
     */
    public function testQueryByGame()
    {
        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);

        $this->em->persist($game);
        $entry = new EntryEntity();
        $entry->setPoints(0);
        $entry->setGame($game);
        $this->tm->insert($entry);
        $return = $this->tm->queryByGame($game);
        $this->assertEquals("object", gettype($return));
        $this->assertEquals("Doctrine\ORM\Query", get_class($return));
    }

    /**
     * @depends testQueryByGame
     */
    public function testFindByGameId()
    {
        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);

        $this->em->persist($game);
        $entry = new EntryEntity();
        $entry->setPoints(0);
        $entry->setGame($game);
        $this->tm->insert($entry);

        $results = $this->tm->findByGameId(1);

        $this->assertEquals($entry->getId(), $results[0]->getId());
    }

    /**
     * @depends testFindByGameId
     */
    public function testUpdate()
    {
        $entry = new EntryEntity();
        $entry->setPoints(0);
        $entry = $this->tm->insert($entry);
        $entry->setPoints(1);
        $entry = $this->tm->update($entry);

        $this->assertEquals(1, $entry->getPoints());
    }

    /**
     * @depends testUpdate
     */
    public function testRemove()
    {
        $entry = new EntryEntity();
        $entry->setPoints(0);
        $entry = $this->tm->insert($entry);
        $id = $entry->getId();
        $this->tm->remove($entry);
        $this->assertEquals(array(), $this->tm->findAll());
    }

    /**
     * @depends testRemove
     */
    public function testFindAll()
    {
        $entries = $this->tm->findAll();
        foreach ($entries as $entry) {
            $this->tm->remove($entry);
        }

        $entry1 = new EntryEntity();
        $entry1->setPoints(1);
        $this->tm->insert($entry1);

        $entry2 = new EntryEntity();
        $entry2->setPoints(2);
        $this->tm->insert($entry2);

        $entry3 = new EntryEntity();
        $entry3->setPoints(3);
        $this->tm->insert($entry3);

        $entries = $this->tm->findAll();

        $this->assertEquals(3, count($entries));
    }



    /*public function testFindPlayersWithOneEntryBy()
    {
        $user = new \PlaygroundUser\Entity\User();
        $user->setEmail('mail@mail.fr');
        $user->setPassword('CeciEstUnPassword');
        $this->em->persist($user);

        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);

        $this->em->persist($game);
        $entry = new EntryEntity();
        $entry->setPoints(0);
        $entry->setGame($game);
        $entry->setUser($user);
        $this->tm->insert($entry);

        var_dump($this->tm->findPlayersWithOneEntryBy($game));
    }*/

    public function tearDown()
    {
        parent::tearDown();
    }
}
