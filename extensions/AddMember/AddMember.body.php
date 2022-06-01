<?php
require_once("AddMember.php");

$wgHooks['AddNewAccount'][] = 'UserCreate::afterCreateUser';

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
    
    function afterCreateUser($wgUser, $byEmail=true){
        global $wgLocalTZoffset, $wgOut;
        $mUserType = $_POST['wpUserType'];
        $id = $wgUser->getId();
        
        DBFunctions::commit();
        DBFunctions::begin();
        
        $roleId = null;
        // Add Roles
        if(isset($_POST['wpUserType']) && $_POST['wpUserType'] != ""){
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
                                          'start_date' => @$_POST['start_date'],
                                          'end_date' => @$_POST['end_date']));
                $roleId = DBFunctions::insertId();
                // Add Projects
                if(isset($_POST['wpNS'])){
                    $box = $_POST['wpNS'];
                    while (list ($key,$val) = @each ($box)) {
                        if($val != null && $val != ""){
                            $project = Project::newFromName($val);
                            DBFunctions::insert('mw_user_groups',
                                                array('ug_user' => $id,
                                                      'ug_group' => $val));
                            DBFunctions::insert('grand_role_projects',
                                                array('role_id' => $roleId,
                                                      'project_id' => $project->getId()));
                            Cache::delete("project{$project->getId()}_people");
                            Cache::delete("project{$project->getId()}_peopleDuring", true);
                        }
                    }
                }
            }
        }
        // Add Roles
        if(isset($_POST['wpUserSubType']) && $_POST['wpUserSubType'] != ""){
            foreach($_POST['wpUserSubType'] as $subrole){
                DBFunctions::insert('grand_role_subtype',
                                    array('user_id' => $id,
                                          'sub_role' => $subrole));
            }
        }
        Cache::delete("rolesCache");
        
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
        Cache::delete("nameCache_{$wgUser->getId()}");
        Cache::delete("idsCache_{$wgUser->getId()}");
        $person = Person::newFromId($wgUser->getId());
        MailingList::subscribeAll($person);
        return true;
    }
    
    static function addNewUserPage($wgUser){
        //Do Nothing
        return true;
    }   
}

?>
