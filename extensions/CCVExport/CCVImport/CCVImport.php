<?php

BackbonePage::register('CCVImport', 'CCV Import', 'network-tools', dirname(__FILE__));

class CCVImport extends BackbonePage {
    
    function isListed(){
        return true;
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isRoleAtLeast(MANAGER);
    }
    
    function getTemplates(){
        return array("csv_import");
    }
    
    function getViews(){
        return array("CSVImportView"
		     );
    }
    
    function getModels(){
        return array("CCVImportModel");
    }

}

?>
