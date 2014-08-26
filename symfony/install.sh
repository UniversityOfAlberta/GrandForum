#!/bin/bash

mkdir bin
mkdir output
mkdir screenshots

curl http://getcomposer.org/installer | php
echo "This might take a while..."
php composer.phar install --prefer-source
echo "Downloading Selenium..."
curl http://selenium-release.storage.googleapis.com/2.42/selenium-server-standalone-2.42.2.jar > bin/selenium.jar
