<?php
autoload_register('Poll');
require_once("MyPolls.php");
require_once("CreatePoll.php");

$poll = new PollView();

UnknownAction::createAction(array($poll, 'viewPoll'));
//$notificationFunctions[] = 'PollView::createNotification';

class PollView {

	var $pollCollection;
	
	static function createNotification(){
		global $wgUser, $notifications, $wgServer, $wgScriptPath;
		$rows = DBFunctions::select(array('grand_poll_collection'),
		                            array('collection_id'));
		foreach($rows as $row){
			$collection = PollCollection::newFromId($row['collection_id']);
			$canUserViewPoll = $collection->canUserViewPoll($wgUser);
			if($canUserViewPoll){
				if(!$collection->hasUserVoted($wgUser->getId()) && !$collection->isPollExpired()){
					$notifications[] = new Notification("Poll: {$collection->name}", "You have not yet voted on this poll", "$wgServer$wgScriptPath/index.php?action=viewPoll&id={$collection->id}");
				}
			}
		}	
	}

	function viewPoll($action, $article){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath;
		if($action == "viewPoll"){
			if($_GET['id'] == "latest"){
			    $this->pollCollection = PollCollection::getLatest();
			}
            elseif($_GET['id'] == "random"){
                $this->pollCollection = PollCollection::getRandom();
            }
			else{
			    $this->pollCollection = PollCollection::newFromId($_GET['id']);
			}
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
						throw new PermissionsError('read');
					}
					return false;
				}
				$isOwner = ($wgUser->getId() == $this->pollCollection->author->getId() && $found);
				if(!$expired){
					$notVotedYet = (!$this->pollCollection->hasUserVoted($wgUser->getId()) && !(isset($_POST['submit']) && $this->allQuestionsAnswered()));
					if($isOwner){
						$this->sendEmails();
					}
					
					$wgOut->addHTML("<h2>Weekly Poll</h2>");
					//$wgOut->addHTML("<b>Created By:</b> {$this->pollCollection->author->getName()}<br />");
					//$wgOut->addHTML("<b>Expires:</b> {$this->pollCollection->getExpirationDate()}<br />");
				
					if($notVotedYet){
						$embed = "";
						if(isset($_POST['submit'])){
							$wgOut->addHTML("Not all questions were answered.<br />");
						}
						if(isset($_GET['embed'])){
						    $embed = "&embed";
						}
						$wgOut->addHTML("<form action='index.php?action=viewPoll&id={$this->pollCollection->id}$embed' method='post'>");
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
						$wgOut->addHTML("<br /><input type='submit' name='submit' value='Submit' />");
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
				$wgOut->addHTML("<script type='text/javascript'>
		                            $('table.poll').DataTable({
		                                bFilter: false,
		                                bPaginate: false,
		                                sDom: 'rt',
		                                aaSorting: [],
		                                columns: [
                                            {orderable: true},
                                            {orderable: false},
                                            {orderable: true}
                                        ]
		                            });
		                        </script>");
				return false;
			}
			else{
				$wgOut->setPageTitle($this->pollCollection->name);
				$wgOut->addHTML("There is no poll with this id");
				return false;
			}
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
			$this->resultsHTML($wgOut,$poll);
		}
		else if($submitted){
			$option = $poll->getOption($_POST["choice{$poll->id}"]);
			$option->addVote($wgUser->getId());
			$this->resultsHTML($wgOut,$poll);
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
		$wgOut->addHTML("		</td>
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
	    global $config;
		$totalVotes = $poll->getTotalVotes();
		$wgOut->addHTML("<h2>{$poll->name}</h2>
				<table class='poll wikitable sortable' cellpadding='5' cellspacing='1' width='100%' style='background:#CCCCCC;'>
				    <thead>
					<tr style='background:#EEEEEE;'><th>Option</th><th>Bar Graph</th><th>Total Votes</th></tr>
					</thead>
					<tbody>");
		foreach($poll->options as $option){
			$nVotes = $option->getTotalVotes();
			if($totalVotes != 0){
				$percentOfTotal = ($nVotes/$totalVotes)*100.00;
			}
			else{
				$percentOfTotal = 0;
			}
			$wgOut->addHTML("<tr style='background:#FFFFFF;'><td>{$option->name}</td><td><table style='background: {$config->getValue('highlightColor')};' width='$percentOfTotal%'><tr><td style='background:transparent;border-width:0;padding:1px;'></td></tr></table></td><td>$nVotes&nbsp;<span style='float:right;'>(".number_format($percentOfTotal, 2)."%)</span></td></tr>");
		}
		$wgOut->addHTML("</tbody></table><br />");
	}

}

?>
