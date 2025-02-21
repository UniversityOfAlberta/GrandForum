<?php

    $wgHooks['BeforePageDisplay'][] = 'reportIssue';
    UnknownAction::createAction('reportIssueAction');

    function reportIssue($wgOut, $skin){
        global $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        
        $wgOut->addScript("<link rel='stylesheet' type='text/css' href='{$wgServer}{$wgScriptPath}/extensions/ReportIssue/reportIssue.css' />");
        $wgOut->addScript("<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/ReportIssue/reportIssue.js'></script>");
        
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
            <b>Additional Comments:</b><br />
            <small>Include names of HQP, titles of publications, grants etc. in the description if applicable.</small>
            <textarea style='width:100%;height:100px;' id='additional_comments'></textarea>
        </div>");
        return true;
    }
    
    function reportIssueAction($action){
        global $config;
        if($action == 'reportIssue'){
            $me = Person::newFromWgUser();
            $comments = nl2br($_POST['comments']);
            $file = base64_decode(str_replace(' ', '+', str_replace("data:image/png;base64,", "", $_POST['img'])));
            $file = str_replace("data:image/png;base64,", "", $_POST['img']);
            $filename = "Screenshot.png";
            $file_size = strlen($file);
            $uid = md5(uniqid(time()));
            $email = ($me->isLoggedIn()) ? $me->getEmail() : $_POST['email'];
            $msg = "<p>{$comments}</p><br />
                    <b>User:</b> {$me->getName()} ({$email})<br />
                    <b>Browser:</b> {$_POST['browser']}<br />
                    <b>Url:</b> <a href='{$_POST['url']}'>{$_POST['url']}</a>";
            
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
            $message .= "--".$uid.$eol;
            $message .= "Content-Type: application/octet-stream; name=\"".$filename."\"".$eol;
            $message .= "Content-Transfer-Encoding: base64".$eol;
            $message .= "Content-Disposition: attachment; filename=\"".$filename."\"".$eol.$eol;
            $message .= chunk_split($file).$eol.$eol;
            $message .= "--".$uid."--";
            
            mail($config->getValue('supportEmail'), "[{$config->getValue('networkName')}] Report Issue", $message, $header);
            exit;
        }
        return true;
    }

?>
