.. index:: single: Contribution.php

Contribution.php
================

================     =====
**Location:**        extensions/GrandObjects/Contribution.php
**Source Code:**     `master`_
**Classes:**         `Contribution`_
================     =====

Description
-----------
The Contribution class is used to access data from the ``grand_contributions`` table.  A Contribution stores information about financial contributions made typically by a Partner.  A contribution can be either cash or inkind or both.  Contributions have a list of associated people involved, and project(s) that the contribution is associated with.  Permissions for Contributions are determined by the People involved and the associated Projects.

Static Factory Methods
----------------------
- ``newFromId($id)``
- ``newFromName($name)``
- ``newFromRevId($id)``


.. _master: https://github.com/UniversityOfAlberta/GrandForum/blob/master/extensions/GrandObjects/Contribution.php
.. _Contribution: http://grand.cs.ualberta.ca/docs/classContribution.html
