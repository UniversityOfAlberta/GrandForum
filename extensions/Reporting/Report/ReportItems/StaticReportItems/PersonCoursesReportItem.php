<?php

class PersonCoursesReportItem extends StaticReportItem {

    function getHTML(){
        global $wgServer, $wgScriptPath;
        
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        
        $tab = new PersonCoursesTab($person, array());
        return $tab->getHTML($start, $end, true);
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
