.. index:: single: Wiki.php

Wiki.php
========

================     =====
**Location:**        extensions/GrandObjects/Wiki.php
**Source Code:**     `master`_
**Classes:**         `Wiki`_
================     =====

Description
-----------
The Wiki class is used to get information about MediaWiki Articles.  It has more limited functionality compared to the built in MediaWiki classes, so it does not replace them, however for simple tasks this class can make things easier and it also implements the BackboneModel class so that it has some RESTful functionality.

Static Factory Methods
----------------------
- ``newFromId($id)``
- ``newFromTitle($text)``

.. _master: https://github.com/UniversityOfAlberta/GrandForum/blob/master/extensions/GrandObjects/Wiki.php
.. _Wiki: http://grand.cs.ualberta.ca/docs/classWiki.html
