#!/bin/bash

mkdir bin
mkdir output
mkdir screenshots

curl http://getcomposer.org/installer | php
echo "This might take a while..."
php composer.phar install --prefer-source
echo "Downloading Selenium..."
wget https://github.com/SeleniumHQ/selenium/releases/download/selenium-3.141.59/selenium-server-standalone-3.141.59.jar -O bin/selenium.jar
echo "Downloading GeckoDriver..."
wget https://github.com/mozilla/geckodriver/releases/download/v0.34.0/geckodriver-v0.34.0-linux64.tar.gz -O geckodriver.tar.gz
tar -xvf geckodriver.tar.gz
mv geckodriver bin/geckodriver
rm geckodriver.tar.gz
