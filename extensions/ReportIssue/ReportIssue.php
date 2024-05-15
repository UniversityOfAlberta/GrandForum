<?php

    $wgHooks['BeforePageDisplay'][] = 'reportIssue';
    UnknownAction::createAction('reportIssueAction');

    function reportIssue($wgOut, $skin){
        global $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        $wgOut->addScript("<link rel='stylesheet' type='text/css' href='{$wgServer}{$wgScriptPath}/extensions/ReportIssue/reportIssue.css?".filemtime(dirname(__FILE__)."/reportIssue.css")."' />");
        $wgOut->addScript("<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/ReportIssue/reportIssue.js?".filemtime(dirname(__FILE__)."/reportIssue.js")."' ></script>");
        
        $firstName = str_replace("'", "&#39;", $me->getFirstName());
        $lastName = str_replace("'", "&#39;", $me->getLastName());
        $email = str_replace("'", "&#39;", $me->getEmail());
        $loggedIn = "<tr>
                        <td class='label'><en>First Name</en><fr>Prénom</fr>:</td>
                        <td class='value'><input type='text' name='first_name' value='{$firstName}' /></td>
                     </tr>
                     <tr>
                        <td class='label'><en>Last Name</en><fr>Nom</fr>:</td>
                        <td class='value'><input type='text' name='last_name' value='{$lastName}' /></td>
                     </tr>
                     <tr>
                        <td class='label'><en>Email</en><fr>Courriel</fr>:</td>
                        <td class='value'><input type='text' name='email' value='{$email}' /></td>
                     </tr>";
        
        $wgOut->addHTML("<div title='Report Issue' id='reportIssueDialog' style='display:none;'>
            <p>
                <en>If you are experiencing an issue on the current page, you can report it here.  Explain what the issue is and a report will be sent to {$config->getValue('supportEmail')}.  The following information will automatically be sent:</en>
                <fr>Si vous rencontrez un problème sur la page actuelle, vous pouvez le signaler ici. Expliquez la nature du problème; un rapport sera envoyé à {$config->getValue('supportEmail')}. Les renseignements suivants seront automatiquement transmis:</fr>
            </p>
            <ul>
                <li>
                    <en>User/Email (if logged in)</en>
                    <fr>Utilisateur/courriel (si connecté)</fr>
                </li>
                <li>
                    <en>Browser Information</en>
                    <fr>Information sur le navigateur</fr>
                </li>
                <li>
                    <en>Url of Page</en>
                    <fr>URL de la page</fr>
                </li>
                <li>
                    <en>Screenshot of page</en>
                    <fr>Capture d’écran de la page</fr>
                </li>
            </ul>
            <table>
            {$loggedIn}
            </table>
            <b><en>Additional Comments:</en><fr>Commentaires supplémentaires:</fr></b>
            <textarea style='width:100%;height:100px;' id='additional_comments'></textarea>
        </div>");
        
        $wgOut->addHTML("<div title='Contact Us' id='contactUsDialog' style='display:none; width: 112px;'>
            <en>
                Do you have questions, comments or need some help?<br />
                Send us an email.
            </en>
            <fr>
                Vous avez des questions, des commentaires ou besoin d’aide?<br />
                Envoyez-nous un courriel.
            </fr>
            <table>
                {$loggedIn}
                <tr>
                    <td class='label'><b><en>Subject</en><fr>Objet</fr>:</b></td>
                    <td class='value'> 
                        <select id='topic' style='vertical-align:middle;'>
                            <option selected>Find an expert</option>
                            <option>Find a student</option>
                            <option>Other</option>
                        </select>
                    </td>
                </tr>
                <tr id='topic_other'>
                    <td class='label'></td>
                    <td class='value'><input type='text' id='topicOther' /></td>
                </tr>
            </table>
            <b><en>Message</en><fr>Message</fr>:</b><br />
            <textarea style='width:100%;height:100px;' id='additional_comments'></textarea>
            <div id='contactFile'>
                <div id='fileSizeError' class='error' style='display:none;'>This file is too large, please choose a file smaller than 5MB</div>
                <b>Attachment:</b><br /><input type='file' /> (5MB max)
            </div>
        </div>");
        
        $wgOut->addHTML("<div title='Help' id='helpDialog' style='display:none; width: 112px;'>
            <table>
                {$loggedIn}
                <tr>
                    <td class='label'>Phone #:</td>
                    <td class='value'><input type='text' name='phone' value='' /></td>
                 </tr>
            </table>
            <b>Message:</b><br />
            <textarea style='width:100%;height:100px;' id='additional_comments'></textarea>
        </div>");
        return true;
    }
    
    function reportIssueAction($action){
        global $config, $wgAdditionalMailParams;
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
            $email = $_POST['email'];
            $msg = "";
            $subj = "";
            $phone = (isset($_POST['phone'])) ? "({$_POST['phone']})" : "";
            if(isset($_POST['img'])){
                $subj = "Report Issue";
                $msg = "<p>{$comments}</p><br />
                        <b>User:</b> {$_POST['first_name']} {$_POST['last_name']} ({$email}) {$phone}<br />
                        <b>Browser:</b> {$_POST['browser']}<br />
                        <b>Url:</b> <a href='{$_POST['url']}'>{$_POST['url']}</a>";
            }
            else{
                if(isset($_POST['topic'])){
                    $subj = "Contact Us - {$_POST['topic']}";
                }
                else{
                    $subj = "Help";
                }
                $msg = "<p>{$comments}</p><br />
                        <b>User:</b> {$_POST['first_name']} {$_POST['last_name']} ({$email}) {$phone}";
            }
            
            $eol = "\r\n";
            // Basic headers
            $header = "From: {$_POST['first_name']} {$_POST['last_name']} <{$_POST['email']}>".$eol;
            $header .= "Reply-To: {$_POST['email']}".$eol;
            $header .= "MIME-Version: 1.0".$eol;
            $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"".$eol;

            // Put everything else in $message
            $message = "--".$uid.$eol;
            $message .= "Content-Type: text/html; charset=UTF-8".$eol;
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
            
            mail($config->getValue('supportEmail'), "[{$config->getValue('networkName')}] {$subj}", $message, $header, $wgAdditionalMailParams);
            exit;
        }
        return true;
    }

?>
