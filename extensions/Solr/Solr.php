<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Solr'] = 'Solr';
$wgExtensionMessagesFiles['Solr'] = $dir . 'Solr.i18n.php';
$wgSpecialPageGroups['Solr'] = 'grand-tools';

function runSolr($par) {
	Solr::run($par);
}

class Solr extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('Solr');
		SpecialPage::SpecialPage("Solr", MANAGER.'+', true, 'runSolr');
	}
	
	function run(){
	    global $wgUser, $wgOut, $wgServer, $wgScriptPath;
	    $wgOut->addHTML("HELLO WORLD");
	}
	
}

?>
