<?php

class PersonEmployment2ReportItem extends StaticReportItem {

    function getHTML(){
        global $wgServer, $wgScriptPath;
        
        $person = Person::newFromId($this->personId);
        
        $employment = $person->getUniversities();
        $items = array();
        foreach($employment as $emp){
            if(!in_array(strtolower($emp['position']), array_merge(Person::$studentPositions['ugrad'], Person::$studentPositions['grad']))){
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
                
                $items[$emp['university']][] = "{$emp['position']}, {$emp['department']}, {$startYear}{$endYear}";
            }
        }
        
        $html = "";
        foreach($items as $key => $item){
            $html .= "<div style='margin-left: 3em;'>
                        {$key}
                        <div style='margin-left: 3em;'>"
                            .implode("<br style='line-height:0.5em;' />", $item).
                        "</div>
                     </div>";
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
