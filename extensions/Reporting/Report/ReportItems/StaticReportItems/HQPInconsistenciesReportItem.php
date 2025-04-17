<?php

class HQPInconsistenciesReportItem extends StaticReportItem {

    function render(){
        global $wgOut;
        $start = $this->getAttr('start', CYCLE_START);
        $end = $this->getAttr('end', CYCLE_END);
        $person = Person::newFromId($this->personId);
        $html = "";
        $rels = array_merge($person->getRelationsDuring(SUPERVISES, $start, $end), 
                            $person->getRelationsDuring(CO_SUPERVISES, $start, $end));
        foreach($rels as $rel){
            if($rel->getType() == "Supervises"){
                $hqp = $rel->getUser2();
                $sups = $hqp->getSupervisors();
                $unique = array();
                foreach($sups as $sup){
                    $unique[$sup->getId()] = $sup->getNameForForms();
                }
                if(count($unique) > 1){
                    $html .= "<li><b>{$hqp->getNameForForms()}</b> is marked as a 'Supervises' relation, but has relations with other supervisors in the same timeframe (".implode("; ", $unique).")</li>";
                }
            }
        }
        $item = $this->processCData("<ul>$html</ul>");
        $wgOut->addHTML($item);
    }

    function renderForPDF(){
        global $wgOut;
        $this->render();
    }
}

?>
