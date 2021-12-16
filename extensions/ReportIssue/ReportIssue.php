<?php

    $wgHooks['BeforePageDisplay'][] = 'reportIssue';
    UnknownAction::createAction('reportIssueAction');

    function reportIssue($wgOut, $skin){
        global $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        $wgOut->addScript("<link rel='stylesheet' type='text/css' href='{$wgServer}{$wgScriptPath}/extensions/ReportIssue/reportIssue.css?".filemtime(dirname(__FILE__)."/reportIssue.css")."' />");
        $wgOut->addScript("<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/ReportIssue/reportIssue.js?".filemtime(dirname(__FILE__)."/reportIssue.js")."' ></script>");
        
        $loggedIn = "";
        if(!$me->isLoggedIn()){
            $loggedIn = "<b>Email:</b> <input type='text' name='email' /><br />";
        }
        
        $wgOut->addHTML("<div title='Report Issue' id='reportIssueDialog' style='display:none;'>
            <p>If you are experiencing an issue on the current page, you can report it here.  Explain what the issue is and a report will be sent to {$config->getValue('supportEmail')}.  The following information will automatically be sent:</p>
            <ul>
                <li>User/Email (if logged in)</li>
                <li>Browser Information</li>
                <li>Url of Page</li>
                <li>Screenshot of page</li>
            </ul>
            {$loggedIn}
            <b>Additional Comments:</b>
            <textarea style='width:100%;height:100px;' id='additional_comments'></textarea>
        </div>");
        
        $wgOut->addHTML("<div title='Contact Us' id='contactUsDialog' style='display:none; width: 112px;'>
            {$loggedIn}
            <table>
                <tr>
                    <td align='right'><b>Topic:</b></td>
                    <td> 
                        <select id='topic' style='vertical-align:middle;'>
                            <option selected>Find an expert</option>
                            <option>Find a student</option>
                            <option>Other</option>
                        </select>
                    </td>
                </tr>
                <tr id='topic_other'>
                    <td align='right'><b>Specify:</b></td>
                    <td><input type='text' id='topicOther' /></td>
                </tr>
            </table>
            <b>Description:</b><br />
            <textarea style='width:100%;height:100px;' id='additional_comments'></textarea>
            <div id='fileSizeError' class='error' style='display:none;'>This file is too large, please choose a file smaller than 5MB</div>
            <b>Attachment:</b><br /><input type='file' /> (5MB max)
        </div>");
        return true;
    }
    
    function reportIssueAction($action){
        global $config;
        if($action == 'reportIssue'){
            $me = Person::newFromWgUser();
            $comments = nl2br($_POST['comments']);
            if(isset($_POST['img'])){
                $file = base64_decode(str_replace(' ', '+', str_replace("data:image/png;base64,", "", $_POST['img'])));
                $file = str_replace("data:image/png;base64,", "", $_POST['img']);
                $filename = "Screenshot.png";
                $file_size = strlen($file);
            }
            $uid = md5(uniqid(time()));
            $email = ($me->isLoggedIn()) ? $me->getEmail() : $_POST['email'];
            $msg = "";
            $subj = "";
            if(isset($_POST['img'])){
                $subj = "Report Issue";
                $msg = "<p>{$comments}</p><br />
                        <b>User:</b> {$me->getName()} ({$email})<br />
                        <b>Browser:</b> {$_POST['browser']}<br />
                        <b>Url:</b> <a href='{$_POST['url']}'>{$_POST['url']}</a>";
            }
            else{
                $subj = "Contact Us - {$_POST['topic']}";
                $msg = "<p>{$comments}</p><br />
                        <b>User:</b> {$me->getName()} ({$email})";
            }
            
            $eol = "\r\n";
            // Basic headers
            $header = "From: {$me->getName()} <{$me->getEmail()}>".$eol;
            $header .= "Reply-To: {$me->getEmail()}".$eol;
            $header .= "MIME-Version: 1.0".$eol;
            $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"".$eol;

            // Put everything else in $message
            $message = "--".$uid.$eol;
            $message .= "Content-Type: text/html; charset=ISO-8859-1".$eol;
            $message .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
            $message .= $msg.$eol.$eol;
            if(isset($_POST['img'])){
                $message .= "--".$uid.$eol;
                $message .= "Content-Type: application/octet-stream; name=\"".$filename."\"".$eol;
                $message .= "Content-Transfer-Encoding: base64".$eol;
                $message .= "Content-Disposition: attachment; filename=\"".$filename."\"".$eol.$eol;
                $message .= chunk_split($file).$eol.$eol;
            }
            if(isset($_POST['fileObj']) && $_POST['fileObj'] != ""){
                $fileObj = $_POST['fileObj'];
                $exploded = explode(",", $fileObj['data']);
                $message .= "--".$uid.$eol;
                $message .= "Content-Type: application/octet-stream; name=\"".$fileObj['filename']."\"".$eol;
                $message .= "Content-Transfer-Encoding: base64".$eol;
                $message .= "Content-Disposition: attachment; filename=\"".$fileObj['filename']."\"".$eol.$eol;
                $message .= @chunk_split($exploded[1]).$eol.$eol;
            }
            $message .= "--".$uid."--";
            
            mail($config->getValue('supportEmail'), "[{$config->getValue('networkName')}] {$subj}", $message, $header);
            exit;
        }
        return true;
    }

?>
