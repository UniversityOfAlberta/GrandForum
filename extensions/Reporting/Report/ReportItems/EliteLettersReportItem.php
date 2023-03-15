<?php

class EliteLettersReportItem extends MultiTextReportItem {
    
    function render(){
        if(isset($_GET['sendEmails'])){
            $this->sendEmail();
            exit;
        }
        parent::render();
    }
    
    function sendEmail($sto=null){
        global $wgServer, $wgScriptPath, $config;
        $data = $this->getBlobValue();
        $report = $this->getReport();
        foreach($data as $row){
            $name = @trim($row['name']);
            $email = @trim($row['email']);
            $id = @trim($row['id']);
            $id = md5("{$email}:{$id}");
            if($name != "" && $email != ""){
                // First check that the file isn't uploaded yet
                $blob = new ReportBlob(BLOB_RAW, $report->year, $this->personId, $this->projectId);
	            $blob_address = ReportBlob::create_address($report->reportType, "PROFILE", "LETTER", $id);
	            $blob->load($blob_address);
	            $blob_data = $blob->getData();
	            if($blob_data != ""){
	                // Already uploaded, so skip
	                continue;
	            }
	            
	            // Continue if not yet uploaded
	            if($sto != null){
                    $tok = urlencode(encrypt(urldecode($sto->metadata('token')), true));
                }
                else{
                    $tok = urlencode(encrypt(urldecode($this->getMD5())));
                }
                if($report->reportType == "RP_PHD_ELITE"){
                    $url = "{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=ReferenceLetter&candidate={$tok}&id={$id}";
                }
                else if($report->reportType == "RP_SCI_PHD_ELITE"){
                    $url = "{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=ScienceReferenceLetter&candidate={$tok}&id={$id}";
                }
                else{
                    $url = "{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=".str_replace("PDF", "", $report->xmlName)."-ReferenceLetter&candidate={$tok}&id={$id}";
                }
                $headers = "From: {$config->getValue('networkName')} Support <{$config->getValue('supportEmail')}>\r\n" .
                           "Reply-To: {$config->getValue('networkName')} Support <{$config->getValue('supportEmail')}>\r\n" .
                           "Content-type:text/html;charset=UTF-8\r\n" .
                           "X-Mailer: PHP/" . phpversion();
                
                $message = "<p>{$report->person->getNameForForms()} has requested that you submit a letter of reference for their {$report->name}.  You can submit your letter of reference <a href='{$url}'><b>here</b></a>.";
                mail($email, "Letter of Reference", $message, $headers);
            }
        }
        return true;
    }
    
    function renderForPDF(){
        global $wgHooks;
        $wgHooks['AfterGeneratePDF'][] = array($this, 'sendEmail');
        return parent::renderForPDF();
    }
}

?>
