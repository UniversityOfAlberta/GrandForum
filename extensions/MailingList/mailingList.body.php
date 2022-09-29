<?php

require_once("MyMailingLists.php");
require_once("MailingListRules/MailingListRules.php");

if($config->getValue("networkName") == "AI4Society"){
    require_once("Newsletter/Newsletter.php");
}

$wgHooks['ArticleViewHeader'][] = 'MailList::createMailListTable';
$wgHooks['userCan'][] = 'MailList::userCanExecute';

class MailList{
    
    static function userCanExecute(&$title, &$user, $action, &$result){
        global $wgOut, $wgServer, $wgScriptPath, $config;
        if($action == "read"){
            $me = Person::newFromUser($user);
            if($me->isRoleAtLeast(MANAGER)){
                $result = true;
                return true;
            }
            $nsText = "";
            $text = "";
            if($title->getNSText() != ""){
                $nsText = $title->getNSText();
                $text = $title->getText();
            }
            else if(strstr($title->getText(), ":") !== false){
                $nsText = explode(":", $title->getText());
                $text = $nsText[1];
                $nsText = $nsText[0];
            }
            else{
                $text = $title->getText();
            }
            if($nsText == "Mail"){
                $list = strtolower($text);
                $result = MailingList::isSubscribed($list, $me);
                if($me->isRoleAtLeast(STAFF) && $list != "support" && $list != strtolower($config->getValue('networkName')."-support")){
                    $result = true;
                }
            }
        }
        return true;
    }
    
    static function removeNextPart($body){
        $exploded = explode("-------------- next part --------------", $body);
        $lines = explode("\n", $exploded[0]);
        $body = "";
        foreach($lines as $line){
            $body .= trim($line)."\n";
        }
        return $body;
    }
    
    static function removeQuotedText($body) {
        $bodyLines = explode("\n", $body);
        $bodyLines = array_reverse($bodyLines);
        $hasQuotesAtEnd = false;
        foreach ($bodyLines as $key => $line) {
            $line = trim($line);
        
            if ($line === "")
                continue;
    
            if ($line[0] == '>') {
                unset($bodyLines[$key]);
                $hasQuotesAtEnd = true;
            }
            else 
                break;
        }
    
        //remove first line before start of quoted text (which is something like "On X, Y wrote:" 
        if ($hasQuotesAtEnd) { 
            foreach ( $bodyLines as $key => $line ) {
                $line = trim($line);
                if ($line === "")
                    continue;
                unset($bodyLines[$key]);
                break;
            }
        }
    
        $bodyLines = array_reverse($bodyLines);
        $found = false;
        foreach($bodyLines as $key => $line){
            $line = trim($line);
            if(strstr($line, ">>>") !== false){
                $found = true;
            }
            if($found){
                unset($bodyLines[$key]);
            }
        }
        
        // If there is still quoted text, it is probably inlined, and shoud be shown, but it should stand out
        $started = false;
        foreach($bodyLines as $key => $line){
            $line = trim($line);
            if ($line === "")
                continue;
            if($line[0] == ">" && !$started){
                if(isset($bodyLines[$key-1]) && trim($bodyLines[$key-1]) == ""){
                    unset($bodyLines[$key-1]);
                }
                $bodyLines[$key] = "<blockquote>".substr($line, 1)."<br />";
                $started = true;
            }
            else if($line[0] != ">" && $started){
                $bodyLines[$key] = "</blockquote>$line<br />";
                $started = false;
            }
            else if($line[0] == ">"){
                $bodyLines[$key] = substr($line, 1)."<br />";
            }
        }
        
        $bodyLines = array_reverse($bodyLines);
        foreach ($bodyLines as $key => $bodyLine) {
            $bodyLine = trim($bodyLine);
            if ($bodyLine === "")
                unset($bodyLines[$key]);
            else 
                break; //$bodyLines[$key] = $bodyLine; //BT: Changed to only remove blank lines at end of message and not trim lines.
        }
        $bodyLines = array_reverse($bodyLines);
    
        return implode("\n\n", $bodyLines);
    }
    
    static function createMailListTable($action, $article){
        global $wgOut, $wgTitle, $wgScriptPath, $wgServer, $wgUser, $config;
        $result = true;
        if($wgTitle->getText() == "Mail Index" || $wgTitle->getNsText() == "Mail" && strpos($wgTitle->getText(), "MAIL") !== 0){
            self::userCanExecute($wgTitle, $wgUser, "read", $result);
            if(!$result){
                permissionError();
            }
            $project_name = strtolower($wgTitle->getText());
            if(isset($_GET['thread'])){
                self::createMailListThread($project_name, $_GET['thread']);
                return false;
            }
            
            $me = Person::newFromWgUser();
            $data = DBFunctions::select(array('wikidev_projects'),
                                        array('*'),
                                        array('mailListName' => EQ($project_name)));
            if(count($data) > 0){
                $wgOut->addHTML("<b>Mail List Address:</b> <a href='mailto:{$data[0]['mailListName']}@{$config->getValue('domain')}'>{$data[0]['mailListName']}@{$config->getValue('domain')}</a>");
                $emails = MailingList::listMembers($project_name);
                $wgOut->addHTML("<h2>List Members</h2><a id='showPeople' class='button'>Show Members on List</a>
                <script type='text/javascript'>
                    $('#showPeople').click(function(){
                        $(this).hide();
                        $('#people').show();
                    });
                </script>");
                $wgOut->addHTML("<div style='display:none;' id='people'>");
                foreach($emails as $email){
                    $person = Person::newFromEmail($email);
                    if($person != null && $person->getId() != 0){
                        $wgOut->addHTML("<a href='{$person->getUrl()}'>{$person->getNameForForms()}</a> &lt;$email&gt;<br />");
                    }
                    else{
                        $wgOut->addHTML("$email<br />");
                    }
                }
                $wgOut->addHTML("</div>");
            }
            else{
                $wgOut->addHTML("This Mailing list has not been set up yet");
            }
            
            $wgOut->addHTML("<h2>$project_name Mail List Archive</h2>");
            $data = MailingList::getThreads($project_name);   
            if(count($data) > 0){
                $wgOut->addHTML("<table style='display:none;' id='mailingListMessages' frame='box' rules='all'>
                        <thead><tr>
                            <th style='white-space:nowrap;'>First Message</th><th style='white-space:nowrap;'>Last Message</th><th style='white-space:nowrap;'>Subject</th><th style='white-space:nowrap;'>Messages</th><th style='white-space:nowrap;'>People</th>
                        </tr></thead>
                        <tbody>");
        
                foreach($data as $row){
                    $data2 = MailingList::getMessages($row['project_id'], $row['refid_header']);
                    $users = "";
                    $people = array();
                    foreach($data2 as $row2){
                        $person = Person::newFromName($row2['user_name']);
                        if($person->getName() != ""){
                            $people[] = "<a href='{$person->getUrl()}'>{$person->getNameForForms()}";
                        }
                    }
                    $users = implode(", ", array_unique($people));
                    $class = "";
                    if($users == ""){
                        $class = "spam";
                    }
                    $wgOut->addHTML("<tr class='$class'>
                        <td style='white-space:nowrap;'>{$row['first_date']}</td>
                        <td style='white-space:nowrap;'>{$row['last_date']}</td>
                        <td><a href='$wgServer$wgScriptPath/index.php/Mail:$project_name?thread=".urlencode($row['refid_header'])."'>{$data2[0]['subject']}</a></td>
                        <td>".count($data2)."</td>
                        <td>$users</td>
                    </tr>");
                }
                $wgOut->addHTML("</tbody></table>");
                $wgOut->addHTML("<script type='text/javascript'>
                    var spam = $('tr.spam').detach();
                    if(spam.length > 0){
                        $('#mailingListMessages').before('<span id=\"spamWrapper\"><p>' + spam.length + ' threads were flagged as being spam. <button id=\"spam\">Show Spam</button></p></span>');
                        $('#spam').click(function(){
                            $('#mailingListMessages').dataTable().fnDestroy();
                            $('#mailingListMessages').hide();
                            $('#mailingListMessages tbody').append(spam);
                            $('#spamWrapper').remove();
                            createTable();
                        });
                    }
                    
                    function createTable(){
                        $('#mailingListMessages').dataTable({'iDisplayLength': 100,
                                            'aaSorting': [ [0,'desc'], [1,'desc']],
                                            'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                                            'autoWidth': false});
                        $('#mailingListMessages').show();
                    }
                    
                    createTable();
                </script>");
            }
            else {
                $wgOut->addHTML("There have been no messages sent");
            }
            $wgOut->setPageTitle("Mailing List Archives");
            $wgOut->output();
            $wgOut->disable();
            exit;
        }
        return false;
    }
    
    function createMailListThread($project_name, $thread){
        global $wgOut;
        $sql = "SELECT m.subject, m.body, m.date, m.author, m.user_name, m.address
                FROM wikidev_projects p, wikidev_messages m
                WHERE m.project_id = p.projectid
                AND m.refid_header = '{$thread}'
                AND p.mailListName = '$project_name'
                ORDER BY date ASC";
        
        $data = DBFunctions::execSQL($sql);
        if(count($data) > 0){
            $wgOut->setPageTitle($data[0]['subject']);
            foreach($data as $row){
                $person = Person::newFromName($row['user_name']);
                $from = "{$row['author']} &lt;<a href='mailto:{$row['address']}'>{$row['address']}</a>&gt;";
                $date = $row['date'];
                $date = date_create($date);
                $img = "";
                if($person->getName() != ""){
                    $from = "<a href='{$person->getUrl()}'><b>{$row['author']}</b></a> &lt;<a href='mailto:{$row['address']}'>{$row['address']}</a>&gt;";
                    $img = "<img class='photo' src='{$person->getPhoto()}' />";
                }
                $wgOut->addHTML("<div class='thread-message'>");
                    $wgOut->addHTML("<table padding='0'>
                        <tr><td rowspan='2'>$img</td><td valign='top'><b>From:</b></td><td valign='top'>$from</td></tr>
                        <tr><td valign='top'><b>Date:</b></td><td valign='top'>".date_format($date, "l, F d, Y g:i A")."</td></tr>
                    </table>");
                    $body = self::removeQuotedText(self::removeNextPart($row['body']));
                    $wgOut->addHTML("<div class='inner-message'>");
                    $wgOut->addWikiTextAsContent($body);
                $wgOut->addHTML("</div></div>");
            }
        }
        else{
            $wgOut->setPageTitle("Thread not found");
            $wgOut->addHTML("This thread doesn't exist for $project_name");
        }
        $wgOut->output();
        $wgOut->disable();
        exit;
    }
}

?>
