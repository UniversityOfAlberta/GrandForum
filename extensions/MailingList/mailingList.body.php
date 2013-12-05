<?php

require_once("MyMailingLists.php");
require_once("MailingListAdmin.php");
require_once("MailingListRequest.php");

global $wgArticle;
$mailList = new MailList();
$wgHooks['ArticleViewHeader'][] = array($mailList, 'createMailListTable');
$wgHooks['userCan'][] = array($mailList, 'userCanExecute');

class MailList{
    
    function userCanExecute(&$title, &$user, $action, &$result){
        global $wgOut, $wgServer, $wgScriptPath;
        if($action == "read"){
            $me = Person::newFromUser($user);
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
            }
        }
        return true;
    }
    
    function removeNextPart($body){
        $exploded = explode("-------------- next part --------------", $body);
        $lines = explode("\n", $exploded[0]);
        $body = "";
        foreach($lines as $line){
            $body .= trim($line)."\n";
        }
        return $body;
    }
    
    function removeQuotedText($body) {
        $bodyLines = explode("\n", $body);
        $bodyLines = array_reverse($bodyLines);
        
        foreach ($bodyLines as $key => $bodyLine) {
            $bodyLine = trim($bodyLine);
            if ($bodyLine === "")
                unset($bodyLines[$key]);
            else 
                break; //$bodyLines[$key] = $bodyLine; //BT: Changed to only remove blank lines at end of message and not trim lines.
        }
        
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
        return $body;
        //return implode("\n", array_reverse($bodyLines));
    }
    
    function createMailListTable($action, $article){
        global $wgOut, $wgTitle, $wgScriptPath, $wgServer, $wgUser;
        $result = true;
        if($wgTitle->getText() == "Mail Index" || $wgTitle->getNsText() == "Mail" && strpos($wgTitle->getText(), "MAIL") !== 0){
            $this->userCanExecute($wgTitle, $wgUser, "read", $result);
            if(!$result){
                permissionError();
            }
            $project_name = strtolower($wgTitle->getText());
            if(isset($_GET['thread'])){
                $this->createMailListThread($project_name, $_GET['thread']);
                return false;
            }
            
            $me = Person::newFromWgUser();
            
            $data = DBFunctions::select(array('wikidev_projects'),
                                        array('*'),
                                        array('mailListName' => EQ($project_name)));
            if(count($data) > 0){
                $wgOut->addHTML("<b>Mail List Address:</b> <a href='mailto:{$data[0]['mailListName']}@forum.grand-nce.ca'>{$data[0]['mailListName']}@forum.grand-nce.ca</a>");
            }
            else{
                $wgOut->addHTML("This Mailing list has not been set up yet");
            }
            $project_name = mysql_real_escape_string($project_name);
            $wgOut->addHTML("<h2>$project_name Mail List Archive</h2>");
            $sql = "SELECT m.refid_header, m.project_id, MIN(date) as first_date, MAX(date) as last_date
                    FROM wikidev_projects p, wikidev_messages m
                    WHERE m.project_id = p.projectid
                    AND p.mailListName = '$project_name'
                    GROUP BY m.refid_header
                    ORDER BY first_date DESC";
            
            $data = DBFunctions::execSQL($sql);    
            if(DBFunctions::getNRows() > 0){
                $wgOut->addHTML("<br /><table id='mailingListMessages' frame='box' rules='all'>
                        <thead><tr>
                            <th style='white-space:nowrap;'>First Message</th><th style='white-space:nowrap;'>Last Message</th><th style='white-space:nowrap;'>Subject</th><th style='white-space:nowrap;'>Messages</th><th style='white-space:nowrap;'>People</th>
                        </tr></thead>
                        <tbody>");
        
                foreach($data as $row){
                    $sql = "SELECT m.user_name, m.subject, m.date, m.body
                            FROM wikidev_messages m 
                            WHERE m.refid_header= '{$row['refid_header']}'
                            AND m.project_id = '{$row['project_id']}'";
                    $data2 = DBFunctions::execSQL($sql);
                    $users = "";
                    
                    $people = array();
                    foreach($data2 as $row2){
                        $person = Person::newFromName($row2['user_name']);
                        if($person->getName() != ""){
                            $people[] = "<a href='{$person->getUrl()}'>{$person->getNameForForms()}";
                        }
                    }
                    $users = implode(", ", array_unique($people));
                    
                    $wgOut->addHTML("<tr>
                        <td style='white-space:nowrap;'>{$row['first_date']}</td>
                        <td style='white-space:nowrap;'>{$row['last_date']}</td>
                        <td><a href='$wgServer$wgScriptPath/index.php/Mail:$project_name?thread=".urlencode($row['refid_header'])."'>{$data2[0]['subject']}</a></td>
                        <td>".count($data2)."</td>
                        <td>$users</td>
                    </tr>");
                }
                $wgOut->addHTML("</tbody></table>");
                $wgOut->addHTML("<script type='text/javascript'>
                    $('#mailingListMessages').dataTable({'iDisplayLength': 100,
                                        'aaSorting': [ [0,'desc'], [1,'desc']],
                                        'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]});
                </script>");
            }
            else {
                $wgOut->addHTML("There have been no messages sent");
            }
            $wgOut->setPageTitle($wgTitle->getNSText()." Mailing List Archives");
            $wgOut->output();
            $wgOut->disable();
            return false;
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
                if($person->getName() != ""){
                    $from = "<a href='{$person->getUrl()}'><b>{$row['author']}</b></a> &lt;<a href='mailto:{$row['address']}'>{$row['address']}</a>&gt;";
                }
                $wgOut->addHTML("<div class='thread-message'>");
                    $wgOut->addHTML("<table padding='0'>
                        <tr><td><b>From:</b></td><td>$from</td></tr>
                        <tr><td><b>Date:</b></td><td>".date_format($date, "l, F d, Y g:i A")."</td></tr>
                    </table>");
                    $body = $this->removeQuotedText($this->removeNextPart($row['body']));
                    $wgOut->addHTML("<div class='inner-message'>");
                    $wgOut->addWikiText($body);
                $wgOut->addHTML("</div></div>");
            }
        }
        else{
            $wgOut->setPageTitle("Thread not found");
            $wgOut->addHTML("This thread doesn't exist for $project_name");
        }
        $wgOut->output();
        $wgOut->disable();
    }
}

?>
