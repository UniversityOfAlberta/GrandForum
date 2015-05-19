<?php

$wgHooks['ToolboxLinks'][] = 'ManageProducts::createToolboxLinks';
BackbonePage::register('ManageProducts', 'Manage '.Inflect::pluralize($config->getValue("productsTerm")), 'network-tools', dirname(__FILE__));

class ManageProducts extends BackbonePage {
    
    function isListed(){
        return true;
    }
    
    function userCanExecute($user){
        return $user->isLoggedIn();
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
        return array('Backbone/*');
    }
    
    static function createToolboxLinks(&$toolbox){
	    global $wgServer, $wgScriptPath, $config;
	    $toolbox['Products']['links'][] = TabUtils::createToolboxLink("Manage ".Inflect::pluralize($config->getValue("productsTerm")), 
	                                                                  "$wgServer$wgScriptPath/index.php/Special:ManageProducts");
	    return true;
	}

}

?>
