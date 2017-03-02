<?php

$wgHooks['SubLevelTabs'][] = 'PdfConversion::createSubTabs';

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
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:PdfConversion";

        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "PdfConversion") ? "selected" : false;
            $tabs["Upload Pdf"]['subtabs'][] = TabUtils::createSubTab("PDF Conversion", "{$url}", $selected);
        }
        
        return true;
    }

}

?>
