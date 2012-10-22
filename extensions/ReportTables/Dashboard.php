<?php

function compareLastNames($a, $b){
    $aname = preg_split('/\./', $a->getName(), 2);
    $alname = (isset($aname[1]))? $aname[1] : "";

    $bname = preg_split('/\./', $b->getName(), 2);
    $blname = (isset($bname[1]))? $bname[1] : "";

    return strcmp($alname, $blname);
}

class Dashboard{

	//Static helper functions
	
	//Get an array of HQP based on filters provided
	static function getHQP($project, $ni, $position="all", $startRange = false, $endRange = false){
		$hqp_objs = array();
        
		if($project instanceof Project){
			$hqp_objs = $project->getAllPeopleDuring("HQP", $startRange, $endRange);
		}else if($ni instanceof Person){
			// Times out due to performance: $hqp_objs = Person::getAllPeopleDuring("HQP"); 
		    //Alternative to above:
		    $hqp_objs = $ni->getHQPDuring($startRange, $endRange); 
        }
		
		$hqps = array();
        foreach ($hqp_objs as $h){
            $supervisor = ($ni instanceof Person)? $ni->relatedTo($h, 'Supervises') : true;
            if($supervisor){
				$hqp_university = $h->getUniversity(); 
                $hqp_position = $hqp_university['position'];
                $hqp_position = ( empty($hqp_position) || $hqp_position=="Unknown" )? "Other" : $hqp_position;
				if($position != "all" && $position != $hqp_position){
					continue;
				}
                
                $hqps[] = $h;  
			}
		}	
		return $hqps;
	}
    
    static function filterHQP($hqps, $position){
        
        $filtered_hqps = array();
        
        foreach ($hqps as $h){
            $hqp_university = $h->getUniversity(); 
            $hqp_position = $hqp_university['position'];
            $hqp_position = ( empty($hqp_position) )? "Other" : $hqp_position;
            if($position != "all" && $position != $hqp_position){
                continue;
            }
           
            $filtered_hqps[] = $h;
        }
        return $filtered_hqps;
    }
    
    //
	static function hqpDetails($hqps){   
        global $wgServer, $wgScriptPath;
        $url_prefix = "$wgServer$wgScriptPath/index.php/";
        
        $hqp_type_map = array(
            "Masters Student" => "MSc",
            "PhD Student" => "PhD",
            "Undergraduate" => "Ugrad"
        );
        
        usort($hqps, "compareLastNames");
        $html = "";    
        foreach($hqps as $h){
            $hqp_name = $h->getName();
            $hqp_name_read = preg_split('/\./', $hqp_name, 2);
            $hqp_name_read = @$hqp_name_read[1].", ".@$hqp_name_read[0];
            
            $hqp_university = $h->getUniversity(); 
            $hqp_uni = ($hqp_university['university'])? ", ".$hqp_university['university'] : "";
            $hqp_type = $hqp_university['position'];
            if (empty($hqp_type)){
                $hqp_type = ", Unknown";
            }    
            else if(isset($hqp_type_map[$hqp_type])){
                $hqp_type = ", ".$hqp_type_map[$hqp_type];
            }else{
                 $hqp_type = ", ".$hqp_type;
            } 
            
            $role = ($h->isHQP())? "HQP" : (($h->isCNI())? "CNI" : (($h->isPNI())? "PNI" : ""));
            if($h->isActive() && $role != ""){
                $html.=<<<EOF
                    <li>
                    <a target='_blank' href='{$url_prefix}{$role}:{$hqp_name}'>
                    $hqp_name_read</a>{$hqp_uni}{$hqp_type}
                    </li>
EOF;
            }
            else{
                $html.=<<<EOF
                    <li>
                    $hqp_name_read (Inactive)
                    </li>
EOF;
            }     
        }
        return $html; 
    }
    

    static function niDetails($hqps){   
        global $wgServer, $wgScriptPath;
        $url_prefix = "$wgServer$wgScriptPath/index.php/";
        
        //Getting Report BloBS
        $rptype = RP_RESEARCHER;
        $section = RES_MILESTONES;
        $item = RES_MIL_CONTRIBUTIONS;
        $subitem = 0;
        $blob_type = BLOB_ARRAY;
        $year = REPORTING_YEAR;

        $rep_addr = ReportBlob::create_address($rptype,$section,$item,$subitem);

        usort($hqps, "compareLastNames");
        $html = "";    
        foreach($hqps as $h){
            $hqp_name = $h->getName();
            $hqp_name_read = preg_split('/\./', $hqp_name, 2);
            $hqp_name_read = @$hqp_name_read[1].", ".@$hqp_name_read[0];
            
            $hqp_university = $h->getUniversity(); 
            $hqp_uni = ($hqp_university['university'])? ", ".$hqp_university['university'] : "";
            $hqp_type = $hqp_university['position'];
            if (empty($hqp_type)){
                $hqp_type = ", Unknown";
            }    
            else{
                 $hqp_type = ", ".$hqp_type;
            } 
            
            //GRAND time percentage
            $uid = $h->getId();
            $grand_activity_blob = new ReportBlob($blob_type, $year, $uid, 0);
            $grand_activity_blob->load($rep_addr);
            $grand_activity_arr = $grand_activity_blob->getData();
            $grand_percent = $grand_activity_arr['grand_percent'];
            $grand_percent = preg_replace('/%/', '', $grand_percent);
            $grand_percent = (is_numeric($grand_percent))? $grand_percent : 0;

            $role = ($h->isHQP())? "HQP" : (($h->isCNI())? "CNI" : (($h->isPNI())? "PNI" : ""));
            if($h->isActive() && $role != ""){
                $html.=<<<EOF
                    <li>
                    <a target='_blank' href='{$url_prefix}{$role}:{$hqp_name}'>
                    $hqp_name_read</a>, {$role}{$hqp_uni}{$hqp_type}, GRAND-related effort: {$grand_percent}%
                    </li>
EOF;
            }
            else{
                $html.=<<<EOF
                    <li>
                    $hqp_name_read (Inactive)
                    </li>
EOF;
            }     
        }
        return $html; 
    }

	//Get an array of Papers based on filters provided
	static function getPapers($project, $author, $category="all", $startRange = false, $endRange = false){
		$paper_objs = array();
        
		if($project instanceof Project){
			$paper_objs = $project->getPapers($category, $startRange, $endRange);
		}
		else if($author instanceof Person){
			$paper_objs = $author->getPapersAuthored($category, $startRange, $endRange);
		}
		
		$papers = array();
        foreach ($paper_objs as $p){
			$im_author = ($author instanceof Person)? $author->isAuthorOf($p) : true;
            if($im_author){
				$pap_date = substr($p->getDate(), 0, 4);
				if( $pap_date >= REPORTING_YEAR && $pap_date <= REPORTING_YEAR+2 ){
					$papers[] = $p;
				}else if( $pap_date == REPORTING_YEAR-1){
                    $papers[] = $p;
                }
			}
		}
		return $papers;
	}
    
    static function filterPapers($papers, $type="all", $status="all"){
        $filtered = array();
        
        foreach($papers as $p){
            $p_type = $p->getType();
            $p_status = $p->getStatus();
            if( ($type=="all" || $p_type == $type) && ($status == "all" || $status == $p_status) ){
                $filtered[] = $p;
            }
        }
        
        return $filtered;
    }
    
    static function paperDetails($papers){
        global $wgServer, $wgScriptPath;
        $url_prefix = "$wgServer$wgScriptPath/index.php/";
        
        $html = "";    
        foreach($papers as $p){
            if($p instanceof Paper){
                $pap_data = $p->getData();
                $event_title = "";
                if(isset($pap_data['event_title'])){
                    $event_title = "; Event Title: ".$pap_data['event_title'];
                }else if(isset($pap_data['book_title'])){
                    $event_title = "; Book Title: ".$pap_data['book_title'];
                }else if(isset($pap_data['journal_title'])){
                    $event_title = "; Journal Title: ".$pap_data['journal_title'];
                }
                
                $pap_authors = $p->getAuthors();    
                $author_arr = array();    
                foreach ($pap_authors as $pap_auth){
                    $author_arr[] = $pap_auth->getNameForForms();
                }
                $author_str = implode(',', $author_arr);
                
                $pap_title = $p->getTitle();
                $pap_wiki_title = $p->getWikiTitle();
                $pap_status = $p->getStatus();
                $html.=<<<EOF
                    <li>
                    <a target='_blank' href='{$url_prefix}Publication:{$p->getId()}'>
                    $pap_title
                    </a>$event_title; $pap_status; Authors: $author_str
                    </li>
EOF;
            }
        }
        return $html;
    }

    //This function takes either project or receiver. If both parameters are given, project dominates.
    static function getContributions($project, $receiver, $year=false){
        $contribution_objs = array();
        
        if($project instanceof Project){
            $contribution_objs = $project->getContributions();
            $project_contr = true;
        }
        else if($receiver instanceof Person){
            $contribution_objs = $receiver->getContributions();
            $person_contr = true;
        }
        
        $contributions = array();
        
        foreach ($contribution_objs as $c){
            $c_people = count(@$c->getPeople());
            $c_projects = count(@$c->getProjects());
            if( $c_people <= 0 || $c_projects <= 0){
                continue;
            }
            
            if($year === false || ($year == $c->getYear()) || ($year == REPORTING_YEAR && $c->getYear() == (REPORTING_YEAR+1)) ){
    			$im_receiver = ($receiver instanceof Person)? $receiver->isReceiverOf($c) : true;
                if($c->getTotal() > 0 && $im_receiver){
                    $contributions[] = $c; 
                }
            }
        }
        
        return $contributions;     
    }
    
	static function getPartners($project, $receiver){
		$contributions = Dashboard::getContributions($project, $receiver);
		
		$partner_arr = array();
		
		foreach ($contributions as $c){
			$c_partners = $c->getPartners();
			foreach($c_partners as $pr){
				$org = $pr->getOrganization();
				if( !in_array($org, $partner_arr) ){
					$partner_arr[] = $org;
				}
			}
		}
		
		$projects = array();
		if( $project instanceof Project){
			$projects[] = $project;
		}
		else{
			$projects = $receiver->getProjects();
		}
		
		if($receiver instanceof Person){
			$champs = array();
			foreach($projects as $pr){
				$champs = array_merge($champs, $pr->getAllPeopleDuring(CHAMP)); //all champions on a project throughout the year
			}
			
			foreach ($champs as $champ){
				if( $receiver->relatedTo($champ, "Works With") ){
					if( $partner_name = $champ->getPartnerName() ){
						if(!in_array($partner_name, $partner_arr)){
							$partner_arr[] = $partner_name;
						}                            
					}    
				}
			}
		}
		
		return $partner_arr;
	}
	
	static function partnerDetails($partners){
        global $wgServer, $wgScriptPath;
       
	    $url_prefix = "$wgServer$wgScriptPath/index.php/"; 
        $html = "";    
        foreach($partners as $p){
			$html.=<<<EOF
				<li>
				$p
				</li>
EOF;
        }
		
		return $html; 	
	}
	
	static function filterContributions($contributions, $type="all"){
        $filtered = array();
        
        foreach($contributions as $c){
            $c_type = $c->getType();

            if( ($type=="all" || $c_type == $type) ){
                $filtered[] = $c;
            }
        }
        
        return $filtered;
    }
	
    static function contributionSum($contributions){
        $total = 0;    
        foreach($contributions as $c){
            $type = $c->getType();
            if($type == 'inki'){
                $total += $c->getKind();
            }
            else if($type == 'caki'){
                $total += $c->getTotal();   
            }
            else{
                $total += $c->getCash();   
            } 
        }
        return $total;
    }

    static function contributionSumCash($contributions){
        $total = 0;    
        foreach($contributions as $c){
            $type = $c->getType();
            if($type == 'cash' || $type == 'caki'){
                $total += $c->getCash();   
            }
        }
        return $total;
    }
    static function contributionSumKind($contributions){
        $total = 0;    
        foreach($contributions as $c){
            $type = $c->getType();
            if($type == 'inki' || $type == 'caki'){
                $total += $c->getKind();   
            }
        }
        return $total;
    }
    
    static function contributionDetails($contributions, $type){
        global $wgServer, $wgScriptPath;
        $url_prefix = "$wgServer$wgScriptPath/index.php/";
        
        $html = "";    
        foreach($contributions as $c){
            $type = $c->getType();
            $amount_breakdown = "";
            
            if($type == 'inki'){
                $amount_inki = number_format($c->getKind(), 2);
                $amount_cash = "0.00";   
                $amount_breakdown = "$ $amount_cash Cash; $ $amount_inki In-Kind";
            }
            else if($type == 'caki'){
                $amount_cash = number_format($c->getCash(), 2);
                $amount_inki = number_format($c->getKind(), 2);
                $amount_breakdown = "$ $amount_cash Cash; $ $amount_inki In-Kind";
            } 
            else{
                $amount_cash = number_format($c->getCash(), 2);
                $amount_inki = "0.00";
                $amount_breakdown = "$ $amount_cash Cash; $ $amount_inki In-Kind";
            }


            if($amount_cash == "0.00" && $amount_inki == "0.00"){
                continue;
            }
            
            $con_year = $c->getYear();
            $con_name = $c->getName();
            $con_name_wiki = $c->getWikiName();
            $con_partners = $c->getPartners();
            $con_partner_arr = array();
            foreach($con_partners as $pr){
                $org = $pr->getOrganization();
                if( !in_array($org, $con_partner_arr) ){
                    $con_partner_arr[] = $org;
                }
            }
            $con_partner_str = (count($con_partner_arr) > 0)? implode(', ',$con_partner_arr ) : "Unknown";
                    
            $html.=<<<EOF
                <li>
                $amount_breakdown: 
                <a target='_blank' href='{$c->getUrl()}'>$con_name</a> 
                by ($con_partner_str), $con_year
                </li>
EOF;
        }
        return $html;             
    }

    static function contributionDetailsXLS($contributions){
        global $wgServer, $wgScriptPath;
        $url_prefix = "$wgServer$wgScriptPath/index.php/";
        
        $html = "";    
        foreach($contributions as $c){
            $type = $c->getType();
            $amount_breakdown = "";

            if($type == 'inki'){
                $amount_inki = number_format($c->getKind(), 2);
                $amount_cash = "0.00";   
                $amount_breakdown = "$ $amount_cash Cash; $ $amount_inki In-Kind";
            }
            else if($type == 'caki'){
                $amount_cash = number_format($c->getCash(), 2);
                $amount_inki = number_format($c->getKind(), 2);
                $amount_breakdown = "$ $amount_cash Cash; $ $amount_inki In-Kind";
            } 
            else{
                $amount_cash = number_format($c->getCash(), 2);
                $amount_inki = "0.00";
                $amount_breakdown = "$ $amount_cash Cash; $ $amount_inki In-Kind";
            }


            if($amount_cash == "0.00" && $amount_inki == "0.00"){
                continue;
            }
            
            $con_type = $c->getHumanReadableType();
            $con_year = $c->getYear();
            $con_name = $c->getName();
            $con_name_wiki = $c->getWikiName();
            $con_partners = $c->getPartners();
            $con_partner_arr = array();
            foreach($con_partners as $pr){
                $org = $pr->getOrganization();
                if( !in_array($org, $con_partner_arr) ){
                    $con_partner_arr[] = $org;
                }
            }
            $con_partner_str = (count($con_partner_arr) > 0)? implode(', ',$con_partner_arr ) : "Unknown";
                    
            $html.=<<<EOF
                <li>
                $amount_breakdown; $con_type; 
                <a target='_blank' href='{$c->getUrl()}'>$con_name</a>; $con_partner_str; $con_year
                </li>
EOF;
        }
        return $html;             
    }
    
	
	//Dashboard version for HQP's
    static function HQPDashboard(){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $reporteeId;
	    $wgOut->setPageTitle("HQP Dashboard");
	   
		$person = Person::newFromId($reporteeId);
		$p_name = $person->getNameForForms();
        $p_university = $person->getUniversity(); 
        $p_position = $p_university['position'];
        $p_department = $p_university['department'];
        $p_university = $p_university['university'];
        
        //Supervisors
        $supervisors_ar = $person->getSupervisors();
        $p_supervisors = "";    
        foreach ($supervisors_ar as $s){
            $s_roles = $s->getRoles();
            $s_username = $s->getName();
            $p_supervisors .= "<a target='_blank' href='{$s->getUrl()}'>".$s->getNameForForms() . "</a>, ";
        }
        $p_supervisors = rtrim($p_supervisors, ' ,');
        
        //Projects
        $projects_ar = $person->getProjects();
        $p_projects = "";
        foreach ($projects_ar as $p){
            $p_projname = $p->getName();
            $p_projects .= "<a target='_blank' href='{$p->getUrl()}'>". $p_projname . "</a>, ";
        }
        $p_projects = rtrim($p_projects, ' ,');
				
       
        $summary = <<<EOF
            <div class="basic_info">
                <p><span class="label">Name:</span> $p_name </p>
                <p><span class="label">Level:</span> $p_position </p>
                <p><span class="label">Department:</span> $p_department </p>
                <p><span class="label">University:</span> $p_university </p>
                <p><span class="label">Supervisor(s):</span> $p_supervisors </p>
                <p><span class="label">Projects:</span> $p_projects </p>
            </div>
EOF;

        $wgOut->addHTML($summary);
        $dashboard = new DashboardTable(HQP_REPORT_STRUCTURE, $person);
        if(isset($_GET['generatePDF']) || isset($_GET['evalPDF'])){
	        $wgOut->addHTML($dashboard->renderForPDF());
	    }
	    else{
	        $wgOut->addHTML($dashboard->render());
	    }
	}
	
	//Dashboard version for CNI's & PNI's
	static function NIDashboard(){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $reporteeId;
	    
	    $wgOut->setPageTitle("NI Dashboard");

		$person = Person::newFromId($reporteeId);
		$p_name = $person->getNameForForms();
        $p_university = $person->getUniversity(); 
        $p_position = $p_university['position'];
        $p_department = $p_university['department'];
        $p_university = $p_university['university'];

        //Projects
        $project_objs = $person->getProjects();
        $project_str = "";
        foreach ($project_objs as $p){
            $proj_name = $p->getName();
            $proj_leader = $p->getLeader();
            $proj_coleader = $p->getCoLeader();
            
            if( !is_null($proj_leader) && $p_name == $proj_leader->getNameForForms() ){
                $project_str .= "<a target='_blank' href='{$p->getUrl()}'>". $proj_name . "(PL)</a>, ";
            }
            else if( !is_null($proj_coleader) && $p_name == $proj_coleader->getNameForForms() ){
                $project_str .= "<a target='_blank' href='{$p->getUrl()}'>". $proj_name . "(CPL)</a>, ";
            }
            else{
                $project_str .= "<a target='_blank' href='{$p->getUrl()}'>". $proj_name . "</a>, ";
            }
        }
        $project_str = rtrim($project_str, ' ,');
        
        $summary = <<<EOF
            <div class="basic_info">
                <p><span class="label">Name:</span> $p_name </p>
                <p><span class="label">Level:</span> $p_position </p>
                <p><span class="label">Department:</span> $p_department </p>
                <p><span class="label">University:</span> $p_university </p>
                <p><span class="label">Projects:</span> $project_str </p>
            </div>
EOF;
        $wgOut->addHTML($summary);
        $dashboard = new DashboardTable(NI_REPORT_STRUCTURE, $person);
        if(isset($_GET['generatePDF']) || isset($_GET['evalPDF'])){
            $wgOut->addHTML($dashboard->renderForPDF());
        }
        else{
	        $wgOut->addHTML($dashboard->render());
	    }
	}
	
	//Questionnaire version for HQP's
    static function HQPQuestionnaire($year=REPORTING_YEAR){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $reporteeId;
	    
	    //Define report address for our milestone questionnaire
	    $uid = $reporteeId; //$wgUser->getId();
		$blob_type=BLOB_ARRAY;
		$rptype = RP_HQP;
    	$section = HQP_MILESTONES;
    	$item = HQP_MIL_CONTRIBUTIONS;
    	$subitem = 0;
		$rep_addr = ReportBlob::create_address($rptype,$section,$item,$subitem);
		$since_until_blob = new ReportBlob($blob_type, $year, $uid, 0);
	    //print_r($_POST);
	    //Check form submission
	    $questionnaire_submit = ($_POST && isset($_POST['hqp_questionnaire']))? $_POST['hqp_questionnaire'] : "";
	    if( $questionnaire_submit === "Save" ){
	        $projects = @$_POST['projects'];
	        if($projects != null){
    	        foreach( $projects as $pname => $milestones){   
    	            $proj = Project::newFromName($pname);
    	            $pid = $proj->getId();
    	            $blob = new ReportBlob($blob_type, $year, $uid, $pid);
	            
        	        //foreach ($milestones as $k => $a){
        	        //    echo "MILE: $k; Contributed = ".$a['contribution'] . "; Comment = ".$a['comment']."<br />";
        	        //} 
	        
    	            $blob->store($milestones, $rep_addr);
    	        }
            }
	        //Involved Since/Until
	        $involved_dates_arr = array("involved_since"=>@$_POST['involved_since'], "involved_until"=>@$_POST['involved_until']);
	        
	        $since_until_blob->store($involved_dates_arr, $rep_addr);
	    }
	    
	 
	    //Render the page
	    $wgOut->setPageTitle("HQP Questionnaire");
	    $pg = "$wgServer$wgScriptPath/index.php/Special:{$wgTitle->getText()}";
	    //$person = Person::newFromId($wgUser->getId());
	    $person = Person::newFromId($reporteeId);
	    
	    $custom_js =<<<EOF
	        <script type='text/javascript'>
    		$(document).ready(function () {
    		
            $( '#involved_since' ).datepicker({ dateFormat: 'yy-mm-dd'});
            $( '#involved_since' ).keydown(function(){
                return false;
            });
            $( '#involved_until' ).datepicker({ dateFormat: 'yy-mm-dd'});
            $( '#involved_until' ).keydown(function(){
                return false;
            });
                
EOF;
	    
	    $since_until_blob->load($rep_addr);
	    $involved_dates_arr = $since_until_blob->getData();
	    $involved_since = $involved_dates_arr['involved_since'];
	    $involved_until = $involved_dates_arr['involved_until'];
	    
		//Milestone table HTML
        $milestone_header_table = <<<EOF
            <p style="font-weight:bold; font-size:13px;">Involved with GRAND</p>
            <p><span style="display:inline-block; width: 85px; font-weight:bold; font-size:13px;">Start Date:</span>
            <input type="text" name="involved_since" id="involved_since" value="$involved_since" class="$involved_since"></p>
            <p><span style="display:inline-block; width: 85px; font-weight:bold; font-size:13px;">End Date:</span>
            <input type="text" name="involved_until" id="involved_until" value="$involved_until"> <span style="font-weight:normal; font-style: italic; font-size:11px;">(actual or anticipated)</span></p>
            
            <table cellpadding='5' cellspacing='0' border='1' width='900' class="milestones_hdr_table">
                <tbody>
                <tr>
                <th width="10%">Worked On</th>
                <th width="35%">Milestone Description</th>
                <th width="55%">Comments<br />
                <span style="font-size:10px; font-style: italic;font-weight: normal;">up to 300 characters</span></th>
                </tr>
                </tbody>
            </table>
EOF;
        $wgOut->addHTML($milestone_header_table);
        
        
        //Projects
        $projects_ar = $person->getProjects();
        foreach ($projects_ar as $p){
            //$p = Project::newFromId(176);
            $p_name = $p->getName();
            $p_id = $p->getId();
            $p_blob = new ReportBlob($blob_type, $year, $uid, $p_id);
            
            $p_blob->load($rep_addr);
            $milestone_data = $p_blob->getData();
            
            
            $lnk_id = $p_name."_milestones_lnk";
            $div_id = $p_name."_milestones_div";
            
            $custom_js .=
                "$('#".$lnk_id."').click(function(e) {
                      e.preventDefault();        
                      //$('#details_div').html( $('".$div_id."').html() );
                      $('#".$div_id."').toggle();
                 });";

        
            $p_link = '<a id="'.$lnk_id.'" href="#">'.$p_name.' <span style="font-size:10px;" class="pdf_hide">(click to show/hide details)</span></a>';
            $wgOut->addHTML("<h3>$p_link</h3>");
            
            //$milestones = $p->getMilestonesSince('2011-01-01 00:00:00');
            $milestones = $p->getMilestonesDuring(); //Will default to current year

            $milestone_table =<<<EOF
            <span class="pdf_show" id="$div_id" style="display:none;">
            <table cellpadding='5' cellspacing='0' border='1' width='900' class='milestones'>
            
EOF;
        
            foreach ($milestones as $m){
                $m_description_full = $m->getDescription();
                $m_id = $m->getMilestoneId(); //$m->getId(); //THE BUG RE milestone id mess in DB
                $m_title = $m->getTitle();
                
                $desc_dialog_id = $p_name."_".$m_id."_desc_dialog";
                $comments_id = $p_name."_".$m_id."_comments";
                $custom_js .= "$(\"#$comments_id\").limit('300');";
                
                //See if we have data
                $checked = (isset($milestone_data[$m_id]['contribution']))? "checked='checked'" : "";
                $comment = (isset($milestone_data[$m_id]['comment']))? $milestone_data[$m_id]['comment'] : "";
                
                
                //Shorten the shown description, setup the dialog that shows full description
                $custom_js .= "$(\"#$desc_dialog_id\").dialog({ autoOpen: false, height: 300, width: 500 });";
                $m_description = substr($m_description_full, 0, 100) . "...";
                $m_description =<<<EOF
                    <p>
                    <a href="#" onclick="$('#$desc_dialog_id').dialog('open'); return false;">$m_description</a>
                    <div class="pdf_hide" title="$m_title" id="$desc_dialog_id">$m_description_full</div>
                    </p>
EOF;

                $milestone_row =<<<EOF
                <tr>
                <td align="center" width="10%">
                <input type="checkbox" name="projects[$p_name][$m_id][contribution]" value="Yes" $checked />
                </td>
                <td width="35%">$m_description</td>
                <td width="55%">
                <textarea id="$comments_id" rows="5" style="border:none; display:block; height:100%;width:468px;" 
                    name="projects[$p_name][$m_id][comment]">$comment</textarea>
                </td>
                </tr>
EOF;
                
                $milestone_table .= $milestone_row;   
            }
            if( empty($milestones) ){
                $milestone_row =
                '<tr>
                 <td colspan="3">There are no milestones available.</td>
                 </tr>';

                $milestone_table .= $milestone_row;
            }
            
            $milestone_table .= "</table></span>";
            $wgOut->addHTML($milestone_table);
            
        }
        
        $custom_js .= "});</script>";
        $wgOut->addScript($custom_js);
        
        //$wgOut->addHTML("<br /><br /><input type='submit' name='hqp_questionnaire' value='Save' class='report_button' />");
        
	}    
	
	//Report version for HQP's
    static function HQPReport($year=REPORTING_YEAR){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $reporteeId;
	    
	    //Define report address for our milestone questionnaire
	    $uid = $reporteeId; //$wgUser->getId();
		$blob_type = BLOB_TEXT;
		$rptype = RP_HQP;
    	$activity_types = array(
	        HQP_RESACT_EXCELLENCE => "Excellence of the Research Program",
	        HQP_RESACT_NETWORKING => "Networking and Partnerships",
	        HQP_RESACT_KTEE => "Knowledge and Technology Exchange and Exploitation"
	    );
    	
		//$rep_addr = ReportBlob::create_address($rptype, HQP_RESACTIVITY, HQP_RESACT_OVERALL, 0);
		//$overall_activity_blb = new ReportBlob($blob_type, $year, $uid, 0);
	    
	    //Form submit processing
	    $report_submit = ($_POST && isset($_POST['hqp_report']))? $_POST['hqp_report'] : "";
	    if( $report_submit === "Save" ){
            
            //Save overall activity
            //$overall_activity = $_POST['overall_activity'];
	        //$overall_activity_blb->store($overall_activity, $rep_addr);
            
	        $activities = @$_POST['activities'];
	        if($activities != null){
	            foreach( $activities as $a_type => $projects){   
    	            foreach ($projects as $p_id => $comment){
    	                $a_rep_addr = ReportBlob::create_address($rptype, HQP_RESACTIVITY, $a_type, 0);
    	                $blob = new ReportBlob($blob_type, $year, $uid, $p_id);
    	                $blob->store($comment, $a_rep_addr);
                    }
    	        }
            }
	    }
	    
	    
	    //Fetch the previously saved data, if exists
	    //$overall_activity_blb->load($rep_addr);
    	//$overall_activity = $overall_activity_blb->getData();
	    
	    //Render the page
	    $wgOut->setPageTitle("HQP Report");
	    $pg = "$wgServer$wgScriptPath/index.php/Special:{$wgTitle->getText()}";
	    //$person = Person::newFromId($wgUser->getId());
	    $person = Person::newFromId($reporteeId);
	    
	    $custom_js =<<<EOF
	        <script type='text/javascript'>
    		$(document).ready(function () {                
EOF;

        /*$activity_overview_html =<<<EOF
            <div id="hqp_report_wrapper">
            <div>
                <p class="blob_header">Overview of HQP Activity</p>
                <textarea id="overall_activity" rows="5" style="" 
                    name="overall_activity">$overall_activity</textarea>
            </div>
EOF;*/
	    
        //$wgOut->addHTML($activity_overview_html);  
	    
	    
	    $activities_html = "<span style=\"display:block;\" id=\"hqp_report_wrapper\">";
	    foreach ($activity_types as $a_type => $a_lbl){
	        $a_rep_addr = ReportBlob::create_address($rptype, HQP_RESACTIVITY, $a_type, 0);
	        
	        //Links, divs and triggers
	        $lnk_id = "lnk_comments_$a_type";
            $div_id = "div_comments_$a_type";
            $preview_lnk_id = "lnk_preview_$a_type";
            $preview_div_id = "div_preview_$a_type";
            
            $allowed_chars = ($a_type == HQP_RESACT_EXCELLENCE)? 900 : 600;
            
            $custom_js .=<<<EOF
                $("#$preview_div_id").dialog({ autoOpen: false, height: 400, width: 600 });
                $('#$lnk_id').click(function(e) {
                      e.preventDefault();        
                      $('#$div_id').toggle();
                 });
                 $('#$preview_lnk_id').click(function(e) {
                       e.preventDefault();
                       report = "";
                       totalchars = 0;
                       $('textarea[name*="activities[$a_type]"]').
                        each(function(index) {
                            totalchars += $(this).val().length;
                            report += "<p>"+$(this).val()+"</p>";
                        });
                        if(totalchars > $allowed_chars){
                            title = "Report Preview <span class='curr_chars'>(Currently "+totalchars+" chars - limit of $allowed_chars chars exceeded.)";
                            $('#ui-dialog-title-$preview_div_id').html(title);
                        }
                        else{
                            title = "Report Preview <span class='curr_chars'>(Currently "+totalchars+" chars  out of allowed $allowed_chars.)";
                            $('#ui-dialog-title-$preview_div_id').html(title);
                        }
                       $('#$preview_div_id').html(report);
                       $('#$preview_div_id').dialog('open');
                  });
EOF;
            
            $a_link = '<a id="'.$lnk_id.'" href="#">'.$a_lbl.'</a>';
	        
	        $preview_link = '<span class="pdf_hide" style="font-size:11px;"><a id="'. $preview_lnk_id.'" href="#">(Preview)</a></span>';
	        
	        $activities_html .= "<h3>$a_link &nbsp;&nbsp;$preview_link</h3><div title=\"Report Preview\" class=\"pdf_hide\" id=\"$preview_div_id\" style=\"white-space: pre-line;\"></div><div class='pdf_show' style='display: none; style=\"white-space: pre-wrap;\"' id=\"$div_id\">";
            
            $projects_ar = $person->getProjects();
            foreach ($projects_ar as $p){
                $p_name = $p->getName();
                $p_id = $p->getId();
                //$p_link = "<a target='_blank' href='{$p->getUrl()}'>". $p_name . "</a>";
                $p_link = $p_name;
                
                //Load previously saved data
                $p_blob = new ReportBlob($blob_type, $year, $uid, $p_id);
                $p_blob->load($a_rep_addr);
                $p_blob_data = $p_blob->getData();
                
                $custom_js .=<<<EOF
        $("#activities\\\[$a_type\\\]\\\[$p_id\\\]").limit("$allowed_chars","#charsLeft_activities\\\[$a_type\\\]\\\[$p_id\\\]");
EOF;
                $activities_html .=<<<EOF
                    <p class="project_header">$p_link &nbsp;
                    <span class="curr_chars pdf_hide">
                        (currently <span id="charsLeft_activities[$a_type][$p_id]">0</span> chars.)</span></p>
                    <textarea rows="7" style="" id="activities[$a_type][$p_id]" name="activities[$a_type][$p_id]">$p_blob_data</textarea>
EOF;
            
            }   
            $activities_html .= "</div>";     
                    
	    }
	    $custom_js .= "});</script>";
        $wgOut->addScript($custom_js);
        
	    $wgOut->addHTML($activities_html."</span>");   
	    //$wgOut->addHTML("<br /><br /><input type='submit' name='hqp_report' value='Save' class='report_button' /></div>");
	}
	
	
	//Questionnaire version for NI's
    static function NIQuestionnaire(){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $reporteeId;

	    //Define report address for our milestone questionnaire
	    $year = REPORTING_YEAR;
	    $uid = $reporteeId; //$wgUser->getId();
		$blob_type = BLOB_ARRAY;
		$rptype = RP_RESEARCHER;
    	$section = RES_MILESTONES;
    	$item = RES_MIL_CONTRIBUTIONS;
    	$subitem = 0;
		$rep_addr = ReportBlob::create_address($rptype,$section,$item,$subitem);
		$hqp_rep_addr = ReportBlob::create_address(RP_HQP, HQP_MILESTONES, HQP_MIL_CONTRIBUTIONS, 0);
		$grand_activity_blob = new ReportBlob($blob_type, $year, $uid, 0);
	    
	    //Check form submission
	    $questionnaire_submit = ($_POST && isset($_POST['ni_questionnaire']))? $_POST['ni_questionnaire'] : "";
	    if( $questionnaire_submit === "Save" ){

	        $projects = @$_POST['projects'];
	        if($projects != null){
	            foreach( $projects as $pname => $milestones){
	                $proj = Project::newFromName($pname);
	                $pid = $proj->getId();
	                $blob = new ReportBlob($blob_type, $year, $uid, $pid);

        	        //foreach ($milestones as $k => $a){
        	        //    echo "MILE: $k; Contributed = ".$a['contribution'] . "; Comment = ".$a['comment']."<br />";
        	        //} 

	                $blob->store($milestones, $rep_addr);
	            }
	        }

	        //Grand Activity: time/week & percentage
	        $grand_activity_arr = array( "grand_time" => @$_POST['grand_time'], "grand_percent" => @$_POST['grand_percent'] );
	        $grand_activity_blob->store($grand_activity_arr, $rep_addr);
	    }


	    //Render the page
	    $wgOut->setPageTitle("NI Questionnaire");
	    $pg = "$wgServer$wgScriptPath/index.php/Special:{$wgTitle->getText()}";
	    //$person = Person::newFromId($wgUser->getId());
        $person = Person::newFromId($reporteeId);
        
	    $custom_js =<<<EOF
	        <script type='text/javascript'>    
    		$(document).ready(function () {
    		    
EOF;

	    $grand_activity_blob->load($rep_addr);
	    $grand_activity_arr = $grand_activity_blob->getData();
	    $grand_time = $grand_activity_arr['grand_time'];
	    $grand_percent = $grand_activity_arr['grand_percent'];

		//Milestone table HTML
        $milestone_header_table = <<<EOF
            <p>
            <input class="short" type="text" name="grand_time" id="grand_time" value="$grand_time" />
            <span style="font-weight:bold; font-size:13px;">Time Devoted to GRAND (hours / week)</span>
            </p>
            <p>
            <input class="short" type="text" name="grand_percent" id="grand_percent" value="$grand_percent" /> 
            <span style="font-weight:bold; font-size:13px;">GRAND-related effort relative to Overall Research Activity (%)</span>
            </p>
            <br />
            <table cellpadding='5' cellspacing='0' border='1' width='900' class="milestones_hdr_table">
                <tbody>
                <tr>
                <th width="10%">Worked On</th>
                <th width="35%">Milestone Description</th>                
                <th width="55%">Comments<br />
                <span style="font-size:10px; font-style: italic;font-weight: normal;">up to 300 characters</span></th></th>
                </tr>
                </tbody>
            </table>
            <div class="ni_questionnaire_wrapper">
EOF;
        $wgOut->addHTML($milestone_header_table);


        //Projects
        $projects_ar = $person->getProjects();
        $total_proj = count($projects_ar);
        $p_count = 0;
        foreach ($projects_ar as $p){
            $p_count++;
            $p_name = $p->getName();
            $p_id = $p->getId();
            $p_blob = new ReportBlob($blob_type, $year, $uid, $p_id);

            $p_blob->load($rep_addr);
            $milestone_data = $p_blob->getData();


            $lnk_id = $p_name."_milestones_lnk";
            $div_id = $p_name."_milestones_div";
             
            
            $custom_js .=
                "$('#".$lnk_id."').click(function(e) {
                      e.preventDefault();        
                      //$('#details_div').html( $('".$div_id."').html() );
                      $('#".$div_id."').toggle();
                 });";


            $p_link = '<a id="'.$lnk_id.'" href="#">'.$p_name.' <span style="font-size:10px;" class="pdf_hide">(click to show/hide details)</span></a>';
            $p_time_commitment = 
                "Time Commitment: <input class=\"short\" type=\"text\" name=\"projects[$p_name][0][time]\" value=\"".$milestone_data[0]['time']."\" > <span style=\"font-weight:normal;\">hours / week</span>";
            
            $wgOut->addHTML("<h3 class='milestones_project_hdr' style='width:900px;position:relative;'>$p_link <span style='position:absolute; left: 250px; bottom:0;'>$p_time_commitment</span></h3>");

            //$milestones = $p->getMilestonesSince('2011-01-01 00:00:00');
            $milestones = $p->getMilestonesDuring(); //Will default to current year

            $milestone_table =<<<EOF
            <span id="$div_id" class="pdf_show" style="display:none;">
            <table cellpadding='5' cellspacing='0' border='1' width='900' class='milestones'>
            
EOF;

            foreach ($milestones as $m){
                $m_description_full = $m->getDescription();
                $m_id = $m->getMilestoneId(); //$m->getId(); //THE BUG RE milestone id mess in DB
                $m_title = $m->getTitle();
                
                $desc_dialog_id = $p_name."_".$m_id."_desc_dialog";
                $dialog_id = $p_name."_".$m_id."_dialog";
                $comments_id = $p_name."_".$m_id."_comments";
                
                $custom_js .= "$(\"#$comments_id\").limit('300');";
                
                //echo "$m_id : ".$milestone_data[$m_id]['contribution']."<br />";
                if( isset($milestone_data[$m_id]['contribution']) && $milestone_data[$m_id]['contribution'] == "Yes" ){
                    $checked_yes = "checked='checked'";
                    $checked_no = "";
                }
                else{
                    $checked_no = "checked='checked'";
                    $checked_yes = "";
                }
                $comment = (isset($milestone_data[$m_id]['comment']))? $milestone_data[$m_id]['comment'] : "";

                //First get All HQPs that I'm supervising, then we'll fetch their comment on the milestone.
                $hqp_objs = $p->getAllPeopleDuring("HQP"); //no range params, so will default to current year
                
                $hqp_milestone_comments = "";
                foreach ($hqp_objs as $h){
                    $h_sups = $h->getSupervisors(true);
                    
                    $supervisor = false;
                    foreach ($h_sups as $s){
                        if ( $s->getId() == $person->getId() ){
                            $supervisor =  true;
                            //echo "HQP: ".$h->getName()."<br />";
                            break;
                        }
                    }
                    
                    if($supervisor){
                        $hqp_milestone_blob = new ReportBlob($blob_type, $year, $h->getId(), $p_id);
                        $hqp_milestone_blob->load($hqp_rep_addr);
                        $hqp_milestone_data = $hqp_milestone_blob->getData();
                        
                        if( isset($hqp_milestone_data[$m_id]) && isset($hqp_milestone_data[$m_id]['comment'])
                            && !empty($hqp_milestone_data[$m_id]['comment']) ){
                            $hqp_milestone_comments .= $h->getNameForForms() . ":<br /><i style='margin:10px;display:block;'>" . 
                                $hqp_milestone_data[$m_id]['comment'] . "</i><br />";

                        }
                    }
                }
                
                //Shorten the shown description, setup the dialog that shows full description
                $custom_js .= "$(\"#$desc_dialog_id\").dialog({ autoOpen: false, height: 300, width: 500 });";
                $m_description = substr($m_description_full, 0, 100) . "...";
                $m_description =<<<EOF
                    <p>
                    <a href="#" onclick="$('#$desc_dialog_id').dialog('open'); return false;">$m_description</a>
                    <div title="$m_title" class="pdf_hide" id="$desc_dialog_id">$m_description_full</div>
                    </p>
EOF;
                
                //Set up the dialog that shows milestone comments
                if ($hqp_milestone_comments){
                    $custom_js .= "$(\"#$dialog_id\").dialog({ autoOpen: false, height: 300, width: 500 });";
                    $m_description .=<<<EOF
                     <a class="pdf_hide" style="font-style:italic; font-size:11px; font-weight:bold; float:right;" onclick="$('#$dialog_id').dialog('open'); return false;" href="#">See HQP Comments</a>
                     <div title="HQP Milestone Comments" style="white-space: pre-line;" class="pdf_hide" id="$dialog_id">$hqp_milestone_comments</div>
EOF;
                }
                
                //Get the history of the milestone
                $history_html = "";
                $parents = array();

                $m_parent = $m;
                while(!is_null($m_parent)){
                    $parents[] = $m_parent;
                    $m_parent = $m_parent->getParent();
                }
                $parents = array_reverse($parents);

                foreach($parents as $m_parent){    
                    $p_status = $m_parent->getStatus();
                    if($p_status == "Continuing"){
                        continue;
                    }
                    $changed_on = $m_parent->getStartDate();
                    $p_title = stripslashes($m_parent->getTitle());
                    $p_end_date = $m_parent->getProjectedEndDate();
                    $p_description = stripslashes(nl2br($m_parent->getDescription()));
                    $p_assessment = stripslashes(nl2br($m_parent->getAssessment()));
                    $p_comment = stripslashes(nl2br($m_parent->getComment()));
                    if($p_comment){
                        $p_comment = "<br /><strong>Comment:</strong> $p_comment";
                    }
                    if($p_status == "New"){
                        $label = "Created";
                    }
                    else{
                        $label = $p_status;
                    }

                    $history_html .=<<<EOF
                     <div style="padding: 10px; 0;"> 
                     <strong>$label</strong> on $changed_on<br />
                     <strong>Projected End Date:</strong> $p_end_date<br />
                     <strong>Title:</strong> $p_title<br />
                     <strong>Description:</strong> $p_description<br />
                     <strong>Assessment:</strong> $p_assessment
                     $p_comment
                     </div>
                     <hr />
EOF;
                }
                if($history_html != "" && !isset($_GET['generatePDF']) ){
                    $history_dialog_id = "history_m_{$m_id}_{$p_id}";
                    $custom_js .= "$(\"#$history_dialog_id\").dialog({ autoOpen: false, height: 600, width: 800 });";
                    $history_html =<<<EOF
                    <p class="pdf_hide"><a style="font-style:italic;font-size:11px;font-weight:bold;" href="#" onclick="$('#$history_dialog_id').dialog('open'); return false;">See Milestone History</a></p>
                    <div class="pdf_hide" title="Milestone History" style="white-space: pre-line;" id="$history_dialog_id">$history_html</div>
EOF;
                }
                if(isset($_GET['generatePDF'])){
                    $history_html = "";
                }
                $m_description .= $history_html;
                
                $milestone_row =<<<EOF
                <tr>
                <td align="left" width="10%" style="padding-left:15px;">
                <input type="radio" name="projects[$p_name][$m_id][contribution]" value="No" $checked_no /> No
                <br />
                <input type="radio" name="projects[$p_name][$m_id][contribution]" value="Yes" $checked_yes /> Yes
                </td>
                <td width="35%">$m_description</td>
                <td width="55%">
                <textarea id="$comments_id" rows="5" style="border:none; display:block; height:100%;width:468px;" 
                    name="projects[$p_name][$m_id][comment]">$comment</textarea>
                </td>
                </tr>
EOF;
                $milestone_table .= $milestone_row;   
            }
            if( empty($milestones) ){
                $milestone_row =
                '<tr>
                 <td colspan="3">There are no milestones available.</td>
                 </tr>';

                $milestone_table .= $milestone_row;
            }

            $milestone_table .= "</table></span>";
            if($p_count < $total_proj){
                $milestone_table .= "<div style='page-break-after:always;'></div>";
            }
            $wgOut->addHTML($milestone_table);
            
        } //projects loop
        $wgOut->addHTML("</div>");
        //$wgOut->addHTML("</span><div style='page-break-after:always;'></div>");
        $custom_js .= "});</script>";
        $wgOut->addScript($custom_js);

        //$wgOut->addHTML("<br /><br /><input type='submit' name='ni_questionnaire' value='Save' class='report_button' />");

	}
	
	//Report version for HQP's
    static function NIBudget(){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $reporteeId, $viewOnly;
	    
	    //Define report address for our milestone questionnaire
	    $year= REPORTING_YEAR;
	    $uid = $reporteeId; //$wgUser->getId();
		$blob_type=BLOB_EXCEL;
		$rptype = RP_RESEARCHER;
    	$section = RES_BUDGET;
    	$item = 0;
    	$subitem = 0;
		$rep_addr = ReportBlob::create_address($rptype,$section,$item,$subitem);
		$budget_blob = new ReportBlob($blob_type, $year, $uid, 0);
		
		$just_rep_addr = ReportBlob::create_address($rptype, $section, RES_BUD_JUSTIF, 0);
		$just_blb = new ReportBlob(BLOB_TEXT, $year, $uid, 0);
		
		$report_submit = ($_POST && isset($_POST['ni_budget']))? $_POST['ni_budget'] : "";
		if($report_submit === "Save" || ($report_submit === "Upload" && 
		   isset($_FILES['budget']))){
		    if(isset($_FILES['budget']) && file_exists($_FILES['budget']['tmp_name'])){
                $budget = file_get_contents($_FILES['budget']['tmp_name']);
	            $budget_blob->store($budget, $rep_addr);
	            
	            // Add new 'Works With' relation
	            $budget_blob->load($rep_addr);
	            
	            Dashboard::addWorksWithRelation($budget_blob);
	        }
	        
	        //Save budget justif
            $budget_just = $_POST['budget_just'];
	        $just_blb->store($budget_just, $just_rep_addr);
	    }
	    
	    //Fetch the previously saved budget justif, if exists
	    $just_blb->load($just_rep_addr);
    	$budget_just = $just_blb->getData();
	    
	    $custom_js =<<<EOF
	        <script type='text/javascript'>
    		$(document).ready(function () { 
    		     $('#budget_just').limit('600','#charsLeft_budget_just'); 
    		     $('#lnk_budget_just').click(function(e) {
                       e.preventDefault();        
                       $('#div_budget_just').toggle();
                  });
            });
            </script>                    
EOF;
        $wgOut->addScript($custom_js);
	    
	    $justification_html =<<<EOF
	        <div id="ni_budget_wrapper">
	        <h3 class='pdfnodisplay'><a href="#" id="lnk_budget_just">Budget Justification</a></h3>
            <div id="div_budget_just" class='pdfnodisplay'>
                <p><span class="curr_chars pdf_hide">(currently <span id="charsLeft_budget_just">0</span> chars out of allowed 600.)</span></p>
                <textarea id="budget_just" rows="6" style="" 
                    name="budget_just">$budget_just</textarea><br />   
            </div><br />
EOF;
        $wgOut->addHTML($justification_html);
		
		// Allow user to download the budget template
		$wgOut->addHTML("<div class='pdfnodisplay'>");
		$wgOut->addHTML("<h2>Download Budget Template</h2> <ul><li><a href='$wgServer$wgScriptPath/data/GRAND Researcher Budget Request (2012-13).xls'>2012-13 Budget Template</a></li></ul>" );
		
		if(!$viewOnly){
		    $wgOut->addHTML("<h2>Budget Upload</h2>
		                    <input type='file' name='budget' />
		                    <input type='submit' name='ni_budget' value='Upload' />");
		}  
		
		$wgOut->addHTML("</div>");                  
		$result = $budget_blob->load($rep_addr);
		$data = $budget_blob->getData();
		
		// Show a preview of the budget
		$wgOut->addHTML("<h2 class='pdfnodisplay'>Budget Preview</h2>");
		if($result !== false){
		    $budget = new Budget("XLS", REPORT2_STRUCTURE, $data);
		    $wgOut->addHTML($budget->copy()->filterCols(V_PROJ, array(""))->render());
		}
		else{
		    $wgOut->addHTML("You have not yet uploaded a budget");
		}
		
		$wgOut->addHTML("</div>");
	}
	
	// This function will add the works with relations based on how the budget
	// has been filled out.
	private static function addWorksWithRelation($blob){
	    global $wgUser;
	    $me = Person::newFromId($wgUser->getId());
	    $data = $blob->getData();
        $budget = new Budget("XLS", REPORT2_STRUCTURE, $data);
        
        // First select the projects
        $projects = $budget->copy()->select(V_PROJ, array())->where(V_PROJ)->xls;
        foreach($projects as $row){
            foreach($row as $proj){
                $project = Project::newFromName($proj->getValue());
                if($project != null && $project->getName() != null){
                    // Now look for the people
                    $people = $budget->copy()->select(V_PROJ, array($project->getName()))->where(V_PERS)->xls;
                    foreach($people as $row){
                        foreach($row as $pers){
                            $person = null;
                            $pers = str_replace("'", "", $pers->getValue());
                            $names = explode(',', $pers);
                            if(count($names) > 1){
                                $name = $names[1].' '.$names[0];
                                $person = Person::newFromNameLike($name);
                                if($person == null || $person->getName() == null){
                                    try{
                                        $person = Person::newFromAlias($name);
                                    }
                                    catch(Exception $e){

                                    }
                                }
                            }
                            if($person == null || $person->getName() == null){
                                $person = Person::newFromNameLike($pers);
                            }
                            if($person == null || $person->getName() == null){
                                try{
                                    $person = Person::newFromAlias($pers);
                                }
                                catch(Exception $e){
                                
                                }
                            }
                            if($person != null && $person->getName() != null){
                                // Ok, it is safe to add this person as a relation
                                $_POST['type'] = WORKS_WITH;
                                $_POST['name1'] = $me->getName();
                                $_POST['name2'] = $person->getName();
                                $_POST['project_relations'] = $project->getName();
                                APIRequest::doAction('AddRelation', true);
                            }
                        }
                    }
                }
            }
        }
	}
	
	//Report version for HQP's
    static function NIReport(){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $reporteeId;
	    
	    //Define report address for our milestone questionnaire
	    $year = REPORTING_YEAR;
	    $uid = $reporteeId; //$wgUser->getId();
		$blob_type = BLOB_TEXT;
		$rptype = RP_RESEARCHER;
		
    	$nce_activity_types = array(
	        RES_RESACT_EXCELLENCE => "A. Excellence of the Research Program",
	        RES_RESACT_HQPDEV => "B. Development of Highly Qualified Personnel",
	        RES_RESACT_NETWORKING => "C. Networking and Partnerships",
	        RES_RESACT_KTEE => "D. Knowledge and Technology Exchange and Exploitation"        
	    );
	    
	    $other_activity_types = array(
	        RES_RESACT_NETMAN => "E. Management of the Network",
	        RES_RESACT_OTHER => "Additional Comments",
	        RES_RESACT_BENEF => "Benefits from being involved in the Network"	        
	    );
        
        $hqp_activity_types = array(
	        RES_RESACT_EXCELLENCE => HQP_RESACT_EXCELLENCE,
	        RES_RESACT_NETWORKING => HQP_RESACT_NETWORKING,
	        RES_RESACT_KTEE => HQP_RESACT_KTEE
	    );
        
		$rep_addr = ReportBlob::create_address($rptype, RES_RESACTIVITY, RES_RESACT_OVERALL, 0);
		$overall_activity_blb = new ReportBlob($blob_type, $year, $uid, 0);

	    //Form submit processing
	    $report_submit = ($_POST && isset($_POST['ni_report']))? $_POST['ni_report'] : "";
	    if( $report_submit === "Save" ){

            //Save overall activity
            $overall_activity = @$_POST['overall_activity'];
	        $overall_activity_blb->store($overall_activity, $rep_addr);

	        $activities = @$_POST['activities'];
	        if($activities != null){
	            foreach( $activities as $a_type => $projects){   
	                foreach ($projects as $p_id => $comment){
	                    $a_rep_addr = ReportBlob::create_address($rptype, RES_RESACTIVITY, $a_type, 0);
	                    $blob = new ReportBlob($blob_type, $year, $uid, $p_id);
	                    $blob->store($comment, $a_rep_addr);
                    }
	            }
	        }
	    }


	    //Fetch the previously saved data, if exists
	    $overall_activity_blb->load($rep_addr);
    	$overall_activity = $overall_activity_blb->getData();

	    //Render the page
	    $wgOut->setPageTitle("NI Report");
	    $pg = "$wgServer$wgScriptPath/index.php/Special:{$wgTitle->getText()}";
	    //$person = Person::newFromId($wgUser->getId());
	    $person = Person::newFromId($reporteeId);

	    $custom_js =<<<EOF
	        <script type='text/javascript'>
    		$(document).ready(function () { 
    		     $('#overall_activity').limit('3600','#charsLeft_overall_activity'); 
    		     $('#lnk_comments_overall').click(function(e) {
                       e.preventDefault();        
                       $('#div_comments_overall').toggle();
                  });              
EOF;

        $activity_overview_html =<<<EOF
            <span style="display:block;" id="ni_report_wrapper">
            <h2>I. Executive Summary</h2>
            <br />
            <h3><a href="#" id="lnk_comments_overall">Overview of NI Activity</a></h3>
            <div class="pdf_show" style="display:none;" id="div_comments_overall">
                <p class="pdf_hide"><span class="curr_chars">(currently <span id="charsLeft_overall_activity">0</span> chars out of allowed 3600.)</span></p>
                <textarea id="overall_activity" rows="15" style="" 
                    name="overall_activity">$overall_activity</textarea><br />   
            </div>
EOF;

        $wgOut->addHTML($activity_overview_html);  


	    $activities_html = "<br /><h2>II. NCE Criteria</h2><br />";
	    //NCE ACTIVITIES
	    foreach ($nce_activity_types as $a_type => $a_lbl){
	        $a_rep_addr = ReportBlob::create_address($rptype, RES_RESACTIVITY, $a_type, 0);

	        //Links, divs and triggers
	        $lnk_id = "lnk_comments_$a_type";
            $div_id = "div_comments_$a_type";
            $preview_lnk_id = "lnk_preview_$a_type";
            $preview_div_id = "div_preview_$a_type";
            
            $custom_js .=<<<EOF
                $("#$preview_div_id").dialog({ autoOpen: false, height: 400, width: 600 });
                $('#$lnk_id').click(function(e) {
                      e.preventDefault();        
                      $('#$div_id').toggle();
                 });
                 $('#$preview_lnk_id').click(function(e) {
                       e.preventDefault();
                       report = "";
                       totalchars = 0;
                       $('textarea[name*="activities[$a_type]"]').
                        each(function(index) {
                            report += "<p>"+$(this).html()+"</p>";
                            totalchars += $(this).text().length;
                        });
                        if(totalchars > 1800){
                            title = "Report Preview <span class='curr_chars'>(Currently "+totalchars+" chars - limit of 1800 chars exceeded.)";
                            $('#ui-dialog-title-$preview_div_id').html(title);
                        }
                        else{
                            title = "Report Preview <span class='curr_chars'>(Currently "+totalchars+" chars  out of allowed 1800.)";
                            $('#ui-dialog-title-$preview_div_id').html(title);
                        }
                        $('#$preview_div_id').html(report);
                        $('#$preview_div_id').dialog('open');
                  });
                  
EOF;
            

            $a_link = '<a id="'.$lnk_id.'" href="#">'.$a_lbl.'</a>';
            $preview_link = '<span class="pdf_hide" style="font-size:11px;"><a id="'. $preview_lnk_id.'" href="#">(Preview)</a></span>';
	        
	        $activities_html .=<<<EOF
	            <h3>$a_link &nbsp;&nbsp;$preview_link</h3>
	            <div title="Report Preview" class="pdf_hide" style="white-space: pre-line;" id="$preview_div_id"></div>
	            <div class="nce_section pdf_show" style="display: none;" id="$div_id">
EOF;
            
            $projects_ar = $person->getProjects();
            $section_char_count = 0;
            foreach ($projects_ar as $p){
                $p_name = $p->getName();
                $p_id = $p->getId();
                //$p_link = "<a target='_blank' href='{$p->getUrl()}'>". $p_name . "</a>";
                $p_link = $p_name;
                //Load HQP comments if they exist
                $hqp_pr_activity_comments = "";
        	    if( isset($hqp_activity_types[$a_type]) ){
        	        $hqp_rep_addr = ReportBlob::create_address(RP_HQP, HQP_RESACTIVITY, $hqp_activity_types[$a_type], 0);
        	        
        	        $hqp_objs = $p->getAllPeopleDuring("HQP");
                    foreach ($hqp_objs as $h){
                        $h_sups = $h->getSupervisors(true);

                        $supervisor = false;
                        foreach ($h_sups as $s){
                            if ( $s->getId() == $person->getId() ){
                                $supervisor =  true;
                                break;
                            }
                        }

                        if($supervisor){
                            $hqp_blob = new ReportBlob($blob_type, $year, $h->getId(), $p_id);
                            $hqp_blob->load($hqp_rep_addr);
                            $hqp_comment = $hqp_blob->getData();
                            if($hqp_comment){
                                $hqp_pr_activity_comments .= $h->getNameForForms() . ":<br /><i style='margin:10px;display:block;'>".
                                    $hqp_comment . "</i><br />";
                            }
    		            }
                    }
                    $hqp_comm_dialog_id = "hqpcomm_".$a_type."_".$p_id;
                    $custom_js .= "$(\"#$hqp_comm_dialog_id\").dialog({ autoOpen: false, height: 300, width: 500 });";
        	    }

                //Load previously saved data
                $p_blob = new ReportBlob($blob_type, $year, $uid, $p_id);
                $p_blob->load($a_rep_addr);
                $p_blob_data = $p_blob->getData();
                
                $custom_js .= "$('#activities\\\[$a_type\\\]\\\[$p_id\\\]').limit('1800','#charsLeft_activities\\\[$a_type\\\]\\\[$p_id\\\]');";
                
                $activities_html .=<<<EOF
                    <p class="project_header">$p_link &nbsp;
                    <span class="pdf_hide">
                        (currently <span id="charsLeft_activities[$a_type][$p_id]">0</span> chars.)</span></p>
                    <textarea rows="10" style="" id="activities[$a_type][$p_id]"  
                        name="activities[$a_type][$p_id]">$p_blob_data</textarea>
EOF;
                if($hqp_pr_activity_comments){
                    $activities_html .=<<<EOF
                    <a class="pdf_hide" style="font-style:italic; font-weight:bold; float:right;" href="#" onclick="$('#$hqp_comm_dialog_id').dialog('open'); return false;">See HQP Comments</a>
                    <div title="HQP Comments" class="pdf_hide" style="white-space: pre-line;" id="$hqp_comm_dialog_id">$hqp_pr_activity_comments</div>
EOF;
                }
                
                $activities_html .= "<br />";
                //echo strlen(stripslashes($p_blob_data))." === <span>"".$p_blob_data</span><br /><br />";
                $section_char_count += strlen($p_blob_data);

            }   
            $activities_html .= "<span class='section_char_count' style='display:none;'>($section_char_count char. out of 1800 allowed)</span></div>";     

	    }
	    
	    //OTHER ACTIVITIES
	    $i=0;
	    foreach ($other_activity_types as $a_type => $a_lbl){
	        $a_rep_addr = ReportBlob::create_address($rptype, RES_RESACTIVITY, $a_type, 0);
            
            if($i == 1){
                $activities_html .= "<br /><h2>III. Supplemental</h2><br />";
                $custom_js .= "$('#activities\\\[$a_type\\\]\\\[0\\\]').limit('600','#charsLeft_activities\\\[$a_type\\\]\\\[0\\\]');";
                $allowed = 600;
            }
            else if($i == 2){
                $custom_js .= "$('#activities\\\[$a_type\\\]\\\[0\\\]').limit('600','#charsLeft_activities\\\[$a_type\\\]\\\[0\\\]');";
                $allowed = 600;
            }
            else{
                $custom_js .= "$('#activities\\\[$a_type\\\]\\\[0\\\]').limit('1800','#charsLeft_activities\\\[$a_type\\\]\\\[0\\\]');";
                $allowed = 1800;
            }
            
	        //Links, divs and triggers
	        $lnk_id = "lnk_comments_$a_type";
            $div_id = "div_comments_$a_type";
            
            $custom_js .=<<<EOF
                $('#$lnk_id').click(function(e) {
                      e.preventDefault();        
                      $('#$div_id').toggle();
                 });
EOF;

            $a_link = '<a id="'.$lnk_id.'" href="#">'.$a_lbl.'</a>';
	        
	        $activities_html .= "<h3>$a_link</h3><div class='pdf_show' style='display: none;' id=\"$div_id\">";
	        $activities_html .= "<p class='pdf_hide'><span class='curr_chars'>
                (currently <span id='charsLeft_activities[$a_type][0]'>0</span> chars out of allowed $allowed.)</span></p>";
                
	        $a_blob = new ReportBlob($blob_type, $year, $uid, 0);
            $a_blob->load($a_rep_addr);
            $a_blob_data = $a_blob->getData();
            
            $rows = $allowed/120;
            $activities_html .=<<<EOF
                <textarea rows="$rows" style="" id="activities[$a_type][0]" name="activities[$a_type][0]">$a_blob_data</textarea>
                </div>
EOF;
	    $i++;
	    }
	    
	    $custom_js .= "});</script>";
        $wgOut->addScript($custom_js);

	    $wgOut->addHTML($activities_html."</span>");   
	    //$wgOut->addHTML("<br /><br /><input type='submit' name='ni_report' value='Save' class='report_button' /></div>");
	}
	
	function NISubmitReport(){
        global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $reportList, $reporteeId;
        //$this->_extradbg = "";
    	$submit_status = "";
    	$person = Person::newFromId($reporteeId);

        $submit_action = "";
        if($_POST){
            if(isset($_POST['action_type']) && $_POST['action_type'] != '' ){
                $submit_action = $_POST['action_type'];
            }
        }
        $sto = new ReportStorage($person);
    	switch ($submit_action) {
    	case 'ni_download_report':
    		$tok = ($_POST && isset($_POST['pdftoken']))? $_POST['pdftoken'] : "";
    		if (! empty($tok)) {
    			$pdf = $sto->fetch_pdf($tok);
    			$len = $sto->metadata('len_pdf');
    			if ($pdf === false || $len == 0) {
    				$wgOut->addHTML("<h4>Warning</h4><p>Could not retrieve PDF for report ID<tt>{$tok}</tt>.  Please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>, and include the report ID in your request.</p>");
    			}
    			else {
    				$tst = $sto->metadata('timestamp');
    				// Make timestamp usable in filename.
    				$tst = strtr($tst, array(':' => '', '-' => '', ' ' => '_'));
    				$name = $person->getName() . "_report-{$tst}.pdf";
    				if ($len == 0) {
    					// No data, or no report at all.
    					$wgOut->addHTML("No reports available for download.");
    					return false;
    				}
    				// Good -- transmit it.
    				$wgOut->disable();
    				ob_clean();
    				header('Content-Type: application/pdf');
    				header('Content-Length: ' . $len);
    				header('Content-Disposition: attachment; filename="'.$name.'"');
    				header('Cache-Control: private, max-age=0, must-revalidate');
    				header('Pragma: public');
    				ini_set('zlib.output_compression','0');
    				echo $pdf;
    				return true;
    			}
    		}
    		break;

    	case 'ni_submit_report':
    		//$subconf = ($_POST && isset($_POST['finalsubmissioncheck']))? $_POST['finalsubmissioncheck'] : "";
    		
    		/*if ($subconf === false) {
    			// Checkbox not selected.
    			$submit_status = "<tr><td style='background-color: #FF6347'>In order to successfully submit, you need to mark the submission checkbox,<br />asserting that you have reviewed the report generated.\n";
    		}
    		else {*/
    		// Try to mark report as submitted, and generate a status message.
			//$tok = Report::post_field($_POST, 'markrptok');
			$tok = ($_POST && isset($_POST['markrptok']))? $_POST['markrptok'] : "";
			if ($tok === false) {
				$submit_status = "<tr><td style='background-color: #FF6347'>Report not found. Please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>.\n";
			}
			else {
				$sto->select_report($tok);
				switch ($sto->mark_submitted($tok)) {
				case 0:
					$submit_status = "<tr><td style='background-color: #FF6347'>Individual report ID #<tt>{$tok}</tt> could not be marked as submitted. Please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>.\n";
					break;

				case 1:
					$submit_status = "<tr><td style='background-color: #90EE90'>Report successfully submitted.\n";
					break;

				case 2:
					$submit_status = "<tr><td style='background-color: #FF6347'>Report was already marked as submitted.\n";
					break;
				}
			}
    		//}
    		break;
    	} //action check


        //Page processing
        
        $tok = false;
        $tst = '';
        $len = 0;
        $sub = 0;
    	
    	$check = $sto->list_reports_current($person->getId());
    	if (count($check) > 0) {
    		$tok = $check[0]['token'];
    		$sto->select_report($tok);    	
    		$tst = $sto->metadata('timestamp');
    		$len = $sto->metadata('len_pdf');
    		$sub = $sto->metadata('submitted');
    	}
    	//$tok = $sto->metadata('token');
        generateHQPReportsHTML($person, REPORTING_YEAR, false, true);
        $chunk = "<input type='hidden' id='action_type' name='action_type' value='' />";

    	$chunk .= "1. Generate a new report for submission</h4>
    <p>Generate a report with the data submitted: <button type='button' onclick='javascript:generateReport();' name='ni_generate_report' value='ni_generate_report'>Generate report</button></p>
    <h4>2. Download the report submitted for reviewing</h4>\n";

    	// Present some data on available reports.
    	if ($tok === false) {
    		// No reports available.
    		$style1 = "";
    		$style2 = "display:none;";
    	}
    	else {
    	    $style2 = "";
    		$style1 = "display:none;";
    	}    
		
		//echo "SUB=$sub; $tok";
		$subm = "";
		if ($sub == 1) {
			$subm = "Yes";
			$subm_style = "background-color:#008800;";
		}
		else {
			$subm = "No";
			$subm_style = "background-color:red;";
		}
		$chunk .= 
		"<p><table cellspacing='8'>
         <tr><th>Identifier</th><th>Generated (GMT " . date('P') . ")</th><th>Download</th><th>Submitted?</th></tr>
         <tr><td><tt id='ex_token'>{$tok}</tt></td><td id='ex_time'>{$tst}</td>
         <td><input id='ex_token2' type='hidden' name='pdftoken' value='{$tok}'/>
         <span style='$style1' id='no_download_button'>No PDF Available</span><button style='$style2' id='download_button' onclick='javascript:submitReportAction(\"ni_download_report\");'>
               Download report as PDF
         </button>
         </td>
         <td align='center' id='submit_status_cell' style='$subm_style'><b>$subm</b></td>
         </table></p>
         <h4>3. Submit the report</h4>
         <p>You can submit your report for evaluation. Make sure you review it before submitting.<br />Please note:</p>
         <ul>
         <li>If you need to make a correction to your report that is already submitted, you can generate and submit again.</li>
         <li>The most recent submission is used for evaluation.</li>
         <li>If no reports were submitted, the report most recently generated is used for evaluation.</li>
         <li>If you encounter any issues, please contact
         <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>.</li>
         </ul></p>\n";
		
		$visibility = "display:none;"; //repoting is now frozen
		if ($sub == 1 || $tok === false || FROZEN ) {
		    $visibility = "display:none;";
		}    
		
		$chunk .= 
		"<div id='report_submit_div' style='$visibility'><p>
        <table border='0' style='margin-top: 20px;' cellpadding='10'>
        <tr><td style='background-color: #E6E6FA'>
        <input type='hidden' id='markrptok' name='markrptok' value='{$tok}'>
        <!--input type='checkbox' name='finalsubmissioncheck' /-->
        <button type='button' onclick='javascript:submitReportAction(\"ni_submit_report\");'>
            Submit final report
        </button>
        </td></tr>
        </table>
        $submit_status
        </p></div>";
		

		// Some space.
		$chunk .= "<p>&nbsp;</p>";
    	

    //		$dbg = (array)$sto->fetch_data($tok);
    //		$chunk .= "<h4>Debugging</h4>\n<pre>\nExtra debug:\n{$this->_extradbg}\nPDF Data:\n" . print_r($dbg, true) . "</pre>\n";

    	$wgOut->setPageTitle("{$person->getName()}: Review & Submit");
    	$wgOut->addHTML($chunk);
    }
    
    static function HQPSubmitReport($year=REPORTING_YEAR){
        global $wgOut, $reporteeId, $wgUser, $wgServer, $wgScriptPath;
        
        $submit_status = "";
    	$person = Person::newFromId($reporteeId);

        $submit_action = "";
        if($_POST){
            if(isset($_POST['action_type']) && $_POST['action_type'] != '' ){
                $submit_action = $_POST['action_type'];
            }
        }
        $sto = new ReportStorage($person);
    	switch ($submit_action) {
    	case 'hqp_download_report':
    		$tok = ($_POST && isset($_POST['pdftoken']))? $_POST['pdftoken'] : "";
    		if (! empty($tok)) {
    			$pdf = $sto->fetch_pdf($tok);
    			$len = $sto->metadata('len_pdf');
    			if ($pdf === false || $len == 0) {
    				$wgOut->addHTML("<h4>Warning</h4><p>Could not retrieve PDF for report ID<tt>{$tok}</tt>.  Please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>, and include the report ID in your request.</p>");
    			}
    			else {
    				$tst = $sto->metadata('timestamp');
    				// Make timestamp usable in filename.
    				$tst = strtr($tst, array(':' => '', '-' => '', ' ' => '_'));
    				$name = $person->getName() . "_report-{$tst}.pdf";
    				if ($len == 0) {
    					// No data, or no report at all.
    					$wgOut->addHTML("No reports available for download.");
    					return false;
    				}
    				// Good -- transmit it.
    				$wgOut->disable();
    				ob_clean();
    				header('Content-Type: application/pdf');
    				header('Content-Length: ' . $len);
    				header('Content-Disposition: attachment; filename="'.$name.'"');
    				header('Cache-Control: private, max-age=0, must-revalidate');
    				header('Pragma: public');
    				ini_set('zlib.output_compression','0');
    				echo $pdf;
    				return true;
    			}
    		}
    		break;

    	case 'hqp_submit_report':
    		$subconf = ($_POST && isset($_POST['finalsubmissioncheck']))? $_POST['finalsubmissioncheck'] : "";
    		
			$tok = ($_POST && isset($_POST['markrptok']))? $_POST['markrptok'] : "";
			if ($tok === false) {
				$submit_status = "<tr><td style='background-color: #FF6347'>Report not found. Please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>.\n";
			}
			else {
				$sto->select_report($tok);
				switch ($sto->mark_submitted($tok)) {
				case 0:
					$submit_status = "<tr><td style='background-color: #FF6347'>Individual report ID #<tt>{$tok}</tt> could not be marked as submitted. Please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>.\n";
					break;

				case 1:
					$submit_status = "<tr><td style='background-color: #90EE90'>Report successfully submitted.\n";
					
					$sql = "SELECT *
					        FROM `grand_role_request`
					        WHERE `created` = 'pending'
					        AND `user` = '{$person->getName()}'
					        ORDER BY `id` DESC LIMIT 1";
					$data = DBFunctions::execSQL($sql);
					
					if(count($data) > 0){
					    
		                $r_comments = $data[0]['comment'];
		                $comment = "";
		                $exploded = explode("HQP::", $r_comments);
		                if(isset($exploded[1])){
		                    $exploded = explode("::", $exploded[1]);
		                    if(isset($exploded[0])){
		                        $comment = $exploded[0];
		                        // Remove the HQP comment from the request
		                        $r_comments = str_replace(HQP."::".$comment." ::", "", $r_comments);
		                        $r_comments = str_replace(HQP."::".$comment."::", "", $r_comments);
		                        $r_comments = str_replace(HQP."::".$comment, "", $r_comments);
		                    }
		                }
		                $r_effectiveDates = $data[0]['effective_date'];
		                $exploded = explode("HQP::", $r_effectiveDates);
		                $date = "";
		                if(isset($exploded[1])){
		                    $exploded = explode("::", $exploded[1]);
		                    if(isset($exploded[0])){
		                        $date = $exploded[0];
		                        // Remove the HQP dates from the request
		                        $r_effectiveDates = str_replace(HQP."::".$date." ::", "", $r_effectiveDates);
		                        $r_effectiveDates = str_replace(HQP."::".$date."::", "", $r_effectiveDates);
		                        $r_effectiveDates = str_replace(HQP."::".$date, "", $r_effectiveDates);
		                    }
		                }
		                
		                $nss = array();
		                foreach($person->getRoles() as $role){
		                    if($role->getRole() != HQP){
		                        $nss[] = $role->getRole();
		                    }
		                }
		                
		                $otherData = unserialize($data[0]['other']);
		                $_POST['role'] = implode(", ", $nss);
					    $_POST['comment'] = HQP.'::'.$comment;
					    $_POST['effectiveDates'] = HQP.'::'.$date;
					    $_POST['user'] = $person->getName();
					    $_POST['requesting_user'] = $data[0]['requesting_user'];
					    $_POST['type'] = 'ROLE';
					    $_POST['id'] = '-1';

					    $wgUser = User::newFromId(4); // Pretend to be Admin for a second
		                EditMember::handleAdminAccept();
		                $wgUser = User::newFromId($person->getId()); // Reset to current user
		                
		                $sql = "UPDATE `grand_role_request`
					            SET `created` = 'true'
					            WHERE `user` = '{$person->getName()}'";
					    DBFunctions::execSQL($sql, true);
					    $wgOut->clearHTML();
					    if($date == ""){
					        $date = date('Y-m-d');
					    }
					    $wgOut->addHTML("HQP Inactivation Report was submitted.  Effective {$date}, you will be removed from the HQP role.  You can view you submitted report at <a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive' target='_blank'>Report Archive</a>.  If you notice any problems with the report, then please contact <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>.");
					    Notification::addNotification($person, Person::newFromName($data[0]['requesting_user']), "HQP Inactivation Report Complete", "{$person->getNameForForms()} completed their HQP inactivation report.", "$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf=$tok");
					    Notification::addNotification($person, $person, "HQP Inactivation Report Complete", "{$person->getNameForForms()} completed their HQP inactivation report.", "$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf=$tok");
					    $wgOut->output();
					    $wgOut->disable();
					    return;
		            }
					break;
				case 2:
					$submit_status = "<tr><td style='background-color: #FF6347'>Report was already marked as submitted.\n";
					break;
				}
			}
    		break;
    	} //action check
        
        $tok = false;
        $tst = '';
        $len = 0;
        $sub = 0;
        
        $check = $sto->list_reports_current($person->getId());
    	if (count($check) > 0) {
    		$tok = $check[0]['token'];
    		$sto->select_report($tok);    	
    		$tst = $sto->metadata('timestamp');
    		$len = $sto->metadata('len_pdf');
    		$sub = $sto->metadata('submitted');	
    	}
        $chunk = "<input type='hidden' id='action_type' name='action_type' value='' />";

    	$chunk .= "<h4>1. Generate a new report for submission</h4>
    <p>Generate a report with the data submitted: <button type='button' onclick='javascript:generateReport();' name='hqp_generate_report' value='hqp_generate_report'>Generate report</button></p>
    <h4>2. Download the report submitted for reviewing</h4>\n";

    	// Present some data on available reports.
    	if ($tok === false) {
    		// No reports available.
    		$style1 = "";
    		$style2 = "display:none;";
    	}
    	else {
    	    $style2 = "";
    		$style1 = "display:none;";
    	}
		
		$subm = "";
		if ($sub == 1) {
			$subm = "Yes";
			$subm_style = "background-color:#008800;";
		}
		else {
			$subm = "No";
			$subm_style = "background-color:red;";
		}
		$chunk .= 
		"<p><table cellspacing='8'>
         <tr><th>Identifier</th><th>Generated (GMT " . date('P') . ")</th><th>Download</th><th>Submitted?</th></tr>
         <tr><td><tt id='ex_token'>{$tok}</tt></td><td id='ex_time'>{$tst}</td>
         <td><input id='ex_token2' type='hidden' name='pdftoken' value='{$tok}'/>
         <span style='$style1' id='no_download_button'>No PDF Available</span><button style='$style2' id='download_button' onclick='javascript:submitReportAction(\"hqp_download_report\");'>
               Download report as PDF
         </button>
         </td>
         <td align='center' id='submit_status_cell' style='$subm_style'><b>$subm</b></td>
         </table></p>
         <h4>3. Submit the report</h4>
         <p>You can submit your report for evaluation. Make sure you review it before submitting.<br />Please note:</p>
         <ul>
         <li>After submitting the report, you will no longer have access to this page.</li>
         <li>The submitted report will be used in the next yearly reporting</li>
         <li>If you encounter any issues, please contact
         <a href='mailto:support@forum.grand-nce.ca'>support@forum.grand-nce.ca</a>.</li>
         </ul></p>\n";
		
		$visibility = "";
		//$visibility = "display:none;"; //repoting is now frozen
		if ($sub == 1 || $tok === false || FROZEN ) {
		    $visibility = "display:none;";
		}    
		
		$chunk .= 
		"<div id='report_submit_div' style='$visibility'><p>
        <table border='0' style='margin-top: 20px;' cellpadding='10'>
        <tr><td style='background-color: #E6E6FA'>
        <input type='hidden' id='markrptok' name='markrptok' value='{$tok}'>
        <!--input type='checkbox' name='finalsubmissioncheck' /-->
        <button type='button' onclick='javascript:submitReportAction(\"hqp_submit_report\");'>
            Submit final report
        </button>
        </td></tr>
        </table>
        $submit_status
        </p></div>";
		

		// Some space.
		$chunk .= "<p>&nbsp;</p>";
		$wgOut->addHTML($chunk);
    }
	
	//Report version for HQP's
    static function PNIAdminBudget(){
	    global $wgUser, $wgOut, $wgTitle, $wgServer, $wgScriptPath, $new_post, $reportList, $reporteeId, $viewOnly;
	    
	    //Define report address for our milestone questionnaire
	    $year= REPORTING_YEAR;
	    $uid = $reporteeId; //$wgUser->getId();
		$blob_type=BLOB_EXCEL;
		$rptype = RP_RESEARCHER;
    	$section = RES_BUDGET_PNIADMIN;
    	$item = 0;
    	$subitem = 0;
		$rep_addr = ReportBlob::create_address($rptype,$section,$item,$subitem);
		$budget_blob = new ReportBlob($blob_type, $year, $uid, 0);
		
		$just_rep_addr = ReportBlob::create_address($rptype, $section, RES_BUD_JUSTIF, 0);
		$just_blb = new ReportBlob(BLOB_TEXT, $year, $uid, 0);
		
		$report_submit = ($_POST && isset($_POST['pni_admin_budget']))? $_POST['pni_admin_budget'] : "";
		if(($report_submit === "Save" || $report_submit === "Upload") && 
		   isset($_FILES['budget'])){
		    if(file_exists($_FILES['budget']['tmp_name'])){
                $budget = file_get_contents($_FILES['budget']['tmp_name']);
	            $budget_blob->store($budget, $rep_addr);
	            
	            // Add new 'Works With' relation
	            $budget_blob->load($rep_addr);
	            
	            Dashboard::addWorksWithRelation($budget_blob);
	        }
	        
	        //Save budget justif
            $budget_just = $_POST['budget_just'];
	        $just_blb->store($budget_just, $just_rep_addr);
	    }
	    
	    //Fetch the previously saved budget justif, if exists
	    $just_blb->load($just_rep_addr);
    	$budget_just = $just_blb->getData();
	    
	    $custom_js =<<<EOF
	        <script type='text/javascript'>
    		$(document).ready(function () { 
    		     $('#budget_just').limit('3600','#charsLeft_budget_just'); 
    		     $('#lnk_budget_just').click(function(e) {
                       e.preventDefault();        
                       $('#div_budget_just').toggle();
                  });
            });
            </script>                    
EOF;
        $wgOut->addScript($custom_js);
	    
	    $justification_html =<<<EOF
	        <div id="ni_budget_wrapper">
	        <h3 class='pdfnodisplay'><a href="#" id="lnk_budget_just">Budget Justification</a></h3>
            <div id="div_budget_just" class='pdfnodisplay'>
                <p><span class="curr_chars pdf_hide">(currently <span id="charsLeft_budget_just">0</span> chars out of allowed 3600.)</span></p>
                <textarea id="budget_just" rows="10" style="" 
                    name="budget_just">$budget_just</textarea><br />   
            </div><br />
EOF;
        $wgOut->addHTML($justification_html);
		
		// Allow user to download the budget template
		$wgOut->addHTML("<div class='pdfnodisplay'>");
		$wgOut->addHTML("<h2>Download Budget Template</h2> <ul><li><a href='$wgServer$wgScriptPath/data/GRAND Researcher Budget Request (2012-13).xls'>2012-13 Budget Template</a></li></ul>" );
		
		if(!$viewOnly){
		    $wgOut->addHTML("<h2>Budget Upload</h2>
		                    <input type='file' name='budget' />
		                    <input type='hidden' name='pni_admin' value='true' />
		                    <input type='submit' name='pni_admin_budget' value='Upload' />");
		}  
		
		$wgOut->addHTML("</div>");                  
		$result = $budget_blob->load($rep_addr);
		$data = $budget_blob->getData();
		
		// Show a preview of the budget
		$wgOut->addHTML("<h2 class='pdfnodisplay'>Budget Preview</h2>");
		if($result !== false){
		    $budget = new Budget("XLS", REPORT2_STRUCTURE, $data);
		    $wgOut->addHTML($budget->copy()->filterCols(V_PROJ, array(""))->render());
		}
		else{
		    $wgOut->addHTML("You have not yet uploaded a budget");
		}
		
		//$wgOut->addHTML("<br /><input type='submit' name='pni_admin_budget' value='Save' /></div>");
	}
	
}
	
?>
