<?php
namespace PlaygroundGame\Form\Admin;

use PlaygroundGame\Options\ModuleOptions;
use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\ServiceManager\ServiceManager;

class Game extends ProvidesEventsForm
{

    /**
     *
     * @var ModuleOptions
     */
    protected $module_options;

    protected $serviceManager;

    public function __construct ($name = null, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($sm);

        $entityManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        // The form will hydrate an object of type "QuizQuestion"
        // This is the secret for working with collections with Doctrine
        // (+ add'Collection'() and remove'Collection'() and "cascade" in
        // corresponding Entity
        // https://github.com/doctrine/DoctrineModule/blob/master/docs/hydrator.md
        //$this->setHydrator(new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\Game'));

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

        $this->add(array(
            'name' => 'canal',
            'options' => array(
                'label' => $translator->translate('Channel', 'playgroundgame')
            ),
            'attributes' => array(
                'type' => 'text'
            )
        ));

        /*$this->add(array(
                'name' => 'prize_category',
                'type' => 'DoctrineORMModule\Form\Element\DoctrineEntity',
                'options' => array(
                        'label' => $translator->translate('Catégorie de gain', 'playgroundgame'),
                        'object_manager' => $entityManager,
                        'target_class' => 'PlaygroundGame\Entity\PrizeCategory',
                        'property' => 'title'
                ),
                'attributes' => array(
                        'required' => false
                )
        ));*/

        $categories = $this->getPrizeCategories();
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'prizeCategory',
            'options' => array(
                'empty_option' => $translator->translate('No category for this game', 'playgroundgame'),
                'value_options' => $categories,
                'label' => $translator->translate('Category benefit', 'playgroundgame')
            )
        ));
/*
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'broadcastFacebook',
            'options' => array(
                'label' => 'Publier ce jeu sur Facebook',
            ),
        ));
*/
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'broadcastPlatform',
            'options' => array(
                'label' => $translator->translate('Publish game on plateform', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'anonymousAllowed',
            'options' => array(
                'label' => $translator->translate('Anonymous players allowed', 'playgroundgame'),
            ),
        ));

        $this->add(array(
        	'type' => 'Zend\Form\Element\Checkbox',
        	'name' => 'broadcastEmbed',
       		'options' => array(
       			'label' => $translator->translate('Publish game on embed mode', 'playgroundgame'),
       		),
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'displayHome',
                'options' => array(
                        'label' => $translator->translate('Publish game on home page', 'playgroundgame'),
                ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
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
                'format' => 'd/m/Y'
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
                'format' => 'd/m/Y'
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
                'format' => 'd/m/Y'
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
                'format' => 'd/m/Y'
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
            'type' => 'Zend\Form\Element\Select',
            'name' => 'active',
            'options' => array(
                'value_options' => array(
                    '0' => $translator->translate('No', 'playgroundgame'),
                    '1' => $translator->translate('Yes', 'playgroundgame')
                ),
                'label' => $translator->translate('Active', 'playgroundgame')
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'mailWinner',
            'options' => array(
                'value_options' => array(
                    '0' => $translator->translate('No', 'playgroundgame'),
                    '1' => $translator->translate('Yes', 'playgroundgame')
                ),
                'label' => $translator->translate('Send a mail to winner', 'playgroundgame')
            )
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
            'type' => 'Zend\Form\Element\Select',
            'name' => 'mailLooser',
            'options' => array(
                'value_options' => array(
                    '0' => $translator->translate('No', 'playgroundgame'),
                    '1' => $translator->translate('Yes', 'playgroundgame')
                ),
                'label' => $translator->translate('Send a mail to looser', 'playgroundgame')
            )
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

        $options = $this->getServiceManager()->get('configuration');

        $layoutArray = array(
            '' => $translator->translate('Use layout per default', 'playgroundgame')
        );
        if (isset($options['core_layout']) && isset($options['core_layout']['PlaygroundGame']) && isset($options['core_layout']['PlaygroundGame']['models'])) {
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

        $fbAppIds = $this->getFbAppIds();
        $fbAppIds_label = array();
        foreach($fbAppIds as $key => $title){
        	$fbAppIds_label[$key] = $translator->translate($title, 'playgroundgame');
        }
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'fbAppId',
            'options' => array(
                'value_options' => $fbAppIds_label,
                'label' => $translator->translate('Facebook Apps', 'playgroundgame')
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
                'name' => 'columnBlock1',
                'options' => array(
                        'label' => $translator->translate('Right column', 'playgroundgame').' 1'
                ),
                'attributes' => array(
                        'cols' => '10',
                        'rows' => '10',
                        'id' => 'columnBlock1'
                )
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'columnBlock2',
                'options' => array(
                        'label' => $translator->translate('Right column', 'playgroundgame').' 2'
                ),
                'attributes' => array(
                        'cols' => '10',
                        'rows' => '10',
                        'id' => 'columnBlock2'
                )
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'columnBlock3',
                'options' => array(
                        'label' => $translator->translate('Right column', 'playgroundgame').' 3'
                ),
                'attributes' => array(
                        'cols' => '10',
                        'rows' => '10',
                        'id' => 'columnBlock3'
                )
        ));

        $this->add(array(
        	'type' => 'Zend\Form\Element\Select',
        	'name' => 'fbFan',
       		'options' => array(
       			//'empty_option' => $translator->translate('Is the answer correct ?', 'playgroundgame'),
       			'value_options' => array(
					'0' => $translator->translate('No', 'playgroundgame'),
       				'1' => $translator->translate('Yes', 'playgroundgame'),
       			),
       			'label' => $translator->translate('You must be fan to participate', 'playgroundgame'),
       		),
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'fbFanGate',
                'options' => array(
                        'label' => $translator->translate('Fan gate content', 'playgroundgame')
                ),
                'attributes' => array(
                        'cols' => '10',
                        'rows' => '10',
                        'id' => 'fbFanGate'
                )
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'fbShareMessage',
                'options' => array(
                        'label' => $translator->translate('Facebook share message', 'playgroundgame')
                ),
                'attributes' => array(
                        'cols' => '10',
                        'rows' => '4',
                        'id' => 'fbShareMessage'
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
                    'cols' => '10',
                    'rows' => '4',
                    'id' => 'twShareMessage'
                )
        ));

        $prizeFieldset = new PrizeFieldset(null,$sm,$translator);
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
    public function getPartners ()
    {
        $partners = array(
            '0' => 'Ce jeu n\'est pas sponsorisé'
        );
        $results = $this->getServiceManager()
            ->get('application')
            ->getEventManager()
            ->trigger(__FUNCTION__, $this, array(
            'partners' => $partners
        ))
            ->last();

        if ($results) {
            $partners = $results;
        }

        //print_r($partners);
        //die();
        return $partners;
    }

    /**
     * An event is triggered so that the module PlaygroundFacebook if installed,
     * can add the Facebook apps list without adherence between the 2 modules
     * PlaygroundGame and PlaygroundFacebook
     *
     * @return array
     */
    public function getFbAppIds ()
    {
        $apps = array('' => 'Don\'t deploy on Facebook');

        $results = $this->getServiceManager()
            ->get('application')
            ->getEventManager()
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
    public function getPrizeCategories ()
    {
        $categories = array();
        $prizeCategoryService = $this->getServiceManager()->get('playgroundgame_prizecategory_service');
        $results = $prizeCategoryService->getActivePrizeCategories();

        foreach ($results as $result) {
            $categories[$result->getId()] = $result->getTitle();
        }

        return $categories;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager ()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager (ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
