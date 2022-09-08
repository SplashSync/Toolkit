<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Symfony Kernel.
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /** @var string */
    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /**
     * @return string
     */
    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/var/cache/'.$this->environment;
    }

    /**
     * @return string
     */
    public function getLogDir(): string
    {
        return $this->getProjectDir().'/var/log';
    }

    /**
     * Register App Bundles
     *
     * @retrun void
     */
    public function registerBundles()
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';
        $dynamics = $this->getDynamicBundles();
        foreach (array_merge($contents, $dynamics) as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                /** @phpstan-ignore-next-line  */
                yield new $class();
            }
        }
    }

    /**
     * Configure Sf Container
     *
     * @param ContainerBuilder $container
     * @param LoaderInterface  $loader
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir().'/config/bundles.php'));
        // Feel free to remove the "container.autowiring.strict_mode" parameter
        // if you are using symfony/dependency-injection 4.0+ as it's the default behavior
        $container->setParameter('container.autowiring.strict_mode', true);
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir().'/config';

        $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');
    }

    /**
     * Configure Sf Routing
     *
     * @param RouteCollectionBuilder $routes
     */
    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir().'/config';

        $routes->import($confDir.'/{routes}/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}'.self::CONFIG_EXTS, '/', 'glob');
    }

    /**
     * Register App Bundles
     *
     * @retrun void
     */
    private function getDynamicBundles(): array
    {
        $bundles = array();
        //==============================================================================
        // Search for Bundles in Connectors Dir
        $finder = new Finder();
        $files = $finder
            ->files()
            ->in(dirname(__DIR__)."/connectors")
            ->ignoreVCS(true)
            ->depth(array(0,1))
            ->name('*Bundle.php')
        ;
        //==============================================================================
        // Walk on Dynamic Connectors
        foreach ($files as $file) {
            //==============================================================================
            // Build Class Name
            $className = sprintf(
                "Splash\Connectors\%s\%s",
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension()
            );
            //==============================================================================
            // Register Bundle
            if (class_exists($className)) {
                $bundles[$className] = array('all' => true);
            }
        }

        return $bundles;
    }
}
