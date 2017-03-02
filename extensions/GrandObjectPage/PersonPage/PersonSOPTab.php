<?php

class PersonSOPTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonSOPTab($person, $visibility){
        parent::AbstractEditableTab("SOP");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgUser;
        $this->person->getLastRole();
        $this->html .= "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:5px;'>";
        $this->html .= "</td><td id='firstLeft' width='60%' valign='top'>";
        $this->showContact($this->person, $this->visibility);
        $extra = array();
        
        // Delete extra widgets which have no content
        foreach($extra as $key => $e){
            if($e == ""){
                unset($extra[$key]);
            }
        }
        $this->html .= "</td><td id='firstRight' valign='top' width='40%' style='padding-top:15px;padding-left:15px;'>".implode("<hr />", $extra)."</td></tr>";
        $this->html .= "</table>";
        $this->html .= "<script type='text/javascript'>
            setInterval(function(){
                var table = $('#personProducts').DataTable();
                if($('#bodyContent').width() < 650){
                    $('td#firstRight').hide();
                    $('.chordChart').hide();
                    
                    table.column(1).visible(false);
                    table.column(2).visible(false);
                    table.column(3).visible(false);
                }
                else{
                    $('td#firstRight').show();
                    $('.chordChart').show();
                    
                    table.column(1).visible(true);
                    table.column(2).visible(true);
                    table.column(3).visible(true);
                }
            }, 33);
        </script>";
        return $this->html;
    }
    
    function generateEditBody(){
        $this->html .= "<table>";
        $this->html .= "</td><td style='padding-right:25px;' valign='top'>";
        $this->showEditContact($this->person, $this->visibility);
        $this->html .= "</table>";
    }
    
    function canEdit(){
        return ($this->visibility['isMe'] || 
                $this->visibility['isSupervisor']);
    }
    
    function handleEdit(){
        $this->handleContactEdit();
        $_POST['user_name'] = $this->person->getName();
        if(isset($_POST['role_title'])){
            foreach($this->person->getRoles() as $role){
                if(isset($_POST['role_title'][$role->getId()])){
                    $value = $_POST['role_title'][$role->getId()];
                    DBFunctions::update('grand_roles', 
                                        array('title' => $value),
                                        array('id' => $role->getId()));
                }
            }
        }
        Person::$rolesCache = array();
        Person::$cache = array();
        Person::$namesCache = array();
        Person::$idsCache = array();
        
        $this->person = Person::newFromId($this->person->getId());
    }
    
    function handleContactEdit(){
        global $wgImpersonating;
        $error = "";
        if($error == ""){
            // Insert the new data into the DB
            $_POST['user_name'] = $this->person->getName();
            $_POST['twitter'] = @$_POST['twitter'];
            $_POST['phone'] = @$_POST['phone'];
            $_POST['website'] = @$_POST['website'];
            $_POST['nationality'] = @$_POST['nationality'];
            $_POST['stakeholder'] = @$_POST['stakeholder'];
            $_POST['email'] = @$_POST['email'];
            $_POST['university'] = @$_POST['university'];
            $_POST['department'] = @$_POST['department'];
            $_POST['title'] = @$_POST['title'];
            $_POST['gender'] = @$_POST['gender'];

            $api = new UserUniversityAPI();
            $api->processParams(array());
            $api->doAction(true);

            $api = new UserPhoneAPI();
            $api->doAction(true);
            $api = new UserTwitterAccountAPI();
            $api->doAction(true);
            $api = new UserWebsiteAPI();
            $api->doAction(true);
            $api = new UserNationalityAPI();
            $api->doAction(true);
            $api = new UserStakeholderAPI();
            $api->doAction(true);
            $api = new UserEmailAPI();
            $api->doAction(true);
            $api = new UserGenderAPI();
            $api->doAction(true);
        }
        
        //Reset the cache to use the changed data
        unset(Person::$cache[$this->person->id]);
        unset(Person::$cache[$this->person->getName()]);
        Person::$idsCache = array();
        Person::$namesCache = array();
        $this->person = Person::newFromId($this->person->id);
        return $error;
    }
    
   /**
    * Displays the contact information for this person
    */
    function showContact($person, $visibility){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
        $this->html .= "<div id='contact' style='white-space: nowrap;position:relative;min-height:172px'>";
        $this->html .= <<<EOF
            <div id='card' style='min-height:142px;display:inline-block;vertical-align:top;'></div>
            <script type='text/javascript'>
                $(document).ready(function(){
                    var person = new Person({$person->toJSON()});
                    var card = new LargePersonCardView({el: $("#card"), model: person});
                    card.render();
                });
            </script>
EOF;
        $this->html .= "</div>";
    }
    
    function showEditContact($person, $visibility){
        global $wgOut, $wgUser, $config;
        $university = $person->getUniversity();
        $nationality = "";
        $me = Person::newFromWgUser();
        if($visibility['isMe'] || $visibility['isSupervisor']){
            $canSelected = ($person->getNationality() == "Canadian") ? "selected='selected'" : "";
            $amerSelected = ($person->getNationality() == "American") ? "selected='selected'" : "";
            $immSelected = ($person->getNationality() == "Landed Immigrant" || $person->getNationality() == "Foreign") ? "selected='selected'" : "";
            $visaSelected = ($person->getNationality() == "Visa Holder") ? "selected='selected'" : "";
            $interSelected = ($person->getNationality() == "International") ? "selected='selected'" : "";
            $nationality = "<tr>
                <td align='right'><b>Nationality:</b></td>
                <td>
                    <select name='nationality'>
                        <option value=''>---</option>
                        <option value='Canadian' $canSelected>Canadian</option>
                        <option value='American' $amerSelected>American</option>
                        <option value='Landed Immigrant' $immSelected>Landed Immigrant</option>
                        <option value='Visa Holder' $visaSelected>Visa Holder</option>
                        <option value='International' $interSelected>International</option>
                    </select>
                </td>
            </tr>";
            
            $blankSelected = ($person->getGender() == "") ? "selected='selected'" : "";
            $maleSelected = ($person->getGender() == "Male") ? "selected='selected'" : "";
            $femaleSelected = ($person->getGender() == "Female") ? "selected='selected'" : "";
            $gender = "<tr>
                <td align='right'><b>Gender:</b></td>
                <td>
                    <select name='gender'>
                        <option value='' $blankSelected>---</option>
                        <option value='Male' $maleSelected>Male</option>
                        <option value='Female' $femaleSelected>Female</option>
                    </select>
                </td>
            </tr>";
            
            $stakeholderCategories = $config->getValue('stakeholderCategories');
            $stakeholder = "";
            if(count($stakeholderCategories) > 0){
                $blankSelected = (!$person->isStakeholder()) ? "selected='selected'" : "";
                $stakeholder = "<tr>
                    <td align='right'><b>Stakeholder<br />Category:</b></td>
                    <td>
                        <select name='stakeholder'>
                            <option value='' $blankSelected>---</option>";
                foreach($stakeholderCategories as $category){
                    $selected = ($person->getStakeholder() == $category) ? "selected='selected'" : "";
                    $stakeholder .= "<option value='$category' $selected>$category</option>";
                }
                $stakeholder .= "</select>
                    </td>
                </tr>";
            }
        }
        $this->html .= "<table>
                            <tr>
                                <td align='right'><b>Email:</b></td>
                                <td><input size='30' type='text' name='email' value='".str_replace("'", "&#39;", $person->getEmail())."' /></td>
                            </tr>
                            {$nationality}
                            {$gender}
                            {$stakeholder}";
        
        $roles = $person->getRoles();
        $universities = new Collection(University::getAllUniversities());
        $uniNames = $universities->pluck('name');
        if($person->isRole(HQP) && ($person->isRoleAtMost(HQP) || $person->isRole(PL))){
            $positions = array("Other", 
                               "Graduate Student - Master's", 
                               "Graduate Student - Doctoral", 
                               "Post-Doctoral Fellow", 
                               "Research Associate", 
                               "Research Assistant", 
                               "Technician",
                               "Professional End User",
                               "Summer Student", 
                               "Undergraduate Student");
        }
        else{
            $positions = Person::getAllPositions();
        }
        $myPosition = "";
        foreach($positions as $key => $position){
            if($university['position'] == $position){
                $myPosition = $key;
            }
        }
        if($myPosition == ""){
            $positions[] = $university['position'];
            $myPosition = count($positions) - 1;
        }
        $departments = Person::getAllDepartments();
        $organizations = $uniNames;
        sort($organizations);
        if($person->isRole(HQP) && ($person->isRoleAtMost(HQP) || $person->isRole(PL))){
            $titleCombo = new SelectBox('title', "Title", $myPosition, $positions);
        }
        else{
            $titleCombo = new ComboBox('title', "Title", $myPosition, $positions);
        }
        $orgCombo = new ComboBox('university', "Institution", $university['university'], $organizations);
        $deptCombo = new ComboBox('department', "Department", $university['department'], $departments);
        $titleCombo->attr('style', 'max-width: 250px;');
        $orgCombo->attr('style', 'max-width: 250px;');
        $deptCombo->attr('style', 'max-width: 250px;');
        $this->html .= "<tr>
                            <td align='right'><b>Title:</b></td>
                            <td>{$titleCombo->render()}</td>
                        </tr>";
        if($me->isRoleAtLeast(STAFF)){
            $this->html .= "<tr>
                                <td></td>
                                <td><table>";
            $titles = array("", "Chair", "Vice-Chair", "Member", "Non-Voting");
            foreach($roles as $role){
                $roleTitleCombo = new ComboBox("role_title[{$role->getId()}]", "Title", $role->getTitle(), $titles);
                $this->html .= "<tr>
                                    <td align='right'><b>{$role->getRole()}:</b></td>
                                    <td>{$roleTitleCombo->render()}</td>
                                </tr>";
            }
            $this->html .= "</table></td></tr>";
        }
        $this->html .= "<tr>
                            <td align='right'><b>Institution:</b></td>
                            <td>{$orgCombo->render()}</td>
                        </tr>
                        <tr>
                            <td align='right'><b>Department:</b></td>
                            <td>{$deptCombo->render()}</td>
                        </tr>";
        $this->html .= "</table>";
    }
    
}
?>
