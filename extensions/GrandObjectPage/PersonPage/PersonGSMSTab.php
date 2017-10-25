<?php

class PersonGSMSTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonGSMSTab($person, $visibility){
        parent::AbstractEditableTab("Department Review");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        // Call APIs here
        $_POST['user_name'] = $this->person->getName();
        $_POST['degree_count'] = $this->person->getName();

        $api = new UserGsmsAPI();
        $api->doAction(true);
    }

    function generateBody(){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
        if($this->canEdit()){
            $gsms = $this->person->getGSMS();
	    $gsms_degrees = $gsms->getDegrees();
	    if(!is_array($gsms_degrees)){
		unserialize($gsms_degrees);
	    }
            $this->html .= "<table class='gsms'>";

	    $this->html .= "<th>Most Recent Academic Degree </th>";
            $this->html .= "<th>Additional Degrees</th>";
            $this->html .= "<th>Background</th>";

	    $this->html .= "<tr>";
	    $this->html .= "<td>";
	    $this->html .= "<table class='gsms'>";
	    //$gsms_degrees = array();
	    if(count($gsms_degrees) > 0){
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Degree (Institution):</td>";
                $this->html .= "<td>{$gsms_degrees[0]['degree']} ({$gsms_degrees[0]['institution']}) </td>";
                $this->html .= "</tr>";

	    }
	    $this->html .= "<tr>";
            $this->html .= "<td class='label'>GPA (over last 60 credits):</td>";
            $this->html .= "<td class='num'>{$gsms->gpa60}</td>";
            $this->html .= "</tr>";
            
            $this->html .= "<tr>";
            $this->html .= "<td class='label'>GPA (over best full year)/number of credits:</td>";
            $this->html .= "<td class='num'>{$gsms->gpafull}/{$gsms->gpafull_credits}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>GPA2 (over best full year)/number of credits:</td>";
            $this->html .= "<td class='num'>{$gsms->gpafull2}/{$gsms->gpafull_credits2}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Number of Failures:</td>";
            $this->html .= "<td class='num'>{$gsms->failures}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Number of Withdrawals:</td>";
            $this->html .= "<td class='num'>{$gsms->withdrawals}</td>";
            $this->html .= "</tr>";

	    $this->html .= "</table>";

            $this->html .= "</td>";
            $this->html .= "<td>";

            $this->html .= "<table class='gsms'>";

            $i=0;
            foreach($gsms_degrees as $degree){
		if($i ==0){
		   $i = $i+1;
		    continue;
		}
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Degree (Institution):</td>";
                $this->html .= "<td>{$degree['degree']} ({$degree['institution']})</td>";
                $this->html .= "</tr>";
            }


            $this->html .= "</table>";


            $this->html .= "</td>";
            $this->html .= "<td>";
            $this->html .= "<table class='gsms'>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Notes:</td>";
            $this->html .= "<td>{$gsms->notes}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Indigenous:</td>";
            $this->html .= "<td>{$gsms->indigenous}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Canadian:</td>";
            $this->html .= "<td>{$gsms->canadian}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Saskatchewan:</td>";
            $this->html .= "<td>{$gsms->saskatchewan}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>International:</td>";
            $this->html .= "<td>{$gsms->international}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Anatomy:</td>";
            $this->html .= "<td>{$gsms->anatomy}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Stats:</td>";
            $this->html .= "<td>{$gsms->stats}</td>";
            $this->html .= "</tr>";

            $this->html .= "</table>";

            $this->html .= "</td>";

            $this->html .= "</tr>";

            $this->html .= "</table><br />";
        }
        
        $this->showSop($this->person, $this->visibility);
        return $this->html;
    }
    
    function generateEditBody(){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
        $gsms = $this->person->getGSMS();
	$sop = $this->person->getSOP();
	$visible = $sop->visible;
        $gsms_degrees = $gsms->getDegrees();
        
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
        $indigenousYes = ($gsms->indigenous == "Yes") ? "checked" : "";
        $canadianYes = ($gsms->canadian == "Yes") ? "checked" : "";
        $saskatchewanYes = ($gsms->saskatchewan == "Yes") ? "checked" : "";
        $internationalYes = ($gsms->international == "Yes") ? "checked" : "";

        $viewYes = ($visible == "true") ? "checked" : "";

 
        $anatomyYes = ($gsms->anatomy == "Yes") ? "checked" : "";
        $anatomyNo  = ($gsms->anatomy == "No")  ? "checked" : "";
        $anatomyInProgress  = ($gsms->anatomy == "In-Progress")  ? "checked" : "";

        
        $statsYes = ($gsms->stats == "Yes") ? "checked" : "";
        $statsNo  = ($gsms->stats == "No")  ? "checked" : "";
        $statsInProgress  = ($gsms->stats == "In-Progress")  ? "checked" : "";

        $this->html .= "<h1 style='margin:0;padding:0;'>{$this->person->getNameForForms()}</h1>";
        $this->html .= "<table id='gsms_bio'>";

        $this->html .= "<tr>";
        $this->html .= "<td> <input name='view' type='checkbox' value='true' $viewYes /> Visible &nbsp";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>GPA (over last 60 credits):</td>";
        $this->html .= "<td><input name='gpa' type='number' step='0.01' min='0' max='4' size='4' value='{$gsms->gpa60}' /></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>GPA (over best full year)/number of credits:</td>";
        $this->html .= "<td><input name='gpafull' type='number' step='0.01' min='0' max='4' size='4' value='{$gsms->gpafull}' />/
                            <input name='gpafull_credits' type='number' step='1' min='0' size='4' value='{$gsms->gpafull_credits}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>GPA2 (over best full year)/number of credits:</td>";
        $this->html .= "<td><input name='gpafull2' type='number' step='0.01' min='0' max='4' size='4' value='{$gsms->gpafull2}' />/
                            <input name='gpafull_credits2' type='number' step='1' min='0' size='4' value='{$gsms->gpafull_credits2}' /></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Notes:</td>";
        $this->html .= "<td style='width:600px;'><input name='notes' type='text' value='{$gsms->notes}' style='width:200px' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Nationality Notes:</td>";
        $this->html .= "<td>";
        $this->html .= "<input name='indigenous' type='checkbox' value='Yes' $indigenousYes /> Indigenous<br />";
        $this->html .= "<input name='canadian' type='checkbox' value='Yes' $canadianYes /> Canadian<br />";
        $this->html .= "<input name='saskatchewan' type='checkbox' value='Yes' $saskatchewanYes /> Saskatchewan<br />";
        $this->html .= "<input name='international' type='checkbox' value='Yes' $internationalYes /> International";
        $this->html .= "</td>";

        $this->html .= "<tr rowspan=2>";
        $this->html .= "</tr>";

        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Anatomy:</td>";
        $this->html .= "<td> <input name='anatomy' type='radio' value='Yes' $anatomyYes /> Yes &nbsp;
                            <input name='anatomy' type='radio' value='No'  $anatomyNo  /> No  &nbsp;
                            <input name='anatomy' type='radio' value='In-Progress'  $anatomyInProgress  /> In-Progress</td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Stats:</td>";
        $this->html .= "<td><input name='stats' type='radio' value='Yes' $statsYes /> Yes  &nbsp;
                            <input name='stats' type='radio' value='No'  $statsNo  /> No  &nbsp;
                            <input name='stats' type='radio' value='In-Progress'  $statsInProgress  /> In-Progress</td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Number of Failures:</td>";
        $this->html .= "<td><input name='failures' type='number' step='1' min='0' value='{$gsms->failures}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Number of Withdrawals:</td>";
        $this->html .= "<td><input name='withdrawals' type='number' step='1' min='0' value='{$gsms->withdrawals}' /></td>";
        $this->html .= "</tr>";

        if(count($gsms_degrees) ==0){
            $this->html .= "<tr id='degree_row0'>";
            $this->html .= "<td class='label'>Degree/Institution:</td>";
            $this->html .= "<td><input class='degree_button_plus' name='0' type='button' value='+'></td>";
            $this->html .= "</tr>";
        }

        $i = 1;
        foreach($gsms_degrees as $degree){
            $this->html .= "<tr id='degree_row$i'>";
            $this->html .= "<td class='label'>Degree/Institution:</td>";
            $this->html .= "<td><input name='degree$i' class='degree' type='text' value='{$degree['degree']}' />/<input name='institution$i' class='institution' type='text' value='{$degree['institution']}' /><input class='degree_button_minus' name='$i' type='button' value='-'><input class='degree_button_plus' name='$i' type='button' value='+'></td>";
            $this->html .= "</tr>";
            $i = $i + 1;
        }

        $this->html .= "</table>";
        
        $this->html .= "<script type='text/javascript'>

                $(document).ready(function(){
                    $('.ui-state-default').hide();
                });

                $(document).on('click', '.degree_button_plus', function(){
                    var row_num = this.name;
                    if(row_num ==0){
                        $('#degree_row'+row_num).remove();
                    }
                    var i = $(\"[class='degree']\").size()+1;
                    for(i; i<100; i++){
                        var new_i = $(\"[name='degree\"+i+\"']\").size();
                        if(new_i ==0){
                            break;
                        }
                    }
                    $('#gsms_bio > tbody:last-child').append(\"<tr id='degree_row\"+i+\"'><td class='label'>Degree/Institution:</td><td><input name='degree\"+i+\"' class='degree' type='text' value=''>/<input name='institution\"+i+\"' class='institution' type='text' value=''><input class='degree_button_minus' name='\"+i+\"' type='button' value='-'><input class='degree_button_plus' name='\"+i+\"' type='button' value='+'></td></tr>\");
                });

                $(document).on('click', '.degree_button_minus', function(){
                    var row_num = this.name;
                    $('#degree_row'+row_num).remove();
                    var i = $(\"[class='degree']\").size();
                    if(i == 0){
                        $('#gsms_bio > tbody:last-child').append(\"<tr id='degree_row\"+i+\"'><td class='label'>Degree/Institution:</td><td><input class='degree_button_plus' name='0' type='button' value='+'></td></tr>\");
                    }
                });
        </script>";
        return $this->html;
    }
    
    /**
     * Displays Sop Review of user
     */
    function showSop($person,$visibility){
        global $wgUser;
        if(isExtensionEnabled('Sops')){
            $me = Person::newFromWgUser();
            if($person->isRole(CI) && $me->isRoleAtLeast(MANAGER)){
                if($person->getSop()){
                    $sop_url = $person->getSop()->getUrl();
            if(!$this->canEdit()){
            $this->html .= "<br /><br />";
            }
                    $this->html .= "<a class='button' href='$sop_url'>Review</a>";
                }
                if($person->getGSMSPdfUrl() != ""){
                    $review_url = $person->getGSMSPdfUrl();
                    $this->html .= "<a class='button' href='$review_url' target='_blank'>View GSMS PDF</a>";
                }
            }
        }
    }
    
    function canEdit() {
        $me = Person::newFromWgUser();
        return ($me->isRoleAtLeast(ADMIN));
    }
    
}
?>
