<?php
$dir = dirname(__FILE__) . '/';

$wgHooks['UnknownAction'][] = 'getack';

$wgSpecialPages['ReviewerConflicts'] = 'ReviewerConflicts';
$wgExtensionMessagesFiles['ReviewerConflicts'] = $dir . 'ReviewerConflicts.i18n.php';
$wgSpecialPageGroups['ReviewerConflicts'] = 'grand-tools';

function runReviewerConflicts($par) {
	ReviewerConflicts::run($par);
}


class ReviewerConflicts extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('ReviewerConflicts');
		SpecialPage::SpecialPage("ReviewerConflicts", CNI.'+', true, 'runReviewerConflicts');
	}
	
	static function run(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
	    
	   
	    $overall = array();
	    $projects = array();
	    $nis = array();
	    
	    
	    $people = Person::getAllPeople();
	    /*foreach($people as $person){
	        $roles = $person->getRoles();
	        foreach($roles as $role){
	            if($role->getRole() == HQP){
	                $hqps[$person->getId()] = $person;
	            }
	            else if($role->getRole() == PNI){
	            	$pnis[$person->getId()] = $person;
	            	$nis[$person->getId()] = $person;
	            }
	            else if($role->getRole() == CNI){ 
	                $cnis[$person->getId()] = $person;  
	                $nis[$person->getId()] = $person;
	            }
	        }
	    }*/
	    
	    $wgOut->setPageTitle("Reviewer Conflicts");
	    $wgOut->addHTML("<div id='ackTabs'>
	                        <ul>
		                        <li><a href='#pnis'>PNIs</a></li>
                                <li><a href='#cnis'>CNIs</a></li>
		                        <li><a href='#projects'>Projects</a></li>
	                        </ul>");

        $cnis = Person::getAllPeople(CNI);
        $pnis = Person::getAllPeople(PNI);
		$wgOut->addHTML("<div id='pnis'>");
	    $overall['PNI'] = ReviewerConflicts::niTable($pnis, 'PNI');           
	    $wgOut->addHTML("</div><div id='cnis'>");
        $overall['CNI'] = ReviewerConflicts::niTable($cnis, 'CNI');           
        $wgOut->addHTML("</div><div id='projects'>");
		$overall['PROJECTS'] = ReviewerConflicts::projectTable($projects);
		$wgOut->addHTML("</div></div>");
	 	
	  
	    $wgOut->addScript("<script src='../scripts/jquery.tablesorter.js' type='text/javascript' charset='utf-8';></script>
                           <script src='../scripts/jquery.qtip.min.js' type='text/javascript' charset='utf-8';></script>
	    				   <script type='text/javascript'>
                                $(document).ready(function(){
                                    $('td[title], input[title]').qtip({position: {my: 'top left', at: 'center center'}});
	                                $('.indexTable').dataTable({'iDisplayLength': 100,
	                                                            'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]});
                                    $('.dataTables_filter input').css('width', 250);
                                    $('#ackTabs').tabs();
                                    
                                    $('input[name=date]').datepicker();
                                    $('input[name=date]').datepicker('option', 'dateFormat', 'dd-mm-yy');
                                	$('#CNI_conflicts').tablesorter({ 
										sortList: [[0,0], [1,0], [2,0], [3,0]],
									});
                                    $('#PNI_conflicts').tablesorter({ 
                                        sortList: [[0,0], [1,0], [2,0], [3,0]],
                                    });
	    							$('#project_conflicts').tablesorter({ 
										sortList: [[0,0], [1,0]],
									});
                                });
                            </script>");
    	

    }
    
    static function overallTable($overall){
    	global $wgOut;
    	$wgOut->addHTML("<table class='indexTable' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
	                        <thead>
	                            <tr bgcolor='#F2F2F2'>
	                                <th>Type</th>
	                                <th>All</th>
	                                <th>Started Report</th>
	                                <th>Uploaded Budget</th>
	                                <th>Generated PDF</th>
	                                <th>Submitted PDF</th>
	                            </tr>
	                        </thead>
	                        <tbody>\n");
    	foreach($overall as $type => $stats){
    		$wgOut->addHTML("<tr><td>{$type}</td>");
    		foreach($stats as $cell => $number){
    			$wgOut->addHTML("<td>$number</td>");
    		}

    		$wgOut->addHTML("</tr>");
    	}
    	$wgOut->addHTML("</tbody></table>");
    }

    
    
    static function niTable($nis, $type='CNI'){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        
        $html = "";

        $me = Person::newFromId($wgUser->getId());
        $allPeople = $nis; //array_merge(Person::getAllPeople(CNI), Person::getAllPeople(PNI));
        $i = 0;
        $names = array();
        foreach($allPeople as $person){
            if($person->getName() != $me->getName()){
                $names[] = $person->getName();
            }
        }
        $names = implode("','", $names);
        
        $js =<<<EOF
        <script type="text/javascript">
        var sort = "first";
        var allPeople = new Array('{$names}');

        function filterResultsCNI(value){
            if(typeof value != 'undefined'){
                value = $.trim(value);
                value = value.replace(/\s+/g, '|');
                //console.log(value);
                $.each($("table#CNI_conflicts tr[name=search]"), function(index, val){
                    if($(val).attr("class").toLowerCase().regexIndexOf(value.toLowerCase()) != -1){
                        $(val).show();
                    }
                    else{
                        $(val).hide();
                    }
                });
            }
        }

        function filterResultsPNI(value){
            if(typeof value != 'undefined'){
                value = $.trim(value);
                value = value.replace(/\s+/g, '|');
                //console.log(value);
                $.each($("table#PNI_conflicts tr[name=search]"), function(index, val){
                    if($(val).attr("class").toLowerCase().regexIndexOf(value.toLowerCase()) != -1){
                        $(val).show();
                    }
                    else{
                        $(val).hide();
                    }
                });
            }
        }
            
        </script>
EOF;
        
        $wgOut->addScript($js);    
        //$html .= $js;    

        $html .=<<<EOF

        <div id='div_new_connections'>
        
        <strong>Search:</strong> <input title='You can search by Name, Organization or Projects' style='width:73%;' id='search_{$type}' type='text' onKeyUp='filterResults{$type}(this.value);' />
        <div style='padding:2px;'></div>
        <table width='850' id='{$type}_conflicts' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
        <thead>
        <tr bgcolor='#F2F2F2'>
        <th width='40%' name="search_lastname_header">Name</th>
        <th width='15%' name="search_firstname_header" title=''>Work With</th>
        <th width='15%' name="search_firstname_header" title=''>Same Organization</th>
        <th width='15%' name="search_projects_header" title=''>Same Projects</th>
        <th width='15%' name="search_university_header" title=''>Co-authorship</th>
        <th width='15%' name="search_university_header" title=''>Co-supervision</th>
        <th width='15%' class='sorter-false' title=''>
            Conflict? <!--input type='checkbox' name="search_selectall_checkbox" onchange="toggleChecked(this.checked, '#new_connections tbody tr:visible input.search_conn_chkbox');" /-->
            
        </th>
        </tr>
        </thead>
        <tbody>
EOF;
	
		$my_organization = $me->getUniversity();      
		$my_organization = $my_organization['university'];

		$my_projects = array();
		foreach($me->getProjects() as $project){
        	$my_projects[] = $project->getName();
        }

        //$my_papers = $me->getPapers("all", true);
        $my_papers = array();
        foreach($me->getPapers("all", true) as $paper){
        	$my_papers[] = $paper->getId();
        }

        //Works With
        $my_coworkers = array();
        foreach($me->getRelations("Works With", true) as $rel){
        	$my_coworkers[] = $rel->getUser2();
        }

        $my_hqp = array();
        foreach($me->getHQP(true) as $hqp){
        	$my_hqp[] = $hqp->getId();
        }

        foreach($allPeople as $person){
            if($person->getName() == $me->getName()){
                continue;
            }

            //Check if they have submitted their report
            $sto = new ReportStorage($person);
            $rep_year = REPORTING_YEAR;
            $check = $sto->list_reports($person->getId(), SUBM, 1000, 0, 0);
            $largestDate = "{$rep_year}-09-01 00:00:00";
            //var_dump($check);

            $latest_pdf = null;
            foreach($check as $c){
               // var_dump($c);
               // echo "<br><br>";
                $tok = $c['token'];
                //$sto->select_report($tok);
                $year = $c['year'];
                $tst = $c['timestamp']; //$sto->metadata('timestamp');

                if($year == $rep_year && strcmp($tst, $largestDate) > 0){
                    $largestDate = $tst;
                    $latest_pdf = $c;   
                }
            }
            //exit;
            if(is_null($latest_pdf) || !$latest_pdf['submitted']){
                continue;
            }

            

            //Name
            $person_name = explode('.', $person->getName()); 
            $fname = $person_name[0];
            $lname = implode(' ', array_slice($person_name, 1));

            //Work With 
            $works_with = "No";            
            $co_workers = array();
        	foreach($person->getRelations("Works With", true) as $rel){
        		$co_workers[] = $rel->getUser2();
        	}
            if($person->relatedTo($me, 'Works With') || $me->relatedTo($person, 'Works With') || in_array($me->getId(), $co_workers)){
            	$works_with = "Yes";
            }

            
            //Organization
            $position = $person->getUniversity();
            $position = $position['university'];
            $same_organization = "No";
            if(!empty($position) && $my_organization == $position){
            	$same_organization = "Yes";
            }

            //Projects
            $projects = $person->getProjects();
            $proj_names = array();
            $same_projects = "No";
            foreach($projects as $project){
            	if(in_array($project->getName(), $my_projects)){
            		$same_projects = "Yes";
            	}
                $proj_names[] = $project->getName();
            }
            $proj_names = implode(' ', $proj_names);
           
      		//Papers
            $papers = $person->getPapers("all", true);
            $co_authorship = "No";
	        foreach($papers as $paper){
	        	if(in_array($paper->getId(), $my_papers)){
	        		$co_authorship = "Yes";
	        		break;
	        	}
	        }
	        //HQP
	        $co_supervision = "No";
	        foreach($person->getHQP(true) as $hqp){
	        	if(in_array($hqp->getId(), $my_hqp)){
	        		$co_supervision = "Yes";
	        		break;
	        	}
	        }
            
            $bgcolor = "#FFFFFF";
            $checked = "";
          	if($works_with == "Yes" || $same_organization == "Yes" || $same_projects == "Yes" || $co_authorship == "Yes" || $co_supervision == "Yes"){
          		$bgcolor = "#DD3333";
          		$checked = "checked='checked' disabled='disabled'";
          	}
            
            $row_id = $person->getName();
            $html .= <<<EOF
            <tr style='background-color:{$bgcolor};' name='search' id='{$row_id}' class='{$row_id} {$proj_names} {$position}'>
                <td class='lname' title='{$proj_names}; {$position}'>{$lname}, {$fname}</td>
                <td class=''>{$works_with}</td>
                <td class=''>{$same_organization}</td>
                <td class=''>{$same_projects}</td>
                <td class=''>{$co_authorship}</td>
                <td class=''>{$co_supervision}</td>
                <td align='center'><input class="conflict_checkbox" type="checkbox" {$checked} /></td>
            </tr>
EOF;
        }

        $html .=<<<EOF
        </tbody>
        </table>
        <button id="confirm_new_connections">Confirm Selected</button>
        </div>
EOF;

		$wgOut->addHTML($html);
		
    }
    
    static function projectTable($nis){
    	global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        
        $html = "";

        $me = Person::newFromId($wgUser->getId());
        $my_projects = array();
		foreach($me->getProjects() as $project){
			//echo $project->getName()."<br>";
        	$my_projects[] = $project->getName();
        }


        $html .=<<<EOF

        <div id='div_projects'>
        
        
        <div style='padding:2px;'></div>
        <table id='project_conflicts' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
        <thead>
        <tr bgcolor='#F2F2F2'>
        <th width='40%' name="search_lastname_header">Project Name</th>
        <th width='15%' name="search_firstname_header" title=''>My Project</th>
        <!--th width='15%' name="search_projects_header" title='Sort by projects'>Same Projects</th>
        <th width='15%' name="search_university_header" title='Sort by university'>Co-authorship</th-->
        <th width='15%' class='sorter-false' title=''>
            Conflict? <!--input type='checkbox' name="search_selectall_checkbox" onchange="toggleChecked(this.checked, '#new_connections tbody tr:visible input.search_conn_chkbox');" /-->
            
        </th>
        </tr>
        </thead>
        <tbody>
EOF;

        $allProjects = Project::getAllProjects();
        foreach($allProjects as $project){
        	$project_name = $project->getName();
            $same_projects = "No";
            if(in_array($project_name, $my_projects)){
                $same_projects = "Yes";
            }   

			$bgcolor = ($same_projects == "Yes")? "#DD3333" : "#FFFFFF";
            $row_id = $project_name;
            $html .= <<<EOF
            <tr style='background-color:{$bgcolor};' name='search' id='{$row_id}' class=''>
                <td class=''>{$project_name}</td>
                <td class=''>{$same_projects}</td>
                <!--td class=''></td>
                <td class=''></td-->
                <td><input class="conflict_checkbox" type="checkbox" /></td>
            </tr>
EOF;

        }

        $html .=<<<EOF
        </tbody>
        </table>
        <button id="confirm_new_connections">Confirm Selected</button>
        </div>
EOF;

		$wgOut->addHTML($html);
    }
}

?>
