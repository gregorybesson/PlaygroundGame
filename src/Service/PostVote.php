<?php

namespace PlaygroundGame\Service;

use Zend\Stdlib\ErrorHandler;
use ZfcDatagrid\Column;
use ZfcDatagrid\Action;
use ZfcDatagrid\Column\Formatter;
use ZfcDatagrid\Column\Type;
use ZfcDatagrid\Column\Style;
use ZfcDatagrid\Filter;
use Doctrine\ORM\Query\Expr;

class PostVote extends Game
{
    protected $postvoteMapper;
    protected $postvoteformMapper;
    protected $postVotePostMapper;
    protected $postVoteVoteMapper;
    protected $postVoteCommentMapper;
    protected $postVotePostElementMapper;
    protected $postVoteShareMapper;
    protected $postVoteViewMapper;

    public function getGameEntity()
    {
        return new \PlaygroundGame\Entity\PostVote;
    }

    public function getPath($post)
    {
        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        $path .= 'game' . $post->getPostVote()->getId() . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $path .= 'post'. $post->getId() . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    public function getMediaUrl($post)
    {
        $media_url = $this->getOptions()->getMediaUrl() . '/';
        $media_url .= 'game' . $post->getPostVote()->getId() . '/' . 'post'. $post->getId() . '/';

        return $media_url;
    }

    /**
     * @param boolean $entry
     */
    public function checkPost($entry)
    {
        $post = $this->getPostVotePostMapper()->findOneBy(array('entry' => $entry));

        if (! $post) {
            $post = new \PlaygroundGame\Entity\PostVotePost();
            $post->setPostvote($entry->getGame());
            $post->setUser($entry->getUser());
            $post->setEntry($entry);
            $post = $this->getPostVotePostMapper()->insert($post);
        }

        return $post;
    }

    public function uploadFileToPost($data, $game, $user)
    {
        $result = false;
        $entry = $this->findLastActiveEntry($game, $user);

        if (!$entry) {
            return '0';
        }

        $post = $this->checkPost($entry);
        $path = $this->getPath($post);
        $media_url = $this->getMediaUrl($post);

        $key = key($data);
        $uploadFile = $this->uploadFile($path, $data[$key]);

        if ($uploadFile) {
            $postElement = $this->getPostVotePostElementMapper()->findOneBy(array('post' => $post, 'name' => $key));
            if (! $postElement) {
                $postElement = new \PlaygroundGame\Entity\PostVotePostElement();
            }
            $postElement->setName($key);
            $postElement->setPosition(0);
            $postElement->setValue($media_url.$uploadFile);
            $postElement->setPost($post);
            $postElement = $this->getPostVotePostElementMapper()->insert($postElement);

            $result = $media_url.$uploadFile;
        }

        return $result;
    }

    public function deleteFilePosted($data, $game, $user)
    {
        $postvotePostMapper = $this->getPostVotePostMapper();
        $postVotePostElementMapper = $this->getPostVotePostElementMapper();

        $entry = $this->findLastActiveEntry($game, $user);

        if (!$entry) {
            return 'falsefin0';
        }

        $post = $postvotePostMapper->findOneBy(array('entry' => $entry));
        $element = $postVotePostElementMapper->findOneBy(array('post' => $post->getId(), 'name' => $data['name']));

        if ($element) {
            $element = $postVotePostElementMapper->remove($element);
            if ($element) {
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
     * @param  array $data
     * @return \PlaygroundGame\Entity\Game
     */
    public function createPost(array $data, $game, $user, $form)
    {
        $postvotePostMapper = $this->getPostVotePostMapper();
        $postVotePostElementMapper = $this->getPostVotePostElementMapper();

        $entry = $this->findLastActiveEntry($game, $user);

        if (!$entry) {
            return false;
        }

        $post = $this->checkPost($entry);
        $path = $this->getPath($post);
        $media_url = $this->getMediaUrl($post);
        $position=1;

        foreach ($data as $name => $value) {
            $postElement = $postVotePostElementMapper->findOneBy(array('post' => $post, 'name' => $name));
            if (! $postElement) {
                $postElement = new \PlaygroundGame\Entity\PostVotePostElement();
            }
            $postElement->setName($name);
            foreach($form as $e) {
                if ($e->getName() == $name) {
                    $postElement->setLabel($e->getLabel());
                    break;
                }
            }
            $postElement->setPosition($position);

            if (is_array($value) && isset($value['tmp_name'])) {
                // The file upload has been done in ajax but some weird bugs remain without it

                if (! $value['error']) {
                    ErrorHandler::start();
                    $value['name'] = $this->fileNewname($path, $value['name'], true);
                    move_uploaded_file($value['tmp_name'], $path . $value['name']);

                    if (getimagesize($path . $value['name'])) {
                        $image = $this->serviceLocator->get('playgroundcore_image_service');
                        $image->setImage($path . $value['name']);

                        if ($image->canCorrectOrientation()) {
                            $image->correctOrientation()->save();
                        }
                        $postElement->setValue($media_url . $value['name']);
                        
                        if (class_exists("Imagick")) {
                            $ext = pathinfo($value['name'], PATHINFO_EXTENSION);
                            $img = new \Imagick($path . $value['name']);
                            $img->cropThumbnailImage(100, 100);
                            $img->setImageCompression(\Imagick::COMPRESSION_JPEG);
                            $img->setImageCompressionQuality(75);
                            // Strip out unneeded meta data
                            $img->stripImage();
                            $img->writeImage($path . str_replace('.'.$ext, '-thumbnail.'.$ext, $value['name']));
                            ErrorHandler::stop(true);
                        }
                    } else {
                        $postElement->setValue($media_url . $value['name']);
                    }
                }
            } elseif (is_array($value) || $form->get($name) instanceof \Zend\Form\Element\Select) {
                $arValues = $form->get($name)->getValueOptions();
                $postElement->setValue($arValues[$value[0]]);
            } elseif (!empty($value)) {
                $postElement->setValue($value);
            }
            $post->addPostElement($postElement);
            // $postElement->setPost($post);
            // $postVotePostElementMapper->insert($postElement);
            $position++;
        }

        $postvotePostMapper->update($post);

        // If a preview step is not proposed, I confirmPost on this step
        $steps = $game->getStepsArray();
        $previewKey = array_search('preview', $steps);
        if (!$previewKey) {
            $post = $this->confirmPost($game, $user);
        }

        $this->getEventManager()->trigger(
            __FUNCTION__ . '.post',
            $this,
            [
                'user' => $user,
                'game' => $game,
                'post' => $post,
                'entry' => $entry,
            ]
        );

        return $post;
    }

    /**
     *
     * @return \PlaygroundGame\Entity\Game
     */
    public function confirmPost($game, $user)
    {
        $postvotePostMapper = $this->getPostVotePostMapper();

        $entryMapper = $this->getEntryMapper();
        $entry = $this->findLastActiveEntry($game, $user);

        if (!$entry) {
            return false;
        }

        $post = $postvotePostMapper->findOneBy(array('entry' => $entry));

        if (! $post) {
            return false;
        }

        // The post is confirmed by user. I update the status and close the associated entry
        // Post are validated by default, unless pre-moderation is enable for the game
        if ($game->getModerationType()) {
            $post->setStatus(1);
        } else {
            $post->setStatus(2);
        }

        $post = $postvotePostMapper->update($post);

        $entry->setActive(0);
        $entryMapper->update($entry);

        $this->getEventManager()->trigger(
            __FUNCTION__ . '.post',
            $this,
            [
                'user' => $user,
                'game' => $game,
                'entry' => $entry,
                'post' => $post,
            ]
        );

        return $post;
    }

    /**
     * This service moderate a post depending on the status
     */
    public function moderatePost($post, $status = null)
    {
        if ($status && strtolower($status) === 'validation') {
            $post->setStatus(2);
            $this->getPostVotePostMapper()->update($post);

            $this->getEventManager()->trigger(
                __FUNCTION__ .'.validation',
                $this,
                array('user' => $post->getUser(), 'game' => $post->getPostvote(), 'entry' => $post->getEntry(), 'post' => $post)
            );
        //} elseif ($status && strtolower($status) === 'rejection' && $post->getStatus() !== 9) {
        } elseif ($status && strtolower($status) === 'rejection') {
            // We reject the $post
            $post->setStatus(9);
            $this->getPostVotePostMapper()->update($post);

            // We signal we want to remove the initial points earned from the $post
            $entry = $post->getEntry();
            $entry->setPoints(-$entry->getPoints());

            $this->getEventManager()->trigger(
                __FUNCTION__ .'.rejection',
                $this,
                array('user' => $post->getUser(), 'game' => $post->getPostvote(), 'entry' => $entry, 'post' => $post)
            );

            // We set the points from the $entry to 0;
            $entry->setPoints(0);
            $entryMapper = $this->getEntryMapper();
            $entryMapper->update($entry);
        }
    }

    /**
     *
     * This service is ready for all types of games
     *
     * @param  array                  $data
     * @return \PlaygroundGame\Entity\Game
     */
    public function createForm(array $data, $game, $form = null)
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

    public function findArrayOfValidatedPosts($game, $user, $filter, $search = '')
    {
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
        $qb = $em->createQueryBuilder();
        $and = $qb->expr()->andx();
        
        $and->add($qb->expr()->eq('p.status', 2));

        $and->add($qb->expr()->eq('g.id', ':game'));
        $qb->setParameter('game', $game);
        
        if ($search != '') {
            $and->add(
                $qb->expr()->orX(
                    $qb->expr()->like('u.username', $qb->expr()->literal('%:search%')),
                    $qb->expr()->like('u.firstname', $qb->expr()->literal('%:search%')),
                    $qb->expr()->like('u.lastname', $qb->expr()->literal('%:search%')),
                    $qb->expr()->like('e.value', $qb->expr()->literal('%:search%')),
                    $qb->expr()->isNull('g.publicationDate')
                )
            );
            $qb->setParameter('search', $search);
        }
        
        if ('push' == $filter) {
            $and->add(
                $qb->expr()->andX(
                    $qb->expr()->eq('p.pushed', 1)
                )
            );
        }
        
        $qb->select('p, SUM(DISTINCT v.note) AS votesCount, SUM(distinct av.note) AS voted')
            ->from('PlaygroundGame\Entity\PostVotePost', 'p')
            ->innerJoin('p.postvote', 'g')
            ->leftJoin('p.user', 'u')
            ->innerJoin('p.postElements', 'e')
            ->leftJoin('p.votes', 'v')
            ->leftJoin('p.votes', 'av', 'WITH', 'av.user = :user')
            ->where($and)
            ->groupBy('p.id');
 
        if ($user) {
            $qb->setParameter('user', $user);
        } else {
            $qb->setParameter('user', null);
        }

        switch ($filter) {
            case 'random':
                $qb->orderBy('e.value', 'ASC');
                break;
            case 'vote':
                $qb->orderBy('votesCount', 'DESC');
                break;
            case 'date':
                $qb->orderBy('p.createdAt', 'DESC');
                break;
            case 'push':
                $qb->orderBy('p.createdAt', 'DESC');
                break;
            case 'id':
                $qb->orderBy('p.createdAt', 'ASC');
                break;
            default:
                $qb->orderBy('p.createdAt', 'ASC');
                break;
        }
        
        $query = $qb->getQuery();
        // echo $query->getSql();
        $posts = $query->getResult();
        $arrayPosts = array();
        $i=0;
        foreach ($posts as $postRaw) {
            $data = array();
            $post = $postRaw[0];
            if ($post) {
                foreach ($post->getPostElements() as $element) {
                    $data[$element->getPosition()] = $element->getValue();
                }
                $arrayPosts[$i]['post']  = $post;
                $arrayPosts[$i]['data']  = $data;
                $arrayPosts[$i]['votes'] = count($post->getVotes());
                $arrayPosts[$i]['voted'] = $postRaw['voted'];
                $arrayPosts[$i]['votesCount'] = $postRaw['votesCount'];
                $arrayPosts[$i]['id']    = $post->getId();
                $arrayPosts[$i]['user']  = $post->getUser();
                $arrayPosts[$i]['createdAt']  = $post->getCreatedAt();
                $i++;
            }
        }

        return $arrayPosts;
    }

    public function toggleVote($user, $ipAddress, $post, $comment = null, $note = 1)
    {
        $postvoteVoteMapper = $this->getPostVoteVoteMapper();
        $postId = $post->getId();
        $commentId = ($comment !== null) ? $comment->getId() : null;
        $vote = null;
        $game = $post->getPostvote();

        if ($user) {
            if ($comment == null) {
                $entryUser = count($postvoteVoteMapper->findBy(array('user' => $user, 'post' => $postId)));
                $vote = $postvoteVoteMapper->findOneBy(array('user' => $user, 'post' => $postId));
            } else {
                $entryUser = count($postvoteVoteMapper->findBy(array('user' => $user, 'post' => $postId, 'postComment' => $commentId)));
                $vote = $postvoteVoteMapper->findOneBy(array('user' => $user, 'post' => $postId, 'postComment' => $commentId));
            }
        } else {
            if ($comment == null) {
                $entryUser = count($postvoteVoteMapper->findBy(array('ip' => $ipAddress, 'post' => $postId)));
                $vote = $postvoteVoteMapper->findOneBy(array('ip' => $ipAddress, 'post' => $postId));
            } else {
                $entryUser = count($postvoteVoteMapper->findBy(array('ip' => $ipAddress, 'post' => $postId, 'postComment' => $commentId)));
                $vote = $postvoteVoteMapper->findOneBy(array('ip' => $ipAddress, 'post' => $postId, 'postComment' => $commentId));
            }
        }

        if ($entryUser && $entryUser > 0) {
            $postvoteVoteMapper->remove($vote);

            return 0;
        } else {
            $vote = new \PlaygroundGame\Entity\PostVoteVote();
            $vote->setPost($post);
            $vote->setIp($ipAddress);
            $vote->setNote($note);
            // If the vote is for a comment
            if ($comment != null) {
                $vote->setPostComment($comment);
                $vote->setPostvote($post->getPostvote());
            // else if the vote is for the post itself
            } else {
                $vote->setPostvote($post->getPostvote(), true);
            }

            if ($user) {
                $vote->setUser($user);
            }

            $postvoteVoteMapper->insert($vote);
        }

        $this->getEventManager()->trigger(
            __FUNCTION__ .'.post',
            $this,
            array('user' => $user, 'game' => $game, 'post' => $post, 'vote' => $vote)
        );

        return 1;
    }

    public function removeVote($user, $ipAddress, $post)
    {
        $postvoteVoteMapper = $this->getPostVoteVoteMapper();
        $postId = $post->getId();
        $commentId = ($comment !== null) ? $comment->getId() : null;
        $vote = null;
        $game = $post->getPostvote();

        if ($user) {
            if ($comment == null) {
                $entryUser = count($postvoteVoteMapper->findBy(array('user' => $user, 'post' => $postId)));
                $vote = $postvoteVoteMapper->findOneBy(array('user' => $user, 'post' => $postId));
            } else {
                $entryUser = count($postvoteVoteMapper->findBy(array('user' => $user, 'post' => $postId, 'postComment' => $commentId)));
                $vote = $postvoteVoteMapper->findOneBy(array('user' => $user, 'post' => $postId, 'postComment' => $commentId));
            }
        } else {
            if ($comment == null) {
                $entryUser = count($postvoteVoteMapper->findBy(array('ip' => $ipAddress, 'post' => $postId)));
                $vote = $postvoteVoteMapper->findOneBy(array('ip' => $ipAddress, 'post' => $postId));
            } else {
                $entryUser = count($postvoteVoteMapper->findBy(array('ip' => $ipAddress, 'post' => $postId, 'postComment' => $commentId)));
                $vote = $postvoteVoteMapper->findOneBy(array('ip' => $ipAddress, 'post' => $postId, 'postComment' => $commentId));
            }
        }

        $this->getEventManager()->trigger(
            __FUNCTION__ .'.post',
            $this,
            array('user' => $user, 'game' => $game, 'post' => $post, 'vote' => $vote)
        );

        return true;
    }

    public function addVote($user, $ipAddress, $post)
    {
        $postvoteVoteMapper = $this->getPostVoteVoteMapper();
        $postId = $post->getId();
        $commentId = ($comment !== null) ? $comment->getId() : null;
        $vote = null;
        $game = $post->getPostvote();

        if ($user) {
            if ($comment == null) {
                $entryUser = count($postvoteVoteMapper->findBy(array('user' => $user, 'post' => $postId)));
                $vote = $postvoteVoteMapper->findOneBy(array('user' => $user, 'post' => $postId));
            } else {
                $entryUser = count($postvoteVoteMapper->findBy(array('user' => $user, 'post' => $postId, 'postComment' => $commentId)));
                $vote = $postvoteVoteMapper->findOneBy(array('user' => $user, 'post' => $postId, 'postComment' => $commentId));
            }
        } else {
            if ($comment == null) {
                $entryUser = count($postvoteVoteMapper->findBy(array('ip' => $ipAddress, 'post' => $postId)));
                $vote = $postvoteVoteMapper->findOneBy(array('ip' => $ipAddress, 'post' => $postId));
            } else {
                $entryUser = count($postvoteVoteMapper->findBy(array('ip' => $ipAddress, 'post' => $postId, 'postComment' => $commentId)));
                $vote = $postvoteVoteMapper->findOneBy(array('ip' => $ipAddress, 'post' => $postId, 'postComment' => $commentId));
            }
        }

        if ($entryUser && $entryUser > 0) {
            return false;
        } else {
            $vote = new \PlaygroundGame\Entity\PostVoteVote();
            $vote->setPost($post);
            $vote->setIp($ipAddress);
            $vote->setNote(1);
            // If the vote is for a comment
            if ($comment != null) {
                $vote->setPostComment($comment);
                $vote->setPostvote($post->getPostvote());
            // else if the vote is for the post itself
            } else {
                $vote->setPostvote($post->getPostvote(), true);
            }
            if ($user) {
                $vote->setUser($user);
            }

            $postvoteVoteMapper->insert($vote);
            $game = $post->getPostvote();
            $this->getEventManager()->trigger(
                __FUNCTION__ .'.post',
                $this,
                array('user' => $user, 'game' => $game, 'post' => $post, 'vote' => $vote)
            );

            return true;
        }
    }

    public function addComment($user, $ipAddress, $post, $message = '', $category = null)
    {
        $postvoteCommentMapper = $this->getPostVoteCommentMapper();
        $game = $post->getPostvote();
        $comment = new \PlaygroundGame\Entity\PostVoteComment();
        $comment->setPost($post);
        $comment->setIp($ipAddress);
        $message = strip_tags($message);
        $comment->setMessage($message);
        if ($category !== null) {
            $comment->setCategory($category);
        }
        $comment->setPostvote($game);
        if ($user) {
            $comment->setUser($user);
        }

        $postvoteCommentMapper->insert($comment);
        
        $this->getEventManager()->trigger(
            __FUNCTION__ .'.post',
            $this,
            array('user' => $user, 'game' => $game, 'post' => $post, 'comment' => $comment)
        );

        return true;
    }

    public function addView($user, $ipAddress, $post)
    {
        $postvoteViewMapper = $this->getPostVoteViewMapper();
        $postView = new \PlaygroundGame\Entity\PostVoteView();
        $postView->setPost($post)
            ->setPostvote($post->getPostvote())
            ->setUser($user)
            ->setIp($ipAddress);
        $postvoteViewMapper->insert($postView);

        $this->getEventManager()->trigger(
            __FUNCTION__ . '.post',
            $this,
            array(
                'post' => $post
            )
        );

        return true;
    }

    public function addShare($post, $user)
    {
        $postvoteShareMapper = $this->getPostVoteShareMapper();
        $postShare = new \PlaygroundGame\Entity\PostVoteShare();
        $postShare->setPost($post)
            ->setPostvote($post->getPostvote())
            ->setUser($user)
            ->setOrigin('mail');
        $postvoteShareMapper->insert($postShare);

        $this->getEventManager()->trigger(
            __FUNCTION__ . '.post',
            $this,
            array(
                'post' => $post
            )
        );

        return true;
    }

    /**
     * Get all comments for this game
     */
    public function getCommentsForPostvote($postvote)
    {
        $postvoteCommentMapper = $this->getPostVoteCommentMapper();
        $comments = $postvoteCommentMapper->findBy(array('postvote' => $postvote), array('createdAt' => 'DESC'));

        return $comments ;
    }

    public function removeComment($user, $ipAddress, $messageId)
    {
        $postvoteCommentMapper = $this->getPostVoteCommentMapper();
        $comment = $postvoteCommentMapper->findOneBy(array('id' => $messageId));
        if ($comment->getUser()->getId() === $user->getId()) {
            $postvoteCommentMapper->remove($comment);
            $this->getEventManager()->trigger(
                'remove_comment_postvote.post',
                $this,
                array('user' => $user, 'comment' => $comment)
            );

            return true;
        }

        return false;
    }

    /**
     * DEPRECATED
     */
    public function getEntriesHeader($game)
    {
        $header = parent::getEntriesHeader($game);
        if ($game->getForm()) {
            $form = json_decode($game->getForm()->getForm(), true);
            foreach ($form as $element) {
                foreach ($element as $k => $v) {
                    if ($k !== 'form_properties') {
                        $header[$v[0]['name']] = 1;
                    }
                }
            }
        }
        if ($game->getVoteActive()) {
            $header['p.votes'] = 1;
        }

        $header['p.views'] = 1;
        $header['p.shares'] = 1;

        return $header;
    }

    public function getGrid($game)
    {
        $qb = $this->getEntriesQuery($game);
        // echo $qb->getQuery()->getSQL();
        // die('---');

        /* @var $grid \ZfcDatagrid\Datagrid */
        $grid = $this->serviceLocator->get('ZfcDatagrid\Datagrid');
        $grid->setTitle('Entries');
        $grid->setDataSource($qb);
        $grid->setDefaultItemsPerPage(50);

        $col = new Column\Select('id', 'p');
        $col->setLabel('Id');
        $col->setIdentity(true);
        $grid->addColumn($col);
        
        $colType = new Type\DateTime(
            'Y-m-d H:i:s',
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );
        $colType->setSourceTimezone('UTC');

        $col = new Column\Select('created_at', 'u');
        $col->setLabel('Created');
        $col->setType($colType);
        $grid->addColumn($col);

        $col = new Column\Select('username', 'u');
        $col->setLabel('Username');
        $grid->addColumn($col);

        $col = new Column\Select('email', 'u');
        $col->setLabel('Email');
        $grid->addColumn($col);

        $col = new Column\Select('firstname', 'u');
        $col->setLabel('Firstname');
        $grid->addColumn($col);

        $col = new Column\Select('lastname', 'u');
        $col->setLabel('Lastname');
        $grid->addColumn($col);

        $col = new Column\Select('winner', 'e');
        $col->setLabel('Status');
        $col->setReplaceValues(
            [
                0 => 'looser',
                1 => 'winner',
            ]
        );
        $grid->addColumn($col);

        $imageFormatter = new Formatter\Image();
        //Set the prefix of the image path and the prefix of the link
        $imageFormatter->setPrefix('/');
        $imageFormatter->setAttribute('width', '120');
        $imageFormatter->setLinkAttribute('class', 'pop');

        if ($game->getForm()) {
            $form = json_decode($game->getForm()->getForm(), true);
            foreach ($form as $element) {
                foreach ($element as $k => $v) {
                    if ($k !== 'form_properties') {
                        $querySelect = new Expr\Select("MAX(CASE WHEN f.name = '".$v[0]['name']."' THEN f.value ELSE '' END)");
                        if ($v[0]['type'] == 'file') {
                            $col = new Column\Select($querySelect, $v[0]['name']);
                            //$col->setType(new Type\Image());
                            $col->addFormatter($imageFormatter);
                        } else {
                            $col = new Column\Select($querySelect, $v[0]['name']);
                        }
                        $col->setLabel($v[0]['name']);
                        $col->setUserFilterDisabled(true);
                        $grid->addColumn($col);
                    }
                }
            }
        }

        if ($game->getVoteActive()) {
            $querySelect = new Expr\Select("COUNT(vo.id)");
            $col = new Column\Select($querySelect, "votes");
            $col->setLabel("Votes");
            $col->setUserFilterDisabled(true);
            $grid->addColumn($col);
        }
    
        $querySelect = new Expr\Select("COUNT(v.id)");
        $col = new Column\Select($querySelect, "views");
        $col->setLabel("Views");
        $col->setUserFilterDisabled(true);
        $grid->addColumn($col);

        $querySelect = new Expr\Select("COUNT(s.id)");
        $col = new Column\Select($querySelect, "shares");
        $col->setLabel("Shares");
        $col->setUserFilterDisabled(true);
        $grid->addColumn($col);

        $actions = new Column\Action();
        $actions->setLabel('');

        $viewAction = new Column\Action\Button();
        $viewAction->setLabel('Moderate');
        $rowId = $viewAction->getRowIdPlaceholder();
        $viewAction->setLink('/admin/game/postvote-moderation-edit/'.$rowId);
        $actions->addAction($viewAction);

        $grid->addColumn($actions);

        // $action = new Action\Mass();
        // $action->setTitle('This is incredible');
        // $action->setLink('/admin/game/postvote-mod-list');
        // $action->setConfirm(true);
        // $grid->addMassAction($action);

        return $grid;
    }

    public function getEntriesQuery($game)
    {
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');

        $qb = $em->createQueryBuilder();

        $selectString = '';

        if ($game->getForm()) {
            $form = json_decode($game->getForm()->getForm(), true);
            foreach ($form as $element) {
                foreach ($element as $k => $v) {
                    if ($k !== 'form_properties') {
                        $selectString .= "MAX(CASE WHEN f.name = '".$v[0]['name']."' THEN f.value ELSE '' END) AS " .$v[0]['name']. ",";
                    }
                }
            }
        }
        $selectString .= 'p, u, e';
        $qb->select($selectString)
            ->from('PlaygroundGame\Entity\PostVotePost', 'p')
            ->leftJoin('p.votes', 'vo')
            ->leftJoin('p.shares', 's')
            ->leftJoin('p.views', 'v')
            ->innerJoin('p.postElements', 'f')
            ->innerJoin('p.entry', 'e')
            ->leftJoin('p.user', 'u')
            ->where($qb->expr()->eq('e.game', ':game'))
            ->groupBy('p.id');
        
        $qb->setParameter('game', $game);

        return $qb;
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
            $postElements = $entry[0]->getPostElements();

            foreach ($header as $key => $v) {
                if (isset($entryData[$key]) && $key !=='id') {
                    $results[$k][$key] = (is_array($entryData[$key]))?implode(', ', $entryData[$key]):$entryData[$key];
                } elseif (array_key_exists($key, $entry)) {
                    $results[$k][$key] = ($entry[$key] instanceof \DateTime)?$entry[$key]->format('Y-m-d'):$entry[$key];
                } else {
                    $results[$k][$key] = '';
                }

                foreach ($postElements as $e) {
                    if ($key === $e->getName()) {
                        $results[$k][$key] = (is_array($e->getValue()))?implode(', ', $e->getValue()):$e->getValue();
                        break;
                    }
                }

                if ($key === 'votes') {
                    $results[$k][$key] = count($entry[0]->getVotes());
                }
                if ($key === 'views') {
                    $results[$k][$key] = count($entry[0]->getViews());
                }
                if ($key === 'shares') {
                    $results[$k][$key] = count($entry[0]->getShares());
                }
            }
        }

        return $results;
    }

    public function getPostVoteFormMapper()
    {
        if (null === $this->postvoteformMapper) {
            $this->postvoteformMapper = $this->serviceLocator->get('playgroundgame_postvoteform_mapper');
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
            $this->postVotePostElementMapper = $this->serviceLocator->get(
                'playgroundgame_postvotepostelement_mapper'
            );
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
            $this->postVoteVoteMapper = $this->serviceLocator->get('playgroundgame_postvotevote_mapper');
        }

        return $this->postVoteVoteMapper;
    }

    public function setPostVoteVoteMapper($postVoteVoteMapper)
    {
        $this->postVoteVoteMapper = $postVoteVoteMapper;

        return $this;
    }

    public function getPostVoteCommentMapper()
    {
        if (null === $this->postVoteCommentMapper) {
            $this->postVoteCommentMapper = $this->serviceLocator->get('playgroundgame_postvotecomment_mapper');
        }

        return $this->postVoteCommentMapper;
    }

    public function setPostVoteCommentMapper($postVoteCommentMapper)
    {
        $this->postVoteCommentMapper = $postVoteCommentMapper;

        return $this;
    }

    public function getPostVotePostMapper()
    {
        if (null === $this->postVotePostMapper) {
            $this->postVotePostMapper = $this->serviceLocator->get('playgroundgame_postvotepost_mapper');
        }

        return $this->postVotePostMapper;
    }

    public function getPostVoteShareMapper()
    {
        if (null === $this->postVoteShareMapper) {
            $this->postVoteShareMapper = $this->serviceLocator->get('playgroundgame_postvoteshare_mapper');
        }

        return $this->postVoteShareMapper;
    }

    public function getPostVoteViewMapper()
    {
        if (null === $this->postVoteViewMapper) {
            $this->postVoteViewMapper = $this->serviceLocator->get('playgroundgame_postvoteview_mapper');
        }

        return $this->postVoteViewMapper;
    }

    public function setPostVotePostMapper($postVotePostMapper)
    {
        $this->postVotePostMapper = $postVotePostMapper;

        return $this;
    }

    public function getPostVoteMapper()
    {
        if (null === $this->postvoteMapper) {
            $this->postvoteMapper = $this->serviceLocator->get('playgroundgame_postvote_mapper');
        }

        return $this->postvoteMapper;
    }

    /**
     * setQuizQuestionMapper
     *
     * @return PostVote
     */
    public function setPostVoteMapper($postvoteMapper)
    {
        $this->postvoteMapper = $postvoteMapper;

        return $this;
    }
}
