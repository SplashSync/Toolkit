#!/bin/bash
########################################################
# Connect Docker to GitLab
docker login https://registry.gitlab.com -u BadPixxel
########################################################
# Build Splash Toolkit Alpine Docker Image
docker build --no-cache -t registry.gitlab.com/splashsync/toolkit:3.0  ./
########################################################
# Upload Docker Alpine Image to GitLab
docker push registry.gitlab.com/splashsync/toolkit:3.0

########################################################
# Build Splash Toolkit Docker Image
docker build --no-cache -t registry.gitlab.com/splashsync/toolkit:latest  ./
########################################################
# Upload Docker Image to GitLab
docker push registry.gitlab.com/splashsync/toolkit:latest