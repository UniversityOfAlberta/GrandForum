.. index:: single: BackbonePage

BackbonePage
============

BackbonePages are type of SpecialPage which contain a backbone.js application.  A new BackbonePage will be a subclass of BackbonePage, and will implement several of the abstract methods.  Javascript files will be added in the Models, Views and Templates directories.

Methods to implement
--------------------

**userCanExecute($user)**

Returns whether or not the user can view this page

**getTemplates**

Returns an array of templates to import

**getViews**

Returns an array of views to import

**getModels**

Returns an array of models to import

Registering a BackbonePage
--------------------------

To make a BackbonePage known to the Forum, the BackbonePage::register function needs to be called.  ie:

.. code:: php

    BackbonePage::register('Products', 'Products', 'network-tools', dirname(__FILE__));
    
Alternatively if you do not want it to be a SpecialPage, and rather a smaller inline widget (like the global search) then you would instead do the following:

.. code:: php

    $wgHooks['BeforePageDisplay'][] = 'initGlobalSearch';

    function initGlobalSearch($out, $skin){
        global $wgServer, $wgScriptPath;
        BackbonePage::$dirs['globalsearch'] = dirname(__FILE__);
        $globalSearch = new GlobalSearch();
        $globalSearch->loadTemplates();
        $globalSearch->loadModels();
        $globalSearch->loadHelpers();
        $globalSearch->loadViews();
        $globalSearch->loadMain();
        return true;
    }

Bake
-------

bake is a tool that can be used to initialize and update BackbonePages so that that they conform to the directory structure and that all the abstract methods are up to date.  To use bake first you need to add the following to your .bashrc

.. code:: bash 
    
    function bake(){
        cp /local/data/home/dwt/bake/bake.php ~/bake.php
        php ~/bake.php $1 $2 $3
    }
    
Once you have done that you can type the following to create a new BackbonePage

.. code:: bash

    $ bake create NewBackbonePage
    
a new directory "NewBackbonePage" will be created and will come with several default file templates which can be edited.

If some changes have been made to the structure of the templates, you can run

.. code:: bash

    $ bake update
    
that command will do a merge of the changes so that the BackbonePage instance is up to date.  You may need to manually merge if there are conflicts.
