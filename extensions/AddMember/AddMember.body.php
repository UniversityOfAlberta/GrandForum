<?php
require_once("AddMember.php");

$notificationFunctions[] = 'UserCreate::createNotification';

class UserCreate {

    static function createNotification(){
        global $notifications, $wgUser, $wgServer, $wgScriptPath;
        $groups = $wgUser->getGroups();
        if($wgUser->isLoggedIn()){
            $me = Person::newFromId($wgUser->getId());
            if($me->isRoleAtLeast(STAFF)){
                $data = DBFunctions::select(array('grand_user_request'),
                                            array('requesting_user', 'wpName'),
                                            array('created' => EQ(0),
                                                  '`ignore`' => EQ(0)));
                if(count($data) > 0){
                    $notifications[] = new Notification("User Creation Request", "There is at least one user creation request pending.", "$wgServer$wgScriptPath/index.php/Special:AddMember?action=view");
                }
            }
        }
    }
    
    static function afterCreateUser($wgUser){
        global $wgLocalTZoffset, $wgOut;
        $mUserType = $_POST['wpUserType'];
        $id = $wgUser->getId();
        
        DBFunctions::commit();
        DBFunctions::begin();
        
        if(isset($_POST['wpUserType'])){
            if($_POST['wpUserType'] != ""){
                foreach($_POST['wpUserType'] as $role){
                    if($role == ""){
                        continue;
                    }
                    //Add Role to DB
                    DBFunctions::insert('mw_user_groups',
                                        array('ug_user' => $id,
                                              'ug_group' => $role));
                    DBFunctions::insert('grand_roles',
                                        array('user_id' => $id,
                                              'role' => $role,
                                              'start_date' => EQ(COL('CURRENT_TIMESTAMP'))));
                }
            }
        }
        
        if(isset($_POST['wpNS'])){
            $box = $_POST['wpNS'];
            while (list ($key,$val) = @each ($box)) {
                if($val != null && $val != ""){
                    $project = Project::newFromName($val);
                    DBFunctions::insert('mw_user_groups',
                                        array('ug_user' => $id,
                                              'ug_group' => $val));
                    DBFunctions::insert('grand_project_members',
                                        array('user_id' => $id,
                                              'project_id' => $project->getId(),
                                              'start_date' => EQ(COL('CURRENT_TIMESTAMP'))));
                }
            }
        }
        
        $_POST['candidate'] = isset($_POST['candidate']) ? $_POST['candidate'] : "0";
        DBFunctions::update('mw_user',
	                        array('candidate' => $_POST['candidate']),
	                        array('user_id' => EQ($wgUser->getId())));
        
        UserCreate::addNewUserPage($wgUser);
        DBFunctions::commit();
        Person::$cache = array();
        Person::$idsCache = array();
        Person::$namesCache = array();
        Person::$rolesCache = array();
        $person = Person::newFromId($wgUser->getId());
        return true;
    }
    
    static function addNewUserPage($wgUser){
        //Do Nothing
        return true;
    }   
}

?>
