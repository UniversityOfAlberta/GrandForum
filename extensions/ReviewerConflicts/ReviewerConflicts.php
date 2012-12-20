<?php
$dir = dirname(__FILE__) . '/';

$wgHooks['UnknownAction'][] = 'getack';

$wgSpecialPages['ReviewerConflicts'] = 'ReviewerConflicts';
$wgExtensionMessagesFiles['ReviewerConflicts'] = $dir . 'ReviewerConflicts.i18n.php';
$wgSpecialPageGroups['ReviewerConflicts'] = 'grand-tools';

$wgHooks['SkinTemplateContentActions'][] = 'ReviewerConflicts::showTabs';

function runReviewerConflicts($par) {
	ReviewerConflicts::run($par);
}


class ReviewerConflicts extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('ReviewerConflicts');
		SpecialPage::SpecialPage("ReviewerConflicts", CNI.'+', true, 'runReviewerConflicts');
	}


    static function createTab(){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $person = Person::newFromId($wgUser->getId());
        $page = "Report";
        if($person->isUnassignedEvaluator()){
            $page = "ReviewerConflicts";
            $selected = "";
            if($wgTitle->getText() == "ReviewerConflicts"){
                $selected = "selected";
            }
        
            echo "<li class='top-nav-element $selected'>\n";
            echo "  <span class='top-nav-left'>&nbsp;</span>\n";
            echo "  <a id='lnk-my_report' class='top-nav-mid' href='$wgServer$wgScriptPath/index.php/Special:$page' class='{$selected}'>Evaluator</a>\n";
            echo "  <span class='top-nav-right'>&nbsp;</span>\n";
            echo "</li>";
        }
    }

    static function showTabs(&$content_actions){
        global $wgTitle, $wgUser, $wgServer, $wgScriptPath;
        if($wgTitle->getText() == "ReviewerConflicts"){
            $content_actions = array();
            $person = Person::newFromId($wgUser->getId());
            
           
            if($person->isUnassignedEvaluator()){
                @$class = ($wgTitle->getText() == "ReviewerConflicts" ) ? "selected" : false;
                
                $content_actions[] = array (
                         'class' => $class,
                         'text'  => "Reviewer Conflicts",
                         'href'  => "$wgServer$wgScriptPath/index.php/Special:ReviewerConflicts",
                        );
            }
            
        }
        return true;
    }
	
	static function run(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
	    
	    $reviewer_id = $wgUser->getId();
	    
        $overall = array();
	    $projects = array();
	    $nis = array();
	    $active = 0;

        if(isset($_GET['download_csv'])){
            $csv_type = $_GET['download_csv'];
            $filename = "/local/data/www-root/grand_forum/data/{$csv_type}_Conflicts_Rollup.csv";
            $wgOut->disable();
            ob_clean();
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Content-Description: File Transfer');
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename={$csv_type}-Reviewer_Conflicts.csv");
            header("Expires: 0");
            header("Pragma: public");
            readfile($filename);
            exit;
        }

	    if(isset($_POST['Submit']) && ($_POST['Submit'] == "Confirm CNI Conflicts" || $_POST['Submit'] == "Confirm PNI Conflicts")){
            if(isset($_POST['reviewee_id'])){
                foreach($_POST['reviewee_id'] as $reviewee_id){
                    if(isset($_POST['conflict_'.$reviewee_id]) && $_POST['conflict_'.$reviewee_id]){
                        $conflict = 1;
                    }
                    else{
                        $conflict = 0;
                    }

                    if(isset($_POST['user_conflict_'.$reviewee_id]) && $_POST['user_conflict_'.$reviewee_id]){
                        $user_conflict = 1;
                    }
                    else{
                        $user_conflict = 0;
                    }

                    $sql = "INSERT INTO grand_reviewer_conflicts(reviewer_id, reviewee_id, conflict, user_conflict) 
                            VALUES('{$reviewer_id}', '{$reviewee_id}', '$conflict', '$user_conflict' ) 
                            ON DUPLICATE KEY UPDATE conflict='{$conflict}', user_conflict='{$user_conflict}'";

                    $data = DBFunctions::execSQL($sql, true);
                }

            }
            
        }
        else if(isset($_POST['Submit']) && $_POST['Submit'] == "Confirm Projects Conflicts"){
            if(isset($_POST['project_id'])){
                foreach($_POST['project_id'] as $project_id){
                    if(isset($_POST['conflict_'.$project_id]) && $_POST['conflict_'.$project_id]){
                        $conflict = 1;
                    }
                    else{
                        $conflict = 0;
                    }

                    if(isset($_POST['user_conflict_'.$project_id]) && $_POST['user_conflict_'.$project_id]){
                        $user_conflict = 1;
                    }
                    else{
                        $user_conflict = 0;
                    }

                    $sql = "INSERT INTO grand_project_conflicts(reviewer_id, project_id, conflict, user_conflict) 
                            VALUES('{$reviewer_id}', '{$project_id}', '$conflict', '$user_conflict' ) 
                            ON DUPLICATE KEY UPDATE conflict='{$conflict}', user_conflict='{$user_conflict}'";

                    $data = DBFunctions::execSQL($sql, true);
                }

            }
        }

        if(isset($_POST['type'])){
            if($_POST['type'] == 'PNI'){
                $active = 0;
            }
            else if($_POST['type'] == 'CNI'){
                $active = 1;
            }   
            else if($_POST['type'] == 'PROJECTS'){
                $active = 2;
            }  
        }
        

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

        $me = Person::newFromId($wgUser->getId());
        if($me->isRole(MANAGER)){
            $overall['PNI'] = ReviewerConflicts::managerNiTable($pnis, 'PNI');           
            $wgOut->addHTML("</div><div id='cnis'>");
            $overall['CNI'] = ReviewerConflicts::managerNiTable($cnis, 'CNI');           
            $wgOut->addHTML("</div><div id='projects'>");
            $overall['PROJECTS'] = ReviewerConflicts::managerProjectTable($projects);
        }
        else{
            $overall['PNI'] = ReviewerConflicts::niTable($pnis, 'PNI');           
    	    $wgOut->addHTML("</div><div id='cnis'>");
            $overall['CNI'] = ReviewerConflicts::niTable($cnis, 'CNI');           
            $wgOut->addHTML("</div><div id='projects'>");
    		$overall['PROJECTS'] = ReviewerConflicts::projectTable($projects);
		}

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
                                    $('#ackTabs').tabs( 'select', $active);
                                    $('input[name=date]').datepicker();
                                    $('input[name=date]').datepicker('option', 'dateFormat', 'dd-mm-yy');
                                	$('#CNI_conflicts').tablesorter({ 
										sortList: [[0,0], [1,0], [2,0], [3,0]],
									});
                                    $('#PNI_conflicts').tablesorter({ 
                                        sortList: [[0,0], [1,0], [2,0], [3,0]],
                                    });
                                    $('#CNI_conflicts_man').tablesorter({ 
                                        sortList: [[0,0]],
                                    });
                                    $('#PNI_conflicts_man').tablesorter({ 
                                        sortList: [[0,0]],
                                    });
	    							$('#project_conflicts').tablesorter({ 
										sortList: [[0,0], [1,0]],
									});
                                    $('#project_conflicts_man').tablesorter({ 
                                        sortList: [[0,0]],
                                    });

                                    $('.conflict_found').click(function(e){
                                        e.preventDefault();
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

    
    static function managerNiTable($nis, $type='CNI'){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        
        $html = "";
        $csv = "";

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

        $current_evals = array(17,563,152,25,90,27,28,564,32,565,566,36,38,41,48,55,60,61,1263);

        $sql = "SELECT DISTINCT reviewer_id FROM grand_reviewer_conflicts";
        $data = DBFunctions::execSQL($sql);
        $total_conflict_submissions = count($data);
        $total_evaluators = count($current_evals);

        $eval_papers = array();

        $html .=<<<EOF

        <div id='div_new_connections'>
        <p style="background-color:yellow; display:inline-block;">{$total_conflict_submissions} out of {$total_evaluators} Evaluators have submitted their NI Conflict Reviews.</p><br />
        <strong>Search:</strong> <input title='You can search by Name, Organization or Projects' style='width:82%;' id='search_{$type}' type='text' onKeyUp='filterResults{$type}(this.value);' />
        <div style='padding:2px;'></div>
        <form id='submitForm' action='$wgServer$wgScriptPath/index.php/Special:ReviewerConflicts' method='post'>
        <div style="width:1000px; overflow: scroll;">
        <table id='{$type}_conflicts_man' class='wikitable' cellspacing='1' cellpadding='5' frame='box' rules='all'>
        <thead>
        <tr bgcolor='#F2F2F2'>
        <th name="search_lastname_header">Names</th>
EOF;
        
        $csv .= '"Names"';

        foreach($current_evals as $eval_id){
            $eval = Person::newFromId($eval_id);
            $eval_name = $eval->getName();
            $eval_name_prop = explode('.', $eval_name); 
            $efname = $eval_name_prop[0];
            $elname = implode(' ', array_slice($eval_name_prop, 1));
            
            $html .= "<th name='' title='' class='sorter-false'>{$elname}<br />{$efname}</th>";
            $csv .= ',"'.$eval_name.'"';

            //cache eval papers
            $eval_papers[$eval_id] = array();
            foreach($eval->getPapers("all", true) as $epaper){
                $eval_papers[$eval_id][] = $epaper->getId();
            }
        }
        $csv .= "\n";

        $html .=<<<EOF
        </tr>
        </thead>
        <tbody>
EOF;

        $sql = "SELECT * FROM grand_reviewer_conflicts";
        $data = DBFunctions::execSQL($sql);
        $conflicts = array();
        foreach($data as $row){
            $eval_id = $row['reviewer_id'];
            $rev_id = $row['reviewee_id'];
            $conflict = $row['conflict'];
            $user_conflict = $row['user_conflict'];

            $inner = array("conflict"=>$conflict, 'user_conflict'=>$user_conflict);
            $conflicts[$eval_id][$rev_id] = $inner;
        }
        
        foreach($allPeople as $person){
            $reviewee_id = $person->getId();

            //if(in_array($reviewee_id, $current_evals)){
            //    continue;
            //}

            $row_id = $person->getName();
            $person_name = explode('.', $person->getName()); 
            $fname = $person_name[0];
            $lname = implode(' ', array_slice($person_name, 1));

            //Organization
            $position = $person->getUniversity();
            $position = $position['university'];
            
            //Projects
            $projects = $person->getProjects();
            $proj_names = array();
            
            foreach($projects as $project){
                $proj_names[] = $project->getName();
            }
            $proj_names = implode(' ', $proj_names);

            $papers = $person->getPapers("all", true);
            $projects = $person->getProjects();
            $person_position = $person->getUniversity();

            $bgcolor = "#FFFFFF";
            $html .=<<<EOF
            <tr style='background-color:{$bgcolor};' name='search' id='{$row_id}' class='{$row_id} {$proj_names} {$position}'>
            <td class=''>{$lname}, {$fname}</td>
EOF;
            $csv .= '"'.$person->getName().'"';
            foreach($current_evals as $eval_id){

                $eval = Person::newFromId($eval_id);
                $eval_name = $eval->getName();
                $eval_name_prop = explode('.', $eval_name); 
                $efname = $eval_name_prop[0];
                $elname = implode(' ', array_slice($eval_name_prop, 1));
        
                //$sql = "SELECT * FROM grand_reviewer_conflicts WHERE reviewer_id = '{$eval_id}' AND reviewee_id = '{$reviewee_id}'";
                //$data = DBFunctions::execSQL($sql);
                

                $bgcolor = "#FFFFFF";
                if(isset($conflicts[$eval_id][$reviewee_id])){
                    $data = $conflicts[$eval_id][$reviewee_id];
                    $conflict = ($data['conflict'] == 1)? "Y" : "N";
                    $user_conflict = ($data['user_conflict'] == 1)? "Y" : "N";

                    if($conflict != $user_conflict){
                        $bgcolor = "yellow";
                    }
                    else if($conflict == "Y") {
                        $bgcolor = "#DD3333";
                    }
                    $html .= "<td style='background-color:{$bgcolor};' align='center' title='Evaluator: {$efname} {$elname}<br />NI: {$fname} {$lname}' name='' title=''>O=$conflict<br />U=$user_conflict</td>";    
                    $csv .= ',"O='.$conflict.'; U='.$user_conflict.'"';
                }
                else{

                    //EVAL DATA
                    $eval_organization = $eval->getUniversity();      
                    $eval_organization = $eval_organization['university'];

                    $eval_projects = array();
                    foreach($eval->getProjects() as $eproject){
                        $eval_projects[] = $eproject->getName();
                    }

                    //$eval_papers = array();
                    //foreach($eval->getPapers("all", true) as $epaper){
                    //    $eval_papers[] = $epaper->getId();
                    //}

                    //Works With
                    $eval_coworkers = array();
                    foreach($eval->getRelations("Works With", true) as $erel){
                        $eval_coworkers[] = $erel->getUser2();
                    }

                    $eval_hqp = array();
                    foreach($eval->getHQP(true) as $ehqp){
                        $eval_hqp[] = $ehqp->getId();
                    }

                    //NI DATA
                    //Work With 
                    $works_with = "No";            
                    $co_workers = array();
                    foreach($person->getRelations("Works With", true) as $rel){
                        $co_workers[] = $rel->getUser2()->getId();
                    }

                    if($person->relatedTo($eval, 'Works With') || $eval->relatedTo($person, 'Works With') || in_array($eval_id, $co_workers)){
                        $works_with = "Yes";
                    }
                    
                    //Organization
                    $position = $person_position['university'];
                    $same_organization = "No";
                    if(!empty($position) && $eval_organization == $position){
                        $same_organization = "Yes";
                    }

                    //Projects
                    $same_projects = "No";
                    foreach($projects as $project){
                        if(in_array($project->getName(), $eval_projects)){
                            $same_projects = "Yes";
                        }
                    }
                   
                    //Papers
                    $co_authorship = "No";
                    foreach($papers as $paper){
                        if(in_array($paper->getId(), $eval_papers[$eval_id])){
                            $co_authorship = "Yes";
                            break;
                        }
                    }

                    //HQP
                    $co_supervision = "No";
                    foreach($person->getHQP(true) as $hqp){
                        if(in_array($hqp->getId(), $eval_hqp)){
                            $co_supervision = "Yes";
                            break;
                        }
                    }

                    if($works_with == "Yes" || $same_organization == "Yes" || $same_projects == "Yes" || $co_authorship == "Yes" || $co_supervision == "Yes"){
                        $conflict = "Y";
                        $bgcolor = "#DD3333";
                    }
                    else{
                        $conflict = "N";
                    }
                    $user_conflict = "N/A";

                    $html .= "<td style='background-color:{$bgcolor};' align='center' title='Evaluator: {$efname} {$elname}<br />NI: {$fname} {$lname}' width='10%' name='' title=''>O=$conflict<br />U=$user_conflict</td>";
                
                    $csv .= ',"O='.$conflict.'; U='.$user_conflict.'"';
                }
               
            }
            $csv .= "\n";
        }

        $myFile = $type."_Conflicts_Rollup.csv";
        $fh = fopen('/local/data/www-root/grand_forum/data/'.$myFile, 'w');
        //$fh = fopen('/Library/WebServer/Documents/giga_forum/data/'.$myFile, 'w');
        fwrite($fh, $csv);
        fclose($fh);
        
        $html .=<<<EOF
        </tbody>
        </table>
        </div>
        <input type="hidden" name="type" value="{$type}" />
        <a href="/index.php/Special:ReviewerConflicts?download_csv={$type}" target="_blank">[Download as CSV]</a>
        </form>
        </div>
EOF;

        $wgOut->addHTML($html);
        
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
        
        <strong>Search:</strong> <input title='You can search by Name, Organization or Projects' style='width:82%;' id='search_{$type}' type='text' onKeyUp='filterResults{$type}(this.value);' />
        <div style='padding:2px;'></div>
        <form id='submitForm' action='$wgServer$wgScriptPath/index.php/Special:ReviewerConflicts' method='post'>
        <table width='950' id='{$type}_conflicts' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
        <thead>
        <tr bgcolor='#F2F2F2'>
        <th width='25%' name="search_lastname_header">Name</th>
        <th width='10%' name="search_firstname_header" title=''>Work With</th>
        <th width='10%' name="search_firstname_header" title=''>Same Organization</th>
        <th width='10%' name="search_projects_header" title=''>Same Projects</th>
        <th width='10%' name="search_university_header" title=''>Co-authorship</th>
        <th width='10%' name="search_university_header" title=''>Co-supervision</th>
        <th width='10%' class='sorter-false' title=''>Conflict Found</th>
        <th width='15%' class='sorter-false' title=''>Do you think there is a conflict?</th>
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


        //Get saved conflicts data if any
        $reviewer_id = $me->getId();
        $sql = "SELECT * FROM grand_reviewer_conflicts WHERE reviewer_id = '{$reviewer_id}'";
        $data = DBFunctions::execSQL($sql);    

        $conflicts = array();
        foreach($data as $row){
            $conflicts["'".$row['reviewee_id']."'"] = $row['user_conflict'];
        }

        //print_r($conflicts);

        foreach($allPeople as $person){
            if($person->getName() == $me->getName()){
                continue;
            }

            //Check if they have submitted their report
            /*
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
            */
            

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


            $row_id = $person->getName();
            $reviewee_id = $person->getId();

            $bgcolor = "#FFFFFF";
            $conflict_checked = 0;
            $user_conflict_checked = "";
            $conflict_found = "No";
          	if($works_with == "Yes" || $same_organization == "Yes" || $same_projects == "Yes" || $co_authorship == "Yes" || $co_supervision == "Yes"){
          		$bgcolor = "#DD3333";
          		$conflict_checked = 1;
                $conflict_found = "Yes";
                $user_conflict_checked = "checked='checked'";
          	}

            
            if(isset($conflicts["'".$reviewee_id."'"])) {
                //if($conflicts["'".$reviewee_id."'"]){
                //    $conflict_checked = "checked='checked'";
                //}
                
                $user_conflict_checked = ($conflicts["'".$reviewee_id."'"])? "checked='checked'" : "";
            
                
            }


            $html .= <<<EOF
            <tr style='background-color:{$bgcolor};' name='search' id='{$row_id}' class='{$row_id} {$proj_names} {$position}'>
                <td class='lname' title='{$proj_names}; {$position}'>{$lname}, {$fname}</td>
                <td class=''>{$works_with}</td>
                <td class=''>{$same_organization}</td>
                <td class=''>{$same_projects}</td>
                <td class=''>{$co_authorship}</td>
                <td class=''>{$co_supervision}</td>
                <td>{$conflict_found}<input type="hidden" name="conflict_{$reviewee_id}" value="{$conflict_checked}" /></td> 
                <td align='center'>
                <input type="hidden" name="reviewee_id[]" value="{$reviewee_id}" />
                <input type="checkbox" name="user_conflict_{$reviewee_id}" {$user_conflict_checked} />
                </td>
            </tr>
EOF;
        }

        $html .=<<<EOF
        </tbody>
        </table>
        <input type="hidden" name="type" value="{$type}" />
        <input type="submit" name="Submit" value="Confirm {$type} Conflicts" />
        </form>
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
        <form id='submitForm' action='$wgServer$wgScriptPath/index.php/Special:ReviewerConflicts' method='post'>
        <table id='project_conflicts' class='wikitable' cellspacing='1' cellpadding='2' frame='box' rules='all'>
        <thead>
        <tr bgcolor='#F2F2F2'>
        <th width='35%' name="search_lastname_header">Project Name</th>
        <th width='10%' name="search_firstname_header" title=''>Conflict Found</th>
        <th width='25%' class='sorter-false' title=''>
            Do you think there is a conflict? 
        </th>
        </tr>
        </thead>
        <tbody>
EOF;

        //Get saved conflicts data if any
        $reviewer_id = $me->getId();
        $sql = "SELECT * FROM grand_project_conflicts WHERE reviewer_id = '{$reviewer_id}'";
        $data = DBFunctions::execSQL($sql);    

        $conflicts = array();
        foreach($data as $row){
            $conflicts["'".$row['project_id']."'"] = $row['user_conflict'];
        }

        $allProjects = Project::getAllProjects();
        foreach($allProjects as $project){
        	$project_name = $project->getName();
            $project_id = $project->getId();
            $same_projects = "No";
            if(in_array($project_name, $my_projects)){
                $same_projects = "Yes";
            }   

            $bgcolor = "#FFFFFF";
            $conflict_checked = 0;
            $user_conflict_checked = "";
            if($same_projects == "Yes"){
                $bgcolor = "#DD3333";
                $conflict_checked = 1;
                $user_conflict_checked = "checked='checked'";
            }
			
            if(isset($conflicts["'".$project_id."'"])) {
                $user_conflict_checked = ($conflicts["'".$project_id."'"])? "checked='checked'" : "";
            }

            $row_id = $project_name;
            $html .= <<<EOF
            <tr style='background-color:{$bgcolor};' name='search' id='{$row_id}' class=''>
                <td>{$project_name}</td>
                <td>
                    {$same_projects}
                    <input type="hidden" name="conflict_{$project_id}" value="{$conflict_checked}" /></td>
                <td align='center'>
                <input type="hidden" name="project_id[]" value="{$project_id}" />
                <input type="checkbox" name="user_conflict_{$project_id}" {$user_conflict_checked} />
                </td>
            </tr>
EOF;

        }

        $html .=<<<EOF
        </tbody>
        </table>
        <input type="hidden" name="type" value="PROJECTS" />
        <input type="submit" name="Submit" value="Confirm Projects Conflicts" />
        </form>
        </div>
EOF;

		$wgOut->addHTML($html);
    }

    static function managerProjectTable($nis){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        
        $html = "";
        $csv = "";
        //$me = Person::newFromId($wgUser->getId());
        
        $current_evals = array(17,563,152,25,90,27,28,564,32,565,566,36,38,41,48,55,60,61,1263);
        $sql = "SELECT DISTINCT reviewer_id FROM grand_project_conflicts";
        $data = DBFunctions::execSQL($sql);
        $total_conflict_submissions = count($data);
        $total_evaluators = count($current_evals);

        $html .=<<<EOF

        <div id='div_projects'>
        
        <p style="background-color:yellow; display:inline-block;">{$total_conflict_submissions} out of {$total_evaluators} Evaluators have submitted their Project Conflict Reviews.</p><br />

        <div style='padding:2px;'></div>
        <form id='submitForm' action='$wgServer$wgScriptPath/index.php/Special:ReviewerConflicts' method='post'>
        <div style="width:1000px; overflow: scroll;">
        <table id='project_conflicts_man' class='wikitable' cellspacing='1' cellpadding='5' frame='box' rules='all'>
        <thead>
        <tr bgcolor='#F2F2F2'>
        <th name="search_lastname_header">Project Name</th>
EOF;

        $csv .= '"Names"';        
        foreach($current_evals as $eval_id){
            $eval = Person::newFromId($eval_id);
            $eval_name = $eval->getName();
            $eval_name_prop = explode('.', $eval_name); 
            $efname = $eval_name_prop[0];
            $elname = implode(' ', array_slice($eval_name_prop, 1));
            
            $html .= "<th name='' title='' class='sorter-false'>{$elname}<br />{$efname}</th>";
            $csv .= ',"'.$elname.','.$efname.'"';
        }
        $csv .= "\n";

        $html .=<<<EOF
        </tr>
        </thead>
        <tbody>
EOF;


        $allProjects = Project::getAllProjects();
        foreach($allProjects as $project){
            $project_name = $project->getName();
            $project_id = $project->getId();            
           
            $row_id = $project_name;
            $html .= <<<EOF
            <tr name='search' id='{$row_id}' class=''>
                <td>{$project_name}</td>
EOF;
            $csv .= '"'.$project_name.'"';

            foreach($current_evals as $eval_id){
                $eval = Person::newFromId($eval_id);
                $eval_name = $eval->getName();
                $eval_name_prop = explode('.', $eval_name); 
                $efname = $eval_name_prop[0];
                $elname = implode(' ', array_slice($eval_name_prop, 1));

                 //Get saved conflicts data if any
                $sql = "SELECT * FROM grand_project_conflicts WHERE reviewer_id = '{$eval_id}' AND project_id = '{$project_id}'";
                $data = DBFunctions::execSQL($sql);
                $bgcolor = "#FFFFFF";    
                if(count($data) > 0){
                    $conflict = ($data[0]['conflict'] == 1)? "Y" : "N";
                    $user_conflict = ($data[0]['user_conflict'] == 1)? "Y" : "N";

                    if($conflict != $user_conflict){
                        $bgcolor = "yellow";
                    }
                    else if($conflict == "Y") {
                        $bgcolor = "#DD3333";
                    }
                   
                    $html .= "<td style='background-color:{$bgcolor};' align='center' title='Evaluator: {$efname} {$elname}<br />Project: {$project_name}' name='' title=''>O=$conflict<br />U=$user_conflict</td>";    
                    $csv .= ',"O='.$conflict.'; U='.$user_conflict.'"';
                }
                else{
                    $eval_projects = array();
                    foreach($eval->getProjects() as $eproject){
                        $eval_projects[] = $eproject->getName();
                    }
                    
                    $bgcolor = "#FFFFFF";
                    if(in_array($project_name, $eval_projects)){
                        $conflict = "Y";
                        $bgcolor = "#DD3333";
                    }
                    else{
                        $conflict = "N";
                    }   
                    $user_conflict = "N/A";

                    $html .= "<td style='background-color:{$bgcolor};' align='center' title='Evaluator: {$efname} {$elname}<br />Project: {$project_name}' name='' title=''>O=$conflict<br />U=$user_conflict</td>";
                
                    $csv .= ',"O='.$conflict.'; U='.$user_conflict.'"';
                }

            }
            $csv .= "\n";
                
            $html .= "</tr>";

        }

        $myFile = "Project_Conflicts_Rollup.csv";
        $fh = fopen('/local/data/www-root/grand_forum/data/'.$myFile, 'w');
        //$fh = fopen('/Library/WebServer/Documents/giga_forum/data/'.$myFile, 'w');
        fwrite($fh, $csv);
        fclose($fh);

        $html .=<<<EOF
        </tbody>
        </table>
        <input type="hidden" name="type" value="PROJECTS" />
        <a href="/index.php/Special:ReviewerConflicts?download_csv=Project" target="_blank">[Download as CSV]</a>
        </form>
        </div>
EOF;

        $wgOut->addHTML($html);
    }
}

?>
