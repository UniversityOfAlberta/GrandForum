<?php

use MediaWiki\MediaWikiServices;

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AnnokiControl'] = 'AnnokiControl'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AnnokiControl'] = $dir . 'AnnokiControl.i18n.php';
$wgSpecialPageGroups['AnnokiControl'] = 'other';
 
class AnnokiControl extends SpecialPage {

  function __construct() {
    SpecialPage::__construct("AnnokiControl", STAFF.'+', true);
  }

  static function onMessagesPreLoad($title, &$message) {
    global $wgServer, $wgScriptPath;
    switch(strtolower($title)){
        case "mediawarning": 
            $message = "";
            break;
        case "passwordreset-emailtext-ip":
            $message = "A new password has been requested for {{SITENAME}} (<a href='{$wgServer}{$wgScriptPath}'>{$wgServer}{$wgScriptPath}</a>). A temporary password has been made for the following user:

$2
                        
Your temporary password will expire in {{PLURAL:$5|one day|$5 days}}.";
            break;
        case "passwordremindertext":
            $message = "A new password has been requested for {{SITENAME}} (<a href='{$wgServer}{$wgScriptPath}'>{$wgServer}{$wgScriptPath}</a>). A temporary password for user
\"$2\" has been created and was set to \"$3\".  Your temporary password will expire in {{PLURAL:$5|one day|$5 days}}.";
            break;
        case "createaccount-text":
            $message = "An account has been created for your e-mail address on {{SITENAME}} (<a href='{$wgServer}{$wgScriptPath}'>{$wgServer}{$wgScriptPath}</a>) named \"$2\"";
            if(!isExtensionEnabled('Shibboleth')){
                $message .= ', with password "$3".
You should log in and change your password now.';
            }
            else{
                $message .= '.';
            }
            break;
        case "readonlytext":
            $message = '$1';
            break;
        case "confirmemail_body":
            $message = "<p>An account has been created for your e-mail address on {{SITENAME}} (<a href='{$wgServer}{$wgScriptPath}'>{$wgServer}{$wgScriptPath}</a>) named \"$2\"</p>

                        <p>To confirm that this account really does belong to you and activate email features on {{SITENAME}}, open this link in your browser:</p>

                        <p><a href='$3'>$3</a></p>

                        <p><b>This confirmation code will expire at $4.</b></p>";
            break;
        case "passwordreset-emailelement":
            $message = "<u>Username:</u> <b>$1</b><br />
                        <br />
                        <u>Temporary Password:</u> <b>$2</b>";
            break;
    }
    return true;
  }
  
  static function onUserGetLanguageObject($user, &$code){
      global $wgLang;
        if(@$_GET['lang'] == 'fr' || @$_GET['lang'] == 'en'){
            if($user->isLoggedIn() && $user->getOption("language") != $_GET['lang']){
                $user->setOption("language", $_GET['lang']);
                MediaWikiServices::getInstance()->getUserOptionsManager()->saveOptions( $user );
                DBFunctions::commit();
            }
            else{
                setcookie('lang', $_GET['lang'], time()+60*60*24*30);
            }
            if($code != $_GET['lang']){
                $code = $_GET['lang'];
            }
        }
        else if(!$user->isLoggedIn() && isset($_COOKIE['lang'])){
            $code = $_COOKIE['lang'];
        }
        return true;
    }
  
  function execute( $par ) {
    global $wgOut, $egAnnokiExtensions, $wgEmergencyContact;
    $this->getOutput()->setPageTitle("Annoki Control");
    $newHTML = "<div><table class='wikitable sortable' border=1 cellpadding=5>
    <thead>
        <tr><th>Extension</th><th>Installation Status</th><th>Extension Status</th><th>Memory Usage (MB)</th><th>Execution Time (ms)</th></tr>
    </thead>
    <tbody>";
    $totalMem = 0;
    $totalTime = 0;
    foreach($egAnnokiExtensions as $key => $extension){
      $exist = "<td>" . (is_readable($extension['path'])?"Installed":"Not Installed") . "</td>";

      $status = "<td>" . (isExtensionEnabled($key)?"Enabled":"Disabled") . "</td>";
      $newHTML .= "<tr><td>".$extension['name']."</td>$exist$status<td align='right'>{$extension['size']}</td><td align='right'>{$extension['time']}</td></tr>\n";
      $totalMem += $extension['size'];
      $totalTime += $extension['time'];
    }
    
    $newHTML .= "</tbody></table></div>";
    $newHTML .= "<script type='text/javascript'>
        $('.wikitable').dataTable({
            iDisplayLength: 100
        });
    </script>";
    $newHTML .= "<b>Total Memory:</b> {$totalMem}<br />
                 <b>Total Time:</b> {$totalTime}";
    $wgOut->addHTML($newHTML);
  }
}


?>
