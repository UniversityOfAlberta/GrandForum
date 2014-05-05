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

To install the forum, you should first cd into maintenance/install, then
run install.php. 

::

    $ cd maintenance/install
    $ php install.php

This will initialize the database with all the necessary tables, and
will ask you several questions about the installation, like setting up
the Admin user, but will also ask you whether or not to import data from
several csv files if they exist. These files are in the following
formats: 

**people.csv**

lastName, firstName, role, website, university, department, title, email 

**themes.csv**

acronym, name, description, phase

**projects.csv**

acronym, theme, title, status, type, description, problem, solution, projectLeader, projectCoLeader, phase, bigBet 

**project\_members.csv**

userName, projectName
