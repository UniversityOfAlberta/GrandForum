.. index:: single: AnnokiControl

AnnokiControl
=============

This is essentially an extension loader for the rest of the extensions.  The extension loading is done by adding one of these:

.. code-block:: php

    $egAnnokiExtensions['GrandObjects'] = array('name' => 'GrandObjects',
                                                'path' => "$IP/extensions/GrandObjects/GrandObjects.php");

If the extension 'GrandObjects' is also enabled in the config.php file, then it will be loaded whenever a page request is made.

DBFunctions
===========

Another useful file is DBFunctions.php.  This file contains database access functions which automatically sanitize inputs in queries.  For example simple SELECT query can be written like the following:

.. code-block:: php
    
    DBFunctions::select(array('mw_user'),
                        array('*'),
                        array('user_id' => EQ('1')));
                        
Which gets translated to:

.. code-block:: sql

    SELECT *
    FROM `mw_user`
    WHERE `user_id` = '1'
    
You can go through the file to see all of the functions, but the ones that are most commonly used are:

.. code-block:: php
    
    DBFunctions::select($tables=array(), $cols=array(), $where=array(), $order=array(), $limit=array());
    
    DBFunctions::insert($table, $values=array(), $rollback=false);
    
    DBFunctions::delete($table, $where=array(), $rollback=false);
    
    DBFunctions::update($table, $values=array(), $where=array(), $limit=array(), $rollback=false);
    
You can also run raw SQL using the following:

.. code-block:: php

    DBFunctions::execSQL($sql, $update=false, $rollback=false);

just be aware that inputs are not sanitized with this function, so it will need to be done manually by using DBFunctions::escape() for each input.
