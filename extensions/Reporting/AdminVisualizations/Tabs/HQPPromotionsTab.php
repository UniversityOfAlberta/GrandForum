<?php

UnknownAction::createAction('HQPPromotionsTab::getHQPPromotionsData');

class HQPPromotionsTab extends AbstractTab {
	
	function HQPPromotionsTab(){
        parent::AbstractTab("HQP Promotions");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    $people = Person::getAllPeople();
	    $this->html .= "The following are the timelines of HQP who have at some point been promoted to an NI.";
	    foreach($people as $person){
	        $hqpFound = false;
	        $niFound = false;
	        $roles = $person->getRoles(true);
	        foreach($roles as $role){
	            if($role->getRole() == HQP){
	                $hqpFound = true;
	            }
	            if($role->getRole() == NI && $hqpFound){
	                $niFound = true;
	            }
	        }
	        if($niFound){
	            $this->html .= "<h2>{$person->getNameForForms()}</h2>";
	            $timeline = new VisTimeline("{$wgServer}{$wgScriptPath}/index.php?action=getHQPPromotionsTimelineData&person={$person->getId()}");
	            $timeline->height = 600;
	            $this->html .= $timeline->show();
	            $this->html .= "<script type='text/javascript'>
                $('#adminVis').bind('tabsselect', function(event, ui) {
                    if(ui.panel.id == 'hqp-promotions'){
                        onLoad{$timeline->index}();
                    }
                });
                </script><br />";
	        }
	    }
	}
	
	static function getHQPPromotionsData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    $year = (isset($_GET['date'])) ? $_GET['date'] : REPORTING_YEAR;
	    if($action == "getHQPPromotionsTimelineData" && $me->isRoleAtLeast(MANAGER)){
	        session_write_close();
	        $array = array();
            $person = Person::newFromId($_GET['person']);
            $today = date("Y-m-d");
            
            $array = array();
            $items = array();
            $groups = array(array('id' => 'roles',
                                  'content' => 'Roles',
                                  'className' => 'visRed'),
                            array('id' => 'locations',
                                  'content' => 'Locations',
                                  'className' => 'visPurple'),
                            array('id' => 'projects',
                                  'content' => 'Projects',
                                  'className' => 'visBlue'),
                            array('id' => 'relations',
                                  'content' => 'Relations',
                                  'className' => 'visGreen'),
                            array('id' => 'products',
                                  'content' => 'Products',
                                  'className' => 'visOrange'),
                            array('id' => 'reports',
                                  'content' => 'Report Mentions',
                                  'className' => 'visYellow'));
            foreach($person->getRoles(true) as $role){
                $start = substr($role->getStartDate(), 0, 10);
                $end = substr($role->getEndDate(), 0, 10);
                if($end == "0000-00-00"){
                    $end = $today;
                }
                if(strcmp($start, $end) > 0){
                    $start = $end;
                }
                $items[] = array('content' => $role->getRole(),
                                 'description' => array('title' => $role->getRole(),
                                                        'text' => ""),
                                 'group' => 'roles',
                                 'start' => $start,
                                 'end' => $end);
            }
            
            foreach($person->getUniversities() as $university){
                $start = substr($university['start'], 0, 10);
                $end = substr($university['end'], 0, 10);
                if($end == "0000-00-00"){
                    $end = $today;
                }
                if($start == "0000-00-00"){
                    $startY = substr($person->getRegistration(), 0, 4);
                    $startM = substr($person->getRegistration(), 4, 2);
                    $startD = substr($person->getRegistration(), 6, 2);
                    $start = "$startY-$startM-$startD";
                }
                if(strcmp($start, $end) > 0){
                    $start = $end;
                }
                $items[] = array('content' => $university['university'],
                                 'description' => array('title' => $university['university'],
                                                        'text' => "<table>
                                                                    <tr>
                                                                        <td><b>Title:</b></td><td>{$university['position']}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><b>Department:</b></td><td>{$university['department']}</td>
                                                                    </tr>
                                                                    </table>"),
                                 'group' => 'locations',
                                 'start' => $start,
                                 'end' => $end);
            }
       
            foreach($person->getProjects(true) as $project){
                $start = substr($project->getJoinDate($person), 0, 10);
                $end = substr($project->getEndDate($person), 0, 10);
                if($end == "0000-00-00"){
                    $end = $today;
                }
                if(strcmp($start, $end) > 0){
                    $start = $end;
                }
                $content = "<a href='{$project->getUrl()}' target='_blank'>View Project's Page</a>";
                $items[] = array('content' => $project->getName(),
                                 'description' => array('title' => $project->getName(),
                                                        'text' => $content),
                                 'group' => 'projects',
                                 'start' => $start,
                                 'end' => $end);
            }
           
            if(count($person->getRelations('all', true)) > 0){
                foreach($person->getRelations('all', true) as $type){
                    foreach($type as $relation){
                        $start = substr($relation->getStartDate(), 0, 10);
                        $end = substr($relation->getEndDate(), 0, 10);
                        if($end == "0000-00-00"){
                            $end = $today;
                        }
                        if(strcmp($start, $end) > 0){
                            $start = $end;
                        }
                        $content = "<a href='{$relation->getUser1()->getUrl()}' target='_blank'>{$relation->getUser1()->getNameForForms()}</a> {$relation->getType()} <a href='{$relation->getUser2()->getUrl()}' target='_blank'>{$relation->getUser2()->getNameForForms()}</a>";
                        $items[] = array('content' => $relation->getUser2()->getNameForForms(),
                                         'description' => array('title' => $relation->getUser2()->getNameForForms(),
                                                                'text' => "$content"),
                                         'group' => 'relations',
                                         'start' => $start,
                                         'end' => $end);
                    }
                }
            }
            
            foreach($person->getPapers('all') as $paper){
                $start = $paper->getDate();
                $content = "<a href='{$paper->getUrl()}' target='_blank'>View Product's Page</a>";
                $items[] = array('content' => str_replace("&#39;", "'", $paper->getTitle()),
                                 'description' => array('title' => str_replace("&#39;", "'", $paper->getTitle()),
                                                        'text' => $content),
                                 'group' => 'products',
                                 'start' => $start,
                                 'type' => 'point');
            }
            
            $sql = "SELECT * FROM `grand_report_blobs` 
                    WHERE (`data` LIKE '%{$person->getNameForForms()}%' 
                        OR `data` LIKE '%{$person->getReversedName()}%'
                        OR `data` LIKE '%".str_replace(".", " ", $person->getName())."%')
                    AND `user_id` != '{$person->getId()}'
                    AND `user_id` != '0'
                    AND `blob_type` != '16385'";
            $data = DBFunctions::execSQL($sql);
            $reportsAlreadyDone = array();
            foreach($data as $row){
                $year = $row['year'];
                $start = $year."-12-31";
                $user = Person::newFromId($row['user_id']);
                if(!isset($reportsAlreadyDone[$year."_".$user->getId()]) && $user->getId() != $person->getId()){
                    $items[] = array('content' => $user->getNameForForms(),
                                     'description' => array('title' => $user->getNameForForms(),
                                                            'text' => ''),
                                     'group' => 'reports',
                                     'start' => $start,
                                     'type' => 'point');
                }
                $reportsAlreadyDone[$year."_".$user->getId()] = true;
            }
            $array['items'] = $items;
            $array['groups'] = $groups;
            header("Content-Type: application/json");
            echo json_encode($array);
            exit;
        }
        return true;
	}
}
?>
