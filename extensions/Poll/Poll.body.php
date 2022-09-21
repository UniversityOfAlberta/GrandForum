<?php
autoload_register('Poll');
require_once("MyPolls.php");
require_once("CreatePoll.php");

UnknownAction::createAction('PollView::viewPoll');
$notificationFunctions[] = 'PollView::createNotification';

class PollView {

    var $pollCollection;
    
    static function createNotification(){
        global $wgUser, $notifications, $wgServer, $wgScriptPath;
        $rows = DBFunctions::execSQL("SELECT collection_id
                                      FROM grand_poll_collection
                                      WHERE (UNIX_TIMESTAMP() <= timestamp + time_limit*60*60*24 OR time_limit = 0)");
        foreach($rows as $row){
            $collection = PollCollection::newFromId($row['collection_id']);
            $canUserViewPoll = $collection->canUserViewPoll($wgUser);
            if($canUserViewPoll){
                if(!$collection->hasUserVoted($wgUser->getId())){
                    $notifications[] = new Notification("Poll: {$collection->name}", "You have not yet voted on this poll", "$wgServer$wgScriptPath/index.php?action=viewPoll&id={$collection->id}");
                }
            }
        }    
    }
    
    static function viewPoll($action, $article){
        if($action == "viewPoll"){
            $poll = new PollView();
            $poll->view();
            return false;
        }
        return true;
    }

    function view(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        $this->pollCollection = PollCollection::newFromId($_GET['id']);
        if($this->pollCollection != null){
            $groups = $wgUser->getGroups();
            $found = false;
            $found = $this->pollCollection->canUserViewPoll($wgUser);
            $expired = $this->pollCollection->isPollExpired();
            
            if(!$found) {
                // User is not allowed to view this poll
                if($wgUser->isLoggedIn()){
                    $wgOut->setPageTitle("Poll Permissions Error");
                    $wgOut->addHTML("You are not allowed to view this poll");
                }
                else {
                    $wgOut->loginToUse();
                    $wgOut->output();
                    $wgOut->disable();
                    exit;
                }
                return false;
            }
            
            $isOwner = ($wgUser->getId() == $this->pollCollection->author->getId() && $found);
            if(!$expired){
                $notVotedYet = (!$this->pollCollection->hasUserVoted($wgUser->getId()) && !(isset($_POST['submit']) && $this->allQuestionsAnswered()));
                if($isOwner){
                    $this->sendEmails();
                }
                
                $wgOut->addHTML("<b>Created By:</b> {$this->pollCollection->author->getName()}<br />");
                $wgOut->addHTML("<b>Expires:</b> {$this->pollCollection->getExpirationDate()}<br />");
            
                if($notVotedYet){
                    if(isset($_POST['submit'])){
                        $wgOut->addHTML("Not all questions were answered.<br />");
                    }
                    $wgOut->addHTML("<form action='index.php?action=viewPoll&id={$this->pollCollection->id}' method='post'>");
                }
                foreach($this->pollCollection->getPolls() as $poll){
                    if($isOwner){
                        // User is the owner of this poll, and is allowed to view the poll
                        // (We have to check this because the user could have been a part
                        // of a group when the poll was created, but might not be anymore)
                        $this->ownerViews($poll);
                    }
                    else if($found){
                        // User is allowed to view this poll
                        $this->voterViews($poll);
                    }
                }
                if($notVotedYet){
                    $wgOut->addHTML("<input type='submit' name='submit' value='Submit' />");
                    $wgOut->addHTML("</form>");
                }
                else if($isOwner){
                    $this->aggregateTable();
                }
            }
            else{
                $wgOut->setPageTitle("Poll is Expired");
                $wgOut->addHTML("This poll is expired");
                if($isOwner){
                    // Even though the poll is expired, the owner can still view the results of the poll, but cannot vote
                    foreach($this->pollCollection->getPolls() as $poll){
                        $this->ownerViews($poll);
                    }
                }
            }
            return false;
        }
        else{
            $wgOut->setPageTitle($this->pollCollection->name);
            $wgOut->addHTML("There is no poll with this id");
            return false;
        }
        return true;
    }
    
    function allQuestionsAnswered(){
        foreach($this->pollCollection->getPolls() as $poll){
            if(!isset($_POST["choice{$poll->id}"])){
                return false;
            }
        }
        return true;
    }
    
    function sendEmails(){
        global $wgOut, $wgServer, $wgScriptPath, $config;
        if(isset($_GET['email']) && $_GET['email'] == true){
            foreach($this->pollCollection->getPotentialVoters() as $user){
                if(!$this->pollCollection->hasUserVoted($user->user_id)){
                    $to = $user->user_email;
                    if($to != ""){
                        $subject = "{$config->getValue('siteName')}: You have been requested to vote on a poll";
                        $headers = "From: {$config->getValue('supportEmail')}\r\n" .
                                
                                'Content-type:text/html;charset=iso-8859-1' . "" .
                                'X-Mailer: PHP/' . phpversion();
                        $userName = str_replace(".", " ", $user->user_name);
                        $authorName = str_replace(".", " ", $this->pollCollection->author->getName());
                        $message = 
                        "Dear $userName<br />
                        <br />
                        $authorName has requested that you submit your vote for the poll \"<a href='$wgServer$wgScriptPath/index.php?action=viewPoll&id={$this->pollCollection->id}'>{$this->pollCollection->name}</a>\".<br /><br />
                        Best Regards<br />
                        {$config->getValue('siteName')} Forum Team";
                        mail("dwt@ualberta.ca", $subject, $message, $headers);
                    }
                }
                $wgOut->redirect("$wgServer$wgScriptPath/index.php?action=viewPoll&id={$this->pollCollection->id}&emailsSent=true");
            }
        }
        else if(isset($_GET['emailsSent']) && $_GET['emailsSent'] == true){
            $wgOut->addHTML("Users who have not yet voted have been emailed.<br />");
        }
    }
    
    function ownerViews($poll){
        global $wgOut, $wgUser;
        $submitted = (isset($_POST['submit']) && isset($_POST['submit']) && $this->allQuestionsAnswered());
        $wgOut->setPageTitle($this->pollCollection->name);
        if($this->pollCollection->isPollExpired() || $this->pollCollection->hasUserVoted($wgUser->getId())){
            $this->resultsHTML($wgOut, $poll);
        }
        else if($submitted){
            $option = $poll->getOption($_POST["choice{$poll->id}"]);
            $option->addVote($wgUser->getId());
            $wgOut->addHTML("Vote added<br />");
            $this->resultsHTML($wgOut, $poll);
        }
        else{
            $this->pollCollectionHTML($wgOut, $poll);
        }
    }
    
    function voterViews($poll){
        global $wgOut, $wgUser;
        $submitted = (isset($_POST['submit']) && isset($_POST['submit']) && $this->allQuestionsAnswered());
        $wgOut->setPageTitle($this->pollCollection->name);
        if($this->pollCollection->hasUserVoted($wgUser->getId())){
            $wgOut->addHTML("Thank you for your submission");
        }
        else if($submitted){
            $option = Option::newFromId($_POST["choice{$poll->id}"]);
            $option->addVote($wgUser->getId());
            $wgOut->addHTML("Vote added<br />");
        }
        else{
            $this->pollCollectionHTML($wgOut, $poll);
        }
    }
    
    function aggregateTable(){
        global $wgOut;
        $totalVotes = $this->pollCollection->getTotalVotes();
        $potentialVoters = $this->pollCollection->getTotalPotentialVoters();
        $wgOut->addHTML("</table>
                <br />
                <table class='wikitable' cellpadding='5' cellspacing='1' style='background:#CCCCCC;'>
                    <tr style='background:#EEEEEE;'>
                        <th>Stat</th> <th>Value</th>
                    </tr>
                    <tr style='background:#FFFFFF;'>
                        <td><b>Total Votes:</b></td><td>$totalVotes</td>
                    </tr>
                    <tr style='background:#FFFFFF;'>
                        <td><b>Potential Voters:</b></td><td>$potentialVoters</td>
                    </tr>
                    <tr style='background:#FFFFFF;'>
                        <td><b>Users who have not voted:</b></td><td>".($potentialVoters - $totalVotes));
        if(!$this->pollCollection->isPollExpired()){
            //$wgOut->addHTML("<a href='index.php?action=viewPoll&id={$this->pollCollection->id}&email=true'>[Email]</a>");
        }
        $wgOut->addHTML("        </td>
                    </tr>
                </table>");
    }
    
    function pollCollectionHTML($wgOut, $poll){
        $wgOut->addHTML("<fieldset>
                <legend><b>Q:</b> {$poll->name}</legend>
                <table cellpadding='5'>\n");
        foreach($poll->options as $option){
            $wgOut->addHTML("<tr><td><input type='radio' name='choice{$poll->id}' value='{$option->id}' /></td><td>{$option->name}</td></tr>");
        }
        $wgOut->addHTML("<tr><td colspan='2'></td></tr>
                </table>
                </fieldset>");
    }
    
    function resultsHTML($wgOut, $poll){
        $totalVotes = $poll->getTotalVotes();
        $wgOut->addHTML("<h2>{$poll->name}</h2>
                <table class='wikitable sortable' cellpadding='5' cellspacing='1' width='100%' style='background:#CCCCCC;'>
                    <tr style='background:#EEEEEE;'><th width='20%'>Option</th><th width='60%'>Bar Graph</th><th width='10%'>Total Votes</th><th width='10%'>Percent of Total</th></tr>");
        foreach($poll->options as $option){
            $nVotes = $option->getTotalVotes();
            if($totalVotes != 0){
                $percentOfTotal = ($nVotes/$totalVotes)*100.00;
            }
            else{
                $percentOfTotal = 0;
            }
            $wgOut->addHTML("<tr style='background:#FFFFFF;'><td>{$option->name}</td><td><table style='border: 1px solid #000000; background: #4c5b7b;' width='$percentOfTotal%'><tr><td></td></tr></table></td><td>$nVotes</td><td>".number_format($percentOfTotal, 2)."%</td></tr>");
        }
        $wgOut->addHTML("</table><br />");
    }

}

?>
