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
        $entry->setPoints('DesPoints');
        $this->tm->insert($entry);
        $this->assertEquals($entry, $this->tm->findById(1));
    }

    public function testFindBy() 
    {
        $entry = new EntryEntity();
        $entry->setPoints('DesPoints');
        $entry = $this->tm->insert($entry);
        $entry2 = $this->tm->findBy(array('id' => 1));
        $this->assertEquals($entry->getId(), $entry2[0]->getId());
    }

    public function testCountByGame() 
    {
        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);

        $this->em->persist($game);
        $entry = new EntryEntity();
        $entry->setPoints('DesPoints');
        $entry->setGame($game);
        $entry = $this->tm->insert($entry);

        $this->assertEquals(1, $this->tm->countByGame($game));
    }

    public function testQueryByGame() 
    {
        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);

        $this->em->persist($game);
        $entry = new EntryEntity();
        $entry->setPoints('DesPoints');
        $entry->setGame($game);
        $this->tm->insert($entry);
        $return = $this->tm->queryByGame($game);
        $this->assertEquals("object", gettype($return));
        $this->assertEquals("Doctrine\ORM\Query", get_class($return));
    }

    public function testFindByGameId() 
    {
        $game = new \PlaygroundGame\Entity\Quiz();
        $game->setTitle('CeciEstUnTitre');
        $game->setIdentifier('gameid');
        $game->setWinners(2);
        $game->setSubstitutes(2);

        $this->em->persist($game);
        $entry = new EntryEntity();
        $entry->setPoints('DesPoints');
        $entry->setGame($game);
        $this->tm->insert($entry);

        $this->assertEquals($entry->getId(), $this->tm->findByGameId(1)[0]->getId());
    }

    public function testUpdate() 
    {
        $entry = new EntryEntity();
        $entry->setPoints('DesPoints');
        $entry = $this->tm->insert($entry);
        $entry->setPoints('DesNouveauxPoints');
        $entry = $this->tm->update($entry);

        $this->assertEquals('DesNouveauxPoints', $entry->getPoints());
    }

    public function testRemove() 
    {
        $entry = new EntryEntity();
        $entry->setPoints('DesPoints');
        $entry = $this->tm->insert($entry);
        $id = $entry->getId();
        $this->tm->remove($entry);
        $this->assertEquals(array(), $this->tm->findAll());
    }

    public function testFindAll() 
    {
        $entries = $this->tm->findAll();
        foreach ($entries as $entry) {
            $this->tm->remove($entry);
        }

        $entry = new EntryEntity();
        $entry->setPoints('DesPoints1');
        $entry = $this->tm->insert($entry);
        $entry = new EntryEntity();
        $entry->setPoints('DesPoints2');
        $entry = $this->tm->insert($entry);
        $entry = new EntryEntity();
        $entry->setPoints('DesPoints3');
        $entry = $this->tm->insert($entry);
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
        $entry->setPoints('DesPoints');
        $entry->setGame($game);
        $entry->setUser($user);
        $this->tm->insert($entry);

        var_dump($this->tm->findPlayersWithOneEntryBy($game));
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
