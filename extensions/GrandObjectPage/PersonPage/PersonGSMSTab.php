<?php

class PersonGSMSTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonGSMSTab($person, $visibility){
        parent::AbstractEditableTab("Bio");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        // Call APIs here
        $_POST['user_name'] = $this->person->getName();
        $api = new UserGsmsAPI();
        $api->doAction(true);
    }

    function generateBody(){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
        $this->html .= <<<EOF
            <div id='card' style='min-height:142px;display:inline-block;vertical-align:top;'></div>
            <script type='text/javascript'>
                $(document).ready(function(){
                    var person = new Person({$this->person->toJSON()});
                    var card = new LargePersonCardView({el: $("#card"), model: person});
                    card.render();
                });
            </script>
EOF;
        if($this->canEdit()){
            $gsms = $this->person->getGSMS();
            $this->html .= "<h1>GSMS</h1>";
            $this->html .= "<table>";
            
            $this->html .= "<tr>";
            $this->html .= "<td class='label'>GPA (over last 60 credits):</td>";
            $this->html .= "<td>{$gsms['gpa60']}</td>";
            $this->html .= "</tr>";
            
            $this->html .= "<tr>";
            $this->html .= "<td class='label'>GPA (over best full year)/number of credits:</td>";
            $this->html .= "<td>{$gsms['gpafull']}/{$gsms['gpafull_credits']}</td>";
            $this->html .= "</tr>";
            
            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Notes:</td>";
            $this->html .= "<td>{$gsms['notes']}</td>";
            $this->html .= "</tr>";
            
            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Anatomy:</td>";
            $this->html .= "<td>{$gsms['anatomy']}</td>";
            $this->html .= "</tr>";
            
            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Stats:</td>";
            $this->html .= "<td>{$gsms['stats']}</td>";
            $this->html .= "</tr>";
            
            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Degree/Institution:</td>";
            $this->html .= "<td>{$gsms['degree']}/{$gsms['institution']}</td>";
            $this->html .= "</tr>";
            
            $this->html .= "<tr>";
            $this->html .= "<td class='label'>Number of Failures/Withdrawals:</td>";
            $this->html .= "<td>{$gsms['failures']}</td>";
            $this->html .= "</tr>";

            $this->html .= "</table><br />";
        }
        $this->showSop($this->person, $this->visibility);
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
        
        $anatomyYes = ($gsms['anatomy'] == "Yes") ? "checked" : "";
        $anatomyNo  = ($gsms['anatomy'] == "No")  ? "checked" : "";
        
        $statsYes = ($gsms['stats'] == "Yes") ? "checked" : "";
        $statsNo  = ($gsms['stats'] == "No")  ? "checked" : "";
        
        $this->html .= "<table>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>GPA (over last 60 credits):</td>";
        $this->html .= "<td><input name='gpa' type='number' step='0.01' min='0' max='4' size='4' value='{$gsms['gpa60']}' /></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>GPA (over best full year)/number of credits:</td>";
        $this->html .= "<td><input name='gpafull' type='number' step='0.01' min='0' max='4' size='4' value='{$gsms['gpafull']}' />/
                            <input name='gpafull_credits' type='number' step='1' min='0' size='4' value='{$gsms['gpafull_credits']}' /></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Notes:</td>";
        $this->html .= "<td style='width:400px;'><input name='notes' type='text' value='{$gsms['notes']}' /></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Anatomy:</td>";
        $this->html .= "<td><input name='anatomy' type='radio' value='Yes' $anatomyYes /> Yes<br />
                            <input name='anatomy' type='radio' value='No'  $anatomyNo  /> No</td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Stats:</td>";
        $this->html .= "<td><input name='stats' type='radio' value='Yes' $statsYes /> Yes<br />
                            <input name='stats' type='radio' value='No'  $statsNo  /> No</td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Degree/Institution:</td>";
        $this->html .= "<td><input name='degree' type='text' value='{$gsms['degree']}' />/<input name='institution' type='text' value='{$gsms['institution']}' /></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Number of Failures/Withdrawals:</td>";
        $this->html .= "<td><input name='failures' type='number' step='1' min='0' value='{$gsms['failures']}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "</table>";
        
        $this->html .= "<script type='text/javascript'>
            $('input[name=notes]').tagit();
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
            }
        }
    }
    
    function canEdit(){
        $me = Person::newFromWgUser();
        return ($me->isRoleAtLeast(ADMIN));
    }
    
}
?>
