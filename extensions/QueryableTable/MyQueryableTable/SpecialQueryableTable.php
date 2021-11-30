<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialQueryableTable'] = 'SpecialQueryableTable';
$wgExtensionMessagesFiles['SpecialQueryableTable'] = $dir . 'SpecialQueryableTable.i18n.php';

require_once("MyQueryableTable.php");

function runSpecialQueryableTable($par) {
	SpecialQueryableTable::execute($par);
}

class SpecialQueryableTable extends SpecialPage {

	function __construct() {
		SpecialPage::__construct("SpecialQueryableTable", HQP.'+', true, 'runSpecialQueryableTable');
	}
	
	function execute($par){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $data = array(array("Channel1", "Channel2", "Channel3", "Channel4"),
	                  array("Red", "Green", "Blue", "Alpha"),
	                  array("128", "64", "192", "255"));
	    $table = new MyQueryableTable(MY_STRUCTURE, $data);
	    $wgOut->addHTML($table->render());
	    
	    $wgOut->addHTML($table->copy()->select(HEAD, array("Channel1"))->render());
	    
	    $wgOut->addHTML($table->copy()->where(HEAD, array("Channel1"))->render());
	    
	    $wgOut->addHTML($table->copy()->filterCols(HEAD, array("Channel1"))->render());
	    
	    $wgOut->addHTML($table->copy()->filter(HEAD, array("Channel1"))->render());
	    
	    $wgOut->addHTML($table->copy()->limit(0,2)->render());
	    
	    $wgOut->addHTML($table->copy()->transpose()->render());
	    
	    $wgOut->addHTML($table->copy()->count()->render());
	    
	    $wgOut->addHTML($table->copy()->concat()->render());
	    
	    $wgOut->addHTML($table->copy()->rasterize()->render());
	    
	    $wgOut->addHTML($table->copy()->join($table->copy())->render());
	    
	    $wgOut->addHTML($table->copy()->union($table->copy())->render());
	}
}
?>
