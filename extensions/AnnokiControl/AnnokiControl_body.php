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
