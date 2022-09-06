<?php

require_once('commandLine.inc');

$wgUser = User::newFromId(1);

$people = Person::getAllPeople();

$dir = dirname(__FILE__);

$reminders = @json_decode(file_get_contents("{$dir}/reminders.json"), true);
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
       !AVOIDDashboard::hasSubmittedSurvey($person->getId()) && 
       (time() - strtotime($person->getRegistration()))/86400 > 2 &&
       getReminder("AbandonedAssessment", $person)['count'] < 2 && time() - getReminder("AbandonedAssessment", $person)['time'] > 2*86400){
        addReminder("AbandonedAssessment", $person);
        $subject = "Get your personal report";
        $message = "<p>You've registered with AVOID Frailty but haven't completed the Healthy Aging Assessment. That means you're missing out on your personal report. The report will provide you with valuable information about your frailty status and give you recommendations about what you can do within the program to improve your health. Log back in <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a>, and continue where you left off. Get to the good part!</p>";
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
    $actionPlans = ActionPlan::newFromUserId($person->getId());
    foreach($actionPlans as $actionPlan){
        if(!$actionPlan->submitted &&
           getReminder("ActionPlanCheckIn{$actionPlan->getId()}", $person)['count'] < 1 &&
           strtotime($actionPlan->getDate()) + 7*86400 < time()){
            addReminder("ActionPlanCheckIn{$actionPlan->getId()}", $person);
            $subject = "Time to Submit your Action Plan";
            $message = "<p>How has your week been? Did you do what you committed to? Don't forget to sign back into your account at <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a> to submit your weekly action <a href='https://healthyagingcentres.ca/portal/index.php/Special:AVOIDDashboard'>plan</a>. It will show up in your logged accomplishments. If things didn't go as planned, that's okay too! Maybe you want to edit your weekly plan to something more attainable.</p>";
            sendMail($subject, $message, $person);
            echo "{$person->getNameForForms()} <{$person->getEmail()}>: {$subject}\n";
        }
    }
    
    // Three/Six Month reminders
    $baseLineSubmitted = AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID");
    $threeMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_THREEMO");
    $sixMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_SIXMO");
    
    $baseDiff = (time() - strtotime(AVOIDDashboard::submissionDate($person->getId(), "RP_AVOID")))/86400;
    $threeMonthDiff = (time() - strtotime(AVOIDDashboard::submissionDate($person->getId(), "RP_AVOID_THREEMO")))/86400;
    $sixMonthDiff = (time() - strtotime(AVOIDDashboard::submissionDate($person->getId(), "RP_AVOID_SIXMO")))/86400;

    if($baseLineSubmitted && !$threeMonthSubmitted && $baseDiff >= 30*3 && getReminder("3MonthReminder", $person)['count'] < 1){
        // 3 Month
        addReminder("3MonthReminder", $person);
        $subject = "Been 3 months since healthy aging assessment was completed";
        $message = "<p>Hello, It has been 3 months since you completed AVOID Frailty's Healthy Aging Assessment. We hope you are enjoying the program.  We would like to see if the program has supported you in uptaking healthy behaviours. When you have a minute, please fill in the health-related behaviours and lifestyle section of the assessment, which should take less than 5 minutes. This will also allow us to display for you, your healthy aging progress. Please log into your account at your convenience.  <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a></p>";
        sendMail($subject, $message, $person);
        echo "{$person->getNameForForms()} <{$person->getEmail()}>: {$subject}\n";
    }
    else if($threeMonthSubmitted && !$sixMonthSubmitted && $baseDiff >= 30*6 && getReminder("6MonthReminder", $person)['count'] < 1){
        // 6 Month
        /*addReminder("6MonthReminder", $person);
        $subject = "Been 6 months since healthy aging assessment was completed";
        $message = "<p>Hello, It has been 6 months since you completed AVOID Frailty's Healthy Aging Assessment. We hope you are enjoying the program.  We would like to see if the program has supported you in uptaking healthy behaviours. When you have a minute, please fill in the health-related behaviours and lifestyle section of the assessment, which should take less than 5 minutes. This will also allow us to display for you, your healthy aging progress. Please log into your account at your convenience.  <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a></p>";
        sendMail($subject, $message, $person);
        echo "{$person->getNameForForms()} <{$person->getEmail()}>: {$subject}\n";*/
    }
}

file_put_contents("{$dir}/reminders.json", json_encode($reminders));

?>
