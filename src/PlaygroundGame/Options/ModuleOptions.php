<?php

namespace PlaygroundGame\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions implements GameEditOptionsInterface
{
    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;

    /**
     * drive path to game media files
     */
    protected $media_path = 'public/media/game';

    protected $media_path_mission = 'public/media/mission';

    /**
     * url path to game media files
     */
    protected $media_url = 'media/game';

    protected $media_url_mission = 'media/mission';

    /**
     * core_layout config
     */
    protected $core_layout = array();

    /**
     * @var string
     */
    protected $emailFromAddress = '';
	
	/**
     * @var string
     */
    protected $defaultSubjectLine = '';
	
	/**
     * @var string
     */
	protected $participationSubjectLine = '';
	
	/**
     * @var string
     */
	protected $shareSubjectLine = '';

    /**
     * @var string
     */
    protected $gameEntityClass = 'PlaygroundGame\Entity\Game';

    /**
     * Set game entity class name
     *
     * @param $gameEntityClass
     * @return ModuleOptions
     */
    public function setGameEntityClass($gameEntityClass)
    {
        $this->gameEntityClass = $gameEntityClass;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGameEntityClass()
    {
        return $this->gameEntityClass;
    }

    /**
     * Set media path
     *
     * @param  string                           $media_path
     * @return \PlaygroundGame\Options\ModuleOptions
     */
    public function setMediaPath($media_path)
    {
        $this->media_path = $media_path;

        return $this;
    }

    /**
     * @return string
     */
    public function getMediaPath()
    {
        return $this->media_path;
    }

    /**
     *
     * @param  string                           $media_url
     * @return \PlaygroundGame\Options\ModuleOptions
     */
    public function setMediaUrl($media_url)
    {
        $this->media_url = $media_url;

        return $this;
    }

    /**
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->media_url;
    }
    
    /**
     * Set media path
     *
     * @param  string                           $media_path
     * @return \PlaygroundGame\Options\ModuleOptions
     */
    public function setMediaPathMission($media_path_mission)
    {
        $this->media_path_mission = $media_path_mission;

        return $this;
    }

    /**
     * @return string
     */
    public function getMediaPathMission()
    {
        return $this->media_path_mission;
    }

    /**
     *
     * @param  string                           $media_url
     * @return \PlaygroundGame\Options\ModuleOptions
     */
    public function setMediaUrlMission($media_url_mission)
    {
        $this->media_url_mission = $media_url_mission;

        return $this;
    }

    /**
     * @return string
     */
    public function getMediaUrlMission()
    {
        return $this->media_url_mission;
    }

	
	public function setEmailFromAddress($emailFromAddress)
    {
        $this->emailFromAddress = $emailFromAddress;

        return $this;
    }
	
	public function getEmailFromAddress()
    {
        return $this->emailFromAddress;
    }
	
	public function setDefaultSubjectLine($defaultSubjectLine)
    {
        $this->defaultSubjectLine = $defaultSubjectLine;

        return $this;
    }
	
	public function getDefaultSubjectLine()
    {
        return $this->defaultSubjectLine;
    }
	
	public function setParticipationSubjectLine($participationSubjectLine)
    {
        $this->participationSubjectLine = $participationSubjectLine;

        return $this;
    }
	
	public function getParticipationSubjectLine()
    {
        return $this->participationSubjectLine;
    }
	
	public function setShareSubjectLine($shareSubjectLine)
    {
        $this->shareSubjectLine = $shareSubjectLine;

        return $this;
    }
	
	public function getShareSubjectLine()
    {
        return $this->shareSubjectLine;
    }

    /**
     *
     * @param  string                           $core_layout
     * @return \PlaygroundGame\Options\ModuleOptions
     */
    public function setCoreLayout($core_layout)
    {
        $this->core_layout = $core_layout;

        return $this;
    }

    /**
     * @return string
     */
    public function getCoreLayout()
    {
        return $this->core_layout;
    }

}