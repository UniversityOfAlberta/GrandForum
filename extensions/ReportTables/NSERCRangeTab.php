<?php

class NSERCRangeTab extends AbstractTab {

    var $startYear = "";
    var $endYear = "";
    
    function NSERCRangeTab($startYear, $endYear){
        global $wgOut;
        parent::AbstractTab("$startYear-$endYear");
        $this->startYear = $startYear;
        $this->endYear = $endYear;
    }
    
    function generateBody(){
        $last_year = $this->startYear;
        $this_year = $this->endYear;
        
        $int_start = $last_year.REPORTING_CYCLE_START_MONTH.' 00:00:00';
		$int_end =   ($this_year).REPORTING_NCE_END_MONTH. ' 23:59:59';
		$tab = new NSERCRangeVariableTab("{$this->startYear}-{$this->endYear}", $int_start, $int_end, $this->startYear, $this->endYear);
		$this->html = $tab->generateBody();
        return $this->html;
    }
}    
    
?>
