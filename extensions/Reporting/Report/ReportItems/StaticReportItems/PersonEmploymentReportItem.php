<?php

class PersonEmploymentReportItem extends StaticReportItem {

    function getHTML(){
        global $wgServer, $wgScriptPath;
        
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        
        $employment = array_reverse($person->getUniversities());
        $item = "";
        foreach($employment as $emp){
            $startYear = substr($emp['start'], 0, 4);
            $endYear = substr($emp['end'], 0, 4);
            
            if($endYear == "0000"){
                $endYear = "Present";
            }
            
            $item .= "<h3>{$emp['university']}</h3>";
            $item .= "<b>Department:</b> {$emp['department']}<br />";
            $item .= "<b>Position:</b> {$emp['position']}<br />";
            $item .= "<b>Years:</b> {$startYear} - {$endYear}<br />";
        }
        
        return $item;
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
