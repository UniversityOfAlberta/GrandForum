<?php

class ManageProductsReportItem extends StaticReportItem {

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
        
        // Load the scripts for Manage People so that the University editing can be used
        $manageProducts = new ManageProducts();
        $scripts = array_merge($manageProducts->loadTemplates(true),
                               $manageProducts->loadModels(true),
                               $manageProducts->loadViews(true));
        $projectJSON = ($this->projectId != 0) ? ", project: new Project(".Project::newFromId($this->projectId)->toJSON().")" : "";
        $projectId = ($this->projectId != 0) ? "{$this->projectId}" : "undefined";
        
        $categories = json_encode(explode(",", $this->getAttr('categories')));

        $view = "<style>
                    #manageProductsDescription { display: none; }
                 </style>
                 <link href='$wgServer$wgScriptPath/extensions/GrandObjectPage/ManageProducts/style.css' type='text/css' rel='stylesheet' />"
                .implode("", $scripts)."
                 <div id='manageProducts'></div>
                 <script type='text/javascript'>    
                     var publicationsFrozen = false;
                     var students = ".json_encode($students).";
                     var studentNames = ".json_encode($studentNames).";
                     var studentFullNames = ".json_encode($studentFullNames).";
                     var products = me.getManagedProducts($projectId);
                     products.all = false;
                     view = new ManageProductsView({el: $('#manageProducts'), model: products, categories: {$categories} {$projectJSON}});
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
