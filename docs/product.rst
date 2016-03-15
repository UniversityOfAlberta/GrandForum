.. index:: single: Product.php

Product.php (Paper)
===================

================     =====
**Location:**        extensions/GrandObjects/Paper.php
**Source Code:**     `master`_
**Classes:**         `Paper`_, `Product`_
================     =====

Description
-----------
The Product class is used to access data from the ``grand_products`` table.  The Product class is an alias of the Paper class.  Paper.php may end up being depricated in favor of Product.php in the future.  A Product contains common information like authors, title, description and date.  The specific attributes for each type of Product is contained in the $data array, and the structure of these fields are defined in the ``extenstions/GrandObjects/ProductStructures/`` xml files.  These files will define all of the categories, and types that can be used for the respective forum instance.  Products can be associated with Projects, and if they are, then are considered part of the network.

Static Factory Methods
----------------------
- ``newFromId($id)``
- ``newFromCCVId($ccv_id)``
- ``newFromBibTeXId($bibtex_id, $title="")``
- ``newFromIds($ids, $onlyPublic=true)``
- ``newFromTitle($title, $category = "%", $type = "%", $status = "%")``

.. _master: https://github.com/UniversityOfAlberta/GrandForum/blob/master/extensions/GrandObjects/Paper.php
.. _Paper: http://grand.cs.ualberta.ca/docs/classPaper.html
.. _Product: http://grand.cs.ualberta.ca/docs/classProduct.html
