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
		SpecialPage::SpecialPage("LoiProposals", HQP.'+', true, 'runLoiProposals');
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
        else if(isset($_POST['Submit']) && $_POST['Submit'] == "Submit LOI Comments"){
        	$error = 0;
        	foreach($_POST['loi_ids'] as $loi_id){
                $loi = LOI::newFromId($loi_id);
                $man_comments_orig = $loi->getManagerComments();

                if(isset($_POST['manager_comments-'.$loi_id])){
                    $man_comments_new = $_POST['manager_comments-'.$loi_id];
                	
                	if($man_comments_new == $man_comments_orig){
                		continue;
                	}

                	$man_comments_new = mysql_real_escape_string($man_comments_new);
                	$sql = "UPDATE grand_loi
                        	SET manager_comments='{$man_comments_new}'
                        	WHERE id={$loi_id}";

	                $res = DBFunctions::execSQL($sql, true);
	                if($res != 1){
	                	$error = 1;
	                }
                }
            }

            if($error == 0){
            	$wgMessage->addSuccess("Your comments have been saved successfully!");
            }else{
            	$wgMessage->addWarning("There was a problems saving your comments.");
            }
        }

	    $wgOut->setPageTitle("LOI Proposals");
	    $wgOut->addHTML("");
	    $html ="
	    <div id='ackTabs'>
        <ul>";

        if($me->isRole(RMC) || $me->isRole(MANAGER) || $me->isRole(STAFF)){
			$html .="
            <li><a href='#lois'>LOI Proposals</a></li>
            <li><a href='#lois_res'>LOI Responses</a></li>
            <li><a href='#cv'>CV</a></li>
            <li><a href='#conflicts'>Conflicts/Preferences</a></li>
            <li><a href='#reportsTbl'>Report Stats</a></li>";
        }
		else if($me->isRoleAtLeast(HQP)){
			$html .="
            <li><a href='#lois_public'>LOI Proposals</a></li>
            <li><a href='#lois_res'>LOI Responses</a></li>";
		}

		// if($me->isRole(MANAGER) || $me->isRole(STAFF)){
		// 	$html .="<li><a href='#reportsTbl'>Report Stats</a></li>";
		// }
        
        $html .="</ul>";
        
        if($me->isRole(RMC) || $me->isRole(MANAGER) || $me->isRole(STAFF)){
			$html .= "<div id='lois' style='width: 100%; position:relative; overflow: scroll;'>";
			$html .= LoiProposals::loiTable();
			$html .= "</div>";

			$html .= "<div id='lois_res' style='width: 100%; position:relative; overflow: scroll;'>";
			$html .= LoiProposals::loiResTable();
			$html .= "</div>";

			$html .= "<div id='cv' style='width: 100%; overflow: scroll;'>";
			$html .= LoiProposals::cvTable();
			$html .= "</div>";

			$html .= "<div id='conflicts' style='width: 100%; overflow: scroll;'>";
			$html .= LoiProposals::conflictsTable();
			$html .= "</div>";

			$html .= "<div id='reportsTbl' style='width: 100%; position:relative; overflow: scroll;'>";
			$html .= LoiProposals::loiReportsTable();
			$html .= "</div>";

		}
		else if($me->isRoleAtLeast(HQP)){
			$html .= "<div id='lois_public' style='width: 100%; position:relative; overflow: scroll;'>";
			$html .= LoiProposals::loiPublicTable();
			$html .= "</div>";

			$html .= "<div id='lois_res' style='width: 100%; position:relative; overflow: scroll;'>";
			$html .= LoiProposals::loiResTable();
			$html .= "</div>";
		}

		// if($me->isRole(MANAGER) || $me->isRole(STAFF)){
		// 	$html .= "<div id='reportsTbl' style='width: 100%; position:relative; overflow: scroll;'>";
		// 	$html .= LoiProposals::loiReportsTable();
		// 	$html .= "</div>";
		// }


		$html .=<<<EOF
		<style type='text/css'>
			.qtipStyle{
                font-size: 14px;
                line-height: 120%;
                padding: 5px;
            }
		</style>
		<script type='text/javascript'>
			function openDialog(ev_id, sub_id, num){
	            $('#dialog'+num+'-'+ev_id+'-'+sub_id).dialog("open");
	        }
            $(document).ready(function(){
            	$('span.q_tip').qtip({
	                position: {
	                    corner: {
	                        target: 'topRight',
	                        tooltip: 'bottomLeft'
	                    }
	                }, 
	                style: {
	                    classes: 'qtipStyle'
	                }
           	 	});
            	$('.comment_dialog').dialog( "destroy" );
            	$('.comment_dialog').dialog({ autoOpen: false, width: 600, height: 400 });

                $('.loiindexTable').dataTable({
                	"bAutoWidth": false,
                	"iDisplayLength": 25
    			});
				$('.cvindexTable').dataTable({
                	"bAutoWidth": false,
                	"iDisplayLength": 100
    			});
				$('.conflIndexTable').dataTable({
                	"bAutoWidth": false,
                	"iDisplayLength": 100
    			});
				$('.loiReportsTable').dataTable({
                	"bAutoWidth": false,
                	"iDisplayLength": 25
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
	    <table class='loiindexTable' style='background:#ffffff; width: 100%; table-layout: auto; text-align: left;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
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
			
			//Lead name
			$lead_arr = explode("<br />", $row['lead'], 2);
			$lead_person = Person::newFromNameLike($lead_arr[0]);
			if($lead_person->getId()){
				$lead = "<a href='".$lead_person->getUrl()."'>".$lead_person->getNameForForms() ."</a>";
			}
			else{
				$lead = $lead_arr[0];
			}
			if(isset($lead_arr[1])){
				$lead .= "<br />".$lead_arr[1];
			}


			//Co-lead name
			$colead_arr = explode("<br />", $row['colead'], 2);
			//echo $name . ": ". $row['colead']."<br>";
			$colead_person = Person::newFromNameLike($colead_arr[0]);

			if($colead_person->getId()){
				$colead = "<a href='".$colead_person->getUrl()."'>".$colead_person->getNameForForms() ."</a>";
			}
			else{
				$colead = $colead_arr[0];
			}
			if(isset($colead_arr[1])){
				$colead .= "<br />".$colead_arr[1];
			}

			//Champion name
			//$champion = $row['champion'];
			$champion_arr = explode("<br />", $row['champion'], 2);
			//echo $name . ": ". $row['colead']."<br>";
			$champion_person = Person::newFromNameLike($champion_arr[0]);

			if($champion_person->getId()){
				$champion = "<a href='".$champion_person->getUrl()."'>".$champion_person->getNameForForms() ."</a>";
			}
			else{
				$champion = $champion_arr[0];
			}
			if(isset($champion_arr[1])){
				$champion .= "<br />".$champion_arr[1];
			}


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

	static function loiResTable(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;

		$html =<<<EOF
		<h2>GRAND NCE – Phase 2 Research Projects – LOI Responses</h2>
		<p>
        At the close of LOI Submissions in June, GRAND had received fifty-five separate LOIs, of which thirty-five were submitted as "full project" LOIs. All of these fifty-five LOI Submissions have now been reviewed by GRAND's Research Management Committee, and the next stage in the process will be focused on shaping the best of this proposed research into a portfolio of research projects that spans the seven GRAND Challenges. The target is for GRAND to have 20- 25 research projects as we movie into Phase 2.
        </p>
        
        <p>
        There are a number of possible approaches to moving from 55 LOI Submissions to 20-25 projects. There are not sufficient funds available for GRAND to fund all the research proposed in these 55 LOI Submissions, so clearly some difficult selections will have to be made. Rather than have all such selections made by the Research Management Committee, the LOI Response round will encourage project teams to make some of the difficult selections regarding the proposed research that should move forward, and how that research should best be structured within projects.
        </p>
        
        <p>
        At this stage, no LOI Submission has been "accepted" as a new project, and none has been rejected as having no place in the Phase 2 research program. Each LOI Submission is receiving feedback, and each of those teams is invited to be involved in the development of an LOI Response. However, there will be a limited number of LOI Responses accepted, and each LOI Response must describe a full project.
        </p>
        
        <p>
        Below is an initial approximation or draft outline of the Phase 2 research project portfolio. The purpose of this initial approximation is to provide some guidance to the project teams of the LOI Submissions regarding how they might fit into the final set of 20-25 projects that will form GRAND's Phase 2 research project portfolio. The LOI Responses will be new submissions, each under a primary challenge. There are 22 separate project slots in the draft outline, and it is expected that there will be no more than 25 submissions of LOI Responses to fill these slots. These new submissions should incorporate the feedback received from the initial LOI Submissions, as well as reflect to some extent the suggested groupings that are included in the draft outline.
        </p>
        
        <p>
        Each LOI Submission is included below. In these lists/groupings, parentheses are used to indicate those LOI Submissions that were submitted as subprojects. Groups from the collections of LOI Submissions are encouraged to construct LOI Responses that include the best representations of the proposed research from that group, and to make an initial attempt to structure it as a coherent project. Where an LOI Submission is not being asked to merge, this may not be a reflection on the quality of the LOI Submission but only that it is the only LOI Submission that covers a research area that is considered significant for GRAND. These will still be expected to submit an LOI Response that fully addresses the problems and concerns raised in the feedback in order to be considered for project status under Phase 2.
        </p>
        
        <p>
        For each of the challenge areas, there are two RMC representatives who will serve as points of contact for LOI Responses under that challenge area, should any issues arise.
        </p>
        
        <p>
        The current outline for the Phase 2 research project portfolio, based on the 55 LOI Submissions, is as follows:
        </p>
        
        <p>
        <u>BIG DATA (RMC Contacts: Sean Gouglas; Vic DiCiccio)</u>
        Expected Number of Projects: 3<br />
        Suggested LOI Submission combinations to form the LOI Responses
        <ol>
        <li>AVID; LPD; (COMMCAR)</li>
        <li>BIG; SENSEMAKING; (ICARE)</li>
        <li>HUMAN</li>
        </ol>
        </p>
        <br />
        <p>
        <u>WORK (RMC Contacts: Abby Goodrum; Bart Simon)</u>
        Expected Number of Projects: 3<br />
        Suggested LOI Submission combinations to form the LOI Responses
        <ol>
        <li>CRI; DIGIPUB; (ENOW); (SNETS)</li>
        <li>MAKE; SHARE; SYNTHO;</li>
        <li>ENGAGE; INDIEGAME; (WATERLOO)</li>
        </ol>
        </p>
        <br />
        <p>
        <u>SUSTAINABILITY (RMC Contacts: Rob Woodbury; Kellogg Booth)</u>
        Expected Number of Projects: 2<br />
        Suggested that the following pool LOI Submissions recombine into two LOI Responses<br />
        BUSY; NMSPS; PRIDES; (MIDEASS)
        </p>
        <br />
        <p>
        <u>ENTERTAINMENT (RMC Contacts: Pierre Poulin; Eugene Fiume)</u>
        Expected Number of Projects: 4<br />
        Suggested that the following pool LOI Submissions recombine into four LOI Responses<br />
        BELIEVE2; COLT; CREATE; DATUM; MOVITA2; (MEASURE); (SYNTHHGEN)
        </p>
        <br />
        <p>
        <u>HEALTHCARE (RMC Contacts: Diane Gromala; Beverly Harrison)</u>
        Expected Number of Projects: 3<br />
        Suggested LOI Submission combinations to form the LOI Responses
        <ol>
        <li>ARSURG; HLTHSIM2; SYNTHH</li>
        <li>INCLUDE2; (SOCIABLE)</li>
        <li>CPRM2; G4HLTH; (TAMP)</li>
        </ol>
        </p>
        <br />
        <p>
        <u>CITIZENSHIP (RMC Contacts: Sam Trosow; Catherine Middleton)</u>
        Expected Number of Projects: 2*<br />
        Suggested LOI Submission combinations to form the LOI Responses
        <ol>
        <li>NEWS2; PRIVLIT; (TRUST)</li>
        <li>USENET; (NIND); (SMARTMAIN); (DIGIHOUSE)</li>
        <li>*. (INFPO) [This * is a placeholder for a future project focused on "Informing Digital Media Policy"]</li>
        </ol>
        </p>
        <br />
        <p>
        <u>LEARNING (RMC Contacts: Jen Jenson; Carl Gutwin)</u>
        Expected Number of Projects: 3<br />
        Suggested that the following pool LOI Submissions recombine into three LOI Responses<br />
        COGS; COORDN8; EXPERT; KIDZ; RILDIM
        </p>
        <br />
        <p>
        <u>OTHER (RMC Contact: Vic DiCiccio)</u>
        Expected Number of Projects: 1<br />
        Suggested that the following pool of LOI Submissions recombine into one LOI Response<br />
        (CUSTOM); (MOBIS); (PROGRES)
        </p>
        <br />
        <p>
        <u>LOOKING FOR A HOME</u>
        The onus is on each of these LOI Submissions to find an appropriate LOI Response that will be a reasonable fit for the proposed research and that will accept it as a subproject.<br />
        (GLOBALCHILD); (LISTEN); (VICTOR)
        </p>
EOF;
		
		return $html;
	}

	static function loiPublicTable(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;

		$html =<<<EOF
	    <table class='loiindexTable' style='background:#ffffff; width: 100%; table-layout: auto; text-align: left;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
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
		
			//Lead name
			$lead_arr = explode("<br />", $row['lead'], 2);
			$lead_person = Person::newFromNameLike($lead_arr[0]);
			if($lead_person->getId()){
				$lead = "<a href='".$lead_person->getUrl()."'>".$lead_person->getNameForForms() ."</a>";
			}
			else{
				$lead = $lead_arr[0];
			}
			if(isset($lead_arr[1])){
				$lead .= "<br />".$lead_arr[1];
			}


			//Co-lead name
			$colead_arr = explode("<br />", $row['colead'], 2);
			//echo $name . ": ". $row['colead']."<br>";
			$colead_person = Person::newFromNameLike($colead_arr[0]);

			if($colead_person->getId()){
				$colead = "<a href='".$colead_person->getUrl()."'>".$colead_person->getNameForForms() ."</a>";
			}
			else{
				$colead = $colead_arr[0];
			}
			if(isset($colead_arr[1])){
				$colead .= "<br />".$colead_arr[1];
			}

			//Champion name
			//$champion = $row['champion'];
			$champion_arr = explode("<br />", $row['champion'], 2);
			//echo $name . ": ". $row['colead']."<br>";
			$champion_person = Person::newFromNameLike($champion_arr[0]);

			if($champion_person->getId()){
				$champion = "<a href='".$champion_person->getUrl()."'>".$champion_person->getNameForForms() ."</a>";
			}
			else{
				$champion = $champion_arr[0];
			}
			if(isset($champion_arr[1])){
				$champion .= "<br />".$champion_arr[1];
			}

			
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
		<style type="text/css">
		#conflicts .dataTables_wrapper {
			width: 500px;
		}
		</style>
	    <table class='conflIndexTable' style='background:#ffffff; table-layout: auto; text-align: left;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
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
                    <th width="40%">LOI Name</th>
                    <th width="30%">Conflict</th>
                    <th width="30%">Preference</th>
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

	static function loiReportsTable(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;

		$html =<<<EOF
		<form id='submitForm' action='$wgServer$wgScriptPath/index.php/Special:LoiProposals' method='post'>
	    <table class='loiReportsTable' style='background:#ffffff; width: 100%; table-layout: auto; text-align: left;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
            <thead>
                <tr bgcolor='#F2F2F2'>
                    <th width="10%">LOI</th>
                    <td style='padding:0px;'>
                    <table width='100%' rules='all'>
                    <tr>
		            <td width="15%"><b>Evaluator</b></td>
		            <td width=5%"><span class="q_tip" title="Was this submitted as a full project?">Q1</span></td>
		            <td width=5%"><span class="q_tip" title="Should this be considered as a full project?">Q2</span></td>
		            <td width=5%"><span class="q_tip" title="Is the proposed title appropriate?">Q3</span></td>
		            <td width=5%"><span class="q_tip" title="Does the summary description accurately describe the research?">Q4</span></td>
		            <td width=5%"><span class="q_tip" title="Are the proposed leader(s) and champion appropriate?">Q5</span></td>
		            <td width=5%"><span class="q_tip" title="Part A: Are the proposed receptors and partners appropriate?">Q6</span></td>
		            <td width=5%"><span class="q_tip" title="Part B: Are there missing linkages to other projects or LOIs?">Q7</span></td>
		            <td width=5%"><span class="q_tip" title="Part C: Is the proposed research team appropriate?">Q8</span></td>
		            <td width=5%"><span class="q_tip" title="Part D-­F: Is the proposed research appropriate for GRAND?">Q9</span></td>
		            <td width=5%"><span class="q_tip" title="Part G: Is the proposed KTEE activity appropriate?">Q10</span></td>
		            <td width=5%"><span class="q_tip" title="Part H: Is the proposed networking with other projects appropriate?">Q11</span></td>
		            <td width=5%"><span class="q_tip" title="Part I: Is the proposed engagement with partners appropriate?">Q12</span></td>
		            <td width=5%"><span class="q_tip" title="Part J (1): Is the proposed primary impact area appropriate?">Q13</span></td>
		            <td width=5%"><span class="q_tip" title="Part J (2): Are the proposed secondary impact areas appropriate?">Q14</span></td>
		            <td width=5%"><span class="q_tip" title="Comments only to the RMC: Do you think this LOI should be accepted?">Q15</span></td>
		            <!--<td width=5%"><span>PDF</span></td>-->
		            </tr>
		            </table>
		            </td>
                </tr>
            </thead>
            <tbody>
EOF;

		$questions = array();
		

		$question_text = array(
			"Was this submitted as a full project?",
			"Should this be considered as a full project?",
			"Is the proposed title appropriate?",
			"Does the summary description accurately describe the research?",
			"Are the proposed leader(s) and champion appropriate?",
			"Part A: Are the proposed receptors and partners appropriate?",
			"Part B: Are there missing linkages to other projects or LOIs?",
			"Part C: Is the proposed research team appropriate?",
			"Part D-­F: Is the proposed research appropriate for GRAND?",
			"Part G: Is the proposed KTEE activity appropriate?",
			"Part H: Is the proposed networking with other projects appropriate?",
			"Part I: Is the proposed engagement with partners appropriate?",
			"Part J (1): Is the proposed primary impact area appropriate?",
			"Part J (2): Are the proposed secondary impact areas appropriate?",
			"Comments only to the RMC: Do you think this LOI should be accepted?"
		);

		$evals = Person::getAllPeople(RMC); 
		$lois = LOI::getAllLOIs();

		$me = Person::newFromId($wgUser->getId());
		$editable = $me->isRole(MANAGER);
		$disabled = (!$editable)? 'disabled="disabled"' : '';

		foreach($lois as $loi){
			$loi_id = $loi->getId();
			$loi_name = $loi->getName();
			$evals = $loi->getEvaluators();
			$evals_count = count($evals);


			$admin = Person::newFromId(4); //Owner of this report
	        $report = new DummyReport("LOIEvalReportPDF", $admin, $loi);
	        $check = $report->getPDF();
	        $pdf_link = "";
	        if(count($check) > 0){
	            $tok = $check[0]['token'];
	            $pdf_link = "<br /><a target='downloadIframe' class='' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf=$tok'>Download PDF</a>";
	        }

			if($evals_count > 0){
				$html .=<<<EOF
				<tr>
					<td>{$loi_name}{$pdf_link}</td>
					<td style="padding:0px;">
					<table width='100%' rules='all'>
EOF;
				$first = true;		
				foreach($evals as $eval){
					
					$eval_id = $eval->getId();
					$eval_name = $eval->getNameForForms();
					$html .= "<tr><td width='15%'>{$eval_name}</td>";

					for ($q=1; $q<=15; $q++){
						$yes_no = LoiProposals::getData(BLOB_ARRAY, RP_EVAL_LOI, $q, EVL_LOI_YN, $eval_id, REPORTING_YEAR, $loi_id);
						$comment = LoiProposals::getData(BLOB_TEXT,RP_EVAL_LOI, $q, EVL_LOI_C, $eval_id, REPORTING_YEAR, $loi_id);

						if(is_array($yes_no) && !empty($yes_no)){
							$yes_no = reset($yes_no);
						}else{
							$yes_no = "";
						}
						
						$yn = "";
						if($yes_no == "Not Specified"){
							$yn = "NS";
						}
						else if(!empty($yes_no)){
							$yn = substr($yes_no, 0,1);
						}
						

						if(empty($yn) && !empty($comment)){
							$yn = "T";
						}

						$cell = "";
						$q_text = $question_text[$q-1];
						if(!empty($yes_no) || !empty($comment)){
							$cell =<<<EOF
							<a href='#' onclick='openDialog("{$eval_id}", "{$loi_id}", {$q}); return false;'>{$yn}</a>
	                        <div id='dialog{$q}-{$eval_id}-{$loi_id}' class='comment_dialog' title='{$eval_name} on {$loi_name}: {$q_text}'>
	                        <h4>{$yes_no}</h4>
	                        <h5>Text Comment:</h5>
	                        {$comment}
	                        </div>
EOF;
						}


						$html .= "<td width='5%'>{$cell}</td>";
					
					}

					/*
					$report = new DummyReport("EvalLOIReportPDF", $eval, $loi);
					
        			$check = $report->getPDF();
        			$pdf = "";
        			if (count($check) > 0) {
		        		$tok = $check[0]['token']; 	
		        		$tst = $check[0]['timestamp'];
		        		$sub = $check[0]['submitted'];
		        		$pdf = "<a id='' target='downloadIframe' class='' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf=$tok'>PDF</a>";
		        	}
					
					$html .= "<td width='5%'>{$pdf}</td>";
					*/
					
					$html .= "</tr>";
				}

				//Manager Comments
				$man_comments = $loi->getManagerComments();
				if($editable){
					$textarea = "<textarea {$disabled} name='manager_comments-{$loi_id}' style='height:40px;'>{$man_comments}</textarea>";
				}
				else{
					$textarea = "<p><i>{$man_comments}</i></p>";
				}

				$html .=<<<EOF
					<tr>
					<td><b>Comments:</b></td>
					<td colspan="16">
					<input type="hidden" name="loi_ids[]" value="{$loi_id}" />
					{$textarea}
					</td>
					</tr>
EOF;

				$html .= "</table></td></tr>";
			}
			
		}

		$html .=<<<EOF
		</tbody>
		</table>
		<br />
EOF;

		if($editable){
    		$html .= '<input type="submit" name="Submit" value="Submit LOI Comments" />';
    	}

    	$html .= "</form>";
		

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

	static function getData($blob_type, $rptype, $question, $subitem, $eval_id=0, $evalYear=EVAL_YEAR, $proj_id=0){

        $addr = ReportBlob::create_address($rptype, SEC_NONE, $question, $subitem);
        $blb = new ReportBlob($blob_type, $evalYear, $eval_id, $proj_id);
        
        $data = "";
       
        $result = $blb->load($addr);
        
        $data = $blb->getData();
        
        return $data;
    }
}

?>