<?php

class HQPExitTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function HQPExitTab($person, $visibility){
        parent::AbstractEditableTab("HQP Exit");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        $this->generateInactiveHQPHTML($this->person, $this->visibility['edit']);
        return $this->html;
    }
    
    function handleEdit(){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath;
        $me = Person::newFromId($wgUser->getId());
        if(isset($_POST['where']) || 
           isset($_POST['studies']) || 
           isset($_POST['employer']) || 
           isset($_POST['city']) || 
           isset($_POST['country'])){
            $_POST['user'] = $this->person->getName();
            $_POST['where'] = @str_replace("'", "&#39;", $_POST['where']);
            $_POST['studies'] = @str_replace("'", "&#39;", $_POST['studies']);
            $_POST['employer'] = @str_replace("'", "&#39;", $_POST['employer']);
            $_POST['city'] = @str_replace("'", "&#39;", $_POST['city']);
            $_POST['country'] = @str_replace("'", "&#39;", $_POST['country']);
            APIRequest::doAction('AddHQPMovedOn', true);
            $wgOut->addHTML("{$_POST['user']}'s movedOn added<br />\n");
        }
        if(isset($_POST['thesis'])){
            $_POST['user'] = $this->person->getName();
            APIRequest::doAction('AddHQPThesis', true);
            $wgOut->addHTML("{$_POST['user']}'s thesis added</br />\n");
        }
        if($this->visibility['isSupervisor']){
            Notification::addNotification($me, $this->person, "Profile Change", "Your profile has been edited by {$me->getName()}.", "{$this->person->getUrl()}");
            foreach($this->person->getSupervisors() as $supervisor){
                if($me->getName() != $supervisor->getName()){
                    Notification::addNotification($me, $this->person, "Profile Change", "{$this->person->getNameForForms()}'s profile has been edited by {$me->getReversedName()}.", "{$this->person->getUrl()}");
                }
            }
        }
    }
    
    function generateEditBody(){
        $this->generateInactiveHQPHTML($this->person, ($this->canEdit() && isset($_GET['edit'])));
        return $this->html;
    }
    
    function canEdit(){
        global $wgUser;
        $me = Person::newFromId($wgUser->getId());
        $supervisors = $this->person->getSupervisors(true);
        $found = false;
        foreach($supervisors as $supervisor){
            if($supervisor->getId() == $me->getId()){
                $found = true;
                break;
            }
        }
        return ($found || $me->getId() == $this->person->getId() || $me->isRoleAtLeast(STAFF));
    }
    
    function generateInactiveHQPHTML($person, $edit){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $wgOut;
        $user = Person::newFromId($wgUser->getId());
        $person = Person::newFromName(str_replace(" ", ".", $person->getName()));
        $boxes = "";
        if($person->isRoleDuring(HQP, '0000-00-00 00:00:00', '2030-00-00 00:00:00')){
            $wgOut->addScript("<script type='text/javascript'>
                var theses = Array();\n");
            $theses = Paper::getAllPapersForThesis($person);
            foreach($theses as $thesis){
                $title = $thesis->getTitle();
                if(strlen($thesis->getTitle()) > 50){
                    $title = substr($title, 0, 50)."...";
                }
                $wgOut->addScript("theses[{$thesis->getId()}] = '".str_replace("'", "&#39;", $title)."';\n");
            }
            $partners = array();
            foreach(Partner::getAllPartners() as $partner){
                $partners[] = $partner->getOrganization();
            }
            $universities = array();
            foreach(Person::getAllUniversities() as $uni){
                $universities[] = $uni;
            }
            $movedOn = $person->getMovedOn();
            $thesis = $person->getThesis();
            $tId = ($thesis != null) ? $thesis->getId() : 0;
            
            $roleHistory = $person->getRoles(true);
            $lastHQPRole = null;
            foreach($roleHistory as $role){
                if($role->getRole() == HQP){
                    if($lastHQPRole == null){
                        $lastHQPRole = $role;
                    }
                    else if($role->getEndDate() >= $lastHQPRole->getEndDate()){
                        $lastHQPRole = $role;
                    }
                }
            }
            
            $wgOut->addScript("
                var partners = [\"".implode("\",\n\"", $partners)."\"];
                var universities = [\"".implode("\",\n\"", $universities)."\"];
                
                function updateStep2(){
                    var reason = $('input[name=reason]:checked').attr('value');
                    if(reason == 'graduated'){
                        var options = '<option value=\"No Thesis\">No Thesis</option>';
                        for(index in theses){
                            if(index != 'indexOf'){
                                if(index == $tId){
                                    options += '<option value=\"' + index + '\" selected=\"selected\">' + theses[index] + '</option>';
                                }
                                else{
                                    options += '<option value=\"' + index + '\">' + theses[index] + '</option>';
                                }
                            }
                        }
                        var text = \"<tr><td valign='top' align='right'>Thesis:</td><td><select name='thesis'>\" + options + \"</select><br /><small>If The thesis is not in the list, then you can <a target='_blank' href='$wgServer$wgScriptPath/index.php/Special:AddPublicationPage'>add it</a> and then <a href='javascript:history.go(0);'>reload</a> this page.</small></td></tr>\" +
                                   \"<tr><td align='right'>Further&nbsp;Studies&nbsp;at:</td><td><input type='text' id='studies' size='25' name='studies' value='".str_replace("\"", "&quot;", $movedOn['studies'])."' /></td></tr>\" +
                                   \"<tr><td align='right'>Employed&nbsp;by:</td><td><input type='text' id='employer' name='employer' size='25' value='".str_replace("\"", "", $movedOn['employer'])."' /></td></tr>\" +
                                   \"<tr><td align='right' valign='top'>Location:</td><td></tr><tr><td align='right'>City:</td><td><input type='text' id='city' name='city' size='25' value='".str_replace("\"", "&quot;", $movedOn['city'])."' /></td></tr><td align='right'>Country:</td><td><input type='text' id='country' name='country' size='25' value='".str_replace("\"", "&quot;", $movedOn['country'])."' /></td></tr>\";
                        $('#step2').html(text);
                        $('#employer').autocomplete({
                            source: partners
                        });
                        $('#country').autocomplete({
                            source: countries
                        });
                        $('#studies').autocomplete({
                            source: universities
                        });
                    }
                    else if(reason == 'movedOn'){
                        var text = \"<tr><td align='right'>Further&nbsp;Studies&nbsp;at:</td><td><input type='text' id='studies' size='25' name='studies' value='".str_replace("\"", "&quot;", $movedOn['studies'])."' /></td></tr>\" +
                                   \"<tr><td align='right'>Employed&nbsp;by:</td><td><input type='text' id='employer' name='employer' size='25' value='".str_replace("\"", "&quot;", $movedOn['employer'])."' /></td></tr>\" +
                                   \"<tr><td align='right' valign='top'>Location:</td><td></tr><tr><td align='right'>City:</td><td><input type='text' id='city' name='city' size='25' value='".str_replace("\"", "&quot;", $movedOn['city'])."' /></td></tr><td align='right'>Country:</td><td><input type='text' id='country' name='country' size='25' value='".str_replace("\"", "&quot;", $movedOn['country'])."' /></td></tr>\";
                        $('#step2').html(text);
                        $('#employer').autocomplete({
                            source: partners
                        });
                        $('#country').autocomplete({
                            source: countries
                        });
                        $('#studies').autocomplete({
                            source: universities
                        });
                    }
                    $('#step3').show();
                }
                
                $(document).ready(function(){
                    updateStep2();
                });  
                                         
            </script>");
            if($tId != 0){
                $checkedGraduated = " checked='checked'";
                $checkedMovedOn = "";
            }
            else{
                $checkedGraduated = "";
                $checkedMovedOn = " checked='checked'";
            }
            if($edit){
                $this->html .= "<div style='padding-left:30px;'>
                                <fieldset><legend>Reason for ".HQP." Inactivation</legend>
                                <table>
                                <tr>
                                    <td colspan='2'>Date&nbsp;Effective:</td><td></td>
                                </tr>
                                <tr id='step1'>
                                    <td colspan='2'><input type='radio' name='reason' value='graduated' onChange='updateStep2()'$checkedGraduated /> Graduated</td>
                                </tr>
                                <tr>
                                    <td colspan='2'><input type='radio' name='reason' value='movedOn' onChange='updateStep2()'$checkedMovedOn /> Moved On</td>
                                </tr>
                                <tbody id='step2'>
                                    
                                </tbody>
                                </table>
                                </fieldset>
                            </div><br />";
            }
            else{
                $this->html .= "<table style='margin-left:30px;'>";
                if($lastHQPRole != null){
                    $this->html .= "<tr><td align='right'>Effective Date:</td><td>{$lastHQPRole->getEndDate()}</td></tr>";
                }
                if($thesis != null){
                    $this->html .= "<tr><td align='right'>Thesis:</td><td><a href='{$thesis->getUrl()}'>{$thesis->getTitle()}</a></td></tr>";
                }
                if(count($movedOn) > 0){
                    $this->html .="<tr><td align='right'>Further Studies at:</td><td>{$movedOn['studies']}</td></tr>";
                    $this->html .="<tr><td align='right'>Employed By:</td><td>{$movedOn['employer']}</td></tr>";
                    $this->html .="<tr><td align='right'>City:</td><td>{$movedOn['city']}</td></tr>";
                    $this->html .="<tr><td align='right'>Country:</td><td>{$movedOn['country']}</td></tr>";
                }
                $this->html .="</table>";
            }
        }
    }
}
?>
