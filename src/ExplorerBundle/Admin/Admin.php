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

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Splash\Core\SplashCore as Splash;

/**
 * @abstract    Base Admin Class for Splash Connectors Explorer
 */
class Admin extends AbstractAdmin
{

    /**
     * @var string
     */
    private $connector;
    
    /**
     * @var string
     */
    private $connexion;
    
    /**
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     * @param string $connexion
     * @param string $section
     */
    public function __construct($code, $class, $baseControllerName, $connector, $connexion, $section)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->baseRouteName    = "sonata_admin_" . $code . "_" . $section;
        $this->baseRoutePattern = $connexion . "/" . $section;
        $this->connector    =   $connector;
        $this->connexion    =   $connexion;
        
    }    

    public function getConnectorName()
    {
        return $this->connector;
    }
    
    public function getConnectionName()
    {
        return $this->connexion;
    }
    
    public function configureActionButtons($action, $object = null)
    {
        $list = parent::configureActionButtons($action, $object);
//dump($list);
//        unset($list['create']);
//        $list['create']['template'] = '@AppExplorer/Objects/create_button.html.twig';

        return $list;
    }
     
//    public function getClass()
//    {
//        return "array";
//    }     
    
    

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
//        $formMapper->ifTrue(false);
        
        $formMapper
            ->tab("Main")    
//                ->with('General', array('class' => 'col-md-6'))
                    ->add('id', TextType::class )
                ->end()
            ->end()
                
            ->tab("Main")    
//                ->with('General', array('class' => 'col-md-6'))
                    ->add('varchar1', TextType::class )
                    ->add('bool1', CheckboxType::class )
//                ->end()
            ->end()
                
            ->with('Webservice', array('class' => 'col-md-6'))
//                ->add('identifier')
//                ->add('host')
//                ->add('folder')
//                ->add('https', 'checkbox', array(
//                    'property_path'             => 'settings[EnableHttps]',
//                    'required'                  => False,
//                    ))
            ->end()
            ->with('Security', array('class' => 'col-md-6'))
//                ->add('http_auth')
//                ->add('http_user')
//                ->add('http_pwd')
            ->end()                
            ->with('Encoding', array('class' => 'col-md-6'))
//                ->add('crypt_mode')
//                ->add('crypt_key')
            ->end()                
//            ->with('inspections', array('class' => 'col-md-12'))
//                ->add('inspections', 'sonata_type_collection', array(
//                    'by_reference'       => false,
//                    'cascade_validation' => true,
//                ), array(
//                    'edit' => 'inline',
//                    'inline' => 'table'
//                ))
            ->end()
        ;
    }    
    
    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
//        $object = parent::getNewInstance();
//
////        $inspection = new Inspection();
////        $inspection->setDate(new \DateTime());
////        $inspection->setComment("Initial inspection");
//
////        $object->addInspection($inspection);
//
        return new ArrayObject(array("id" => null, "varchar1" => null, "bool1" => null), ArrayObject::ARRAY_AS_PROPS);
    }    
}
