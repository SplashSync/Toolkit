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

namespace Splash\Toolkit;

use Exception;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Splash Toolkit Symfony Kernel.
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * This Bundle is Always Active
     */
    const BUNDLE_ALL = array("all" => true);

    /**
     * This Bundle is Only Active in DEV Environments
     */
    const BUNDLE_DEBUG = array('dev' => true, 'test' => true);

    /**
     * @var null|string
     */
    private static ?string $projectDir = null;

    /**
     * Gets the application root dir (path of the project's composer file).
     *
     * @return string The project root dir
     */
    public function getProjectDir(): string
    {
        return self::getProjectDirStatic();
    }

    /**
     * Gets the application root dir (path of the project's composer file).
     *
     * @return string The project root dir
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function getProjectDirStatic(): string
    {
        global $_composer_autoload_path;

        if (null === self::$projectDir) {
            $reflectionObject = new \ReflectionObject(new self("dev", false));

            if (!file_exists($dir = (string) $reflectionObject->getFileName())) {
                throw new \LogicException(
                    sprintf('Cannot auto-detect project dir for kernel of class "%s".', $reflectionObject->name)
                );
            }

            $dir = $rootDir = realpath($_composer_autoload_path ?? "") ?: $dir;
            while (!file_exists($dir.'/composer.json') || (false !== strpos((string) $dir, "paddock-core"))) {
                if (($dir === \dirname($dir))) {
                    return self::$projectDir = $rootDir;
                }
                $dir = \dirname($dir);
            }
            self::$projectDir = $dir;
        }

        return self::$projectDir;
    }

    /**
     * Register App Bundles
     *
     * @retrun void
     */
    public function registerBundles(): iterable
    {
        //==============================================================================
        // Search for All Active Bundles
        $bundles = array_merge(
            $this->getCoreBundles(),
            $this->getSplashBundles(),
            require $this->getToolkitResourcesPath()."/bundles/splash.php",
            $this->getConnectorsBundles(),
            $this->getLocalBundles(),
            $this->getDynamicBundles(),
        );

        foreach ($bundles as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                /** @phpstan-ignore-next-line  */
                yield new $class();
            }
        }
    }

    /**
     * Configure Container from Project Config Dir & Toolkit Config Dir
     *
     * @param ContainerBuilder $container
     * @param LoaderInterface  $loader
     *
     * @throws Exception
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        //==============================================================================
        // Search for Configs in Project & Toolkit Dirs
        $confDirs = array(
            $this->getProjectDir().'/config',
            $this->getToolkitResourcesPath(),
        );
        //==============================================================================
        // Walk on Config Dirs
        foreach ($confDirs as $confDir) {
            if (!is_dir($confDir)) {
                continue;
            }
            $loader->load($confDir.'/{packages}/*.yaml', 'glob');
            $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*.yaml', 'glob');
            $loader->load($confDir.'/{services}.yaml', 'glob');
            $loader->load($confDir.'/{services}_'.$this->environment.'.yaml', 'glob');
        }
    }

    /**
     * Configure Sf Routing
     *
     * @param RouteCollectionBuilder $routes
     */
    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        //==============================================================================
        // Search for Configs in Project & Toolkit Dirs
        $confDirs = array(
            $this->getProjectDir().'/config',
            $this->getToolkitResourcesPath(),
        );
        //==============================================================================
        // Walk on Config Dirs
        foreach ($confDirs as $confDir) {
            if (!is_dir($confDir)) {
                continue;
            }
            $routes->import($confDir.'/{routes}/*.yaml', '/', 'glob');
            $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*.yaml', '/', 'glob');
            $routes->import($confDir.'/{routes}.yaml', '/', 'glob');
        }
    }

    private function getToolkitResourcesPath(): string
    {
        return __DIR__."/Resources/config";
    }

    /**
     * Register Toolkit Core Bundles
     *
     * @retrun void
     */
    private function getCoreBundles(): array
    {
        return require $this->getToolkitResourcesPath()."/bundles/core.php";
    }

    /**
     * Register Splash Bundles
     *
     * @retrun void
     */
    private function getSplashBundles(): array
    {
        return require $this->getToolkitResourcesPath()."/bundles/splash.php";
    }

    /**
     * Register Available Connectors Bundles
     *
     * @retrun void
     */
    private function getConnectorsBundles(): array
    {
        return self::filterBundles(require $this->getToolkitResourcesPath()."/bundles/connectors.php");
    }

    /**
     * Register Available Local Bundles
     *
     * @retrun void
     */
    private function getLocalBundles(): array
    {
        if (file_exists(self::getProjectDir()."/config/bundles.php")) {
            return require self::getProjectDir()."/config/bundles.php";
        }

        return array();
    }

    /**
     * Register Dynamic Connectors Bundles
     *
     * @retrun void
     */
    private function getDynamicBundles(): array
    {
        $bundles = array();
        //==============================================================================
        // Safety Check - Connector Dir Exists
        if (!is_dir($this->getProjectDir()."/connectors")) {
            return $bundles;
        }
        //==============================================================================
        // Search for Bundles in Connectors Dir
        $finder = new Finder();
        $files = $finder
            ->files()
            ->in($this->getProjectDir()."/connectors")
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
                "Splash\\Connectors\\%s\\%s",
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension()
            );
            //==============================================================================
            // Register Bundle
            if (class_exists($className)) {
                $bundles[$className] = self::BUNDLE_ALL;
            }
        }

        return $bundles;
    }

    /**
     * Filter Inactive Bundles
     *
     * @param array<class-string, array> $bundles
     *
     * @return array<class-string, array>
     */
    private static function filterBundles(array $bundles): array
    {
        foreach (array_keys($bundles) as $class) {
            if (!class_exists($class)) {
                unset($bundles[$class]);
            }
        }

        return $bundles;
    }
}
