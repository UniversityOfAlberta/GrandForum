.. index:: single: Relationship.php

Relationship.php
================

================     =====
**Location:**        extensions/GrandObjects/Relationship.php
**Source Code:**     `master`_
**Classes:**         `Relationship`_
================     =====

Description
-----------
The Relationship class is used to access data from the ``grand_relations`` table.  A Relationship is used to link two people by some relationship like 'Supervises' or 'Works With'.  The 'Supervises' relationship is specifically used to determine who a Person's HQP are.  The types of allowed relationships is defined in the config variable ``relationTypes``.

Static Factory Methods
----------------------
- ``newFromId($id)``

.. _master: https://github.com/UniversityOfAlberta/GrandForum/blob/master/extensions/GrandObjects/Relationship.php
.. _Relationship: http://grand.cs.ualberta.ca/docs/classRelationship.html
