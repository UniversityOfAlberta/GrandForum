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
                            </tr>
                            <tr>
                                <td><input type='datepicker' name='startRange' value='{$this->startRange}' size='10' /></td>
                                <td><input type='datepicker' name='endRange' value='{$this->endRange}' size='10' /></td>
                                <td><input type='button' value='Update' /></td>
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
             <h3><a href='#'>Examining Commitee Membership</a></h3>
             <div>
                {$this->showCommiteeRelations($this->person, $this->visibility)}   
             </div>
        </div>";
        if($this->visibility['isMe'] || $this->visibility['isSupervisor']){
            $this->html .= "<br /><input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:ManagePeople\");' value='Manage HQP' />";
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
            if(isset($hqpsDone[$hqp->getId()])){
                continue;
            }
            //$start_date = substr($r->getStartDate(), 0, 10);
            //$end_date = substr($r->getEndDate(), 0, 10);
            
            if($endDate != "0000-00-00"){
                // Normal Date range
                $universities = $hqp->getUniversitiesDuring($endDate, $endDate);
            }
            else{
                // Person is still continuing
                $universities = $hqp->getUniversitiesDuring($endDate, "2100-00-00");
            }
            if(count($universities) == 0){
                // Nothing was found, just get everything
                $universities = $hqp->getUniversitiesDuring("0000-00-00", "2100-00-00");
            }
            if(count($universities) == 0){
                // Still Nothing was found, so skip this person
                continue;
            }
            
            foreach($universities as $university){
                if(in_array(strtolower($university['position']), $hqpTypes)){
                    break;
                }
            }
            
            $minRelation = $r;
            $minInterval = 1000000;
            foreach($relations as $rel){
                // Find the best matching relation
                // Probably slow
                
                if($rel->getUser2() == $r->getUser2()){
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
            $startDate = substr($r->getStartDate(), 0, 10);
            $endDate = substr($r->getEndDate(), 0, 10);
            
            $uni = $university['university'];
            $research_area = $university['research_area'];
            $position = $university['position'];
            if(!in_array(strtolower($position), $hqpTypes)){
                continue;
            }
            if($endDate == "0000-00-00" || (substr($university['end'], 0, 10) != "0000-00-00" && substr($university['end'], 0, 10) < $endDate)){
                $endDate = substr($university['end'], 0, 10);
            }
            $end_date = ($endDate == '0000-00-00')? "Current" : $endDate;
            $hqp_name = $hqp->getNameForForms();
            $role = $r->getType();
            $repeat_check = false;
            $check = array('name'=>$hqp_name, 'role'=>$role);
            //TODO: Might want to remove duplicates, but probably not
            
            if($role == SUPERVISES){
                $role = "Supervisor";
            }
            else if($role == CO_SUPERVISES){
                $role = "Co-Supervisor";
            }
            else{
                continue;
            }
            $names = array();
            $awards = $hqp->getPapersAuthored('Award', $startDate, $endDate);
            $awardCitations = array();
            foreach($awards as $award){
                $awardCitations[] = "<span style='margin-left:2em;><a href='{$award->getUrl()}'>{$award->getTitle()}</a></span>";
            }
            $rowspan = 1;
            if(count($awardCitations) > 0){
                $rowspan = 2;
            }
            $rows[$end_date.$startDate] = 
            "<tr>
                <td rowspan='$rowspan' style='white-space: nowrap;'><a href='{$hqp->getUrl()}'>{$hqp->getReversedName()}</a></td>
                <td style='white-space: nowrap;'>$position</td>
                <td style='white-space: nowrap;'>$startDate</td>
                <td style='white-space: nowrap;'>$end_date</td>
                <!--td>$research_area</td-->
                <!--td></td><td>".implode("; ",$names)."</td-->
                <td style='white-space: nowrap;'>$role</td>
            </tr>";
            if(count($awardCitations) > 0){
                $rows[$end_date.$startDate] .= "<tr><td colspan='4'><b>Awards</b><br />".implode("<br />", $awardCitations)."</td></tr>";
            }
            $hqpsDone[$hqp->getId()] = true;
        }
        ksort($rows);
        $html .= implode(array_reverse($rows));
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
                $html .= "<h3>Graduate Students (Supervised or Co-supervised)</h3>";
                $html .= $this->supervisesHTML(array("phd","msc","phd student", "msc student", "graduate student - master's course", "graduate student - master's thesis", "graduate student - master's", "graduate student - master&#39;s", "graduate student - doctoral"), $this->startRange, $this->endRange);
                
                $html .= "<h3>Post-doctoral Fellows and Research Associates (Supervised or Co-supervised)</h3>";
                $html .= $this->supervisesHTML(array("pdf","post-doctoral fellow"), $this->startRange, $this->endRange);
                
                $html .= "<h3>Technicians</h3>";
                $html .= $this->supervisesHTML(array("technician", "ra", "research/technical assistant", "professional end user"), $this->startRange, $this->endRange);
                
                $html .= "<h3>Undergraduates</h3>";
                $html .= $this->supervisesHTML(array("ugrad", "undergraduate", "undergraduate student"), $this->startRange, $this->endRange);
                
                //$html .= "<script type='text/javascript'>$('.relations_table').dataTable({autoWidth: false, 'iDisplayLength': 25, 'order': [[3, 'desc']]});</script>";
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
                $html .= "<table width='100%'><tr>";
                if(count($person->getRelationsDuring('all', $this->startRange, $this->endRange)>0)){
                    $html .= "<td style='width:100%;' valign='top'>";
                    $html .= "<table id='relations_table2' class='wikitable sortable' width='100%' cellspacing='1' cellpadding='2' rules='all' frame='box'>
                                <thead><tr>
                                    <th width='20%'>Name</th>
                                    <th width='20%'>Position</th>
                                    <th width='20%' style='white-space: nowrap;'>Start Date</th>
                                    <th width='20%' style='white-space: nowrap;'>End Date</th>
                                    <!--th style='white-space: nowrap;'>Research Area</th-->
                                    <!--th>Completion Milestones</th>
                                    <th>Co-Supervisors & Committees</th-->
                                    <th width='20%'>Role</th>
                                </tr></thead><tbody>";
                    $relations = $person->getRelationsDuring('all', $this->startRange, $this->endRange);
                    $students = array();
                    foreach($relations as $r){
                        $hqp = $r->getUser2();
                        if(in_array($hqp->getNameForForms(), $students)){
                            $role = $r->getType();
                            continue;
                        }
                        else{
                            $students[] = $hqp->getNameForForms();
                        }

                        $start_date = substr($r->getStartDate(), 0, 10);
                        $end_date = substr($r->getEndDate(), 0, 10);
                        $end_date = ($end_date == '0000-00-00')? "Current" : $end_date;
                        $position = $hqp->getUniversity();
                        $research_area = $position['research_area'];
                        $position = $position['position'];
                        $role = $r->getType();
                        if($role == SUPERVISES || $role == CO_SUPERVISES || $role == WORKS_WITH || $role == MENTORS){
                            continue;
                        }
                        $names = array();
                        /*$rel = array_merge($hqp->getSupervisors(), $hqp->getCommittee());
                        foreach($rel as $rels){
                            if(count($rel) == 1){
                               break;
                            }
                            $names[] = "<a href='{$rels->getUrl()}'>{$rels->getNameForForms()}</a>";
                        }*/
                        $html .=
                        "<tr>
                            <td style='white-space: nowrap;'><a href='{$hqp->getUrl()}'>{$hqp->getReversedName()}</a></td>
                            <td style='white-space: nowrap;'>$position</td>
                            <td style='white-space: nowrap;'>$start_date</td>
                            <td style='white-space: nowrap;'>$end_date</td>
                            <!--td>$research_area</td-->
                            <!--td></td><td>".implode("; ",$names)."</td-->
                            <td style='white-space: nowrap;'>$role</td>";
                        $html .= "</tr>";
                    }
                    $html .= "</tbody></table><script type='text/javascript'>$('#relations_table2').dataTable({autoWidth: false, 'iDisplayLength': 25})</script>";
                    $html .= "</td></tr></table>";
                }
            }
        }
        return $html;
    }
}
?>
