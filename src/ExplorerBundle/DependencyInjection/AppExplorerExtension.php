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

namespace App\ExplorerBundle\DependencyInjection;

use ArrayObject;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

use App\ExplorerBundle\Admin\Admin;
use App\ExplorerBundle\Admin\Configuration;
//use App\ExplorerBundle\Admin\Admin;
use App\ExplorerBundle\Controller\ConfigurationController;
use App\ExplorerBundle\Controller\ProfileCRUDController;
use App\ExplorerBundle\Controller\ObjectsCRUDController;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AppExplorerExtension extends Extension
{
    const TYPE_CONFIG   =   "configuration";
    const TYPE_PROFILE  =   "profile";
    const TYPE_OBJECTS  =   "objects";
    const TYPE_WIDGETS  =   "widgets";
    
    /**
     * @var ContainerBuilder 
     */
    private $container;
    
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        //====================================================================//
        // Store Container	
        $this->container    =   $container;
        //====================================================================//
        // Load Bundle Services	
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        //====================================================================//
        // Load Splash Core Bundle Configuration	
        $config = $container->getParameter('splash');        

        //====================================================================//
        // Add Availables Connections to Sonata Admin	
        foreach ($config["connections"]  as $Id => $Connection) {
            $this->addAdminService(self::TYPE_PROFILE, $Id, $Connection["name"], $Connection["connector"]);
            $this->addAdminService(self::TYPE_CONFIG, $Id, $Connection["name"], $Connection["connector"]);
            $this->addAdminService(self::TYPE_OBJECTS, $Id, $Connection["name"], $Connection["connector"]);
            $this->addAdminService(self::TYPE_WIDGETS, $Id, $Connection["name"], $Connection["connector"]);
        }        

    }
    
    private function addAdminService(string $Type, string $Id, string $Name, string $Connector)
    {
        //====================================================================//
        // Build Service Tags Array
        $tags   =   array(
            "manager_type"  => "orm", 
            "group"         => $Name, 
            "label"         => ucwords($Type), 
            "icon"          => '<span class="fa fa-server"></span>' 
        );  
        //====================================================================//
        // Build Service Configurations
        switch ($Type) {
            //====================================================================//
            // Connector Configuration Admin Service	
            case self::TYPE_CONFIG:
                $adminClass =   Configuration::class;  
                $args   =   array(
                    null, ArrayObject::class,
                    ConfigurationController::class,
                    $Connector, $Id, $Type
                );
                break;
            //====================================================================//
            // Connector Profile Admin Service	
            case self::TYPE_PROFILE:
                $adminClass =   Admin::class;  
                $args   =   array(
                    null, ArrayObject::class,
                    ProfileCRUDController::class,
                    $Connector, $Id, $Type
                );
                break;
            //====================================================================//
            // Connector Objects Admin Service	
            case self::TYPE_OBJECTS:
                $adminClass =   Admin::class;  
                $args   =   array(
                    null, ArrayObject::class,
                    ObjectsCRUDController::class,
                    $Connector, $Id, $Type
                );
                break;
            //====================================================================//
            // Connector Widgets Admin Service	
            case self::TYPE_WIDGETS:
                $adminClass =   Admin::class;  
                $args   =   array(
                    null, ArrayObject::class,
                    ProfileCRUDController::class,
                    $Connector, $Id, $Type
                );
                break;
        }
        
        
        //====================================================================//
        // Create Sonata Admin Service	
        $this->container
            ->register('splash.admin.' . $Id . '.' . $Type , $adminClass)
                ->addTag("sonata.admin", $tags)
                ->setArguments($args)
                ;
        
    }
    
}
