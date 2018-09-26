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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;


use App\ExplorerBundle\Admin\ObjectAdmin;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AppExplorerExtension extends Extension implements CompilerPassInterface, PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
//        $configuration = new Configuration();
//        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
  
//            $container
//                ->register('splash.explorer', 'Splash\Connectors\FakerBundle\Objects\Generic');
//                    
//var_dump(        $container->findTaggedServiceIds('splash.standalone.object'));
//exit;        


    }
    
    public function prepend(ContainerBuilder $container)
    {
//var_dump(        $container->findTaggedServiceIds('splash.standalone.object'));
    }    
    
    public function process(ContainerBuilder $container)
    {
//var_dump(        $container->findTaggedServiceIds('splash.standalone.object'));

        $pool = $container->get('sonata.admin.pool');

        //====================================================================//
        // Add Availables Splash Connectors to Admin Explore Service	
        foreach ($container->findTaggedServiceIds('splash.connectors')  as $Connector) {
//            $container
//                ->register('splash.explorer', ObjectAdmin::class)
////                ->register('splash.explorer.' . $Connector, 'Splash\Connectors\FakerBundle\Objects\Generic')
//                ->addTag(0, array( "name" => "sonata.admin", "manager_type" => "orm", "group" => "Node", "label" => "Category" ))
//                    ->setPublic(true)
////                ->addMethodCall('setConfiguration', array($Object))
//                    ;
//            
//            $Ids    =   $pool->getAdminServiceIds();
//            $Ids[]  =   'splash.explorer';
//            $pool->setAdminServiceIds($Ids);
            
        }
       // ... do something during the compilation
    }    
    
}
