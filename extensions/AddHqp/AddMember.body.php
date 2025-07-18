<?php

$wgHooks['AddNewAccount'][] = 'UserCreate::afterCreateUser';

class UserCreate {
    
    static function afterCreateUser($wgUser, $byEmail=true){
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
                    $start_date = (isset($_POST['startDate'])) ? ZERO_DATE($_POST['startDate'], zull) : EQ(COL('CURRENT_TIMESTAMP'));
                    DBFunctions::insert('grand_roles',
                                        array('user_id' => $id,
                                              'role' => $role,
                                              'start_date' => $start_date));
                }
            }
        }
        DBCache::delete("rolesCache");
	    DBCache::delete("allPeopleCache");
        DBCache::delete("mw_user_{$wgUser->getId()}");
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
