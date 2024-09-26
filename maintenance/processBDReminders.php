<?php

require_once('commandLine.inc');

$wgUser = User::newFromId(1);

function sendMail($subject, $message, $to){
    global $config, $wgServer, $wgScriptPath, $wgLang;
    $message = nl2br($message);
    $headers  = "Content-Type: text/html; charset=UTF-8\r\n"; 
    $headers .= "From: BD Forum <support@forum.bridgingdivides.ca>" . "\r\n";

    mail($to, $subject, $message, $headers);
}

$year = date('Y');
$today = date('Y-m-d');
$future = date('m-d', time() + 86400*7);
$futureDate = date('F j, Y', time() + 86400*7);

$projectQ = Report::dateToProjectQuarter($today);
$themeQ = Report::dateToThemeQuarter($today);

// Project Reports
if($future == "02-15" ||
   $future == "06-15" ||
   $future == "10-15"){
    $message = "Dear Project Leads,

                This is a kind reminder to submit your <b>".str_replace("Q", "R", str_replace("_", ":", $projectQ))." project report(s) by {$futureDate}</b> via the <a href='https://forum.bridgingdivides.ca/index.php/Main_Page'>Bridging Divides Forum</a>. Instructions on how to navigate the Forum and submit reports can be found <a href='https://www.torontomu.ca/bridging-divides/documents/manuals/bd-forum-instructions.pdf'>here</a>. Please note that questions regarding Knowledge mobilization and community engagement and Changes in the research teams have been added to the reporting form.
                
                If you need further assistance, please contact <a href='skornicer@torontomu.ca'>Sanja Kornicer</a> or <a href='mailto:bridging.divides@torontomu.ca'>bridging.divides@torontomu.ca</a>.

                Warm regards,

                The Bridging Divides Team";
    sendMail("Project Reports Due", $message, "bd-leaders@forum.bridgingdivides.ca");
}

// Theme Reports
if($future == "03-15" ||
   $future == "07-15" ||
   $future == "11-15"){
    $message = "Dear Theme Leads,

                This is a kind reminder to submit your <b>".str_replace("Q", "R", str_replace("_", ":", $themeQ))." theme reports by {$futureDate}</b> via the <a href='https://forum.bridgingdivides.ca/index.php/Main_Page'>Bridging Divides Forum</a>. Instructions on how to navigate the Forum and submit reports can be found <a href='https://www.torontomu.ca/bridging-divides/documents/manuals/bd-forum-instructions.pdf'>here</a>.  Please note that questions regarding Knowledge mobilization and community engagement and Changes in the research teams have been added to the reporting form.
                
                If you need further assistance, please contact <a href='skornicer@torontomu.ca'>Sanja Kornicer</a> or <a href='mailto:bridging.divides@torontomu.ca'>bridging.divides@torontomu.ca</a>.

                Warm regards,

                The Bridging Divides Team";
    sendMail("Theme Reports Due", $message, "bd-themeleaders@forum.bridgingdivides.ca");
}

function getBlobValue($item, $person){
    $blob = new ReportBlob(BLOB_TEXT, 0, $person->getId(), 0);
    $blob_address = ReportBlob::create_address("RP_SELF_IDENTIFICATION", "FORM", $item, 0);
    $blob->load($blob_address);
    return $blob->getData();
}

$people = Person::getAllPeople();

foreach($people as $person){
    $submitted = (getBlobValue("SUBMITTED", $person) >= (date('Y') - 1).date('-m-d'));
    $skipped = (getBlobValue("SKIPPED", $person) == "SKIP");
    if(($person->getRegistration() <= "2024-03-31" || count($person->getRolesDuring("2023-04-01", "2024-03-31")) > 0) && !$submitted && !$skipped){
        $candidate = urlencode(encrypt($person->getId()));
        $url = "https://forum.bridgingdivides.ca/index.php/Special:Report?report=SelfIdentification&candidate={$candidate}";
        $message = "";
        if(date('Y-m-d') == "2024-09-13"){
            // First Email
            $message = "Dear Bridging Divides Member,
            
                As part of the annual Bridging Divides (BD) progress reporting requirements, the Tri-agency Institutional Programs Secretariat (TIPS) requires all students or HQP, researchers, staff and collaborators who joined the program in the fiscal year April 1, 2023 – March 31, 2024 to complete a Self-Identification survey. The survey is operated by Bridging Divides and can be accessed via the BD Forum platform. A “prefer not to answer” option is available for each question, as well as an option to opt-out of the survey altogether.
                
                We kindly ask that you complete or opt out of the survey by <b>Monday, September 30, 2024</b>.

                To access the survey, please follow the unique link below. This link is available to you only and should not be shared. As an extra measure to protect your information, <b>you will not be able to open the link while logged in to the Forum</b>. If you have a Forum account, please log out or open the link in an incognito/private browsing window.
                
                <a href='{$url}'>Survey Link</a>

                Your information is anonymized and used exclusively to produce aggregate data. If you have questions about the collection, use and disclosure of this information please contact <a href='mailto:bridging.divides@torontomu.ca'>bridging.divides@torontomu.ca</a>. 
                Thank you in advance for your collaboration.

                Kind regards,

                The Bridging Divides Team";
        }
        else if(date('Y-m-d') == "2024-09-18" || 
                date('Y-m-d') == "2024-09-25"){
            // Second & Third Email
            $message = "Dear Bridging Divides Member,
            
                This is a kind reminder to either complete or opt out of the Self-Identification Survey conducted by Bridging Divides. As part of our annual progress reporting requirements, all team members who joined during the fiscal year April 1, 2023 – March 31, 2024, are invited to submit their responses. <b>The survey takes less than 3 minutes to complete.</b>

                Please complete or opt out of the survey by <b>Monday, September 30, 2024</b>, using this unique <a href='{$url}'>link</a>. <b>The link will not work if you are logged in to the Forum</b>. If you have questions about the collection, use, or disclosure of this information, please contact <a href='mailto:bridging.divides@torontomu.ca'>bridging.divides@torontomu.ca</a>.

                Kind regards,

                The Bridging Divides Team";
        }
        
        if($message != ""){
            sendMail("Self-Identification Survey", $message, "{$person->getEmail()}");
            echo "{$person->getName()}\n";
        }
    }
}

?>
