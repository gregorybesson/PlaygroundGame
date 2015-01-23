<?php
namespace PlaygroundGame\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"quiz" = "Quiz", "lottery" = "Lottery", "instantwin" =
 * "InstantWin", "postvote" = "PostVote"})
 * @ORM\Table(name="game")
 * @Gedmo\TranslationEntity(class="PlaygroundGame\Entity\GameTranslation")
 */
abstract class Game implements InputFilterAwareInterface, Translatable
{
    // not yet published
    const GAME_SCHEDULE  = 'scheduled';
    // published and not yet started
    const GAME_PUBLISHED  = 'published';
    // published and game in progress
    const GAME_IN_PROGRESS = 'in progress';
    // published and game finished
    const GAME_FINISHED   = 'finished';
    // closed
    const GAME_CLOSED = 'closed';

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    protected $locale;

    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\PlaygroundPartnership\Entity\Partner")
     */
    protected $partner;

    /**
     * Implementer ManyToOne(targetEntity="PrizeCategory") avec hydrator sur le formulaire
     * @ORM\Column(name="prize_category", type="integer", nullable=true)
     */
    protected $prizeCategory;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=false)
     */
    protected $identifier;

    /**
     * @ORM\OneToOne(targetEntity="PlayerForm", mappedBy="game", cascade={"persist","remove"})
     **/
    protected $playerForm;

    /**
     * @ORM\Column(name="main_image", type="string", length=255, nullable=true)
     */
    protected $mainImage;

    /**
     * @ORM\Column(name="second_image", type="string", length=255, nullable=true)
     */
    protected $secondImage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $canal;

    /**
     * @ORM\Column(name="broadcast_facebook",type="boolean", nullable=true)
     */
    protected $broadcastFacebook = 0;

    /**
     * @ORM\Column(name="broadcast_platform",type="boolean", nullable=true)
     */
    protected $broadcastPlatform = 0;

    /**
     * @ORM\Column(name="broadcast_embed",type="boolean", nullable=true)
     */
    protected $broadcastEmbed = 0;

    /**
     * @ORM\Column(name="push_home",type="boolean", nullable=true)
     */
    protected $pushHome = 0;

    /**
     * @ORM\Column(name="display_home",type="boolean", nullable=true)
     */
    protected $displayHome = 0;

    /**
     * @ORM\Column(name="mail_winner",type="boolean", nullable=true)
     */
    protected $mailWinner = 0;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="mail_winner_block", type="text", nullable=true)
     */
    protected $mailWinnerBlock;

    /**
     * @ORM\Column(name="mail_looser",type="boolean", nullable=true)
     */
    protected $mailLooser = 0;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="mail_looser_block", type="text", nullable=true)
     */
    protected $mailLooserBlock;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $active = 0;

    /**
     * @ORM\Column(name="anonymous_allowed",type="boolean", nullable=true)
     */
    protected $anonymousAllowed = 0;
    
    /**
     * This column can be filled in when anonymousAllowed = 1.
     * If you put a value, it has to be a field key from playerdata. This key will
     * then be used to identify a player (generally 'email')
     * 
     * @ORM\Column(name="anonymous_identifier", type="text", nullable=true)
     */
    protected $anonymousIdentifier;

    /**
     * @ORM\Column(name="publication_date", type="datetime", nullable=true)
     */
    protected $publicationDate;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    protected $startDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * @ORM\Column(name="close_date", type="datetime", nullable=true)
     */
    protected $closeDate;

    /**
     * play limitation. 0 : No limit
     *
     * @ORM\Column(name="play_limit", type="integer", nullable=false)
     */
    protected $playLimit = 0;

    /**
     * this field is taken into account only if playLimit<>0.
     * if 'always' only $playLimit play by person for this game
     * if 'day' only $playLimit play by person a day
     * if 'week' only $playLimit play by person a week
     * if 'month' only $playLimit play by person a month
     * if 'year' only $playLimit play by person a year
     *
     * @ORM\Column(name="play_limit_scale", type="string", nullable=true)
     */
    protected $playLimitScale;

    /**
     * this field is used for offering a complementary play entry
     * (for example, when the player share the game). The entries
     * of type 'bonus' won't be taken into account in the calaculation
     * of the authorized playLimit.
     *
     * if 'none' or null no play bonus is offered
     * if 'per_entry' a play bonus is offered for each entry
     * if 'one' only one play bonus is offered for every entries of the game
     *
     * @ORM\Column(name="play_bonus", type="string", nullable=true)
     */
    protected $playBonus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $layout;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $stylesheet;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="welcome_block", type="text", nullable=true)
     */
    protected $welcomeBlock;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    protected $termsBlock;

    /**
     * @ORM\Column(name="terms_optin", type="boolean", nullable=true)
     */
    protected $termsOptin = 0;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    protected $conditionsBlock;

    // Adherence CMS de ces blocs à revoir
    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    protected $columnBlock1;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    protected $columnBlock2;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    protected $columnBlock3;

    /**
     * @ORM\OneToMany(targetEntity="Prize", mappedBy="game", cascade={"persist","remove"}, orphanRemoval=true)
     */
    private $prizes;

    /**
     * @ORM\Column(name="fb_app_id", type="string", nullable=true)
     */
    protected $fbAppId;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="fb_page_tab_title", type="string", length=255, nullable=true)
     */
    protected $fbPageTabTitle;

    /**
     * @ORM\Column(name="fb_page_tab_image", type="string", length=255, nullable=true)
     */
    protected $fbPageTabImage;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="fb_share_message", type="text", nullable=true)
     */
    protected $fbShareMessage;

    /**
     * @ORM\Column(name="fb_share_image", type="string", length=255, nullable=true)
     */
    protected $fbShareImage;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="fb_request_message", type="text", nullable=true)
     */
    protected $fbRequestMessage;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $fbFan = 0;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="fb_fan_gate", type="text", nullable=true)
     */
    protected $fbFanGate;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="tw_share_message", type="string", length=255, nullable=true)
     */
    protected $twShareMessage;

    /**
     * @ORM\Column(name="steps", type="string", length=255, nullable=true)
     */
    protected $steps = '{"0":"index","1":"play","2":"result","3":"bounce"}';

    /**
     * @ORM\Column(name="steps_views", type="string", length=255, nullable=true)
     */
    protected $stepsViews = '{"index":{},"play":{},"result":{},"bounce":{}}';

    /**
     * Doctrine accessible value of discriminator (field 'type' is not
     * accessible through query)
     * And I want to be able to sort game collection based on type
     * http://www.doctrine-project.org/jira/browse/DDC-707
     * @ORM\Column(name="class_type", type="string", length=255, nullable=false)
     */
    protected $classType;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    public function __construct()
    {
    	$this->prizes = new ArrayCollection();
    }

    /**
     * @PrePersist
     */
    public function createChrono ()
    {
        $this->createdAt = new \DateTime("now");
        $this->updatedAt = new \DateTime("now");
    }

    /**
     * @PreUpdate
     */
    public function updateChrono ()
    {
        $this->updatedAt = new \DateTime("now");
    }

    /**
     *
     * @return the $id
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     *
     * @param field_type $id
     */
    public function setId ($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return the $playerForm
     */
    public function getPlayerForm()
    {
        return $this->playerForm;
    }

	/**
     * @param field_type $playerForm
     */
    public function setPlayerForm($playerForm)
    {
        $this->playerForm = $playerForm;

        return $this;
    }

	/**
     *
     * @return the unknown_type
     */
    public function getPartner ()
    {
        return $this->partner;
    }

    /**
     *
     * @param unknown_type $partner
     */
    public function setPartner ($partner)
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     *
     * @param unknown_type $prizeCategory
     */
    public function setPrizeCategory ($prizeCategory)
    {
        $this->prizeCategory = $prizeCategory;

        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getPrizeCategory ()
    {
        return $this->prizeCategory;
    }

    /**
     *
     * @return the $title
     */
    public function getTitle ()
    {
        return $this->title;
    }

    /**
     *
     * @param field_type $title
     */
    public function setTitle ($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     *
     * @return the $identifier
     */
    public function getIdentifier ()
    {
        return $this->identifier;
    }

    /**
     *
     * @param field_type $identifier
     */
    public function setIdentifier ($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

	/**
     * @return integer $anonymousAllowed
     */
    public function getAnonymousAllowed()
    {
        return $this->anonymousAllowed;
    }

	/**
     * @param number $anonymousAllowed
     */
    public function setAnonymousAllowed($anonymousAllowed)
    {
        $this->anonymousAllowed = $anonymousAllowed;

        return $this;
    }

	/**
     * @return the $anonymousIdentifier
     */
    public function getAnonymousIdentifier()
    {
        return $this->anonymousIdentifier;
    }

	/**
     * @param field_type $anonymousIdentifier
     */
    public function setAnonymousIdentifier($anonymousIdentifier)
    {
        $this->anonymousIdentifier = $anonymousIdentifier;
    }

	/**
     *
     * @return the $mainImage
     */
    public function getMainImage ()
    {
        return $this->mainImage;
    }

    /**
     *
     * @param field_type $mainImage
     */
    public function setMainImage ($mainImage)
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    /**
     *
     * @return the $secondImage
     */
    public function getSecondImage ()
    {
        return $this->secondImage;
    }

    /**
     *
     * @param field_type $secondImage
     */
    public function setSecondImage ($secondImage)
    {
        $this->secondImage = $secondImage;

        return $this;
    }

    /**
     *
     * @return the $canal
     */
    public function getCanal ()
    {
        return $this->canal;
    }

    /**
     *
     * @param field_type $canal
     */
    public function setCanal ($canal)
    {
        $this->canal = $canal;

        return $this;
    }

    /**
     *
     * @return integer $canal
     */
    public function getBroadcastFacebook ()
    {
        return $this->broadcastFacebook;
    }

    /**
     *
     * @param field_type $broadcastFacebook
     */
    public function setBroadcastFacebook ($broadcastFacebook)
    {
        $this->broadcastFacebook = $broadcastFacebook;

        return $this;
    }

    /**
     *
     * @return integer $canal
     */
    public function getBroadcastPlatform ()
    {
        return $this->broadcastPlatform;
    }

    /**
     *
     * @param field_type $broadcastPlatform
     */
    public function setBroadcastPlatform ($broadcastPlatform)
    {
        $this->broadcastPlatform = $broadcastPlatform;

        return $this;
    }

    /**
	 * @return integer $broadcastEmbed
	 */
	public function getBroadcastEmbed()
	{
		return $this->broadcastEmbed;
	}

	/**
	 * @param number $broadcastEmbed
	 */
	public function setBroadcastEmbed($broadcastEmbed)
	{
		$this->broadcastEmbed = $broadcastEmbed;

		return $this;
	}

	/**
     * @return integer $mailWinner
     */
    public function getMailWinner()
    {
        return $this->mailWinner;
    }

	/**
     * @param number $mailWinner
     */
    public function setMailWinner($mailWinner)
    {
        $this->mailWinner = $mailWinner;
    }

	/**
     * @return the $mailWinnerBlock
     */
    public function getMailWinnerBlock()
    {
        return $this->mailWinnerBlock;
    }

	/**
     * @param field_type $mailWinnerBlock
     */
    public function setMailWinnerBlock($mailWinnerBlock)
    {
        $this->mailWinnerBlock = $mailWinnerBlock;
    }

	/**
     * @return integer $mailLooser
     */
    public function getMailLooser()
    {
        return $this->mailLooser;
    }

	/**
     * @param number $mailLooser
     */
    public function setMailLooser($mailLooser)
    {
        $this->mailLooser = $mailLooser;
    }

	/**
     * @return the $mailLooserBlock
     */
    public function getMailLooserBlock()
    {
        return $this->mailLooserBlock;
    }

	/**
     * @param field_type $mailLooserBlock
     */
    public function setMailLooserBlock($mailLooserBlock)
    {
        $this->mailLooserBlock = $mailLooserBlock;
    }

	/**
     *
     * @return integer $pushHome
     */
    public function getPushHome ()
    {
        return $this->pushHome;
    }

    /**
     *
     * @param field_type $pushHome
     */
    public function setPushHome ($pushHome)
    {
        $this->pushHome = $pushHome;

        return $this;
    }

    /**
     *
     * @return integer $displayHome
     */
    public function getDisplayHome ()
    {
        return $this->displayHome;
    }

    /**
     *
     * @param field_type $displayHome
     */
    public function setDisplayHome ($displayHome)
    {
        $this->displayHome = $displayHome;

        return $this;
    }

    /**
     *
     * @return the $publicationDate
     */
    public function getPublicationDate ()
    {
        return $this->publicationDate;
    }

    /**
     *
     * @param field_type $publicationDate
     */
    public function setPublicationDate ($publicationDate)
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    /**
     *
     * @return the $startDate
     */
    public function getStartDate ()
    {
        return $this->startDate;
    }

    /**
     *
     * @param field_type $startDate
     */
    public function setStartDate ($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     *
     * @return the $endDate
     */
    public function getEndDate ()
    {
        return $this->endDate;
    }

    /**
     *
     * @param field_type $endDate
     */
    public function setEndDate ($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     *
     * @return the $closeDate
     */
    public function getCloseDate ()
    {
        return $this->closeDate;
    }

    /**
     *
     * @param field_type $closeDate
     */
    public function setCloseDate ($closeDate)
    {
        $this->closeDate = $closeDate;

        return $this;
    }

    public function isClosed ()
    {
        $today = new DateTime('now');
        if(
            ($this->getCloseDate() && $this->getCloseDate()->setTime(23,59,59) < $today)
            ||
            ($this->getPublicationDate() && $this->getPublicationDate()->setTime(0,0,0) > $today)
        ){
            return true;
        }

        return false;
    }

    public function isOpen ()
    {
        return !$this->isClosed();
    }

    public function isStarted ()
    {
        $today = new DateTime('now');
        if(((!$this->getStartDate() || $this->getStartDate()->setTime(0,0,0) <= $today))
                &&
                (!$this->getEndDate() || $this->getEndDate()->setTime(23,59,59) > $today)
        ){
            return true;
        }

        return false;
    }

    public function isFinished ()
    {
        $today = new DateTime('now');
        if ($this->getEndDate() && $this->getEndDate()->setTime(23,59,59) <= $today
            ||
            ($this->getCloseDate() && $this->getCloseDate()->setTime(23,59,59) <= $today)
        ) {
            return true;
        }

        return false;
    }

    public function isOnline ()
    {
        if($this->getActive() && $this->getBroadcastPlatform()){
            return true;
        }

        return false;
    }

    // json array : {"0":"index","1":"play","2":"result","3":"bounce"}
    public function getStepsArray()
    {
        $steps = null;

        if($this->getSteps()){
            $steps = json_decode($this->getSteps(), true);
        }
        if(!$steps){
            $steps = array('index','play','result','bounce');
        }
        return $steps;
    }



    public function getStepsViewsArray()
    {
      $viewSteps = null;

      if($this->getStepsViews()){
        $viewSteps = json_decode($this->getStepsViews(), true);
      }
      if(!$viewSteps){
        $viewSteps = array('index','play','result','bounce');
      }

      return $viewSteps;
    }

    public function getSteps()
    {
        return $this->steps;
    }

    public function setSteps($steps)
    {
        $this->steps = $steps;

        return $this;
    }

    /**
     * This method returns the first step in the game workflow
     * @return string
     */
    public function firstStep()
    {
        $steps = $this->getStepsArray();

        return $steps[0];
    }

    /**
     * This method returns the last step in the game workflow
     * @return string
     */
    public function lastStep()
    {
        $steps = $this->getStepsArray();
        $nbSteps = count($steps);

        return $steps[$nbSteps-1];
    }

    /**
     * This method returns the previous step in the game workflow
     * @param string $step
     * @return string
     */
    public function previousStep($step = null)
    {
        $steps = $this->getStepsArray();
        $key = array_search($step, $steps);

        if(is_int($key) && $key > 0 ){
            return $steps[$key-1];
        }

        return false;
    }

    /**
     * This method returns the next step in the game workflow
     * @param string $step
     * @return string
     */
    public function nextStep($step = null)
    {
        $steps = $this->getStepsArray();
        $key = array_search($step, $steps);

        if(is_int($key) && $key < count($steps)-1 ){
            return $steps[$key+1];
        }

        return false;
    }

    /**
     * @return string $stepsViews
     */
    public function getStepsViews()
    {
        return $this->stepsViews;
    }

	/**
     * @param string $stepsViews
     */
    public function setStepsViews($stepsViews)
    {
        $this->stepsViews = $stepsViews;

        return $this;
    }

	public function getState()
    {
        if ($this->isOpen()){
            if (!$this->isStarted() && !$this->isFinished()) {
                return self::GAME_PUBLISHED;
            } elseif ($this->isStarted()) {
                return self::GAME_IN_PROGRESS;
            } elseif ($this->isFinished()) {
                return self::GAME_FINISHED;
            }
        }else{
            if ($this->isFinished()) {
                return self::GAME_CLOSED;
            } else{
                return self::GAME_SCHEDULE;
            }
        }
    }

    /**
     * @return integer unknown_type
     */
    public function getPlayLimit()
    {
        return $this->playLimit;
    }

    /**
     * @param unknown_type $playLimit
     */
    public function setPlayLimit($playLimit)
    {
        $this->playLimit = $playLimit;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getPlayLimitScale()
    {
        return $this->playLimitScale;
    }

    /**
     * @param unknown_type $playLimitScale
     */
    public function setPlayLimitScale($playLimitScale)
    {
        $this->playLimitScale = $playLimitScale;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getPlayBonus()
    {
        return $this->playBonus;
    }

    /**
     * @param unknown_type $playBonus
     */
    public function setPlayBonus($playBonus)
    {
        $this->playBonus = $playBonus;

        return $this;
    }

    /**
     *
     * @return the $layout
     */
    public function getLayout ()
    {
        return $this->layout;
    }

    /**
     *
     * @param field_type $layout
     */
    public function setLayout ($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     *
     * @return the $stylesheet
     */
    public function getStylesheet ()
    {
        return $this->stylesheet;
    }

    /**
     *
     * @param field_type $stylesheet
     */
    public function setStylesheet ($stylesheet)
    {
        $this->stylesheet = $stylesheet;

        return $this;
    }

    /**
     *
     * @return the $welcomeBlock
     */
    public function getWelcomeBlock ()
    {
        return $this->welcomeBlock;
    }

    /**
     *
     * @param field_type $welcomeBlock
     */
    public function setWelcomeBlock ($welcomeBlock)
    {
        $this->welcomeBlock = $welcomeBlock;

        return $this;
    }

    /**
     *
     * @return the $termsBlock
     */
    public function getTermsBlock ()
    {
        return $this->termsBlock;
    }

    /**
     *
     * @param text $termsBlock
     */
    public function setTermsBlock ($termsBlock)
    {
        $this->termsBlock = $termsBlock;

        return $this;
    }

    /**
     *
     * @return integer $termsOptin
     */
    public function getTermsOptin ()
    {
    	return $this->termsOptin;
    }

    /**
     *
     * @param text $termsOptin
     */
    public function setTermsOptin ($termsOptin)
    {
    	$this->termsOptin = $termsOptin;

    	return $this;
    }

    /**
     *
     * @return the $conditionsBlock
     */
    public function getConditionsBlock ()
    {
        return $this->conditionsBlock;
    }

    /**
     *
     * @param text $conditionsBlock
     */
    public function setConditionsBlock ($conditionsBlock)
    {
        $this->conditionsBlock = $conditionsBlock;

        return $this;
    }

    /**
     *
     * @return the $columnBlock1
     */
    public function getColumnBlock1 ()
    {
        return $this->columnBlock1;
    }

    /**
     *
     * @param text $columnBlock1
     */
    public function setColumnBlock1 ($columnBlock1)
    {
        $this->columnBlock1 = $columnBlock1;

        return $this;
    }

    /**
     *
     * @return the $columnBlock2
     */
    public function getColumnBlock2 ()
    {
        return $this->columnBlock2;
    }

    /**
     *
     * @param text $columnBlock2
     */
    public function setColumnBlock2 ($columnBlock2)
    {
        $this->columnBlock2 = $columnBlock2;

        return $this;
    }

    /**
     *
     * @return the $columnBlock3
     */
    public function getColumnBlock3 ()
    {
        return $this->columnBlock3;
    }

    /**
     *
     * @param text $columnBlock3
     */
    public function setColumnBlock3 ($columnBlock3)
    {
        $this->columnBlock3 = $columnBlock3;

        return $this;
    }

    /**
     * @return ArrayCollection unknown_type
     */
    public function getPrizes()
    {
    	return $this->prizes;
    }

    /**
     * frm collection solution
     * @param ArrayCollection $prizes
     */
    public function setPrizes(ArrayCollection $prizes)
    {
    	$this->prizes = $prizes;

    	return $this;
    }

    public function addPrizes(ArrayCollection $prizes)
    {
    	foreach ($prizes as $prize) {
    		$prize->setGame($this);
    		$this->prizes->add($prize);
    	}
    }


    public function removePrizes(ArrayCollection $prizes)
    {
    	foreach ($prizes as $prize) {
    		$prize->setGame(null);
    		$this->prizes->removeElement($prize);
    	}
    }

    /**
     * Add a prize to the game.
     *
     * @param Prize $prize
     *
     * @return void
     */
    public function addPrize($prize)
    {
    	$this->prizes[] = $prize;
    }

    /**
     *
     * @return string $classType
     */
    public function getClassType ()
    {
        return $this->classType;
    }

    /**
     *
     * @param string classType
     * @param string $classType
     */
    public function setClassType ($classType)
    {
        $this->classType = $classType;

        return $this;
    }

    /**
     *
     * @return integer unknown_type
     */
    public function getActive ()
    {
        return $this->active;
    }

    /**
     *
     * @param unknown_type $active
     */
    public function setActive ($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     *
     * @return string the Facebook app_id
     */
    public function getFbAppId ()
    {
        return $this->fbAppId;
    }

    /**
     *
     * @param string $fbAppId
     */
    public function setFbAppId ($fbAppId)
    {
        $this->fbAppId = $fbAppId;

        return $this;
    }

    /**
     *
     * @return string the Facebook fbPageTabTitle
     */
    public function getFbPageTabTitle ()
    {
        return $this->fbPageTabTitle;
    }

    /**
     *
     * @param string $fbPageTabTitle
     */
    public function setFbPageTabTitle ($fbPageTabTitle)
    {
        $this->fbPageTabTitle = $fbPageTabTitle;

        return $this;
    }

    /**
     *
     * @return string the Facebook fbPageTabImage
     */
    public function getFbPageTabImage ()
    {
        return $this->fbPageTabImage;
    }

    /**
     *
     * @param string $fbPageTabImage
     */
    public function setFbPageTabImage ($fbPageTabImage)
    {
        $this->fbPageTabImage = $fbPageTabImage;

        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getFbShareMessage ()
    {
        return $this->fbShareMessage;
    }

    /**
     *
     * @param unknown_type $fbShareMessage
     */
    public function setFbShareMessage ($fbShareMessage)
    {
        $this->fbShareMessage = $fbShareMessage;

        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getFbShareImage ()
    {
        return $this->fbShareImage;
    }

    /**
     *
     * @param unknown_type $fbShareImage
     */
    public function setFbShareImage ($fbShareImage)
    {
        $this->fbShareImage = $fbShareImage;

        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getFbRequestMessage ()
    {
        return $this->fbRequestMessage;
    }

    /**
     *
     * @param unknown_type $fbRequestMessage
     */
    public function setFbRequestMessage ($fbRequestMessage)
    {
        $this->fbRequestMessage = $fbRequestMessage;

        return $this;
    }

    /**
     *
     * @return integer fbFan
     */
    public function getFbFan ()
    {
    	return $this->fbFan;
    }

    /**
     *
     * @param unknown_type $fbFan
     */
    public function setFbFan ($fbFan)
    {
    	$this->fbFan = $fbFan;

    	return $this;
    }

    /**
     *
     * @return the fbFanGate
     */
    public function getFbFanGate ()
    {
        return $this->fbFanGate;
    }

    /**
     *
     * @param unknown_type $fbFanGate
     */
    public function setFbFanGate ($fbFanGate)
    {
        $this->fbFanGate = $fbFanGate;

        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getTwShareMessage ()
    {
        return $this->twShareMessage;
    }

    /**
     *
     * @param unknown_type $twShareMessage
     */
    public function setTwShareMessage ($twShareMessage)
    {
        $this->twShareMessage = $twShareMessage;

        return $this;
    }

    /**
     *
     * @return DateTime $createdAt
     */
    public function getCreatedAt ()
    {
        return $this->createdAt;
    }

    /**
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt ($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     *
     * @return DateTime $updatedAt
     */
    public function getUpdatedAt ()
    {
        return $this->updatedAt;
    }

    /**
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt ($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy ()
    {
        $obj_vars = get_object_vars($this);

        if (isset($obj_vars['publicationDate']) && $obj_vars['publicationDate'] !== null) {
            $obj_vars['publicationDate'] = $obj_vars['publicationDate']->format('d/m/Y');
        }

        if (isset($obj_vars['endDate']) && $obj_vars['endDate'] !== null) {
            $obj_vars['endDate'] = $obj_vars['endDate']->format('d/m/Y');
        }
        if (isset($obj_vars['startDate']) && $obj_vars['startDate'] !== null) {
            $obj_vars['startDate'] = $obj_vars['startDate']->format('d/m/Y');
        }

        return $obj_vars;
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate ($data = array())
    {
        if (isset($data['partner']) && $data['partner'] !== null) {
            $this->partner = $data['partner'];
        }

        $this->title = (isset($data['title'])) ? $data['title'] : null;
        $this->type = (isset($data['type']) && $data['type'] !== null) ? $data['type'] : null;

        if (isset($data['mainImage']) && $data['mainImage'] !== null) {
            $this->mainImage = $data['mainImage'];
        }

        if (isset($data['secondImage']) && $data['secondImage'] !== null) {
            $this->secondImage = $data['secondImage'];
        }

        if (isset($data['active']) && $data['active'] !== null) {
            $this->active = $data['active'];
        }

        $this->layout           = (isset($data['layout'])) ? $data['layout'] : null;
        $this->stylesheet       = (isset($data['stylesheet'])) ? $data['stylesheet'] : null;

        $this->pushHome         = (isset($data['pushHome']) && $data['pushHome'] !== null) ? $data['pushHome'] : 0;
        $this->displayHome      = (isset($data['displayHome']) && $data['displayHome'] !== null) ? $data['displayHome'] : 0;

        $this->canal            = (isset($data['canal'])) ? $data['canal'] : null;
        $this->prizeCategory   = (isset($data['prizeCategory'])) ? $data['prizeCategory'] : null;

        $this->publicationDate  = (isset($data['publicationDate']) && $data['publicationDate'] !== null) ? DateTime::createFromFormat('d/m/Y', $data['publicationDate']) : null;
        $this->endDate         = (isset($data['endDate']) && $data['endDate'] !== null) ? DateTime::createFromFormat('d/m/Y', $data['endDate']) : null;
        $this->startDate       = (isset($data['startDate']) && $data['startDate'] !== null) ? DateTime::createFromFormat('d/m/Y', $data['startDate']) : null;

        $this->identifier       = (isset($data['identifier'])) ? $data['identifier'] : null;
        $this->welcomeBlock    = (isset($data['welcomeBlock'])) ? $data['welcomeBlock'] : null;
        $this->termsBlock       = (isset($data['termsBlock'])) ? $data['termsBlock'] : null;
        $this->conditionsBlock  = (isset($data['conditionsBlock'])) ? $data['conditionsBlock'] : null;
        $this->columnBlock1     = (isset($data['columnBlock1'])) ? $data['columnBlock1'] : null;
        $this->columnBlock2     = (isset($data['columnBlock2'])) ? $data['columnBlock2'] : null;
        $this->columnBlock3     = (isset($data['columnBlock3'])) ? $data['columnBlock3'] : null;

        $this->fbShareMessage   = (isset($data['fbShareMessage'])) ? $data['fbShareMessage'] : null;
        $this->fbShareImage     = (isset($data['fbShareImage'])) ? $data['fbShareImage'] : null;
        $this->fbRequestMessage = (isset($data['fbRequestMessage'])) ? $data['fbRequestMessage'] : null;
        $this->twShareMessage   = (isset($data['twShareMessage'])) ? $data['twShareMessage'] : null;
    }

    public function setInputFilter (InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter ()
    {
        if (! $this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name' => 'id',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Int'
                    )
                )
            )));

            $inputFilter->add($factory->createInput(array(
                    'name' => 'partner',
                    'required' => false
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'fbAppId',
                'required' => false
            )));

            $inputFilter->add($factory->createInput(array(
           		'name' => 'fbFan',
           		'required' => false
            )));

            $inputFilter->add($factory->createInput(array(
                    'name' => 'fbFanGate',
                    'required' => false
            )));

            $inputFilter->add($factory->createInput(array(
            	'name' => 'prizes',
            	'required' => false
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'title',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 5,
                            'max' => 255
                        )
                    )
                )
            )));

            $inputFilter->add($factory->createInput(array(
                    'name' => 'publicationDate',
                    'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'startDate',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'endDate',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
               'name' => 'closeDate',
               'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
            	'name' => 'termsOptin',
            	'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'identifier',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    ),
                    array(
                        'name' => 'PlaygroundCore\Filter\Slugify'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 3,
                            'max' => 255
                        )
                    )
                )
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'playLimit',
                'required' => false,
                'validators' => array(
                    array(
                        'name'    => 'Between',
                        'options' => array(
                            'min'      => 0,
                            'max'      => 999999,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'playLimitScale',
                'required' => false,
                'validators' => array(
                    array(
                        'name' => 'InArray',
                        'options' => array(
                            'haystack' => array('day', 'week', 'month', 'year', 'always'),
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'playBonus',
                'required' => false,
                'validators' => array(
                    array(
                        'name' => 'InArray',
                        'options' => array(
                            'haystack' => array('none', 'per_entry', 'one'),
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'active',
                'required' => true
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'displayHome',
                'required' => false
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'pushHome',
                'required' => false
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'prizeCategory',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'Int'
                    )
                )
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'fbPageTabTitle',
                'required' => false
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'fbPageTabImage',
                'required' => false
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'layout',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 0,
                            'max' => 255
                        )
                    )
                )
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'stylesheet',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 0,
                            'max' => 255
                        )
                    )
                )
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'fbShareImage',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 255
                        )
                    )
                )
            )));

            $inputFilter->add($factory->createInput(array(
                    'name' => 'fbShareMessage',
                    'required' => false,
                    'filters' => array(
                            array(
                                    'name' => 'StripTags'
                            ),
                            array(
                                    'name' => 'StringTrim'
                            )
                    ),
                    'validators' => array(
                        array(
                	        'name' => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'min' => 1,
                                'max' => 500
                             )
                        )
                    )
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'fbRequestMessage',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                ),
                'validators' => array(
                    array(
                    	'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 500
                        )
                    )
                )
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'twShareMessage',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 255
                        )
                    )
                )
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name' => 'anonymousIdentifier',
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 0,
                            'max' => 255
                        )
                    )
                )
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}
