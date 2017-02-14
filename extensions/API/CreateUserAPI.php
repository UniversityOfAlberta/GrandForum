<?php

class CreateUserAPI extends API{

    function CreateUserAPI(){
        $this->addPOST("wpName",true,"The User Name of the user to add","UserName");
        $this->addPOST("wpPassword",false,"The Password of the user to add","Password");
        $this->addPOST("wpEmail",false,"The User's email address","me@email.com");
        $this->addPOST("wpRealName",false,"The User's real name","My Real Name");
        $this->addPOST("wpUserType",true,"The User Roles, must be in the form \"Role1, Role2, ...\"","HQP, RMC");
        $this->addPOST("wpNS",false,"The list of projects that the user is a part of.  Must be in the form \"Project1, Project2, ...\"","MEOW, NAVEL");
        $this->addPOST("wpSendMail",false,"Whether or not to send an email to the user or not.  This value should be either 'true' or 'false'.  If this parameter is not included, it is assumed that not email should be sent","true");
        $this->addPOST("candidate",false,"Whether or not to make this user a candidate", "1");
        $this->addPOST("id",false,"The id of the creation request(You probably should not touch this parameter unless you know exactly what you are doing)", "15");
    }

    function processParams($params){
        // DO NOTHING
    }

    function doAction($doEcho=true){
        global $wgRequest, $wgUser, $wgServer, $wgScriptPath, $wgOut, $wgMessage, $wgEnableEmail, $wgEmailAuthentication, $wgEnableUserEmail;
        $me = Person::newFromId($wgUser->getId());
        $oldWPNS = "";
        $oldWPType = "";
        if(isset($_POST['wpNS'])){
            $oldWPNS = $_POST['wpNS'];
            $nss = explode(", ", $_POST['wpNS']);
            unset($_POST['wpNS']);
            foreach($nss as $ns){
                $_POST['wpNS'][] = $ns;
            }
        }
        $oldWPType = $_POST['wpUserType'];
        $roles = explode(", ", $_POST['wpUserType']);
        unset($_POST['wpUserType']);
        foreach($roles as $role){
            $_POST['wpUserType'][] = $role;
        }
        // Finished manditory checks
        $_POST['candidate'] = isset($_POST['candidate']) ? $_POST['candidate'] : "0";
        if($me->isRoleAtLeast(STAFF) || $_POST['candidate'] == "1"){
            // First check to see if the user already exists
            $person = Person::newFromName($_POST['wpName']);
            if($person != null && $person->getName() != ""){
                if($doEcho){
                    echo "This user already exists.\n";
                }
                else{
                    $message = "This user already exists.";
                    $wgMessage->addWarning($message);
                    return $message;
                }
            }
            $wgRequest->setVal('wpName', $_POST['wpName']);
            // Actually create a new user
            $oldWgEnableEmail = $wgEnableEmail;
            $wgEnableEmail = true;
            if(isset($_POST['wpSendMail']) && $wgEnableEmail){
                if($_POST['wpSendMail'] === "true"){
                    $wgRequest->setVal('wpEmail', $_POST['wpEmail']);
                    $wgRequest->setVal('wpCreateaccountMail', true);
                }
                else {
                    $wgEmailAuthentication = false;
                    $wgEnableUserEmail = false;
                    $wgEnableEmail = false;
                    $wgRequest->setVal('wpCreateaccount', true);
                    $_POST['wpPassword'] = User::randomPassword();
                    $_POST['wpRetype'] = $_POST['wpPassword'];
                }
            }
            else{
                $wgRequest->setVal('wpEmail', $_POST['wpEmail']);
                $wgRequest->setVal('wpCreateaccount', true);
                $_POST['wpPassword'] = User::randomPassword();
                $_POST['wpRetype'] = $_POST['wpPassword'];
            }
            $wgRequest->setSessionData('wsCreateaccountToken', 'true');
            $wgRequest->setVal('wpCreateaccountToken', 'true');
            $wgRequest->setVal('type', 'signup');
            if(isset($_POST['wpPassword'])){
                $wgRequest->setVal('wpRetype', $_POST['wpPassword']);
                $wgRequest->setVal('wpPassword', $_POST['wpPassword']);
            }
            $creator = self::getCreator($me);
            LoginForm::setCreateaccountToken();
            $wgRequest->setSessionData('wpCreateaccountToken', LoginForm::getCreateaccountToken());
            $wgRequest->setVal('wpCreateaccountToken', LoginForm::getCreateaccountToken());
            $specialUserLogin = new LoginForm($wgRequest);
            
            $specialUserLogin->getUser()->mRights = null;
            $specialUserLogin->getUser()->mEffectiveGroups = null;
            GrandAccess::$alreadyDone = array();
            $tmpUser = User::newFromName($_POST['wpName']);
            if($tmpUser->getID() == 0 && ($specialUserLogin->execute('signup') != false || $_POST['wpSendMail'] == true)){
                $wgEnableEmail = $oldWgEnableEmail;
                $__POST['user_name'] = 
                
                Person::$cache = array();
                Person::$namesCache = array();
                Person::$aliasCache = array();
                Person::$idsCache = array();
                $person = Person::newFromName($_POST['wpName']);
                if($person != null && $person->getName() != null){
                    $_POST['user_name'] = $person->getName();
                    $_POST['email'] = $_POST['wpEmail'];
                    
                    $api = new UserEmailAPI();
                    $api->doAction(true);
                
                    if(isset($_POST['university']) && isset($_POST['department']) && isset($_POST['position'])){
                        $_POST['title'] = $_POST['position'];
                        $api = new UserUniversityAPI();
                        $api->doAction(true);
                    }
                    else{
                        $defaultUni = Person::getDefaultUniversity();
                        $unis = array_flip(Person::getAllUniversities());
                        $defaultPos = Person::getDefaultPosition();
                        $poss = array_flip(Person::getAllPositions());
                        DBFunctions::insert('grand_user_university',
                                            array('user_id' => $person->getId(),
                                                  'university_id' => $unis[$defaultUni],
                                                  'position_id' => $poss[$defaultPos]));
                    }
                    if(isset($_POST['subtype']) && is_array($_POST['subtype'])){
                        // Adds the role subtype if it is set
                        foreach($_POST['subtype'] as $subtype){
                            DBFunctions::insert('grand_role_subtype',
                                                array('user_id' => $person->id,
                                                      'sub_role' => $subtype));
                        }
                    }
                    Notification::addNotification("", $creator, "User Created", "A new user has been added to the forum: {$person->getReversedName()}", "{$person->getUrl()}");
                    $data = DBFunctions::select(array('grand_notifications'),
                                                array('id'),
                                                array('user_id' => EQ($creator->getId()),
                                                      'message' => LIKE("%{$person->getName()}%"),
                                                      'url' => EQ(''),
                                                      'creator' => EQ(''),
                                                      'active' => EQ(1)));
                    if(count($data) > 0){
                        // Remove the Notification that the user was sent after the request
                        Notification::deactivateNotification($data[0]['id']);
                    }
                }
                DBFunctions::commit();
                if($doEcho){
                    echo "User created successfully.\n";
                }
                else{
                    $message = "User created successfully.";
                    $wgMessage->addSuccess($message);
                    return $message;
                }
            }
            else{
                $wgEnableEmail = $oldWgEnableEmail;
                if($doEcho){
                    echo "User not created successfully.\n";
                }
                else{
                    $message = "User not created successfully.";
                    $wgMessage->addError($message);
                    return $message;
                }
            }
        }
        else {
            if($doEcho){
                echo "You must be staff to use this API\n";
            }
            else{
                $message = "You must be staff to use this API";
                $wgMessage->addError($message);
                return $message;
            }
        }
    }
    
    // Returns the creator of the role request.  
    // If the creator cannot be determined, then 'me' is returned
    function getCreator($me){
        if(isset($_POST['id'])){
            $data = DBFunctions::select(array('grand_user_request'),
                                        array('requesting_user'),
                                        array('id' => EQ($_POST['id'])));
            if(count($data) > 0){
                return Person::newFromId($data[0]['requesting_user']);
            }
        }   
        return $me;
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
