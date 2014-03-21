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

	    $revision = 1;
	    if(isset($_GET['revision']) && intval($_GET['revision'])!=0){
	    	$revision = $_GET['revision'];
	    }

		if (isset($_GET['getpdf'])) {
			$filename = $_GET['getpdf'];
			$filepath = "/local/data/www-root/grand_forum/data/loi_proposals/loi/{$revision}/{$filename}";
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

		if(isset($_GET['ajaxtab']) && $_GET['ajaxtab']=="4"){
			$wgOut->disable();
            ob_clean();
			$html = LoiProposals::cvTable($revision);
			echo $html;
			exit;
		}
		/*else if(isset($_GET['ajaxtab']) && $_GET['ajaxtab']=="5"){
			$wgOut->disable();
            ob_clean();
			$html = LoiProposals::conflictsTable($revision);
			echo $html;
			exit;
		}*/
		else if(isset($_GET['ajaxtab']) && $_GET['ajaxtab']=="6"){
			$wgOut->disable();
            ob_clean();
			$html = LoiProposals::loiReportsTable($revision);
			echo $html;
			exit;
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

        if($revision == 2){
	        if($me->isRole(RMC) || $me->isRole(MANAGER) || $me->isRole(STAFF) || $me->isRole(ISAC)){
				$html .="
	            <li><a href='#lois'>Proposals</a></li>";
	        }
			else if($me->isRoleAtLeast(HQP)){
				$html .="
	            <li><a href='#lois_public'>Proposals</a></li>";
			}
		}
		else{
			if($me->isRole(RMC) || $me->isRole(MANAGER) || $me->isRole(STAFF)){
				$html .="
	            <li><a href='#lois'>Proposals</a></li>
	            <li><a href='#lois_res'>Responses</a></li>
	            <li><a href='#faq'>FAQ</a></li>
	            <li><a href='{$wgServer}{$wgScriptPath}/index.php/Special:LoiProposals?ajaxtab=4'>CV</a></li>
	            <!--<li><a href='{$wgServer}{$wgScriptPath}/index.php/Special:LoiProposals?ajaxtab=5'>Conflicts/Preferences</a></li>-->
	            <li><a href='{$wgServer}{$wgScriptPath}/index.php/Special:LoiProposals?ajaxtab=6'>Report Stats</a></li>";
	        }
			else if($me->isRoleAtLeast(HQP)){
				$html .="
	            <li><a href='#lois_public'>Proposals</a></li>
	            <li><a href='#lois_res'>Responses</a></li>
	            <li><a href='#faq'>FAQ</a></li>";
			}
		}
		
        
        $html .=<<<EOF
        	</ul>
        	<div id='spinner' style='display:none; padding:25px;'>
        	<img src='{$wgServer}{$wgScriptPath}/skins/Throbber.gif' /> Please wait while the content is being loaded...
        	</div>
EOF;
		
		if($revision == 2){
        
	        if($me->isRole(RMC) || $me->isRole(MANAGER) || $me->isRole(STAFF) || $me->isRole(ISAC)){
				$html .= "<div id='lois' style='position:relative; overflow: auto;'>";
				$html .= LoiProposals::loiTable($revision);
				$html .= "</div>";

			}
			else if($me->isRoleAtLeast(HQP)){
				$html .= "<div id='lois_public' style='width: 100%; position:relative; overflow: scroll;'>";
				$html .= LoiProposals::loiPublicTable($revision);
				$html .= "</div>";

			}
		}
		else{
			if($me->isRole(RMC) || $me->isRole(MANAGER) || $me->isRole(STAFF)){
				$html .= "<div id='lois' style='position:relative; overflow: auto;'>";
				$html .= LoiProposals::loiTable($revision);
				$html .= "</div>";

				$html .= "<div id='lois_res' style='position:relative; overflow: auto;'>";
				$html .= LoiProposals::loiResTable();
				$html .= "</div>";
				
				$html .= "<div id='faq' style='position:relative; overflow: auto;'>";
				$html .= LoiProposals::loiFAQ();
				$html .= "</div>";

				$html .= "<div id='cv' style='width: 100%; overflow: auto;'>";
				//$html .= LoiProposals::cvTable();
				$html .= "</div>";

				$html .= "<div id='conflicts' style='width: 100%; overflow: auto;'>";
				//$html .= LoiProposals::conflictsTable();
				$html .= "</div>";

				$html .= "<div id='reportsTbl' style='width: 100%; position:relative; overflow: auto;'>";
				//$html .= LoiProposals::loiReportsTable();
				$html .= "</div>";

			}
			else if($me->isRoleAtLeast(HQP)){
				$html .= "<div id='lois_public' style='width: 100%; position:relative; overflow: scroll;'>";
				$html .= LoiProposals::loiPublicTable($revision);
				$html .= "</div>";

				$html .= "<div id='lois_res' style='width: 100%; position:relative; overflow: scroll;'>";
				$html .= LoiProposals::loiResTable();
				$html .= "</div>";

				$html .= "<div id='faq' style='width: 100%; position:relative; overflow: scroll;'>";
				$html .= LoiProposals::loiFAQ();
				$html .= "</div>";
			}
		}


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
                $('#ackTabs').tabs({
      //           	beforeLoad: function( event, ui ) {
				  //       ui.jqXHR.error(function() {
				  //         ui.panel.html("Couldn't load this tab. We'll try to fix this as soon as possible.");
				  //       });
						// ui.jqXHR.beforeSend(function() {
				  //         ui.panel.html("");
				  //       });
						// ui.jqXHR.complete(function() {
				  //         ui.panel.html("");
				  //       });
				  //   },
				    ajaxOptions: {
			            error: function(xhr, status, index, anchor) {
			                $(anchor.hash).html();
			            },
			            beforeSend: function() {
			                $('#spinner').show();
			            },
			            complete: function() {
			                $("#spinner").hide();
			            }
			        }
                });
            });
        </script>
EOF;
		$wgOut->addHTML($html);
	}

	static function loiTable($revision=1){
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

		$query = "SELECT * FROM grand_loi WHERE year=2013 AND revision={$revision}";
		$data = DBFunctions::execSQL($query);
		foreach($data as $row){
			$name 	= $row['name'];
			$full_name = $row['full_name'];
			$type = $row['type'];
			$related_loi = $row['related_loi'];
			$description = $row['description'];
			
			if($revision == 1){
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
			}
			else{
				//Lead name
				//$lead_arr = explode("<br />", $row['lead'], 2);
				$lead_person = Person::newFromNameLike($row['lead']);
				if($lead_person->getId()){
					$lead = "<a href='".$lead_person->getUrl()."'>".$lead_person->getNameForForms() ."</a>";
					if($lead_person->getUni()){
						$lead .= "<br />".$lead_person->getUni();
					}
				}
				else{
					$lead = $row['lead'];
				}
				
			}

			if($revision == 1){
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
			}
			else{
				$colead_arr = explode("<br />", $row['colead'], 2);
				$colead = "";
				foreach($colead_arr as $p){
					$colead_person = Person::newFromNameLike($p);

					if($colead_person->getId()){
						$colead .= "<a href='".$colead_person->getUrl()."'>".$colead_person->getNameForForms() ."</a>";
						if($colead_person->getUni()){
							$colead .= "<br />".$colead_person->getUni();
						}
					}
					else{
						$colead .= $p;
					}
					$colead .= "<br /><br />";	
				}
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
				$loi_pdf = "<a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:LoiProposals?revision={$revision}&getpdf={$loi_pdf}'>{$loi_pdf}</a>";
			}else{
				$loi_pdf = "N/A";
			}

			if(!empty($supplemental_pdf)){
				$supplemental_pdf = "<a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:LoiProposals?revision={$revision}&getpdf={$supplemental_pdf}'>{$supplemental_pdf}</a>";
			}else{
				$supplemental_pdf = "N/A";
			}

			if($revision == 2){
				$rel_lbl = "Initial LOI Submission(s)";
			}else{
				$rel_lbl = "Related LOI(s)";
			}
			$html .=<<<EOF
				<tr>
				<td width="13%">
				<b>{$name}:</b><br /><br />
				{$full_name}<br />
				<b>{$rel_lbl}: </b>{$related_loi}
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
EOF;

			if($revision != 2){
				$html .=<<<EOF
				<p>
				<b>Secondary:</b><br />
				{$secondary_challenge}
				</p>
EOF;
			}

			$html .=<<<EOF
				</td>
				<td>
				<b>LOI: {$loi_pdf}</b>
EOF;
			if($revision != 2){
				$html .=<<<EOF
				<br /><br />
				<b>Supplemental: {$supplemental_pdf}</b>
EOF;
			}

			$html .=<<<EOF
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

	static function loiFAQ(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;

		$html =<<<EOF
		<h2>GRAND Renewal Process FAQ</h2>
		<p>
		Just remember, as you go through the LOI process for Phase2 of the GRAND NCE, that as Dr. Benjamin Spock told parents in the opening sentence of his The Common Sense Book of Baby and Child Care, "You know more than you think you do." Usually common sense will guide you to the right answer. But when that doesn't work, here are some additional pointers that may be of use.
		</p>
		<p>
		Q: <i>Why are we being asked to consider merging or rearranging our research proposals (LOIs) into larger projects?</i>
		<br />
		A: During Phase 1 of GRAND we found that having over thirty projects created too much administrative overhead for researchers and for the network, so we are moving to a smaller number of larger projects, but we are also introducing formal subprojects. The expectation is that subprojects will be small enough to provide a sharp focus on specific research problems and the larger projects will provide sufficient scope to include a coherent set of activities that do not spill across project boundaries as much as they did in Phase 1.
		</p>

		<p>
		Q: <i>Not everything that we are proposing in our LOI falls under a single GRAND Challenge. Does our project have to be grouped into one of the seven themes that align with the Challenges?</i>
		<br />
		A: Yes. In Phase 1, GRAND's five themes provided a framework for establishing a strong research base but did not provide an effective way to mobilize the network to focus on receptor needs (in other words, themes looked at GRAND in terms of capacity to solve problems, not in terms of problems being solving). In Phase 2, GRAND will move to receptor-driven projects, so the theme structure is being changed to recognize this using the seven GRAND Challenges. Each subproject usually makes a significant contribution to only one challenge. Projects are organized into themes based on aggregate contributions of its subprojects even though many projects are likely to have a subproject whose primary contribution is to the challenge for another theme. This continues the experience in Phase 1, where network investigators were asked to identify and report on linkages between projects. In Phase 2, this should be easier to do because this will happen at the subproject level.
		</p>

		<p>
		Q: <i>Does the new project structure mean less funding for our research?</i>
		<br />
		A: No. The current planning is for an annual budget of $4.2M that will be allocated to network investigators across approximately 21 projects during Phase 2 of GRAND. For comparison, the budget for Phase 1 was $3.8M annually. Adjusting for inflation, this is approximately constant funding for network investigators involved in project-based research. At the project level, these numbers would lead to average annual funding of $200K per project, but there will be some variance in this, perhaps in the range of $100K to $300K of NCE funds. The total NCE funds will of course have to sum to $4.2M over all projects. There is no limit on the amount of cash that projects can spend if it comes from partners.
		</p>

		<p>
		Q: <i>Are we only supposed to have one project champion per project?</i>
		<br />
		A: No. You should be looking for multiple project champions. This has not changed from Phase 1. We asked for only one champion on the first page of the LOIs because if an LOI did not have at least one champion, it was game over. But every subproject eventually needs to have a champion and some will have more than one, so every project will definitely have multiple champions. The champion could be the same for multiple subprojects (or not). Part A has lots of room to list partner organizations, each of which will eventually need to have a person who is a project champion if the organization is to fully qualify as a receptor partner. If you know the name of a person who is or is likely to become a project champion, include that as well as the name of the organization in Part A. (To save space, don't bother listing that person again as a champion in Part B because you probably want to reserve that space for the principal researchers on your project).
		</p>

		<p>
		Q: <i>What is the role of a project champion?</i>
		<br />
		A: The LOI template explains some of the expectations of a project champion: "a researcher or practitioner who works in the receptor community and who has an involvement in the planning and execution of a research project. The project champion's organization does not have to be a project partner, but the organization does have to permit the project champion's involvement. Involvement in a project by a champion can be as a research collaborator, a potential user of the results of the research, a mentor or advisor to the researchers, or someone who will assess and critique the project from the perspective of the receptor community. If a project is approved, one or more project champions will be expected to provide annual assessments of the project to the RMC [Research Management Committee]."
		</p>
		<p>
		The new template for LOI Responses has a much shorter version of this: "There must be at least one Project Champion personally involved in planning and carrying out the project who is affiliated with a current or potential GRAND Partner drawn from the receptor community." This is correct, but doesn’t have as much detail as did the version in the LOI template.
		</p>
		<p>
		The role of a project champion was described fairly succinctly in the 2011 GRAND Researcher Guide (<a href="http://grand-nce.ca/research/researcher-guide">http://grand-nce.ca/research/researcher-guide</a>):
		</p>
		<blockquote style="font-style:normal;">
		<b>PROJECT CHAMPION:</b> Individuals that represent a PARTNER organization within a PROJECT. PROJECT CHAMPION roles may take many forms, but they must demonstrate sustained involvement in the project and real benefits flowing from project to the PARTNER and vice-versa. PROJECT CHAMPIONS are expected to serve as a liaison between RESEARCHERS on a project and the RECEPTOR COMMUNITY and perform reporting duties for GRAND.
		</blockquote>

		<p>
		Q: <i>I work closely with an international researcher with whom I collaborate a lot and we regularly publish together. Was I right to list this person as a project champion?</i>
		<br />
		A: No. There is a difference between a project champion and a research collaborator. A project champion will exploit the results of the research in some way beyond being a collaborator in the research or being someone who just builds on the research with more research. Exploitation might be commercialization, but it could also be using the results to set public policy, or incorporating knowledge gained into practice in a professional or educational setting, etc. International research collaborators can be listed as researchers, but not as champions, unless they are likely to provide cash contributions to the project.
		</p>

		<p>
		Q: <i>Are we supposed to get commitments of cash and in-kind from partners even though we do not yet know whether our LOI will be accepted?</i>
		<br />
		A: No. You are not expected to have solid commitments from partners at this stage. You should, however, be having discussions about this because in Phase 2 the expectations for partner engagement are increasing over what they were in Phase 1, and this includes higher expectations for how much cash funding each project will receive from partners over the five years. So it is more important this time around that there be projects in GRAND that meet the needs of partners in the receptor community. The goal is to finalize the list of projects by October 1, and also name the project leaders and co-leaders. At that point it will be appropriate to start in-depth discussions with partners about cash and in-kind contributions. These commitments will need to be documented in the renewal application that GRAND submits to the NCE Program on June 11, 2014.
		</p>

		<p>
		Q: <i>How do we know which subprojects to include in our projects if we haven't yet confirmed who are partners are?</i>
		<br />
		A: Good question! There is indeed a chicken-and-egg issue here. We are trying to encourage a hybrid approach whereby projects are organized in collaboration with receptor partners but we also realize that potential partners may want to know what the researchers will be doing before they commit their time to helping refine the project. The list of accepted LOIs (projects) will be announced by October 1. There will still be opportunities to refine the subprojects through the end of December, ideally by matching them up with receptor partners. That is when the subproject structure for each project will need to be fully described as part of the annual reporting process that will also determine the funding allocations for new projects that will commence on April 1.
		</p>

		<p>
		Q: <i>How do we respond to requests in the feedback we got on our LOI for more detail when we have limited space and we are trying to describe multiple subprojects that resulted from merging two or more LOIs into a single project LOI? To be specific, our LOI was told "Overall, all the subprojects require more details and greater specificity. Describe Who will do What and How it will be done. Describe the anticipated specific research outcomes and how they will be assessed/measured. Describe who will care enough about each subproject and how those partners will contribute to the success of the subproject and what they will gain specifically."</i>
		<br />
		A: Wow, this is a lot to explain in not very much space. Fortunately, you do not have to explain all of the subprojects, just those that are representative of the direction the project is taking. In the limited space you have for the six subprojects you can make best use of the space by (for example) listing the last names of the researchers, and the names of partner organizations right after the title of the subproject (or in the opening line of text) in parentheses to minimize space. You could also put this in Part A by indicating for each partner the subprojects they are involved in. For timelines, we did not mean a day-by-day timeline, but more something like "2 years" vs. "six months" if the subprojects have limited extent. What we need is an idea of the scope of the subprojects and how they fit into the larger project. You can also integrate some of this into Part E or the other parts (where relevant), so long as it gets covered some place. Especially if some of the receptors are going to benefit from multiple subprojects, put this in Part E or in Part I or even Part G. Similarly, if you wish to describe more than six subprojects, put that in Part E or you could even double up in Part F with two per section.
		</p>
		<b><i>August 28, 2013</i></b>

		<p>
		Q: <i>If Phase 2 projects are expected to have significant cash contributions from partners, doesn't that disadvantage projects that are entirely SSHRC-oriented and favor projects that are entirely NSERC-oriented?</i>
		<br />
		A: Yes. But we should not have projects that are entirely SSHRC-oriented or entirely NSERC-oriented. In Phase 1 we expected many of or projects to include both NSERC and SSHRC components. In Phase 2, this is even more the case, for two reasons. The first reason is that the larger project size means there is more opportunity to have both NSERC-oriented subprojects and SSHRC-oriented subprojects that look at different aspects of the same problems. The second reason is that during Phase 1 we saw a lot of relationships develop within the network that demonstrate the value of integrating NSERC and SSHRC research activities, so we should be building on that experience and constructing Phase 2 projects so they are more highly multidisciplinary.
 			<br />
			One of the assessment criteria for projects is the degree to which they gain value from being in GRAND. There are only a few ways to demonstrate this: having a mix of NSERC and SSHRC researchers in a project, having a mix of universities involved in a project, and having interactions with new receptor partners that came through GRAND (having funding from GRAND is not one of the ways to demonstrate value from being in GRAND!).
		</p>
		<b><i>September 3, 2013</i></b>
EOF;
		
		return $html;
	}


	static function loiPublicTable($revision=1){
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

		$query = "SELECT * FROM grand_loi WHERE year=2013 AND revision={$revision}";
		$data = DBFunctions::execSQL($query);
		foreach($data as $row){
			$name 	= $row['name'];
			$full_name = $row['full_name'];
			$type = $row['type'];
			$related_loi = $row['related_loi'];
			$description = $row['description'];

			if($revision == 1){
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
			}
			else{
				//Lead name
				//$lead_arr = explode("<br />", $row['lead'], 2);
				$lead_person = Person::newFromNameLike($row['lead']);
				if($lead_person->getId()){
					$lead = "<a href='".$lead_person->getUrl()."'>".$lead_person->getNameForForms() ."</a>";
					if($lead_person->getUni()){
						$lead .= "<br />".$lead_person->getUni();
					}
				}
				else{
					$lead = $row['lead'];
				}
				
			}

			if($revision == 1){
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
			}
			else{
				$colead_arr = explode("<br />", $row['colead'], 2);
				$colead = "";
				foreach($colead_arr as $p){
					$colead_person = Person::newFromNameLike($p);

					if($colead_person->getId()){
						$colead .= "<a href='".$colead_person->getUrl()."'>".$colead_person->getNameForForms() ."</a>";
						if($colead_person->getUni()){
							$colead .= "<br />".$colead_person->getUni();
						}
					}
					else{
						$colead .= $p;
					}
					$colead .= "<br /><br />";	
				}
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

			if($revision == 2){
				$rel_lbl = "Initial LOI Submission(s)";
			}else{
				$rel_lbl = "Related LOI(s)";
			}

			$html .=<<<EOF
				<tr>
				<td width="13%">
				<b>{$name}:</b><br /><br />
				{$full_name}<br />
				<b>{$rel_lbl}: </b>{$related_loi}
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
EOF;

			if($revision != 2){
				$html .=<<<EOF
				<p>
				<b>Secondary:</b><br />
				{$secondary_challenge}
				</p>
EOF;
			}

			$html .=<<<EOF
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

	static function cvTable($revision=1){
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
			<script type="text/javascript">
			$(document).ready(function(){
				$('.conflIndexTable').dataTable({
	            	"bAutoWidth": false,
	            	"iDisplayLength": 25
				});
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
			});
			</script>
EOF;

		return $html;

	}

	static function conflictsTable($revision=1){
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
				  LEFT JOIN grand_eval_conflicts lc ON(l.id = lc.sub_id AND lc.eval_id={$my_id}) 
				  WHERE l.year=2013 AND l.revision={$revision} AND lc.type='LOI'";
		
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
			<script type="text/javascript">
			$(document).ready(function(){
				$('.cvindexTable').dataTable({
	            	"bAutoWidth": false,
	            	"iDisplayLength": 25
				});
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
			});
			</script>
EOF;

		return $html;

	}

	static function loiReportsTable($revision=1){
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
		$lois = LOI::getAllLOIs(REPORTING_YEAR, $revision);

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
							<a href='#' onclick='openDialog2("{$eval_id}", "{$loi_id}", {$q}); return false;'>{$yn}</a>
	                        <div style="display:none;" id='dialog{$q}-{$eval_id}-{$loi_id}' class='comment_dialog' title='{$eval_name} on {$loi_name}: {$q_text}'>
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
		
		$html .=<<<EOF
		<script type="text/javascript">
		function openDialog2(ev_id, sub_id, num){
			$('#dialog'+num+'-'+ev_id+'-'+sub_id).dialog( "destroy" );
            $('#dialog'+num+'-'+ev_id+'-'+sub_id).dialog({ autoOpen: false, width: 600, height: 400 });
	        $('#dialog'+num+'-'+ev_id+'-'+sub_id).dialog("open");
	    }
		function setDialogs(){
			$('.comment_dialog').dialog( "destroy" );
            $('.comment_dialog').dialog({ autoOpen: false, width: 600, height: 400 });
		}

		$(document).ready(function(){
			$('.loiReportsTable').dataTable({
            	"bAutoWidth": false,
            	"iDisplayLength": 25
			});
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
			 //setDialogs();
		});
		</script>
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
