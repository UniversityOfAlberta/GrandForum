<?php

require_once("EventRegistration.php");
require_once("SpecialMaterialSubmission.php");
require_once("SpecialNewsMaterialSubmission.php");
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
        global $wgServer, $wgScriptPath, $wgMessage, $config;
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
            // Now send email
            $to = $_POST['email'];
            $subject = "Registration Confirmed - ".substr($event->getTitle(), 0, 85);
            $from = $config->getValue('supportEmail');
            $message = "";
            if($event->getImageUrl(4) != ""){
                $message .= "<div style='text-align:center;width:100%;'><img style='max-height: 200px;width: 100%;object-fit: contain;object-position: left;' src='{$event->getImageUrl(4)}'></div>";
            }
            $message .= "<p>Dear {$_POST['name']},</p>
                        <p>Your registration has been confirmed to the following event: <a href='{$event->getUrl()}'>{$event->getTitle()}</a></p>";
            if($event->getArticleLink() != ""){
                $link = (substr($event->getArticleLink(), 0, 4) == "http") ? "<a href='{$event->getArticleLink()}'>{$event->getArticleLink()}</a>" : $event->getArticleLink();
                $message .= "<p>Please join us following dates and time shown on the aforementioned event using this link: {$link}</p>";
            }
            $message .= "<p>Please save this email for future reference, we look forward to see you.</p>";
            $message .= "<p>Contact <a href='mailto:ai4s@ualberta.ca'>ai4s@ualberta.ca</a> if you have any questions.</p>";
            if($event->getImageUrl(1) != ""){
                $message .= "<div style='text-align:center;width:100%;'><img style='max-height: 200px;width: 100%;object-fit: contain;object-position: left;' src='{$event->getImageUrl(1)}'></div>";
            }
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: '.$from."\r\n".
                        'Reply-To: '.$from."\r\n" .
                        'X-Mailer: PHP/' . phpversion();
            mail($to, $subject, $message, $headers);
            if($event != null && $event->title != ""){
                redirect($event->getUrl());
            }
        }
        $getStr = isset($_GET['event']) ? "?event={$_GET['event']}" : "";
        redirect("{$wgServer}{$wgScriptPath}/index.php/Special:SpecialEventRegistration{$getStr}");
    }

    function execute($par){
        global $wgOut, $wgTitle, $wgUser, $config, $wgServer, $wgScriptPath;
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
            $wgOut->setPageTitle("Event Registration: {$event->title}");
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
        
        $affiliation = "";
        
        $twitter = ($me->isLoggedIn()) ? $me->getTwitter() : "";
        $twitterField = new TextField("twitter", "twitter", $twitter);
        
        $webpage = ($me->isLoggedIn()) ? $me->getWebsite() : "";
        $webpageField = new TextField("webpage", "webpage", $webpage);

        $roles = array("Audience", "Keynote Speaker", "Host", "Presenter");
        $defaultRole = "Audience";
        $misc = "";
        $roleLabel = "Participant Role";
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
        else if($default->title == "Ethical Data and AI - Salon #2" ||
                $default->title == "Ethical Data and AI - Salon #3" ||
                $default->title == "Ethical Data and AI - Salon #4" ||
                $default->title == "Ethical Data and AI - Salon #5"){
            $misc = "<h3>How do you plan to attend?</h3>
                     <select name='misc[Attend]' required>
                        <option></option>
                        <option>Online</option>
                        <option>In person</option>
                     </select>";
        }
        else if($default->title == "AI in Construction - Academia and Industry, Meet and Greet Event"){
            $roles = array("Industry Partner", "Researcher");
            $defaultRole = "Industry Partner";
        }
        else if($default->title == "AI4Society Reverse EXPO"){
            $roles = array("Audience");
        }
        else if($default->title == "Energy Hackathon 2022 - APIC"){
            $roles = array("Participant", "Judge", "Host");
            $defaultRole = "Participant";
        }
        else if($default->title == "Reimagining Architecture and Urbanism in the Post-Pandemic World through Illustration"){
            $roleLabel = "Theme/Category of the competition";
            $roles = array("Public Space and Urban Built Environment",
                           "Tactical urbanism and Temporality",
                           "Smart Cities and Artificial Intelligence",
                           "Designing Built Environments and Hybrid Remote Space",
                           "(in)formal Public Space",
                           "Engaging Community and Participation",
                           "Connection with Nature for Mental Health and Wellness",
                           "Future of Post-Pandemic Public Space and Disaster Preparedness");
            $defaultRole = "Public Space and Urban Built Environment";
            
            $miscField = new TextField("misc[Affiliation]", "misc", "");
            $affiliation = "<tr>
                                <td class='label' style='vertical-align: middle;'>Your Affiliation</td>
                                <td>{$miscField->render()}</td>
                            </tr>";
        }
        
        $roleField = new SelectBox("role", "role", $defaultRole, $roles);
        
        $prepreamble = "<p>AI4Society holds a variety of events such as dialogues, workshops, symposia, etc. Please select the upcoming event you want to attend, and fill out the information required. You will receive the login information via email.</p>";
        $preamble = "";
        $showOther = "style='display:block;'";
        if(trim($default->title) == "Replaying Japan Conference"){
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
        else if(trim($default->title) == "3rd AI4IA Conference"){
            $showOther = "style='display:none;'";
            $prepreamble = "<div style='font-size: 16px;'><p>The UNESCO Information For All Programme (IFAP) Working Group on Information Accessibility (WGIA), is hosting it's second online one-day conference on 28 September 2022. This event will be hosted in collaboration with the Kule Institute for Advanced Studies (KIAS) and AI for Society (AI4S), both at University of Alberta, Canada, the Centre for New Economic Diplomacy (CNED) in ORF, India and the Broadcasting Commission of Jamaica. It is being organised under the auspices of the UNESCO Cluster Office for the Caribbean, Kingston, Jamaica and the UNESCO Regional Office for Southern Africa, Harare, Zimbabwe.</p>

                        <p>AI can be very beneficial to society but if abused it can also be very harmful. The AI4IA Conference, therefore, raises a range of issues, including the relationship between Artificial Intelligence (AI) and Law, AI and Ethics, media and our right to know, creativity and innovation. It is necessary to understand how AI can be made inclusive, thereby enabling the widest cross-section of society.</p>
                         
                        <p>This event provides a platform for open discourse involving participants from academia, civil society, private sector and government.</p>
                        
                        <p><b>Organizing Committee</b><br />
                            Cordel Green, Samridhi Arora Kalra, Geoffrey Rockwell, Nicolás Arnáez, Erica Simmons, Soniya Mukhedkar, Trisha Ray, Maria Dolores Souza, Andrea Millwood Hargrave , Andrew J Haire, David Soutar.</p>

                        <p><b>Program</b><br />
                            The AI4IA conference is an on-demand conference with live sessions on 28 September 2022.</p>

                        <p>On-demand viewing of the conference line-up will be available from 00:00 GMT (+0) from 26 September until 28 September 2022.</p>
                        
                        <p>A live opening session will be held on 28 September 2022 from 13:00 GMT/08:00 EST on ZOOM.<br />
                           There will also be Live interactive sessions with the speakers on 28 September 2022 during the hours from 08:00 -10:00 (GMT) and 16:00-18:00 (GMT).<br />
                           If you have never used the Gather.town platform before, please review the user guide here. We look forward to seeing everyone!
                        </p></div>
                        <script type='text/javascript'>
                            $('#sideToggle').html('&gt;');
                            $('#side').css('left', '-200px');
	                        $('#outerHeader').css('left', '-3px');
	                        $('#bodyContent').css('left', '-3px');
	                        sideToggled = 'in';
                            $(document).ready(function(){
                                $('#banner2 img').css('max-width', '275px');
                                $('#webpage').hide();
                                $('#twitter').hide();
                            });
                        </script>";
        }
        $eventShow = ($defaultEvent != 0) ? "style='display:none;'" : "";
        $getStr = isset($_GET['event']) ? "?event={$_GET['event']}" : "";
        $banner1 = ($default->getImageUrl(4) != "") ? "<img style='max-height: 200px;width: 100%;object-fit: contain;object-position: left;' src='{$default->getImageUrl(4)}' />" : "";
        $banner2 = ($default->getImageUrl(5) != "") ? "<img style='max-width: 200px;height: 100%;object-fit: contain;object-position: top;' src='{$default->getImageUrl(5)}' />" : "";
        $wgOut->addHTML("<form action='{$wgServer}{$wgScriptPath}/index.php/Special:SpecialEventRegistration{$getStr}' method='post' enctype='multipart/form-data'>
            {$prepreamble}
            <div style='display:flex;'>
                <div style='width:800px;margin-right:15px;'>
                    <div id='banner1' style='text-align:center;width:100%;'>{$banner1}</div>
                    {$preamble}
                    <h3>Participant information</h3>
                    <table class='wikitable' frame='box' rules='all'>
                        <tr {$eventShow}>
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
                        {$affiliation}
                        <tr>
                            <td class='label' style='vertical-align: middle;'>{$roleLabel}</td>
                            <td>{$roleField->render()}</td>
                        </tr>
                        <tr id='webpage'>
                            <td class='label' style='vertical-align: middle;'>Webpage</td>
                            <td>{$webpageField->render()}</td>
                        </tr>
                        <tr id='twitter'>
                            <td class='label' style='vertical-align: middle;'>Twitter</td>
                            <td>{$twitterField->render()}</td>
                        </tr>
                    </table>
                    {$misc}
                    <div><div {$showOther}>
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
                    </div></div>
                    <input type='submit' name='submit' value='Submit' style='margin-top: 1em;' />
                </div>
                <div id='banner2'>
                    {$banner2}
                </div>
            </div>
        </form>");
    }

}

?>
