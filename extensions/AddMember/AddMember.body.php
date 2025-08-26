<?php
use MediaWiki\MediaWikiServices;

require_once("AddMember.php");

$wgHooks['AddNewAccount'][] = 'UserCreate::afterCreateUser';
$wgHooks['SpecialPasswordResetOnSubmit'][] = 'UserCreate::afterPasswordReset';

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
    
    static function afterCreateUser($wgUser, $byEmail=true){
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
                    while (list ($key,$val) = @each($box)) {
                        if($val != null && $val != ""){
                            $split = explode(":", $val);
                            if(count($split) == 1 || $split[0] == "$role"){
                                $project = (count($split) == 1) ? Project::newFromName($split[0]) : Project::newFromName($split[1]);
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
        }
        // Add Roles
        if(isset($_POST['wpUserSubType']) && $_POST['wpUserSubType'] != ""){
            foreach($_POST['wpUserSubType'] as $subrole){
                if($subrole != ""){
                    DBFunctions::insert('grand_role_subtype',
                                        array('user_id' => $id,
                                              'sub_role' => $subrole));
                }
            }
        }
        Cache::delete("rolesCache");
        
        $_POST['candidate'] = isset($_POST['candidate']) ? $_POST['candidate'] : "0";
        DBFunctions::update('mw_user',
	                        array('candidate' => $_POST['candidate']),
	                        array('user_id' => EQ($wgUser->getId())));
	                        
	    if(isset($_POST['nationality']) && $_POST['nationality'] != ""){
	        DBFunctions::update('mw_user',
	                            array('user_nationality' => $_POST['nationality']),
	                            array('user_id' => EQ($wgUser->getId())));
	    }
	    if(isset($_POST['linkedin']) && $_POST['linkedin'] != ""){
	        DBFunctions::update('mw_user',
	                            array('user_linkedin' => $_POST['linkedin']),
	                            array('user_id' => EQ($wgUser->getId())));
	    }
        
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
    
    static function afterPasswordReset($users, $data, &$error){ 
        foreach($users as $user){
            $person = Person::newFromUser($user);
            if(!$person->isAuthenticated()){
                $passwd = PasswordFactory::generateRandomPasswordString();
                DBFunctions::update('mw_user',
                                    array('user_password' => MediaWikiServices::getInstance()->getPasswordFactory()->newFromPlaintext($passwd)->toString(),
                                          'user_email_token' => EQ(COL('NULL')),
                                          'user_email_token_expires' => EQ(COL('NULL'))),
                                    array('user_id' => EQ($user->getId())));
                DBFunctions::commit();
	        }
        }
    }
    
    static function addNewUserPage($wgUser){
        //Do Nothing
        return true;
    }   
}

?>
