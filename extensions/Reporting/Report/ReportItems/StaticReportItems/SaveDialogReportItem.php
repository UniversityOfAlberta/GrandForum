<?php

class SaveDialogReportItem extends StaticReportItem {

	function render(){
		global $wgOut, $wgServer, $wgScriptPath, $config, $wgAdditionalMailParams;
		$message = $this->getAttr("message", "");
		$emails = $this->getAttr('emails', '');
		$item = $this->processCData("<div title='Section Complete' id='saveDialog' style='display:none;'>
		    $message
		</div>");
		if(isset($_GET['saveDialogSubmit'])){
		    if($emails != "" && $wgScriptPath == ""){
		        $headers = "From: {$config->getValue('networkName')} Support <{$config->getValue('supportEmail')}>\r\n" .
                           "Reply-To: {$config->getValue('networkName')} Support <{$config->getValue('supportEmail')}>\r\n" .
                           "X-Mailer: PHP/" . phpversion();
                foreach(explode(",", $emails) as $email){
                    mail($email, $this->getSection()->title." Submitted", $message, $headers, $wgAdditionalMailParams);
                }
            }
		}
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
        // Do nothing
	}
	
}

?>
