<?php

require_once("SpecialImpersonate.php");

$wgHooks['AuthPluginSetup'][] = 'impersonate';
$wgHooks['UserGetRights'][] = 'changeGroups';
$wgHooks['UserLogoutComplete'][] = 'clearImpersonation';
$wgHooks['UnknownAction'][] = 'getUserMode';

function getUserMode($action, $page){
    global $wgUser, $wgImpersonating;
    $me = Person::newFromUser($wgUser);
    if($action == 'getUserMode'){
        session_write_close();
        $json = array();
        if(!$wgUser->isLoggedIn()){
            $json = array('mode' => 'loggedOut',
                          'message' => 'You are currently logged out');
            header('Content-Type: application/json');
            echo json_encode($json);
            exit;
        }
        else if(FROZEN && !$me->isRoleAtLeast(MANAGER)){
            $json = array('mode' => 'frozen',
                          'message' => "The Forum is currently not available for edits during the RMC review-and-deliberation period.");
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

function impersonate(){
    global $wgRequest, $wgServer, $wgScriptPath, $wgUser, $wgMessage, $wgRealUser, $wgImpersonating, $wgTitle;
    if(!$wgUser->isLoggedIn()){
        return true;
    }
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
        
        $_COOKIE['impersonate'] = "{$_GET['impersonate']}|".(time()+(60*60));
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
            setcookie('urlBeforeImpersonate', $urlBeforeImpersonate, time()+(60*60), '/');
            setcookie('impersonate', "{$_GET['impersonate']}|".(time()+(60*60)), time()+(60*60), '/'); // Cookie will expire in one hour
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
        if($person->isRoleDuring(HQP)){
            $hqps = $realPerson->getHQPDuring();
            foreach($hqps as $hqp){
                if($hqp->getId() == $person->getId()){
                    if(("$ns:$title" == "Special:Report" &&
                       @$_GET['report'] == "HQPReport") || ("$ns:$title" == "Special:ReportArchive" && checkSupervisesImpersonee())){
                        $pageAllowed = true;
                    }
                    break;
                }
            }
        }
        
        if($realPerson->isRoleAtLeast(MANAGER)){
            $pageAllowed = true;
        }
        else{
            $leadership = $realPerson->leadershipDuring();
            if(count($leadership) > 0){
                foreach($leadership as $proj){
                    if(($person->isRoleDuring(PNI) || $person->isRoleDuring(CNI)) &&
                       $person->isMemberOfDuring($proj)){
                        if("$ns:$title" == "Special:Report" &&
                           @$_GET['report'] == "NIReport" &&
                           @$_GET['project'] == $proj->getName()){
                            $pageAllowed = true;
                        }
                    }
                }
            }
        }
        
        if(!$pageAllowed && !((isset($_POST['submit']) && $_POST['submit'] == "Save") || isset($_GET['showInstructions']) || (isset($_GET['action']) && $_GET['action'] == 'getUserMode'))){
            permissionError($ns, $title);
            return true;
        }
    }
    return true;
}

function getImpersonatingMessage(){
    global $wgRequest, $wgServer, $wgScriptPath, $wgUser, $wgMessage, $wgRealUser, $wgImpersonating, $wgTitle;
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
    $wgImpersonating = true;
    $isSupervisor = false;
    $showReadOnly = true;
    $wgUser = User::newFromId($person->getId());
    
    if($person->isRoleDuring(HQP)){
        $hqps = $realPerson->getHQPDuring();
        foreach($hqps as $hqp){
            if($hqp->getId() == $person->getId()){
                if(checkSupervisesImpersonee()){
                    $showReadOnly = false;
                }
                $isSupervisor = false;
                break;
            }
        }
    }
    
    $readOnly = "";
    if($showReadOnly){
        $readOnly = " in read-only mode";
    }
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
        $message .= "<a href='{$realPerson->getUrl()}'>{$realPerson->getNameForForms()}</a> is currently viewing the forum as <a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>$readOnly.  This session will expire in ".ceil($time/(60))." minutes.<br />
                            <a href='{$wgServer}{$page}{$renewSession}'>Renew My Session as {$person->getNameForForms()}</a> | <a href='{$wgServer}{$page}{$stopImpersonating}'>Stop Impersonating and Resume as {$realPerson->getNameForForms()}</a>";
    }
    else{
        $message .= "<a href='{$realPerson->getUrl()}'>{$realPerson->getNameForForms()}</a> is currently viewing the forum as <a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>$readOnly.  This session will expire once you navigate away from this page";
    }
    if($isSupervisor){
        $message .= "<br />As a supervisor, you are able to edit, generate and submit the report of your HQP.  The user who edits, generates and submits the report is recorded.";
    }
    return $message;
}

function checkSupervisesImpersonee(){
    global $wgUser, $wgRealUser, $wgImpersonating;
    if($wgImpersonating){
        $realPerson = Person::newFromId($wgRealUser->getId());
        $person = Person::newFromId($wgUser->getId());
        $hqps = $realPerson->getHQPDuring();
        
        foreach($hqps as $hqp){
            if($person->getId() == $hqp->getId()){
                return true;
            }
        }
    }
    return false;
}

function permissionError($ns, $title){
    global $wgOut, $wgServer, $wgScriptPath, $wgTitle;
    $wgTitle = Title::newFromText("$ns:$title");
    $wgOut->setPageTitle("Permission error");
    $wgOut->addHTML("<p>You are not allowed to execute the action you have requested.</p>
                     <p>Return to <a href='$wgServer$wgScriptPath/index.php/Main_Page'>Main Page</a>.</p>");
    $wgOut->output();
    $wgOut->disable();
    exit;
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
