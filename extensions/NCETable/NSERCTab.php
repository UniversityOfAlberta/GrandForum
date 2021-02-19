<?php

class NSERCTab extends AbstractTab {

    var $year = "";
    
    function NSERCTab($year){
        global $wgOut;
        $lastYear = $year - 1;
        parent::AbstractTab("{$lastYear}/{$year}");
        $this->year = $year;
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
    
        $tabbedPage = new InnerTabbedPage("tabs_{$this->year}");

		$int_start = $last_year.NCE_START_MONTH.' 00:00:00';
		$int_end =   $this_year.NCE_END_MONTH. ' 23:59:59';
		$tabbedPage->addTab(new NSERCVariableTab("Apr{$last_year}-Mar{$this_year}", $int_start, $int_end, $this->year));
        
        /*
        $int_start = $last_year.CYCLE_START_MONTH.' 00:00:00';
		$int_end =   ($this_year-1).CYCLE_END_MONTH_ACTUAL. ' 23:59:59';
		$tabbedPage->addTab(new NSERCVariableTab("Jan-Dec{$last_year}", $int_start, $int_end, $this->year));
        
    	$int_start = $last_year.CYCLE_START_MONTH.' 00:00:00';
		$int_end =   $last_year.NCE_END_MONTH. ' 23:59:59';
		$tabbedPage->addTab(new NSERCVariableTab("Jan-Mar{$last_year}", $int_start, $int_end, $this->year));
		
		$int_start = $last_year.NCE_START_MONTH.' 00:00:00';
		$int_end =   ($this_year-1).CYCLE_END_MONTH_ACTUAL. ' 23:59:59';
		$tabbedPage->addTab(new NSERCVariableTab("Apr-Dec{$last_year}", $int_start, $int_end, $this->year));
		
		$int_start = $this_year.CYCLE_START_MONTH.' 00:00:00';
		$int_end =   $this_year.NCE_END_MONTH. ' 23:59:59';
		$tabbedPage->addTab(new NSERCVariableTab("Jan-Mar{$this_year}", $int_start, $int_end, $this->year));
    	*/
        $this->html = $tabbedPage->showPage($init_tab);
    }
}    
    
?>
