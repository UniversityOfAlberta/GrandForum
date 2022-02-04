<?php

/**
 * @package GrandObjects
 */

class PhDEliteProfile extends EliteProfile {
    
    static $rpType = "PHD_ELITE";
    
    function acceptedMessage(){
        $subject = "Engineering-IBET-ELITE PhD Fellowship - Decision";
        $message = "Dear PhD Fellowship Candidate,
Thank you for your submission to the Engineering-IBET-ELITE PhD Fellowship program. After a review and adjudication process, we are pleased to advise that you have been selected to receive a PhD Fellowship. Congratulations!

Please see attached a detailed memorandum with additional information and instructions. Please address the action items in the document that were directed to you and respond by the date indicated in the memorandum.

Thank you for your interest and continued support of the Engineering-IBET-ELITE PhD Fellowship initiative. We look forward to working with you.
 
With kind regards,

André G. McDonald, PhD, BS Law, PEng, CEng, FASM, FIMMM, FIMechE
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
        return array($subject, $message);
    }
    
    function shortlistMessage(){
        $subject = "";
        $message = "";
        return array($subject, $message);
    }
    
    function moreInfoMessage(){
        $subject = "";
        $message = "";
        return array($subject, $message);
    }
    
    function rejectedMessage(){
        $subject = "Engineering-IBET-ELITE PhD Fellowship - Decision";
        $message = "Dear PhD Fellowship Candidate,
Thank you for your submission to the Engineering-IBET-ELITE PhD Fellowship program.

We received many applications for only three positions in this year's cohort. After a thorough review and adjudication process, we regret to inform you that your application was not selected to move forward for a fellowship. While this news is unfortunate, we hope that you will not be discouraged, and will apply directly to professors working in your area of research interest and expertise.

Thank you for your interest and continued support of the Engineering-IBET-ELITE PhD Fellowship initiative.
 
With kind regards,

André G. McDonald, PhD, BS Law, PEng, CEng, FASM, FIMMM, FIMechE
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
        return array($subject, $message);
    }
    
    function receivedMessage(){
        $subject = "Engineering-IBET-ELITE PhD Fellowship - Confirmation of Receipt of Submission";
        $message = "Dear PhD Fellowship Candidate,
Thank you for your submission to the Engineering-IBET-ELITE PhD Fellowship program. This message confirms that we received an application from you.

We will begin to review applications shortly. We hope to have the process completed within the next 3 to 8 weeks.

Please note that we have received more applications than fellowship positions available. As a result, not all applicants will receive an offer. We will work as expeditiously as possible to advise you of the outcome so that you will all be able to plan accordingly.

In the interim, please do not hesitate to contact us should you have any questions.

Thank you for your interest and continued support of the Engineering-IBET-ELITE PhD Fellowship initiative.

With kind regards,

André G. McDonald, PhD, BS Law, PEng, CEng, FASM, FIMMM, FIMechE
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
        return array($subject, $message);
    }
    
    function sendMatchedMail($person){
        global $config;
        $subject = "Engineering-IBET-ELITE PhD Fellowship – Confirmation and Decision";
        $message = "Dear PhD Fellowship Supervisor,
Thank you, again, for participating in the Engineering-IBET-ELITE PhD Fellowship initiative.
 
I am writing to advise you that we have selected a candidate for the PhD position that you are offering. Please log in to the ELITE Program platform to review the application package received from the shortlisted candidate that was matched to your project. We kindly ask that you complete the following steps:

1. Please log into the platform at <a href='https://applyportal.eliteprogram.ca/index.php/Main_Page'>https://applyportal.eliteprogram.ca/index.php/Main_Page</a> with your username and password.

2. Please review the application package and interview the candidate, as needed.

3. Please indicate if you would like to hire the candidate by accepting or declining the candidate accordingly.

Please provide your decision within two (2) weeks after you receive this message.
 
Please do not hesitate to contact us should you have any questions.
 
Thank you for your interest and continued support of the Engineering-IBET-ELITE PhD Fellowship initiative.

With kind regards,

André G. McDonald, PhD, BS Law, PEng, CEng, FASM, FIMMM, FIMechE
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
        $message = nl2br($message);
        $headers  = "Content-type: text/html\r\n"; 
        $headers .= "From: {$config->getValue('siteName')} <{$config->getValue('supportEmail')}>" . "\r\n";
        mail($person->getEmail(), $subject, $message, $headers);
    }
    
    function sendHiresMail($person){
        global $config;
        $subject = "Engineering-IBET-ELITE PhD Fellowship – Feedback Received";
        $message = "Dear PhD Fellowship Supervisor,
Thank you, again, for participating in the Engineering-IBET-ELITE PhD Fellowship initiative.
 
I am writing to advise you that we have received your feedback in good order.
 
Please do not hesitate to contact us should you have any questions.
 
Thank you for your interest and continued support of the Engineering-IBET-ELITE PhD Fellowship initiative.

With kind regards,

André G. McDonald, PhD, BS Law, PEng, CEng, FASM, FIMMM, FIMechE
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
        $message = nl2br($message);
        $headers  = "Content-type: text/html\r\n"; 
        $headers .= "From: {$config->getValue('siteName')} <{$config->getValue('supportEmail')}>" . "\r\n";
        mail($person->getEmail(), $subject, $message, $headers);
    }
    
}

?>
