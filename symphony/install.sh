#!/bin/bash

mkdir bin
mkdir output
mkdir screenshots

curl http://getcomposer.org/installer | php
echo "This might take a while..."
php composer.phar install --prefer-source
echo "Downloading Selenium..."
curl http://selenium.googlecode.com/files/selenium-server-standalone-2.38.0.jar > bin/selenium.jar

ln -s vendor/doxphp/doxphp/bin/doxphp bin/doxphp
ln -s vendor/doxphp/doxphp/bin/doxphp2sphinx bin/doxphp2sphinx
