<?php

BackbonePage::register('JobPostingPage', 'Job Posting', 'network-tools', dirname(__FILE__));

$wgHooks['ToolboxLinks'][] = 'JobPostingPage::createToolboxLinks';

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
    
    function execute($par){
        global $wgOut;
        $wgOut->addHTML("<p>As of December 15, 2021, CS-Can|Info-Can will no longer use the Forum for uploading departmental job postings.</p>

                         <p>Postings can now be posted directly on the CS-Can|Info-Can website by clicking on <a href='https://cscan-infocan.ca/login/?redirect_to=/post-a-job/'>https://cscan-infocan.ca/login/?redirect_to=/post-a-job/</a></p>

                         <p>This will take you to a login page to enter your email address and the password you set up when you activated your account on the CS-Can|Info-Can membership page.</p>

                         <p>If you haven’t yet activated your account, please contact <a href='mailto:adele_newton@cscan-infocan.ca'>adele_newton@cscan-infocan.ca</a></p>");
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $toolbox['Postings']['links'][] = TabUtils::createToolboxLink("Job Postings", "$wgServer$wgScriptPath/index.php/Special:JobPostingPage");
        }
        return true;
    }

}

?>
