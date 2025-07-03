<?php

class NSERCTab extends AbstractTab {

    var $year = "";
    var $phase = "";
    
    function __construct($year, $phase=""){
        global $wgOut;
        $lastYear = $year - 1;
        parent::__construct("{$lastYear}/{$year}");
        $this->year = $year;
        $this->phase = $phase;
        $this->id = str_replace(" ", "", "{$this->id}_{$phase}");
    }
    
    function generateBody(){
        $init_tab = 0;
        
        $last_year = $this->year-1;
        $this_year = $this->year;
        
        $init_tabs = array("tabs_{$this->year}_Apr{$last_year}-Mar{$this_year}" => 0, 
                           "tabs_{$this->year}_Jan-Dec{$last_year}" => 1,
		                   "tabs_{$this->year}_Jan-Mar{$last_year}" => 2, 
		                   "tabs_{$this->year}_Apr-Dec{$last_year}" => 3, 
		                   "tabs_{$this->year}_Jan-Mar{$this_year}" => 4);

		if(isset($_GET['year']) && isset($init_tabs[$_GET['year']])){
	    	$init_tab = $init_tabs[$_GET['year']];
	    }
    
        $tabbedPage = new InnerTabbedPage("tabs_{$this->year}_{$this->phase}");

		$int_start = $last_year.NCE_START_MONTH.' 00:00:00';
		$int_end =   $this_year.NCE_END_MONTH. ' 23:59:59';
		$tabbedPage->addTab(new NSERCVariableTab("Apr{$last_year}-Mar{$this_year}", $int_start, $int_end, $this->year, $this->phase));

        $this->html = $tabbedPage->showPage($init_tab);
    }
}    
    
?>
