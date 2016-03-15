.. index:: single: Theme.php

Theme.php
=========

================     =====
**Location:**        extensions/GrandObjects/Theme.php
**Source Code:**     `master`_
**Classes:**         `Theme`_
================     =====

Description
-----------
The Theme class is used to access data from the ``grand_themes`` table.  A Theme is a grouping of Projects, so to get the Projects in the Theme use the ``getProjects()`` method.  Themes contain leaders just like Projects.  The name of 'theme' throughout the Forum can be renamed by changing the ``projectThemes`` config variable.

Static Factory Methods
----------------------
- ``newFromId($id)``
- ``newFromName($name)``


.. _master: https://github.com/UniversityOfAlberta/GrandForum/blob/master/extensions/GrandObjects/Theme.php
.. _Theme: http://grand.cs.ualberta.ca/docs/classTheme.html
