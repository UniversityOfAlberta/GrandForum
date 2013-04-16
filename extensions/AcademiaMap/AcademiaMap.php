<?php

require_once("AcademiaMapProxy.php");

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AcademiaMap'] = 'AcademiaMap';
$wgExtensionMessagesFiles['AcademiaMap'] = $dir . 'AcademiaMap.i18n.php';
$wgSpecialPageGroups['AcademiaMap'] = 'grand-tools';

function runAcademiaMap($par) {
	AcademiaMap::run($par);
}

class AcademiaMap extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('AcademiaMap');
		SpecialPage::SpecialPage("AcademiaMap", HQP.'+', true, 'runAcademiaMap');
	}
	
	function run(){
	    global $wgUser, $wgOut, $wgServer, $wgScriptPath;
	    $wgOut->addHTML("<script type='text/javascript'>
	        function updateIframeHeight(height){
	            $('#academiaMapFrame').height(height + 75);
	        }
	    </script>
	    <iframe id='academiaMapFrame' frameborder='0' scrolling='no' style='border-width:0;min-width:1000px;min-height:500px;' src='$wgServer$wgScriptPath/index.php?action=academiaMapProxy&url=http://academiamap.com/?de=cs&as=grand_nce'></iframe>");
	}
	
}
?>
