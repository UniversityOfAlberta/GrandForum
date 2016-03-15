.. index:: single: Milestone.php

Milestone.php
=============

================     =====
**Location:**        extensions/GrandObjects/Milestone.php
**Source Code:**     `master`_
**Classes:**         `Milestone`_
================     =====

Description
-----------
The Milestone class is used to access data from the ``grand_milestones`` table.  A Milestone is a specific goal that is to be accomplished by a Project.  The milestone has a start/end date (or quarters).  Each edit to a milestone is a created as a new revision.  The Milestones belong to more broad Activities.

Static Factory Methods
----------------------
- ``newFromId($milestone_id, $id=2147483647)``
- ``newFromIndex($id=2147483647)``
- ``newFromTitle($milestone_title, $id=2147483647)``


.. _master: https://github.com/UniversityOfAlberta/GrandForum/blob/master/extensions/GrandObjects/Milestone.php
.. _Milestone: http://grand.cs.ualberta.ca/docs/classMilestone.html
