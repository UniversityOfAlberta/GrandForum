.. index:: single: Person.php

Person.php
==========

================     =====
**Location:**        extensions/GrandObjects/Person.php
**Source Code:**     `master`_
**Classes:**         `Person`_
================     =====

Description
-----------
The Person class is used to access data from the mw_user table.  It is similar to the User mediawiki class, however it can also be used to get additional information like getProjects(), getRoles(), getRelations(), getPapers(), getHQP().

Static Factory Methods
----------------------

- ``newFromId($id)``
- ``newFromName($name)``
- ``newFromReversedName($name)``
- ``newFromEmail($email)``
- ``newFromUser($user)``
- ``newFromWgUser()``
- ``newFromNameLike($name)``
- ``newFromAlias($alias)``

.. _master: https://github.com/UniversityOfAlberta/GrandForum/blob/master/extensions/GrandObjects/Person.php
.. _Person: http://grand.cs.ualberta.ca/docs/classPerson.html
