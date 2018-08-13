<?php

BackbonePage::register('DiversitySurvey', 'Diversity Survey', 'network-tools', dirname(__FILE__));

class DiversitySurvey extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        return $user->isLoggedIn();
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'diversity');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'DiversitySurveyView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }

}

?>
