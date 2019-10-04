################################################################################
#
#  This file is part of SplashSync Project.
# 
#  Copyright (C) Splash Sync <www.splashsync.com>
# 
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# 
#  For the full copyright and license information, please view the LICENSE
#  file that was distributed with this source code.
# 
#  @author Bernard Paquier <contact@splashsync.com>
#
################################################################################

echo "*************************************************************************"
echo "** Build Toolkit Docker ..."
echo "*************************************************************************"

# docker-compose stop -v
docker-compose build
# docker-compose up -d

echo "*************************************************************************"
echo "** Configure the ToolKit ..."
echo "*************************************************************************"

docker-compose run symfony php -v

docker-compose run symfony ls -la /app/


echo "*************************************************************************"
echo "** Install Toolkit ..."
echo "*************************************************************************"

echo "** Composer Update "
docker-compose run symfony composer update

docker-compose up

# echo "** Install Symfony "
# docker-compose exec symfony php bin/console --env=prod cache:clear --no-warmup
# docker-compose exec symfony php bin/console --env=prod assets:install --force --symlink --clean


