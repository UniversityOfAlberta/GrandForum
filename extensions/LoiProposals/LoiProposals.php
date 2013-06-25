<?php
$dir = dirname(__FILE__) . '/';

//$wgHooks['UnknownAction'][] = 'getack';

$wgSpecialPages['LoiProposals'] = 'LoiProposals';
$wgExtensionMessagesFiles['LoiProposals'] = $dir . 'LoiProposals.i18n.php';
$wgSpecialPageGroups['LoiProposals'] = 'grand-tools';

function runLoiProposals($par) {
	LoiProposals::run($par);
}

class LoiProposals extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('LoiProposals');
		SpecialPage::SpecialPage("LoiProposals", CNI.'+', true, 'runLoiProposals');
	}
	
	static function run(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
		
	    $me = Person::newFromId($wgUser->getId());

		if (isset($_GET['getpdf'])) {
			$filename = $_GET['getpdf'];
			$filepath = "/local/data/www-root/grand_forum/data/loi_proposals/loi/{$filename}";
			//echo $filepath;
			if (file_exists($filepath)) {
				$wgOut->disable();
            	ob_clean();
            	//flush();
			    header("Pragma: public");
	            header("Expires: 0");
	            header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	            header("Content-Type: application/force-download");
	            header("Content-Type: application/octet-stream");
	            header("Content-Type: application/download");
			    header('Content-Disposition: attachment; filename='.$filename);
			    header('Content-Transfer-Encoding: binary');
			   
			    readfile($filepath);
			    exit;
			}	
		}
		else if (isset($_GET['getcvpdf'])) {
			$filename = $_GET['getcvpdf'];
			$filepath = "/local/data/www-root/grand_forum/data/loi_proposals/cv/{$filename}";
			//echo $filepath;
			if (file_exists($filepath)) {
				$wgOut->disable();
            	ob_clean();
            	//flush();
			    header("Pragma: public");
	            header("Expires: 0");
	            header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	            header("Content-Type: application/force-download");
	            header("Content-Type: application/octet-stream");
	            header("Content-Type: application/download");
			    header('Content-Disposition: attachment; filename='.$filename);
			    header('Content-Transfer-Encoding: binary');
			   
			    readfile($filepath);
			    exit;
			}	
		}

		if(isset($_POST['Submit']) && $_POST['Submit'] == "Submit LOI Preferences"){
            if(isset($_POST['reviewer_id'])){
            	$reviewer_id = $_POST['reviewer_id'];
            	$error = 0;
                foreach($_POST['loi_ids'] as $loi_id){
                    if(isset($_POST['conflict_'.$loi_id]) && $_POST['conflict_'.$loi_id]){
                        $conflict = 1;
                    }
                    else{
                        $conflict = 0;
                    }

                    if(isset($_POST['preference_'.$loi_id]) && $_POST['preference_'.$loi_id]){
                        $preference = 1;
                    }
                    else{
                        $preference = 0;
                    }

                    $sql = "INSERT INTO grand_loi_conflicts(reviewer_id, loi_id, conflict, preference) 
                            VALUES('{$reviewer_id}', '{$loi_id}', '$conflict', '$preference' ) 
                            ON DUPLICATE KEY UPDATE conflict='{$conflict}', preference='{$preference}'";

                    $res = DBFunctions::execSQL($sql, true);
                    if($res != 1){
                    	$error = 1;
                    }
                }
                if($error == 0){
                	$wgMessage->addSuccess("Your preferences have been saved successfully!");
                }else{
                	$wgMessage->addWarning("There was a problems saving your preferences.");
                }

            }
        }

	    $wgOut->setPageTitle("LOI Proposals");
	    $wgOut->addHTML("");
	    $html ="
	    <div id='ackTabs'>
        <ul>";

        if($me->isRoleAtLeast(RMC)){
			$html .="
            <li><a href='#lois'>LOI Proposals</a></li>
            <li><a href='#cv'>CV</a></li>
            <li><a href='#conflicts'>Conflicts/Preferences</a></li>";
        }
		else if($me->isRoleAtLeast(CNI)){
			$html .="
            <li><a href='#lois_public'>LOI Proposals</a></li>";
		}
        
        $html .="</ul>";
        
        if($me->isRoleAtLeast(RMC)){
			$html .= "<div id='lois' style='width: 100%; position:relative; overflow: scroll;'>";
			$html .= LoiProposals::loiTable();
			$html .= "</div>";

			$html .= "<div id='cv' style='width: 100%; overflow: scroll;'>";
			$html .= LoiProposals::cvTable();
			$html .= "</div>";

			$html .= "<div id='conflicts' style='width: 100%; overflow: scroll;'>";
			$html .= LoiProposals::conflictsTable();
			$html .= "</div>";

		}
		else if($me->isRoleAtLeast(CNI)){
			$html .= "<div id='lois_public' style='width: 100%; position:relative; overflow: scroll;'>";
			$html .= LoiProposals::loiPublicTable();
			$html .= "</div>";
		}
		//$html .= "</div";

		$html .=<<<EOF
		<script type='text/javascript'>
            $(document).ready(function(){
                $('.loiindexTable').dataTable({
                	"bAutoWidth": false,
                	"iDisplayLength": 25
    			});
				$('.cvindexTable').dataTable({
                	"bAutoWidth": false,
                	"iDisplayLength": 100
    			});
                $('#ackTabs').tabs();
            });
        </script>
EOF;
		$wgOut->addHTML($html);
	}

	static function loiTable(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;

		$html =<<<EOF
	    <table class='loiindexTable' style='background:#ffffff; width: 100%; table-layout: auto;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
            <thead>
                <tr bgcolor='#F2F2F2'>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Lead</th>
                    <th>Co-Lead</th>
                    <th>Champion</th>
                    <th>Challenges</th>
                    <!--<th>Secondary Challenge</th>-->
                    <th>LOI Files</th>
                </tr>
            </thead>
            <tbody>
EOF;

		$query = "SELECT * FROM grand_loi WHERE year=2013";
		$data = DBFunctions::execSQL($query);
		foreach($data as $row){
			$name 	= $row['name'];
			$full_name = $row['full_name'];
			$type = $row['type'];
			$related_loi = $row['related_loi'];
			$description = $row['description'];
			$lead 	= $row['lead'];
			$colead = $row['colead'];
			$champion = $row['champion'];
			$primary_challenge = $row['primary_challenge'];
			$secondary_challenge = $row['secondary_challenge'];
			$loi_pdf = $row['loi_pdf'];
			$supplemental_pdf = $row['supplemental_pdf'];
			if(!empty($loi_pdf)){
				$loi_pdf = "<a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:LoiProposals?getpdf={$loi_pdf}'>{$loi_pdf}</a>";
			}else{
				$loi_pdf = "N/A";
			}

			if(!empty($supplemental_pdf)){
				$supplemental_pdf = "<a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:LoiProposals?getpdf={$supplemental_pdf}'>{$supplemental_pdf}</a>";
			}else{
				$supplemental_pdf = "N/A";
			}

			$html .=<<<EOF
				<tr>
				<td width="13%">
				<b>{$name}:</b><br /><br />
				{$full_name}<br />
				<b>Related LOI: </b>{$related_loi}
				</td>
				
				<td>{$type}</td>
				<td>{$description}</td>
				<td>{$lead}</td>
				<td>{$colead}</td>
				<td>{$champion}</td>
				<td>
				<p>
				<b>Primary:</b><br />
				{$primary_challenge}
				</p>
				<p>
				<b>Secondary:</b><br />
				{$secondary_challenge}
				</p>
				</td>
				<!--<td>{$secondary_challenge}</td>-->
				<td>
				<b>LOI: {$loi_pdf}</b><br /><br />
				<b>Supplemental: {$supplemental_pdf}</b>
				</td>
				</tr>
EOF;
		}
		
		$html .=<<<EOF
			</tbody>
			</table>
EOF;

		return $html;

	}

	static function loiPublicTable(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;

		$html =<<<EOF
	    <table class='loiindexTable' style='background:#ffffff; width: 100%; table-layout: auto;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
            <thead>
                <tr bgcolor='#F2F2F2'>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Lead</th>
                    <th>Co-Lead</th>
                    <!--<th>Champion</th>-->
                    <th>Challenges</th>
                </tr>
            </thead>
            <tbody>
EOF;

		$query = "SELECT * FROM grand_loi WHERE year=2013";
		$data = DBFunctions::execSQL($query);
		foreach($data as $row){
			$name 	= $row['name'];
			$full_name = $row['full_name'];
			$type = $row['type'];
			$related_loi = $row['related_loi'];
			$description = $row['description'];
			$lead 	= $row['lead'];
			$colead = $row['colead'];
			$champion = $row['champion'];
			$primary_challenge = $row['primary_challenge'];
			$secondary_challenge = $row['secondary_challenge'];
			/*
			$loi_pdf = $row['loi_pdf'];
			$supplemental_pdf = $row['supplemental_pdf'];
			if(!empty($loi_pdf)){
				$loi_pdf = "<a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:LoiProposals?getpdf={$loi_pdf}'>{$loi_pdf}</a>";
			}else{
				$loi_pdf = "N/A";
			}

			if(!empty($supplemental_pdf)){
				$supplemental_pdf = "<a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:LoiProposals?getpdf={$supplemental_pdf}'>{$supplemental_pdf}</a>";
			}else{
				$supplemental_pdf = "N/A";
			}
			*/

			$html .=<<<EOF
				<tr>
				<td width="13%">
				<b>{$name}:</b><br /><br />
				{$full_name}<br />
				<b>Related LOI: </b>{$related_loi}
				</td>
				
				<td>{$type}</td>
				<td>{$description}</td>
				<td>{$lead}</td>
				<td>{$colead}</td>
				<!--td>{$champion}</td-->
				<td>
				<p>
				<b>Primary:</b><br />
				{$primary_challenge}
				</p>
				<p>
				<b>Secondary:</b><br />
				{$secondary_challenge}
				</p>
				</td>
				
				</tr>
EOF;
		}
		
		$html .=<<<EOF
			</tbody>
			</table>
EOF;

		return $html;

	}

	static function cvTable(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;

		$html =<<<EOF
	    <table class='cvindexTable' style='background:#ffffff; table-layout: auto;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
            <thead>
                <tr bgcolor='#F2F2F2'>
                    <th>Researcher Name</th>
                    <th>Download CV</th>
                </tr>
            </thead>
            <tbody>
EOF;

		$query = "SELECT * FROM grand_researcher_cv WHERE year=2013";
		$data = DBFunctions::execSQL($query);
		foreach($data as $row){
			$researchername 	= $row['researcher_name'];
			$cv_pdf = $row['filename'];
			
			if(!empty($cv_pdf)){
				$cv_pdf = "<a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:LoiProposals?getcvpdf={$cv_pdf}'>{$cv_pdf}</a>";
			}else{
				$cv_pdf = "N/A";
			}

			$html .=<<<EOF
				<tr>
				<td>{$researchername}</td>
				<td>{$cv_pdf}</td>
				</tr>
EOF;
		}
		
		$html .=<<<EOF
			</tbody>
			</table>
EOF;

		return $html;

	}

	static function conflictsTable(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
		$my_id = $wgUser->getId();

		$html =<<<EOF
		<form id='submitForm' action='$wgServer$wgScriptPath/index.php/Special:LoiProposals' method='post'>
	    <table class='cvindexTable' style='background:#ffffff; width: 100%; table-layout: auto;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
            <thead>
                <tr bgcolor='#F2F2F2'>
                    <th>LOI Name</th>
                    <th>Conflict</th>
                    <th>Preference</th>
                </tr>
            </thead>
            <tbody>
EOF;

		$query = "SELECT l.id, l.name, l.full_name, lc.*
				  FROM grand_loi l 
				  LEFT JOIN grand_loi_conflicts lc ON(l.id = lc.loi_id AND lc.reviewer_id={$my_id}) 
				  WHERE l.year=2013";
		
		$data = DBFunctions::execSQL($query);
		foreach($data as $row){
			$name 	= $row['name'];
			$loi_id = $row['id'];
			$conflict = (empty($row['conflict']))? 0 : $row['conflict'];
			$conflict_chk = ($conflict)? 'checked="checked"' : '';

			$preference = (empty($row['preference']))? 0 : $row['preference'];
			$preference_chk = ($preference)? 'checked="checked"' : '';
		

			$html .=<<<EOF
				<tr>
				<td width="13%">
				<b>{$name}:</b>
				<input type="hidden" name="loi_ids[]" value="{$loi_id}" />
				</td>
				
				<td><input type="checkbox" name="conflict_{$loi_id}" value='1' {$conflict_chk} /></td>
				<td><input type="checkbox" name="preference_{$loi_id}" value='1' {$preference_chk} /></td>
				
				</tr>
EOF;
		}
		
		$html .=<<<EOF
			</tbody>
			</table>
			<br />
			<input type="hidden" name="reviewer_id" value="{$my_id}" />
        	<input type="submit" name="Submit" value="Submit LOI Preferences" />
			</form>
EOF;

		return $html;

	}

	// static function createTab(){
	// 	global $notifications, $wgServer, $wgScriptPath;
		
		
	// 	$selected = "";
		
	// 	    echo "<li class='top-nav-element $selected'>\n";
	// 	    echo "	<span class='top-nav-left'>&nbsp;</span>\n";
	// 	    echo "	<a id='lnk-notifications' class='top-nav-mid' href='$wgServer$wgScriptPath/index.php/Special' class='new'>My&nbsp;Notifications</a>\n";
	// 	    echo "	<span class='top-nav-right'>&nbsp;</span>\n";
		
	// }

}

?>