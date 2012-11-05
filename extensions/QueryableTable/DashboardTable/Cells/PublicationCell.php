<?php

abstract class PublicationCell extends DashboardCell {
    
    var $category;
    var $table;
    
    function sortByStatus(){
        $newValues = array();
        foreach($this->values as $type => $values){
            foreach($values as $item){
                $paper = Paper::newFromId($item);
                $status = $paper->getStatus();
                $value = $this->getTypeValue($paper->getType());
                $data = $paper->getData();
                $extra = 0;
                if(!isset($data['peer_reviewed']) || $data['peer_reviewed'] == "No"){
                    $extra = 1;
                }
                switch($status){
                    case "Peer Reviewed":
                    case "Published":
                        $newValues[0+$extra][$type][$value][] = $item;
                        break;
                    case "Not Peer Reviewed":
                    case "To Appear":
                        $newValues[2+$extra][$type][$value][] = $item;
                        break;
                    case "Under Revision":
                        $newValues[4+$extra][$type][$value][] = $item;
                        break;
                    case "Submitted":
                        $newValues[6+$extra][$type][$value][] = $item;
                        break;
                    case "Rejected":
                        $newValues[8+$extra][$type][$value][] = $item;
                        break;
                }
            }
        }
        $values = array();
        ksort($newValues);
        foreach($newValues as $value){
            foreach($value as $type => $items){
                ksort($items);
                foreach($items as $t){
                    foreach($t as $item)
                    $values[$type][] = $item;
                }
            }
        }
        $this->setValues($values);
    }
    
    function getTypeValue($type){
        $value = 0;
        switch($type){
            case "Journal Paper":
                $value = 0;
                break;
            case "Book Chapter":
                $value = 1;
                break;
            case "Collections Paper":
            case "Proceedings Paper":
                $value = 2;
                break;
            case "PhD Thesis":
                $value = 3;
                break;
            case "Masters Thesis":
                $value = 4;
                break;
            case "Bachelors Thesis":
                $value = 5;
                break;
            default:
                $value = 100;
                break;
        }
        return $value;
    }
    
    function toString(){
        return $this->value;
    }
    
    function getHeaders(){
        if($this->category == "Publication" || $this->category == "Artifact"){
            return array("Publication Date", "Projects", "First Author", "Previously Reported", $this->category);
        }
        return array("Publication Date", "Projects", "First Author", $this->category);
    }
    
    function detailsRow($item){
        global $wgServer, $wgScriptPath;
        $paper = Paper::newFromId($item);
        $projects = $paper->getProjects();
        $projs = array();
        foreach($projects as $project){
            $projs[] = "<a href='{$project->getUrl()}' target='_blank'>{$project->getName()}</a>";
        }
        $authors = $paper->getAuthors();
        $first_author = (isset($authors[0]))? explode(' ', $authors[0]->getNameForForms()) : array("","");
        if(count($first_author) > 1){
            $first_author = $first_author[1];
        }
        else{
            $first_author = $first_author[0];
        }
        $citation = $paper->getProperCitation();
        $reported = "";
        if($this->category == "Publication" || $this->category == "Artifact"){
            $rmcYears = $paper->getReportedYears('RMC');
            //$nceYears = $paper->getReportedYears('NCE');
            $nce = "";
            $rmc = (count($rmcYears) > 0) ? implode(", ", $rmcYears) : "Never";
            /*if($this->category == "Publication"){
                $nce = (count($nceYears) > 0) ? implode(", ", $nceYears) : "Never";
                $nce = "<span class='pdfnodisplay'><br /></span><span class='pdfOnly'>; </span><b>Reported to NCE:</b> {$nce}";
            }*/
            $reported = "<td style='text-align:left;'><b>Reported to RMC:</b> {$rmc}{$nce}</td>";
        }
        $hqpAuthored = "<br />";
        if($this instanceof PersonPublicationCell){
            $found = false;
            foreach($paper->getAuthors() as $author){
                if($author->getId() == $this->table->obj->getId()){
                    $found = true;
                }
            }
            if(!$found){
                $hqpAuthored = "<span class='pdfOnly'>;</span> (Authored by HQP)<br />";
            }
        }
        $details = "<td style='white-space:nowrap;text-align:left;'>{$paper->getDate()}<span class='pdfOnly'>, </span></td><td style='text-align:left;'>".implode(", ", $projs)."<span class='pdfOnly'>; </span></td><td class='pdfnodisplay' style='text-align:left;'>{$first_author}<span class='pdfnodisplay'>{$hqpAuthored}</span></td>{$reported}<td style='width:50%;text-align:left;'><span class='pdfOnly'>{$hqpAuthored}</span>{$citation}</td>\n";
        return $details;
    }
    
    function render(){
        global $wgServer, $wgScriptPath;
        $table = "<table width='100%'>\n";
        foreach($this->values as $type => $values){
            $count = count($values);
            $details = "";
            if(!isset($_GET['generatePDF']) && !isset($_GET['evalPDF'])){
                $details = $this->initDetailsTable($type, $this->getHeaders());
                foreach($values as $item){
                    $details .= '<tr>'.$this->detailsRow($item)."</tr>\n";
                }
                $details .= "</tbody></table><br /><br />\n";
                $details .= "<input type='button' onClick='window.open(\"$wgServer$wgScriptPath/index.php/Special:Add{$this->category}Page\");' value='Add {$this->category}' />\n";
            }
            $table .= $this->dashboardRow($type, $details);
        }
        if(count($this->values) == 0){
            $table .= "<tr><td style='text-align:right;border-width:0px;'>0</td></tr>\n";
        }
        $table .= "</table>\n";
        return "$table";
    }
    
}
    
?>
