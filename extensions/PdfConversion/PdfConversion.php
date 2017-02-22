<?php

BackbonePage::register('PdfConversion', 'PDF Conversion', 'network-tools', dirname(__FILE__));

class PdfConversion extends BackbonePage {
    
    function isListed(){
        return true;
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isRoleAtLeast(ADMIN);
    }
    
    function getTemplates(){
        return array("pdf_conversion");
    }
    
    function getViews(){
        return array("PdfConversionView");
    }
    
    function getModels(){
        return array("PdfConversionModel");
    }

}

?>
