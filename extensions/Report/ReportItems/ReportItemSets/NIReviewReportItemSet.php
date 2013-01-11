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
        $person = Person::newFromId($this->personId);
        $person_name = $person->getReversedName();

        $html =<<<EOF
            <div class="ni_review_item_wrapper">
            <h2>{$person_name}</h2>
            <div>
EOF;

        
        $wgOut->addHTML($html);
        foreach($this->items as $item){
            $item->setAttribute("blobSubItem",$this->personId);
            $item->render();
            //exit;
        }
        $wgOut->addHTML("</div></div>");
    }

}

?>
