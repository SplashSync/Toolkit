<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

return array(
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => array('all' => true),
    Sonata\EasyExtendsBundle\SonataEasyExtendsBundle::class => array('all' => true),
    Sonata\DatagridBundle\SonataDatagridBundle::class => array('all' => true),
    Sonata\CoreBundle\SonataCoreBundle::class => array('all' => true),
    Symfony\Bundle\TwigBundle\TwigBundle::class => array('all' => true),
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => array('all' => true),
    Sonata\BlockBundle\SonataBlockBundle::class => array('all' => true),
    Knp\Bundle\MenuBundle\KnpMenuBundle::class => array('all' => true),
    FOS\UserBundle\FOSUserBundle::class => array('all' => true),
    Sonata\UserBundle\SonataUserBundle::class => array('all' => true),
    Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle::class => array('all' => true),
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => array('all' => true),
    Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle::class => array('all' => true),
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => array('dev' => true, 'test' => true),
    Burgov\Bundle\KeyValueFormBundle\BurgovKeyValueFormBundle::class => array('all' => true),
    App\UserBundle\AppUserBundle::class => array('all' => true),
    Sonata\AdminBundle\SonataAdminBundle::class => array('all' => true),
    Symfony\Bundle\MonologBundle\MonologBundle::class => array('all' => true),
    Symfony\Bundle\DebugBundle\DebugBundle::class => array('dev' => true, 'test' => true),
    Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class => array('all' => true),
    Mopa\Bundle\BootstrapBundle\MopaBootstrapBundle::class => array('all' => true),
    FOS\JsRoutingBundle\FOSJsRoutingBundle::class => array('all' => true),
    Knp\Bundle\TimeBundle\KnpTimeBundle::class => array('all' => true),
    Splash\Bundle\SplashBundle::class => array('all' => true),
    Splash\Widgets\SplashWidgetsBundle::class => array('all' => true),
    Splash\Connectors\Faker\FakerBundle::class => array('all' => true),
    Splash\Connectors\Soap\SoapBundle::class => array('all' => true),
    Splash\Admin\SplashAdminBundle::class => array('all' => true),
    Splash\SonataAdminMonologBundle\SplashSonataAdminMonologBundle::class => array('all' => true),
    Splash\Connectors\MailChimp\MailChimpBundle::class => array('all' => true),
    Splash\Connectors\Mailjet\MailjetBundle::class => array('all' => true),
    Splash\Connectors\SendInBlue\SendInBlueBundle::class => array('all' => true),
    Splash\Connectors\Shopify\ShopifyBundle::class => array('all' => true),
    Splash\Connectors\Optilog\OptilogBundle::class => array('all' => true),
    Splash\Console\ConsoleBundle::class => array('all' => true),
    KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle::class => array('all' => true),
    Symfony\Bundle\WebServerBundle\WebServerBundle::class => array('dev' => true),
);
