<?php

class PersonGradStudentsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonGradStudentsTab($person, $visibility){
        parent::AbstractTab("HQP");
        $this->person = $person;
        $this->visibility = $visibility;
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
        
        $me = Person::newFromWgUser();
        if(!isset($_GET['startRange']) && !isset($_GET['endRange']) && $me->getId() == $this->person->getId()){
            $startRange = ($me->getProfileStartDate() != "0000-00-00 00:00:00") ? $me->getProfileStartDate() : CYCLE_START;
            $endRange   = ($me->getProfileEndDate()   != "0000-00-00 00:00:00") ? $me->getProfileEndDate()   : CYCLE_END;
        }
        else{
            $startRange = (isset($_GET['startRange'])) ? $_GET['startRange'] : CYCLE_START;
            $endRange   = (isset($_GET['endRange']))   ? $_GET['endRange']   : CYCLE_END;
        }
        
        $this->html .= "<div id='{$this->id}'>
                        <table>
                            <tr>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th></th>
                            </tr>
                            <tr>
                                <td><input type='datepicker' name='startRange' value='{$startRange}' size='10' /></td>
                                <td><input type='datepicker' name='endRange' value='{$endRange}' size='10' /></td>
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
                            <th width='20%'>Name</th>
                            <th width='20%'>Position</th>
                            <th width='20%' style='white-space: nowrap;'>Start Date</th>
                            <th width='20%' style='white-space: nowrap;'>End Date</th>
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
        foreach($relations as $r){
            $hqp = $r->getUser2();
            
            $start_date = substr($r->getStartDate(), 0, 10);
            $end_date = substr($r->getEndDate(), 0, 10);
            
            if($end_date != "0000-00-00"){
                $university = $hqp->getUniversityDuring($end_date, $end_date);
            }
            else{
                $university = $hqp->getUniversity();
            }
            
            $uni = $university['university'];
            $research_area = $university['research_area'];
            $position = $university['position'];
            if(!in_array(strtolower($position), $hqpTypes)){
                continue;
            }
            $end_date = ($end_date == '0000-00-00')? "Current" : $end_date;
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
            /*$rel = array_merge($hqp->getSupervisors(), $hqp->getCommittee());
            foreach($rel as $rels){
                if(count($rel) == 1){
                   break;
                }
                $names[] = "<a href='{$rels->getUrl()}'>{$rels->getNameForForms()}</a>";
            } */
            $awards = $hqp->getPapersAuthored('Award', $startDate, $endDate);
            $awardCitations = array();
            foreach($awards as $award){
                $awardCitations[] = "<span style='margin-left:2em;><a href='{$award->getUrl()}'>{$award->getTitle()}</a></span>";
            }
            $rowspan = 1;
            if(count($awardCitations) > 0){
                $rowspan = 2;
            }
            $html .= 
            "<tr>
                <td width='25%' rowspan='$rowspan' style='white-space: nowrap;'><a href='{$hqp->getUrl()}'>{$hqp->getReversedName()}</a></td>
                <td width='25%' style='white-space: nowrap;'>$position</td>
                <td width='15%' style='white-space: nowrap;'>$start_date</td>
                <td width='15%' style='white-space: nowrap;'>$end_date</td>
                <!--td>$research_area</td-->
                <!--td></td><td>".implode("; ",$names)."</td-->
                <td width='20%' style='white-space: nowrap;'>$role</td>
            </tr>";
            if(count($awardCitations) > 0){
                $html .= "<tr><td colspan='4'><b>Awards</b><br />".implode("<br />", $awardCitations)."</td></tr>";
            }
        }
        $html .= "</tbody></table>";
        return $html;
    }
    
    /**
     * Displays all of the user's relations
     */
    function showSupervisorRelations($person, $visibility){
        global $wgUser, $wgOut, $wgScriptPath, $wgServer;
        $html = "";
        $startRange = (isset($_GET['startRange'])) ? $_GET['startRange'] : CYCLE_START;
        $endRange   = (isset($_GET['endRange']))   ? $_GET['endRange']   : CYCLE_END;
        if($wgUser->isLoggedIn() && ($visibility['edit'] || (!$visibility['edit'] && (count($person->getRelations('public')) > 0 || count($person->getSupervisors(true)) > 0 || ($visibility['isMe'] && count($person->getRelations()) > 0))))){
            if($person->isRoleAtLeast(HQP) || ($person->isRole(INACTIVE) && $person->wasLastRoleAtLeast(HQP))){
                $html .= "<h3>Graduate Students (Supervised or Co-supervised)</h3>";
                $html .= $this->supervisesHTML(array("phd","msc","phd student", "msc student", "graduate student - master's course", "graduate student - master's thesis", "graduate student - master's", "graduate student - master&#39;s", "graduate student - doctoral"), $startRange, $endRange);
                
                $html .= "<h3>Post-doctoral Fellows and Research Associates (Supervised or Co-supervised)</h3>";
                $html .= $this->supervisesHTML(array("pdf","post-doctoral fellow"), $startRange, $endRange);
                
                $html .= "<h3>Technicians</h3>";
                $html .= $this->supervisesHTML(array("technician", "ra", "research/technical assistant", "professional end user"), $startRange, $endRange);
                
                $html .= "<h3>Undergraduates</h3>";
                $html .= $this->supervisesHTML(array("ugrad", "undergraduate", "undergraduate student"), $startRange, $endRange);
                
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
        $startRange = (isset($_GET['startRange'])) ? $_GET['startRange'] : CYCLE_START;
        $endRange   = (isset($_GET['endRange']))   ? $_GET['endRange']   : CYCLE_END;
        if($wgUser->isLoggedIn() && ($visibility['edit'] || (!$visibility['edit'] && (count($person->getRelations('public')) > 0 || count($person->getSupervisors(true)) > 0 || ($visibility['isMe'] && count($person->getRelations()) > 0))))){
            if($person->isRoleAtLeast(HQP) || ($person->isRole(INACTIVE) && $person->wasLastRoleAtLeast(HQP))){
                $html .= "<table width='100%'><tr>";
                if(count($person->getRelationsDuring('all', $startRange, $endRange)>0)){
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
                    $relations = $person->getRelationsDuring('all', $startRange, $endRange);
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
