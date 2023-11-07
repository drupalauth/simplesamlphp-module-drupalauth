#!/usr/bin/env bash

rm -rf ./vendor composer.lock
composer install

./vendor/bin/phpcbf
