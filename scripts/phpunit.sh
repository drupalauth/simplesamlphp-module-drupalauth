#!/usr/bin/env bash

php -v
rm -rf ./vendor composer.lock
composer install

./vendor/bin/phpunit
