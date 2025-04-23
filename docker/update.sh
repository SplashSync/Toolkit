#!/bin/bash
########################################################
# Connect Docker to GitLab
docker login https://registry.gitlab.com -u BadPixxel
########################################################
# Build Splash Toolkit Latest Docker Image
docker build ./ -t registry.gitlab.com/splashsync/toolkit:latest    --push --no-cache
########################################################
# Build Splash Toolkit Alpine Docker Image
docker build ./ -t registry.gitlab.com/splashsync/toolkit:alpine    --push
########################################################
# Build Splash Toolkit V3.0 Docker Image
docker build ./ -t registry.gitlab.com/splashsync/toolkit:2.5       --push
