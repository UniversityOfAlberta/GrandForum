<?php

class VoteResultsReportItem extends SelectReportItem {

    function render(){
        global $wgOut;
        $report = $this->getReport();
        $section = $this->getSection();
        $year = $report->year;

        $voteBlobItem = $this->getAttr("voteBlobItem");
        $votes = DBFunctions::select(array('grand_report_blobs'),
                                     array('data'),
                                     array('year' => $year,
                                           'rp_type' => $report->reportType,
                                           'rp_section' => $section->sec,
                                           'rp_item' => $voteBlobItem,
                                           'rp_subitem' => $this->blobSubItem));
                                           
        $yes = 0;
        $no = 0;
        $abstain = 0;
        foreach($votes as $vote){
            if($vote['data'] == "Yes"){
                $yes++;
            }
            else if($vote['data'] == "No"){
                $no++;
            }
            else if($vote['data'] == "Abstain"){
                $abstain++;
            }
        }
        
        $options = $this->parseOptions();
        $value = $this->getBlobValue();
        $default = $this->getAttr('default', '');
        if($value === "" && $default != ''){
            $value = $default;
        }
        $width = $this->getAttr("width", "150px");
        $items = array();
        foreach($options as $option){
            $selected = "";
            if($value == $option){
                $selected = "selected";
            }
            $option = str_replace("'", "&#39;", $option);
            $items[] = "<option value='{$option}' $selected >{$option}</option>";
        }
        
        $output = "<b>Yes:</b> $yes<br />
                   <b>No:</b> $no<br />
                   <b>Abstain:</b> $abstain<br />";
        $output .= "<select style='width:{$width};' name='{$this->getPostId()}'>".implode("\n", $items)."</select>";

        $output = $this->processCData("<div style='display:inline-block;'>{$output}</div>");
        $wgOut->addHTML($output);
    }

    function parseOptions(){
        return array("Unfrozen", "Frozen");
    }
}

?>
