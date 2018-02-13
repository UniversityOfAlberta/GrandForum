<?php

class PersonCoursesReportItem extends StaticReportItem {

    function getHTML(){
        global $wgServer, $wgScriptPath;
        
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        $showPercentages = (strtolower($this->getAttr('showPercentages', 'false')) == "true");
        $levels = $this->getAttr('levels', null);
        if($levels != null){
            $levels = explode(",", $levels);
        }
        $tab = new PersonCoursesTab($person, array());
        $tab->levels = $levels;
        return $tab->getHTML($start, $end, $showPercentages, true);
        return $html;
    }

    function render(){
        global $wgOut;
        $item = $this->getHTML();
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function renderForPDF(){
        global $wgOut;
        $item = $this->getHTML();
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
}

?>
