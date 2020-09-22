<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['CreatePoll'] = 'CreatePoll'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['CreatePoll'] = $dir . 'CreatePoll.i18n.php';
$wgSpecialPageGroups['CreatePoll'] = 'other-tools';

function runCreatePoll($par) {
  CreatePoll::execute($par);
}

$FORM_TEXT = "<fieldset id='q1'>
<legend>Question 1</legend>
<table>
	<tr>
		<td align='right' valign='top'>
			<b>Question:</b>
		</td>
		<td>
			<input type='text' name='question_1' size='50' />
		</td>
	</tr>
	<tr>
		<td align='right' valign='top'>
			<br />
			<b>Options:</b>
		</td>
		<td>
			<br />
			<input type='text' name='op0_1' /><br />
			<p id='add1'>
				<a href='javascript:addOption(1);'>[Add Option]</a>&nbsp;&nbsp;&nbsp;<a href='javascript:removeOption(1);'>[Remove Option]</a>
			</p>
		</td>
	</tr>
	<tr>
	    <td><b>Number of choices:</b></td>
	    <td><input type='number' name='nChoices_1' min='1' max='8' value='1' style='width:30px;' /></td>
	</tr>
</table>
</fieldset>";

class CreatePoll extends SpecialPage{

	function CreatePoll() {
		SpecialPage::__construct("CreatePoll", HQP.'+', true, 'runCreatePoll');
	}

	function execute($par){
		global $wgOut, $wgUser, $wgScriptPath, $wgServer, $wgTitle, $wgArticle, $wgMessage;
		if(isset($_POST['submit'])){
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
		
			$j = 1;
			$someQuestionsBlank = false;
			while(isset($_POST["op0_$j"])){
				if($_POST["question_$j"] == ""){
					$someQuestionsBlank = true;
					break;
				}
				$j++;
			}
		
			$validTime = false;
			if($_POST['time'] == "" || ctype_digit($_POST['time'])){
				$validTime = true;
			}
		
			if(!$someQuestionsBlank && !$noGroupsSelected && !$noName && $validTime){
				DBFunctions::insert('grand_poll_collection',
				                    array('author_id' => $wgUser->getId(),
				                          'collection_name' => $name,
				                          'description' => $description,
				                          'self_vote' => $_POST['self'],
				                          'timestamp' => time(),
				                          'time_limit' => $_POST['time']));

				$collection_id = DBFunctions::insertId();
				foreach($groups as $group){
				    DBFunctions::insert('grand_poll_groups',
				                        array('group_name' => $group,
				                              'collection_id' => $collection_id));
			    }

			    $j = 1;
			    while(isset($_POST["op0_$j"])){
				    $i = 0;
				    $options = array();
		
				    $question = $_POST["question_$j"];
				    $choices = $_POST["nChoices_$j"];
		
				    while(isset($_POST["op{$i}_$j"])){
					    if($_POST["op{$i}_$j"] != null){
						    $options[] = $_POST["op{$i}_$j"];
					    }
					    $i++;
				    }
		            DBFunctions::insert('grand_poll',
		                                array('collection_id' => $collection_id,
		                                      'poll_name' => $question,
		                                      'choices' => $choices));

		            $poll_id = DBFunctions::insertId();
				    foreach($options as $option){
				        DBFunctions::insert('grand_poll_options',
				                            array('option_name' => $option,
				                                  'poll_id' => $poll_id));
				    }
				    $j++;
			    }
			    header("Location: $wgServer$wgScriptPath/index.php?action=viewPoll&id=$collection_id");
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
				if($someQuestionsBlank){
					$wgMessage->addError("There was at least one question which is blank.");
				}
				if($noGroupsSelected){
					$wgMessage->addError("There were not user groups selected.");
				}
				CreatePoll::generateFormHTML($wgOut);
			}
		}
		else {
			CreatePoll::generateFormHTML($wgOut);
		}
	}
	
	function generateFormHTML($wgOut){
		global $wgUser, $wgServer, $wgScriptPath, $FORM_TEXT;
		$me = Person::newFromWgUser();
		$wgOut->addScript("<script type='text/javascript'>
					var opID = new Array();
					var qID = 2;
		
					opID[1] = 1;
		
					function addOption(which) {
 						var o = document.createElement('p');
 						o.id='op'.concat(opID).concat('_' + opID[which]);
 						var add = document.getElementById('add' + which);
 						var input = document.createElement('input');
 						input.type='text';
 						input.name='op'.concat(opID[which]).concat('_' + which);

 						o.appendChild(input);
 						add.parentNode.insertBefore(o, add);
 						opID[which]++;
					}
					
					function removeOption(which) {
						if(opID[which] > 1){
							opID[which]--;
							var o = document.getElementById('op'.concat(opID).concat('_' + opID[which]));
	 						o.parentNode.removeChild(o);
	 					}
					}
					
					function addQuestion() {
						opID[qID] = 1;
						var template = 
						    \"<fieldset id='q\" + qID + \"'>\" + 
                                \"<legend>Question \" + qID + \"</legend>\" +
                                \"<table>\" + 
	                                \"<tr>\" + 
		                                \"<td valign='top' align='right'>\" +
			                                \"<b>Question:</b>\" +
		                                \"</td>\" +
		                                \"<td>\" +
			                                \"<input type='text' name='question_\" + qID + \"' size='50'>\" +
		                                \"</td>\" +
	                                \"</tr>\" +
	                                \"<tr>\" +
		                                \"<td valign='top' align='right'>\" +
			                                \"<br>\" +
			                                \"<b>Options:</b>\" +
		                                \"</td>\" +
		                                \"<td>\" +
			                                \"<br>\" +
			                                \"<input type='text' name='op0_\" + qID + \"'><br>\" +
			                                \"<p id='add\" + qID + \"'>\" +
				                                \"<a href='javascript:addOption(\" + qID + \");'>[Add Option]</a>&nbsp;&nbsp;&nbsp;<a href='javascript:removeOption(\" + qID + \");'>[Remove Option]</a>\" +
			                                \"</p>\" +
		                                \"</td>\" +
	                                \"</tr>\" +
	                                \"<tr>\" +
	                                    \"<td><b>Number of choices:</b></td>\" +
	                                    \"<td><input type='number' name='nChoices_\" + qID + \"' min='1' max='8' value='1' style='width:30px;' />\" +
	                                \"</tr>\" +
                                \"</table>\" +
                            \"</fieldset>\";
 						$(template).insertBefore(addQ).parent();
 						qID++;
					}
					
					function removeQuestion() {
						if(qID > 2){
							qID--;
							$('#q' + qID).remove();
	 					}
					}
				</script>");
		
		$wgOut->addHTML("<form action='$wgServer$wgScriptPath/index.php/Special:CreatePoll' method='post'>
					<table>
						<tr>
							<td align='right' valign='top'>
								<b>Poll Name:</b>
							</td>
							<td>
								<input type='text' name='name' size='50' />
								<br />
							</td>
						</tr>
						<tr>
							<td align='right' valign='top'>
								<b>Description:</b>
							</td>
							<td>
								<textarea name='description' style='height:200px;' /></textarea>
							</td>
						</tr>
						<tr>
							<td align='right' valign='top'>
								<b>Time Limit:</b>
							</td>
							<td>
								<input type='text' name='time' size='5' />
								<div class='prefsectiontip'>
									<p>Time Limit should be a number which represents the number of days to leave the poll open.  If it is left blank or is 0 then the poll will stay open indefinitely.</p>
								</div>
							</td>
						</tr>
						<tr>
							<td align='right' valign='top'>
								<b>Allow myself to vote:</b>
							</td>
							<td>
								<input type='radio' name='self' value='1' checked /> Yes<br />
								<input type='radio' name='self' value='0' /> No<br />
								<div class='prefsectiontip'>
									<p>If 'Yes' is selected, you will be able to vote in the poll just like anyone else.  If 'No' is selected, you will only be able to see the results of the poll.</p>
								</div>
							</td>
						</tr>
					</table>
					<script type='text/javascript'>
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
					</script>
					$FORM_TEXT
					<p id='addQ'>
						<a href='javascript:addQuestion();'>[Add Question]</a>&nbsp;&nbsp;&nbsp;<a href='javascript:removeQuestion();'>[Remove Question]</a>
					</p>
					<table>
						<tr>
							<td align='right' valign='top'>
								<b>Poll Visibility:</b>
							</td>
							<td>");
		$groups = $me->getAllowedRoles();
		if($me->isRoleAtLeast(PL)){
		    $groups[] = PL;
		}
		$groups = array_unique($groups);
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
		
		$wgOut->addHTML("<table border='0' cellspacing='2' width='500'>
				<tr>
					<td colspan='3'><input type='checkbox' name='groups[]' value='all' /> All Users</td>\n");
			$i = 0;
			foreach($groups as $group){
				if($i % 3 == 0){
					$wgOut->addHTML("</tr><tr>\n");
				}
				$wgOut->addHTML("<td><input type='checkbox' name='groups[]' value='$group' /> $group</td>\n");
				$i++;
			}
			$wgOut->addHTML("</tr></table>\n");
		$wgOut->addHTML("				
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td>
								<input type='submit' name='submit' value='Submit' />
							</td>
						</tr>
					</table>
				</form>");
	}
}

?>
