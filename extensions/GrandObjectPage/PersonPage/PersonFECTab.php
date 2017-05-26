<?php

class PersonFECTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonFECTab($person, $visibility){
        parent::AbstractEditableTab("FEC Info");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        $this->person->getFecPersonalInfo();
        $this->person->dateOfPhd = @$_POST['dateOfPhd'];
        $this->person->dateOfAppointment = @$_POST['dateOfAppointment'];
        $this->person->dateOfAssistant = @$_POST['dateOfAssistant'];
        $this->person->dateOfAssociate = @$_POST['dateOfAssociate'];
        $this->person->dateOfProfessor = @$_POST['dateOfProfessor'];
        $this->person->dateOfTenure = @$_POST['dateOfTenure'];
        $this->person->dateOfRetirement = @$_POST['dateOfRetirement'];
        $this->person->dateOfLastDegree = @$_POST['dateOfLastDegree'];
        $this->person->lastDegree = @str_replace("'", "&#39;", $_POST['lastDegree']);
        $this->person->updateFecInfo();
        //redirect("{$this->person->getUrl()}?tab=fec-info");
    }
    
    function userCanView(){
        $me = Person::newFromWgUser();
        return ($me->getId() == $this->person->getId() || $me->isRoleAtLeast(STAFF));
    }

    function generateBody(){
        if(!$this->userCanView()){
            return "";
        }
        $this->person->getFecPersonalInfo();
        $this->html .= "<table>";
        $this->html .= "<tr><td align='right'><b>Date of PhD:</b></td><td>".substr($this->person->dateOfPhd, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right'><b>Date of Appointment:</b></td><td>".substr($this->person->dateOfAppointment, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right'><b>Date of Assistant:</b></td><td>".substr($this->person->dateOfAssistant, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right'><b>Date of Associate:</b></td><td>".substr($this->person->dateOfAssociate, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right'><b>Date of Professor:</b></td><td>".substr($this->person->dateOfProfessor, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right'><b>Date of Tenure:</b></td><td>".substr($this->person->dateOfTenure, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right'><b>Date of Retirement:</b></td><td>".substr($this->person->dateOfRetirement, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right'><b>Date of Last Degree:</b></td><td>".substr($this->person->dateOfLastDegree, 0, 10)."</td></tr>";
        $this->html .= "<tr><td align='right'><b>Last Degree:</b></td><td>".$this->person->lastDegree."</td></tr>";
        $this->html .= "</table>";
    }
    
    function generateEditBody(){
        if(!$this->userCanView()){
            return "";
        }
        $this->person->getFecPersonalInfo();
        $this->html .= "<table>";
        $this->html .= "<tr><td align='right'><b>Date of PhD:</b></td><td><input type='text' name='dateOfPhd' class='calendar' value='".substr($this->person->dateOfPhd, 0, 10)."' /></td></tr>";
        $this->html .= "<tr><td align='right'><b>Date of Appointment:</b></td><td><input type='text' name='dateOfAppointment' class='calendar' value='".substr($this->person->dateOfAppointment, 0, 10)."' /></td></tr>";
        $this->html .= "<tr><td align='right'><b>Date of Assistant:</b></td><td><input type='text' name='dateOfAssistant' class='calendar' value='".substr($this->person->dateOfAssistant, 0, 10)."' /></td></tr>";
        $this->html .= "<tr><td align='right'><b>Date of Associate:</b></td><td><input type='text' name='dateOfAssociate' class='calendar' value='".substr($this->person->dateOfAssociate, 0, 10)."' /></td></tr>";
        $this->html .= "<tr><td align='right'><b>Date of Professor:</b></td><td><input type='text' name='dateOfProfessor' class='calendar' value='".substr($this->person->dateOfProfessor, 0, 10)."' /></td></tr>";
        $this->html .= "<tr><td align='right'><b>Date of Tenure:</b></td><td><input type='text' name='dateOfTenure' class='calendar' value='".substr($this->person->dateOfTenure, 0, 10)."' /></td></tr>";
        $this->html .= "<tr><td align='right'><b>Date of Retirement:</b></td><td><input type='text' name='dateOfRetirement' class='calendar' value='".substr($this->person->dateOfRetirement, 0, 10)."' /></td></tr>";
        $this->html .= "<tr><td align='right'><b>Date of Last Degree:</b></td><td><input type='text' name='dateOfLastDegree' class='calendar' value='".substr($this->person->dateOfLastDegree, 0, 10)."' /></td></tr>";
        $this->html .= "<tr><td align='right'><b>Last Degree:</b></td><td><input type='text' name='lastDegree' value='".$this->person->lastDegree."' /></td></tr>";
        $this->html .= "</table>";
        
        $this->html .= "<script type='text/javascript'>
            $('input.calendar').keyup(function(){ return false; });
            $('input.calendar').keydown(function(){ return false; });
            $('input.calendar').datepicker({dateFormat: 'yy-mm-dd'});
            $('<span style=\"vertical-align: middle;\" class=\"delete-icon\" title=\"Clear Date\"></span>').insertAfter('input.calendar').click(function(){ $(this).prev().val('0000-00-00'); });
        </script>";
    }
    
    function canEdit(){
        return $this->userCanView();
    }
    
}
?>
