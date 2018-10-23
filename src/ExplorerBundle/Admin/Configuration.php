<?php
/**
 * This file is part of SplashSync Project.
 *
 * Copyright (C) Splash Sync <www.splashsync.com>
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * @author Bernard Paquier <contact@splashsync.com>
 */

namespace App\ExplorerBundle\Admin;

use ArrayObject;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

//use Sonata\CoreBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Form\Type\CollectionType;
//use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use Splash\Core\SplashCore as Splash;

use App\ExplorerBundle\Fields\FormHelper;
use App\ExplorerBundle\Form\FieldsListType;

use App\ExplorerBundle\Model\ConnectorAwareAdminTrait;

/**
 * @abstract    Connectors Configuration Admin Class for Splash Connectors Explorer
 */
class Configuration extends AbstractAdmin
{
    use ConnectorAwareAdminTrait;
    
    /**
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     * @param string $serverId
     * @param string $section
     */
    public function __construct($code, $class, $baseControllerName, $connectorName, $serverId, $section)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->baseRouteName    = "sonata_admin_" . $code . "_" . $section;
        $this->baseRoutePattern = $serverId . "/" . $section;
        
        $this->setConnectorName($connectorName);
        $this->setServerId($serverId);
        
    }    

    public function configure()
    {
        //====================================================================//
        // Setup Model Manager     
        $this->configureModelManager();
    }
    
    

    
//    public function getModelManager()
//    {
//        return $this->modelManager;
//    }    
    
//    public function getClass()
//    {
//        if ($this->hasRequest()) {
//            return $this->getObjectType();
//        } 
//        return parent::getClass();
//    }    
    

    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('batch');
        $collection->remove('create');
        $collection->remove('edit');
        $collection->remove('show');
        $collection->remove('delete');
        $collection->remove('export');
    }    
    
//    public function configureActionButtons($action, $object = null)
//    {
//        $list = parent::configureActionButtons($action, $object);
////dump($list);
//        unset($list['create']);
//        $list['create']['template'] = '@AppExplorer/Objects/create_button.html.twig';
//
//        return $list;
//    }
     
//    public function getClass()
//    {
//        return "array";
//    }     
    
    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
//dump(parent::getFormTheme());        
//dump(parent::getTemplates());        
        $this->configureFields($showMapper);
    }
            

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->configureFields($formMapper);
    }
    
    protected function configureFields($Mapper)
    {
        $Lists =   array();
        //====================================================================//
        // Load Object Fields
        $Fields = $this->getModelManager()->getObjectFields();
        //====================================================================//
        // Walk on Object Fields
        foreach ($Fields as $Field) {
            //====================================================================//
            // Add Single Fields to Mapper
            if ( !FormHelper::isListField($Field->type)) {
                $this->buildFieldForm($Mapper, $Field);
                continue;
            }
            //====================================================================//
            // Add List Field to Buffer
            $List   =   FormHelper::isListField($Field->id);
            $Lists  =   array_merge_recursive($Lists, array(
                $List["listname"] => array(
                    $List["fieldname"]  =>     $Field
                )));
        }
       
        //====================================================================//
        // Walk on Object Lists
        foreach ($Lists as $Name => $Fields) {
            $this->buildFieldListForm($Mapper, $Name, $Fields);
        }
        
    }    
    
    public function buildFieldForm($mapper, ArrayObject $Field)
    {
        if ($mapper instanceof ShowMapper) {
            $options    =   FormHelper::showOptions($Field);
        } else {
            $options    =   FormHelper::formOptions($Field);
        }
        $mapper
            ->with(FormHelper::formGroup($Field), array('class' => 'col-md-6'))
                ->add(
                        $Field->id, 
                        FormHelper::formType($Field),
                        $options
                    )
            ->end()
        ;
        return $this;
    }      

    public function buildFieldListForm($mapper, string $Name, array $Fields)
    {
        
        $options    =   array(
                    'entry_type'    => FieldsListType::class,
                    'entry_options' => array(
                        'label'     => false,
                        'fields'    => $Fields,
                        ),
                    'required' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                );
                
        if ($mapper instanceof ShowMapper) {
            $options    =   array_merge_recursive($options, FormHelper::showOptions($Fields, true));
        }        
        
        $mapper
            ->with($Name, array('class' => 'col-md-6'))
                ->add($Name, CollectionType::class, $options, array())
            ->end()
        ;
        return $this;
    }      

    
    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
        $Object =   new ArrayObject(array("id" => null), ArrayObject::ARRAY_AS_PROPS);
        
        
        $Fields = $this->getModelManager()->getObjectFields();
        foreach ($Fields as $Field) {
            
            //====================================================================//
            // Add Empty List Field to Object
            $List   =   FormHelper::isListField($Field->id);
            if ( $List ) {
                $Object[$List["listname"]] = null;
            //====================================================================//
            // Add Empty Single Field to Object
            } else {
                $Object[$Field->id] = null;
            }
        
        }
        
//        $object = parent::getNewInstance();
//
////        $inspection = new Inspection();
////        $inspection->setDate(new \DateTime());
////        $inspection->setComment("Initial inspection");
//
////        $object->addInspection($inspection);
//
        return $Object;
    }    
}
