<?php

BackbonePage::register('BibliographyPage', 'BibliographyPage', 'network-tools', dirname(__FILE__));

class BibliographyPage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'Products/product',
                     'bibliography',
                     'bibliography_edit');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'Products/ProductView',
                     'BibliographyView',
                     'BibliographyEditView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }

}

?>
