<?php

class VoteResultsReportItem extends SelectReportItem {

    function render(){
        global $wgOut;
        $freezeId = $this->getAttr("freezeId", "");
        
        $votes = $this->getVotes();
                                           
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
        $output = "<td class='{$freezeId}'>$yes</td>
                   <td class='{$freezeId}'>$no</td>
                   <td class='{$freezeId}'>$abstain</td>";
        $output .= "<td class='{$freezeId}'><select style='width:{$width};' name='{$this->getPostId()}'>".implode("\n", $items)."</select></td>";

        $output = $this->processCData("{$output}");
        $wgOut->addHTML($output);
    }
    
    function getVotes(){
        $report = $this->getReport();
        $section = $this->getSection();
        $year = $report->year;

        $voteBlobItem = $this->getAttr("voteBlobItem");
        $votes = DBFunctions::select(array('grand_report_blobs'),
                                     array('user_id', 'data'),
                                     array('year' => $year,
                                           'rp_type' => $report->reportType,
                                           'rp_section' => $section->sec,
                                           'rp_item' => $voteBlobItem,
                                           'rp_subitem' => $this->blobSubItem));
        return $votes;
    }
    
    function setBlobValue($value){
        $prev = $this->getBlobValue();
        if($prev == "Frozen" && $value == "Unfrozen"){
            // Reset all votes
            $votes = $this->getVotes();
            
            $report = $this->getReport();
            $section = $this->getSection();
            $year = $report->year;
            
            $voteBlobItem = $this->getAttr("voteBlobItem");
            foreach($votes as $vote){
                $blob = new ReportBlob(BLOB_TEXT, $year, $vote['user_id'], 0);
	            $blob_address = ReportBlob::create_address($report->reportType, $section->sec, $voteBlobItem, $this->blobSubItem);
                $blob->delete($blob_address);
	        }
        }
        parent::setBlobValue($value);
    }

    function parseOptions(){
        return array("Unfrozen", "Frozen");
    }
}

?>
