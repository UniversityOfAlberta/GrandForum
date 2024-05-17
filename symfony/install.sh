#!/bin/bash

mkdir -p bin
mkdir -p output
mkdir -p screenshots

curl -s http://getcomposer.org/installer | php
echo "This might take a while..."
php composer.phar install --prefer-source
echo "Downloading Selenium..."
wget -nv https://github.com/SeleniumHQ/selenium/releases/download/selenium-3.141.59/selenium-server-standalone-3.141.59.jar -O bin/selenium.jar &> /dev/null
echo "Downloading GeckoDriver..."
wget -nv https://github.com/mozilla/geckodriver/releases/download/v0.34.0/geckodriver-v0.34.0-linux64.tar.gz -O geckodriver.tar.gz &> /dev/null
tar -xf geckodriver.tar.gz
mv geckodriver bin/geckodriver
rm geckodriver.tar.gz
echo "Downloading Latest Firefox..."
rm -fr bin/firefox
wget -nv --content-disposition "https://download.mozilla.org/?product=firefox-latest-ssl&os=linux64&lang=en-US" -O firefox.tar.bz2 &> /dev/null
tar -xf firefox.tar.bz2
mv firefox bin/firefox
rm -fr firefox.tar.bz2
