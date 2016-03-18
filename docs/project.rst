.. index:: single: Project.php

Project.php
===========

================     =====
**Location:**        extensions/GrandObjects/Project.php
**Source Code:**     `master`_
**Classes:**         `Project`_
================     =====

Description
-----------
The Project class is used to access data from the grand_project table (additional tables are used in joins).  A Project may contain multiple evolutions which can be accessed using the getPreds() method.  The project belongs to a Theme, and will contain members and leaders.  Projects can be 'disabled' in the system by editing the value of the ``projectsEnabled`` config variable.

Static Factory Methods
----------------------
- ``newFromId($id)``
- ``newFromName($name)``
- ``newFromTitle($title)``
- ``newFromHistoricId($id, $evolutionId=null)``
- ``newFromHistoricName($name)``

.. _master: https://github.com/UniversityOfAlberta/GrandForum/blob/master/extensions/GrandObjects/Project.php
.. _Project: http://grand.cs.ualberta.ca/docs/classProject.html
