#!/bin/bash

mkdir bin
mkdir output
mkdir screenshots

curl http://getcomposer.org/installer | php
echo "This might take a while..."
php composer.phar install --prefer-source
#echo "Downloading Selenium..."
#curl http://selenium-release.storage.googleapis.com/2.43/selenium-server-standalone-2.43.0.jar > bin/selenium.jar
