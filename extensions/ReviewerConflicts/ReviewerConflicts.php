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
		                        <li><a href='#nis'>NIs</a></li>
		                        <li><a href='#projects'>Projects</a></li>
	                        </ul>");


		$wgOut->addHTML("<div id='nis'>");
	    $overall['NI'] = ReviewerConflicts::niTable($nis);           
	    $wgOut->addHTML("</div><div id='projects'>");
		$overall['PROJECTS'] = ReviewerConflicts::projectTable($projects);
		$wgOut->addHTML("</div></div>");
	 	
	  
	    $wgOut->addScript("<script src='../scripts/jquery.tablesorter.js' type='text/javascript' charset='utf-8';></script>
	    				   <script type='text/javascript'>
                                $(document).ready(function(){
	                                $('.indexTable').dataTable({'iDisplayLength': 100,
	                                                            'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]});
                                    $('.dataTables_filter input').css('width', 250);
                                    $('#ackTabs').tabs();
                                    
                                    $('input[name=date]').datepicker();
                                    $('input[name=date]').datepicker('option', 'dateFormat', 'dd-mm-yy');
                                	$('#ni_conflicts').tablesorter({ 
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

    
    
    static function niTable($nis){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        
        $html = "";

        $me = Person::newFromId($wgUser->getId());
        $allPeople = array_merge(Person::getAllPeople(CNI), Person::getAllPeople(PNI));
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

        var oldOptions = Array();

        //SEARCH
        var no = $("#no").detach();
        if(no.length > 0){
            oldOptions["no"] = no;
        }
        filterResults($("#search").attr("value"));
        
        $("#search").keypress(function(event) {
            if(event.keyCode == 40){        //DOWN
                $.each($("#names").children(":selected").not("#no"), function(index, value){
                    if($(value).next().length > 0){
                        $(value).attr("selected", false);
                        $(value).next().attr("selected", true);
                    }
                });
            }
            else if(event.keyCode == 38){   //UP
                $.each($("#names").children(":selected").not("#no"), function(index, value){
                    if($(value).prev().length > 0){
                        $(value).attr("selected", false);
                        $(value).prev().attr("selected", true);
                    }
                });
            }
            colorSearchRows();
        });
        
        $("#search").keyup(function(event) {
            if(event.keyCode == 13){
                // Enter key was pressed
                var page = $("select option:selected").attr("name");
                if(typeof page != "undefined"){
                    document.location = "{$wgServer}{$wgScriptPath}/index.php/" + page;
                }
            }
            if(event.keyCode != 40 && event.keyCode != 38){
                filterResults(this.value);
            }
        });
            
        </script>
EOF;
        
        //$wgOut->addScript($js);    
        //$html .= $js;    

        $html .=<<<EOF
        <h3>CNI/PNI</h3>

        <div id='div_new_connections'>
        
        <!--strong>Search:</strong> <input style='width:93%;' id='search' type='text' onKeyUp='filterResults(this.value);' /-->
        <div style='padding:2px;'></div>
        <table id='ni_conflicts' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
        <thead>
        <tr bgcolor='#F2F2F2'>
        <th width='40%' name="search_lastname_header">Name</th>
        <th width='15%' name="search_firstname_header" title='Sort by first name'>Same Organization</th>
        <th width='15%' name="search_projects_header" title='Sort by projects'>Same Projects</th>
        <th width='15%' name="search_university_header" title='Sort by university'>Co-authorship</th>
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

        foreach($allPeople as $person){
            if($person->getName() == $me->getName()){
                continue;
            }    

            //Name
            $person_name = explode('.', $person->getName()); 
            $fname = $person_name[0];
            $lname = implode(' ', array_slice($person_name, 1));

            //Organization
            $position = $person->getUniversity();
            $position = $position['university'];
            $same_organization = "No";
            if(!empty($position) && $my_organization == $position){
            	$same_organization = "Yes";
            }

            //Projects
            $projects = $person->getProjects();
            $same_projects = "No";
            foreach($projects as $project){
            	if(in_array($project->getName(), $my_projects)){
            		$same_projects = "Yes";
            		break;
            	}
            }
           
      
            $papers = $person->getPapers("all", true);
            $co_authorship = "No";
	        foreach($papers as $paper){
	        	if(in_array($paper->getId(), $my_papers)){
	        		$co_authorship = "Yes";
	        		break;
	        	}
	        }

            
          
            $bgcolor = ($same_organization == "Yes" || $same_projects == "Yes" || $co_authorship == "Yes")? "#DD3333" : "#FFFFFF";
            $row_id = $person->getName();
            $html .= <<<EOF
            <tr style='background-color:{$bgcolor};' name='search' id='{$row_id}' class=''>
                <td class='lname'>{$lname}, {$fname}</td>
                <td class=''>{$same_organization}</td>
                <td class=''>{$same_projects}</td>
                <td class=''>{$co_authorship}</td>
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
        <h3>Projects</h3>

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
