<?php
require_once('AddMember.php');

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AddMember'] = 'AddMember'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AddMember'] = $dir . 'AddMember.i18n.php';
$wgSpecialPageGroups['AddMember'] = 'grand-tools';

function runAddMember($par) {
  AddMember::run($par);
}

class AddMember extends SpecialPage{

	function AddMember() {
		wfLoadExtensionMessages('AddMember');
		if(FROZEN){
		    SpecialPage::SpecialPage("AddMember", STAFF.'+', true, 'runAddMember');
	    }
	    else{
	        SpecialPage::SpecialPage("AddMember", CNI.'+', true, 'runAddMember');
	    }
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
		$user = Person::newFromId($wgUser->getId());
		if(isset($_GET['action']) && $_GET['action'] = "view" && $user->isRoleAtLeast(STAFF)){
			if(isset($_POST['submit']) && $_POST['submit'] == "Accept"){
			    $sendEmail = "false";
			    if(isset($_POST['wpEmail']) && $_POST['wpEmail'] != ""){
			        $sendEmail = "true";
			    }
                $_POST['wpSendMail'] = "$sendEmail";
			    $result = APIRequest::doAction('CreateUser', false);
			    if(strstr($result, "already exists") === false){
				    $uTable = getTableName("user_create_request");
				    $sql = "UPDATE $uTable 
					        SET `last_modified` = SUBDATE(CURRENT_TIMESTAMP, INTERVAL 5 SECOND),
			                    `staff` = '{$user->getName()}',
					            `created` = 'true'
					        WHERE `id` = '{$_POST['id']}'";
				    DBFunctions::execSQL($sql, true);
				}
			}
			else if(isset($_POST['submit']) && $_POST['submit'] == "Ignore"){
				$uTable = getTableName("user_create_request");
				$sql = "UPDATE $uTable 
				        SET `last_modified` = SUBDATE(CURRENT_TIMESTAMP, INTERVAL 5 SECOND),
		                    `staff` = '{$user->getName()}',
				            `ignore` = 'true'
				        WHERE `id` = '{$_POST['id']}'";
				DBFunctions::execSQL($sql, true);
				$wgMessage->addSuccess("User '{$_POST['wpName']}' Ignored");
			}
			AddMember::generateViewHTML($wgOut);
		}
		else if(!isset($_POST['submit'])){
			// Form not entered yet
			AddMember::generateFormHTML($wgOut);
		}
		else{
			// The Form has been entered
			if(isset($_POST['wpNS'])){
			    $nss = implode(", ", $_POST['wpNS']);
			}
			else{
			    $nss = "";
			}
			if(isset($_POST['wpUserType'])){
			    $types = implode(", ", $_POST['wpUserType']);
			}
			else{
			    $types = "";
			}
			
			$_POST['wpFirstName'] = str_replace(" ", "-", preg_replace('/\s\s+/', ' ', trim($_POST['wpFirstName'])));
			$_POST['wpLastName'] = str_replace(" ", "-", preg_replace('/\s\s+/', ' ', trim($_POST['wpLastName'])));
			
			$_POST['wpName'] = ucfirst(strtolower($_POST['wpFirstName'])).".".ucfirst(strtolower($_POST['wpLastName']));
			$_POST['wpRealName'] = "{$_POST['wpFirstName']} {$_POST['wpLastName']}";
			$_POST['wpUserType'] = $types;
			$_POST['wpNS'] = $nss;
			$_POST['user_name'] = $user->getName();
            APIRequest::doAction('RequestUser', false);
            AddMember::generateFormHTML($wgOut);
		}
	}
	
	function generateViewHTML($wgOut){
		global $wgScriptPath, $wgServer;
		$history = false;
		if(isset($_GET['history']) && $_GET['history'] == true){
		    $history = true;
		}
		if($history){
		    $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:AddMember?action=view'>View New Requests</a><br /><br />
		                <table id='requests' style='display:none;background:#ffffff;text-align:center;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
					    <thead><tr bgcolor='#F2F2F2'>
						    <th>Requesting User</th> <th>User Name</th> <th>Timestamp</th> <th>Staff</th> <th>Email</th> <th>User Type</th> <th>Projects</th> <th>Status</th>
					    </tr></thead><tbody>\n");
		}
		else{
		    $wgOut->addHTML("<a href='$wgServer$wgScriptPath/index.php/Special:AddMember?action=view&history=true'>View History</a><br /><br />
		                <table id='requests' style='display:none;background:#ffffff;text-align:center;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
					    <thead><tr bgcolor='#F2F2F2'>
						    <th>Requesting User</th> <th>User Name</th> <th>Timestamp</th> <th>Email</th> <th>User Type</th> <th>Projects</th> <th>Accept</th> <th>Ignore</th>
					    </tr></thead><tbody>\n");
		}
	
		$uTable = getTableName("user_create_request");
		if($history){
		    $sql = "SELECT *
			        FROM $uTable
			        WHERE `created` = 'true'
			        OR `ignore` = 'true'
			        ORDER BY last_modified DESC";
		}
		else{
		    $sql = "SELECT *
			        FROM $uTable
			        WHERE `created` = 'false'
			        AND `ignore` = 'false'";
        }
		$data = DBFunctions::execSQL($sql);
		foreach($data as $row){
		    $req_user = Person::newFromName($row['requesting_user']);
		    $projects = array();
		    $roles = array();
		    if($req_user->getProjects() != null){
		        foreach($req_user->getProjects() as $project){
		            $projects[] = $project->getName();
		        }
		    }
		    if($req_user->getRoles() != null){
		        foreach($req_user->getRoles() as $role){
		            $roles[] = $role->getRole();
		        }
		    }
			$wgOut->addHTML("<tr>
						<td align='left'><a target='_blank' href='{$req_user->getUrl()}'>{$req_user->getName()}</a><br />
						<b>Roles:</b> ".implode(",", $roles)."<br />
						<b>Projects:</b> ".implode(",", $projects)."</td> <td align='left'>{$row['wpName']}</td> <td>{$row['last_modified']}</td>");
			if($history){
			    $wgOut->addHTML("<td>{$row['staff']}</td>");
			}
			$wgOut->addHTML("<td align='left'> {$row['wpEmail']}</td> <td>{$row['wpUserType']}</td> <td align='left'>{$row['wpNS']}</td> 
						<form action='$wgServer$wgScriptPath/index.php/Special:AddMember?action=view' method='post'>
							<input type='hidden' name='id' value='{$row['id']}' />
							<input type='hidden' name='wpName' value='{$row['wpName']}' />
							<input type='hidden' name='wpEmail' value='{$row['wpEmail']}' />
							<input type='hidden' name='wpRealName' value='{$row['wpRealName']}' />
							<input type='hidden' name='wpUserType' value='{$row['wpUserType']}' />
							<input type='hidden' name='wpNS' value='{$row['wpNS']}' />
                            <input type='hidden' name='wpSendMail' value='true' />");
			if($history){
		        if($row['created'] == "true"){
		            $wgOut->addHTML("<td>Accepted</td>");
		        }
		        else{
		            $wgOut->addHTML("<td>Ignored</td>");
		        }
		    }
		    else{
			    $wgOut->addHTML("<td><input type='submit' name='submit' value='Accept' /></td> <td><input type='submit' name='submit' value='Ignore' /></td>");
			}
			$wgOut->addHTML("</form>
					</tr>");
		}
		$wgOut->addHTML("</tbody></table><script type='text/javascript'>
		                                    $('#requests').dataTable().fnSort([[2,'desc']]);
		                                    $('#requests').css('display', 'table');
		                                 </script>");
	}
	
	function generateFormHTML($wgOut){
		global $wgUser, $wgServer, $wgScriptPath, $wgRoles;
		$user = Person::newFromId($wgUser->getId());
		$first = @str_replace("'", "&#39;", $_POST['wpFirstName']);
		$last = @str_replace("'", "&#39;", $_POST['wpLastName']);
		$email = @str_replace("'", "&#39;", $_POST['wpEmail']);
		if($user->isRoleAtLeast(STAFF)){
	        $wgOut->addHTML("<b><a href='$wgServer$wgScriptPath/index.php/Special:AddMember?action=view'>View Requests</a></b><br /><br />");
	    }
	    $wgOut->addHTML("Adding a member to the forum will allow them to access content relevant to the user roles and projects which are selected below.  By selecting projects, the user will be automatically added to the projects on the forum, and subscribed to the project mailing lists.  The new user's email must be provided as it will be used to send a randomly generated password to the user.  After pressing the 'Submit Request' button, an administrator will be able to accept the request.  If there is a problem in the request (ie. there was an obvious typo in the name), then you may be contacted by the administrator about the request.<br /><br />");
		$wgOut->addHTML("<form action='$wgScriptPath/index.php/Special:AddMember' method='post'>\n");
		$wgOut->addHTML("<table>
					<tr>
						<td class='mw-label'><label for='wpFirstName'>First Name:</label></td>
						<td class='mw-input'>
							&nbsp;&nbsp;<input type='text' class='loginText' name='wpFirstName' id='wpFirstName'
								tabindex='1'
								value='$first' size='20' /> 
						</td>
					</tr>
					<tr>
						<td class='mw-label'><label for='wpLastName'>Last Name:</label></td>
						<td class='mw-input'>
							&nbsp;&nbsp;<input type='text' class='loginText' name='wpLastName' id='wpLastName'
								tabindex='1'
								value='$last' size='20' /> 
						</td>
					</tr>
					<tr>
					</tr>
					<tr>
						<td class='mw-label'><label for='wpEmail'>Email:</label></td>
						<td class='mw-input'>
							&nbsp;&nbsp;<input type='text' class='loginText' name='wpEmail' id='wpEmail'
								tabindex='1'
								value='$email' size='20' />
						</td>
					</tr>
					<tr>
						<td class='mw-label'><label for='wpType'>User Roles:</label></td>
						<td class='mw-input'>");
        foreach($wgRoles as $role){
            if($user->isRoleAtLeast($role)){
                $wgOut->addHTML("&nbsp;<input type='checkbox' name='wpUserType[]' value='$role' />$role<br />\n");
            }
        }
		$wgOut->addHTML("</td>
					</tr>
					<tr>
						<td class='mw-label'><label for='wpNs'>Associated Projects:</label></td>
						<td class='mw-input'>");

			$rows = array();
			$projects = Project::getAllProjects();
			$rows = array();
			foreach($projects as $project){
			    $rows[] = $project->getName();
			}
			
			$nPerCol = ceil(count($rows)/3);
			$remainder = count($rows) % 3;
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
				if(isset($rows[$i])){
					$col1[] = $rows[$i];
				}
				if(isset($rows[$j]) && $j < $jEnd){
					$col2[] = $rows[$j];
				}
				if(isset($rows[$k]) && $k < $kEnd){
					$col3[] = $rows[$k];
				}
				$j++;
				$k++;
			}
			
			$rows = array();
			$i = 0;
			foreach($col1 as $row){
				if(isset($col1[$i])){
					$rows[] = $col1[$i];
				}
				if(isset($col2[$i])){
					$rows[] = $col2[$i];
				}
				if(isset($col3[$i])){
					$rows[] = $col3[$i];
				}
				$i++;
			}
			
			$wgOut->addHTML("<table border='0' cellspacing='2' width='500'>
				<tr>\n");
			$i = 0;
			foreach($rows as $row){
				if($i % 3 == 0){
					$wgOut->addHTML("</tr><tr>\n");
				}
				$wgOut->addHTML("<td><input type='checkbox' name='wpNS[]' value='{$row}' /> {$row}</td>\n");
				$i++;
			}
			$wgOut->addHTML("</tr></table>\n");
			$wgOut->addHTML("<tr>
						<td class='mw-label'></td>
						<td class='mw-input'>
							<input type='submit' name='submit' value='Submit Request' />
						</td>
					</tr>");
			$wgOut->addHTML("</td></tr></table\n");
		$wgOut->addHTML("</form>\n");
	}
	
	function parse($text){
		$text = str_replace("'", "&#39;", $text);
		$text = str_replace("\"", "&quot;", $text); 
		return $text;
	}
}

?>
