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
use App\ExplorerBundle\Controller\ProfileCRUDController;
use App\ExplorerBundle\Controller\ObjectsCRUDController;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AppExplorerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        
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
            //====================================================================//
            // Connector Profile Sonata Admin Class	
            $container
                ->register('splash.admin.' . $Id . '.profile', Admin::class)
                    ->addTag("sonata.admin", array( 
                        "manager_type"  => "orm", 
                        "group"         => $Connection["name"], 
                        "label"         => "Profile", 
                        "icon"          => '<span class="fa fa-binoculars"></span>' 
                    ))
                    ->setArguments(array(
                        null,
                        ArrayObject::class,
                        ProfileCRUDController::class,
                        $Connection["connector"],
                        $Id,
                        "profile"
                        ))
                    ;
            //====================================================================//
            // Objects Sonata Admin Class	
            $container
                ->register('splash.admin.' . $Id . '.objects', Admin::class)
                    ->addTag("sonata.admin", array( 
                        "manager_type"  => "orm", 
                        "group"         => $Connection["name"], 
                        "label"         => "Objects", 
                        "icon"          => '<span class="fa fa-binoculars"></span>' 
                    ))
                    ->setArguments(array(
                        null,
                        ArrayObject::class,
                        ObjectsCRUDController::class,
                        $Connection["connector"],
                        $Id,
                        "objects"
                        ))
                    ;
            
            //====================================================================//
            // Objects Sonata Admin Class	
//            $container
//                ->register('splash.admin.' . $Id . '.objects', Admin::class)
//                    ->addTag("sonata.admin", array( 
//                        "manager_type"  => "orm", 
//                        "group"         => $Connection["name"], 
//                        "label"         => "Objects Orm", 
//                        "icon"          => '<span class="fa fa-binoculars"></span>' 
//                    ))
//                    ->setArguments(array(
//                        null,
//                        ArrayObject::class,
//                        ObjectsCRUDController::class,
//                        $Connection["connector"],
//                        $Id,
//                        "objects"
//                        ))
//                    ->addMethodCall("setModelManager", [ $container->get('sonata.admin.manager.splash') ])
//                    ;            
            //====================================================================//
            // Widgets Sonata Admin Class	
            
        }        

    }
    
}
