<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AnnokiControl'] = 'AnnokiControl'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AnnokiControl'] = $dir . 'AnnokiControl.i18n.php';
$wgSpecialPageGroups['AnnokiControl'] = 'other';
 
class AnnokiControl extends SpecialPage {

  function AnnokiControl() {
    SpecialPage::__construct("AnnokiControl", STAFF.'+', true);
  }

  // Can be used for custom CSS (if we have any) as well
  function addCustomJavascript(&$out){
    global $wgScriptPath;
    //  $out->addScript("\n" . '<link rel="stylesheet" type="text/css" href="' .
    //                 $wgScriptPath . '/extensions/AnnokiControl/AnnokiCSS.css"' . " />");
    $out->addScript("\n         <script type='text/javascript' src='" .
		    $wgScriptPath . '/extensions/AnnokiControl/AnnokiJS.js' . "'></script>");
    $out->addScript("\n         <script type='text/javascript' src='" .
                    $wgScriptPath . '/extensions/AnnokiControl/common/Annoki.js' . "'></script>");
    return true;
  }

  function onMessagesPreLoad($title, &$message) {
    switch(strtolower($title)){
        case "passwordreset-emailtext-ip":
            $message = 'A new password has been requested for {{SITENAME}} ($4). A temporary password has been made for the following user:

"$2"
                        
Your temporary password will expire in {{PLURAL:$5|one day|$5 days}}.';
            break;
        case "passwordremindertext":
            $message = 'A new password has been requested for {{SITENAME}} ($4). A temporary password for user
"$2" has been created and was set to "$3".  Your temporary password will expire in {{PLURAL:$5|one day|$5 days}}.';
            break;
        case "createaccount-text":
            $message = 'An account has been created for your e-mail address on {{SITENAME}} ($4) named "$2", with password "$3".
You should log in and change your password now.';
            break;
    }
    return true;
  }
  
  function execute( $par ) {
    global $wgOut, $egAnnokiExtensions, $wgEmergencyContact;
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
