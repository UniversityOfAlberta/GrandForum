<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['VQE'] = 'VQE'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['VQE'] = $dir . 'VQE.i18n.php';
$wgSpecialPageGroups['VQE'] = 'sociql-tools';

function runVQE($par) {
  VQE::run($par);
}

class VQE extends SpecialPage{

	function VQE() {
		wfLoadExtensionMessages('VQE');
		SpecialPage::SpecialPage("VQE", HQP.'+', true, 'runVQE');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
		$wgOut->addHTML("<iframe src='$wgServer$wgScriptPath/extensions/VisualQueryEditor/bin-debug/VisualQueryEditor.html' width='100%' height='600px'></iframe>");
	}
	
	private function execSQL($sql) {
		$dbr = wfGetDB(DB_READ);
		$result = $dbr->query($sql);

		$rows = array();
		while ($row = $dbr->fetchRow($result)) {
			$rows[] = $row;
		}
		
		return $rows;
	}
}

class MyClass {
	
	var $name = "";
	
	function MyClass($name){
		$this->name = $name;
	}
	
	function showName(){
		global $wgOut;
		$wgOut->addHTML("<br />".$this->name);
	}
}

?>
