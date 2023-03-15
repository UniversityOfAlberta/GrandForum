<?php

class EliteUploadedLettersReportItem extends MultiTextReportItem {
    
    function render(){
        global $wgOut, $wgServer, $wgScriptPath;
        $this->id = $this->getAttr('arrayId', $this->id);
        $showStatus = (strtolower($this->getAttr('showStatus', 'false')) == 'true');
        $letters = $this->getBlobValue();
        $md5s = array();
        if(is_array($letters)){
            foreach($letters as $letter){
                $email = trim($letter['email']);
                $id = trim($letter['id']);
                $md5 = md5("{$email}:{$id}");
                $reference = DBFunctions::select(array('grand_report_blobs'),
                                                 array('md5'),
                                                 array('year' => $this->getReport()->year,
                                                       'user_id' => $this->personId,
                                                       'rp_type' => $this->getReport()->reportType,
                                                       'rp_section' => "PROFILE",
                                                       'rp_item' => "LETTER",
                                                       'rp_subitem' => $md5));
                $md5s[$letter['name']] = @$reference[0]['md5'];
            }
        }
        $item = "";
        if(!$showStatus){
            $urls = array();
            foreach($md5s as $name => $md5){
                if($md5 != ""){
                    $md5 = urlencode(encrypt($md5));
                    $urls[] = "<a href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$md5}'>{$name}</a>";
                }
            }
            $item .= implode(", ", $urls);
        }
        else {
            $item .= "<table>";
            foreach($md5s as $name => $md5){
                $status = ($md5 != "") ? "Uploaded" : "Not Uploaded";
                $item .= "<tr><td class='label'>{$name}:</td><td class='value'>{$status}</td></tr>";
            }
            $item .= "</table>";
        }
        $item = $this->processCData($item);
        $wgOut->addHTML("$item");
    }
    
    function renderForPDF(){
        return $this->render();
    }
}

?>
