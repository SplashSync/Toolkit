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
    // Splash Bundles
    Splash\Toolkit\ToolkitBundle::class => Kernel::BUNDLE_ALL,
    Splash\Bundle\SplashBundle::class => Kernel::BUNDLE_ALL,
    Splash\Console\ConsoleBundle::class => Kernel::BUNDLE_ALL,
    Splash\Admin\SplashAdminBundle::class => Kernel::BUNDLE_ALL,
    Splash\Widgets\SplashWidgetsBundle::class => Kernel::BUNDLE_ALL,
    Splash\OpenApi\Bundle\SplashOpenApiBundle::class => Kernel::BUNDLE_ALL,
);
