.. index:: single: BackboneModel.php

BackboneModel.php
=================

================     =====
**Location:**        extensions/GrandObjects/BackboneModel.php
**Source Code:**     `master`_
**Classes:**         `BackboneModel`_
================     =====

Description
-----------
The BackboneModel abstract class is used as the parent class to which many of the other classes extend.  The purpose of the BackboneModel is to allow for RESTful actions to be used by APIs.  Each class which implements a BackboneModel will typically have a corresponding javascript model in ``extensions/GrandObjects/BackboneModels/`` and an API in ``extensions/GrandObjects/API/``.

Abstract Methods
----------------------
- ``toArray()``
- ``create()``
- ``update()``
- ``delete()``
- ``exists()``
- ``getCacheId()``


.. _master: https://github.com/UniversityOfAlberta/GrandForum/blob/master/extensions/GrandObjects/BackboneModel.php
.. _BackboneModel: http://grand.cs.ualberta.ca/docs/classBackboneModel.html
