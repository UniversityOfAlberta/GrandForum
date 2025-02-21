<?php

require_once("SpecialImpersonate.php");

$wgHooks['AuthPluginSetup'][] = 'startImpersonate';
$wgHooks['UserGetRights'][] = 'changeGroups';
$wgHooks['UserLogoutComplete'][] = 'clearImpersonation';
UnknownAction::createAction('getUserMode');

function getUserMode($action, $page){
    global $wgUser, $wgImpersonating, $wgDelegating, $config;
    $me = Person::newFromUser($wgUser);
    if($action == 'getUserMode'){
        session_write_close();
        $json = array();
        if(!$wgUser->isRegistered()){
            $json = array('mode' => 'loggedOut',
                          'message' => 'You are currently logged out');
            header('Content-Type: application/json');
            echo json_encode($json);
            exit;
        }
        else if(FROZEN && !$me->isRoleAtLeast(STAFF)){
            $json = array('mode' => 'frozen',
                          'message' => "The {$config->getValue('siteName')} is currently not available for edits during the RMC review-and-deliberation period.");
            header('Content-Type: application/json');
            echo json_encode($json);
            exit;
        }
        else if($wgImpersonating){
            $json = array('mode' => 'impersonating',
                          'message' => getImpersonatingMessage());
            header('Content-Type: application/json');
            echo json_encode($json);
            exit;
        }
        else if(isset($_GET['user']) && $_GET['user'] != $wgUser->getName()){
            $json = array('mode' => 'differentUser',
                          'message' => 'You are currently logged in as <i>'.$wgUser->getName().'</i>.  Your browser session is associated with the user <i>'.$_GET['user'].'</i>.  To correct this, refresh the page, but make sure to copy any unsaved changes which you may have made, as they have not been saved.');
            header('Content-Type: application/json');
            echo json_encode($json);
            exit;
        }
        else{
            $json = array('mode' => 'loggedIn',
                          'message' => "");
            header('Content-Type: application/json');
            echo json_encode($json);
            exit;
        }
    }
    return true;
}

function startImpersonate(){
    global $wgRequest, $wgServer, $wgScriptPath, $wgUser, $wgMessage, $wgRealUser, $wgImpersonating, $wgTitle;
    if(!$wgUser->isRegistered()){
        return true;
    }
    /*if(isset($_GET['embed']) && $_GET['embed'] != "false"){
        $wgUser->setId(0);
        return true;
    }*/
    $exploded = explode("?", @$_SERVER["REQUEST_URI"]);
    $page = $exploded[0];
    $title = explode("/", $page);
    $title = @$title[count($title)-1];
    $ns = explode(":", $title);
    $title = @$ns[1];
    $ns = @$ns[0];
    $i = 0;
    
    foreach($_GET as $key => $get){
        if($key != "impersonate" && $key != "nocookie" && $key != "stopImpersonating" && $key != "renewSession" && $key != "title" && $key != "section"){
            if($i == 0){
                $page .= "?$key=$get";
            }
            else{
                $page .= "&$key=$get";
            }
            $i++;
        }
    }
    $me = Person::newFromId($wgUser->getId());
    if(isset($_GET['renewSession']) || (isset($_GET['impersonate']) && !isset($_COOKIE['impersonate']))){
        if(!isset($_GET['impersonate']) && isset($_COOKIE['impersonate'])){
            $exploded = explode("|", $_COOKIE['impersonate']);
            $name = $exploded[0];
            $time = $exploded[1] - time();
            $_GET['impersonate'] = $name;
        }
        
        $_COOKIE['impersonate'] = "{$_GET['impersonate']}|".(time()+(60*60*6));
        if(!isset($_GET['nocookie'])){
            $urlBeforeImpersonate = $page;
            if(isset($_COOKIE['urlBeforeImpersonate'])){
                $urlBeforeImpersonate = $_COOKIE['urlBeforeImpersonate'];
            }
            else{
                if(isset($_SERVER['HTTP_REFERER'])){
                    // Use the referring url
                    $urlBeforeImpersonate = str_replace($wgServer, "", $_SERVER['HTTP_REFERER']);
                }
                else{
                    // Url was not referred
                    $urlBeforeImpersonate = $page;
                }
            }
            setcookie('urlBeforeImpersonate', $urlBeforeImpersonate, time()+(60*60*6), '/');
            setcookie('impersonate', "{$_GET['impersonate']}|".(time()+(60*60*6)), time()+(60*60*6), '/'); // Cookie will expire in six hours
            redirect("{$wgServer}{$page}");
        }
    }
    if(isset($_GET['stopImpersonating'])){
        if(isset($_COOKIE['urlBeforeImpersonate'])){
            $urlBeforeImpersonate = $_COOKIE['urlBeforeImpersonate'];
        }
        else{
            $urlBeforeImpersonate = "$wgScriptPath/index.php/Main_Page";
        }
        setcookie('impersonate', '', time()-(60*60), '/'); // Delete Cookie
        setcookie('urlBeforeImpersonate', '', time()-(60*60), '/'); // Delete Cookie
        redirect("{$wgServer}{$urlBeforeImpersonate}");
    }
    if(isset($_COOKIE['impersonate'])){
        $exploded = explode("|", $_COOKIE['impersonate']);
        $name = $exploded[0];
        $person = Person::newFromName($name);
        $message = getImpersonatingMessage();
        $realPerson = Person::newFromId($wgRealUser->getId());
        $wgMessage->addInfo($message);
        
        $pageAllowed = false;
        if(($realPerson->isRoleAtLeast(STAFF)) && ($realPerson->isRoleAtLeast(MANAGER) || !$person->isRoleAtLeast(MANAGER))){
            $pageAllowed = true;
        }
        if($realPerson->isDelegateFor($person)){
            $pageAllowed = true;
        }
        else{
            wfRunHooks('CheckImpersonationPermissions', array($person, $realPerson, $ns, $title, &$pageAllowed));
        }
        
        if(!$pageAllowed && !((isset($_POST['submit']) && $_POST['submit'] == "Save") || isset($_GET['showInstructions']) || (isset($_GET['action']) && $_GET['action'] == 'getUserMode'))){
            permissionError();
            return true;
        }
    }
    return true;
}

function getImpersonatingMessage(){
    global $wgRequest, $wgServer, $wgScriptPath, $wgUser, $wgMessage, $wgRealUser, $wgImpersonating, $wgDelegating, $wgTitle, $config;
    $exploded = explode("?", @$_SERVER["REQUEST_URI"]);
    $page = $exploded[0];
    $title = explode("/", $page);
    $title = @$title[count($title)-1];
    $ns = explode(":", $title);
    $title = @$ns[1];
    $ns = @$ns[0];
    $exploded = explode("|", $_COOKIE['impersonate']);
    
    $name = $exploded[0];
    $time = $exploded[1] - time();
    $person = Person::newFromName($name);
    if($wgRealUser == null){
        $wgRealUser = $wgUser;
    }
    $realPerson = Person::newFromId($wgRealUser->getId());
    if($realPerson->isRoleAtLeast(STAFF) || $realPerson->isDelegateFor($person)){
        $wgImpersonating = false;
        $wgDelegating = true;
    }
    else{
        $wgImpersonating = true;
    }
    $wgUser = User::newFromId($person->getId());

    $context = RequestContext::getMain();
    $context->setUser($wgUser);

    $message = "";
    if(!isset($_GET['nocookie'])){
        if(strstr($page, "?") !== false){
            $stopImpersonating = "&stopImpersonating";
            $impersonate = "&impersonate={$person->getName()}";
            $renewSession = "&renewSession";
        }
        else{
            $stopImpersonating = "?stopImpersonating";
            $impersonate = "?impersonate={$person->getName()}";
            $renewSession = "?renewSession";
        }
        $readOnly = ($wgDelegating) ? "" : " in read-only mode";
        $message .= "<a href='{$realPerson->getUrl()}'>{$realPerson->getNameForForms()}</a> is currently viewing the {$config->getValue('siteName')} as <a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>{$readOnly}.  This session will expire in ".ceil($time/(60))." minutes.<br />
                            <a href='{$wgServer}{$page}{$renewSession}'>Renew My Session as {$person->getNameForForms()}</a> | <a href='{$wgServer}{$page}{$stopImpersonating}'>Stop Impersonating and Resume as {$realPerson->getNameForForms()}</a>";
    }
    else{
        $readOnly = ($wgDelegating) ? "" : " in read-only mode";
        $message .= "<a href='{$realPerson->getUrl()}'>{$realPerson->getNameForForms()}</a> is currently viewing the {$config->getValue('siteName')} as <a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>{$readOnly}.  This session will expire once you navigate away from this page";
    }
    wfRunHooks('ImpersonationMessage', array($person, $realPerson, $ns, $title, &$message));
    return $message;
}

function checkSupervisesImpersonee(){
    global $wgUser, $wgRealUser, $wgImpersonating;
    if($wgImpersonating){
        $realPerson = Person::newFromId($wgRealUser->getId());
        $person = Person::newFromId($wgUser->getId());
        $hqps = $realPerson->getHQPDuring(CYCLE_START, CYCLE_END);
        
        foreach($hqps as $hqp){
            if($person->getId() == $hqp->getId()){
                return true;
            }
        }
    }
    return false;
}

function changeGroups($user, &$aRights){
    global $wgRoles;
    foreach($aRights as $key => $right){
        if($key >= 1000){
            continue;
        }
        unset($aRights[$key]);
    }
    $aRights[0] = 'read';
    return true;
}

function clearImpersonation( &$user, &$inject_html, $old_name ){
    global $wgImpersonating, $wgScriptPath;
    if($wgImpersonating){
        if(isset($_COOKIE['urlBeforeImpersonate'])){
            $urlBeforeImpersonate = $_COOKIE['urlBeforeImpersonate'];
        }
        else{
            $urlBeforeImpersonate = "$wgScriptPath/index.php/Main_Page";
        }
        setcookie('impersonate', '', time()-(60*60), '/'); // Delete Cookie
        setcookie('urlBeforeImpersonate', '', time()-(60*60), '/'); // Delete Cookie
        redirect("{$wgServer}{$urlBeforeImpersonate}");
    }
    return true;
}

?>
