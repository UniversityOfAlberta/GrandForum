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
                $value = $paper->getCCVType();
                $data = $paper->getData();
                $extra = 0;
                if(!isset($data['peer_reviewed']) || $data['peer_reviewed'] == "No"){
                    $extra = 1;
                }
                switch($status){
                    case "Peer Reviewed":
                    case "Published":
                        $newValues[$type][$value][0+$extra][] = $item;
                        break;
                    case "Not Peer Reviewed":
                    case "To Appear":
                        $newValues[$type][$value][2+$extra][] = $item;
                        break;
                    case "Under Revision":
                        $newValues[$type][$value][4+$extra][] = $item;
                        break;
                    case "Submitted":
                        $newValues[$type][$value][6+$extra][] = $item;
                        break;
                    case "Rejected":
                        $newValues[$type][$value][8+$extra][] = $item;
                        break;
                    default:
                        $newValues[$type][$value][10+$extra][] = $item;
                        break;
                }

            }
        }
        $values = array();
        ksort($newValues);
        foreach($newValues as $type => $value){
            ksort($value);
            foreach($value as $items){
                ksort($items);
                foreach($items as $t){
                    foreach($t as $item){
                        $values[$type][] = $item;
                    }
                }
            }
        }
        $this->setValues($values);
    }
    
    function getTypeValue($type){
        $value = 0;
        switch($type){
            case "Journal Paper":
                $value = 100;
                break;
            case "Book Chapter":
                $value = 200;
                break;
            case "Conference Paper":
            case "Collections Paper":
            case "Proceedings Paper":
                $value = 300;
                break;
            case "PHD Dissertation":
            case "PHD Thesis":
                $value = 400;
                break;
            case "Masters Dissertation":
            case "Masters Thesis":
                $value = 500;
                break;
            case "Bachelors Thesis":
                $value = 600;
                break;
            default:
                $value = 1000;
                break;
        }
        return $value;
    }
    
    function toString(){
        return $this->value;
    }
    
    function getHeaders(){
        if($this->category == "Publication" || $this->category == "Artifact"){
            return array("Publication Date", "First Author", $this->category);
        }
        return array("Publication Date", "First Author", $this->category);
    }
    
    function detailsRow($item){
        global $wgServer, $wgScriptPath;
        $paper = Paper::newFromId($item);
        $data = $paper->getData();
        $authors = $paper->getAuthors();
        $first_author = (isset($authors[0]))? explode(' ', $authors[0]->getNameForForms()) : array("","");
        if(count($first_author) > 1){
            $first_author = $first_author[1];
        }
        else{
            $first_author = $first_author[0];
        }
        $citation = $paper->getCitation();
        $hqpAuthored = "";
        if($this instanceof PersonPublicationCell){
            $found = false;
            foreach($paper->getAuthors() as $author){
                if($author->getId() == $this->table->obj->getId()){
                    $found = true;
                }
            }
            if(!$found){
                $hqpAuthored = "<span class='pdfOnly'>;</span> (Authored by HQP)";
            }
        }
        $status = $paper->getStatus();
        if(!isset($data['peer_reviewed'])){
            $pr = "No";
        }
        else{
            $pr = $data['peer_reviewed'];
        }
        if($pr == "No"){
            $pr = "Not Peer Reviewed";
        }
        else if($pr == "Yes"){
            $pr = "Peer Reviewed";
        }
        $stat = "";
        if($paper->getCategory() == "Publication"){
            $stat = "{$status} / {$pr}";
        }
        else{
            if($status != ""){
                $stat = "{$status}";
            }
        }

        $details = "<td style='white-space:nowrap;text-align:left;' class='pdfnodisplay'>{$paper->getDate()}</td><td class='pdfnodisplay' style='text-align:left;'>{$first_author}{$hqpAuthored}</td><td style='width:50%;text-align:left;'>{$citation}<div class='pdfOnly' style='width:50%;margin-left:50%;text-align:right;'><i>{$stat}</i></div></td>\n";
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
                $details .= "</tbody></table>\n";
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
