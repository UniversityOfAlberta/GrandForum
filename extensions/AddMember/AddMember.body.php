<?php
if(isExtensionEnabled("AddMember")){
     require_once("AddMember.php");
}

$wgHooks['AddNewAccount'][] = 'UserCreate::afterCreateUser';

class UserCreate {
    
    function afterCreateUser($wgUser, $byEmail=true){
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
                    DBFunctions::insert('grand_roles',
                                        array('user_id' => $id,
                                              'role' => $role,
                                              'start_date' => EQ(COL('CURRENT_TIMESTAMP'))));
                }
            }
        }
        Cache::delete("rolesCache");
        
        $_POST['candidate'] = isset($_POST['candidate']) ? $_POST['candidate'] : "0";
        DBFunctions::update('mw_user',
	                        array('candidate' => $_POST['candidate']),
	                        array('user_id' => EQ($wgUser->getId())));
	    Cache::delete("allPeopleCache");
        Cache::delete("mw_user_{$wgUser->getId()}");
        UserCreate::addNewUserPage($wgUser);
        DBFunctions::commit();
        Person::$cache = array();
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
