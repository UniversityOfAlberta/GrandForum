<?php

class CheckSubmittedReportItem extends ReviewSubmitReportItem {

	function render(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgImpersonating, $config;
		$person = Person::newFromId($wgUser->getId());
		$personId = (isset($_GET['person'])) ? "&person=".urlencode($_GET['person']) : "";
		$year = "";
		$pdfcount = 1;
		$pdfFiles = $this->getAttr('pdfFiles', '');
		if($pdfFiles != ''){
		    $pdfFiles = explode(',', $pdfFiles);
		}
		else{
		    $pdfFiles = $this->getReport()->pdfFiles;
		}
        foreach($pdfFiles as $file){
            $tok = false;
            $tst = '';
            $project = null;
            if($this->getReport()->project instanceof Project){
                $project = $this->getReport()->project;
            }
            else if($this->getReport()->project instanceof Theme){
                $project = Theme::newFromId($this->projectId);
            }
            if($file != $this->getReport()->xmlName){
                $report = new DummyReport($file, $person, $project);
            }
            else{
                $report = $this->getReport();
            }
        	$check = $report->getPDF();
        	if (count($check) > 0) {
        		$tok = $check[0]['token']; 	
        		$tst = $check[0]['timestamp'];
        	}
        	
        	// Present some data on available reports.
        	$style1 = "";
        	if ($tok === false) {
        		// No reports available.
                $wgOut->addHTML("<script type='text/javascript'>
                                    var SubmittedPDF = false;
                                </script>
                                ");

        	}
            else{
                $wgOut->addHTML("<script type='text/javascript'>
                    var SubmittedPDF = true;
                    </script>
                    ");
            }
            $pdfcount++;
        }
        
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $wgOut->addHTML($this->processCData(""));
	}
}

?>
