.. index:: single: Role.php

Role.php
========

================     =====
**Location:**        extensions/GrandObjects/Role.php
**Source Code:**     `master`_
**Classes:**         `Role`_
================     =====

Description
-----------
The Role class is used to access data from the ``grand_roles`` table.  A Role belongs to a Person and can optionally contain a list of Projects that the Role is associated with.  For example a Person might be an HQP on Project1.  Roles are used to determine what permissions the Person has on the Forum.  Roles have a rough hierarchy which is defined in the global variable ``$wgRoleValues``, and the types of Roles enabled in the system can be configured in the config.php file.

Static Factory Methods
----------------------
- ``newFromId($id)``


.. _master: https://github.com/UniversityOfAlberta/GrandForum/blob/master/extensions/GrandObjects/Role.php
.. _Role: http://grand.cs.ualberta.ca/docs/classRole.html
