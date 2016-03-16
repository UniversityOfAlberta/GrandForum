.. index:: single: Files

Important Files/Folders
=======================

config/config.php
-----------------

Configurations to the forum will be done here.  Config settings like database credentials, name of the Forum and roles.

LocalSettings.php
-----------------

This is where mediawiki config is done.  These settings are shared between Forum instances, so specific Forum config should still go in ``config.php``.

extensions/
-----------

This is where most of the development to the Forum goes.  Sub-folders in this directory are separate extensions which can be loaded in using the AnnokiControl extension (and configurable in config.php)

extensions/AnnokiControl/AnnokiControl.php
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This is essentially the entry point for the extensions.  This file loads all other extensions based on the configurations in ``config.php``.

skins/cavendish/
----------------

The skin/shell of the Forum is all in here.  The css files in particular ``main.css`` and ``cavendish.css``.  The template of the skin is in ``cavendish.php``.

scripts/
--------

Most javascript libraries go in here.  They need to be imported in files like ``cavendish.php``

Classes/
-------

Any 3rd party php libraries go in here.


