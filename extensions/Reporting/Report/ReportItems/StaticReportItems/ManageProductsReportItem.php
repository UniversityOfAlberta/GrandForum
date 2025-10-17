<?php

class ManageProductsReportItem extends StaticReportItem {

    static $loaded = false;

    function render(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        
        $students = array();
        $studentNames = array();
        $studentFullNames = array();
        $person = Person::newFromWgUser();
        foreach($person->getHQP(true) as $hqp){
            $students[] = $hqp->getId();
            $studentNames[] = $hqp->getName();
            $studentFullNames[] = $hqp->getNameForForms();
        }
        $view = "";
        // Load the scripts for Manage People so that the University editing can be used
        if(!self::$loaded){
            $manageProducts = new ManageProducts();
            $scripts = array_merge($manageProducts->loadTemplates(true),
                                   $manageProducts->loadModels(true),
                                   $manageProducts->loadViews(true));
            $view .= "<style>
                    #manageProductsDescription { display: none; }
                    #listTable thead tr:first-child { display: none; }
                    #listTable .projectCell { display: none; }
                    #listTable .privateCell { display: none; }
                    #listTable tr { border-bottom: none !important; }
                    #listTable td, #listTable th { border-left: none !important; }
                    #saveProducts, #deletePrivate, #releasePrivate { display: none !important; }
                    .manageProductButtons { position: absolute; left: 5px; z-index: 1; } ";
            if($this->getAttr('categories') != ""){
                $view .= "tr#category { display: none; }";
            }
            $view .= "</style>".
                 implode("", $scripts)."
                 <link href='$wgServer$wgScriptPath/extensions/GrandObjectPage/ManageProducts/style.css' type='text/css' rel='stylesheet' />
                 <script type='text/javascript'>
                    publicationsFrozen = false;
                    students = ".json_encode($students).";
                    studentNames = ".json_encode($studentNames).";
                    studentFullNames = ".json_encode($studentFullNames).";
                 </script>";
            self::$loaded = true;
        }
        $projectJSON = ($this->projectId != 0) ? ", project: new Project(".Project::newFromId($this->projectId)->toJSON().")" : "";
        $projectId = ($this->projectId != 0) ? "{$this->projectId}" : "undefined";
        
        $categories = json_encode(array_filter(explode(",", $this->getAttr('categories'))));

        $view .= "<div id='{$this->id}' style='position: relative;'></div>
                 <script type='text/javascript'>
                    function {$this->id}(){
                         var products = me.getManagedProducts($projectId, true);
                         products.all = false;
                         var view = new ManageProductsView({el: $('#{$this->id}'), model: products, categories: {$categories} {$projectJSON}});
                     }
                     {$this->id}();
                 </script>";
        
        $item = $this->processCData($view);
        $wgOut->addHTML($item);
    }

    function renderForPDF(){
        global $wgOut, $wgUser;
        
        $item = $this->processCData($view);
        $wgOut->addHTML($item);
    }

}

?>
