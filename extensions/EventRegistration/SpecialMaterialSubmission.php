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
            $f = 1;
            foreach($_FILES as $file){
                if($file['name'] != ""){
                    $fileName = "{$_POST['name']}_Event{$_POST['event']}_File{$f}_{$file['name']}";
                    move_uploaded_file($file["tmp_name"], "extensions/EventRegistration/uploads/{$fileName}");
                    $_POST["misc"]["file{$f}"] = $fileName;
                    $_POST["misc"]["desc{$f}"] = $_POST["desc{$f}"];
                    $f++;
                }
            }
            $eventRegistration = new EventRegistration(array());
            $eventRegistration->eventId = $_POST['event'];
            $eventRegistration->type = "Material Submission";
            $eventRegistration->email = $_POST['email'];
            $eventRegistration->name = $_POST['name'];
            $eventRegistration->role = $_POST['role'];
            $eventRegistration->misc = @$_POST['misc'];
            $eventRegistration->create();
            $wgMessage->addSuccess("Thank you for submitting your materials");
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
            if($event->startDate >= date('Y-m-d', time() - 3600*24*30) && $event->getVisibility() == "Publish" && $event->isMaterialSubmissionEnabled()){
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

        $roles = array("Audience", "Keynote Speaker", "Host", "Presenter");
        $roleLabel = "Participant Role";
        $roleField = new SelectBox("role", "role", "Presenter", $roles);
        
        $instructions = "Please, upload here your material to be saved in our repository";
        $preamble = "";
        if($default->title == "Replaying Japan Conference"){
            $instructions = "Upload your conference video/slides/paper here. こちらに発表のビデオ・スライド・論文をアップロードして下さい。";
            $preamble = "<p>Presenters are expected to upload their presentation by August 2nd. You can upload any of the following:<br />
                            アップロードは、８月２日までに以下のいずれかの形態でお願いします。</p>
                        <p>1. Video: a short video of what would have been your full presentation<br />
                           ビデオ：発表に相当する短いビデオ </p>
                        <p>2. Slides: a slide deck that explains your presentation<br />
                           スライド：発表要旨を含むスライド</p>
                        <p>3. Draft paper: a written conference paper for people to read<br />
                           発表の草案：参加者が読むための原稿 <br />
                           You are welcome to upload up to four files for each submission accepted.<br />
                           一発表につき、４ファイルまで提出できます</p>
                        <p>Please name your files in the following fashion:<br />
                           提出されるファイルは、以下の形式でお願いします。</p>
                        <p>&lt;Last Name of Contact Presenter&gt;, &lt;Short Title&gt;, &lt;Format of Upload File&gt; (Eg. Rockwell, Moral Management, Paper.pdf or Rockwell, Moral Management of Game Companies, Slides.pptx)</p>
                        <p>Remember: Register for the conference here: <a href='https://forum.ai4society.ca/index.php/Special:SpecialEventRegistration?event=33'>https://forum.ai4society.ca/index.php/Special:SpecialEventRegistration?event=33</a></p>
                        <p>カンファレンスの参加登録はこちらからおこなって下さい。<a href='https://forum.ai4society.ca/index.php/Special:SpecialEventRegistration?event=33'>https://forum.ai4society.ca/index.php/Special:SpecialEventRegistration?event=33</a></p>";
            $roleLabel = "Are you a Grad Student?";
            $roles = array("Grad Student" => "Yes", 
                           "Not a Grad Student" => "No");
            $roleField = new VerticalRadioBox("role", "role", "Yes", $roles);
        }
        
        $linksField = new TextareaField("misc[Links]", "misc", "");
        
        $getStr = isset($_GET['event']) ? "?event={$_GET['event']}" : "";
        $banner1 = ($default->getImageUrl(4) != "") ? "<img style='max-height: 200px;width: 100%;object-fit: contain;object-position: left;' src='{$default->getImageUrl(4)}' />" : "";
        $banner2 = ($default->getImageUrl(5) != "") ? "<img style='max-width: 200px;height: 100%;object-fit: contain;object-position: top;' src='{$default->getImageUrl(5)}' />" : "";
        $maxFileSize = min((float)str_replace('M', '', ini_get('post_max_size')), (float)str_replace('M', '', ini_get('upload_max_filesize')));
        $wgOut->addHTML("<form action='{$wgServer}{$wgScriptPath}/index.php/Special:SpecialMaterialSubmission{$getStr}' method='post' onSubmit='return validate()' enctype='multipart/form-data'>
            <p>{$instructions}</p>
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
                            <td class='label'>{$roleLabel}</td>
                            <td class='value'>{$roleField->render()}</td>
                        </tr>
                    </table>
                    <h3>Please upload up to 4 files with your material here.</h3>
                    Please, use common formats like pdf, doc, mov, mp4, ppt, pptx, wav, mp3, etc.
                     <table class='wikitable' frame='box' rules='all'>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>File 1</td>
                            <td><input id='file1' type='file' name='drive1' /></td>
                            <td><input style='width:300px;' type='text' name='desc1' placeholder='Description...' /></td>
                        </tr>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>File 2</td>
                            <td><input id='file2' type='file' name='drive2' /></td>
                            <td><input style='width:300px;' type='text' name='desc2' placeholder='Description...' /></td>
                        </tr>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>File 3</td>
                            <td><input id='file3' type='file' name='drive3' /></td>
                            <td><input style='width:300px;' type='text' name='desc3' placeholder='Description...' /></td>
                        </tr>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>File 4</td>
                            <td><input id='file4' type='file' name='drive4' /></td>
                            <td><input style='width:300px;' type='text' name='desc4' placeholder='Description...' /></td>
                        </tr>
                    </table>
                    <b>NOTE: Total file limit is {$maxFileSize}MB, if you need more space please upload them to a server and share the link with us here:</b>
                    <h3>Link to your material (optional)</h3>
                    {$linksField->render()}<br />
                    <br />
                    <input type='submit' name='submit' value='Submit' />
                </div>
                <div>
                    {$banner2}
                </div>
            </div>
        </form>
        <script type='text/javascript'>
            function validate(){
                var limit = 1024*1024*{$maxFileSize};
                var file_size = 0;
                if(document.getElementById('file1').files[0] != undefined)
                    file_size += document.getElementById('file1').files[0].size;
                if(document.getElementById('file2').files[0] != undefined)
                    file_size += document.getElementById('file2').files[0].size;
                if(document.getElementById('file3').files[0] != undefined)
                    file_size += document.getElementById('file3').files[0].size;
                if(document.getElementById('file4').files[0] != undefined)
                    file_size += document.getElementById('file4').files[0].size;
                    
                if(file_size>=limit){
                    alert('Files exceed {$maxFileSize}MB');
                    return false;
                }
                return true;
            }
        </script>
");
    }

}

?>
