<?php

class NIReviewReportItemSet extends ReportItemSet {

    function getData(){
        /*$data = array();
        $person = Person::newFromId($this->personId);
        
        $subs = $person->getEvaluatePNIs();
        
        if(is_array($subs)){
            foreach($subs as $sub){
                $tuple = self::createTuple();
                $tuple['person_id'] = $sub->getId();
                $data[] = $tuple;
            }
        }
        return $data;*/
    
        $data = array();
        $tuple = self::createTuple();
        $data[] = $tuple;
        return $data;
    }

    function render(){
        global $wgOut;
        $type = $this->getAttr('subType', 'NI');

        if($type == "NI"){
            $person = Person::newFromId($this->personId);
            $header = $person->getReversedName();
            $id = $this->personId;
        }else if($type == "Project"){
            $project = Project::newFromId($this->projectId);
            $header = $project->getName();
            $id = $this->projectId;
        }
        $html =<<<EOF
            <div class="ni_review_item_wrapper">
            <h2>{$header}</h2>
            <div>
EOF;

        
        $wgOut->addHTML($html);
        foreach($this->items as $item){
            $item->setAttribute("blobSubItem", $id);
            $item->render();
        }
        $wgOut->addHTML("</div></div>");
    }

}

?>
