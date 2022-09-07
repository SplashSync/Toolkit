#!/bin/bash
########################################################
# Connect Docker to GitLab
docker login https://registry.gitlab.com -u BadPixxel
########################################################
# Build Splash Toolkit Alpine Docker Image
docker build --no-cache -t registry.gitlab.com/splashsync/toolkit:alpine  ./
########################################################
# Upload Docker Alpine Image to GitLab
docker push registry.gitlab.com/splashsync/toolkit:alpine

########################################################
# Build Splash Toolkit Docker Image
#docker build --no-cache -t registry.gitlab.com/splashsync/toolkit:alpine  ./
########################################################
# Upload Docker Image to GitLab
#docker push registry.gitlab.com/splashsync/toolkit