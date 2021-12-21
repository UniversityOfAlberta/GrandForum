<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Impersonate'] = 'Impersonate'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Impersonate'] = $dir . 'Impersonate.i18n.php';
$wgSpecialPageGroups['Impersonate'] = 'network-tools';

$wgHooks['ToolboxLinks'][] = 'Impersonate::createDelegateLink';

if(!isExtensionEnabled('Shibboleth')){
    $wgHooks['AuthPluginSetup'][] = 'startImpersonate';
}
$wgHooks['UserLogoutComplete'][] = 'clearImpersonation';
UnknownAction::createAction('getUserMode');

class Impersonate extends SpecialPage {

	function __construct() {
	    global $wgOut, $wgServer, $wgScriptPath;
	    SpecialPage::__construct("Impersonate", null, true);
	}
	
	function userCanExecute($user){
	    global $wgImpersonate, $wgDelegate;
	    if($wgImpersonate || $wgDelegate){
	        return false;
	    }
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(SD) || count($person->getDelegates()) > 0);
    }
	
	function execute($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
		$this->getOutput()->setPageTitle("Impersonate");
	    $user = Person::newFromWgUser();
	    $allPeople = array();
	    if($user->isRoleAtLeast(STAFF) || $user->isRole(SD)){
	        $allPeople = Person::getAllCandidates('all');
        }
        else if(count($user->getDelegates()) > 0){
            $allPeople = $user->getDelegates();
        }
	    
	    $wgOut->addHTML("<span id='pageDescription'>Impersonating allows you to temporarily view the Forum as another user.<br />Select a user from the list below, and then click the 'Impersonate' button to begin a session.</span><table>
	                        <tr><td>
	                            <select id='names' data-placeholder='Chose a Person...' name='name' size='10' style='width:100%'>");
	    foreach($allPeople as $person){
	        $wgOut->addHTML("<option value=\"{$person->getName()}\">".str_replace(".", " ", $person->getNameForForms())."</option>\n");
	    }
	    $wgOut->addHTML("</select>
	            </td></tr>
	            <tr><td>
	        <input type='button' id='button' name='next' value='Impersonate' disabled='disabled' /></td></tr></table>
	    <script type='text/javascript'>
	        $('#names').chosen();
	        $(document).ready(function(){
	            $('#names').change(function(){
	                var page = $('#names').val();
	                if(page != ''){
	                    $('#button').prop('disabled', false);
	                }
	            });
	            $('#button').click(function(){
                    var page = $('#names').val();
                    if(typeof page != 'undefined'){
                        document.location = '".$wgServer.$wgScriptPath."/index.php?impersonate=' + page;
                    }
                });
	        });
	    </script>");
	}
	
	static function createDelegateLink(&$toolbox){
        global $wgImpersonating, $wgDelegating, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if(!$wgImpersonating && !$wgDelegating && count($me->getDelegates()) > 0){
            $link = TabUtils::createToolboxLink("Delegate", "$wgServer$wgScriptPath/index.php/Special:Impersonate");
            $toolbox['Other']['links'][] = $link;
        }
        else if(!$wgImpersonating && !$wgDelegating && ($me->isRoleAtLeast(STAFF) || $me->isRole(SD))){
            $link = TabUtils::createToolboxLink("Impersonate", "$wgServer$wgScriptPath/index.php/Special:Impersonate");
            $toolbox['Other']['links'][] = $link;
        }
        return true;
    }
	
}

function getUserMode($action, $page){
    global $wgUser, $wgImpersonating, $wgDelegating;
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
    if(!$wgUser->isLoggedIn()){
        return true;
    }
    if(isset($_GET['embed']) && $_GET['embed'] != "false"){
        $wgUser->setId(0);
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
        if(($realPerson->isRoleAtLeast(STAFF) || $realPerson->isRoleAtLeast(SD)) && ($realPerson->isRoleAtLeast(MANAGER) || !$person->isRoleAtLeast(MANAGER))){
            $pageAllowed = true;
        }
        if($realPerson->isDelegateFor($person)){
            $pageAllowed = true;
        }
        else{
            Hooks::run('CheckImpersonationPermissions', array($person, $realPerson, $ns, $title, &$pageAllowed));
        }
        
        if(!$pageAllowed && !((isset($_POST['submit']) && $_POST['submit'] == "Save") || isset($_GET['showInstructions']) || (isset($_GET['action']) && $_GET['action'] == 'getUserMode'))){
            permissionError();
            return true;
        }
    }
    return true;
}

function getImpersonatingMessage(){
    global $wgRequest, $wgServer, $wgScriptPath, $wgUser, $wgMessage, $wgRealUser, $wgImpersonating, $wgDelegating, $wgTitle, $wgReadOnly;
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
        if(!$wgDelegating){
            $wgReadOnly = "The wiki is currently in read-only mode while you are impersonating <b>{$person->getNameForForms()}</b>.";
        }
        $readOnly = ($wgDelegating) ? "" : " in read-only mode";
        $message .= "<a href='{$realPerson->getUrl()}'>{$realPerson->getNameForForms()}</a> is currently viewing the forum as <a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>{$readOnly}.  This session will expire in ".ceil($time/(60))." minutes.<br />
                            <a href='{$wgServer}{$page}{$renewSession}'>Renew My Session as {$person->getNameForForms()}</a> | <a href='{$wgServer}{$page}{$stopImpersonating}'>Stop Impersonating and Resume as {$realPerson->getNameForForms()}</a>";
    }
    else{
        $readOnly = ($wgDelegating) ? "" : " in read-only mode";
        $message .= "<a href='{$realPerson->getUrl()}'>{$realPerson->getNameForForms()}</a> is currently viewing the forum as <a href='{$person->getUrl()}'>{$person->getNameForForms()}</a>{$readOnly}.  This session will expire once you navigate away from this page";
    }
    Hooks::run('ImpersonationMessage', array($person, $realPerson, $ns, $title, &$message));
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
