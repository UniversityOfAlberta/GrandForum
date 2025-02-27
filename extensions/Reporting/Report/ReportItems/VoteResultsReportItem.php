<?php

class VoteResultsReportItem extends SelectReportItem {

    function render(){
        global $wgOut;
        $me = Person::newFromWgUser();
        $voteType = $this->getAttr("voteType");
        if($voteType == "tenure"){
            $blob = new ReportBlob(BLOB_TEXT, $this->getReport()->year, 0, 0);
            $address = ReportBlob::create_address("RP_CHAIR", "FEC_REVIEW", "TENURE", $this->blobSubItem);
            $blob->load($address);
            $data = $blob->getData();
            
            if($data != "i recommend that an appointment with tenure be offered" &&
               $data != "i recommend tenure as per clause 12.17 (special recommendation for tenure)" &&
               $data != "i recommend that continuing appointment be offered"){
                $wgOut->addHTML("<td></td>
                                 <td></td>
                                 <td></td>
                                 <td></td>");
                return;
            }
        }
        else if($voteType == "promotion"){
            $blob = new ReportBlob(BLOB_TEXT, $this->getReport()->year, 0, 0);
            $address = ReportBlob::create_address("RP_CHAIR", "FEC_REVIEW", "PROMOTION", $this->blobSubItem);
            $blob->load($address);
            $data = $blob->getData();
            
            if(strstr($data, "i recommend promotion") === false){
                $wgOut->addHTML("<td></td>
                                 <td></td>
                                 <td></td>
                                 <td></td>");
                return;
            }
        }
        $freezeId = $this->getAttr("freezeId", "");
        
        $votes = $this->getVotes();
                                           
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
        
        sort($names);
        
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
        if($me->isRoleAtLeast(VDEAN) && !$me->isRole(HR)){
            $output .= "<td class='{$freezeId}'><div style='display:inline-block;'><select style='width:{$width};' name='{$this->getPostId()}'>".implode("\n", $items)."</select></div></td>";
        }
        else{
            $output .= "<td class='{$freezeId}'><div style='display:inline-block;'>{$value}</div></td>";
        }

        $output = $this->processCData("{$output}");
        $wgOut->addHTML($output);
    }
    
    function getVotes(){
        $report = $this->getReport();
        $section = $this->getSection();
        $year = $report->year;
        
        $voteBlobItem = $this->getAttr("voteBlobItem");
        $votes = DBFunctions::select(array('grand_report_blobs'),
                                     array('user_id', 'data', 'encrypted'),
                                     array('year' => $year,
                                           'rp_type' => $report->reportType,
                                           'rp_section' => $section->sec,
                                           'rp_item' => $voteBlobItem,
                                           'rp_subitem' => $this->blobSubItem));
        // Decrypt if needed
        foreach($votes as $key => $vote){
            if($vote['encrypted']){
                $votes[$key]['data'] = decrypt($vote['data']);
            }
        }
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
                $blob->store(trim($vote['data']), $blob_address, $vote['encrypted']);
                
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
        return array("Unfrozen", "Frozen", "Unanimous");
    }
}

?>
