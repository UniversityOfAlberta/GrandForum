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
    
    function supervisesHTML($position='other', $startDate=null, $endDate=null){
        $html = "<table class='wikitable relations_table' width='100%' cellspacing='1' cellpadding='2' rules='all' frame='box'>
                    <thead><tr>
                            <th width='28%'>Name</th>
                            <th width='22%'>Position</th>
                            <th width='' style='white-space: nowrap;'>Start Date</th>
                            <th width='' style='white-space: nowrap;'>End Date</th>
                            <th width='' style='white-space: nowrap;'>Status</th>
                            <!--th style='white-space: nowrap;'>Research Area</th-->
                            <th width='10%'>Role</th>
                        </tr>
                    </thead><tbody>";
        $hqpTypes = (isset(Person::$studentPositions[$position])) ? Person::$studentPositions[$position] : $position;
        $rows = array();
        $data = $this->person->getStudentInfo($hqpTypes, $startDate, $endDate);
        foreach($data as $key => $row){
            $rows[$row['hqp']][$key] = "
                <td style='white-space: nowrap;'>{$row['position']}</td>
                <td style='white-space: nowrap;'>{$row['start_date']}</td>
                <td style='white-space: nowrap;'>{$row['end_date']}</td>
                <td style='white-space: nowrap;'>{$row['status']}</td>
                <td style='white-space: nowrap;'>{$row['role']}</td>";
        }
        
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
                $html .= $this->supervisesHTML('phd', $this->startRange, $this->endRange);
                
                $html .= "<h3>Master's Students (Supervised or Co-supervised)</h3>";
                $html .= $this->supervisesHTML('msc', $this->startRange, $this->endRange);
                
                $html .= "<h3>Undergraduates</h3>";
                $html .= $this->supervisesHTML('ugrad', $this->startRange, $this->endRange);
                
                $html .= "<h3>Post-doctoral Fellows and Research Associates (Supervised or Co-supervised)</h3>";
                $html .= $this->supervisesHTML('pdf',$this->startRange, $this->endRange);
                
                $html .= "<h3>Research/Technical Assistants</h3>";
                $html .= $this->supervisesHTML('tech', $this->startRange, $this->endRange);
                
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
                    $html .= $this->supervisesHTML('committee', $this->startRange, $this->endRange);
                }
            }
        }
        return $html;
    }
}
?>
