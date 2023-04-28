<?php

class PersonFECTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonFECTab($person, $visibility){
        parent::AbstractEditableTab("FEC History");
        $this->person = $person;
        $this->visibility = $visibility;
        $this->tooltip = "Contains information about important milestones in the faculty member's academic and employment record.";
    }
    
    function handleEdit(){
        $me = Person::newFromWgUser();
        $this->person->getFecPersonalInfo();
        if($me->isRoleAtLeast(STAFF)){
            $this->person->dateOfPhd = @$_POST['dateOfPhd'];
            $this->person->dateOfAppointment = @$_POST['dateOfAppointment'];
            $this->person->dateOfAssistant = @$_POST['dateOfAssistant'];
            $this->person->dateOfAssociate = @$_POST['dateOfAssociate'];
            $this->person->dateOfProfessor = @$_POST['dateOfProfessor'];
            $this->person->dateOfTenure = @$_POST['dateOfTenure'];
            $this->person->dateOfProbation1 = @$_POST['dateOfProbation1'];
            $this->person->dateOfProbation2 = @$_POST['dateOfProbation2'];
            $this->person->dateOfRetirement = @$_POST['dateOfRetirement'];
            $this->person->dateOfLastDegree = @$_POST['dateOfLastDegree'];
            $this->person->dateFso2 = @$_POST['dateFso2'];
            $this->person->dateFso3 = @$_POST['dateFso3'];
            $this->person->dateFso4 = @$_POST['dateFso4'];
            $this->person->dateAtsec1 = @$_POST['dateAtsec1'];
            $this->person->dateAtsec2 = @$_POST['dateAtsec2'];
            $this->person->dateAtsec3 = @$_POST['dateAtsec3'];
            $this->person->lastDegree = @str_replace("'", "&#39;", $_POST['lastDegree']);
            $this->person->updateFecInfo();
        }
        
        // Files
        $magic = MimeMagic::singleton();
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
        if(($me->isSubRole("FEC") && $this->person->isRole("Faculty")) ||
           ($me->isSubRole("ATSEC") && $this->person->isRole("ATS"))){
           return true;
        }
        return false;
    }
    
    function isChair(){
        $departments = array("Mathematical And Statistical Sciences",
                             "Chemistry",
                             "Psychology",
                             "Biological Sciences",
                             "Computing Science",
                             "Physics",
                             "Earth And Atmospheric Sciences");
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
        return ($me->getId() == $this->person->getId() || $me->isRoleAtLeast(STAFF) || $this->isChair() || $this->isCommittee());
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        if(!$this->userCanView()){
            return "";
        }
        $this->person->getFecPersonalInfo();
        $eFECLastYear = $this->person->getProductHistoryLastYear();
        if($eFECLastYear != ""){
            $eFECLastYear++;
            $report = new DummyReport("FEC", $this->person, null, $eFECLastYear);
            $reportSection = new ReportSection();
            $reportItem = new StaticReportItem();
            $reportItem->parent = $reportSection;
            $reportSection->parent = $report;
            $reportItem->personId = $this->person->getId();
            $callback = new ReportItemCallback($reportItem);
            $products = $this->person->getPapersAuthored("Publication", ($eFECLastYear)."-07-01", "2100-01-01", false);
            $count = 0;
            $peerCount = 0;
            foreach($products as $product){
                if($product->getData('peer_reviewed') == "Yes"){
                    $peerCount++;
                }
                $count++;
            }
            $this->html .= "<div style='float:right; display: inline-block;'>The lifetime total count of publications reported by the eFEC system by June 30, {$eFECLastYear} was {$callback->getUserLifetimePublicationCount('Publication')}. An additional {$count} publications (of which {$peerCount} refereed) have been imported to the Forum.</div>";
        }
        else{
            $products = $this->person->getPapersAuthored("Publication", "1900-07-01", "2100-01-01", false);
            $count = 0;
            $peerCount = 0;
            foreach($products as $product){
                if($product->getData('peer_reviewed') == "Yes"){
                    $peerCount++;
                }
                $count++;
            }
            $this->html .= "<div style='float: right; display: inline-block;'>{$count} publications (of which {$peerCount} refereed) have been imported to the Forum.</div>";
        }
        
        // Tenure
        $blb = new ReportBlob(BLOB_RAW, 0, $this->person->getId(), 0);
        $addr = ReportBlob::create_address('FEC_HISTORY', 'FEC_HISTORY', 'TENURE', 0);
        $result = $blb->load($addr, true);
        $tenureMD5 = $blb->getMD5();
        $tenureLink = ($tenureMD5 != "") ? "<a href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$tenureMD5}'>Download</a>" : "";
        
        // Promotion
        $blb = new ReportBlob(BLOB_RAW, 0, $this->person->getId(), 0);
        $addr = ReportBlob::create_address('FEC_HISTORY', 'FEC_HISTORY', 'PROMOTION', 0);
        $result = $blb->load($addr, true);
        $promotionMD5 = $blb->getMD5();
        $promotionLink = ($promotionMD5 != "") ? "<a href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$promotionMD5}'>Download</a>" : "";
        
        // Sabbatical
        $blb = new ReportBlob(BLOB_RAW, 0, $this->person->getId(), 0);
        $addr = ReportBlob::create_address('FEC_HISTORY', 'FEC_HISTORY', 'SABBATICAL', 0);
        $result = $blb->load($addr, true);
        $sabbaticalMD5 = $blb->getMD5();
        $sabbaticalLink = ($sabbaticalMD5 != "") ? "<a href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$sabbaticalMD5}'>Download</a>" : "";
        
        $this->html .= "<table>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Date of PhD:</b></td><td>".substr($this->person->dateOfPhd, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Date of Appointment:</b></td><td>".substr($this->person->dateOfAppointment, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Date of Assistant:</b></td><td>".substr($this->person->dateOfAssistant, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Date of Associate:</b></td><td>".substr($this->person->dateOfAssociate, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Date of Professor:</b></td><td>".substr($this->person->dateOfProfessor, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Date of FSO II:</b></td><td>".substr($this->person->dateFso2, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Date of FSO III:</b></td><td>".substr($this->person->dateFso3, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Date of FSO IV:</b></td><td>".substr($this->person->dateFso4, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Date of ATS I:</b></td><td>".substr($this->person->dateAtsec1, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Date of ATS II:</b></td><td>".substr($this->person->dateAtsec2, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Date of ATS III:</b></td><td>".substr($this->person->dateAtsec3, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>End of Probation 1:</b></td><td>".substr($this->person->dateOfProbation1, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>End of Probation 2:</b></td><td>".substr($this->person->dateOfProbation2, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Date of Tenure:</b></td><td>".substr($this->person->dateOfTenure, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Date of Retirement:</b></td><td>".substr($this->person->dateOfRetirement, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Date of Last Degree:</b></td><td>".substr($this->person->dateOfLastDegree, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Last Degree:</b></td><td>".$this->person->lastDegree."</td></tr>";
        if($this->isChair() || $this->isCommittee()){
            $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Tenure Package:</b></td><td>{$tenureLink}</td></tr>";
            $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Promotion Package:</b></td><td>{$promotionLink}</td></tr>";
        }
        $this->html .= "<tr><td align='right' style='white-space:nowrap;'><b>Sabbatical Package:</b></td><td>{$sabbaticalLink}</td></tr>";
        $this->html .= "</table>";
    }
    
    function generateEditBody(){
        if(!$this->userCanView()){
            return "";
        }
        $me = Person::newFromWgUser();
        $this->person->getFecPersonalInfo();
        $this->html .= "<table>";
        if($me->isRoleAtLeast(STAFF)){
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
            $this->html .= "<tr><td align='right'><b>End of Probation1:</b></td><td><input type='text' name='dateOfProbation1' class='calendar' style='display:none;' value='".substr($this->person->dateOfProbation1, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>End of Probation2:</b></td><td><input type='text' name='dateOfProbation2' class='calendar' style='display:none;' value='".substr($this->person->dateOfProbation2, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of Tenure:</b></td><td><input type='text' name='dateOfTenure' class='calendar' style='display:none;' value='".substr($this->person->dateOfTenure, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of Retirement:</b></td><td><input type='text' name='dateOfRetirement' class='calendar' style='display:none;' value='".substr($this->person->dateOfRetirement, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Date of Last Degree:</b></td><td><input type='text' name='dateOfLastDegree' class='calendar' style='display:none;' value='".substr($this->person->dateOfLastDegree, 0, 10)."' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Last Degree:</b></td><td><input type='text' name='lastDegree' value='".$this->person->lastDegree."' /></td></tr>";
        }
        if($this->isChair() || $this->isCommittee()){
            $this->html .= "<tr><td align='right'><b>Tenure Package:</b></td><td><input type='file' name='tenure' accept='application/pdf' /></td></tr>";
            $this->html .= "<tr><td align='right'><b>Promotion Package:</b></td><td><input type='file' name='promotion' accept='application/pdf' /></td></tr>";
        }
        $this->html .= "<tr><td align='right'><b>Sabbatical Package:</b></td><td><input type='file' name='sabbatical' accept='application/pdf' /></td></tr>";
        $this->html .= "</table>";
        
        $this->html .= "<script type='text/javascript'>
            $(document).ready(function(){
                $('input.calendar').show();
                $('input.calendar').keyup(function(){ return false; });
                $('input.calendar').keydown(function(){ return false; });
                $('input.calendar').datepicker({dateFormat: 'yy-mm-dd', 
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
                $('<span style=\"vertical-align: middle;\" class=\"delete-icon\" title=\"Clear Date\"></span>').insertAfter('input.calendar').click(function(){ $(this).prev().val('0000-00-00'); });
            });
        </script>";
    }
    
    function canEdit(){
        return $this->userCanView();
    }
    
}
?>
