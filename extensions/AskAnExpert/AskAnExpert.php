<?php

BackbonePage::register('AskAnExpert', 'AskAnExpert', 'network-tools', dirname(__FILE__));
UnknownAction::createAction('AskAnExpert::registerExpertEventAction');


class AskAnExpert extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    function userCanExecute($user){
        $me = Person::newFromWgUser();
        return $me->isLoggedIn();
    }
    
    
    function getTemplates(){
	global $wgOut;
	$me = Person::newFromWgUser();
	$firstName = str_replace("'", "&#39;", $me->getFirstName());
        $lastName = str_replace("'", "&#39;", $me->getLastName());
        $email = str_replace("'", "&#39;", $me->getEmail());
        $wgOut->addHTML("<script type='text/javascript'>
	    var userFirstName = \"".$firstName."\";
	    var userLastName = \"".$lastName."\";
            var useremail = \"".$email."\";
        </script>");

        return array('Backbone/*',
		     'expert_dashboard',
		     'expert_row',
		     'expert_edit',
		     'event_register',
		    );
    }
    
    function getViews(){
        return array('Backbone/*',
		     'ExpertDashboardView',
		     'ExpertRowView',
		     'ExpertEditView',
		     'EventRegisterView',
		    );
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createTab(&$tabs){
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
	if($wgUser->isLoggedIn()){

        }
        return true;
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath, $wgUser,$wgOut,$wgLang;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
        }
        if($me->isRoleAtLeast(HQP)){
        }
        return true;
    }


    static function registerExpertEventAction($action){
    global $config;
    if($action == 'registerExpertEventAction'){
        $me = Person::newFromWgUser();
        $uid = md5(uniqid(time()));
        $firstName = $_POST['firstname'];
        $lastName = $_POST['lastname'];
        $email = $_POST['email'];
        $subj = "Contact Us - {$_POST['topic']}";
        $msg = "<p></p><br />
                <b>User:</b> {$_POST['firstname']} {$_POST['lastname']} ({$email})";

        $eol = "\r\n";
        // Basic headers
        $header = "From: {$_POST['firstname']} {$_POST['lastname']} <{$_POST['email']}>".$eol;
        $header .= "Reply-To: {$_POST['email']}".$eol;
        $header .= "MIME-Version: 1.0".$eol;
        $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"".$eol;

        // Put everything else in $message
        $message = "--".$uid.$eol;
        $message .= "Content-Type: text/html; charset=ISO-8859-1".$eol;
        $message .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
        $message .= $msg.$eol.$eol;
        $message .= "--".$uid."--";

        mail("rjsdee@gmail.com", "[{$config->getValue('networkName')}] {$subj}", $message, $header);
        exit;
    }
    return true;
}
}

?>
