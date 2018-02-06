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
            
            $role = ($h->isRole(HQP))? "HQP" : (($h->isRole(NI))? "NI" : "");
            if($h->isActive() && $role != ""){
                $html.=<<<EOF
                    <li>
                    <a target='_blank' href='{$h->getUrl()}'>
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
    

    static function niDetails($hqps, $year=YEAR){   
        global $wgServer, $wgScriptPath, $config;
        $url_prefix = "$wgServer$wgScriptPath/index.php/";
        
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

            $role = ($h->isRole(HQP))? "HQP" : (($h->isRole(NI))? "NI" : "");
            if($h->isActive() && $role != ""){
                $html.=<<<EOF
                    <li>
                    <a target='_blank' href='{$h->getUrl()}'>
                    $hqp_name_read</a>, {$role}{$hqp_uni}{$hqp_type}
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
                $papers[] = $p;
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
        
        $html = "";
        if(is_array($papers)){
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
                    $pap_status = $p->getStatus();
                    $html.= "EOF
                        <li>
                        <a target='_blank' href='{$p->getUrl()}'>
                        $pap_title
                        </a>$event_title; $pap_status; Authors: $author_str
                        </li>";
                }
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
            
            if($year === false || ($c->getStartYear() <= $year && $c->getEndYear() >= $year)){
    			$im_receiver = ($receiver instanceof Person)? $receiver->isReceiverOf($c) : true;
                if($c->getTotal() > 0 && $im_receiver){
                    $contributions[] = $c; 
                }
            }
        }
        
        return $contributions;     
    }
    
	static function getPartners($project, $receiver, $year=YEAR){
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
				$champs = array_merge($champs, $pr->getAllPeopleDuring(CHAMP, $year."-01-01 00:00:00", $year."-12-31 23:59:59")); //all champions on a project throughout the year
			}
			
			foreach ($champs as $champ){
				if( $receiver->relatedTo($champ, "Works With") ){
					if( $partner_name = $champ->getUni() ){
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
            
            $con_year = $c->getStartYear();
            $con_name = $c->getName();
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
            $con_year = $c->getStartYear();
            $con_name = $c->getName();
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
}
	
?>

