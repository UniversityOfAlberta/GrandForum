<?php

require_once('commandLine.inc');

$me = User::newFromId(1);

$people = Person::getAllPeople();

foreach($people as $person){
    // Completed Assessment
    if($person->isRole(CI) && AVOIDDashboard::hasSubmittedSurvey($person->getId())){
        $subject = "Welcome to AVOID Frailty";
        $message = "Welcome to the AVOID Frailty for Health Aging program. Thanks for completing the healthy aging assessment. By now, maybe you know your Frailty status and have seen your personal report, which has recommendations just for you. If you're not sure where to start, some people have found that going through the Ingredients for Change education module is a good place. It will help you create behaviour changing goals and build habits for healthy aging. Everything within this program is free for you. Tour the site and let us know if you have any questions. We can't wait to follow your healthy aging journey!";
        echo "{$person->getNameForForms()}: {$subject}\n";
    }
    
    // Abandoned healthy aging assessment
    $report = new DummyReport("IntakeSurvey", $person, null, REPORTING_YEAR, true);
    if($person->isRole(CI) && 
       $report->hasStarted() && 
       !AVOIDDashboard::hasSubmittedSurvey($person->getId()) && 
       (time() - strtotime($person->getRegistration()))/86400 > 2){
        $subject = "Get your personal report";
        $message = "You started your AVOID Frailty Healthy Aging Assessment, but didn't get your full report. The report will provide you with valuable information about your frailty status and give you personalized recommendations about what you can do within the program to improve your health. By logging in at <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a>, you will continue where you left off. Do yourself the favour, and get to the good part!";
        echo "{$person->getNameForForms()}: {$subject}\n";
    }
    
    // Inactive registered user
    if((time() - strtotime($person->getTouched()))/86400 > 14){
        $subject = "We've missed you!";
        $message = "Just checking in to see how you've been. We don't want you to miss out on the programs and resources that other older adults in KFL&A have been using. Visit <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a> and be part of a growing community who is taking control of their health.";
        echo "{$person->getNameForForms()}: {$subject}\n";
    }
    
    // Created an action plan 
    
}

?>
