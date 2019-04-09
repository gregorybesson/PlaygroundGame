<?php
namespace PlaygroundGame\Form\Admin;

use PlaygroundGame\Options\ModuleOptions;
use Zend\Form\Element;
use ZfcUser\Form\ProvidesEventsForm;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;
use Zend\EventManager\EventManager;

class Game extends ProvidesEventsForm
{
    /**
     *
     * @var ModuleOptions
     */
    protected $module_options;

    protected $serviceManager;

    protected $event;

    public function __construct($name, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($sm);

        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0
            )
        ));

        $this->add(array(
            'name' => 'title',
            'options' => array(
                'label' => $translator->translate('Title', 'playgroundgame')
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Title', 'playgroundgame')
            )
        ));

        $this->add(array(
            'name' => 'identifier',
            'options' => array(
                'label' => $translator->translate('Slug', 'playgroundgame')
            ),
            'attributes' => array(
                'type' => 'text'
            )
        ));

        // Adding an empty upload field to be able to correctly handle this on
        // the service side.
        $this->add(array(
            'name' => 'uploadMainImage',
            'attributes' => array(
                'type' => 'file'
            ),
            'options' => array(
                'label' => $translator->translate('Main image', 'playgroundgame')
            )
        ));
        $this->add(array(
            'name' => 'mainImage',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => ''
            )
        ));
        $this->add(array(
            'name' => 'deleteMainImage',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => '',
                'class' => 'delete_main_image',
            ),
        ));

        // Adding an empty upload field to be able to correctly handle this on
        // the service side.
        $this->add(array(
            'name' => 'uploadSecondImage',
            'attributes' => array(
                'type' => 'file'
            ),
            'options' => array(
                'label' => $translator->translate('Secondary image', 'playgroundgame')
            )
        ));
        $this->add(array(
            'name' => 'secondImage',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => ''
            )
        ));
        $this->add(array(
            'name' => 'deleteSecondImage',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => '',
                'class' => 'delete_second_image',
            ),
        ));

        $categories = $this->getPrizeCategories();
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'prizeCategory',
            'options' => array(
                'empty_option' => $translator->translate('No category for this game', 'playgroundgame'),
                'value_options' => $categories,
                'label' => $translator->translate('Category benefit', 'playgroundgame'),
                'disable_inarray_validator' => true
            ),

        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'class' => 'switch-input'
            ),
            'name' => 'broadcastPlatform',
            'options' => array(
                'label' => $translator->translate('Publish game on plateform', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'class' => 'switch-input'
            ),
            'name' => 'broadcastFacebook',
            'options' => array(
                'label' => $translator->translate('Publish game on a Facebook Tab', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'domain',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => $translator->translate('Give a domain name to this game', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'class' => 'switch-input'
            ),
            'name' => 'anonymousAllowed',
            'options' => array(
                'label' => $translator->translate('Anonymous players allowed', 'playgroundgame'),
            ),
        ));
        
        $this->add(array(
            'name' => 'anonymousIdentifier',
            'options' => array(
                'label' => $translator->translate('Anonymous Identifier', 'playgroundgame')
            ),
            'attributes' => array(
                'type' => 'text'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'class' => 'switch-input'
            ),
            'name' => 'broadcastPostFacebook',
               'options' => array(
                   'label' => $translator->translate('Publish the game as a Post Facebook', 'playgroundgame'),
               ),
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Checkbox',
                'attributes' => array(
                    'class' => 'switch-input'
                ),
                'name' => 'displayHome',
                'options' => array(
                    'label' => $translator->translate('Display the game on the homepage', 'playgroundgame'),
                ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'attributes' => array(
                'class' => 'switch-input'
            ),
            'name' => 'pushHome',
            'options' => array(
                'label' => $translator->translate('Publish game on home page slider', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\DateTime',
            'name' => 'publicationDate',
            'options' => array(
                'label' => $translator->translate('Publication date', 'playgroundgame'),
                'format' => 'd/m/Y H:i:s'
            ),
            'attributes' => array(
                'type' => 'text',
                'class'=> 'datepicker'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\DateTime',
            'name' => 'startDate',
            'options' => array(
                'label' => $translator->translate('Start Date', 'playgroundgame'),
                'format' => 'd/m/Y H:i:s'
            ),
            'attributes' => array(
                'type' => 'text',
                'class'=> 'datepicker'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\DateTime',
            'name' => 'endDate',
            'options' => array(
                'label' => $translator->translate('End Date', 'playgroundgame'),
                'format' => 'd/m/Y H:i:s'
            ),
            'attributes' => array(
                'type' => 'text',
                'class'=> 'datepicker'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\DateTime',
            'name' => 'closeDate',
            'options' => array(
                'label' => $translator->translate('Unpublished date', 'playgroundgame'),
                'format' => 'd/m/Y H:i:s'
            ),
            'attributes' => array(
                'type' => 'text',
                'class'=> 'datepicker'
            )
        ));

        $this->add(array(
            'name' => 'playLimit',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Tries limit per player', 'playgroundgame'),
            ),
            'options' => array(
                'label' => $translator->translate('What\'s the tries limit per player ?', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'playLimitScale',
            'attributes' =>  array(
                'id' => 'playLimitScale',
                'options' => array(
                    'day' => $translator->translate('Day', 'playgroundgame'),
                    'week' => $translator->translate('Week', 'playgroundgame'),
                    'month' => $translator->translate('Month', 'playgroundgame'),
                    'year' => $translator->translate('Year', 'playgroundgame'),
                    'always' => $translator->translate('Everytime', 'playgroundgame'),
                ),
            ),
            'options' => array(
                    'empty_option' => $translator->translate('Limitation time', 'playgroundgame'),
                    'label' => $translator->translate('Limit time', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'playBonus',
            'attributes' =>  array(
                'id' => 'playBonus',
                'options' => array(
                    'none' => $translator->translate('No bonus entry', 'playgroundgame'),
                    'one' => $translator->translate('One bonus entry per game', 'playgroundgame'),
                    'per_entry' => $translator->translate('One bonus entry by entry', 'playgroundgame'),
                ),
            ),
            'options' => array(
                'empty_option' => $translator->translate('Bonus entries', 'playgroundgame'),
                'label' => $translator->translate('Any bonus entries ?', 'playgroundgame'),

            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'active',
            'attributes' => array(
                'class' => 'switch-input'
            ),
            'options' => array(
                'label' => $translator->translate('Make this game available to the users', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'onInvitation',
            'options' => array(
                'value_options' => array(
                    '0' => $translator->translate('No', 'playgroundgame'),
                    '1' => $translator->translate('Yes', 'playgroundgame')
                ),
                'label' => $translator->translate('On Invitation', 'playgroundgame')
            ),
            'attributes' => array(
                'id' => 'onInvitation'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'mailWinner',
            'attributes' => array(
                'class' => 'switch-input'
            ),
            'options' => array(
                'label' => $translator->translate('Send a mail to winner', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'mailWinnerBlock',
            'options' => array(
                'label' => $translator->translate('Mail winner content', 'playgroundgame')
            ),
            'attributes' => array(
                'cols' => '10',
                'rows' => '10',
                'id' => 'mailWinnerBlock'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'mailLooser',
            'attributes' => array(
                'class' => 'switch-input'
            ),
            'options' => array(
                'label' => $translator->translate('Send a mail to looser', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'mailLooserBlock',
            'options' => array(
                'label' => $translator->translate('Mail looser content', 'playgroundgame')
            ),
            'attributes' => array(
                'cols' => '10',
                'rows' => '10',
                'id' => 'mailLooserBlock'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'mailEntry',
            'attributes' => array(
                'class' => 'switch-input'
            ),
            'options' => array(
                'label' => $translator->translate('Send a mail for each entry', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'emailShare',
            'attributes' => array(
                'class' => 'switch-input'
            ),
            'options' => array(
                'label' => $translator->translate('Share the game by mail', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'fbShare',
            'attributes' => array(
                'class' => 'switch-input'
            ),
            'options' => array(
                'label' => $translator->translate('Share the game with Facebook', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'twShare',
            'attributes' => array(
                'class' => 'switch-input'
            ),
            'options' => array(
                'label' => $translator->translate('Share the game with Twitter', 'playgroundgame'),
            ),
        ));

        $options = $this->getServiceManager()->get('configuration');

        $layoutArray = array(
            '' => $translator->translate('Use layout per default', 'playgroundgame')
        );
        if (isset($options['core_layout']) &&
            isset($options['core_layout']['PlaygroundGame']) &&
            isset($options['core_layout']['PlaygroundGame']['models'])
        ) {
            $layoutOptions = array();
            $layoutOptions = $options['core_layout']['PlaygroundGame']['models'];
            foreach ($layoutOptions as $k => $v) {
                $layoutArray[$v['layout']] = $v['description'];
            }
        }

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'layout',
            'options' => array(
                // 'empty_option' => $translator->translate('Ce jeu n\'a pas de catégorie', 'playgroundgame'),
                'value_options' => $layoutArray,
                'label' => $translator->translate('Layout', 'playgroundgame')
            )
        ));

        // The additional Stylesheets are populated by the controllers
        $stylesheetArray = array(
            '' => $translator->translate('Use default style sheet', 'playgroundgame')
        );

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'stylesheet',
            'options' => array(
                'value_options' => $stylesheetArray,
                'label' => $translator->translate('Style sheet', 'playgroundgame')
            )
        ));

        $this->add(array(
            'name' => 'uploadStylesheet',
            'attributes' => array(
                'type' => 'file'
            ),
            'options' => array(
                'label' => $translator->translate('Add style sheet', 'playgroundgame')
            )
        ));

        $partners = $this->getPartners();
        if (count($partners) <= 1) {
            $this->add(array(
                'name' => 'partner',
                'type' => 'Zend\Form\Element\Hidden',
                'attributes' => array(
                    'value' => 0
                )
            ));
        } else {
            $this->add(array(
                'type' => 'Zend\Form\Element\Select',
                'name' => 'partner',
                'options' => array(
                    'value_options' => $partners,
                    'label' => $translator->translate('Sponsor', 'playgroundgame')
                )
            ));
        }

        // $fbAppIds = $this->getFbAppIds();
        // $fbAppIds_label = array();
        // foreach ($fbAppIds as $key => $title) {
        //     $fbAppIds_label[$key] = $translator->translate($title, 'playgroundgame');
        // }
        // $this->add(array(
        //     'type' => 'Zend\Form\Element\Select',
        //     'name' => 'fbAppId',
        //     'options' => array(
        //         'value_options' => $fbAppIds_label,
        //         'label' => $translator->translate('Facebook Apps', 'playgroundgame')
        //     )
        // ));

        $this->add(array(
            'name' => 'fbAppId',
            'options' => array(
                'label' => $translator->translate('Facebook app Id', 'playgroundgame')
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Facebook app Id', 'playgroundgame')
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'fbPageId',
            'options' => array(
                'value_options' => [],
                'label' => $translator->translate('Facebook Pages', 'playgroundgame')
            )
        ));

        $this->add(array(
            'name' => 'fbPageTabTitle',
            'options' => array(
                'label' => $translator->translate('Game tab title', 'playgroundgame')
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Game tab title', 'playgroundgame')
            )
        ));

        $this->add(array(
            'name' => 'fbPageTabPosition',
            'options' => array(
                'label' => $translator->translate('Game tab position', 'playgroundgame')
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Game tab position', 'playgroundgame')
            )
        ));

        $this->add(array(
            'name' => 'uploadFbPageTabImage',
            'attributes' => array(
                'type' => 'file'
            ),
            'options' => array(
                'label' => $translator->translate('Game tab icone', 'playgroundgame')
            )
        ));
        $this->add(array(
            'name' => 'fbPageTabImage',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => ''
            )
        ));
        $this->add(array(
            'name' => 'deleteFbPageTabImage',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => '',
                'class' => 'delete_fb_page_tab_image',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'welcomeBlock',
            'options' => array(
                'label' => $translator->translate('Welcome block', 'playgroundgame')
            ),
            'attributes' => array(
                'cols' => '10',
                'rows' => '10',
                'id' => 'welcomeBlock'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'termsOptin',
            'options' => array(
                //'empty_option' => $translator->translate('Is the answer correct ?', 'playgroundgame'),
                'value_options' => array(
                    '0' => $translator->translate('No', 'playgroundgame'),
                    '1' => $translator->translate('Yes', 'playgroundgame'),
                ),
                'label' => $translator->translate('Player must accept the rules', 'playgroundgame'),
            ),
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'conditionsBlock',
                'options' => array(
                        'label' => $translator->translate('Legal status', 'playgroundgame')
                ),
                'attributes' => array(
                        'cols' => '10',
                        'rows' => '10',
                        'id' => 'conditionsBlock'
                )
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'termsBlock',
                'options' => array(
                        'label' => $translator->translate('Payment page', 'playgroundgame')
                ),
                'attributes' => array(
                        'cols' => '10',
                        'rows' => '10',
                        'id' => 'termsBlock'
                )
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'fbShareDescription',
                'options' => array(
                        'label' => $translator->translate('Facebook share description', 'playgroundgame')
                ),
                'attributes' => array(
                        'cols' => '40',
                        'rows' => '4',
                        'id' => 'fbShareDescription'
                )
        ));

        $this->add(array(
            'name' => 'uploadFbShareImage',
            'attributes' => array(
                'type' => 'file'
            ),
            'options' => array(
                'label' => $translator->translate('Facebook share image', 'playgroundgame')
            )
        ));
        $this->add(array(
            'name' => 'fbShareImage',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => ''
            )
        ));
        $this->add(array(
            'name' => 'deleteFbShareImage',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => '',
                'class' => 'delete_fb_share_image',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'fbRequestMessage',
            'options' => array(
                'label' => $translator->translate('Facebook invitation message', 'playgroundgame')
            ),
            'attributes' => array(
                'cols' => '10',
                'rows' => '4',
                'id' => 'fbRequestMessage'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'twShareMessage',
            'options' => array(
                'label' => $translator->translate('Twitter share message', 'playgroundgame')
            ),
            'attributes' => array(
                'cols' => '40',
                'rows' => '4',
                'id' => 'twShareMessage'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'emailShareSubject',
            'options' => array(
                'label' => $translator->translate('Email subject', 'playgroundgame')
            ),
            'attributes' => array(
                'cols' => '40',
                'rows' => '4',
                'id' => 'emailShareSubject'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'emailShareMessage',
            'options' => array(
                'label' => $translator->translate('Email message', 'playgroundgame')
            ),
            'attributes' => array(
                'cols' => '40',
                'rows' => '4',
                'id' => 'emailShareMessage'
            )
        ));

        $prizeFieldset = new PrizeFieldset(null, $sm, $translator);
        $this->add(array(
            'type'    => 'Zend\Form\Element\Collection',
            'name'    => 'prizes',
            'options' => array(
                'id'    => 'prizes',
                'label' => $translator->translate('List of prizes', 'playgroundgame'),
                'count' => 0,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => $prizeFieldset
            )
        ));

        $this->add(array(
            'name' => 'steps',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Game steps', 'playgroundgame'),
                'value' => 'index,play,result,bounce',
            ),
            'options' => array(
                'label' => $translator->translate('The steps of the game', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'stepsViews',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('steps views', 'playgroundgame'),
                'value' => '{"index":{},"play":{},"result":{},"bounce":{}}',
            ),
            'options' => array(
                'label' => $translator->translate('The steps views', 'playgroundgame'),
            ),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement->setAttributes(array(
            'type' => 'submit'
        ));

        $this->add($submitElement, array(
            'priority' => - 100
        ));
    }

    /**
     * An event is triggered so that the module PlaygroundPartnership if installed,
     * can add the partners list without adherence between the 2 modules
     * PlaygroundGame and PlaygroundPartnership
     *
     * @return array
     */
    public function getPartners()
    {
        $partners = array(
            '0' => 'Ce jeu n\'est pas sponsorisé'
        );
        $results = $this->getEventManager()
            ->trigger(__FUNCTION__, $this, array(
            'partners' => $partners
            ))
            ->last();

        if ($results) {
            $partners = $results;
        }

        return $partners;
    }

    /**
     * An event is triggered so that the module PlaygroundFacebook if installed,
     * can add the Facebook apps list without adherence between the 2 modules
     * PlaygroundGame and PlaygroundFacebook
     *
     * @return array
     */
    public function getFbAppIds()
    {
        $apps = array('' => 'Don\'t deploy on Facebook');

        $results = $this->getEventManager()
            ->trigger(__FUNCTION__, $this, array(
            'apps' => $apps
            ))
            ->last();

        if ($results) {
            $apps = $results;
        }

        return $apps;
    }

    /**
     *
     * @return array
     */
    public function getPrizeCategories()
    {
        $categories = array();
        $prizeCategoryService = $this->getServiceManager()->get('playgroundgame_prizecategory_service');
        $results = $prizeCategoryService->getActivePrizeCategories();

        foreach ($results as $result) {
            $categories[$result->getId()] = $result->getTitle();
        }

        return $categories;
    }

    public function getEventManager()
    {
        if ($this->event === NULL) {
            $this->event = new EventManager(
                $this->getServiceManager()->get('SharedEventManager'), [get_class($this)]
            );
        }
        return $this->event;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return Game
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
