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

set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- apache2-foreground "$@"
fi

################################################################################
echo "Flush Symfony cache"
rm -Rf var/cache/*

echo "Serving Splash Toolkit..."
exec "$@"
