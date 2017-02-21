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
                     'bibliography',
                     'bibliography_edit');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'BibliographyView',
                     'BibliographyEditView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }

}

?>
