<?php

BackbonePage::register('AskAnExpert', 'AskAnExpert', 'network-tools', dirname(__FILE__));
UnknownAction::createAction('AskAnExpert::registerExpertEventAction');

$wgHooks['TopLevelTabs'][] = 'AskAnExpert::createTab';

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
        $phone = str_replace("'", "&#39;", $me->getPhoneNumber());
        $wgOut->addHTML("<script type='text/javascript'>
                            var userFirstName = \"".$firstName."\";
                            var userLastName = \"".$lastName."\";
                            var useremail = \"".$email."\";
                            var userphone = \"".$phone."\";
        </script>");

        return array('Backbone/*',
                     'expert_dashboard',
                     'expert_row',
                     'expert_edit',
                     'expert_details',
                     'event_register');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'ExpertDashboardView',
                     'ExpertRowView',
                     'ExpertEditView',
                     'ExpertDetailsView',
                     'EventRegisterView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            if(AVOIDDashboard::checkAllSubmissions($wgUser->getId())){
                $selected = @($wgTitle->getText() == "AskAnExpert") ? "selected" : false;
                $GLOBALS['tabs']['AskAnExpert'] = TabUtils::createTab("<en>Ask an Expert</en><fr>Événements</fr>", "{$wgServer}{$wgScriptPath}/index.php/Special:AskAnExpert", $selected);
            }
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
            $phone = $_POST['phone'];
            $question = $_POST['question'];
            if($question == "No Question"){
                $subj = "Ask An Expert - {$_POST['topic']}";
            }
            else{
                $subj = "Ask An Expert - {$_POST['topic']}";
            }
            $msg = "<p></p><br />
                    <b>Firstname:</b>{$_POST['firstname']}<br /><b>Lastname</b>: {$_POST['lastname']} <br /><b>Email:</b>({$email})<br /><b>Phone:</b>{$_POST['phone']}
                    <br />";
            if($question != "No Question"){
                $msg .= "<b>Question:</b>{$question}";
            }

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

            mail($config->getValue('supportEmail'), "[{$config->getValue('networkName')}] {$subj}", $message, $header);
            exit;
        }
        return true;
    }
}

?>
