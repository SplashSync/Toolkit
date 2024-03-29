#!/bin/sh
################################################################################
#
# * This file is part of SplashSync Project.
# *
# * Copyright (C) Splash Sync <www.splashsync.com>
# *
# * This program is distributed in the hope that it will be useful,
# * but WITHOUT ANY WARRANTY; without even the implied warranty of
# * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# *
# * For the full copyright and license information, please view the LICENSE
# * file that was distributed with this source code.
# *
# * @author Bernard Paquier <contact@splashsync.com>
#
################################################################################

################################################################################
echo "Flush Symfony cache"
chown www-data:www-data -Rf  var/
rm -Rf var/cache

echo "Install Assets"
php bin/console assets:install --symlink

echo "Stop Symfony Server"
/usr/local/bin/symfony server:stop

echo "Serving Splash Toolkit..."
exec "$@"
