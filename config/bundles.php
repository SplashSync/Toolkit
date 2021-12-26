<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

return array(
    App\UserBundle\AppUserBundle::class => array('all' => true),

    // Symfony Bundles
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => array('all' => true),
    Symfony\Bundle\TwigBundle\TwigBundle::class => array('all' => true),
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => array('all' => true),
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => array('dev' => true, 'test' => true),
    Symfony\Bundle\MonologBundle\MonologBundle::class => array('all' => true),
    Symfony\Bundle\DebugBundle\DebugBundle::class => array('dev' => true, 'test' => true),
    Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class => array('all' => true),
    Symfony\Bundle\WebServerBundle\WebServerBundle::class => array('dev' => true),
    Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class => array('all' => true),
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => array('all' => true),

    // Doctrine ORM
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => array('all' => true),

    // Sonata Project Bundles
    Sonata\DatagridBundle\SonataDatagridBundle::class => array('all' => true),
    Sonata\Doctrine\Bridge\Symfony\SonataDoctrineBundle::class => array('all' => true),
    Sonata\BlockBundle\SonataBlockBundle::class => array('all' => true),
    Sonata\UserBundle\SonataUserBundle::class => array('all' => true),
    Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle::class => array('all' => true),
    Sonata\AdminBundle\SonataAdminBundle::class => array('all' => true),
    Sonata\Twig\Bridge\Symfony\SonataTwigBundle::class => array('all' => true),
    Sonata\Form\Bridge\Symfony\SonataFormBundle::class => array('all' => true),

    // Various Bundles
    Knp\Bundle\MenuBundle\KnpMenuBundle::class => array('all' => true),
    Knp\Bundle\TimeBundle\KnpTimeBundle::class => array('all' => true),
    KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle::class => array('all' => true),
    FOS\UserBundle\FOSUserBundle::class => array('all' => true),
    Burgov\Bundle\KeyValueFormBundle\BurgovKeyValueFormBundle::class => array('all' => true),

    // Splash Bundles
    Splash\Bundle\SplashBundle::class => array('all' => true),
    Splash\Console\ConsoleBundle::class => array('all' => true),
    Splash\Admin\SplashAdminBundle::class => array('all' => true),
    Splash\Widgets\SplashWidgetsBundle::class => array('all' => true),
    Splash\OpenApi\Bundle\SplashOpenApiBundle::class => array('all' => true),

    // Splash Connectors
    Splash\Connectors\Faker\FakerBundle::class => array('all' => true),
    Splash\Connectors\Soap\SoapBundle::class => array('all' => true),
    Splash\Connectors\MailChimp\MailChimpBundle::class => array('all' => true),
    Splash\Connectors\Mailjet\MailjetBundle::class => array('all' => true),
    Splash\Connectors\SendInBlue\SendInBlueBundle::class => array('all' => true),
    Splash\Connectors\Shopify\ShopifyBundle::class => array('all' => true),
    Splash\Connectors\Optilog\OptilogBundle::class => array('all' => true),
    Splash\Connectors\ReCommerce\ReCommerceBundle::class => array('all' => true),
    Splash\Connectors\Flat\FlatBundle::class => array('all' => true),
);
