#!/bin/sh

set -eux

APP_VERSION=develop

docker pull nginxinc/nginx-unprivileged:alpine

docker build --pull --no-cache --build-arg APP_VERSION=${APP_VERSION} --tag=os2display/display-api-service:${APP_VERSION} --file="display-api-service/Dockerfile" display-api-service
docker build --no-cache --build-arg VERSION=${APP_VERSION} --tag=os2display/display-api-service-nginx:${APP_VERSION} --file="nginx/Dockerfile" nginx

# docker push os2display/display-api-service:${APP_VERSION}
# docker push os2display/display-api-service-nginx:${APP_VERSION}
