<?php

class HQPDocsTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("HQP Docs");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function userCanView(){
        $me = Person::newFromWgUser();
        return $me->isRoleAtLeast(STAFF);
    }
    
    function canEdit(){
        return $this->userCanView();
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if(!$this->userCanView()){
            return "";
        }
        $this->html = "<table>";
        $doc1 = $this->getBlobValue('HQP_EPIC_DOCS_A');
        $this->html .= "<tr><td align='right'><b>Appendix A:</b></td><td align='center'><span style='font-size:2em;'>{$doc1}</span></td></tr>";
        
        $doc2 = $this->getBlobValue('HQP_EPIC_DOCS_COI');
        $this->html .= "<tr><td align='right'><b>COI:</b></td><td align='center'><span style='font-size:2em;'>{$doc2}</span></td></tr>";
        
        $doc3 = $this->getBlobValue('HQP_EPIC_DOCS_NDA');
        $this->html .= "<tr><td align='right'><b>NDA:</b></td><td align='center'><span style='font-size:2em;'>{$doc3}</span></td></tr>";
        $this->html .= "</table>";
    }
    
    function generateEditBody(){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if(!$this->userCanView()){
            return "";
        }
        $this->html = "<table>";
        $doc1 = $this->getBlobValue('HQP_EPIC_DOCS_A');
        $checked = ($doc1 != "") ? "checked='checked'" : "";
        $this->html .= "<tr><td align='right'><b>Appendix A:</b></td><td align='center'><input type='checkbox' name='doc_HQP_EPIC_DOCS_A' value='&#10003;' {$checked} /></td></tr>";
        
        $doc2 = $this->getBlobValue('HQP_EPIC_DOCS_COI');
        $checked = ($doc2 != "") ? "checked='checked'" : "";
        $this->html .= "<tr><td align='right'><b>COI:</b></td><td align='center'><input type='checkbox' name='doc_HQP_EPIC_DOCS_COI' value='&#10003;' {$checked} /></td></tr>";
        
        $doc3 = $this->getBlobValue('HQP_EPIC_DOCS_NDA');
        $checked = ($doc1 != "") ? "checked='checked'" : "";
        $this->html .= "<tr><td align='right'><b>NDA:</b></td><td align='center'><input type='checkbox' name='doc_HQP_EPIC_DOCS_NDA' value='&#10003;' {$checked} /></td></tr>";
        $this->html .= "</table>";
    }
    
    function getBlobValue($blobItem){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $this->person->getId();
        $projectId = 0;
        
        $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
        $addr = ReportBlob::create_address(RP_HQP_APPLICATION, HQP_APPLICATION_EPIC, $blobItem, 0);
        $result = $blb->load($addr);
        $data = $blb->getData();
        
        return $data;
    }
    
    function saveBlobValue($blobItem, $value){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $this->person->getId();
        $projectId = 0;
        
        $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
        $addr = ReportBlob::create_address(RP_HQP_APPLICATION, HQP_APPLICATION_EPIC, $blobItem, 0);
        $blb->store($value, $addr);
    }
    
    function handleEdit(){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath;
        foreach($_POST as $key => $value){
            if(strstr($key, "doc_") !== false){
                $this->saveBlobValue(str_replace("doc_", "", $key), $value);
            }
        }
        DBFunctions::commit();
        header("Location: {$this->person->getUrl()}?tab=epic");
        exit;
    }
}
?>
