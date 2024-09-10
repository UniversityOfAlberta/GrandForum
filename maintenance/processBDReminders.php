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
    $message = "<p>Dear Project Leads,</p>

                <p>This is a kind reminder to submit your ".str_replace("Q", "R", str_replace("_", ":", $projectQ))." project report(s) by {$futureDate} via the <a href='https://forum.bridgingdivides.ca/index.php/Main_Page'>Bridging Divides Forum</a>. Instructions on how to navigate the Forum and submit reports can be found <a href='https://www.torontomu.ca/bridging-divides/documents/manuals/bd-forum-instructions.pdf'>here</a>. If you need further assistance, please contact <a href='mailto:ldellapietra@torontomu.ca'>Luisa Della Pietra</a> or <a href='mailto:bridging.divides@torontomu.ca'>bridging.divides@torontomu.ca</a>.</p>

                <p>Warm regards,</p>

                <p>The Bridging Divides Team</p>";
    mail("Project Reports Due", $message, "bd-leaders@forum.bridgingdivides.ca");
}

// Theme Reports
if($future == "03-15" ||
   $future == "07-15" ||
   $future == "11-15"){
    $message = "<p>Dear Theme Leads,</p>

                <p>This is a kind reminder to submit your ".str_replace("Q", "R", str_replace("_", ":", $themeQ))." theme reports by {$futureDate} via the <a href='https://forum.bridgingdivides.ca/index.php/Main_Page'>Bridging Divides Forum</a>. Instructions on how to navigate the Forum and submit reports can be found <a href='https://www.torontomu.ca/bridging-divides/documents/manuals/bd-forum-instructions.pdf'>here</a>. If you need further assistance, please contact <a href='mailto:ldellapietra@torontomu.ca'>Luisa Della Pietra</a> or <a href='mailto:bridging.divides@torontomu.ca'>bridging.divides@torontomu.ca</a>.</p>

                <p>Warm regards,</p>

                <p>The Bridging Divides Team</p>";
    mail("Theme Reports Due", $message, "bd-themeleaders@forum.bridgingdivides.ca");
}

?>
