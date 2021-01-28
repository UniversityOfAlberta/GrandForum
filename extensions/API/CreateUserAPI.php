<?php

use MediaWiki\MediaWikiServices;

class CreateUserAPI extends API{

    function __construct(){
        $this->addPOST("wpName",true,"The User Name of the user to add","UserName");
        $this->addPOST("wpPassword",false,"The Password of the user to add","Password");
        $this->addPOST("wpEmail",false,"The User's email address","me@email.com");
        $this->addPOST("wpRealName",false,"The User's real name","My Real Name");
        $this->addPOST("wpAlias",false,"The User's alias","My Alias");
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
        global $wgUser, $wgServer, $wgScriptPath, $wgOut, $wgMessage, $wgEnableEmail;
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
            // Actually create a new user
            DBFunctions::delete('mw_actor',
                                array('actor_name' => EQ($_POST['wpName'])));
            $creator = self::getCreator($me);
            GrandAccess::$alreadyDone = array();
            $passwd = PasswordFactory::generateRandomPasswordString();
            $tmpUser = User::createNew($_POST['wpName'], array('real_name' => $_POST['wpRealName'], 
                                                               'password' => MediaWikiServices::getInstance()->getPasswordFactory()->newFromPlaintext($passwd)->toString(), 
                                                               'email' => $_POST['wpEmail']));
            if($tmpUser != null){
                if(isset($_POST['wpSendMail']) && $_POST['wpSendMail'] === "true"){
                    $this->sendNewAccountEmail($tmpUser, $creator->getUser(), $passwd);
                }
                Person::$cache = array();
                Person::$namesCache = array();
                Person::$aliasCache = array();
                Person::$idsCache = array();
                $person = Person::newFromName($_POST['wpName']);
                if($person != null && $person->getName() != null){
                    if(isset($_POST['university']) && isset($_POST['department']) && isset($_POST['position'])){
                        $_POST['title'] = $_POST['position'];
                        $_POST['user_name'] = $person->getName();
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
                    $language = "en";
                    $provison = false;
                    if($_POST['wpProvision'] = "Yes"){
                        $provision = true;
                    }
                    if($_POST['wpLanguage'] == "French"){
                        $language = "fr";
                    }

                    $person->getUser()->setOption("language", $language);
                    $person->getUser()->saveSettings();
                    
                    $provData = DBFunctions::select(array('grand_provinces'),
                                                    array('id'),
                                                    array('province' => EQ($_POST['wpProvince'])));
                    $provinceId = isset($provData[0]['id']) ? $provData[0]['id'] : null;
                    if($provinceId == null){
                        DBFunctions::insert('grand_provinces',
                                            array('province' => $_POST['wpProvince']));
                        $provinceId = DBFunctions::insertId();
                    }
                    DBFunctions::insert('grand_personal_caps_info',
                        array('user_id' => $person->id,
                              'postal_code' => $_POST['wpPostalCode'],
                              'city' => $_POST['wpCity'],
                              'province' => $provinceId,
                              'specialty' => $_POST['wpSpecialty'],
                              'prior_abortion_service' => $provision,
                              'accept_referrals' => 0));
                    
                    $collect_demo     = (isset($_POST['wpCollectDemo']))     ? $_POST['wpCollectDemo'] : '0';
                    $collect_comments = (isset($_POST['wpCollectComments'])) ? $_POST['wpCollectComments'] : '0';
                    $alias = (isset($_POST['wpAlias'])) ? $_POST['wpAlias'] : '';
                    DBFunctions::update('mw_user',
                                        array('collect_comments' => $collect_comments,
                                              'collect_demo' => $collect_demo,
                                              'alias' => $alias),
                                        array('user_id'=>$person->id));
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
    
    /**
     * Note: From TemporaryPasswordPrimaryAuthenticationProvider.php
	 * Send an email about the new account creation and the temporary password.
	 * @param User $user The new user account
	 * @param User $creatingUser The user who created the account (can be anonymous)
	 * @param string $password The temporary password
	 * @return \Status
	 */
	protected function sendNewAccountEmail( User $user, User $creatingUser, $password ) {
		$ip = $creatingUser->getRequest()->getIP();
		// @codeCoverageIgnoreStart
		if ( !$ip ) {
			return \Status::newFatal( 'badipaddress' );
		}
		// @codeCoverageIgnoreEnd

		$mainPageUrl = \Title::newMainPage()->getCanonicalURL();
		$userLanguage = $user->getOption( 'language' );
		$subjectMessage = wfMessage( 'createaccount-title' )->inLanguage( $userLanguage );
		$bodyMessage = wfMessage( 'createaccount-text', $ip, $user->getName(), $password,
			'<' . $mainPageUrl . '>', round( 7*24*3600 / 86400 ) )
			->inLanguage( $userLanguage );

		$status = $user->sendMail( $subjectMessage->text(), $bodyMessage->text() );

		return $status;
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
