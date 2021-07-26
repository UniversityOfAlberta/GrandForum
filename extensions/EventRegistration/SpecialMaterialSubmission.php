<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialMaterialSubmission'] = 'SpecialMaterialSubmission'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['SpecialMaterialSubmission'] = $dir . 'SpecialMaterialSubmission.i18n.php';
$wgSpecialPageGroups['SpecialMaterialSubmission'] = 'network-tools';

class SpecialMaterialSubmission extends SpecialPage{

    function SpecialMaterialSubmission() {
        parent::__construct("SpecialMaterialSubmission", '', true);
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
            $eventRegistration->misc = @$_POST['misc'];
            $eventRegistration->create();
            $wgMessage->addSuccess("Thank you for registering");
            $event = EventPosting::newFromId($_POST['event']);
            if($event != null && $event->title != ""){
                redirect($event->getUrl());
            }
        }
        $getStr = isset($_GET['event']) ? "?event={$_GET['event']}" : "";
        redirect("{$wgServer}{$wgScriptPath}/index.php/Special:SpecialEventRegistration{$getStr}");
    }

    function execute($par){
        global $wgOut, $wgUser, $config, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if(isset($_POST['submit'])){
            $this->handleEdit();
        }
        $defaultEvent = "";
        $eventOptions = array();
        $event = EventPosting::newFromId(@$_GET['event']);
        if($event->getId() != 0 && date('Y-m-d') >= $event->getStartDate()){
            $wgOut->addHTML("This event has already past");
            return;
        }
        $default = $event;
        if($event != null && $event->title != "" && $event->getVisibility() == "Publish"){
            $eventOptions[$event->id] = $event->title;
            $defaultEvent = $event->id;
        }
        $events = EventPosting::getAllPostings();
        foreach($events as $event){
            if($event->startDate >= date('Y-m-d') && $event->getVisibility() == "Publish" && $event->isRegistrationEnabled()){
                $eventOptions[$event->id] = $event->title;
            }
        }
        $eventField = new SelectBox("event", "event", $defaultEvent, $eventOptions);
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

        $roles = array("Keynote Speaker", "Host", "Presenter");
        
        $roleField = new SelectBox("role", "role", "Presenter", $roles);
        
        $linksField = new TextareaField("misc[Links]", "misc", "");
        
        $getStr = isset($_GET['event']) ? "?event={$_GET['event']}" : "";
        $banner1 = ($default->getImageUrl(4) != "") ? "<img style='max-height: 200px;width: 100%;object-fit: contain;object-position: left;' src='{$default->getImageUrl(4)}' />" : "";
        $banner2 = ($default->getImageUrl(5) != "") ? "<img style='max-width: 200px;height: 100%;object-fit: contain;object-position: top;' src='{$default->getImageUrl(5)}' />" : "";
        $wgOut->addHTML("<form action='{$wgServer}{$wgScriptPath}/index.php/Special:SpecialEventRegistration{$getStr}' method='post' enctype='multipart/form-data'>
            <p>Please, upload here your material to be saved in our repository</p>
            <div style='display:flex;'>
                <div style='width:800px;margin-right:15px;'>
                    <div style='text-align:center;width:100%;'>{$banner1}</div>
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
                            <td>{$roleField->render()}</td>
                        </tr>
                    </table>
                    <h3>Please upload up to 4 files with your material here.</h3>
                    Please, use common formats like pdf, doc, mov, mp4, ppt, pptx, wav, mp3, etc.
                     <table class='wikitable' frame='box' rules='all'>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>File 1</td>
                            <td><input type='file' name='drive1' /></td>
                        </tr>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>File 2</td>
                            <td><input type='file' name='drive2' /></td>
                        </tr>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>File 3</td>
                            <td><input type='file' name='drive3' /></td>
                        </tr>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>File 4</td>
                            <td><input type='file' name='drive4' /></td>
                        </tr>
                    </table>
                    <b>NOTE: Total file limit is 750MB, if you need more space please upload them to a server and share the link with us here:</b>
                    <h3>Link to your material (optional)</h3>
                    {$linksField->render()}<br />
                    <br />
                    <input type='submit' name='submit' value='Submit' />
                </div>
                <div>
                    {$banner2}
                </div>
            </div>
        </form>");
    }

}

?>
