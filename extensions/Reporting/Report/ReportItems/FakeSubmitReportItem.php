<?php

class FakeSubmitReportItem extends TextReportItem {
    
    function render(){
        global $wgOut;
        $wgOut->addHTML("<div id='fake_submit' style='display:none;'>");
        parent::render();
        $wgOut->addHTML("</div>");
        if($this->getBlobValue() == "Submitted"){
            $wgOut->addHTML("<div class='success'>Your recommendation has been submitted.</div>");
        }
        $wgOut->addHTML("<div>
                            Submitting your recommendation will mark your reviews as 'submitted', however you may continue to edit your recommendation after submitting if needed.
                        </div><br />
                        <a class='button' id='submit_review'>Submit</a>
                        <script type='text/javascript'>
                            $('#reportFooter').prev().hide();
                            $('#reportFooter').hide();
                            $('#submit_review').click(function(){
                                $('#fake_submit input').val('Submitted');
                                $('.selectedReportTab').click();
                            });
                        </script>");
    }
    
    function renderForPDF(){
        // Do Nothing
    }
    
    function setBlobValue($value){
        global $wgAdditionalMailParams, $config, $wgScriptPath;
        $emails = $this->getAttr("emails", "");
        if($value == "Submitted" && $this->getBlobValue() == "" && $emails != "" && $wgScriptPath == ""){
            $me = Person::newFromWgUser();
            $headers = "From: {$config->getValue('networkName')} Support <{$config->getValue('supportEmail')}>\r\n" .
                       "Reply-To: {$config->getValue('networkName')} Support <{$config->getValue('supportEmail')}>\r\n" .
                       "X-Mailer: PHP/" . phpversion();
            foreach(explode(",", $emails) as $email){
                mail($email, "{$this->getReport()->name} Submitted", "{$me->getName()} has submitted their {$this->getReport()->name}", $headers, $wgAdditionalMailParams);
            }
        }
        return parent::setBlobValue($value);
    }

}

?>
