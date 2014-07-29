<?php

$wgHooks['ToolboxLinks'][] = 'ManageProducts::createToolboxLinks';

BackbonePage::register('ManageProducts', 'Manage Products', 'network-tools', dirname(__FILE__));

class ManageProducts extends BackbonePage {
    
    function userCanExecute($user){
        return true;
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
    
    static function createToolboxLinks($toolbox){
	    global $wgServer, $wgScriptPath;
	    $toolbox['Products']['links'][] = TabUtils::createToolboxLink("Manage Products", "$wgServer$wgScriptPath/index.php/Special:ManageProducts");
	    return true;
	}

}

?>
