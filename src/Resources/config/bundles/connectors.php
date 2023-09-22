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

//==============================================================================
// Splash Connectors
//==============================================================================

return array(
    //==============================================================================
    // Splash Fake Objects Connector
    Splash\Connectors\Faker\FakerBundle::class => Kernel::BUNDLE_ALL,
    //==============================================================================
    // Generic Connector
    Splash\Connectors\Soap\SoapBundle::class => Kernel::BUNDLE_ALL,
    Splash\Connectors\Flat\FlatBundle::class => Kernel::BUNDLE_ALL,
    //==============================================================================
    // Emailing Connectors
    /** @phpstan-ignore-next-line  */
    Splash\Connectors\MailChimp\MailChimpBundle::class => Kernel::BUNDLE_ALL,
    /** @phpstan-ignore-next-line  */
    Splash\Connectors\Mailjet\MailjetBundle::class => Kernel::BUNDLE_ALL,
    /** @phpstan-ignore-next-line  */
    Splash\Connectors\SendInBlue\SendInBlueBundle::class => Kernel::BUNDLE_ALL,
    /** @phpstan-ignore-next-line  */
    Splash\Connectors\Brevo\BrevoBundle::class => Kernel::BUNDLE_ALL,
    //==============================================================================
    // Logistics Connectors
    /** @phpstan-ignore-next-line  */
    Splash\Connectors\Optilog\OptilogBundle::class => Kernel::BUNDLE_ALL,
    /** @phpstan-ignore-next-line  */
    Splash\Connectors\ShippingBo\ShippingBoBundle::class => Kernel::BUNDLE_ALL,
    //==============================================================================
    // E-Commerce Connectors
    /** @phpstan-ignore-next-line  */
    Splash\Connectors\Shopify\ShopifyBundle::class => Kernel::BUNDLE_ALL,
    /** @phpstan-ignore-next-line  */
    KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle::class => Kernel::BUNDLE_ALL,
    //==============================================================================
    // Others Connectors
    /** @phpstan-ignore-next-line  */
    Splash\Connectors\ReCommerce\ReCommerceBundle::class => Kernel::BUNDLE_ALL,
);
