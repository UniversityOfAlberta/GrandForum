<?php

BackbonePage::register('CCVImport', 'CCV Import', 'network-tools', dirname(__FILE__));

class CCVImport extends BackbonePage {
    
    function isListed(){
        return true;
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isRoleAtLeast(INACTIVE);
    }
    
    function getTemplates(){
        return array(
		     "Backbone/*",
		     "ccv_import",
		     "csv_import",
		     "eval_import",
		     "grad_import",
		     "grant_import",
		     "tab"
		     );
    }
    
    function getViews(){
        return array("
		     Backbone/*",
		     "CCVImportView",
		     "CSVImportView",
		     "EvalImportView",
		     "GradImportView",
		     "GrantImportView",
		     "TabView"
		     );
    }
    
    function getModels(){
        return array("CCVImportModel");
    }

}

?>
