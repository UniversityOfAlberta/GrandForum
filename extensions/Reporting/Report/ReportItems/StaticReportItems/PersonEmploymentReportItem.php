<?php

class PersonEmploymentReportItem extends StaticReportItem {

    function getHTML(){
        global $wgServer, $wgScriptPath;
        
        $person = Person::newFromId($this->personId);
        
        $employment = array_reverse($person->getUniversities());
        $items = array();
        foreach($employment as $emp){
            if(!in_array($emp['position'], array("Undergraduate", "Graduate Student - Master's", "Graduate Student - Master's Thesis", "Graduate Student - Master's Course", "Graduate Student - Doctoral", "Post-Doctoral Fellow"))){
                $startYear = substr($emp['start'], 0, 4)." - ";
                $endYear = substr($emp['end'], 0, 4);
                if($startYear == "0000 - "){
                    $startYear = "";
                }
                if($endYear == "0000"){
                    $endYear = "Present";
                }
                if($startYear == $endYear){
                    $startYear = "";
                }
                
                $items[$emp['university']][] = "{$emp['position']}, {$emp['department']}<br />
                                                {$startYear}{$endYear}<br />";
            }
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
        if($item != ""){
            $item = $this->processCData($item);
            $wgOut->addHTML($item);
        }
    }
    
    function renderForPDF(){
        global $wgOut;
        $item = $this->getHTML();
        if($item != ""){
            $item = $this->processCData($item);
            $wgOut->addHTML($item);
        }
    }
}

?>
