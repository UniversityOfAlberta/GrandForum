<?php

class PersonGSMSOutcomeTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonGSMSOutcomeTab($person, $visibility){
        parent::AbstractEditableTab("GSMS Outcome");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        // Call APIs here
        $_POST['user_name'] = $this->person->getName();
        $_POST['degree_count'] = $this->person->getName();

        $api = new UserGsmsOutcomeAPI();
        $api->doAction(true);
    }

    function generateBody(){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
        if($this->canEdit()){
            $gsms = $this->person->getGSMSOutcome();
            $this->html .= "<table class='gsms'>";

	    $this->html .= "<th>Most Recent Academic Degree </th>";

	    $this->html .= "<tr>";
	    $this->html .= "<td>";
	    $this->html .= "<table class='gsms'>";
	    //$gsms_degrees = array();
            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Academic Year</td>";
            $this->html .= "<td class='num'>{$gsms['academic_year']}</td>";
            $this->html .= "</tr>";

	    $this->html .= "<tr>";
            $this->html .= "<td class='label'>Term</td>";
            $this->html .= "<td class='num'>{$gsms['term']}</td>";
            $this->html .= "</tr>";
            
            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Program</td>";
            $this->html .= "<td class='num'>{$gsms['program']}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Degree Code</td>";
            $this->html .= "<td class='num'>{$gsms['degree']}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Folder</td>";
            $this->html .= "<td class='num'>{$gsms['folder']}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Decision Response</td>";
            $this->html .= "<td class='num'>{$gsms['decision_response']}</td>";
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
        $gsms = $this->person->getGSMSOutcome();
        
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
            $fSelected = ($gsms['term'] == "fall") ? "selected='selected'" : "";
            $wSelected = ($gsms['term'] == "winter") ? "selected='selected'" : "";
            $sSelected = ($gsms['term'] == "spring") ? "selected='selected'" : "";
            $suSelected = ($gsms['term'] == "summer") ? "selected='selected'" : "";

            $rejSelected = ($gsms['folder'] == "Rejected Apps") ? "selected='selected'" : "";
            $declinedSelected = ($gsms['folder'] == "Offer Declined") ? "selected='selected'" : "";
            $withSelected = ($gsms['folder'] == "Withdrawn") ? "selected='selected'" : "";
            $waitSelected = ($gsms['folder'] == "Waitlist") ? "selected='selected'" : "";
            $acceptedSelected = ($gsms['folder'] == "Offer Accepted") ? "selected='selected'" : "";



        $this->html .= "<h1 style='margin:0;padding:0;'>{$this->person->getNameForForms()}</h1>";
        $this->html .= "<table id='gsms_bio'>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Academic Year: </td>";
        $this->html .= "<td><input name='academic_year' type='text' value='{$gsms['academic_year']}' /></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Term: </td>";
        $this->html .= "<td><select name='term'><option value='fall' $fSelected>Fall</option><option value='winter' $wSelected>Winter</option><option value='spring' $sSelected>Spring</option><option value='summer' $suSelected>Summer</option></select></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Program: </td>";
        $this->html .= "<td><input name='program' type='text' value='{$gsms['program']}' /></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";

        $this->html .= "<td class='label'>Degree Code: </td>";
        $this->html .= "<td><input name='degree' type='text' value='{$gsms['degree']}'/></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Folder: </td>";
        $this->html .= "<td>";
        $this->html .= "<select name='folder'><option value='Rejected Apps' $rejSelected>Rejected Apps</option><option value='Offer Declined' $declinedSelected>Offer Declined</option><option value='Offer Accepted' $acceptedSelected>Offer Accepted</option><option value='Withdrawn' $withSelected>Withdrawn</option><option value='Waitlist' $waitSelected>Waitlist</option></select>";
        $this->html .= "</td>";

        $this->html .= "<tr rowspan=2>";
        $this->html .= "</tr>";

        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Decision Response: </td>";
        $this->html .= "<td> <input name='decision_response' type='text' value='{$gsms['decision_response']}'/></td>";
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
