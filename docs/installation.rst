.. index:: single: Installation

Pre-Requisites
==============

Make sure that Apache, PHP and MySQL/MariaDB are installed.  For mysql, you will likely need
to add the following to your my.cnf file

.. code-block:: bash

    sql_mode = STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION

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

.. code-block:: bash

    $ cd symfony/
    $ ./install.sh

You should also install some other system packages like pdftk, php-mbstring, php-mcrypt.  
The Forum also uses APC (Alternative PHP Cache) as an opcode and general cache to improve 
the performance.  It isn't required, but for larger installations can make a big difference 
(PHP 5.x only).

Once the symfony libraries are installed, you can begin to install the forum.
To install the forum, you should first cd into maintenance/install, then
run install.php. 

.. code-block:: bash

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
