<?php

namespace PlaygroundGame\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ErrorHandler;

use Zend\File\Transfer\Adapter\Http;
use Zend\Validator\File\Size;
use Zend\Validator\File\IsImage;

class PostVote extends Game implements ServiceManagerAwareInterface
{

    protected $postvoteMapper;
    protected $postvoteformMapper;
    protected $postVotePostMapper;
    protected $postVoteVoteMapper;
    protected $postVotePostElementMapper;

    public function getGameEntity()
    {
        return new \PlaygroundGame\Entity\PostVote;
    }

    public function uploadFileToPost($data, $game, $user)
    {
        $postvotePostMapper = $this->getPostVotePostMapper();
        $postVotePostElementMapper = $this->getPostVotePostElementMapper();

        $entryMapper = $this->getEntryMapper();
        $entry = $entryMapper->findLastActiveEntryById($game, $user);

        if (!$entry) {
            return 'falsefin0';
        }

        $post = $postvotePostMapper->findOneBy(array('entry' => $entry));

        if (! $post) {
            $post = new \PlaygroundGame\Entity\PostVotePost();
            $post->setPostvote($game);
            $post->setUser($user);
            $post->setEntry($entry);
            $post = $postvotePostMapper->insert($post);
        }

        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR . 'game' . $game->getId() . '_post'. $post->getId() . '_';
        $media_url = $this->getOptions()->getMediaUrl() . '/' . 'game' . $game->getId() . '_post'. $post->getId() . '_';

        $key = key($data);
        $uploadFile = $this->uploadFile($path, $data[$key]);

        if ($uploadFile) {
            $postElement = $postVotePostElementMapper->findOneBy(array('post' => $post, 'name' => $key));
            if (! $postElement) {
                $postElement = new \PlaygroundGame\Entity\PostVotePostElement();
            }
            $postElement->setName($key);
            $postElement->setPosition(0);
            $postElement->setValue($media_url.$uploadFile);
            $postElement->setPost($post);
            $postElement = $postVotePostElementMapper->insert($postElement);

            return $media_url.$uploadFile;

        } else {
            return false;
        }
    }

    public function deleteFilePosted($data, $game, $user)
    {
        $postvotePostMapper = $this->getPostVotePostMapper();
        $postVotePostElementMapper = $this->getPostVotePostElementMapper();

        $entryMapper = $this->getEntryMapper();
        $entry = $entryMapper->findLastActiveEntryById($game, $user);

        if (!$entry) {
            return 'falsefin0';
        }

        $post = $postvotePostMapper->findOneBy(array('entry' => $entry));
        $element = $postVotePostElementMapper->findOneBy(array('post' => $post->getId(), 'name' => $data['name']));

        if ($element) {
            $element = $postVotePostElementMapper->remove($element);
            if($element) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    /**
     *
     *
     *
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function createPost(array $data, $game, $user, $form)
    {

        $postvotePostMapper = $this->getPostVotePostMapper();
        $postVotePostElementMapper = $this->getPostVotePostElementMapper();

        $entryMapper = $this->getEntryMapper();
        $entry = $entryMapper->findLastActiveEntryById($game, $user);

        if (!$entry) {
            return false;
        }

        $post = $postvotePostMapper->findOneBy(array('entry' => $entry));

        if (! $post) {
            $post = new \PlaygroundGame\Entity\PostVotePost();
            $post->setPostvote($game);
            $post->setUser($user);
            $post->setEntry($entry);
            $post = $postvotePostMapper->insert($post);
        }

        //print_r($data);
        //die('FIN');

        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR . 'game' . $game->getId() . '_post'. $post->getId() . '_';
        $media_url = $this->getOptions()->getMediaUrl() . '/' . 'game' . $game->getId() . '_post'. $post->getId() . '_';
        $position=1;
        //$postVotePostElementMapper->removeAll($post);
        foreach ($data as $name => $value) {
            $postElement = $postVotePostElementMapper->findOneBy(array('post' => $post, 'name' => $name));
            if (! $postElement) {
                $postElement = new \PlaygroundGame\Entity\PostVotePostElement();
            }
            $postElement->setName($name);
            $postElement->setPosition($position);
            // TODO : Manage uploads
            if (is_array($value) && isset($value['tmp_name'])) {
				if ( ! $value['error'] ) {
                	ErrorHandler::start();
/*
                    $adapter = new \Zend\File\Transfer\Adapter\Http();
                    // 400ko
                    $size = new Size(array('max'=>400000));
                    $is_image = new IsImage('jpeg,png,gif,jpg');
                    $adapter->setValidators(array($size, $is_image), $value['name']);

                    if (!$adapter->isValid()) {
                        $dataError = $adapter->getMessages();
                        $error = array();
                        foreach ($dataError as $key=>$row) {
                            // TODO : remove the exception below once understood why it appears
                            if ($key != 'fileUploadErrorNoFile') {
                                $error[] = $row;
                            }
                        }

                        $form->setMessages(array($name=>$error ));

                        return false;
                    }
*/
					$value['name'] = $this->fileNewname($path, $value['name'], true);
                    move_uploaded_file($value['tmp_name'], $path . $value['name']);
                    $postElement->setValue($media_url . $value['name']);
                    ErrorHandler::stop(true);
                }
            } else {
                $postElement->setValue($value);
            }
            $postElement->setPost($post);
            $postElement = $postVotePostElementMapper->insert($postElement);
            $position++;
        }
        $postvotePostMapper->update($post);
        return $post;
    }

    /**
     *
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function confirmPost($game, $user)
    {
        $postvotePostMapper = $this->getPostVotePostMapper();

        $entryMapper = $this->getEntryMapper();
        $entry = $entryMapper->findLastActiveEntryById($game, $user);

        if (!$entry) {
            return false;
        }

        $post = $postvotePostMapper->findOneBy(array('entry' => $entry));

        if (! $post) {
            return false;
        }

        // The post is confirmed by user. I update the status and close the associated entry
        // Post validated by default, then the admin change the status
        $post->setStatus(2);
        $postvotePostMapper->update($post);

        $entry->setActive(0);
        $entryMapper->update($entry);
        
        $this->getEventManager()->trigger('complete_postvote.post', $this, array('user' => $user, 'game' => $game, 'entry' => $entry, 'post' => $post));
        
        return $post;
    }

    /**
     *
     * This service is ready for all types of games
     *
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function createForm(array $data, $game, $form=null)
    {

        $title ='';
        $description = '';

        if ($data['form_jsonified']) {
            $jsonPV = json_decode($data['form_jsonified']);
            foreach ($jsonPV as $element) {
                if ($element->form_properties) {
                    $attributes  = $element->form_properties[0];
                    $title       = $attributes->title;
                    $description = $attributes->description;

                    break;
                }
            }
        }
        if (!$form) {
            $form = new \PlaygroundGame\Entity\PostVoteForm();
        }
        $form->setPostvote($game);
        $form->setTitle($title);
        $form->setDescription($description);
        $form->setForm($data['form_jsonified']);
        $form->setFormTemplate($data['form_template']);

        $form = $this->getPostVoteFormMapper()->insert($form);

        return $form;
    }

    public function findArrayOfValidatedPosts($game, $filter, $search='')
    {
        //$posts = $this->getPostVotePostMapper()->findBy(array('postvote'=> $game, 'status' => 2));
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $postSort = '';
        $filterSearch = '';
        switch ($filter) {
            case 'random' :
                $postSort = 'ORDER BY e.value ASC';
                break;
            case 'vote' :
                $postSort = 'ORDER BY votesCount DESC';
                break;
            case 'date' :
                $postSort = 'ORDER BY p.createdAt DESC';
        }
        
        if ($search != '') {
            $filterSearch = " AND (u.username like '%" . $search . "%' OR u.lastname like '%" . $search . "%' OR u.firstname like '%" . $search . "%' OR e.value like '%" . $search . "%')";
        }
        
        $query = $em->createQuery('
            SELECT p, COUNT(v) AS votesCount
            FROM PlaygroundGame\Entity\PostVotePost p
            JOIN p.postvote g
            JOIN p.user u
            JOIN p.postElements e
            LEFT JOIN p.votes v
            WHERE g.id = :game
            ' . $filterSearch . '
            AND p.status = 2
            GROUP BY p.id
            ' . $postSort . '
        ');

        $query->setParameter('game', $game);
        $posts = $query->getResult();
        $arrayPosts = array();
        $i=0;
        foreach ($posts as $postRaw) {
            $data = array();
            $post = $postRaw[0];
            foreach ($post->getPostElements() as $element) {
                $data[$element->getPosition()] = $element->getValue();
            }
            $arrayPosts[$i]['data']  = $data;
            $arrayPosts[$i]['votes'] = count($post->getVotes());
            $arrayPosts[$i]['id']    = $post->getId();
            $arrayPosts[$i]['user']  = $post->getUser();
            $i++;
        }

        return $arrayPosts;
    }

    public function addVote($user = null, $ipAddress = '', $post)
    {
        $postvoteVoteMapper = $this->getPostVoteVoteMapper();
        $postId = $post->getId();

        if ($user) {
            $userId = $user->getId();
            $entryUser = count($postvoteVoteMapper->findBy(array('userId' => $userId, 'post' =>$postId)));
        } else {
            $entryUser =count($postvoteVoteMapper->findBy(array('ip' => $ipAddress, 'post' =>$postId)));
        }
        if ($entryUser && $entryUser > 0) {
            return false;
        } else {
            $vote = new \PlaygroundGame\Entity\PostVoteVote();
            $vote->setPost($post);
            $vote->setIp($ipAddress);
            if ($user) {
                $vote->setUserId($user->getId());
            }

            $postvoteVoteMapper->insert($vote);
            $game = $post->getPostvote();
            $this->getEventManager()->trigger('vote_postvote.post', $this, array('user' => $user, 'game' => $game, 'post' => $post,'vote' => $vote));
            
            return true;
        }
    }

    public function getPostVoteFormMapper()
    {
        if (null === $this->postvoteformMapper) {
            $this->postvoteformMapper = $this->getServiceManager()->get('playgroundgame_postvoteform_mapper');
        }

        return $this->postvoteformMapper;
    }

    public function setPostVoteFormMapper($postvoteformMapper)
    {
        $this->postvoteformMapper = $postvoteformMapper;

        return $this;
    }

    public function getPostVotePostElementMapper()
    {
        if (null === $this->postVotePostElementMapper) {
            $this->postVotePostElementMapper = $this->getServiceManager()->get('playgroundgame_postvotepostelement_mapper');
        }

        return $this->postVotePostElementMapper;
    }

    public function setPostVotePostElementMapper($postVotePostElementMapper)
    {
        $this->postVotePostElementMapper = $postVotePostElementMapper;

        return $this;
    }

    public function getPostVoteVoteMapper()
    {
        if (null === $this->postVoteVoteMapper) {
            $this->postVoteVoteMapper = $this->getServiceManager()->get('playgroundgame_postvotevote_mapper');
        }

        return $this->postVoteVoteMapper;
    }

    public function setPostVoteVoteMapper($postVoteVoteMapper)
    {
        $this->postVoteVoteMapper = $postVoteVoteMapper;

        return $this;
    }

    public function getPostVotePostMapper()
    {
        if (null === $this->postVotePostMapper) {
            $this->postVotePostMapper = $this->getServiceManager()->get('playgroundgame_postvotepost_mapper');
        }

        return $this->postVotePostMapper;
    }

    public function setPostVotePostMapper($postVotePostMapper)
    {
        $this->postVotePostMapper = $postVotePostMapper;

        return $this;
    }

    public function getPostVoteMapper()
    {
        if (null === $this->postvoteMapper) {
            $this->postvoteMapper = $this->getServiceManager()->get('playgroundgame_postvote_mapper');
        }

        return $this->postvoteMapper;
    }

    /**
     * setQuizQuestionMapper
     *
     * @param  QuizQuestionMapperInterface $quizquestionMapper
     * @return QuizQuestion
     */
    public function setPostVoteMapper($postvoteMapper)
    {
        $this->postvoteMapper = $postvoteMapper;

        return $this;
    }
}
