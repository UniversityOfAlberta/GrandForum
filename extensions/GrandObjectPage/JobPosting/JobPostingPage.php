<?php

BackbonePage::register('JobPostingPage', 'Job Posting', 'network-tools', dirname(__FILE__));

class JobPostingPage extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'jobpostings',
                     'jobposting',
                     'jobposting_edit');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'JobPostingsView',
                     'JobPostingView',
                     'JobPostingEditView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }

}

?>
