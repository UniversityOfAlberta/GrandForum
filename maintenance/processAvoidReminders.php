<?php

require_once('commandLine.inc');

$wgUser = User::newFromId(1);

$people = Person::getAllPeople();

$reminders = @json_decode(file_get_contents("reminders.json"), true);
if($reminders == null){
    $reminders = array();
}

function getReminder($id, $person){
    global $reminders;
    if(!isset($reminders["{$id}_{$person->getId()}"])){
        return array('count' => 0, 'time' => 0);
    }
    return $reminders["{$id}_{$person->getId()}"];
}

function addReminder($id, $person){
    global $reminders;
    if(!isset($reminders["{$id}_{$person->getId()}"])){
        $reminders["{$id}_{$person->getId()}"] = array('count' => 0, 'time' => 0);
    }
    $reminders["{$id}_{$person->getId()}"]['count']++;
    $reminders["{$id}_{$person->getId()}"]['time'] = time();
}

function sendMail($subject, $message, $person){
    global $config;
    $message = nl2br($message);
    $headers  = "Content-type: text/html\r\n"; 
    $headers .= "From: AVOID Frailty <noreply@healthyagingcentres.ca>" . "\r\n";
    $hash = hash('sha256', $person->getId()."_".$person->getRegistration());
    
    $message .= "<p><a href='https://healthyagingcentres.ca/portal/index.php?action=api.userunsub&code={$hash}'>Click here</a> to unsubscribe from AVOID notifications.</p>"; 
    mail($person->getEmail(), $subject, $message, $headers);
}

foreach($people as $person){
    $data = DBFunctions::select(array('wikidev_unsubs'),
                                array('*'),
                                array('user_id' => $person->getId(),
                                      'project_id' => 0));
    if(count($data) > 0){
        continue;
    }
    // Completed Assessment
    if($person->isRole(CI) && AVOIDDashboard::hasSubmittedSurvey($person->getId()) && getReminder("CompletedAssessment", $person)['count'] < 1){
        addReminder("CompletedAssessment", $person);
        $subject = "Welcome to AVOID Frailty";
        $message = "<p>Welcome to the AVOID Frailty for Healthy Aging program.  Thanks for completing the healthy aging assessment.  Have you had a chance to see your frailty status and report with personalized recommendations?  If you're not sure where to start, some people have found that going through the Ingredients for Change education module is a good place.  It will help you create behaviour changing goals and build habits for healthy aging.  Everything within this program is free for you.  Tour the site and let us know if you have any questions.  We can't wait to follow your healthy aging journey!</p>";
        sendMail($subject, $message, $person);
        echo "{$person->getNameForForms()} <{$person->getEmail()}>: {$subject}\n";
    }
    
    // Abandoned healthy aging assessment
    $report = new DummyReport("IntakeSurvey", $person, null, REPORTING_YEAR, true);
    if($person->isRole(CI) && 
       $report->hasStarted() && 
       !AVOIDDashboard::hasSubmittedSurvey($person->getId()) && 
       (time() - strtotime($person->getRegistration()))/86400 > 2 &&
       getReminder("AbandonedAssessment", $person)['count'] < 2 && time() - getReminder("AbandonedAssessment", $person)['time'] > 2*86400){
        addReminder("AbandonedAssessment", $person);
        $subject = "Get your personal report";
        $message = "<p>You started your AVOID Frailty Healthy Aging Assessment, but didn't get your full report.  The report will provide you with valuable information about your frailty status and give you personalized recommendations about what you can do within the program to improve your health.  By logging in at <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a>, you will continue where you left off.  Do yourself the favour, and get to the good part!</p>";
        sendMail($subject, $message, $person);
        echo "{$person->getNameForForms()} <{$person->getEmail()}>: {$subject}\n";
    }
    
    // Inactive registered user
    if((time() - strtotime($person->getTouched()))/86400 > 14 &&
       getReminder("InactiveUser", $person)['count'] < 2 && time() - getReminder("InactiveUser", $person)['time'] > 14*86400){
        addReminder("InactiveUser", $person);
        $subject = "We've missed you!";
        $message = "<p>Just checking in to see how you've been.  We don't want you to miss out on the programs and resources that other older adults in KFL&A have been using.  Visit <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a> and be part of a growing community who is taking control of their health.</p>";
        sendMail($subject, $message, $person);
        echo "{$person->getNameForForms()} <{$person->getEmail()}>: {$subject}\n";
    }
    
    // Created an action plan 
    
}

file_put_contents("reminders.json", json_encode($reminders));

?>
