<?php

class VoteResultsReportItem extends SelectReportItem {

    function render(){
        global $wgOut;
        $freezeId = $this->getAttr("freezeId", "");
        
        $votes = $this->getVotes();
        $this->getVotes(true);
                                           
        $yes = 0;
        $no = 0;
        $abstain = 0;
        $names = array();
        foreach($votes as $vote){
            $person = Person::newFromId($vote['user_id']);
            if($vote['data'] == "Yes"){
                $yes++;
            }
            else if($vote['data'] == "No"){
                $no++;
            }
            else if($vote['data'] == "Abstain"){
                $abstain++;
            }
            if($vote['data'] != ""){
                $names[] = $person->getLastName();
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
        $output = "<td class='{$freezeId} tooltip' title=\"".implode("&lt;br /&gt;\n", $names)."\">$yes</td>
                   <td class='{$freezeId} tooltip' title=\"".implode("&lt;br /&gt;\n", $names)."\">$no</td>
                   <td class='{$freezeId} tooltip' title=\"".implode("&lt;br /&gt;\n", $names)."\">$abstain</td>";
        $output .= "<td class='{$freezeId}'><div style='display:inline-block;'><select style='width:{$width};' name='{$this->getPostId()}'>".implode("\n", $items)."</select></div></td>";

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
        
        $report = $this->getReport();
        $section = $this->getSection();
        $year = $report->year;
        $voteBlobItem = $this->getAttr("voteBlobItem");
        
        $votesBlob = new ReportBlob(BLOB_TEXT, $year, 0, 0);
        $votesAddress = ReportBlob::create_address($report->reportType, $section->sec, "{$voteBlobItem}_VOTES", $this->blobSubItem);
        $votesBlob->load($votesAddress);
        $nVotes = $votesBlob->getData();
        
        if($prev == "Frozen" && $value == "Unfrozen"){
            // Reset all votes
            $votes = $this->getVotes();
            
            foreach($votes as $vote){
                // If not yet archived, do it
                $blob = new ReportBlob(BLOB_TEXT, $year, $vote['user_id'], 0);
                $blob_address = ReportBlob::create_address($report->reportType, $section->sec, "{$voteBlobItem}_ARCHIVED_{$nVotes}", $this->blobSubItem);
                $blob->store(trim($vote['data']), $blob_address);
                
                // Now delete old vote
                $blob = new ReportBlob(BLOB_TEXT, $year, $vote['user_id'], 0);
	            $blob_address = ReportBlob::create_address($report->reportType, $section->sec, $voteBlobItem, $this->blobSubItem);
                $blob->delete($blob_address);
	        }
        }
        else if(($prev == "Unfrozen" || $prev == "") && $value == "Frozen"){
            // Increment #Votes
            $nVotes++;
	        $votesBlob->store($nVotes, $votesAddress);
        }
        parent::setBlobValue($value);
    }

    function parseOptions(){
        return array("Unfrozen", "Frozen");
    }
}

?>
