#!/bin/bash
mkdir TopicModeling
wget http://mallet.cs.umass.edu/dist/mallet-2.0.8.tar.gz
tar -xzvf mallet-2.0.8.tar.gz
mv -v mallet-2.0.8/* TopicModeling/
rm -r mallet-2.0.8
rm mallet-2.0.8.tar.gz
