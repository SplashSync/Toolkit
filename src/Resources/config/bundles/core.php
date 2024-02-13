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

use Splash\Toolkit\Kernel;

return array(
    //==============================================================================
    // Symfony Bundles
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => Kernel::BUNDLE_ALL,
    Symfony\Bundle\TwigBundle\TwigBundle::class => Kernel::BUNDLE_ALL,
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => Kernel::BUNDLE_ALL,
    Symfony\Bundle\MonologBundle\MonologBundle::class => Kernel::BUNDLE_ALL,
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => Kernel::BUNDLE_ALL,
    //==============================================================================
    // Doctrine ORM
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => Kernel::BUNDLE_ALL,
    //==============================================================================
    // Sonata Project Bundles
    Sonata\Doctrine\Bridge\Symfony\SonataDoctrineBundle::class => Kernel::BUNDLE_ALL,
    Sonata\BlockBundle\SonataBlockBundle::class => Kernel::BUNDLE_ALL,
    Sonata\UserBundle\SonataUserBundle::class => Kernel::BUNDLE_ALL,
    Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle::class => Kernel::BUNDLE_ALL,
    Sonata\AdminBundle\SonataAdminBundle::class => Kernel::BUNDLE_ALL,
    Sonata\Twig\Bridge\Symfony\SonataTwigBundle::class => Kernel::BUNDLE_ALL,
    Sonata\Form\Bridge\Symfony\SonataFormBundle::class => Kernel::BUNDLE_ALL,
    //==============================================================================
    // Various Bundles
    Knp\Bundle\MenuBundle\KnpMenuBundle::class => Kernel::BUNDLE_ALL,
    Knp\Bundle\TimeBundle\KnpTimeBundle::class => Kernel::BUNDLE_ALL,

    Burgov\Bundle\KeyValueFormBundle\BurgovKeyValueFormBundle::class => Kernel::BUNDLE_ALL,
    KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle::class => Kernel::BUNDLE_ALL,
    //==============================================================================
    // Symfony Debug Bundles
    Symfony\Bundle\DebugBundle\DebugBundle::class => Kernel::BUNDLE_DEBUG,
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => Kernel::BUNDLE_DEBUG,
);
