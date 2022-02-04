<?php

/**
 * @package GrandObjects
 */

class InternEliteProfile extends EliteProfile {
    
    static $rpType = "ELITE";
    
    function acceptedMessage(){
        $subject = "ELITE Program for Black Youth - Decision";
        $message = "Dear Intern Candidate,
Thank you for submitting your application for an internship through the Experiential Learning in Innovation, Technology, and Entrepreneurship (ELITE) Program for Black Youth. After a review, adjudication, and interview process, which involved the internship hosts, we are pleased to advise that you have been selected to participate in the ELITE Program for Black Youth. Congratulations!

Please see attached a detailed memorandum with additional information and instructions. Please address the action items in the document that were directed to you and respond by the date indicated in the memorandum.

Thank you for your interest and continued support of the ELITE Program for Black Youth. We look forward to working with you.
 
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
        $subject = "ELITE Program for Black Youth – Update On Next Steps";
        $message = "Dear Intern Candidate,
Thank you for your submission to the paid work-integrated internship program offered by the Experiential Learning in Innovation, Technology, and Entrepreneurship (ELITE) Program for Black Youth. We are writing to inform you that you have been shortlisted through our initial screening process. Your application has been advanced to the second phase of review.
 
As part of the second phase, you may be contacted by an internship host for an interview. We kindly ask you to promptly respond to those requests and work with the hosts to arrange the interview(s) at your earliest opportunity, should they contact you. 

Congratulations on your progress in the review process. In the interim, please do not hesitate to contact us should you have any questions.
 
Thank you for your interest and continued support of the ELITE Program for Black Youth.

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
    
    function moreInfoMessage(){
        $subject = "";
        $message = "";
        return array($subject, $message);
    }
    
    function rejectedMessage(){
        $subject = "ELITE Program for Black Youth - Decision";
        $message = "Dear Intern Candidate,
Thank you for submitting your application for an internship through the Experiential Learning in Innovation, Technology, and Entrepreneurship (ELITE) Program for Black Youth.

We received many applications for only 50 to 60 positions in this year's cohort. After a thorough review and adjudication process, we regret to inform you that your application was not selected to move forward for a position in the Program. While this news is unfortunate, we hope that you will not be discouraged, and will apply again next year.

Thank you for your interest and continued support of the ELITE Program for Black Youth.
 
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
        $subject = "ELITE Program for Black Youth - Confirmation of Receipt of Submission";
        $message = "Dear Intern Candidate,
Thank you for your submission to the paid work-integrated internship program offered by the Experiential Learning in Innovation, Technology, and Entrepreneurship (ELITE) Program for Black Youth. This message confirms that we received an application from you.

We will begin to review applications shortly and will arrange for interviews for those who have been short-listed. We hope to have the process completed within the next 3 to 6 weeks.

Please note that we have received more applications than internship positions available. As a result, not all applicants will receive an offer. We will work as expeditiously as possible to advise you of the outcome so that you will all be able to plan accordingly.

In the interim, please do not hesitate to contact us should you have any questions.

Thank you for your interest and continued support of the ELITE Program for Black Youth.

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
        $subject = "ELITE Program for Black Youth – Request for Interview and Decision";
        $message = "Dear Internship Host Applicant,
Thank you, again, for offering a position in the paid work-integrated internship program offered by the Experiential Learning in Innovation, Technology, and Entrepreneurship (ELITE) Program for Black Youth.
 
I am writing to advise you that we have selected an initial candidate for the position that you are offering. Please log in to the ELITE Program platform to review the application package received from the shortlisted intern candidate that was matched to your project. We kindly ask that you complete the following steps:

1. Please log into the platform at <a href='https://applyportal.eliteprogram.ca/index.php/Main_Page'>https://applyportal.eliteprogram.ca/index.php/Main_Page</a> with your username and password.

2. Please review the application package and interview the candidate, as needed.

3. Please indicate which intern candidates you would like to hire, if any, by accepting or declining the candidates accordingly.

Please provide your decision within two (2) weeks after you receive this message.
 
Please do not hesitate to contact us should you have any questions.
 
Thank you for your interest and continued support of the ELITE Program for Black Youth.

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
        $subject = "ELITE Program for Black Youth – Feedback Received";
        $message = "Dear Internship Host,
Thank you, again, for offering a position in the paid work-integrated internship program offered by the Experiential Learning in Innovation, Technology, and Entrepreneurship (ELITE) Program for Black Youth.
 
I am writing to advise you that we have received your feedback in good order.
 
Please do not hesitate to contact us should you have any questions.
 
Thank you for your interest and continued support of the ELITE Program for Black Youth.

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
