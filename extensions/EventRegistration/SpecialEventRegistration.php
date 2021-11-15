<?php

require_once("EventRegistration.php");
require_once("SpecialMaterialSubmission.php");
require_once("SpecialEventRegistrationTable.php");

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
        else if(isset($_FILES["misc"]) && $_FILES["misc"]["size"] > 1024*1024*5){
            $wgMessage->addError("The file must not be over 5MB");
        }
        else if(isset($_FILES["misc_doc"]) && $_FILES["misc_doc"]["size"] > 1024*1024*5){
            $wgMessage->addError("The file must not be over 5MB");
        }
        else{
            // Add Event Registration
            $eventRegistration = new EventRegistration(array());
            $eventRegistration->eventId = $_POST['event'];
            $eventRegistration->type = "Event Registration";
            $eventRegistration->email = $_POST['email'];
            $eventRegistration->name = $_POST['name'];
            $eventRegistration->role = $_POST['role'];
            $eventRegistration->webpage = @$_POST['webpage'];
            $eventRegistration->twitter = @$_POST['twitter'];
            $eventRegistration->receiveInformation = isset($_POST['receive_information']);
            $eventRegistration->joinNewsletter = isset($_POST['join_newsletter']);
            $eventRegistration->createProfile = isset($_POST['create_profile']);
            $eventRegistration->similarEvents = isset($_POST['similar_events']);
            $eventRegistration->misc = @$_POST['misc'];
            if(isset($_FILES["misc"])){
                $eventRegistration->misc["PDF"] = base64_encode(file_get_contents($_FILES["misc"]["tmp_name"]));
            }
            if(isset($_FILES["misc_doc"])){
                $eventRegistration->misc["DOC"] = base64_encode(file_get_contents($_FILES["misc_doc"]["tmp_name"]));
            }
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
        if($event->getId() != 0 && date('Y-m-d', time() - 3600*24*30) >= $event->getStartDate()){
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
            if($event->startDate >= date('Y-m-d', time() - 3600*24*30) && $event->getVisibility() == "Publish" && $event->isRegistrationEnabled()){
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
        
        $twitter = ($me->isLoggedIn()) ? $me->getTwitter() : "";
        $twitterField = new TextField("twitter", "twitter", $twitter);
        
        $webpage = ($me->isLoggedIn()) ? $me->getWebsite() : "";
        $webpageField = new TextField("webpage", "webpage", $webpage);

        $roles = array("Audience", "Presenter", "Host");
        $misc = "";
        if($default->title == "Energy Hackathon 2021 - APIC"){
            $miscField = new TextareaField("misc[Programming]", "misc", "");
            $misc = "<h3>Which programming technologies or tools are you familiar with or would like to learn?</h3>
                     {$miscField->render()}";
            $roles = array("Audience", "Host/Judge");
        }
        else if($default->title == "Reimagining Public Spaces and Built Environments in the Post-pandemic World"){
            $misc = "<h3>Please upload ONE Word document by <b>December 1st, 2021</b>.  Be sure to include:</h3>
                     <ul>
                        <li>Paper title</li>
                        <li>Name of author/s, abstract (250 words)</li>
                        <li>5 keywords</li>
                        <li>Body of the text (3500 words)</li>
                        <li>References</li>
                        <li>Short bio (100 words maximum)</li>
                     </ul>
                     <input type='file' name='misc_doc' accept='.doc,.docx' />";
            $roles = array("Audience", "Presenter", "Host", "Author", "Co-author", "Scientific Committee");
        }
        else if($default->title == "Ethical Data and AI - Salon #2"){
            $misc = "<h3>How do you plan to attend?</h3>
                     <select name='misc[Attend]'>
                        <option>Online</option>
                        <option>In person</option>
                     </select>";
        }
        else if($default->title == "Digital Law Forum 2022 - Myths & Reality, Artificial Intelligence and the Law"){
            $roles = array("Industry Partner", "Researcher");
        }
        
        $roleField = new SelectBox("role", "role", "Audience", $roles);
        
        $preamble = "";
        if($default->title == "Replaying Japan Conference"){
            $preamble = "<p>Register for Replaying Japan 2021 Here!<br />
                           Replaying Japan 2021の参加登録はこちらから行って下さい。</p>
                        <p>Registration is FREE. Register so we can send you online participation information. You won’t get the links if you don’t register.<br />
                           参加費は無料です。オンライン参加に必要なリンク情報をお送りするため、登録をお願いいたします。登録されない場合は、リンク情報が送信されません。</p>
                        <p>To find out more about the conference, including scheduling, go to:<br />
                           当学会の詳細並びにスケジュール等はこちらをご覧下さい。<br />
                           <a href='http://replaying.jp' target='_blank'>http://replaying.jp</a></p>
                        <p>Replaying Japan 2021 is hosted by the University of Alberta<br />
                           ２０２１年度Replaying Japanはアルバータ大学が主催です。</p>
                        <p>Questions? Send an email to <a href='mailto:ai4society@ualberta.ca'>ai4society@ualberta.ca</a><br />
                           その他質問事項がありましたら、 <a href='mailto:ai4society@ualberta.ca'>ai4society@ualberta.ca</a>迄メールして下さい。</p>";
        }
        
        $getStr = isset($_GET['event']) ? "?event={$_GET['event']}" : "";
        $banner1 = ($default->getImageUrl(4) != "") ? "<img style='max-height: 200px;width: 100%;object-fit: contain;object-position: left;' src='{$default->getImageUrl(4)}' />" : "";
        $banner2 = ($default->getImageUrl(5) != "") ? "<img style='max-width: 200px;height: 100%;object-fit: contain;object-position: top;' src='{$default->getImageUrl(5)}' />" : "";
        $wgOut->addHTML("<form action='{$wgServer}{$wgScriptPath}/index.php/Special:SpecialEventRegistration{$getStr}' method='post' enctype='multipart/form-data'>
            <p>AI4Society holds a variety of events such as dialogues, workshops, symposia, etc. Please select the upcoming event you want to attend, and fill out the information required. You will receive the login information via email.</p>
            <div style='display:flex;'>
                <div style='width:800px;margin-right:15px;'>
                    <div style='text-align:center;width:100%;'>{$banner1}</div>
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
                            <td>{$roleField->render()}</td>
                        </tr>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>Webpage</td>
                            <td>{$webpageField->render()}</td>
                        </tr>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>Twitter</td>
                            <td>{$twitterField->render()}</td>
                        </tr>
                    </table>
                    {$misc}
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
                </div>
                <div>
                    {$banner2}
                </div>
            </div>
        </form>");
    }

}

?>
