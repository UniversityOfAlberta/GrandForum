<?php

class PersonSupervisesReportItem extends StaticReportItem {

    var $footnotes;
    var $awards;

    function getHTML($pdf=false){
        global $wgServer, $wgScriptPath;
        $dir = dirname(__FILE__);
        require_once($dir . '/../../../../../Classes/SmartDomDocument/SmartDomDocument.php');
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        $splitGrad = strtolower($this->getAttr('splitGrad', 'false'));
        $showOther = (strtolower($this->getAttr('showOther', 'true')) == "true");
        $showCommittees = (strtolower($this->getAttr('showCommittees', 'false')) == "true");
        
        $tab = new PersonGradStudentsTab($person, array());

        $callback = new ReportItemCallback($this);
        
        $gradCount  = $callback->getUserGradCount();
        $mscCount  = $callback->getUserMscCount();
        $phdCount  = $callback->getUserPhdCount();
        $pdfCount   = $callback->getUserFellowCount();
        $techCount  = $callback->getUserTechCount();
        $ugradCount = $callback->getUserUgradCount();
        $otherCount = $callback->getUserOtherCount();
        $committeeCount = $callback->getUserCommitteeCount();
        
        $item = "";
        if($splitGrad != "true"){
            $item .= "<h4>Graduate Students (Supervised or Co-supervised): {$gradCount}</h4>";
            if($gradCount > 0){
                $item .= $tab->supervisesHTML('grad', 
                                              $this->getReport()->startYear."-07-01", 
                                              $this->getReport()->year."-06-30");
            }
        }
        else{
            $item .= "<h4>Doctoral Students (Supervised or Co-supervised): {$phdCount}</h4>";
            if($phdCount > 0){
                $item .= $tab->supervisesHTML('phd', 
                                              $this->getReport()->startYear."-07-01", 
                                              $this->getReport()->year."-06-30");
            }
            
            $item .= "<br /><h4>Master's Students (Supervised or Co-supervised): {$mscCount}</h4>";
            if($mscCount > 0){
                $item .= $tab->supervisesHTML('msc', 
                                              $this->getReport()->startYear."-07-01", 
                                              $this->getReport()->year."-06-30");
            }
        }
        
        $item .= "<br /><h4>Undergraduates: {$ugradCount}</h4>";
        if($ugradCount > 0){
            $item .= $tab->supervisesHTML('ugrad', 
                                          $this->getReport()->startYear."-07-01", 
                                          $this->getReport()->year."-06-30");
        }
        
        $item .= "<br /><h4>Post-doctoral Fellows and Research Associates (Supervised or Co-supervised): {$pdfCount}</h4>";
        if($pdfCount > 0){
            $item .= $tab->supervisesHTML('pdf', 
                                          $this->getReport()->startYear."-07-01", 
                                          $this->getReport()->year."-06-30");
        }
        
        $item .= "<br /><h4>Research/Technical Assistants: {$techCount}</h4>";
        if($techCount > 0){
            $item .= $tab->supervisesHTML('tech', 
                                          $this->getReport()->startYear."-07-01", 
                                          $this->getReport()->year."-06-30");
        }
        
        if($showOther && $otherCount > 0){
            $item .= "<br /><h4>Other: {$otherCount}</h4>";
            $item .= $tab->supervisesHTML('other', 
                                          $this->getReport()->startYear."-07-01", 
                                          $this->getReport()->year."-06-30");
        }
        
        if($showCommittees && $committeeCount > 0){
            $item .= "<br /><h4>Student Committee Responsibilities: {$committeeCount}</h4>";
            $item .= $tab->supervisesHTML('committee',
                                          $this->getReport()->startYear."-07-01",
                                          $this->getReport()->year."-06-30");
        }
        
        $this->footnotes = array();
        $this->awards = array();
        $dom = new SmartDomDocument();
        $dom->loadHTML($item);
        $trs = $dom->getElementsByTagName("tr");
        $hqpIdDone = array();
        for($i=0; $i<$trs->length; $i++){
            $tr = $trs->item($i);
            $tds = $tr->getElementsByTagName("td");
            $firstTd = $tds->item(0);
            $rowspan = 1;
            if($firstTd != null && $firstTd->getAttribute('rowspan') != "" && $firstTd->getAttribute('rowspan') > 1){
                $rowspan = $firstTd->getAttribute('rowspan');
            }
            if($tr->getAttribute('hqp-id') != ""){
                $hqpId = $tr->getAttribute('hqp-id');
                if(isset($hqpIdDone[$hqpId])){
                    continue;
                }
                $hqpIdDone[$hqpId] = true;
                $section = $this->getSection();
                $sec = $this->getAttr('blobSection', $section->sec); //added for FEC report -rd
                if($sec != '0'){
                    $section->sec = $sec;
                }
                if(strtolower($this->getAttr("awards", "false")) == "true"){
                    $awards = new TextareaReportItem();
                    $awards->setId("{$this->id}_{$hqpId}_awards");
                    $awards->setAttr("blobSection", $sec);
                    $awards->setAttr("height", "60px");
                    $awards->setAttr("width", "200px");
                    $awards->setBlobItem($this->getAttr("blobItem", "HQP_AWARDS"));
                    $awards->setBlobSubItem($hqpId);
                    $awards->setParent($this);
                    $this->awards[] = $awards;
                    if(!$pdf){
                        // EDIT
                        $td = $dom->createDocumentFragment();
                        $td->appendXML("<td rowspan='$rowspan' align='center'>".str_replace("&", "&amp;", $awards->getHTML())."</td>");
                        $tr->appendChild($td);
                    }
                    else {
                        $td = $dom->createDocumentFragment();
                        $td->appendXML("<td rowspan='$rowspan'>".str_replace("&", "&amp;", $awards->getHTMLForPDF())."</td>");
                        $tr->appendChild($td);
                    }
                }
                if(strtolower($this->getAttr("footnotes", "false")) == "true"){
                    $footnote = new FootnotesReportItem();
                    $footnote->setId("{$this->id}_{$hqpId}_footnotes");
                    $footnote->setAttr("blobSection", $sec);
                    $footnote->setBlobItem($this->getAttr("blobItem", "HQP"));
                    $footnote->setBlobSubItem($hqpId);
                    $footnote->setParent($this);
                    $footnote->setAttr("isTopAnchor", $this->getAttr("isTopAnchor", "false"));
                    $this->footnotes[] = $footnote;
                    if(!$pdf){
                        // EDIT
                        $td = $dom->createDocumentFragment();
                        $td->appendXML("<td rowspan='$rowspan' align='center'>{$footnote->getHTML()}</td>");
                        $tr->appendChild($td);
                    }
                    else{
                        // PDF
                        if(strtolower($this->getAttr("isTopAnchor", "false")) == "true"){
                            $linkHTML = $footnote->getPDFHTML();
                            if($linkHTML != ""){
                                $td = $tr->getElementsByTagName("td")->item(0);
                                $link = $dom->createDocumentFragment();
                                $link->appendXML(" <sup>$linkHTML</sup>");
                                $td->appendChild($link);
                            }
                        }
                    }
                }
            }
            else {
                if(strtolower($this->getAttr("awards", "false")) == "true"){
                    // Header & Edit
                    $th = $dom->createDocumentFragment();
                    $th->appendXML("<th>Awards</th>");
                    $tr->appendChild($th);
                }
                if(!$pdf && strtolower($this->getAttr("footnotes", "false")) == "true"){
                    // Header & Edit
                    $th = $dom->createDocumentFragment();
                    $th->appendXML("<th>Footnotes</th>");
                    $tr->appendChild($th);
                }
            }
        }
        return $dom;
    }
    
    function save(){
        $this->getHTML();
        $errors = array();
        foreach($this->footnotes as $footnote){
            $errors = array_merge($errors, $footnote->save());
        }
        foreach($this->awards as $award){
            $errors = array_merge($errors, $award->save());
        }
        return $errors;
    }

    function render(){
        global $wgOut;
        $item = $this->getHTML();
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function renderForPDF(){
        global $wgOut;
        $isTopAnchor = (strtolower($this->getAttr('isTopAnchor', 'true')) == 'true');
        $item = $this->getHTML(true);
        if($isTopAnchor){
            // Top Anchor
            $item = $this->processCData($item);
            $wgOut->addHTML($item);
        }
        else{
            // Bottom Anchor
            $item = "";
            foreach($this->footnotes as $footnote){
                $html = $footnote->getPDFHTML();
                if($html != ""){
                    $item .= "<li>$html</li>";
                }
            }
            $wgOut->addHTML($item);
        }
    }
}

?>
