<?php

class PersonEmploymentReportItem extends StaticReportItem {

    function getHTML(){
        global $wgServer, $wgScriptPath;
        
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        
        $employment = array_reverse($person->getUniversities());
        $items = array();
        foreach($employment as $emp){
            $startYear = substr($emp['start'], 0, 4);
            $endYear = substr($emp['end'], 0, 4);
            
            if($endYear == "0000"){
                $endYear = "Present";
            }
            
            $items[$emp['university']][] = "{$emp['position']}, {$emp['department']}<br />
                                            {$startYear} - {$endYear}<br />";
        }
        
        $html = "";
        foreach($items as $key => $item){
            $html .= "<h3>{$key}</h3>".implode("<br style='line-height:0.5em;' />", $item);
        }
        
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
