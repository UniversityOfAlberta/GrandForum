<?php

class PersonFECTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("FEC History");
        $this->person = $person;
        $this->visibility = $visibility;
        $this->tooltip = "Contains information about important milestones in the faculty member's academic and employment record.";
    }
    
    function handleEdit(){
        $me = Person::newFromWgUser();
        $this->person->getFecPersonalInfo();
        if($me->isRoleAtLeast(STAFF)){
            $sabbaticals = array();
            if(isset($_POST['sabbatical_start']) && is_array($_POST['sabbatical_start'])){
                foreach($_POST['sabbatical_start'] as $key => $start){
                    $sabbaticals[] = array('start' => $start,
                                           'duration' => @$_POST['sabbatical_duration'][$key]);
                }
            }
            $this->person->faculty = @$_POST['faculty'];
            $this->person->departments = array();
            if(isset($_POST['department1']) && $_POST['department1'] != ""){
                $this->person->departments[$_POST['department1']] = intval($_POST['department1_percent']);
            }
            if(isset($_POST['department2']) && $_POST['department2'] != ""){
                $this->person->departments[$_POST['department2']] = intval($_POST['department2_percent']);
            }
            $this->person->dateOfPhd = @$_POST['dateOfPhd'];
            $this->person->dateOfAppointment = @$_POST['dateOfAppointment'];
            $this->person->dateOfAssistant = @$_POST['dateOfAssistant'];
            $this->person->dateOfAssociate = @$_POST['dateOfAssociate'];
            $this->person->dateOfProfessor = @$_POST['dateOfProfessor'];
            $this->person->dateOfTenure = @$_POST['dateOfTenure'];
            $this->person->dateOfProbation1 = @$_POST['dateOfProbation1'];
            $this->person->dateOfProbation2 = @$_POST['dateOfProbation2'];
            $this->person->sabbatical = $sabbaticals;
            $this->person->dateOfRetirement = @$_POST['dateOfRetirement'];
            $this->person->dateOfLastDegree = @$_POST['dateOfLastDegree'];
            $this->person->dateFso2 = @$_POST['dateFso2'];
            $this->person->dateFso3 = @$_POST['dateFso3'];
            $this->person->dateFso4 = @$_POST['dateFso4'];
            $this->person->dateAtsec1 = @$_POST['dateAtsec1'];
            $this->person->dateAtsec2 = @$_POST['dateAtsec2'];
            $this->person->dateAtsec3 = @$_POST['dateAtsec3'];
            $this->person->dateAtsAnniversary = @$_POST['dateAtsAnniversary'];
            $this->person->lastDegree = @str_replace("'", "&#39;", $_POST['lastDegree']);
            $this->person->updateFecInfo();
        }
        
        // Files
        $magic = MediaWiki\MediaWikiServices::getInstance()->getMimeAnalyzer();
        
        if(isset($_FILES)){
            foreach($_FILES as $key => $file){
                if($file['tmp_name'] != ""){
                    $name = $file['name'];
                    $size = $file['size'];
                    $contents = base64_encode(file_get_contents($file['tmp_name']));
                    $mime = $magic->guessMimeType($file['tmp_name'], false);
                    $hash = md5($contents);
                    $data = array('name' => $name,
                                  'type' => $mime,
                                  'size' => $size,
                                  'hash' => $hash,
                                  'file' => $contents);
                    $blb = new ReportBlob(BLOB_RAW, 0, $this->person->getId(), 0);
                    $addr = ReportBlob::create_address('FEC_HISTORY', 'FEC_HISTORY', strtoupper($key), 0);
                    $blb->store(json_encode($data), $addr);
                }
            }
        }
    }
    
    function isCommittee(){
        $me = Person::newFromWgUser();
        if(($me->isRole("FEC") && $this->person->isRole("Faculty")) ||
           ($me->isRole("ATSEC") && $this->person->isRole("ATS"))){
           return true;
        }
        return false;
    }
    
    function isChair(){
        global $facultyMap;
        $departments = $facultyMap[getFaculty()]; 
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            return true;
        }
        if($me->isRole(CHAIR)){
            $myUnis = array_pluck($me->getUniversities(), 'department');
            $personUnis = array_pluck($this->person->getUniversities(), 'department');
            foreach($departments as $dept){
                if(array_search($dept, $myUnis) !== false &&
                   array_search($dept, $personUnis) !== false){
                    return true;
                }
            }
        }
        return false;
    }
    
    function userCanView(){
        $me = Person::newFromWgUser();
        return ($me->getId() == $this->person->getId() || $me->isRoleAtLeast(STAFF) || $me->isRole(HR) || $this->isChair() || $this->isCommittee());
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        if(!$this->userCanView()){
            return "";
        }
        $this->person->getFecPersonalInfo();
        
        $departments = array_keys($this->person->departments);
        $percents = array_values($this->person->departments);
        
        $this->html .= "<table>";
        $this->html .= ($this->person->faculty != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Faculty:</b></td><td>{$this->person->faculty}</td></tr>" : "";
        $this->html .= (isset($departments[0])) ? "<tr><td align='right' style='white-space:nowrap;'><b>Department:</b></td><td>{$departments[0]} ({$percents[0]}%)</td></tr>" : "";
        $this->html .= (isset($departments[1])) ? "<tr><td align='right' style='white-space:nowrap;'><b>Department 2:</b></td><td>{$departments[1]} ({$percents[1]}%)</td></tr>" : "";
        
        $this->html .= ($this->person->dateOfPhd != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of PhD:</b></td><td>".substr($this->person->dateOfPhd, 0, 10)."</td></tr>" : "";
        $this->html .= ($this->person->dateOfAppointment != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of Appointment:</b></td><td>".substr($this->person->dateOfAppointment, 0, 10)."</td></tr>" : "";
        $this->html .= ($this->person->dateOfAssistant != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of Assistant:</b></td><td>".substr($this->person->dateOfAssistant, 0, 10)."</td></tr>" : "";
        $this->html .= ($this->person->dateOfAssociate != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of Associate:</b></td><td>".substr($this->person->dateOfAssociate, 0, 10)."</td></tr>" : "";
        $this->html .= ($this->person->dateOfProfessor != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of Professor:</b></td><td>".substr($this->person->dateOfProfessor, 0, 10)."</td></tr>" : "";
        $this->html .= ($this->person->dateFso2 != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of FSO II:</b></td><td>".substr($this->person->dateFso2, 0, 10)."</td></tr>" : "";
        $this->html .= ($this->person->dateFso3 != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of FSO III:</b></td><td>".substr($this->person->dateFso3, 0, 10)."</td></tr>" : "";
        $this->html .= ($this->person->dateFso4 != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of FSO IV:</b></td><td>".substr($this->person->dateFso4, 0, 10)."</td></tr>" : "";
        $this->html .= ($this->person->dateAtsec1 != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of ATS I:</b></td><td>".substr($this->person->dateAtsec1, 0, 10)."</td></tr>" : "";
        $this->html .= ($this->person->dateAtsec2 != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of ATS II:</b></td><td>".substr($this->person->dateAtsec2, 0, 10)."</td></tr>" : "";
        $this->html .= ($this->person->dateAtsec3 != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of ATS III:</b></td><td>".substr($this->person->dateAtsec3, 0, 10)."</td></tr>" : "";
        $this->html .= ($this->person->dateAtsAnniversary != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of ATS Anniversary:</b></td><td>".substr($this->person->dateAtsAnniversary, 0, 10)."</td></tr>" : "";
        $this->html .= ($this->person->dateOfProbation1 != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>End of Probation 1:</b></td><td>".substr($this->person->dateOfProbation1, 0, 10)."</td></tr>" : "";
        $this->html .= ($this->person->dateOfProbation2 != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>End of Probation 2:</b></td><td>".substr($this->person->dateOfProbation2, 0, 10)."</td></tr>" : "";
        $this->html .= ($this->person->dateOfTenure != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of Tenure:</b></td><td>".substr($this->person->dateOfTenure, 0, 10)."</td></tr>" : "";
        if(!empty($this->person->sabbatical)){
            $sabbs = array();
            foreach($this->person->sabbatical as $sabbatical){
                $end = date('Y-m-d', strtotime("+{$sabbatical['duration']} month -1 day", strtotime($sabbatical['start'])));
                $sabbs[] = "{$sabbatical['start']} - {$end}";
            }
            $this->html .= "<tr><td align='right' valign='top' style='white-space:nowrap;'><b>Sabbaticals:</b></td><td>".implode("<br />", $sabbs)."</td></tr>";
        }
        $this->html .= ($this->person->dateOfRetirement != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of Retirement:</b></td><td>".substr($this->person->dateOfRetirement, 0, 10)."</td></tr>" : "";
        //$this->html .= ($this->person->dateOfLastDegree != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Date of Last Degree:</b></td><td>".substr($this->person->dateOfLastDegree, 0, 10)."</td></tr>" : "";
        //$this->html .= ($this->person->lastDegree != "") ? "<tr><td align='right' style='white-space:nowrap;'><b>Last Degree:</b></td><td>".$this->person->lastDegree."</td></tr>" : "";
        $this->html .= "</table>";
    }
    
    function generateEditBody(){
        global $facultyMap, $facultyMapSimple;
        if(!$this->userCanView()){
            return "";
        }
        $me = Person::newFromWgUser();
        $this->person->getFecPersonalInfo();
        $this->html .= "<table>";
        if($me->isRoleAtLeast(STAFF)){
            $sabbaticalsHTML = array();
            if(!empty($this->person->sabbatical)){
                foreach($this->person->sabbatical as $sabbatical){
                    $sixSelected = ($sabbatical['duration'] == "6") ? " selected" : "";
                    $twelveSelected = ($sabbatical['duration'] == "12") ? " selected" : "";
                    $sabbaticalsHTML[] = "<div class='sabbatical'>
                                            <input type='text' class='calendar' name='sabbatical_start[]' value='{$sabbatical['start']}' />&nbsp;
                                            <select name='sabbatical_duration[]'><option value='6' {$sixSelected}>6 Months</option><option value='12' {$twelveSelected}>12 Months</option></select>
                                          </div>";
                }
            }
            $departments = array_keys($this->person->departments);
            $percents = array_values($this->person->departments);
            
            $facultySelect = new SelectBox('faculty', 'faculty', $this->person->faculty, array_merge(array('', 'All'), array_keys($facultyMap)));
            $department1Select = new SelectBox('department1', 'department1', @$departments[0], array_merge(array(''), $facultyMapSimple));
            $department2Select = new SelectBox('department2', 'department2', @$departments[1], array_merge(array(''), $facultyMapSimple));
            
            $this->html .= "<tr><td align='right'><b>Faculty:</b></td><td>{$facultySelect->render()}</td></tr>";
            $this->html .= @"<tr><td align='right'><b>Department:</b></td><td>{$department1Select->render()} <input type='text' name='department1_percent' value='{$percents[0]}' style='width:3em;' />%</td></tr>";
            $this->html .= @"<tr><td align='right'><b>Department 2:</b></td><td>{$department2Select->render()} <input type='text' name='department2_percent' value='{$percents[1]}' style='width:3em;' />%</td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of PhD:</b></td><td><input type='text' name='dateOfPhd' class='calendar' style='display:none;' value='".substr($this->person->dateOfPhd, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of Appointment:</b></td><td><input type='text' name='dateOfAppointment' class='calendar' style='display:none;' value='".substr($this->person->dateOfAppointment, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of Assistant:</b></td><td><input type='text' name='dateOfAssistant' class='calendar' style='display:none;' value='".substr($this->person->dateOfAssistant, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of Associate:</b></td><td><input type='text' name='dateOfAssociate' class='calendar' style='display:none;' value='".substr($this->person->dateOfAssociate, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of Professor:</b></td><td><input type='text' name='dateOfProfessor' class='calendar' style='display:none;' value='".substr($this->person->dateOfProfessor, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of FSO II:</b></td><td><input type='text' name='dateFso2' class='calendar' style='display:none;' value='".substr($this->person->dateFso2, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of FSO III:</b></td><td><input type='text' name='dateFso3' class='calendar' style='display:none;' value='".substr($this->person->dateFso3, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of FSO IV:</b></td><td><input type='text' name='dateFso4' class='calendar' style='display:none;' value='".substr($this->person->dateFso4, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of ATS I:</b></td><td><input type='text' name='dateAtsec1' class='calendar' style='display:none;' value='".substr($this->person->dateAtsec1, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of ATS II:</b></td><td><input type='text' name='dateAtsec2' class='calendar' style='display:none;' value='".substr($this->person->dateAtsec2, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of ATS III:</b></td><td><input type='text' name='dateAtsec3' class='calendar' style='display:none;' value='".substr($this->person->dateAtsec3, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of ATS Anniversary:</b></td><td><input type='text' name='dateAtsAnniversary' class='calendar' style='display:none;' value='".substr($this->person->dateAtsAnniversary, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>End of Probation1:</b></td><td><input type='text' name='dateOfProbation1' class='calendar' style='display:none;' value='".substr($this->person->dateOfProbation1, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>End of Probation2:</b></td><td><input type='text' name='dateOfProbation2' class='calendar' style='display:none;' value='".substr($this->person->dateOfProbation2, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of Tenure:</b></td><td><input type='text' name='dateOfTenure' class='calendar' style='display:none;' value='".substr($this->person->dateOfTenure, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right' class='label'><b>Sabbaticals:</b></td><td><div id='sabbaticals'>".implode("<hr />", $sabbaticalsHTML)."</div><button id='minus' type='button'>-</button><button id='plus' type='button'>+</button></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of Retirement:</b></td><td><input type='text' name='dateOfRetirement' class='calendar' style='display:none;' value='".substr($this->person->dateOfRetirement, 0, 10)."' /></td></tr>";
            $this->html .= "<tr style='display:none;'><td align='right'><b>Date of Last Degree:</b></td><td><input type='text' name='dateOfLastDegree' class='calendar' style='display:none;' value='".substr($this->person->dateOfLastDegree, 0, 10)."' /></td></tr>";
            $this->html .= "<tr style='display:none;'><td align='right'><b>Last Degree:</b></td><td><input type='text' name='lastDegree' value='".$this->person->lastDegree."' /></td></tr>";
        }
        $this->html .= "</table>";
        
        $this->html .= "<script type='text/javascript'>
            $(document).ready(function(){
                function initCals(){
                    $('input.calendar:not(.initialized)').show();
                    $('input.calendar:not(.initialized)').datepicker({dateFormat: 'yy-mm-dd', 
                                                    changeYear: true, 
                                                    changeMonth: true, 
                                                    yearRange: '1900:".(date('Y')+3)."',
                                                    onChangeMonthYear: function (year, month, inst) {
                                                        var curDate = $(this).datepicker('getDate');
                                                        if (curDate == null)
                                                            return;
                                                        if (curDate.getYear() != year || curDate.getMonth() != month - 1) {
                                                            curDate.setYear(year);
                                                            curDate.setMonth(month - 1);
                                                            while(curDate.getMonth() != month -1){
                                                                curDate.setDate(curDate.getDate() - 1);
                                                            }
                                                            $(this).datepicker('setDate', curDate);
                                                            $(this).trigger('change');
                                                        }
                                                    }
                                                  });
                    $('<span style=\"vertical-align: middle;\" class=\"delete-icon\" title=\"Clear Date\"></span>').insertAfter('input.calendar:not(.initialized):not([name=sabbatical_start\\\[\\\]])').click(function(){ $(this).prev().val(ZOT); });
                    $('input.calendar:not(.initialized)').addClass('initialized');
                }
                initCals();
                
                $('#plus').click(function(){
                    if($('.sabbatical').length > 0){
                        $('#sabbaticals').append('<hr />');
                    }
                    $('#sabbaticals').append(\"<div class='sabbatical'><input type='text' class='calendar' name='sabbatical_start[]' />&nbsp;<select name='sabbatical_duration[]'><option value='6'>6 Months</option><option value='12'>12 Months</option></select></div>\");
                    initCals();
                });
                
                $('#minus').click(function(){
                    $('#sabbaticals hr').last().remove();
                    $('.sabbatical').last().remove();
                });
            });
        </script>";
    }
    
    function canEdit(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            return true;
        }
        return false;
    }
    
}
?>
