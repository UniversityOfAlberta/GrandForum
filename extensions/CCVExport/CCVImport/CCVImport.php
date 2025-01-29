<?php
$wgHooks['ToolboxLinks'][] = 'CCVImport::createToolboxLinks';
BackbonePage::register('CCVImport', 'Import UoA Data', 'network-tools', dirname(__FILE__));

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
		     "eval_import",
		     "grad_import",
		     "grant_import",
		     "course_import",
		     "tab",
		     );
    }
    
    function getViews(){
        return array("
		     Backbone/*",
		     "CCVImportView",
		     "EvalImportView",
		     "GradImportView",
		     "GrantImportView",
		     "CourseFileImportView",
		     "TabView",
		     );
    }
    
    function getModels(){
        return array("CCVImportModel");
    }

    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser;
        if((new self)->userCanExecute($wgUser)){
            array_splice($toolbox['Tools']['links'], 0, 0, array(TabUtils::createToolboxLink("Import UoA Data", "$wgServer$wgScriptPath/index.php/Special:CCVImport")));
        }
        return true;
    }


}

?>
