<?php

class HQPExitTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function HQPExitTab($person, $visibility){
        parent::AbstractEditableTab("HQP Alumni");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        $this->generateInactiveHQPHTML($this->person, $this->visibility['edit']);
        return $this->html;
    }
    
    function handleEdit(){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $wgMessage;
        $me = Person::newFromId($wgUser->getId());
        DBFunctions::delete('grand_acknowledgements',
                            array('user_id' => $this->person->getId()));
        if(isset($_POST['acknowledged'])){
            DBFunctions::insert('grand_acknowledgements',
                                array('user_id' => $this->person->getId()));
        }
        DBFunctions::commit();
        if(isset($_POST['reason'])){
            $studies = $_POST['studies'];
            $employer = $_POST['employer'];
            $city = $_POST['city'];
            $country = $_POST['country'];
            $employment_type = $_POST['employment_type'];
            $thesis = @$_POST['thesis'];
            $effective_date = $_POST['effective_date'];
            foreach($_POST['reason'] as $key => $reason){
                if(($key == "new" && !isset($_POST['doNew'])) || 
                    isset($_POST['delete']['new'])){
                    continue;
                }
                $_POST['id'] = $key;
                $_POST['user'] = $this->person->getName();
                $_POST['studies'] = @str_replace("'", "&#39;", $studies[$key]);
                $_POST['employer'] = @str_replace("'", "&#39;", $employer[$key]);
                $_POST['city'] = @str_replace("'", "&#39;", $city[$key]);
                $_POST['country'] = @str_replace("'", "&#39;", $country[$key]);
                $_POST['employment_type'] = @str_replace("'", "&#39;", $employment_type[$key]);
                $_POST['effective_date'] = @str_replace("'", "&#39;", $effective_date[$key]);
                APIRequest::doAction('AddHQPMovedOn', true);
                if($reason == "graduated"){
                    $_POST['thesis'] = $thesis[$key];
                    APIRequest::doAction('AddHQPThesis', true);
                }
                else{
                    $_POST['thesis'] = "No Thesis";
                    APIRequest::doAction('AddHQPThesis', true);
                }
            }
            if(isset($_POST['delete'])){
                foreach($_POST['delete'] as $key => $id){
                    if(is_numeric($id)){
                        DBFunctions::delete('grand_movedOn',
                                            array('id' => EQ($id)));
                        DBFunctions::delete('grand_theses',
                                            array('moved_on' => EQ($id)));
                    }
                }
            }
            $wgMessage->addSuccess("The 'HQP Alumni' information for {$this->person->getNameForForms()} has been updated");
        }
        if($this->visibility['isSupervisor']){
            Notification::addNotification($me, $this->person, "Profile Change", "Your profile has been edited by {$me->getNameForForms()}.", "{$this->person->getUrl()}");
            foreach($this->person->getSupervisors() as $supervisor){
                if($me->getName() != $supervisor->getName()){
                    Notification::addNotification($me, $this->person, "Profile Change", "{$this->person->getNameForForms()}'s profile has been edited by {$me->getReversedName()}.", "{$this->person->getUrl()}");
                }
            }
        }
        header("Location: {$this->person->getUrl()}?tab=hqp-moved-on");
        exit;
    }
    
    function generateEditBody(){
        $this->generateInactiveHQPHTML($this->person, ($this->canEdit() && isset($_POST['edit'])));
        return $this->html;
    }
    
    function canEdit(){
        $me = Person::newFromWgUser();
        return $me->isAllowedToEdit($this->person);
    }
    
    function addEditHTML($id, $row, $hidden=false){
        $person = $this->person;
        $theses = $person->getPapers("all", false, "grand", true, "Public");
        $thesisHTML = "";
        foreach($theses as $thesis){
            $title = trim($thesis->getTitle());
            if(strlen($thesis->getTitle()) > 50){
                $title = substr($title, 0, 50)."...";
            }
            $title = str_replace("'", "&#39;", $title);
            $thesisHTML .= "<option value='{$thesis->getId()}'>{$title}</option>\n";
        }
        $display = "";
        if($hidden){
            $display = "display:none;";
        }
        $thesisDisplay = "";
        if($row['reason'] != "graduated"){
            $thesisDisplay = "display:none;";
        }
        $graduatedChecked = "";
        $movedOnChecked = "";
        if($row['reason'] == "graduated"){
            $graduatedChecked = "checked='checked'";
        }
        else {
            $movedOnChecked = "checked='checked'";
        }
        $employmentSelect = new SelectBox("employment_type[{$id}]", "Employment Type", $row['employment_type'], 
                                          array("",
                                                "University",
                                                "Industry", 
                                                "Government",
                                                "Hospital",
                                                "Other",
                                                "Unemployed"));
        $html = <<<EOF
            <div id='movedOn_{$id}' style="$display">
                <fieldset>
                    <legend><b>Date:</b> <input type='text' class='datepicker' name='effective_date[{$id}]' value='{$row['effective_date']}' /></legend>
                    <table>
                    <tr id='step1'>
                        <td colspan='2'><input type='radio' class='reason' name='reason[{$id}]' value='graduated' $graduatedChecked /> Graduated</td>
                    </tr>
                    <tr>
                        <td colspan='2'><input type='radio' class='reason' name='reason[{$id}]' value='movedOn' $movedOnChecked /> Moved On</td>
                    </tr>
                    <tbody id='step2'>
                        <tr id='thesis_{$id}' style="$thesisDisplay">
                            <td align='right'><b>Thesis:</b></td>
                            <td>
                                <select name='thesis[{$id}]'>
                                    $thesisHTML
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td align='right'><b>Further Studies at:</b></td>
                            <td><input id='studies' type='text' name='studies[{$id}]' value='{$row['studies']}' /></td>
                        </tr>
                        <tr>
                            <td align='right'><b>Employment Type:</b></td>
                            <td>
                                {$employmentSelect->render()}  
                            </td>
                        </tr>
                        <tr>
                            <td align='right'><b>Employed By:</b></td>
                            <td><input id='employer' type='text' name='employer[{$id}]' value='{$row['employer']}' /></td>
                        </tr>
                        <tr>
                            <td align='right'><b>City:</b></td>
                            <td><input id='city' type='text' name='city[{$id}]' value='{$row['city']}' /></td>
                        </tr>
                        <tr>
                            <td align='right'><b>Country:</b></td>
                            <td><input id='country' type='text' name='country[{$id}]' value='{$row['country']}' /></td>
                        </tr>
                        <tr>
                            <td align='right'><b>Delete?</b></td><td><input type='checkbox' value='{$id}' name='delete[{$id}]' onChange="deleteMovedOn('{$id}')" /></td>
                        </tr>
                    </tbody>
                    </table>
                </fieldset>
            </div>
            <script type='text/javascript'>
                var container = $('#movedOn_$id');
                $('.datepicker', container).datepicker({dateFormat: 'yy-mm-dd',
                                                        changeMonth: true,
                                                        changeYear: true,
                                                        showOn: 'both',
                                                        buttonImage: '../skins/calendar.gif',
                                                        buttonText: 'Date',
                                                        buttonImageOnly: true
                                                       });
                $('.datepicker', container).keydown(function(){
                    return false;
                });
                
                $('#employer', container).autocomplete({
                    source: partners
                });
                $('#country', container).autocomplete({
                    source: countries
                });
                $('#studies', container).autocomplete({
                    source: universities
                });
                
                $('.reason', container).change(function(){
                    var val = $('.reason:checked', $('#movedOn_$id')).val();
                    if(val == 'graduated'){
                        $('#thesis_{$id}', $('#movedOn_$id')).show();
                    }
                    else{
                        $('#thesis_{$id}', $('#movedOn_$id')).hide();
                    }
                });
            </script>
EOF;
        return $html;
    }
    
    function generateInactiveHQPHTML($person, $edit){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $wgOut, $config;
        $user = Person::newFromId($wgUser->getId());
        $person = Person::newFromName(str_replace(" ", ".", $person->getName()));
        $boxes = "";
        if($person->isRoleDuring(HQP, '0000-00-00 00:00:00', '2030-00-00 00:00:00')){
            $movedOn = $person->getAllMovedOn();
            if($edit){
                $wgOut->addScript("<script type='text/javascript'>
                    var theses = Array();\n");
                $theses = $person->getPapers("all", false, "grand", true, "Public");
                foreach($theses as $thesis){
                    $title = trim($thesis->getTitle());
                    if(strlen($thesis->getTitle()) > 50){
                        $title = substr($title, 0, 50)."...";
                    }
                    $wgOut->addScript("theses[{$thesis->getId()}] = '".str_replace("\\", "", str_replace("'", "&#39;", $title))."';\n");
                }
                $partners = array();
                foreach(Partner::getAllPartners() as $partner){
                    $partners[] = $partner->getOrganization();
                }
                $universities = array();
                foreach(Person::getAllUniversities() as $uni){
                    $universities[] = $uni;
                }
                $wgOut->addScript("
                    var partners = [\"".implode("\",\n\"", $partners)."\"];
                    var universities = [\"".implode("\",\n\"", $universities)."\"];
                    
                    function showNewMovedOn(){
                        $('#movedOn_new').show();
                        $('#movedOn_new').append('<input type=hidden name=doNew value=new />');
                        $('#addMovedOn').hide();
                    }
                    
                    function deleteMovedOn(id){
                        if($('#movedOn_' + id).hasClass('deleted')){
                            $('#movedOn_' + id).removeClass('deleted');
                            $('#movedOn_' + id).css('background', '#FFFFFF');
                        }
                        else{
                            $('#movedOn_' + id).addClass('deleted');
                            $('#movedOn_' + id).css('background', '#DDDDDD');
                        }
                    }
                    </script>
                ");
                
                foreach($movedOn as $key => $row){
                    $this->html .= $this->addEditHTML($key, $row);
                }
                $this->html .= $this->addEditHTML("new", array("effective_date" => date('Y-m-d'), 
                                                               "studies" => "", 
                                                               "employer" => "", 
                                                               "city" => "", 
                                                               "country" => "",
                                                               "employment_type" => "",
                                                               "thesis" => null,
                                                               "reason" => "graduated"), true);
                if($config->getValue('networkName') == 'FES'){
                    $acks = DBFunctions::select(array('grand_acknowledgements'),
                                                array('*'),
                                                array('user_id' => EQ($this->person->getId())));
                    $checked = (count($acks) > 0) ? "checked='checked'" : "";
                    $this->html .= "<br /><input type='checkbox' value='Yes' name='acknowledged' $checked /> Check this box to confirm that the HQP has consented to share their employment information with FES";
                }
                $this->html .= "<br /><input id='addMovedOn' type='button' onClick='showNewMovedOn();' value='Add \"Alumni\" Info' />";
            }
            else{
                if(count($movedOn) > 0){
                    foreach($movedOn as $key => $row){
                        if($row['reason'] == "graduated"){
                            $type = "Graduated";
                        }
                        else{
                            $type = "Moved On";
                        }
                        $this->html .= "<h3>{$type} {$row['effective_date']}</h3>";
                        $this->html .= "<table style='margin-left:30px;'>";
                        if($row['thesis'] != null){
                            $this->html .= "<tr><td align='right'><b>Thesis:</b></td><td><a href='{$row['thesis']->getUrl()}'>{$row['thesis']->getTitle()}</a></td></tr>";
                        }
                        if($row['studies'] != "") $this->html .= "<tr><td align='right'><b>Further Studies at:</b></td><td>{$row['studies']}</td></tr>";
                        if($row['employment_type'] != "") $this->html .= "<tr><td align='right'><b>Employment Type:</b></td><td>{$row['employment_type']}</td></tr>";
                        if($row['employer'] != "") $this->html .= "<tr><td align='right'><b>Employed By:</b></td><td>{$row['employer']}</td></tr>";
                        if($row['city'] != "") $this->html .= "<tr><td align='right'><b>City:</b></td><td>{$row['city']}</td></tr>";
                        if($row['country'] != "") $this->html .= "<tr><td align='right'><b>Country:</b></td><td>{$row['country']}</td></tr>";
                        $this->html .="</table>";
                    }
                }
                else{
                    $this->html .= "{$person->getNameForForms()} does not have any alumni information yet.";
                }
            }
        }
    }
}
?>
