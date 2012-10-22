<?php

class PersonProfileTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonProfileTab($person, $visibility){
        parent::AbstractEditableTab("Profile");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        $this->showProfile($this->person, $this->visibility);
        return $this->html;
    }
    
    function generateEditBody(){
        $this->showEditProfile($this->person, $this->visibility);
    }
    
    function canEdit(){
        return ($this->visibility['isMe'] || 
                $this->visibility['isSupervisor']);
    }
    
    function handleEdit(){
        $_POST['user_name'] = $this->person->getName();
        $_POST['type'] = "public";
        $_POST['profile'] = str_replace("'", "&#39;", $_POST['public_profile']);
        $_POST['profile'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['profile'])));
        APIRequest::doAction('UserProfile', true);
        $_POST['type'] = "private";
        $_POST['profile'] = @addslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", $_POST['private_profile'])));
        APIRequest::doAction('UserProfile', true);
    }
    
    /*
     * Displays the profile for this user
     */
    function showProfile($person, $visibility){
        global $wgUser;
        $this->html .= nl2br($person->getProfile($wgUser->isLoggedIn()));
    }
    
    function showEditProfile($person, $visibility){
        $this->html .= "<table>
                                <tr>
                                    <td align='right' valign='top'><b>GRAND Website:</b></td>
                                    <td><textarea style='width:600px; height:150px;' name='public_profile'>{$person->getProfile(false)}</textarea></td>
                                </tr>
                                <tr>
                                    <td align='right' valign='top'><b>GRAND Forum:</b></td>
                                    <td><textarea style='width:600px; height:150px;' name='private_profile'>{$person->getProfile(true)}</textarea></td>
                                </tr>
                            </table>";
    }
    
}
?>
