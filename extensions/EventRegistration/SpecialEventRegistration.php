<?php

require_once("EventRegistration.php");

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialEventRegistration'] = 'SpecialEventRegistration'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['SpecialEventRegistration'] = $dir . 'SpecialEventRegistration.i18n.php';
$wgSpecialPageGroups['SpecialEventRegistration'] = 'network-tools';

class SpecialEventRegistration extends SpecialPage{

    function SpecialEventRegistration() {
        parent::__construct("SpecialEventRegistration", '', true);
    }
    
    function handleEdit(){
        global $wgServer, $wgScriptPath, $wgMessage;
        if(!isset($_POST['event']) || trim($_POST['event']) == ""){
            $wgMessage->addError("You must select and Event");
        }
        else if(!isset($_POST['email']) || trim($_POST['email']) == ""){
            $wgMessage->addError("You must provide your email address");
        }
        else if(!isset($_POST['name']) || trim($_POST['name']) == ""){
            $wgMessage->addError("You must provide your name");
        }
        else if(!isset($_POST['role']) || trim($_POST['name']) == ""){
            $wgMessage->addError("You must provide a role");
        }
        else{
            // Add Event Registration
            $eventRegistration = new EventRegistration(array());
            $eventRegistration->eventId = $_POST['event'];
            $eventRegistration->email = $_POST['email'];
            $eventRegistration->name = $_POST['name'];
            $eventRegistration->role = $_POST['role'];
            $eventRegistration->receiveInformation = isset($_POST['receive_information']);
            $eventRegistration->joinNewsletter = isset($_POST['join_newsletter']);
            $eventRegistration->createProfile = isset($_POST['create_profile']);
            $eventRegistration->similarEvents = isset($_POST['similar_events']);
            $eventRegistration->create();
            $wgMessage->addSuccess("Thank you for registering");
        }
        redirect("{$wgServer}{$wgScriptPath}/index.php/Special:SpecialEventRegistration");
    }

    function execute($par){
        global $wgOut, $wgUser, $config, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if(isset($_POST['submit'])){
            $this->handleEdit();
        }
        $eventOptions = array();
        $events = EventPosting::getAllPostings();
        foreach($events as $event){
            if($event->startDate >= date('Y-m-d')){
                $eventOptions[$event->id] = $event->title;
            }
        }
        $eventField = new SelectBox("event", "event", "", $eventOptions);
        $eventField->attr('required', 'required');
        $eventField->forceKey = true;
        
        $email = ($me->isLoggedIn()) ? $me->getEmail() : 
                ((isset($_SERVER['uid']) && isset($_SERVER['sn'])) ? $_SERVER['uid']."@ualberta.ca" : "");
        $emailField = new TextField("email", "email", $email);
        $emailField->attr('required', 'required');
        
        $name = ($me->isLoggedIn()) ? $me->getNameForForms() : 
                ((isset($_SERVER['givenName']) && isset($_SERVER['sn'])) ? ucfirst($_SERVER['givenName'])." ".ucfirst($_SERVER['sn']) : "");
        $nameField = new TextField("name", "name", $name);
        $nameField->attr('required', 'required');
        
        $preamble = "";
        if($config->getValue('networkName')){
            $preamble = "<p>AI4Society holds a variety of events such as dialogues, workshops, symposia, etc. Please select the upcoming event you want to attend, and fill out the information required. You will receive the login information via email.</p>";
        }
        
        $wgOut->addHTML("<form action='{$wgServer}{$wgScriptPath}/index.php/Special:SpecialEventRegistration' method='post'>
            {$preamble}
            <h3>Participant information</h3>
            <table class='wikitable' frame='box' rules='all'>
                <tr>
                    <td class='label' style='vertical-align: middle;'>Event</td>
                    <td>{$eventField->render()}</td>
                </tr>
                <tr>
                    <td class='label' style='vertical-align: middle;'>Your Email</td>
                    <td>{$emailField->render()}</td>
                </tr>
                <tr>
                    <td class='label' style='vertical-align: middle;'>Your Name</td>
                    <td>{$nameField->render()}</td>
                </tr>
                <tr>
                    <td class='label' style='vertical-align: middle;'>Participant Role</td>
                    <td><select name='role' required='required'>
                        <option>Presenter</option>
                        <option>Host</option>
                        <option>Audience</option>
                    </select></td>
                </tr>
            </table>
            <h3>Other information</h3>
            <table class='wikitable' frame='box' rules='all'>
                <tr>
                    <td><input type='checkbox' name='receive_information' value='1' checked /></td>
                    <td style='max-width:600px;'>Receive post-event information: for some events we release video recordings, text documents, and similar documentation. If this box is checked you will receive links to them when ready.</td>
                </tr>
                <tr>
                    <td><input type='checkbox' name='join_newsletter' value='1' /></td>
                    <td>Join AI4Society mailing list to receive our by-weekly newsletter</td>
                </tr>
                <tr>
                    <td><input type='checkbox' name='create_profile' value='1' /></td>
                    <td>Become an AI4Society Member</td>
                </tr>
                <tr>
                    <td><input type='checkbox' name='similar_events' value='1' /></td>
                    <td>Inform me about similar events</td>
                </tr>
            </table>
            <input type='submit' name='submit' value='Submit' />
        </form>");
    }

}

?>
