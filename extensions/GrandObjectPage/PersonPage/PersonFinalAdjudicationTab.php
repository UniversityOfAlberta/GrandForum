<?php

class PersonFinalAdjudicationTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonFinalAdjudicationTab($person, $visibility){
        parent::AbstractEditableTab("Final Application Adjudication");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        // Call APIs here
        $_POST['user_name'] = $this->person->getName();
        $_POST['degree_count'] = $this->person->getName();

        $api = new UpdateUserFinalAdjudicationAPI();
        $api->doAction(true);
    }

    function generateBody(){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
        if($this->canEdit()){
            $gsms = $this->person->getGSMS();
            $this->html .= "<table class='gsms'>";

	    $this->html .= "<th>Final Applicant Adjudication </th>";

	    $this->html .= "<tr>";
	    $this->html .= "<td>";
	    $this->html .= "<table class='gsms'>";
            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Funding Note</td>";
            $this->html .= "<td class='text'>{$gsms->funding_note}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Department Decision</td>";
            $this->html .= "<td class='text'>{$gsms->department_decision}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>FGSR Decision</td>";
            $this->html .= "<td class='text'>{$gsms->fgsr_decision}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Decision Response</td>";
            $this->html .= "<td class='text'>{$gsms->decision_response}</td>";
            $this->html .= "</tr>";

	    $this->html .= "</table>";

            $this->html .= "</td>";
            $this->html .= "<td>";

            $this->html .= "<table class='gsms'>";

            $i=0;


            $this->html .= "</table>";


            $this->html .= "</td>";
            $this->html .= "<td>";
       

            $this->html .= "</td>";

            $this->html .= "</tr>";

            $this->html .= "</table><br />";
        }
        return $this->html;
    }
    
    function generateEditBody(){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
        $gsms = $this->person->getGSMS();
        $this->html .= "<style>
            input[type=number]::-webkit-inner-spin-button, 
            input[type=number]::-webkit-outer-spin-button { 
                -webkit-appearance: none;
                appearance: none;
                margin: 0; 
            }
            
            input[type=number] {
                -moz-appearance:textfield;
                width: 25px;
            }
            
            input[type=radio] {
                vertical-align: bottom;
            }
        </style>";


        $this->html .= "<h1 style='margin:0;padding:0;'>{$this->person->getNameForForms()}</h1>";
        $this->html .= "<table id='gsms_bio'>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Funding Note: </td>";
        $this->html .= "<td><input name='funding_note' type='text' value='{$gsms->funding_note}' /></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Department Decision: </td>";
        $this->html .= "<td><input name='department_decision' type='text' value='{$gsms->department_decision}' /></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";

        $this->html .= "<td class='label'>FGSR Decision: </td>";
        $this->html .= "<td><input name='fgsr_decision' type='text' value='{$gsms->fgsr_decision}'/></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Decision Response: </td>";
        $this->html .= "<td> <input name='decision_response' type='text' value='{$gsms->decision_response}'/></td>";
        $this->html .= "</tr>";

        $this->html .= "</table>";
        
        $this->html .= "<script type='text/javascript'>

                $(document).ready(function(){
                    $('.ui-state-default').hide();
                });
        </script>";
        return $this->html;
    }
    
    function canEdit(){
        $me = Person::newFromWgUser();
        return ($me->isRoleAtLeast(ADMIN));
    }
    
}
?>
