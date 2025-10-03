<?php

class DownloadReportItem extends StaticReportItem {

	function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        
        $reportType = $this->getAttr("reportType", 'NIReport');
        $buttonName = $this->getAttr("buttonName", "Misc Attachment");

        $section_id = $this->getAttr("sectionId", "");
        $report_item_set_id = $this->getAttr("reportItemSetId", "");
        $report_item_id = $this->getAttr("reportItemId", "");

        $person = Person::newFromId($this->personId);
        
        $report = new DummyReport($reportType, $person);
        
        $report_item_set = null;
        $report_item = null;

        $section = $report->getSectionById($section_id);
        if($section){
            $report_item_set = $section->getReportItemById($report_item_set_id);
        }
        if($report_item_set){
            $report_item = $report_item_set->getReportItemById($report_item_id);
        }

        if($report_item){
            
            $data = json_decode($report_item->getBlobValue());
            if(isset($_GET['downloadFile']) && isset($_GET['hash'])){
                $hash = $_GET['hash'];
                if($data != null && $data->hash == $hash){
                    header("Content-disposition: attachment; filename='".addslashes($data->name)."'");
                    echo base64_decode($data->file);
                    close();
                }
            }
            
            $report_name = $this->getReport()->xmlName;
            $section_name = $this->getSection()->name;
            $section_name = urlencode($section_name);
        	if($data){
                $hash = $data->hash;
        		$item = "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:Report?report={$report_name}&section={$section_name}&downloadFile&hash={$hash}'>{$buttonName}</a>";
        		$item = $this->processCData($item);
        	    $wgOut->addHTML($item);
        	}
        	else{
        	    $wgOut->addHTML($this->processCData(""));
        	}
        }else{
            $wgOut->addHTML($this->processCData(""));
        }
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $wgOut->addHTML($this->processCData(""));
	}
}

?>
