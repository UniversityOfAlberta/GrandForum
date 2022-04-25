<?php

class SubmitEmailReportItem extends StaticReportItem {

	function render(){
        global $wgServer, $wgScriptPath, $wgOut;
        if(isset($_GET['programSubmit'])){
            $this->sendEmail();
            exit;
        }
        $report = $this->getReport();
        $section = $this->getSection();
        $message = str_replace("'", "&#39;", $this->getAttr('message', "Thank you for submitting"));
        $html = "<iframe id='programFrame' style='width:100%;display:none;' src=''></iframe>
                 <div id='program-messages'></div>
                 <div>
                     <a id='programSubmit' class='program-button' style='min-width: 100px; text-align: center;' id='{$this->getPostId()}' type='button' value='Submit'>Submit <span style='display:none;' class='throbber'></span></a>
                 </div>
                 <script type='text/javascript'>
                    function programSubmitted(){
                        clearAllMessages('#program-messages');
                        addSuccess('$message', false, '#program-messages');
                        $('#programSubmit .throbber').hide();
                    }
                    
                    $('#reportFooter').prev().hide();
                    $('#reportFooter').hide();
                    
                    $(document).ready(function(){
                        $('#reportFooter').prev().hide();
                        $('#reportFooter').hide();
                    });
                    
                    $('#programSubmit').click(function(){
                        $('#programSubmit .throbber').show();
                        saveAll(function(){
                            $('#programFrame').attr('src', '{$wgServer}{$wgScriptPath}/index.php/Special:Report?report={$report->xmlName}&section=".urlencode($section->name)."&programSubmit');
                        });
                    });
                 </script>";
        $wgOut->addHTML($html);
	}
	
	function sendEmail(){
	    global $config, $wgPasswordSender;
	    $subject = "Program Submitted";
	    $message = $this->processCData("");
	    $emails = $this->getAttr('emails', "{$config->getValue('supportEmail')}");
	    $headers = "From: {$config->getValue('networkName')} Support <{$wgPasswordSender}>\r\n" .
                   "Reply-To: {$config->getValue('networkName')} Support <{$wgPasswordSender}>\r\n" .
                   "MIME-Version: 1.0\r\n" .
                   "Content-type: text/html; charset=iso-8859-1\r\n" .
                   "X-Mailer: PHP/" . phpversion();
	    foreach(explode(",", $emails) as $email){
            mail($email, $subject, $message, $headers);
        }
	    echo "{$message}
	    <script type='text/javascript'>
	        parent.programSubmitted();
	    </script>";
	}
	
	function renderForPDF(){
	    global $wgOut;
	    $wgOut->addHTML($this->processCData(""));
	}
}

?>
