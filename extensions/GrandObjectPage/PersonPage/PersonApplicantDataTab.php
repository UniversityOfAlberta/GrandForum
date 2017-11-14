<?php

class PersonApplicantDataTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonApplicantDataTab($person, $visibility){
        parent::AbstractEditableTab("Applicant Data");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        // Call APIs here
        $_POST['user_name'] = $this->person->getName();

        $api = new UserApplicantDataAPI();
        $api->doAction(true);
    }

    function generateBody(){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
        if($this->canEdit()){
            $gsms = $this->person->getGSMS();
            $this->html .= "<br/><table class='gsms'>";

	    $this->html .= "<th><font color='green'>".$this->person->getNameForForms()." ({$gsms->gsms_id})</font></th>";

	    $this->html .= "<tr>";
	    $this->html .= "<td>";
	    $this->html .= "<table class='gsms'>";
            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Email</td>";
            $this->html .= "<td class='text'>{$this->person->getEmail()}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Student ID</td>";
            $this->html .= "<td class='text'>{$gsms->student_id}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>CS app#</td>";
            $this->html .= "<td class='text'>{$gsms->cs_app}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Gender</td>";
            $this->html .= "<td class='text'>{$gsms->gender}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>DOB</td>";
            $this->html .= "<td class='text'>{$gsms->date_of_birth}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Country of Birth</td>";
            $this->html .= "<td class='text'>{$gsms->country_of_birth}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Country of Citizenship</td>";
            $this->html .= "<td class='text'>{$gsms->country_of_citizenship}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Applicant Type</td>";
            $this->html .= "<td class='text'>{$gsms->applicant_type}</td>";
            $this->html .= "</tr>";


            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Folder</td>";
            $this->html .= "<td class='text'>{$gsms->folder}</td>";
            $this->html .= "</tr>";


            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Education History</td>";
            $this->html .= "<td class='text'>{$gsms->education_history}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>EPL Test</td>";
            $this->html .= "<td class='text'>{$gsms->epl_test}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>EPL Score</td>";
            $this->html .= "<td class='text'>{$gsms->epl_score}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Listen</td>";
            $this->html .= "<td class='text'>{$gsms->epl_listen}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Write</td>";
            $this->html .= "<td class='text'>{$gsms->epl_write}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Read</td>";
            $this->html .= "<td class='text'>{$gsms->epl_read}</td>";
            $this->html .= "</tr>";


            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Speaking</td>";
            $this->html .= "<td class='text'>{$gsms->epl_speaking}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Academic Year</td>";
            $this->html .= "<td class='text'>{$gsms->academic_year}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Term</td>";
            $this->html .= "<td class='text'>{$gsms->term}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Program Subplan Name</td>";
            $this->html .= "<td class='text'>{$gsms->subplan_name}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Degree Code</td>";
            $this->html .= "<td class='text'>{$gsms->degree_code}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Program Name</td>";
            $this->html .= "<td class='text'>{$gsms->program_name}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Admission Program Name</td>";
            $this->html .= "<td class='text'>{$gsms->admission_program_name}</td>";
            $this->html .= "</tr>";

            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Submitted Date</td>";
            $this->html .= "<td class='text'>{$gsms->submitted_date}</td>";
            $this->html .= "</tr>";
	    $this->html .= "</table>";

            $this->html .= "</td>";
            $this->html .= "<td>";

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
            $fSelected = ($gsms->term == "fall") ? "selected='selected'" : "";
            $wSelected = ($gsms->term == "winter") ? "selected='selected'" : "";
            $sSelected = ($gsms->term == "spring") ? "selected='selected'" : "";
            $suSelected = ($gsms->term == "summer") ? "selected='selected'" : "";

            $bSelected = ($gsms->folder == "") ? "selected='selected'" : "";
            $progSelected = ($gsms->folder == "In Progress") ? "selected='selected'" : "";
            $rprogrSelected = ($gsms->folder == "Review in Progress") ? "selected='selected'" : "";
            $newappSelected = ($gsms->folder == "New Applications") ? "selected='selected'" : "";


            $rejSelected = ($gsms->folder == "Rejected Apps") ? "selected='selected'" : "";
            $declinedSelected = ($gsms->folder == "Offer Declined") ? "selected='selected'" : "";
            $withSelected = ($gsms->folder == "Withdrawn") ? "selected='selected'" : "";
            $waitSelected = ($gsms->folder == "Waitlist") ? "selected='selected'" : "";
            $acceptedSelected = ($gsms->folder == "Offer Accepted") ? "selected='selected'" : "";

///----------------------------START HERE -------///

        $this->html .= "<h1 style='margin:0;padding:0;'>{$this->person->getNameForForms()}</h1>";
        $this->html .= "<table id='gsms_bio'>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Email: </td>";
        $this->html .= "<td><input name='email' type='text' value='{$this->person->getEmail()}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>GSMS ID: </td>";
        $this->html .= "<td><input name='gsms_id' style='width:100px' type='number' value='{$gsms->gsms_id}' /></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Student ID: </td>";
        $this->html .= "<td><input name='student_id' style='width:100px' type='number' value='{$gsms->student_id}' /></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";

        $this->html .= "<td class='label'>CS App#: </td>";
        $this->html .= "<td><input name='cs_app' style='width:100px' type='number' value='{$gsms->cs_app}'/></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Gender: </td>";
        $this->html .= "<td><input name='gender' type='text' value='{$gsms->gender}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>DOB: </td>";
        $this->html .= "<td><input name='dob' type='date' value='{$gsms->date_of_birth}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Country of Birth: </td>";
        $this->html .= "<td><input name='country_birth' type='text' value='{$gsms->country_of_birth}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Country of Citizenship: </td>";
        $this->html .= "<td><input name='country_citizenship' type='text' value='{$gsms->country_of_citizenship}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Applicant Type:  </td>";
        $this->html .= "<td><input name='applicant_type' type='text' value='{$gsms->applicant_type}' /></td>";
        $this->html .= "</tr>";



        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Folder: </td>";
        $this->html .= "<td>";
        $this->html .= "<select name='folder'>
                        <option value='' $bSelected>--</option>
                        <option value='' $newappSelected>New Applications</option>
                        <option value='In Progress' $progSelected>In Progress</option>
                        <option value='Review in Prgoress' $rprogrSelected>Review in Progress</option>
                        <option value='Rejected Apps' $rejSelected>Rejected Apps</option>
                        <option value='Offer Declined' $declinedSelected>Offer Declined</option>
                        <option value='Offer Accepted' $acceptedSelected>Offer Accepted</option>
                        <option value='Withdrawn' $withSelected>Withdrawn</option>
                        <option value='Waitlist' $waitSelected>Waitlist</option></select>";
        $this->html .= "</td>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Education History: </td>";
        $this->html .= "<td><input name='education_history' type='text' value='{$gsms->education_history}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>EPL Test: </td>";
        $this->html .= "<td><input name='epl_test' style='width:100px' type='number' value='{$gsms->epl_test}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>EPL Score: </td>";
        $this->html .= "<td><input name='epl_score' style='width:100px' type='number' value='{$gsms->epl_score}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Listen: </td>";
        $this->html .= "<td><input name='listen' style='width:100px' type='number' value='{$gsms->epl_listen}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Write: </td>";
        $this->html .= "<td><input name='write' style='width:100px' type='number' value='{$gsms->epl_write}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Read: </td>";
        $this->html .= "<td><input name='read' style='width:100px' type='number' value='{$gsms->epl_read}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Speaking: </td>";
        $this->html .= "<td><input name='speaking' style='width:100px' type='number' value='{$gsms->epl_speaking}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Academic Year: </td>";
        $this->html .= "<td><input name='academic_year' type='text' value='{$gsms->academic_year}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Term: </td>";        $this->html .= "<td><select name='term'><option value='fall' $fSelected>Fall</option><option value='winter' $wSelected>Winter</option><option value='spring' $sSelected>Spring</option><option value='summer' $suSelected>Summer</option></select></td>";
        $this->html .= "</tr>";


        $this->html .= "<tr rowspan=2>";
        $this->html .= "</tr>";

        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Program Subplan Name: </td>";
        $this->html .= "<td> <input name='program_subplan' type='text' value='{$gsms->subplan_name}'/></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Degree Code: </td>";
        $this->html .= "<td> <input name='degree_code' style='width:100px' type='number' value='{$gsms->degree_code}'/></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Program Name: </td>";
        $this->html .= "<td> <input name='program_name' type='text' value='{$gsms->program_name}'/></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Admission Program Name: </td>";
        $this->html .= "<td> <input name='admission_program' type='text' value='{$gsms->admission_program_name}'/></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Submitted Date: </td>";
        $this->html .= "<td> <input name='submitted_date' type='date' value='{$gsms->submitted_date}'/></td>";
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
