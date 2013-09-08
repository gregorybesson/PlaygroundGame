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
                'empty_option' => $translator->translate('Ce jeu n\'a pas de catégorie', 'playgroundgame'),
                'value_options' => $categories,
                'label' => $translator->translate('Catégorie de gain', 'playgroundgame')
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
                'label' => 'Publier ce jeu sur la Plateforme',
            ),
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Checkbox',
                'name' => 'displayHome',
                'options' => array(
                        'label' => 'Publier ce jeu sur la home',
                ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'pushHome',
            'options' => array(
                'label' => 'Publier ce jeu sur le slider Home',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\DateTime',
            'name' => 'publicationDate',
            'options' => array(
                'label' => $translator->translate('Date de publication', 'playgroundgame'),
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
                'label' => $translator->translate('Date de début', 'playgroundgame'),
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
                'label' => $translator->translate('Date de fin', 'playgroundgame'),
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
                'label' => $translator->translate('Date de dépublication', 'playgroundgame'),
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
                        'placeholder' => 'Nombre d\'essais par joueur',
                ),
                'options' => array(
                        'label' => 'Quel est la limite du nombre d\'essais par joueur ?',
                ),
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Select',
                'name' => 'playLimitScale',
                'attributes' =>  array(
                        'id' => 'playLimitScale',
                        'options' => array(
                                'day' => $translator->translate('Jour', 'playgroundgame'),
                                'week' => $translator->translate('Semaine', 'playgroundgame'),
                                'month' => $translator->translate('Mois', 'playgroundgame'),
                                'year' => $translator->translate('An', 'playgroundgame'),
                                'always' => $translator->translate('Toujours', 'playgroundgame'),
                        ),
                ),
                'options' => array(
                        'empty_option' => $translator->translate('Quelle est la durée de limitation ?', 'playgroundgame'),
                        'label' => $translator->translate('Durée de la limite', 'playgroundgame'),
                ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'playBonus',
            'attributes' =>  array(
                'id' => 'playBonus',
                'options' => array(
                    'none' => $translator->translate('Aucune participation bonus', 'playgroundgame'),
                    'per_entry' => $translator->translate('Au max une participation bonus par participation', 'playgroundgame'),
                    'one' => $translator->translate('Une seule participation bonus pour le jeu', 'playgroundgame'),
                ),
            ),
            'options' => array(
                'empty_option' => $translator->translate('Des participations bonus peuvent-elles être offertes ?', 'playgroundgame'),
                'label' => $translator->translate('Participations bonus', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'active',
            'options' => array(
                'value_options' => array(
                    '0' => $translator->translate('Non', 'playgroundgame'),
                    '1' => $translator->translate('Oui', 'playgroundgame')
                ),
                'label' => $translator->translate('Actif', 'playgroundgame')
            )
        ));

        $options = $this->getServiceManager()->get('configuration');

        $layoutArray = array(
            '' => $translator->translate('Utiliser le layout par défaut', 'playgroundgame')
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
            '' => $translator->translate('Utiliser la feuille de styles par défaut', 'playgroundgame')
        );

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'stylesheet',
            'options' => array(
                'value_options' => $stylesheetArray,
                'label' => $translator->translate('Feuille de style', 'playgroundgame')
            )
        ));

        $this->add(array(
            'name' => 'uploadStylesheet',
            'attributes' => array(
                'type' => 'file'
            ),
            'options' => array(
                'label' => $translator->translate('Ajouter une feuille de style', 'playgroundgame')
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
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'fbAppId',
            'options' => array(
                'value_options' => $fbAppIds,
                'label' => $translator->translate('Facebook Apps', 'playgroundgame')
            )
        ));

        $this->add(array(
            'name' => 'fbPageTabTitle',
            'options' => array(
                'label' => $translator->translate('Titre de l\'onglet du jeu', 'playgroundgame')
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Titre de l\'onglet du jeu', 'playgroundgame')
            )
        ));

        $this->add(array(
                'name' => 'uploadFbPageTabImage',
                'attributes' => array(
                        'type' => 'file'
                ),
                'options' => array(
                        'label' => $translator->translate('Icône de l\'onglet du jeu', 'playgroundgame')
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
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'welcomeBlock',
            'options' => array(
                'label' => $translator->translate('Bloc de bienvenue', 'playgroundgame')
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
        				'label' => $translator->translate('Le joueur doit accepter le règlement pour jouer', 'playgroundgame'),
        		),
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'termsBlock',
                'options' => array(
                        'label' => $translator->translate('Page de règlement', 'playgroundgame')
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
                        'label' => $translator->translate('Bloc colonne de droite 1', 'playgroundgame')
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
                        'label' => $translator->translate('Bloc colonne de droite 2', 'playgroundgame')
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
                        'label' => $translator->translate('Bloc colonne de droite 3', 'playgroundgame')
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
       			'label' => $translator->translate('Il faut être fan pour participer', 'playgroundgame'),
       		),
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'fbShareMessage',
                'options' => array(
                        'label' => $translator->translate('Message de partage Facebook', 'playgroundgame')
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
                'label' => $translator->translate('Image de partage Facebook', 'playgroundgame')
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
                'type' => 'Zend\Form\Element\Textarea',
                'name' => 'fbRequestMessage',
                'options' => array(
                    'label' => $translator->translate('Message d\'invitation Facebook', 'playgroundgame')
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
                    'label' => $translator->translate('Message de partage Twitter', 'playgroundgame')
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

        $submitElement = new Element\Button('submit');
        $submitElement->setLabel($translator->translate('Create', 'playgroundgame'))
            ->setAttributes(array(
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
        $apps = array('' => 'Ne pas déployer sur Facebook');

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
