<?php

$wgHooks['ToolboxLinks'][] = 'ManageProducts::createToolboxLinks';
BackbonePage::register('ManageProducts', 'Manage '.Inflect::pluralize($config->getValue("productsTerm")), 'network-tools', dirname(__FILE__));

class ManageProducts extends BackbonePage {
    
    function isListed(){
        return true;
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return $person->isRoleAtLeast(INACTIVE);
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'Products/*',
                     'manage_products',
                     'manage_products_row',
                     'manage_products_other_popup',
                     'manage_products_projects_popup',
                     'duplicates_dialog');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'Products/*',
                     'ManageProductsView',
                     'ManageProductsRowView',
                     'DuplicatesDialogView');
    }
    
    function getModels(){
        global $wgOut;
        $students = array();
        $studentNames = array();
        $person = Person::newFromWgUser();
        foreach($person->getHQP(true) as $hqp){
            $students[] = $hqp->getId();
            $studentNames[] = $hqp->getName();
        }
        $wgOut->addScript("<script type='text/javascript'>
            var students = ".json_encode($students).";
            var studentNames = ".json_encode($studentNames).";
        </script>");
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
	    global $wgServer, $wgScriptPath, $config, $wgUser;
	    if(ManageProducts::userCanExecute($wgUser)){
	        $toolbox['Products']['links'][] = TabUtils::createToolboxLink("Manage ".Inflect::pluralize($config->getValue("productsTerm")), 
	                                                                      "$wgServer$wgScriptPath/index.php/Special:ManageProducts");
	    }
	    return true;
	}

}

?>
