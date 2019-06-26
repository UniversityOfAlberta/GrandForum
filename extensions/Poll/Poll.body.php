<?php
autoload_register('Poll');
require_once("MyPolls.php");
require_once("CreatePoll.php");

$wgHooks['UnknownAction'][] = 'PollView::viewPoll';
$notificationFunctions[] = 'PollView::createNotification';
$wgHooks['TopLevelTabs'][] = 'PollView::createTab';
$wgHooks['SubLevelTabs'][] = 'PollView::createSubTabs';

class PollView {

	var $pollCollection;
	
	static function createTab(&$tabs){
	    $tabs["MyPolls"] = TabUtils::createTab("My Polls");
	    return true;
	}
	
	static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        
        $rows = DBFunctions::select(array('grand_poll_collection'),
		                            array('collection_id'));
		foreach($rows as $row){
			$collection = PollCollection::newFromId($row['collection_id']);
			$canUserViewPoll = $collection->canUserViewPoll($wgUser);
			if($canUserViewPoll){
				if(!$collection->hasUserVoted($wgUser->getId()) && !$collection->isPollExpired()){
				    $selected = @($_GET['action'] == "viewPoll" && $_GET['id'] === $collection->id) ? "selected" : false;
                    $tabs["MyPolls"]['subtabs'][] = TabUtils::createSubTab("{$collection->name}", "$wgServer$wgScriptPath/index.php?action=viewPoll&id={$collection->id}", $selected);
				}
			}
		}
        return true;
    }
	
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
				}
				return false;
			}
			
			$isOwner = ($wgUser->getId() == $this->pollCollection->author->getId() && $found);
			if($isOwner && !isset($_GET['edit'])){
			    // Person is Owner, show Edit button
			    $wgOut->addHTML("<a class='button' style='position:absolute; top: 5px; right: 10px;' href='index.php?action=viewPoll&id={$this->pollCollection->id}&edit'>Edit Poll</a>");
			}
			if($isOwner && isset($_GET['edit'])){
			    // Show Edit view
			    $this->editView();
			    return false;
			}
			if(!$expired){
				$notVotedYet = (!$this->pollCollection->hasUserVoted($wgUser->getId()) && !(isset($_POST['submit']) && $this->allQuestionsAnswered()));
				if($isOwner){
					$this->sendEmails();
				}
                $wgOut->addHTML("<div>{$this->pollCollection->description}</div>");
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
		global $wgOut, $wgUser, $wgMessage;
		$submitted = (isset($_POST['submit']) && $this->allQuestionsAnswered());
		$wgOut->setPageTitle($this->pollCollection->name);
		if($this->pollCollection->isPollExpired() || $this->pollCollection->hasUserVoted($wgUser->getId())){
			$this->resultsHTML($wgOut, $poll);
		}
		else if($submitted){
		    if($poll->choices > 1){
		        foreach($_POST["choice{$poll->id}"] as $key => $option){
		            if($key < $poll->choices){
		                $option = $poll->getOption($option);
			            $option->addVote($wgUser->getId());
			        }
		        }
		    }
		    else{
			    $option = $poll->getOption($_POST["choice{$poll->id}"]);
			    $option->addVote($wgUser->getId());
			}
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
			if($poll->choices > 1){
		        foreach($_POST["choice{$poll->id}"] as $key => $option){
		            if($key < $poll->choices){
		                $option = $poll->getOption($option);
			            $option->addVote($wgUser->getId());
			        }
		        }
		    }
		    else{
			    $option = $poll->getOption($_POST["choice{$poll->id}"]);
			    $option->addVote($wgUser->getId());
			}
			$wgOut->addHTML("Vote added<br />");
		}
		else{
			$this->pollCollectionHTML($wgOut, $poll);
		}
	}
	
	function aggregateTable(){
		global $wgOut;
		$totalVoters = $this->pollCollection->getTotalVoters();
		$totalVotes = $this->pollCollection->getTotalVotes();
		$potentialVoters = $this->pollCollection->getTotalPotentialVoters();
		$wgOut->addHTML("</table>
				<br />
				<table class='wikitable' cellpadding='5' cellspacing='1' style='background:#CCCCCC;'>
					<tr style='background:#EEEEEE;'>
						<th>Stat</th> <th>Value</th>
					</tr>
					<tr style='background:#FFFFFF;'>
						<td><b>Total Voters:</b></td><td>$totalVoters</td>
					</tr>
					<tr style='background:#FFFFFF;'>
						<td><b>Total Votes:</b></td><td>$totalVotes</td>
					</tr>
					<tr style='background:#FFFFFF;'>
						<td><b>Potential Voters:</b></td><td>$potentialVoters</td>
					</tr>
					<tr style='background:#FFFFFF;'>
						<td><b>Users who have not voted:</b></td><td>".($potentialVoters - $totalVoters));
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
	    if($poll->choices == 1){
		    foreach($poll->options as $option){
			    $wgOut->addHTML("<tr><td><input type='radio' name='choice{$poll->id}' value='{$option->id}' /></td><td>{$option->name}</td></tr>");
		    }
		}
		else{
		    foreach($poll->options as $option){
			    $wgOut->addHTML("<tr><td><input type='checkbox' name='choice{$poll->id}[]' value='{$option->id}' /></td><td>{$option->name}</td></tr>");
		    }
		}
		$wgOut->addHTML("</table>");
	    if($poll->choices > 1){
		    $wgOut->addHTML("You can select up to {$poll->choices} choices");
		    $wgOut->addHTML("<script type='text/javascript'>
		        $('input[name=choice{$poll->id}\\\\[\\\\]]').on('click', function (evt) {
                    if ($('input[name=choice{$poll->id}\\\\[\\\\]]:checked').length > {$poll->choices}) {
                        this.checked = false;
                    }
                });
		    </script>");
		}
		$wgOut->addHTML("</fieldset>");
	}
	
	function processEdit(){
	    global $wgMessage;
	    $name = $_POST['name'];
		$description = @$_POST['description'];
		$noName = false;
		if($name == ""){
			$noName = true;
		}
		$groups = array();
		$noGroupsSelected = true;
		if(isset($_POST['groups'])){
			$groupP = $_POST['groups'];
			while (list ($key,$val) = @each ($groupP)) {
				$groups[] = $val;
				$noGroupsSelected = false;
			}
		}
		
		$validTime = false;
		if($_POST['time'] == "" || ctype_digit($_POST['time'])){
			$validTime = true;
		}
		
		if(!$noGroupsSelected && !$noName && $validTime){
		    DBFunctions::update('grand_poll_collection',
		                        array('collection_name' => $name,
		                              'description' => $description,
		                              'time_limit' => $_POST['time']),
		                        array('collection_id' => EQ($this->pollCollection->id)));
		    DBFunctions::delete('grand_poll_groups',
		                        array('collection_id' => $this->pollCollection->id));
		    foreach($groups as $group){
			    DBFunctions::insert('grand_poll_groups',
			                        array('group_name' => $group,
			                              'collection_id' => $this->pollCollection->id));
		    }
		    redirect("index.php?action=viewPoll&id={$this->pollCollection->id}");
		    exit;
		}
		else {
			// User failed to enter at least one of the required fields.  Display appropriate errors.
			if($noName){
				$wgMessage->addError("There was no poll name entered.");
			}
			if(!$validTime){
				$wgMessage->addError("The Time Limit must be a positive number, or left blank.");
			}
			if($noGroupsSelected){
				$wgMessage->addError("There were not user groups selected.");
			}
		}
	}
        
    function editView(){
        global $wgOut, $wgUser;
        if(isset($_POST['edit'])){
            $this->processEdit();
        }
        $me = Person::newFromWgUser();
        $wgOut->setPageTitle("Edit Poll");
        $wgOut->addHTML("<form action='index.php?action=viewPoll&id={$this->pollCollection->id}&edit' method='post'>");
        $wgOut->addHTML("<table>
                            <tr>
                                <td align='right' valign='top'>
                                    <b>Poll Name:</b>
                                </td>
                                <td>
                                    <input type='text' name='name' value='".str_replace("'", "&#39;", $this->pollCollection->name)."' size='50' />
                                    <br />
                                </td>
                            </tr>
                            <tr>
                                <td align='right' valign='top'>
                                    <b>Description:</b>
                                </td>
                                <td>
                                    <textarea name='description' style='height:200px;'>{$this->pollCollection->description}</textarea>
                                </td>
                            </tr>
                            <tr>
                                <td align='right' valign='top'>
                                    <b>Time Limit:</b>
                                </td>
                                <td>
                                    <input type='text' name='time' size='5' value='{$this->pollCollection->timeLimit}' />
                                    <div class='prefsectiontip'>
                                        <p>Time Limit should be a number which represents the number of days to leave the poll open.  If it is left blank or is 0 then the poll will stay open indefinitely.</p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <td align='right' valign='top'>
                                    <b>Poll Visibility:</b>
                                </td>
                                <td>");
        $pollGroups = $this->pollCollection->groups;
        $groups = $me->getAllowedRoles();
        if($me->isRole(PL)){
		    $groups[] = PL;
		}
        $nPerCol = ceil(count($groups)/3);
        $remainder = count($groups) % 3;
        $col1 = array();
        $col2 = array();
        $col3 = array();
        if($remainder == 0){
            $j = $nPerCol;
            $k = $nPerCol*2;
            $jEnd = $nPerCol*2;
            $kEnd = $nPerCol*3;
        }
        else if($remainder == 1){
            $j = $nPerCol;
            $k = $nPerCol*2 - 1;
            $jEnd = $nPerCol*2 - 1;
            $kEnd = $nPerCol*3 - 2;
        }
        else if($remainder == 2){
            $j = $nPerCol;
            $k = $nPerCol*2;
            $jEnd = $nPerCol*2;
            $kEnd = $nPerCol*3 - 1;
        }
        for($i = 0; $i < $nPerCol; $i++){
            if(isset($groups[$i])){
                $col1[] = $groups[$i];
            }
            if(isset($groups[$j]) && $j < $jEnd){
                $col2[] = $groups[$j];
            }
            if(isset($groups[$k]) && $k < $kEnd){
                $col3[] = $groups[$k];
            }
            $j++;
            $k++;
        }

        $groups = array();
        $i = 0;
        foreach($col1 as $row){
            if(isset($col1[$i])){
                $groups[] = $col1[$i];
            }
            if(isset($col2[$i])){
                $groups[] = $col2[$i];
            }
            if(isset($col3[$i])){
                $groups[] = $col3[$i];
            }
            $i++;
        }

        $checked = (in_array("all", $pollGroups)) ? "checked" : "";
        $wgOut->addHTML("<table border='0' cellspacing='2' width='500'>
            <tr>
                <td colspan='3'><input type='checkbox' name='groups[]' value='all' $checked /> All Users</td>\n");
        $i = 0;
        foreach($groups as $group){
            if($i % 3 == 0){
                $wgOut->addHTML("</tr><tr>\n");
            }
            $checked = (in_array($group, $pollGroups)) ? "checked" : "";
            $wgOut->addHTML("<td><input type='checkbox' name='groups[]' value='$group' $checked /> $group</td>\n");
            $i++;
        }
        $wgOut->addHTML("</tr></table>");
        $wgOut->addHTML("<input type='submit' name='edit' value='Submit Edits' />");
        $wgOut->addHTML("</td></tr></table>");
        $wgOut->addHTML("</form>");
        $wgOut->addHTML("<script type='text/javascript'>
                            $('textarea[name=description]').tinymce({
                                theme: 'modern',
                                menubar: false,
                                document_base_url: wgServer + wgScriptPath + '/',
                                plugins: 'link charmap lists table paste',
                                toolbar: [
                                    'undo redo | bold italic underline | link charmap | table | bullist numlist outdent indent | subscript superscript | alignleft aligncenter alignright alignjustify'
                                ],
                                paste_data_images: true
                            });
                        </script>");
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
