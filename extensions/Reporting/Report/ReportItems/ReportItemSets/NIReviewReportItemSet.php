<?php

class NIReviewReportItemSet extends ReportItemSet {

    function getData(){
    
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
            $project = Project::newFromHistoricId($this->projectId);
            $header = $project->getName();
            $id = $this->projectId;

        }

        $html =<<<EOF
            <div class="ni_review_item_wrapper">
            <h2>{$header}<span style="position:absolute; right:5px; top:4px; font-size:10px;"><a style="color:#000;" href=""><i>[Show/Hide]</i></a></span></h2>
            <div>
EOF;

        $wgOut->addHTML($html);
        foreach($this->items as $item){
            //$item->setBlobSubItem($id);
            $item->setAttribute("blobSubItem", $id);
            //echo $item->getAttr("blobSubItem") . "<br>";
            $item->render();
        }
        $wgOut->addHTML("</div></div>");
    }

}

?>
