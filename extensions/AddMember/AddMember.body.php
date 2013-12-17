<?php
require_once("AddMember.php");

$userCreate = new UserCreate();

$wgHooks['AddNewAccount'][] = array($userCreate, 'afterCreateUser');

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
        
        $person = Person::newFromId($wgUser->getId());
        $person->email = $wgUser->mEmail;
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
                    if($role == PNI || 
                       $role == CNI || 
                       $role == AR){
                        MailingList::subscribe("grand-forum-researchers", $person);
                    }
                    else if($role == HQP){
                        MailingList::subscribe("grand-forum-hqps", $person);
                    }
                    else if($role == RMC){
                        MailingList::subscribe("rmc-list", $person);
                    }
                    else if($role == ISAC){
                        MailingList::subscribe("isac-list", $person);
                    }
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
        
        $continue = UserCreate::addNewUserPage($wgUser);
        
        // Add User MailingList
        $user = User::newFromId($wgUser->getId());
        $email = $wgUser->mEmail;
        if($email != null){
            foreach($user->getGroups() as $group){
                $project = Project::newFromId($group);
                if($project != null && !$project->isSubProject()){
                    MailingList::subscribe($project, $person);
                }
            }
        }
        DBFunctions::commit();
        return true;
    }
    
    static function addNewUserPage($wgUser){
        //Do Nothing
        return true;
    }   
}

?>
