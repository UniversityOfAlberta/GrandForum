<?php

class MilestoneRadioReportItemSet extends MilestoneReportItemSet {
    
    function renderForPDF(){
        global $wgOut;
        $data = $this->getData();
        foreach($data as $tuple){
            $yesFound = false;
            for($i=0;$i < count($this->items); $i++){
                $item = $this->items[$i];
                if($tuple['milestone_id'] == $item->milestoneId){
                    if($item->id == "contribution"){
                        if($item->getBlobValue() == "Yes"){
                            $yesFound = true;
                            break;
                        }
                    }
                }
            }
            if($yesFound == true){
                for($i=0;$i < count($this->items); $i++){
                    $item = $this->items[$i];
                    if($tuple['milestone_id'] == $item->milestoneId){
                        $item->renderForPDF();
                    }
                }
            }
            else if(isset($_GET['preview'])){
                $wgOut->addHTML("<div style='border: 1px solid #D50013;margin-bottom:10px;background:#FEB8B8;'><small style='color:#D50013;'>(This milestone will not show up in the generated PDF)</small>");
                for($i=0;$i < count($this->items); $i++){
                    $item = $this->items[$i];
                    if($tuple['milestone_id'] == $item->milestoneId){
                        $item->renderForPDF();
                    }
                }
                $wgOut->addHTML("</div>");
            }
        }
    }
    
    function getNMilestonesComplete(){
        $nComplete = 0;
        $noFound = false;
        for($i=0;$i < count($this->items); $i++){
            $item = $this->items[$i];
            if($item->id == "contribution"){
                if($item->getBlobValue() == "No"){
                    $nComplete++;
                    $noFound = true;
                    continue;
                }
                $noFound = false;
            }
            if($noFound){
                continue;
            }
            $nComplete += ($item->getNComplete()/2);
        }
        return floor($nComplete);
    }
    
    function getNMilestonesInvolved(){
        $nInvolved = 0;
        for($i=0;$i < count($this->items); $i++){
            $item = $this->items[$i];
            if($item->id == "contribution"){
                if($item->getBlobValue() == "Yes"){
                    $nInvolved++;
                }
            }
        }
        return $nInvolved;
    }
    
    function getNMilestonesNotInvolved(){
        $nNotInvolved = 0;
        for($i=0;$i < count($this->items); $i++){
            $item = $this->items[$i];
            if($item->id == "contribution"){
                if($item->getBlobValue() == "No"){
                    $nNotInvolved++;
                }
            }
        }
        return $nNotInvolved;
    }
    
    function getNMilestonesNotMentioned(){
        return count($this->getData()) - ($this->getNMilestonesInvolved() + $this->getNMilestonesNotInvolved());
    }
    
    function getNMilestonesCommented(){
        return count($this->getData()) - $this->getNMilestonesEmpty();
    }
    
    function getNMilestonesEmpty(){
        $nEmpty = 0;
        $yesFound = false;
        for($i=0;$i < count($this->items); $i++){
            $item = $this->items[$i];
            if($item->id == "contribution"){
                if($item->getBlobValue() == "Yes"){
                    $yesFound = true;
                    continue;
                }
                $yesFound = false;
            }
            if($yesFound){
                continue;
            }
            if($item instanceof TextareaReportItem){
                $nEmpty++;
            }
        }
        return $nEmpty;
    }
    
    function getNComplete(){
        $nComplete = 0;
        $noFound = false;
        for($i=0;$i < count($this->items); $i++){
            $item = $this->items[$i];
            if($item->id == "contribution"){
                if($item->getBlobValue() == "No"){
                    $nComplete++;
                    $noFound = true;
                    continue;
                }
                $noFound = false;
            }
            if($noFound){
                continue;
            }
            $nComplete += $item->getNComplete();
        }
        return $nComplete;
    }
    
    function getNFields(){
        $nFields = 0;
        $noFound = false;
        for($i=0;$i < count($this->items); $i++){
            $item = $this->items[$i];
            if($item->id == "contribution"){
                if($item->getBlobValue() == "No"){
                    $nFields++;
                    $noFound = true;
                    continue;
                }
                $noFound = false;
            }
            if($noFound){
                continue;
            }
            $nFields += $item->getNFields();
        }
        return $nFields;
    }
    
    function getLimit(){
        $limit = 0;
        $noFound = false;
        foreach($this->items as $item){
            if($item instanceof ReportItemSet){
                if($item->getLimit() > 0){
                    $limit += $item->getLimit();
                }
            }
            else if($item->id == "contribution"){
                if($item->getBlobValue() == "No"){
                    $noFound = true;
                    continue;
                }
                $noFound = false;
            }
            else if ($item instanceof TextareaReportItem){
                if(!$noFound && $item->getLimit() > 0){
                    $limit += $item->getLimit();
                }
            }
        }
        return $limit;
    }
    
    function getNChars(){
        $nChars = 0;
        $noFound = false;
        foreach($this->items as $item){
            if($item instanceof ReportItemSet){
                if($item->getLimit() > 0){
                    $nChars += $item->getNChars();
                }
            }
            else if($item->id == "contribution"){
                if($item->getBlobValue() == "No"){
                    $noFound = true;
                    continue;
                }
                $noFound = false;
            }
            else if ($item instanceof TextareaReportItem){
                if(!$noFound && $item->getLimit() > 0){
                    $nChars += $item->getNChars();
                }
            }
        }
        return $nChars;
    }
    
    function getNTextareas(){
        $nTextareas = 0;
        $yesFound = false;
        foreach($this->items as $item){
            if($item instanceof ReportItemSet){
                $nTextareas += $item->getNTextareas();
            }
            else if($item->id == "contribution"){
                if($item->getBlobValue() == "Yes"){
                    $yesFound = true;
                    continue;
                }
                $yesFound = false;
            }
            else if($item instanceof TextareaReportItem){
                if($yesFound){
                    $nTextareas += 1;
                }
            }
        }
        return $nTextareas;
    }
}

?>
