.. index:: single: Installation

Installation
============

First thing you should do before installing is to configure the forum by
creating a config/config.php file. You may want to copy the contents of
config/default\_config.php into the file first to make things easier. At
a minimum you should set the following values:

-  networkName
-  siteName
-  path
-  domain
-  dbType
-  dbServer
-  dbName
-  dbTestName (not required unless you are running tests on this
   instance)
-  dbUser
-  dbPassword

Once you have set those variables, and you have created a new empty
database (dbName), then you are ready to start installing the forum.

Before installing the forum, you will need to first install some symfony libraries.
To do this, cd into the symfony/ directory and then run the install.sh script:

.. code:: bash

    $ cd symfony/
    $ ./install.sh

Once the symfony libraries are installed, you can begin to install the forum.
To install the forum, you should first cd into maintenance/install, then
run install.php. 

.. code:: bash

    $ cd maintenance/install
    $ php install.php

This will initialize the database with all the necessary tables, and
will ask you several questions about the installation, like setting up
the Admin user, but will also ask you whether or not to import data from
several csv files if they exist. These files are in the following
formats: 

**provinces.csv**

name, color

**universities.csv**

name, province, latitude, longitude, order, default

**people.csv**

lastName, firstName, role, website, university, department, title, email 

**themes.csv**

acronym, name, description, phase

**projects.csv**

acronym, theme, title, status, type, description, problem, solution, projectLeader, projectCoLeader, phase, bigBet 

**project\_members.csv**

userName, projectName
