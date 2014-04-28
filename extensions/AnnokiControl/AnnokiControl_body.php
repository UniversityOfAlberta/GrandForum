<?php

function efRunAnnokiControl( $par ) {
  AnnokiControl::run( $par );
}
 
class AnnokiControl extends SpecialPage {
  function AnnokiControl() {
    wfLoadExtensionMessages('AnnokiControl');
    SpecialPage::SpecialPage("AnnokiControl", STAFF.'+', true, 'efRunAnnokiControl');
  }

  function setLocalizedPageName(&$specialPageArray, $code) {
    // The localized title of the special page is among the messages of the extension:
    wfLoadExtensionMessages('AnnokiControl');
    $text = wfMsg('AnnokiControl');
    // Convert from title in text form to DBKey and put it into the alias array:
    $title = Title::newFromText($text);
    $specialPageArray['AnnokiControl'][] = $title->getDBKey();
    return true;
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
  
  function run( $par ) {
    global $wgOut, $egAnnokiExtensions, $wgEmergencyContact;
    $wgOut->addWikiText("==Annoki Extension Manager==\n");
    $newHTML = "<div><table class='wikitable sortable' border=1 cellpadding=5>
<tr><td><b>Extension</b></td><td><b>Installation Status</b></td><td><b>Extension Status</b></td><td><b>Memory Usage (MB)</b></td><td><b>Execution Time (ms)</b></td></tr>\n";
    $totalMem = 0;
    $totalTime = 0;
    foreach($egAnnokiExtensions as $key => $extension){
      $exist = "<td>" . (is_readable($extension['path'])?"Installed":"Not Installed") . "</td>";

      $status = "<td bgcolor=";
      if (!isExtensionEnabled($key))
	$status .= "grey";
      elseif (!is_readable($extension['path']))
	$status .= "red";
      else
	$status .= "green";
      
      $status .= ">" . (isExtensionEnabled($key)?"Enabled":"Disabled") . "</td>";
      $newHTML .= "<tr><td>".$extension['name']."</td>$exist$status<td align='right'>{$extension['size']}</td><td align='right'>{$extension['time']}</td></tr>\n";
      $totalMem += $extension['size'];
      $totalTime += $extension['time'];
    }
    $newHTML .= "<tr><td colspan='3'></td><td align='right'>{$totalMem}</td><td align='right'>{$totalTime}</td></tr>\n";
    
    $newHTML .= "</table></div>";
    $newHTML .= "Note: If any extensions are listed as \"Not Installed\", it is because they are not readable by AnnokiControl.  If they should be installed (ie, they are Enabled), please contact your system administrator at $wgEmergencyContact.";

    $wgOut->addHTML($newHTML);
  }
}


?>
