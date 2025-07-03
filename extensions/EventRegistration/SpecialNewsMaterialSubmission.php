<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialNewsMaterialSubmission'] = 'SpecialNewsMaterialSubmission'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['SpecialNewsMaterialSubmission'] = $dir . 'SpecialNewsMaterialSubmission.i18n.php';
$wgSpecialPageGroups['SpecialNewsMaterialSubmission'] = 'network-tools';

class SpecialNewsMaterialSubmission extends SpecialPage{

    function __construct() {
        parent::__construct("SpecialNewsMaterialSubmission", '', true);
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
        else{
            // Add Event Registration
            $f = 1;
            foreach($_FILES as $file){
                if($file['name'] != ""){
                    $fileName = "{$_POST['name']}_Event{$_POST['event']}_File{$f}_{$file['name']}";
                    move_uploaded_file($file["tmp_name"], "extensions/EventRegistration/uploads/{$fileName}");
                    $_POST["misc"]["file{$f}"] = $fileName;
                    $f++;
                }
            }
            $eventRegistration = new EventRegistration(array());
            $eventRegistration->eventId = $_POST['event'];
            $eventRegistration->type = "News Material Submission";
            $eventRegistration->email = $_POST['email'];
            $eventRegistration->name = $_POST['name'];
            $eventRegistration->role = @$_POST['role'];
            $eventRegistration->misc = @$_POST['misc'];
            $eventRegistration->create();
            $wgMessage->addSuccess("Thank you for submitting your materials");
            $news = NewsPosting::newFromId($_POST['event']);
            if($news != null && $news->title != ""){
                redirect($news->getUrl());
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
        $defaultNews = "";
        $newsOptions = array();
        $news = NewsPosting::newFromId(@$_GET['event']);
        $default = $news;
        if($news != null && $news->title != "" && $news->getVisibility() == "Publish"){
            $newsOptions[$news->id] = $news->title;
            $defaultNews = $news->id;
        }
        $newses = NewsPosting::getAllPostings();
        foreach($newses as $news){
            if($news->getVisibility() == "Publish" && $news->isMaterialSubmissionEnabled()){
                $newsOptions[$news->id] = $news->title;
            }
        }
        $newsField = new SelectBox("event", "event", $defaultNews, $newsOptions);
        $newsField->attr('required', 'required');
        $newsField->forceKey = true;
        
        $email = ($me->isLoggedIn()) ? $me->getEmail() : 
                ((isset($_SERVER['uid']) && isset($_SERVER['sn'])) ? $_SERVER['uid']."@ualberta.ca" : "");
        $emailField = new TextField("email", "email", $email);
        $emailField->attr('required', 'required');
        
        $name = ($me->isLoggedIn()) ? $me->getNameForForms() : 
                ((isset($_SERVER['givenName']) && isset($_SERVER['sn'])) ? ucfirst($_SERVER['givenName'])." ".ucfirst($_SERVER['sn']) : "");
        $nameField = new TextField("name", "name", $name);
        $nameField->attr('required', 'required');
        
        $instructions = "Please, upload here your material to be saved in our repository";
        $preamble = "";
        
        $linksField = new TextareaField("misc[Links]", "misc", "");
        
        $getStr = isset($_GET['event']) ? "?event={$_GET['event']}" : "";
        $banner1 = ($default->getImageUrl(4) != "") ? "<img style='max-height: 200px;width: 100%;object-fit: contain;object-position: left;' src='{$default->getImageUrl(4)}' />" : "";
        $banner2 = ($default->getImageUrl(5) != "") ? "<img style='max-width: 200px;height: 100%;object-fit: contain;object-position: top;' src='{$default->getImageUrl(5)}' />" : "";
        $maxFileSize = min((float)str_replace('M', '', ini_get('post_max_size')), (float)str_replace('M', '', ini_get('upload_max_filesize')));
        $wgOut->addHTML("<form action='{$wgServer}{$wgScriptPath}/index.php/Special:SpecialNewsMaterialSubmission{$getStr}' method='post' onSubmit='return validate()' enctype='multipart/form-data'>
            <p>{$instructions}</p>
            <div style='display:flex;'>
                <div style='width:800px;margin-right:15px;'>
                    <div style='text-align:center;width:100%;'>{$banner1}</div>
                    {$preamble}
                    <h3>Participant information</h3>
                    <table class='wikitable' frame='box' rules='all'>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>News</td>
                            <td>{$newsField->render()}</td>
                        </tr>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>Your Email</td>
                            <td>{$emailField->render()}</td>
                        </tr>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>Your Name</td>
                            <td>{$nameField->render()}</td>
                        </tr>
                    </table>
                    <h3>Please upload up to 4 files with your material here.</h3>
                    Please, use common formats like pdf, doc, mov, mp4, ppt, pptx, wav, mp3, etc.
                     <table class='wikitable' frame='box' rules='all'>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>File 1</td>
                            <td><input id='file1' type='file' name='drive1' /></td>
                        </tr>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>File 2</td>
                            <td><input id='file2' type='file' name='drive2' /></td>
                        </tr>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>File 3</td>
                            <td><input id='file3' type='file' name='drive3' /></td>
                        </tr>
                        <tr>
                            <td class='label' style='vertical-align: middle;'>File 4</td>
                            <td><input id='file4' type='file' name='drive4' /></td>
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
