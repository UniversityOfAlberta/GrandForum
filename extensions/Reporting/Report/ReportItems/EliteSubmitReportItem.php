<?php

class EliteSubmitReportItem extends AbstractReportItem {
    
    function EliteSubmitReportItem(){
        self::AbstractReportItem();
        $this->setAttr("optional", "true");
    }
    
    function render(){
        // DO NOTHING
    }
    
    function mailApplicant(){
        global $wgOut, $config;
        // Email
        $subject = "";
        $message = "";
        if($this->getReport()->xmlName == "ApplicationPDF"){
            $subject = "ELITE Program for Black Youth - Confirmation of Receipt of Submission";
            $message = "Dear Intern Candidate,

Thank you for your submission to the paid work-integrated internship program offered by the Experiential Learning in Innovation, Technology, and Entrepreneurship (ELITE) Program for Black Youth. This message confirms that we received an application from you.

We will begin to review applications shortly and will arrange for interviews for those who have been short-listed. We hope to have the process completed with four weeks.

Please note that we have received more applications than internship positions available. As a result, not all applicants will receive an offer. We will work as expeditiously as possible to advise you of the outcome so that you will all be able to plan accordingly.

In the interim, please do not hesitate to contact us should you have any questions.

Thank you for your interest and continued support of the ELITE Program for Black Youth.

With kind regards,

André G. McDonald, Ph.D., B.S. Law, P.Eng., FASM, FIMMM
Professor
Lead Editor, <i>Journal of Thermal Spray Technology</i>
Director, ELITE Program for Black Youth (www.eliteprogram.ca)
<i>for</i> 
ELITE Program for Black Youth
<a href='http://www.eliteprogram.ca'>www.eliteprogram.ca</a>
<a href='http://www.eliteprogram.ca/contact-us/'>www.eliteprogram.ca/contact-us/</a>
<i>Work-integrated Training for Upward Mobility</i>
**************************************
Programme ELITE pour la Jeunesse Noire
<a href='http://www.eliteprogram.ca/fr/'>www.eliteprogram.ca/fr/</a>
<a href='http://www.eliteprogram.ca/fr/contactez-nous/'>www.eliteprogram.ca/fr/contactez-nous/</a>
<i>Formation intégrée au travail pour la mobilité ascendante</i>";
        }
        else if($this->getReport()->xmlName == "PhDApplicationPDF"){
            $subject = "Engineering-ELITE-IBET PhD Fellowship - Confirmation of Receipt of Submission";
            $message = "Dear Candidate,

Thank you for your submission to the Engineering-ELITE-IBET PhD Fellowship competition led by the Faculty of Engineering at the University of Alberta. This message confirms that we received an application from you.

We will begin to review applications shortly and will arrange for interviews for those who have been short-listed. We hope to have the process completed with eight weeks.

Please note that we have received more applications than fellowship awards available. As a result, not all applicants will receive an offer of fellowship award. We will work as expeditiously as possible to advise you of the outcome so that you will all be able to plan accordingly.

In the interim, please do not hesitate to contact us should you have any questions.

Thank you for your interest and continued support of the Engineering-ELITE-IBET PhD Fellowship Program.

With kind regards,

The Advisory and Nominating Committee
Faculty of Engineering, University of Alberta
<a href='http://www.eliteprogram.ca/contact-us/'>www.eliteprogram.ca/contact-us/</a>";
        }
        if($message != ""){
            $message = nl2br($message);
            $headers  = "Content-type: text/html\r\n"; 
            $headers .= "From: {$config->getValue('siteName')} <{$config->getValue('supportEmail')}>" . "\r\n";
            mail($this->getReport()->person->getEmail(), $subject, $message, $headers);
        }
    }
    
    function mailProjects(){
        global $wgServer, $wgScriptPath, $config;
        $blob = new ReportBlob(BLOB_ARRAY, $this->getReport()->year, $this->personId, $this->projectId);
        $blob_address = ReportBlob::create_address($this->getReport()->reportType, "PROFILE", "PROJECTS_OTHER", 0);
        $blob->load($blob_address);
        $blob_data = $blob->getData();
        if(isset($blob_data['apply_other']) && is_array($blob_data)){
            foreach($blob_data['apply_other'] as $row){
                $subject = "Engineering-ELITE Program-IBET PhD Fellowship: Professor Action Requested";
                $message = "Dear {$row['name']},

An applicant for the Engineering-ELITE-IBET PhD Fellowship has indicated you as a proposed PhD program supervisor.  Please click the link below to register your project on the PhD Fellowship Supervisor Panel and acknowledge participation.

<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Register'>ELITE Registration</a>
<a href='{$wgServer}{$wgScriptPath}/index.php/Special:ElitePostingPage?page=phd#/phd'>PhD Fellowship Supervisor Panel</a>";
                $message = nl2br($message);
                $headers  = "Content-type: text/html\r\n"; 
                $headers .= "From: {$config->getValue('siteName')} <{$config->getValue('supportEmail')}>" . "\r\n";
                mail($row['email'], $subject, $message, $headers);
            }
        }
    }
    
    function renderForPDF(){
        global $wgOut, $config;
        if(isset($_GET['generatePDF']) && !isset($_GET['preview'])){
            // Application Status
            $this->setAttr("blobReport", $this->getReport()->reportType);
            $this->setAttr("blobSection", "PROFILE");
            $this->blobItem = "STATUS";
            if($this->getBlobValue() == "Requested More Info" || 
               $this->getBlobValue() == "Submitted More Info"){
                $this->setBlobValue("Submitted More Info");
            }
            else {
                $this->setBlobValue("Submitted");
            }
            
            $this->mailProjects();
            $this->mailApplicant();
        }
    }

}

?>
