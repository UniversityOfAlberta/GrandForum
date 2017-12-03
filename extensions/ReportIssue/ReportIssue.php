<?php

    $wgHooks['BeforePageDisplay'][] = 'reportIssue';
    $wgHooks['UnknownAction'][] = 'reportIssueAction';

    function reportIssue($wgOut, $skin){
        global $wgServer, $wgScriptPath, $config;
        $wgOut->addScript("<link rel='stylesheet' type='text/css' href='{$wgServer}{$wgScriptPath}/extensions/ReportIssue/reportIssue.css' />");
        $wgOut->addScript("<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/ReportIssue/reportIssue.js'></script>");
        
        $wgOut->addHTML("<div title='Report Issue' id='reportIssueDialog' style='display:none;'>
            <p>If you are experiencing an issue on the current page, you can report it here.  Explain what the issue is and a report will be sent to {$config->getValue('supportEmail')}.  The following information will automatically be sent:</p>
            <ul>
                <li>User</li>
                <li>Browser Information</li>
                <li>Url of Page</li>
                <li>Screenshot of page</li>
            </ul>
            <b>Additional Comments:</b>
            <textarea style='width:100%;height:100px;' id='additional_comments'></textarea>
        </div>");
        return true;
    }
    
    function reportIssueAction($action){
        global $config;
        if($action == 'reportIssue'){
            $me = Person::newFromWgUser();
            $headers = array();
            $comments = nl2br($_POST['comments']);
            
            // To send HTML mail, the Content-type header must be set
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=iso-8859-1';
            $headers[] = "From: {$me->getName()} <{$me->getEmail()}>";

            $message = "<p>{$comments}</p><br />
                        <b>User:</b> {$me->getName()} ({$me->getEmail()})<br />
                        <b>Browser:</b> {$_POST['browser']}<br />
                        <b>Url:</b> <a href='{$_POST['url']}'>{$_POST['url']}</a><br />
                        <b>Screenshot:</b><br />
                        <img src='{$_POST['img']}' />";
            mail($config->getValue('supportEmail'), "[{$config->getValue('networkName')}] Report Issue", $message, implode("\r\n", $headers));
            exit;
        }
        return true;
    }

?>
