<?php

namespace Splash\Connectors\FakerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SplashFakerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        //====================================================================//
        // Add Splash Standalone Objects Service to Container	
        foreach ($config["objects"]  as $Object) {
            $container
                ->register('splash.connector.faker.object.' . $Object["id"], 'Splash\Connectors\FakerBundle\Objects\Generic')
                ->addTag('splash.standalone.object')
                ->addMethodCall('setConfiguration', array($Object))
                    ;
        }

    }
}
