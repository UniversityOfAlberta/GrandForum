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
    global $config, $wgServer, $wgScriptPath, $wgLang;
    $message = nl2br($message);
    $headers  = "Content-Type: text/html; charset=UTF-8\r\n"; 
    $headers .= "From: AVOID Frailty <noreply@healthyagingcentres.ca>" . "\r\n";
    $hash = hash('sha256', $person->getId()."_".$person->getRegistration());
    if($wgLang->getCode() == "en"){
        $message .= "<p><a href='$wgServer$wgScriptPath/index.php?action=api.userunsub&code={$hash}'>Click here</a> to unsubscribe from AVOID notifications.</p>";
    }
    else{
        $message .= "<p><a href='$wgServer$wgScriptPath/index.php?action=api.userunsub&code={$hash}'>Cliquez ici</a> pour vous désabonner des notifications du programme.</p>";
    }
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
        if($wgLang->getCode() == "en"){
            $subject = "Welcome to AVOID Frailty";
            $message = "<p>Welcome to the AVOID Frailty for Healthy Aging program.  Thanks for completing the healthy aging assessment.  Have you had a chance to see your frailty status and report with personalized recommendations?  If you're not sure where to start, some people have found that going through the Ingredients for Change education module is a good place.  It will help you create behaviour changing goals and build habits for healthy aging.  Everything within this program is free for you.  Tour the site and let us know if you have any questions.  We can't wait to follow your healthy aging journey!</p>";
        }
        else{
            $subject = "Bienvenue dans le programme Proactif";
            $message = "<p>Bienvenue dans le programme Proactif pour éviter la fragilisation. Merci d’avoir rempli le questionnaire. Avez-vous eu la chance de consulter votre statut de fragilité et votre rapport contenant des recommandations personnalisées? Si vous ne savez pas par où commencer, nous vous suggérons d’écouter le module éducatif « Ingrédients du changement ». Vous pourrez ainsi mieux établir vos objectifs afin de mettre en place de saines habitudes de vie, pour un vieillissement en santé. L’entièreté du programme est gratuite. Explorez le site et laissez-nous savoir si vous avez des questions. Nous avons hâte de suivre votre parcours!</p>";
        }
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
        if($wgLang->getCode() == "en"){
            $subject = "Get your personal report";
            $message = "<p>You've registered with AVOID Frailty but haven't completed the Healthy Aging Assessment. That means you're missing out on your personal report. The report will provide you with valuable information about your frailty status and give you recommendations about what you can do within the program to improve your health. Log back in <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a>, and continue where you left off. Get to the good part!</p>";
        }
        else{
            $subject = "Obtenez votre rétroaction personnalisée!";
            $message = "<p>Votre inscription au programme Proactif est complète! Toutefois, vous n’avez pas rempli le questionnaire sur le vieillissement en santé. C’est ce questionnaire qui vous permet d’obtenir une rétroaction personnalisée contenant des informations importantes sur votre niveau de fragilisation et des recommandations pour adopter de saines habitudes de vie. Connectez-vous au <a href='http://www.proactifquebec.ca'>www.proactifquebec.ca</a> et remplissez le questionnaire. Votre rétroaction personnalisée vous attend!</p>
            <img src='{$wgServer}{$wgScriptPath}/EducationModules/Proactif.affiche+guide_Page_1.jpg' style='width: 100%;' /><br />
            <img src='{$wgServer}{$wgScriptPath}/EducationModules/Proactif.affiche+guide_Page_2.jpg' style='width: 100%;' />";
        }
        sendMail($subject, $message, $person);
        echo "{$person->getNameForForms()} <{$person->getEmail()}>: {$subject}\n";
    }
    
    // Inactive registered user
    if((time() - strtotime($person->getTouched()))/86400 > 14 &&
       getReminder("InactiveUser", $person)['count'] < 2 && time() - getReminder("InactiveUser", $person)['time'] > 14*86400){
        addReminder("InactiveUser", $person);
        if($wgLang->getCode() == "en"){
            $subject = "We've missed you!";
            $message = "<p>Just checking in to see how you've been.  We don't want you to miss out on the programs and resources that other older adults in the region have been using.  Visit <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a> and be part of a growing community who is taking control of their health.</p>";
        }
        else {
            $subject = "Vous nous avez manqué!";
            $message = "<p>Comment allez-vous? Nous voulons que vous profitiez pleinement des programmes et des ressources utilisés par les autres personnes aînées de Trois-Rivières. Visitez le <a href='http://www.proactifquebec.ca'>www.proactifquebec.ca</a> et faites partie d’une communauté en plein essor qui a sa santé à cœur!</p>";
        }
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
            if($wgLang->getCode() == "en"){
                $subject = "Time to Submit your Action Plan";
                $message = "<p>How has your week been? Did you do what you committed to? Don't forget to sign back into your account at <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a> to submit your weekly action <a href='$wgServer$wgScriptPath/index.php/Special:AVOIDDashboard'>plan</a>. It will show up in your logged accomplishments. If things didn't go as planned, that's okay too! Maybe you want to edit your weekly plan to something more attainable.</p>";
            }
            else{
                $subject = "Le temps est venu de soumettre votre plan d’action";
                $message = "<p>Comment a été votre semaine? Avez-vous atteint vos objectifs? N’oubliez pas de vous connectez au <a href='http://www.proactifquebec.ca'>www.proactifquebec.ca</a> pour soumettre votre plan d’action hebdomadaire. Vous le retrouverez dans vos accomplissements. Si tout ne se passe pas comme prévu, pas de panique! C’est peut-être signe de le modifier avec des objectifs plus atteignables.</p>";
            }
            sendMail($subject, $message, $person);
            echo "{$person->getNameForForms()} <{$person->getEmail()}>: {$subject}\n";
        }
    }
    
    // Three/Six Month reminders
    $baseLineSubmitted = AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID");
    $threeMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_THREEMO");
    $sixMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_SIXMO");
    $nineMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_NINEMO");
    $twelveMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_TWELVEMO");
    
    $baseDiff = (time() - strtotime(AVOIDDashboard::submissionDate($person->getId(), "RP_AVOID")))/86400;

    if($baseLineSubmitted && !$threeMonthSubmitted && $baseDiff >= 30*3 && $baseDiff < 30*4 && getReminder("3MonthReminder", $person)['count'] < 1){
        // 3 Month
        addReminder("3MonthReminder", $person);
        if($wgLang->getCode() == "en"){
            $subject = "Been 3 months since healthy aging assessment was completed";
            $message = "<p>Hello, It has been 3 months since you completed AVOID Frailty's Healthy Aging Assessment. We hope you are enjoying the program.  We would like to see if the program has supported you in uptaking healthy behaviours. When you have a minute, please fill in the health-related behaviours and lifestyle section of the assessment, which should take less than 5 minutes. This will also allow us to display for you, your healthy aging progress. Please log into your account at your convenience.  <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a></p>";
        }
        else{
            $subject = "Déjà trois mois que vous avez soumis votre questionnaire sur la santé";
            $message = "<p>Bonjour! Il y a déjà trois mois que vous avez soumis votre questionnaire sur la santé Proactif. Nous espérons que vous appréciez le programme. Nous voulons vérifier que le programme vous soutient dans l’adoption et le maintien de saines habitudes de vie. Lorsque vous aurez quelques minutes, veuillez remplir le court questionnaire (moins de 15 minutes) en cliquant sur le lien ci-dessous. Cela nous permettra également de vous présenter votre évolution et de mettre à jour votre rapport sur votre état de fragilité. Connectez-vous à votre compte au moment qui vous convient le mieux. <a href='http://www.proactifquebec.ca'>www.proactifquebec.ca</a></p>";
        }
        sendMail($subject, $message, $person);
        echo "{$person->getNameForForms()} <{$person->getEmail()}>: {$subject}\n";
    }
    else if($baseLineSubmitted && !$sixMonthSubmitted && $baseDiff >= 30*6 && $baseDiff < 30*9 && getReminder("6MonthReminder", $person)['count'] < 1){
        // 6 Month
        addReminder("6MonthReminder", $person);
        if($wgLang->getCode() == "en"){
            $subject = "Been 6 months since healthy aging assessment was completed";
            $message = "<p>Hello, It has been 6 months since you completed AVOID Frailty's Healthy Aging Assessment. We hope you are enjoying the program.  We would like to see if the program has supported you in uptaking healthy behaviours and if that has slowed your risk of frailty. When you have a few minutes, please fill in the portion of the healthy aging assessment linked below, which should take less than 15 minutes. This will also allow us to display for you, your healthy aging progress and refresh your frailty report. Please log into your account at your convenience.  <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a></p>";
        }
        else{
            $subject = "Déjà six mois que vous avez soumis votre questionnaire sur la santé";
            $message = "<p>Bonjour! Il y a déjà six mois que vous avez soumis votre questionnaire sur la santé Proactif. Nous espérons que vous appréciez le programme. Nous voulons vérifier que le programme vous soutient dans l’adoption et le maintien de saines habitudes de vie. Lorsque vous aurez quelques minutes, veuillez remplir le court questionnaire (moins de 15 minutes) en cliquant sur le lien ci-dessous. Cela nous permettra également de vous présenter votre évolution et de mettre à jour votre rapport sur votre état de fragilité. Connectez-vous à votre compte au moment qui vous convient le mieux. <a href='http://www.proactifquebec.ca'>www.proactifquebec.ca</a></p>";
        }
        sendMail($subject, $message, $person);
        echo "{$person->getNameForForms()} <{$person->getEmail()}>: {$subject}\n";
    }
    else if($baseLineSubmitted && !$nineMonthSubmitted && $baseDiff >= 30*9 && $baseDiff < 30*10 && getReminder("9MonthReminder", $person)['count'] < 1){
        // 9 Month
        addReminder("9MonthReminder", $person);
        if($wgLang->getCode() == "en"){
            $subject = "Been 9 months since healthy aging assessment was completed";
            $message = "<p>Hello, It has been 9 months since you completed AVOID Frailty's Healthy Aging Assessment. We hope you are enjoying the program.  We would like to see if the program has supported you in uptaking healthy behaviours. When you have a minute, please fill in the health-related behaviours and lifestyle section of the assessment, which should take less than 5 minutes. This will also allow us to display for you, your healthy aging progress. Please log into your account at your convenience.  <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a></p>";
        }
        else{
            $subject = "Déjà neuf mois que vous avez soumis votre questionnaire sur la santé";
            $message = "<p>Bonjour! Il y a déjà neuf mois que vous avez soumis votre questionnaire sur la santé Proactif. Nous espérons que vous appréciez le programme. Nous voulons vérifier que le programme vous soutient dans l’adoption et le maintien de saines habitudes de vie. Lorsque vous aurez quelques minutes, veuillez remplir le court questionnaire (moins de 5 minutes) en cliquant sur le lien ci-dessous. Cela nous permettra également de vous présenter votre évolution et de mettre à jour votre rapport sur votre état de fragilité. Connectez-vous à votre compte au moment qui vous convient le mieux. <a href='http://www.proactifquebec.ca'>www.proactifquebec.ca</a></p>";
        }
        sendMail($subject, $message, $person);
        echo "{$person->getNameForForms()} <{$person->getEmail()}>: {$subject}\n";
    }
    else if($baseLineSubmitted && !$twelveMonthSubmitted && $baseDiff >= 30*12 && getReminder("12MonthReminder", $person)['count'] < 1){
        // 12 Month
        addReminder("12MonthReminder", $person);
        if($wgLang->getCode() == "en"){
            $subject = "Been 12 months since healthy aging assessment was completed";
            $message = "<p>Hello, It has been 12 months since you completed AVOID Frailty's Healthy Aging Assessment. We hope you are enjoying the program.  We would like to see if the program has supported you in uptaking healthy behaviours and if that has slowed your risk of frailty. When you have a few minutes, please fill in the portion of the healthy aging assessment linked below, which should take less than 15 minutes. This will also allow us to display for you, your healthy aging progress and refresh your frailty report. Please log into your account at your convenience.  <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a></p>";
        }
        else{
            $subject = "Déjà douze mois que vous avez soumis votre questionnaire sur la santé";
            $message = "<p>Bonjour! Il y a déjà douze mois que vous avez soumis votre questionnaire sur la santé Proactif. Nous espérons que vous appréciez le programme. Nous voulons vérifier que le programme vous soutient dans l’adoption et le maintien de saines habitudes de vie. Lorsque vous aurez quelques minutes, veuillez remplir le court questionnaire (moins de 15 minutes) en cliquant sur le lien ci-dessous. Cela nous permettra également de vous présenter votre évolution et de mettre à jour votre rapport sur votre état de fragilité. Connectez-vous à votre compte au moment qui vous convient le mieux. <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a></p>";
        }
        sendMail($subject, $message, $person);
        echo "{$person->getNameForForms()} <{$person->getEmail()}>: {$subject}\n";
    }
    
    // Three/Six Month 5-day reminders
    /*
    if($baseLineSubmitted && !$threeMonthSubmitted && $baseDiff >= 30*3 && 
       time() - getReminder("3MonthReminder", $person)['time'] > 5*86400 && 
       getReminder("3MonthReminder2", $person)['count'] < 1){
        addReminder("3MonthReminder2", $person);
        $subject = "In case you missed it...";
        $message = "<p>Keeping up with healthy habits can be hard, but we’re here to help. It’s already time to re-do a small section of the healthy aging assessment to see if you have made any behavioural changes since joining AVOID Frailty. It will also help inform our program and make improvements as well. Thank you! <a href='https://www.healthyagingcentres.ca'>www.healthyagingcentres.ca</a></p>";
        sendMail($subject, $message, $person);
        echo "{$person->getNameForForms()} <{$person->getEmail()}>: {$subject}\n";
    }
    */
}

file_put_contents("{$dir}/reminders.json", json_encode($reminders));

?>
