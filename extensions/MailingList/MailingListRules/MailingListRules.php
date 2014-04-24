<?php

BackbonePage::register('MailingListRules', 'MailingListRules', 'network-tools', dirname(__FILE__));

class MailingListRules extends BackbonePage {
    
    function userCanExecute($user){
        return true;
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'mailing_list_rules',
                     'list_select',
                     'list_rules');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'MailingListRulesView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }

}

?>
