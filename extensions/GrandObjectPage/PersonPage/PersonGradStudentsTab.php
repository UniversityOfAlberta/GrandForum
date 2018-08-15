<?php

class PersonGradStudentsTab extends AbstractTab {

    var $person;
    var $visibility;
    var $startRange;
    var $endRange;

    function PersonGradStudentsTab($person, $visibility, $startRange="0000-00-00", $endRange=CYCLE_END){
        parent::AbstractTab("HQP");
        $this->person = $person;
        $this->visibility = $visibility;
        $this->startRange = $startRange;
        $this->endRange = $endRange;
        $this->tooltip = "Contains information of the HQP that the faculty member has supervised between the specified start and end dates. Examination-Committee memberships are also included in a separate table.";
    }

    function generateBody(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $config;
        if(!$wgUser->isLoggedIn()){
            return "";
        }
        $me = Person::newFromWgUser();
        $wgOut->addScript(
                "<script type='text/javascript'>
                $(document).ready(function(){
                    $('.supervisorAccordion').accordion({autoHeight: false, collapsible: true, active:false});
                });
                </script>"
        );
        
        $this->html .= "<div id='{$this->id}'>
                        <table>
                            <tr>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th></th>
                                <th></th>
                            </tr>
                            <tr>
                                <td><input type='datepicker' name='startRange' value='{$this->startRange}' size='10' /></td>
                                <td><input type='datepicker' name='endRange' value='{$this->endRange}' size='10' /></td>
                                <td><input type='button' value='Update' /></td>
                                <td id='manage{$this->id}cell'></td>
                            </tr>
                        </table>
                        <script type='text/javascript'>
                            $('div#{$this->id} input[type=datepicker]').datepicker({
                                dateFormat: 'yy-mm-dd',
                                changeMonth: true,
                                changeYear: true,
                                yearRange: '1900:".(date('Y')+3)."'
                            });
                            $('div#{$this->id} input[type=button]').click(function(){
                                var startRange = $('div#{$this->id} input[name=startRange]').val();
                                var endRange = $('div#{$this->id} input[name=endRange]').val();
                                document.location = '{$this->person->getUrl()}?tab={$this->id}&startRange=' + startRange + '&endRange=' + endRange;
                            });
                            $(document).ready(function(){
                                $('#manage{$this->id}').clone().appendTo('#manage{$this->id}cell');
                            });
                        </script>
                        </div>";
        $this->html .= "
        <div class='supervisorAccordion'>
             <h3><a href='#'>Supervision</a></h3>
             <div>
                {$this->showSupervisorRelations($this->person, $this->visibility)}
             </div>
        </div>
                <div class='supervisorAccordion'>
             <h3><a href='#'>Student Committee Responsibilities</a></h3>
             <div>
                {$this->showCommiteeRelations($this->person, $this->visibility)}   
             </div>
        </div>";
        if($me->isAllowedToEdit($this->person)){
            $this->html .= "<br /><a id='manage{$this->id}' href='$wgServer$wgScriptPath/index.php/Special:ManagePeople' class='button'>Manage HQP</a>";
        }
        return $this->html;
    }
    
    function supervisesHTML($hqpTypes=array(), $startDate=null, $endDate=null){
        $html = "<table class='wikitable relations_table' width='100%' cellspacing='1' cellpadding='2' rules='all' frame='box'>
                    <thead><tr>
                            <th width='25%'>Name</th>
                            <th width='25%'>Position</th>
                            <th width='15%' style='white-space: nowrap;'>Start Date</th>
                            <th width='15%' style='white-space: nowrap;'>End Date</th>
                            <th width='15%' style='white-space: nowrap;'>Status</th>
                            <!--th style='white-space: nowrap;'>Research Area</th-->
                            <th width='20%'>Role</th>
                        </tr>
                    </thead><tbody>";
        if($startDate == null || $endDate == null){
            $relations = $this->person->getRelationsAll();
        }
        else{
            $relations = $this->person->getRelationsDuring('all', $startDate, $endDate);
        }
        $students = array();
        $hqpsDone = array();
        $rows = array();
        foreach($relations as $r){
            $hqp = $r->getUser2();
            $role = $r->getType();
            
            if($role == SUPERVISES){
                $role = "Supervisor";
            }
            else if($role == CO_SUPERVISES){
                $role = "Co-Supervisor";
            }
            else{
                continue;
            }
            
            if($r->getEndDate() != "0000-00-00"){
                // Normal Date range
                $universities = $hqp->getUniversitiesDuring($r->getStartDate(), $r->getEndDate());
            }
            else{
                // Person is still continuing
                $universities = $hqp->getUniversitiesDuring($r->getStartDate(), "2100-00-00");
            }
            if(count($universities) == 0){
                // Nothing was found, just get everything
                $universities = $hqp->getUniversitiesDuring("0000-00-00", "2100-00-00");
            }
            if(count($universities) == 0){
                // Still Nothing was found, so skip this person
                continue;
            }
            
            $found = false;
            $merged = array();
            foreach(Person::$studentPositions as $array){
                $merged = array_merge($merged, $array);
            }
            
            foreach($universities as $university){
                if((@in_array(strtolower($university['position']), $hqpTypes) || ($hqpTypes == "other" && !in_array(strtolower($university['position']), $merged))) && 
                   !isset($hqpsDone[$hqp->getId().$university['id']]) &&
                   !($university['start'] < $startDate && $university['end'] < $startDate && $university['end'] != "0000-00-00 00:00:00")){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                continue;
            }
            
            $minRelation = $r;
            $minInterval = 1000000;
            foreach($relations as $rel){
                // Find the best matching relation
                // Probably slow
                if($rel->getUser2() == $r->getUser2() && $rel->getType() == $r->getType()){
                    $start1 = new DateTime($university['start']);
                    $start2 = new DateTime($rel->getStartDate());
                    $end1   = new DateTime($university['end']);
                    $end2   = new DateTime($rel->getEndDate());
                    $startInterval = intval($start1->diff($start2)->format('%a')); // Difference in days
                    $endInterval = intval($end1->diff($end2)->format('%a')); // Difference in days
                    $minInterval = min($minInterval, $startInterval);
                    if($minInterval == $startInterval){
                        $minRelation = $rel;
                    }
                }
            }
            $r = $minRelation;
            $startDate1 = substr($r->getStartDate(), 0, 10);
            $endDate1 = substr($r->getEndDate(), 0, 10);
            $status = $r->getStatus();
            
            $uni = $university['university'];
            $research_area = $university['research_area'];
            $position = $university['position'];
            
            if(!@in_array(strtolower($position), $hqpTypes) && $hqpTypes != "other"){
                continue;
            }
            
            if(isset($hqpsDone[$hqp->getId().$university['id']])){
                //continue;
            }
            
            if($endDate1 == "0000-00-00" || (substr($university['end'], 0, 10) != "0000-00-00" && substr($university['end'], 0, 10) < $endDate1)){
                $endDate1 = substr($university['end'], 0, 10);
            }
            $end_date = ($endDate1 == '0000-00-00')? "Current" : $endDate1;
            $hqp_name = $hqp->getNameForForms();
            
            $repeat_check = false;
            $check = array('name'=>$hqp_name, 'role'=>$role);
            //TODO: Might want to remove duplicates, but probably not

            /*$awards = $hqp->getPapersAuthored('Award', $startDate1, $endDate1);
            $awardCitations = array();
            foreach($awards as $award){
                $awardCitations[] = "<span style='margin-left:2em;><a href='{$award->getUrl()}'>{$award->getTitle()}</a></span>";
            }
            $rowspan = 1;
            if(count($awardCitations) > 0){
                $rowspan = 2;
            }
            if(isset($rows[$hqp->getId()])){
                $rows[$hqp->getId()] = preg_replace_callback("/rowspan='([0-9]*)'/", function($matches) use ($rowspan){
                    return str_replace($matches[1], $matches[1] + $rowspan, $matches[0]);
                }, $rows[$hqp->getId()]);
            }
            else{
                $rows[$hqp->getId()][$end_date.$startDate1.$position] = "
                    <td rowspan='$rowspan' style='white-space: nowrap;'><a href='{$hqp->getUrl()}'>{$hqp->getReversedName()}</a></td>";
            }*/
            $rows[$hqp->getId()][$end_date.$startDate1.$position] = "
                <td style='white-space: nowrap;'>$position</td>
                <td style='white-space: nowrap;'>$startDate1</td>
                <td style='white-space: nowrap;'>$end_date</td>
                <td style='white-space: nowrap;'>$status</td>
                <td style='white-space: nowrap;'>$role</td>";
            /*if(count($awardCitations) > 0){
                $rows[$hqp->getId()][$end_date.$startDate1.$position."_awards"] .= "<tr><td colspan='4'><b>Awards</b><br />".implode("<br />", $awardCitations)."</td></tr>";
            }*/
            $hqpsDone[$hqp->getId().$university['id']] = true;
        }
        //ksort($rows);
        foreach($rows as $key => $row){
            ksort($row);
            $rows[$key] = array_reverse($row);
        }
        uasort($rows, function ($a, $b){
            $ak = array_keys($a);
            $bk = array_keys($b);
            return $ak[0] < $bk[0];
        });
        foreach($rows as $id => $row){
            $hqp = Person::newFromId($id);
            $rowspan = count($row);
            $i = 0;
            foreach($row as $r){
                $html .= "<tr hqp-id='{$hqp->getId()}_".md5($position)."'>";
                if($i == 0){
                    $html .= "<td rowspan='$rowspan' style='white-space: nowrap;'><a href='{$hqp->getUrl()}'>{$hqp->getReversedName()}</a></td>";
                }
                $html .= $r;
                $html .= "</tr>";
                $i++;
            }
        }
        //$html .= implode(array_reverse($rows));
        $html .= "</tbody></table>";
        return $html;
    }
    
    function committeeHTML($startDate=null, $endDate=null){
        $html = "<table id='relations_table2' class='wikitable sortable' width='100%' cellspacing='1' cellpadding='2' rules='all' frame='box'>
                    <thead><tr>
                            <th width='25%'>Name</th>
                            <th width='25%'>Position</th>
                            <th width='15%' style='white-space: nowrap;'>Start Date</th>
                            <th width='15%' style='white-space: nowrap;'>End Date</th>
                            <th width='15%' style='white-space: nowrap;'>Status</th>
                            <!--th style='white-space: nowrap;'>Research Area</th-->
                            <th width='20%'>Role</th>
                        </tr>
                    </thead><tbody>";
        if($startDate == null || $endDate == null){
            $relations = $this->person->getRelationsAll();
        }
        else{
            $relations = $this->person->getRelationsDuring('all', $startDate, $endDate);
        }
        $students = array();
        $hqpsDone = array();
        $rows = array();
        foreach($relations as $r){
            $hqp = $r->getUser2();
            $role = $r->getType();
            
            if($role == SUPERVISES || $role == CO_SUPERVISES){
                continue;
            }
            
            if($r->getEndDate() != "0000-00-00"){
                // Normal Date range
                $universities = $hqp->getUniversitiesDuring($r->getStartDate(), $r->getEndDate());
            }
            else{
                // Person is still continuing
                $universities = $hqp->getUniversitiesDuring($r->getStartDate(), "2100-00-00");
            }
            if(count($universities) == 0){
                // Nothing was found, just get everything
                $universities = $hqp->getUniversitiesDuring("0000-00-00", "2100-00-00");
            }
            if(count($universities) == 0){
                // Still Nothing was found, so skip this person
                continue;
            }
            
            $found = false;
            foreach($universities as $university){
                if(!isset($hqpsDone[$hqp->getId().$university['position'].$role]) &&
                   !($university['start'] < $startDate && $university['end'] < $startDate && $university['end'] != "0000-00-00 00:00:00")){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                continue;
            }
            
            $minRelation = $r;
            $minInterval = 1000000;
            foreach($relations as $rel){
                // Find the best matching relation
                // Probably slow
                if($rel->getUser2() == $r->getUser2() && $rel->getType() == $r->getType()){
                    $start1 = new DateTime($university['start']);
                    $start2 = new DateTime($rel->getStartDate());
                    $end1   = new DateTime($university['end']);
                    $end2   = new DateTime($rel->getEndDate());
                    $startInterval = intval($start1->diff($start2)->format('%a')); // Difference in days
                    $endInterval = intval($end1->diff($end2)->format('%a')); // Difference in days
                    $minInterval = min($minInterval, $startInterval);
                    if($minInterval == $startInterval){
                        $minRelation = $rel;
                    }
                }
            }
            
            $r = $minRelation;
            $startDate1 = substr($r->getStartDate(), 0, 10);
            $endDate1 = substr($r->getEndDate(), 0, 10);
            $status = $r->getStatus();
            
            $uni = $university['university'];
            $research_area = $university['research_area'];
            $position = $university['position'];
            
            if(isset($hqpsDone[$hqp->getId().$position.$role])){
                continue;
            }
            
            if($endDate1 == "0000-00-00" || (substr($university['end'], 0, 10) != "0000-00-00" && substr($university['end'], 0, 10) < $endDate1)){
                $endDate1 = substr($university['end'], 0, 10);
            }
            $end_date = ($endDate1 == '0000-00-00')? "Current" : $endDate1;
            $hqp_name = $hqp->getNameForForms();
            
            $repeat_check = false;
            $check = array('name'=>$hqp_name, 'role'=>$role);
            
            $phd = in_array(strtolower($position), Person::$studentPositions['phd']) ? "1" : "0";
            $msc = in_array(strtolower($position), Person::$studentPositions['msc']) ? "1" : "0";
            
            $rows[$hqp->getId()][$phd.$msc.$end_date.$startDate1.$position] = "
                <td style='white-space: nowrap;'>$position</td>
                <td style='white-space: nowrap;'>$startDate1</td>
                <td style='white-space: nowrap;'>$end_date</td>
                <td style='white-space: nowrap;'>$status</td>
                <td style='white-space: nowrap;'>$role</td>";

            $hqpsDone[$hqp->getId().$position.$role] = true;
        }
        //ksort($rows);
        foreach($rows as $key => $row){
            ksort($row);
            $rows[$key] = array_reverse($row);
        }
        uasort($rows, function ($a, $b){
            $ak = array_keys($a);
            $bk = array_keys($b);
            return $ak[0] < $bk[0];
        });
        
        foreach($rows as $id => $row){
            $hqp = Person::newFromId($id);
            $rowspan = count($row);
            $i = 0;
            foreach($row as $r){
                $html .= "<tr hqp-id='{$hqp->getId()}_".md5($position)."'>";
                if($i == 0){
                    $html .= "<td rowspan='$rowspan' style='white-space: nowrap;'><a href='{$hqp->getUrl()}'>{$hqp->getReversedName()}</a></td>";
                }
                $html .= $r;
                $html .= "</tr>";
                $i++;
            }
        }
        //$html .= implode(array_reverse($rows));
        $html .= "</tbody></table>";
        return $html;
    }
    
    /**
     * Displays all of the user's relations
     */
    function showSupervisorRelations($person, $visibility){
        global $wgUser, $wgOut, $wgScriptPath, $wgServer;
        $html = "";

        if($wgUser->isLoggedIn() && ($visibility['edit'] || (!$visibility['edit'] && (count($person->getRelations('public')) > 0 || count($person->getSupervisors(true)) > 0 || ($visibility['isMe'] && count($person->getRelations()) > 0))))){
            if($person->isRoleAtLeast(HQP) || ($person->isRole(INACTIVE) && $person->wasLastRoleAtLeast(HQP))){
                $html .= "<h3>Doctoral Students (Supervised or Co-supervised)</h3>";
                $html .= $this->supervisesHTML(Person::$studentPositions['phd'], $this->startRange, $this->endRange);
                
                $html .= "<h3>Master's Students (Supervised or Co-supervised)</h3>";
                $html .= $this->supervisesHTML(Person::$studentPositions['msc'], $this->startRange, $this->endRange);
                
                $html .= "<h3>Undergraduates</h3>";
                $html .= $this->supervisesHTML(Person::$studentPositions['ugrad'], $this->startRange, $this->endRange);
                
                $html .= "<h3>Post-doctoral Fellows and Research Associates (Supervised or Co-supervised)</h3>";
                $html .= $this->supervisesHTML(Person::$studentPositions['pdf'], $this->startRange, $this->endRange);
                
                $html .= "<h3>Research/Technical Assistants</h3>";
                $html .= $this->supervisesHTML(Person::$studentPositions['tech'], $this->startRange, $this->endRange);
                
                $html .= "<h3>Other</h3>";
                $html .= $this->supervisesHTML('other', $this->startRange, $this->endRange);
            }
        }
        return $html;
    }


    /**
     * Displays all of the user's relations
     */
    function showCommiteeRelations($person, $visibility){
        global $wgUser, $wgOut, $wgScriptPath, $wgServer;
        $html = "";

        if($wgUser->isLoggedIn() && ($visibility['edit'] || (!$visibility['edit'] && (count($person->getRelations('public')) > 0 || count($person->getSupervisors(true)) > 0 || ($visibility['isMe'] && count($person->getRelations()) > 0))))){
            if($person->isRoleAtLeast(HQP) || ($person->isRole(INACTIVE) && $person->wasLastRoleAtLeast(HQP))){
                if(count($person->getRelationsDuring('all', $this->startRange, $this->endRange)>0)){
                    $html .= $this->committeeHTML($this->startRange, $this->endRange);
                }
            }
        }
        return $html;
    }
}
?>
