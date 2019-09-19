.. index:: single: PDF.php

PDF.php
========

================     =====
**Location:**        extensions/GrandObjects/PDF.php
**Source Code:**     `master`_
**Classes:**         `PDF`_
================     =====

Description
-----------
The PDF class is used to access data from the ``grand_pdf_report`` tables.  A PDF will contain metadata about the PDF like the reporting year, generation date, submission date and report type.  When a PDF is instantiated, the download url can be accessed by calling the ``getUrl()`` method.  There are also some permissions to help prevent people who should not be seeing a PDF document by callin the ``canUserRead()`` method.

Static Factory Methods
----------------------
- ``newFromId($id)``
- ``newFromToken($tok)``


.. _master: https://github.com/UniversityOfAlberta/GrandForum/blob/master/extensions/GrandObjects/PDF.php
.. _PDF: http://grand.cs.ualberta.ca/docs/classPDF.html
