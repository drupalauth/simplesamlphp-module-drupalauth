#!/usr/bin/env bash

rm -rf ./vendor composer.lock
composer install

./vendor/bin/phpcs

#lando phpunit -s php80
#lando style-lint -s php80
#lando phpunit -s php81
#lando style-lint -s php81
#lando phpunit -s php82
#lando style-lint -s php82
