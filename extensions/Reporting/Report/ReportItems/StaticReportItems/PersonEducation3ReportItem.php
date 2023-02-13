<?php

class PersonEducation3ReportItem extends StaticReportItem {

    function getHTML(){
        global $wgServer, $wgScriptPath;
        
        $person = Person::newFromId($this->personId);
        
        $employment = array_reverse($person->getUniversities());
        $items = array();
        foreach($employment as $emp){
            if(in_array(strtolower($emp['position']), array_merge(Person::$studentPositions['ugrad'], Person::$studentPositions['grad']))){
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
                
                $items[$emp['university']][] = "{$startYear}{$endYear}: {$emp['position']}, {$emp['department']}";
            }
        }
        
        $html = "";
        foreach($items as $key => $item){
            $html .= "<div>
                        <h3>{$key}</h3>
                        <div>"
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
