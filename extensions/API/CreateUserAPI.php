<?php

use MediaWiki\MediaWikiServices;

class CreateUserAPI extends API{

    function __construct(){
        $this->addPOST("wpName",true,"The User Name of the user to add","UserName");
        $this->addPOST("wpPassword",false,"The Password of the user to add","Password");
        $this->addPOST("wpEmail",false,"The User's email address","me@email.com");
        $this->addPOST("wpRealName",false,"The User's real name","My Real Name");
        $this->addPOST("wpFirstName",false,"The User's first name","My First Name");
        $this->addPOST("wpMiddleName",false,"The User's middle name","My Middle Name");
        $this->addPOST("wpLastName",false,"The User's last name","My Last Name");
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
        global $wgRequest, $wgUser, $wgServer, $wgScriptPath, $wgOut, $wgMessage, $config;
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
        if(!$me->isLoggedIn()){
            // Check email whitelist to help prevent spam
            $splitEmail = explode("@", $_POST['wpEmail']);
            $domain = @$splitEmail[1];
            if(count($config->getValue('hqpRegisterEmailWhitelist')) > 0 && 
               !preg_match("/".str_replace('.', '\.', implode("|", $config->getValue('hqpRegisterEmailWhitelist')))."/i", $domain)){
                $message = "Email address must match one of the following: ".implode(", ", $config->getValue('hqpRegisterEmailWhitelist'));
                $wgMessage->addWarning($message);
                return $message;
            }
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
            DBFunctions::delete('mw_actor',
                                array('actor_name' => EQ($_POST['wpName'])));
            $creator = self::getCreator($me);
            GrandAccess::$alreadyDone = array();
            $passwd = PasswordFactory::generateRandomPasswordString();
            $tmpUser = User::createNew($_POST['wpName'], array('real_name' => $_POST['wpRealName'], 
                                                               'email' => $_POST['wpEmail']));
            if($tmpUser != null){
                DBFunctions::update('mw_user',
                                    array('user_newpassword' => MediaWikiServices::getInstance()->getPasswordFactory()->newFromPlaintext($passwd)->toString(),
                                          'user_newpass_time' => date('YmdHis')),
                                    array('user_id' => EQ($tmpUser->getId())));
                if(isset($_POST['wpSendMail']) && $_POST['wpSendMail'] === "true"){
                    $this->sendNewAccountEmail($tmpUser, $creator->getUser(), $passwd);
                }
                UserCreate::afterCreateUser($tmpUser);
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
                    
                    DBFunctions::update('mw_user',
                                        array('first_name' => @$_POST['wpFirstName'],
                                              'middle_name' => @$_POST['wpMiddleName'],
                                              'last_name' => @$_POST['wpLastName']),
                                        array('user_id' => $person->getId()));
                    
                    $universities = explode("\n", str_replace("\r", "", $_POST['university']));
                    $departments = explode("\n", str_replace("\r", "", $_POST['department']));
                    $positions = explode("\n", str_replace("\r", "", $_POST['position']));
                    $startDates = explode("\n", str_replace("\r", "", $_POST['start_date']));
                    $endDates = explode("\n", str_replace("\r", "", $_POST['end_date']));
                    $earliestStartDate = "";
                    $latestEndDate = "";
                    foreach($universities as $i => $university){
                        if(trim($universities[$i]) != "" || trim($departments[$i]) != "" || trim($positions[$i]) != ""){
                            $_POST['university'] = trim($universities[$i]);
                            $_POST['department'] = trim($departments[$i]);
                            $_POST['position'] = trim($positions[$i]);
                            $_POST['startDate'] = trim($startDates[$i]);
                            $_POST['endDate'] = trim($endDates[$i]);
                            
                            $api = new PersonUniversitiesAPI();
                            $api->params['id'] = $person->getId();
                            $api->doPOST();
                            
                            if($_POST['startDate'] != "" && $_POST['startDate'] != "0000-00-00"){
                                $earliestStartDate = ($earliestStartDate == "") ? $_POST['startDate'] : min($earliestStartDate, $_POST['startDate']);
                            }
                            $latestEndDate = ($_POST['endDate'] == "" || $_POST['endDate'] == "0000-00-00") ? "0000-00-00" : max($latestEndDate, $_POST['endDate']);
                        }
                    }
                    
                    // Correct role dates
                    DBFunctions::update('grand_roles',
                                        array('start_date' => $earliestStartDate,
                                              'end_date' => $latestEndDate),
                                        array('user_id' => $person->getId()));
                                        
                    if($_POST['employment'] != ""){
                        $_POST['id'] = "";
                        $_POST['user'] = $person->getName();
                        $_POST['studies'] = "";
                        $_POST['employer'] = "";
                        $_POST['city'] = "";
                        $_POST['country'] = "";
                        $_POST['employment_type'] = @str_replace("'", "&#39;", $_POST['employment']);
                        $_POST['effective_date'] = ($latestEndDate == "" || $latestEndDate == "0000-00-00") ? date('Y-m-d') : str_replace("'", "&#39;", $latestEndDate);
                        APIRequest::doAction('AddHQPMovedOn', true);
                    }
                    
                    if($_POST['recruitment'] != ""){
                        DBFunctions::insert('grand_alumni',
                                            array('user_id' => $person->getId(),
                                                  'recruited' => $_POST['recruitment'],
                                                  'recruited_country' => $_POST['recruitmentCountry']));
                    }
                    
                    if(isset($_POST['subtype']) && is_array($_POST['subtype'])){
                        // Adds the role subtype if it is set
                        foreach($_POST['subtype'] as $subtype){
                            DBFunctions::insert('grand_role_subtype',
                                                array('user_id' => $person->id,
                                                      'sub_role' => $subtype));
                        }
                    }
                    if($config->getValue("networkName") == "CFN" && array_search(HQP, $_POST['wpUserType']) !== false && $wgScriptPath == ""){
                        $from = "From: {$config->getValue('siteName')} <{$config->getValue('supportEmail')}>" . "\r\n";
                        $headers = "Content-type: text/html\r\n"; 
                        $headers .= $from;
                        mail("training@cfn-nce.ca", "HQP Created", "A new HQP (<a href='{$person->getUrl()}'>{$person->getReversedName()}</a> &lt;{$person->getEmail()}&gt;) has been created.", $headers);
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
