<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AVOIDDashboard'] = 'AVOIDDashboard'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AVOIDDashboard'] = $dir . 'AVOIDDashboard.i18n.php';
$wgSpecialPageGroups['AVOIDDashboard'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'AVOIDDashboard::createTab';
$wgHooks['SubLevelTabs'][] = 'AVOIDDashboard::createSubTabs';
$wgHooks['BeforePageDisplay'][] = 'AVOIDDashboard::processPage';

function runDashboard($par) {
    AVOIDDashboard::execute($par);
}

class AVOIDDashboard extends SpecialPage {
    
    function __construct() {
        SpecialPage::__construct("AVOIDDashboard", null, true, 'runDashboard');
    }
    
    static function permissionError(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        if(!$wgUser->isLoggedIn()){
            $wgOut->setPageTitle("Please Login");
            $wgOut->addHTML("<div class='program-body'>In order to view this page you must login.  Return to the <a href='$wgServer$wgScriptPath/index.php/Main_Page'>front page</a> to login.</div>");
            $wgOut->output();
            $wgOut->disable();
            exit;
        }
        permissionError();
    }
    
    function userCanExecute($user){
	    if(!$user->isLoggedIn()){
	        AVOIDDashboard::permissionError();
	    }
        return true;
	}
    
    function compareTags($tags1, $tags2){
        $found = 0;
        foreach($tags1 as $tag1){
            if(in_array($tag1, $tags2)){
                $found++;
            }
        }
        return $found;
    }
    
    function sort($objs, $tags){
        usort($objs, function($a, $b) use($tags) {
            return ($this->compareTags($a->tags, $tags) < $this->compareTags($b->tags, $tags));
        });
        return $objs;
    }
    
    static function executeFitBitAPI($url){
        global $wgMessage;
        $me = Person::newFromWgUser();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer {$me->getExtra('fitbit')}"
        ));
        
        //execute post
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if($info['http_code'] != 200){
            throw new Exception("There was an error fetching your fitbit data.  Make sure that you have authorized AVOID to access your fitbit account.");
        }
        return json_decode($result, true);
    }
    
    static function importFitBit(){
	    global $wgMessage, $config, $wgServer, $wgScriptPath, $wgUser;
	    $me = Person::newFromWgUser();
	    $actionPlans = ActionPlan::newFromUserId($me->getId());
	    $actionPlan = @$actionPlans[0];
        if($actionPlan != null && 
           $actionPlan->getType() == "Fitbit Monitoring" &&
           !$actionPlan->getSubmitted() && 
           $me->getExtra('fitbit') != "" &&
           !isset($_COOKIE['lastfitbit'])){
            // Steps
            $startDate = $actionPlan->getStartDate();
            $endDate = $actionPlan->getEndDate();
            $fitbit = $actionPlan->getFitbit();
            try {
                $days = array(
                    $actionPlan->getMon() => "Mon",
                    $actionPlan->getTue() => "Tue",
                    $actionPlan->getWed() => "Wed",
                    $actionPlan->getThu() => "Thu",
                    $actionPlan->getFri() => "Fri",
                    $actionPlan->getSat() => "Sat",
                    $actionPlan->getSun() => "Sun"
                );
                $tracker = array(
                    "Mon" => true,
                    "Tue" => true,
                    "Wed" => true,
                    "Thu" => true,
                    "Fri" => true,
                    "Sat" => true,
                    "Sun" => true);
                if(isset($fitbit->steps)){
                    // Steps
                    $data = self::executeFitBitAPI("https://api.fitbit.com/1/user/-/activities/steps/date/{$startDate}/{$endDate}.json");
                    foreach($data['activities-steps'] as $value){
                        $tracker[$days[$value['dateTime']]] = $tracker[$days[$value['dateTime']]] && (intval($value['value']) >= $fitbit->steps);
                    }
                }
                if(isset($fitbit->distance)){
                    // Distance
                    $data = self::executeFitBitAPI("https://api.fitbit.com/1/user/-/activities/distance/date/{$startDate}/{$endDate}.json");
                    foreach($data['activities-distance'] as $value){
                        $tracker[$days[$value['dateTime']]] = $tracker[$days[$value['dateTime']]] && (intval($value['value']) >= $fitbit->distance);
                    }
                }
                if(isset($fitbit->activity)){
                    // Minutes Active
                    $data1 = self::executeFitBitAPI("https://api.fitbit.com/1/user/-/activities/minutesLightlyActive/date/{$startDate}/{$endDate}.json");
                    $data2 = self::executeFitBitAPI("https://api.fitbit.com/1/user/-/activities/minutesFairlyActive/date/{$startDate}/{$endDate}.json");
                    $data3 = self::executeFitBitAPI("https://api.fitbit.com/1/user/-/activities/minutesVeryActive/date/{$startDate}/{$endDate}.json");
                    
                    $activity = array();
                    foreach(array_merge($data1['activities-minutesLightlyActive'], 
                                        $data2['activities-minutesFairlyActive'],
                                        $data3['activities-minutesVeryActive']) as $value){
                        @$activity[$value['dateTime']] += intval($value['value']);
                    }
                    
                    foreach($activity as $date => $active){
                        $tracker[$days[$date]] = $tracker[$days[$date]] && ($active >= $fitbit->activity);
                    }
                }
                if(isset($fitbit->sleep)){
                    // Sleep
                    $data = self::executeFitBitAPI("https://api.fitbit.com/1.2/user/-/sleep/date/{$startDate}/{$endDate}.json");
                    $sleeps = array($actionPlan->getMon() => 0,
                                    $actionPlan->getTue() => 0,
                                    $actionPlan->getWed() => 0,
                                    $actionPlan->getThu() => 0,
                                    $actionPlan->getFri() => 0,
                                    $actionPlan->getSat() => 0,
                                    $actionPlan->getSun() => 0);
                    foreach($data['sleep'] as $value){
                        $date = $value['dateOfSleep'];
                        $duration = $value['duration'];
                        $sleeps[$date] += $value['duration']/1000/60/60;
                    }
                    foreach($sleeps as $date => $duration){
                        $tracker[$days[$date]] = $tracker[$days[$date]] && ($duration >= $fitbit->sleep);
                    }
                }
                if(isset($fitbit->water)){
                    // Water
                    $data = self::executeFitBitAPI("https://api.fitbit.com/1/user/-/foods/log/water/date/{$startDate}/{$endDate}.json");
                    foreach($data['foods-log-water'] as $value){
                        $tracker[$days[$value['dateTime']]] = $tracker[$days[$value['dateTime']]] && (intval($value['value']) >= $fitbit->water);
                    }
                }
                if(isset($fitbit->fibre) || isset($fitbit->protein)){
                    // Fibre & Protein
                    $data1 = self::executeFitBitAPI("https://api.fitbit.com/1/user/-/foods/log/date/{$actionPlan->getMon()}.json");
                    $data2 = self::executeFitBitAPI("https://api.fitbit.com/1/user/-/foods/log/date/{$actionPlan->getTue()}.json");
                    $data3 = self::executeFitBitAPI("https://api.fitbit.com/1/user/-/foods/log/date/{$actionPlan->getWed()}.json");
                    $data4 = self::executeFitBitAPI("https://api.fitbit.com/1/user/-/foods/log/date/{$actionPlan->getThu()}.json");
                    $data5 = self::executeFitBitAPI("https://api.fitbit.com/1/user/-/foods/log/date/{$actionPlan->getFri()}.json");
                    $data6 = self::executeFitBitAPI("https://api.fitbit.com/1/user/-/foods/log/date/{$actionPlan->getSat()}.json");
                    $data7 = self::executeFitBitAPI("https://api.fitbit.com/1/user/-/foods/log/date/{$actionPlan->getSun()}.json");
                    
                    if(isset($fitbit->fibre)){
                        // Fibre
                        $tracker["Mon"] = $tracker["Mon"] && (floatval($data1['summary']['fiber']) >= $fitbit->fibre);
                        $tracker["Tue"] = $tracker["Tue"] && (floatval($data2['summary']['fiber']) >= $fitbit->fibre);
                        $tracker["Wed"] = $tracker["Wed"] && (floatval($data3['summary']['fiber']) >= $fitbit->fibre);
                        $tracker["Thu"] = $tracker["Thu"] && (floatval($data4['summary']['fiber']) >= $fitbit->fibre);
                        $tracker["Fri"] = $tracker["Fri"] && (floatval($data5['summary']['fiber']) >= $fitbit->fibre);
                        $tracker["Sat"] = $tracker["Sat"] && (floatval($data6['summary']['fiber']) >= $fitbit->fibre);
                        $tracker["Sun"] = $tracker["Sun"] && (floatval($data7['summary']['fiber']) >= $fitbit->fibre);
                    }
                    if(isset($fitbit->protein)){
                        // Protein
                        $tracker["Mon"] = $tracker["Mon"] && (floatval($data1['summary']['protein']) >= $fitbit->protein);
                        $tracker["Tue"] = $tracker["Tue"] && (floatval($data2['summary']['protein']) >= $fitbit->protein);
                        $tracker["Wed"] = $tracker["Wed"] && (floatval($data3['summary']['protein']) >= $fitbit->protein);
                        $tracker["Thu"] = $tracker["Thu"] && (floatval($data4['summary']['protein']) >= $fitbit->protein);
                        $tracker["Fri"] = $tracker["Fri"] && (floatval($data5['summary']['protein']) >= $fitbit->protein);
                        $tracker["Sat"] = $tracker["Sat"] && (floatval($data6['summary']['protein']) >= $fitbit->protein);
                        $tracker["Sun"] = $tracker["Sun"] && (floatval($data7['summary']['protein']) >= $fitbit->protein);
                    }
                }
                $actionPlan->tracker = $tracker;
                $actionPlan->update();
                setcookie('lastfitbit', time(), time()+60*10); // Don't fetch again for another 10min
            } catch (Exception $e) {
                $wgMessage->addError($e->getMessage());
                return;
            }
        }
	}
    
    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath, $wgLang, $config;
        if(isset($_GET['fitbitApi'])){
            $me = Person::newFromWgUser();
            if(isset($_GET['disable']) || isset($_GET['token'])){
                $extra = $me->getExtra();
                $extra['fitbit'] = @"{$_GET['token']}";
                $extra['fitbit_expires'] = time() + intval(@$_GET['expires_in']);
                $me->extra = $extra;
                $me->update();
            }
            else{
                echo "<html><script type='text/javascript'>
                    var search = new URLSearchParams(document.location.hash.replace('#', ''));
                    var scope = search.get('scope');
                    if(typeof scope == 'undefined'){
                        scope = '';
                    }
                    if(search.get('access_token') != null && scope.indexOf('heartrate') !== -1 &&
                                                             scope.indexOf('nutrition') !== -1 &&
                                                             scope.indexOf('sleep') !== -1 &&
                                                             scope.indexOf('activity') !== -1){
                        document.location = '{$wgServer}{$wgScriptPath}/index.php/Special:AVOIDDashboard?fitbitApi&token=' + search.get('access_token') + '&expires_in=' + search.get('expires_in');
                    }
                </script></html>";
            }
            echo "<html><script type='text/javascript'>
                window.close();
            </script></html>";
            exit;
        }
        self::importFitBit();
        $dir = dirname(__FILE__) . '/';
        $me = Person::newFromWgUser();
        $_GET['id'] = $me->getId();
        $tags = (new UserTagsAPI())->getTags($me->getId());
        
        $membersOnly = ($me->isRole("Provider")) ? "members-only" : "";
        /*
        $modules = EducationResources::JSON();
        $complete = array();
        $inProgress = array();
        foreach($modules as $module){
            $completion = EducationResources::completion($module->id);
            $text = "<li><a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/{$module->id}'>".showLanguage($module->title, $module->titleFr)."</a></li>";
            if($completion == 100){
                $complete[] = $text;
            }
            else if($completion > 0){
                $inProgress[] = $text;
            }
        }*/
        
        if($wgLang->getCode() == 'en'){
            $wgOut->setPageTitle("My Profile");
        }
        else{
            $wgOut->setPageTitle("Mon Profil");
        }
        $wgOut->addHTML("<div class='modules'>");
        
        // Frailty Status
        if(AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID_TWELVEMO")){
            $reportType = "RP_AVOID_TWELVEMO";
        }
        else if(AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID_SIXMO")){
            $reportType = "RP_AVOID_SIXMO";
        }
        else {
            $reportType = "RP_AVOID";
        }
        $api = new UserFrailtyIndexAPI();
        $scores = $api->getFrailtyScore($me->getId(), $reportType);
        $score = $scores["Total"];
        $label = $scores["Label"];
        $frailty = "";
        if($label == "very low risk"){ 
            $frailty = "<en>Based on your answers in the assessment, you have a <span style='color: white; background: green; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$label}</span> for frailty.  Find out why below.</en>
                        <fr>Sur la base de vos réponses à l’évaluation, vous avez un <span style='color: white; background: green; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$scores["LabelFr"]}</span> de fragilisation.</fr>
";
        }
        else if($label == "low risk"){
            $frailty = "<en>Based on your answers in the assessment, you have a <span style='color: black; background: #F6BE00; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$label}</span> for frailty.  Find out why below.</en>
                        <fr>Sur la base de vos réponses à l’évaluation, vous avez un <span style='color: black; background: #F6BE00; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$scores["LabelFr"]}</span> de fragilisation.</fr>
";
        }
        else if($label == "medium risk"){
            $frailty = "<en>Based on your answers in the assessment, you have a <span style='color: black; background: orange; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$label}</span> for frailty.  Find out why below.</en>
                        <fr>Sur la base de vos réponses à l’évaluation, vous avez un <span style='color: black; background: orange; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$scores["LabelFr"]}</span> de fragilisation.</fr>
";
        }
        else if($label == "high risk"){
            $frailty = "<en>Based on your answers in the assessment, you have a <span style='color: white; background: #CC0000; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$label}</span> for frailty.  Find out why below.</en>
                        <fr>Sur la base de vos réponses à l’évaluation, vous avez un <span style='color: white; background: #CC0000; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$scores["LabelFr"]}</span> de fragilisation.</fr>";
        }
        
        $progressReport = (AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID_THREEMO") ||
                           AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID_SIXMO")) ? "<br /><a id='viewProgressReport' href='#'>My Recent Mini Assessment Results</a>" : "";

        $assessmentReport = "";
        if(class_exists("InPersonFollowup") && AVOIDDashboard::isPersonAssessmentDone($me->getId())){
            // This might be a bit slow, but unlikely to be noticable.  If it becomes a problem, it should be moved to an ajax/api request
            $content = urldecode(InPersonFollowup::getContent($me));
            $wgOut->addHTML("<div id='assessmentDialog' title='In Person Assessment' style='display:none;'>{$content}</div>");
            /*$wgOut->addHTML("<form style='display:none;' action='{$wgServer}{$wgScriptPath}/index.php?action=api.DownloadWordHtmlApi' enctype='multipart/form-data' id='downloadword' method='post' target='_blank'><input type='hidden' name='content' value='{$content}'><input type='hidden' name='filename' value='{$me->getNameForForms()} In-Person Assessment Download'><input id='downloadWord' type='submit' style='display:none;' value='Download Word'></form>");*/
            $assessmentReport = "<br /><a id='viewAssessmentReport' href='#'>In-Person Frailty Report</a>";
        }
        if($membersOnly == ""){
            // Member Frailty Status
            $facebookLink = ($config->getValue('networkFullName') != "AVOID Australia") 
                          ? "<img src='{$wgServer}{$wgScriptPath}/skins/icons/avoid/glyphicons_social_30_facebook.png' />
                             <span style='display:inline-block; vertical-align: text-top; width: calc(100% - 32px);'>Join the discussion in the <a target='_blank' href='https://www.facebook.com/groups/1751174705081179/'>member's only facebook group</a></span>" 
                          : "";
            $gamificationLink = ($config->getValue('gamificationEnabled')) 
                              ? "<img src='{$wgServer}{$wgScriptPath}/skins/goldstar.png' style='height:24px;' />
                                 <span style='display:inline-block; vertical-align: text-top; width: calc(100% - 32px);'>
                                    <span style='float: right;border: 2px solid {$config->getValue("hyperlinkColor")};border-radius: 10px;padding: 5px;text-align: center;'>
                                        <span style='font-weight: bold;'>My Points<br /></span>
                                        <span style='font-size: 3em; line-height: 1em;'>".Gamification::calculatePoints($me)."</span>
                                    </span>
                                    <a href='https://healthyagingcentres.ca/awards/'>Healthy Lifestyle Rewards</a><br />
                                    
                                    <span>Earn points with a team, for using the program. Compete for prizes and bragging rights!</span>
                                 </span>"
                              : "";
            $wgOut->addHTML("<div class='$membersOnly modules module-2cols-outer'>
                                <h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'><en>My Frailty Status</en><fr>Mon état de fragilité</fr></h1>
                                <div class='program-body' style='width: 100%;'>
                                    <p style='margin-bottom:0.5em;'>
                                        {$frailty}<br />
                                    </p>
                                    <a class='viewReport' href='#'><img src='{$wgServer}{$wgScriptPath}/skins/report.png' style='height:3.5em;max-height:100px;margin-right:0.5em;' /></a>
                                    <div style='display:inline-block;vertical-align:middle;'>
                                        <a class='viewReport' data-href='{$wgServer}{$wgScriptPath}/index.php/Special:FrailtyReport?preview&reportType={$reportType}' href='#'>
                                            <en>My Recent Full Assessment Results</en>
                                            <fr>Mon rapport personnel et mes recommandations</fr>
                                        </a>
                                        {$progressReport}
                                        {$assessmentReport}
                                    </div>
                                    <p style='margin-bottom:0.5em;'>
                                        <a href='{$wgServer}{$wgScriptPath}/EducationModules/What is Frailty-".strtoupper($wgLang->getCode()).".pdf' target='_blank'>
                                            <en>What is Frailty?</en>
                                            <fr>Qu’est-ce que la fragilité?</fr>
                                        </a>
                                        <en>
                                            | 
                                            <a href='https://www.youtube.com/watch?v=tzyYBp1v1WI&list=PLR7yWL6rqm9z9qi4VLAhNtUUBOuOG7qHE' target='_blank'>Frailty: Ask the Expert</a>
                                        </en>
                                        <br />
                                    </p>
                                    <p style='margin-bottom: 0;'>
                                        <b><en>What’s Next?</en><fr>Utilisation du programme</fr></b><br />
                                        <en>Frailty is preventable, and small changes make a difference. Address some of your risks using the resources found in this program.</en>
                                        <fr>Étape 1. Consultez votre rapport personnel ci-dessus pour connaître vos risques et les recommandations à suivre.<br /></fr>
                                        <fr>Étape 2. Utilisez le modèle de plan d’action ci-dessous pour choisir un changement sain et en faire le suivi <b>cette semaine</b><br /></fr>
                                        <fr>Étape 3. Utilisez les modules d’éducation, les programmes et les ressources pour soutenir vos objectifs de vieillissement sain.</fr>
                                    </p>

                                    <div>
                                        <en>
                                            <div class='modules' style='margin-top: 0.5em;'>
                                                <div class='module-2cols-outer'>{$facebookLink}</div>
                                                <div class='module-2cols-outer' style='width:50%;'>{$gamificationLink}</div>
                                            </div>
                                        </en>
                                    </div>
                                </div>
                             </div>");
        }
        else{
            // Clinician 
            $wgOut->addHTML("<div class='modules module-2cols-outer'>
                                <h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'><en>My Toolkit</en><fr>Ma boîte à outils</fr></h1>
                                <div class='program-body' style='width: 100%;'>
                                    <div class='modules'>
                                        <div class='module-2cols-outer'>
                                            <b><en>Resources for Clinic</en><fr>Ressources pour la clinique</fr></b>
                                            <ul>
                                                <li><a href='{$wgServer}{$wgScriptPath}/EducationModules/Clinician/AVOID Frailty Logo.jpg'>AVOID Frailty Logo</a></li>
                                                <li><a href='{$wgServer}{$wgScriptPath}/EducationModules/Clinician/AVOID Frailty Rack Card.pdf'>AVOID Frailty Rack Card</a></li>
                                                <li><a href='{$wgServer}{$wgScriptPath}/EducationModules/Clinician/AVOID FRAILTY video.mp4'>AVOID FRAILTY video</a></li>
                                                <li><a href='{$wgServer}{$wgScriptPath}/EducationModules/Clinician/CFN_Logo.jpg'>CFN_Logo</a></li>
                                                <li><a href='{$wgServer}{$wgScriptPath}/EducationModules/Clinician/Poster 1.jpg'>Poster 1 (JPG)</a></li>
                                                <li><a href='{$wgServer}{$wgScriptPath}/EducationModules/Clinician/Poster 1.pdf'>Poster 1 (PDF)</a></li>
                                                <li><a href='{$wgServer}{$wgScriptPath}/EducationModules/Clinician/Poster 2.jpg'>Poster 2 (JPG)</a></li>
                                                <li><a href='{$wgServer}{$wgScriptPath}/EducationModules/Clinician/Poster 2.pdf'>Poster 2 (PDF)</a></li>
                                                <li><a href='{$wgServer}{$wgScriptPath}/EducationModules/Clinician/Waiting room slide.jpg'>Waiting room slide</a></li>
                                                <li><a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Brochure'>Rack Card Request</a></li>
                                            </ul>
                                        </div>
                                        <div class='module-2cols-outer'>
                                            <b><en>Patient Resources</en><fr>Ressources pour les patients</fr></b>
                                            <ul>
                                                <li><a href='{$wgServer}{$wgScriptPath}/EducationModules/DietAndNutrition/Resources/Protein serves and nutrition risk handout-2.pdf'>Protein serves and nutrition risk handout</a></li>
                                                <li><a href='{$wgServer}{$wgScriptPath}/EducationModules/Interact/Resources/Positive Mental Health in Later Life.pdf'>Positive Mental Health in Later Life</a></li>
                                                <li><a href='{$wgServer}{$wgScriptPath}/EducationModules/Sleep/Resources/Canadian Sleep Society - Sleep in Aging.pdf'>Sleep in Aging</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>");
        }
        
        // Upcoming Events
        $events = Wiki::newFromTitle("UpcomingEvents");
        $wgOut->addHTML("<div class='modules module-2cols-outer'>
                            <h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>
                                <en>My Upcoming Events & Announcements</en>
                                <fr>Mes événements à venir et annonces</fr>
                            </h1>
                            <span class='program-body' style='width: 100%;'>{$events->getText()}</span>
                         </div>");
        
        // Weekly Action Plan
        $fitbitEnabled = ($me->getExtra('fitbit') != "" && time() < $me->getExtra('fitbit_expires')) ? "checked" : "";
        $fitbitHTML = "";
        if($config->getValue('fitbitId') != ""){
            $fitbitHTML = "<div id='fitbitMessages'></div>
                            <en>Connect with your <img src='{$wgServer}{$wgScriptPath}/skins/fitbit.png' style='height: 0.92em; margin-top: -0.2em;' alt='Fitbit' /> to track your goals automatically&nbsp;&nbsp;&nbsp;</en>
                            <fr>Connectez votre FitBit pour obtenir des informations plus précises sur votre santé<br /></fr>
                            <en>Off</en><fr>Désactiver</fr> <label class='switch'>
                                <input type='checkbox' name='fitbitToggle' $fitbitEnabled />
                                <span class='toggle round' style='border: none !important;'></span>
                            </label> <en>On</en><fr>Activer</fr>";
        }
        $wgOut->addHTML("<div class='modules module-2cols-outer'>");
        $wgOut->addHTML("<h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>
                            <en>My Weekly Action Plan</en>
                            <fr>Mon plan d’action hebdomadaire</fr>
                        </h1>");
        $wgOut->addHTML("<div class='program-body $membersOnly' style='width: 100%;'>
                            <div id='actionPlanMessages'></div>
                            <p>
                                <en>
                                    Action plans are small steps towards larger health goals.<br />
                                    <a class='viewActionPlanOverview' href='#'>Overview</a><br />
                                    Looking for motivation? Review the <a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/IngredientsForChange'>Ingredients for Change Module</a>.
                                </en>
                                <fr>
                                    Les plans d’action représentent de petits pas vers des objectifs de santé plus larges. Avant de vous lancer, veuillez lire l’<a class='viewActionPlanOverview' href='#'>aperçu</a> du plan d’action et passer en revue le <a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/IngredientsForChangeFR'>module « Pour un changement réussi »</a> afin d’augmenter vos chances de réussite.
                                </fr>
                            </p>
                            <p>
                                <en>Use the action plan template provided to develop weekly plans, track your daily progress, then review your achievements in your action plans log.</en>
                                <fr>Utilisez le modèle de plan d’action fourni pour élaborer des plans hebdomadaires, suivre vos progrès quotidiens et examiner vos réalisations dans votre journal de plans d’action.</fr>
                            </p>
                            {$fitbitHTML}
                            <p>
                                <div id='newPlan' style='display: none;'>
                                    <a id='createActionPlan' href='#'>
                                        <en>Create NEW Action Plan</en>
                                        <fr>Créer un NOUVEAU plan d’action</fr>
                                    </a>
                                </div>
                                <div id='currentPlan' style='display: none;'>
                                    <en>Current Action Plan</en><fr>Mon plan d'action</fr>
                                    (<a id='viewActionPlan' href='#'><en>View</en><fr>Voir</fr></a> / 
                                     <a id='submitActionPlan' href='#'><en>Submit and Log Accomplishment</en><fr>Soumettre et enregistrer mes accomplissements</fr></a> / 
                                     <a id='repeatActionPlan' href='#'><en>Repeat for another week</en><fr>Répéter le plan d'action une autre semaine</fr></a>)
                                </div>
                            </p>
                            <div id='actionPlanTracker' style='display:none;'></div>
                            <div title='My Weekly Action Plan' style='display:none;' id='createActionPlanDialog' class='actionPlanDialog'></div>
                            <div title='My Weekly Action Plan' style='display:none;' id='viewActionPlanDialog' class='actionPlanDialog'></div>
                            <div title='Action Plan Overview' style='display:none;padding:0;' id='actionPlanOverview'></div>
                        </div>");
        $wgOut->addHTML("</div>");
        
        // Progress
        $wgOut->addHTML("<div class='modules module-2cols-outer'>");
        $wgOut->addHTML("<h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>
                            <en>My AVOID Progress</en>
                            <fr>Mon progrès</fr>
                         </h1>");
        $wgOut->addHTML("<div class='program-body' style='width: 100%;'>");

        // Past Reports
        $pastReports = "";
        if($reportType == "RP_AVOID_TWELVEMO" && AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID_SIXMO")){
            $pastReports .= "<a class='viewReport' data-href='{$wgServer}{$wgScriptPath}/index.php/Special:FrailtyReport?preview&reportType=RP_AVOID_SIXMO' href='#'>Six Month Report</a><br />";
        }
        if(($reportType == "RP_AVOID_SIXMO" || $reportType == "RP_AVOID_TWELVEMO") && AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID")){
            $pastReports .= "<a class='viewReport' data-href='{$wgServer}{$wgScriptPath}/index.php/Special:FrailtyReport?preview&reportType=RP_AVOID' href='#'>First Report</a><br />";
        }
        if($pastReports){
            $wgOut->addHTML("<h3 style='margin-top:0;margin-bottom:0;'><en>Past Reports</en><fr>Rapports d'étape</fr></h3>
                             {$pastReports}<br />");
        }
        $wgOut->addHTML("   <div id='pastActionPlans'></div>
                        </div>
                       </div>");
        
        $wgOut->addHTML("</div>
        <div title='Frailty Report' style='display:none; overflow: hidden; padding:0 !important; background: white;' id='reportDialog'>
            <iframe id='frailtyFrame' style='transform-origin: top left; width:216mm; height: 100%; border: none;' data-src='{$wgServer}{$wgScriptPath}/index.php/Special:FrailtyReport?preview&reportType={$reportType}'></iframe>
        </div>
        <div title='Progress Report' style='display:none; overflow: hidden; padding:0 !important; background: white;' id='progressReportDialog'>
            <iframe id='progressFrame' style='transform-origin: top left; width:216mm; height: 100%; border: none;' data-src='{$wgServer}{$wgScriptPath}/index.php/Special:ProgressReport?preview'></iframe>
        </div>
        <script type='text/javascript'>
            $('#bodyContent h1:not(.program-header)').hide();
            
            $('.viewActionPlanOverview').click(function(){
                var url = (wgLang == 'fr') ? '{$wgServer}{$wgScriptPath}/data/OverviewFR.pdf' : '{$wgServer}{$wgScriptPath}/data/Overview.pdf';
                $('#actionPlanOverview').html('<iframe style=\"width:100%;height:99%;border:none;\" src=\"' + url + '\"></iframe>');
                $('#actionPlanOverview').dialog({
                    modal: true,
                    draggable: false,
                    resizable: false,
                    width: 'auto',
                    height: $(window).height()*0.90,
                    position: { 'my': 'center', 'at': 'center' }
                });
                $(window).resize();
            });
            
            $('.viewReport').click(function(){
                var href = $(this).attr('data-href');
                $('#frailtyFrame').attr('data-src', href);
                $('#frailtyFrame')[0].src = href;
                var reportClick = new DataCollection();
                    reportClick.init(me.get('id'), 'Special:FrailtyReport');
                    reportClick.increment('hits');
                $('#bodyContent').css('overflow-y', 'hidden');
                if($('#reportDialog', $('.ui-dialog')).length == 0){
                    $('#reportDialog').dialog({
                        modal: true,
                        title: '<en>Frailty Report</en><fr>Recommandation du programme Proactif</fr>',
                        draggable: false,
                        resizable: false,
                        width: 'auto',
                        height: $(window).height()*0.90,
                        position: { 'my': 'center', 'at': 'center' },
                        close: function(){
                            $('#bodyContent').css('overflow-y', 'auto');
                            viewFullScreen = false;
                        }
                    });
                    $('.ui-dialog').addClass('program-body').css('margin-bottom', 0);
                    $('.ui-dialog-titlebar:visible').append(\"<a id='viewFullScreen' href='#' style='color: white; position: absolute; top:9px; right: 35px;'><en>View as Full Screen</en><fr>Plein écran</fr></a>\");
                    $('#viewFullScreen', $('.ui-dialog')).click(function(){
                        viewFullScreen = !viewFullScreen;
                        $(window).resize();
                    });
                    $('#frailtyFrame')[0].src = $('#frailtyFrame').attr('data-src'); // Refresh
                }
                else{
                    $('#reportDialog').dialog('open');
                }
                $(window).resize();
            });
            
            $('#viewAssessmentReport').click(function(){
                $('#bodyContent').css('overflow-y', 'hidden');
                if($('#assessmentDialog', $('.ui-dialog')).length == 0){
                    $('#assessmentDialog').dialog({
                        modal: true,
                        draggable: false,
                        resizable: false,
                        width: 'auto',
                        height: $(window).height()*0.90,
                        position: { 'my': 'center', 'at': 'center' },
                        close: function(){
                            $('#bodyContent').css('overflow-y', 'auto');
                            viewFullScreen = false;
                        }
                    });
                    $('.ui-dialog').addClass('program-body').css('margin-bottom', 0);
                    $('.ui-dialog-titlebar:visible').append(\"<a id='viewAssessmentFullScreen' href='#' style='color: white; position: absolute; top:9px; right: 35px;'><en>View as Full Screen</en><fr>Plein écran</fr></a>\");
                    $('#viewAssessmentFullScreen', $('.ui-dialog')).click(function(){
                        viewAssessmentFullScreen = !viewAssessmentFullScreen;
                        $(window).resize();
                    });
                }
                else{
                    $('#assessmentDialog').dialog('open');
                }
                $(window).resize();
            });
            
            $('#assessmentDialog h1').show();
            
            $('#viewProgressReport').click(function(){
                var reportClick = new DataCollection();
                    reportClick.init(me.get('id'), 'Special:ProgressReport');
                    reportClick.increment('hits');
                $('#bodyContent').css('overflow-y', 'hidden');
                if($('#progressReportDialog', $('.ui-dialog')).length == 0){
                    $('#progressReportDialog').dialog({
                        modal: true,
                        draggable: false,
                        resizable: false,
                        width: 'auto',
                        height: $(window).height()*0.90,
                        position: { 'my': 'center', 'at': 'center' },
                        close: function(){
                            $('#bodyContent').css('overflow-y', 'auto');
                            viewProgressFullScreen = false;
                        }
                    });
                    $('.ui-dialog').addClass('program-body').css('margin-bottom', 0);
                    $('.ui-dialog-titlebar:visible').append(\"<a id='viewProgressFullScreen' href='#' style='color: white; position: absolute; top:9px; right: 35px;'><en>View as Full Screen</en><fr>Plein écran</fr></a>\");
                    $('#viewProgressFullScreen', $('.ui-dialog')).click(function(){
                        viewProgressFullScreen = !viewProgressFullScreen;
                        $(window).resize();
                    });
                    $('#progressFrame')[0].src = $('#progressFrame').attr('data-src'); // Refresh
                }
                else{
                    $('#progressReportDialog').dialog('open');
                }
                $(window).resize();
            });
            
            var actionPlans = new ActionPlans();
            var actionPlanHistoryView = new ActionPlanHistoryView({model: actionPlans, el: $('#pastActionPlans')});
            var tracker = undefined;
            actionPlans.on('sync', function(){
                if(actionPlans.length > 0 && !actionPlans.at(0).get('submitted')){
                    $('#newPlan').hide();
                    $('#currentPlan').show();
                    $('#actionPlanTracker').show();
                    if(tracker == undefined){
                        tracker = new ActionPlanTrackerView({model: actionPlans.at(0), el: $('#actionPlanTracker')});
                    }
                }
                else{
                    $('#newPlan').show();
                    $('#currentPlan').hide();
                    $('#actionPlanTracker').hide();
                    if(tracker != undefined){
                        tracker.undelegateEvents();
                    }
                    tracker = undefined;
                }
            });
            actionPlans.fetch();

            $('#createActionPlan').click(function(){
                var createActionPlanView = new ActionPlanCreateView({model: new ActionPlan(), actions: actionPlans, el: $('#createActionPlanDialog')});
            });
            
            $('#viewActionPlan').click(function(){
                var viewActionPlan = new ActionPlanView({model: actionPlans.at(0), el: $('#viewActionPlanDialog')});
            });
            
            $('#submitActionPlan').click(function(){
                $('#submitActionPlan').blur();
                actionPlans.at(0).set('submitted', true);
                actionPlans.at(0).save(null, {
                    success: function(){
                        clearSuccess('#actionPlanMessages');
                        addSuccess('<en>Action Plan submitted!</en><fr>Plan d\'action enregistré!</fr>', false, '#actionPlanMessages');
                    },
                    error: function(){
                        clearError('#actionPlanMessages');
                        addError('Error submitting action plan', false, '#actionPlanMessages');
                    }
                });
            });
            
            $('#repeatActionPlan').click(function(){
                $('#repeatActionPlan').blur();
                tracker.undelegateEvents();
                tracker = undefined;
                var copy = new ActionPlan(actionPlans.at(0).toJSON());
                copy.set('id', ActionPlan.prototype.defaults().id);
                copy.set('tracker', ActionPlan.prototype.defaults().tracker);
                actionPlans.at(0).set('submitted', true);
                actionPlans.at(0).save();
                actionPlans.unshift(copy);
                copy.save(null, {
                    success: function(){
                        clearSuccess('#actionPlanMessages');
                        addSuccess('Action Plan copied!', false, '#actionPlanMessages');
                    },
                    error: function(){
                        clearError('#actionPlanMessages');
                        addError('Error copying action plan', false, '#actionPlanMessages');
                    }
                });
            });
            
            function clickActionPlan(){
                $('.ui-icon').click();
                if($('#createActionPlan').is(':visible')){
                    $('#createActionPlan').click();
                }
                if($('#viewActionPlan').is(':visible')){
                    $('#viewActionPlan').click();
                }
            }
            
            function authorizeFitBit(){
                var toggle = $('[name=fitbitToggle]').is(':checked');
                if(toggle){
                    // Enable
                    var url = 'https://www.fitbit.com/oauth2/authorize?response_type=token' +
                              '&client_id=' + fitbitId +
                              '&redirect_uri=' + document.location.origin + document.location.pathname + '?fitbitApi' +
                              '&scope=activity%20nutrition%20sleep%20heartrate&expires_in=31536000';
                    var popup = window.open(url,'popUpWindow','height=600,width=500,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes');
                    var popupInterval = setInterval(function(){
                        if(popup == null || popup.closed){
                            clearInterval(popupInterval);
                            clearError();
                            me.fetch(function(){
                                if(me.get('extra').fitbit == undefined || me.get('extra').fitbit == '' || new Date().getTime()/1000 >= me.get('extra').fitbit_expires){
                                    // Failed
                                    addError('There was an error connecting to your Fitbit account.  Make sure that you checked \"Allow All\" when authorizing AVOID to access your Fitbit data.', false, '#fitbitMessages');
                                }
                            });
                        }
                    }.bind(this), 500);
                }
                else {
                    // Disable
                    $.get(wgServer + wgScriptPath + '/index.php/Special:AVOIDDashboard?fitbitApi&disable', function(){
                        me.fetch();
                    });
                }
            }
            
            $('[name=fitbitToggle]').change(authorizeFitBit);
            
            var viewFullScreen = false;
            var viewAssessmentFullScreen = false;
            var viewProgressFullScreen = false;
            var initialFrameWidth = $('#reportDialog').width();
            var initialAssessmentWidth = $('#assessmentDialog').width();
            var initialProgressWidth = $('#progressReportDialog').width();
            $('#frailtyFrame').width('100%');
            $('#progressFrame').width('100%');
            
            $(window).resize(function(){
                if(viewFullScreen){
                    $('#viewFullScreen', $('.ui-dialog')).html('<en>Exit Full Screen</en><fr>Quitter en plein écran</fr>').blur();
                    $('.ui-dialog').css('padding', 0)
                                   .css('border-width', 0);
                    $('#reportDialog').dialog({
                        height: $(window).height(),
                        width: $(window).width()
                    });
                    $('#reportDialog').dialog({
                        position: { 'my': 'center', 'at': 'center' }
                    });
                }
                else{
                    $('.ui-dialog').css('padding', 2)
                                   .css('border-width', 1);
                                   
                    $('#viewFullScreen', $('.ui-dialog')).html('<en>View as Full Screen</en><fr>Plein écran</fr>').blur();
                    
                    var desiredWidth = $(window).width()*0.75;
                    if(window.matchMedia('(max-width: 767px)').matches){
                        desiredWidth = $(window).width()*0.99;
                    }
                    else if(window.matchMedia('(max-width: 1024px)').matches){
                        desiredWidth = $(window).width()*0.80;
                    }
                    
                    var scaleFactor = desiredWidth/initialFrameWidth;
                    if($('#reportDialog').is(':visible')){
                        $('#reportDialog').dialog({
                            height: $(window).height()*0.90,
                            width: initialFrameWidth*scaleFactor
                        });
                        $('#reportDialog').dialog({
                            position: { 'my': 'center', 'at': 'center' }
                        });
                    }
                }
                
                if(viewAssessmentFullScreen){
                    $('#viewAssessmentFullScreen', $('.ui-dialog')).html('<en>Exit Full Screen</en><fr>Quitter en plein écran</fr>').blur();
                    $('.ui-dialog').css('padding', 0)
                                   .css('border-width', 0);
                    $('#assessmentDialog').dialog({
                        height: $(window).height(),
                        width: $(window).width()
                    });
                    $('#assessmentDialog').dialog({
                        position: { 'my': 'center', 'at': 'center' }
                    });
                }
                else{
                    $('.ui-dialog').css('padding', 2)
                                   .css('border-width', 1);
                                   
                    $('#viewAssessmentFullScreen', $('.ui-dialog')).html('<en>View as Full Screen</en><fr>Plein écran</fr>').blur();
                    
                    var desiredWidth = $(window).width()*0.75;
                    if(window.matchMedia('(max-width: 767px)').matches){
                        desiredWidth = $(window).width()*0.99;
                    }
                    else if(window.matchMedia('(max-width: 1024px)').matches){
                        desiredWidth = $(window).width()*0.80;
                    }
                    
                    var scaleFactor = desiredWidth/initialProgressWidth;
                    if($('#assessmentDialog').is(':visible')){
                        $('#assessmentDialog').dialog({
                            height: $(window).height()*0.90,
                            width: initialProgressWidth*scaleFactor
                        });
                        $('#assessmentDialog').dialog({
                            position: { 'my': 'center', 'at': 'center' }
                        });
                    }
                }
                
                if(viewProgressFullScreen){
                    $('#viewProgressFullScreen', $('.ui-dialog')).html('<en>Exit Full Screen</en><fr>Quitter en plein écran</fr>').blur();
                    $('.ui-dialog').css('padding', 0)
                                   .css('border-width', 0);
                    $('#progressReportDialog').dialog({
                        height: $(window).height(),
                        width: $(window).width()
                    });
                    $('#progressReportDialog').dialog({
                        position: { 'my': 'center', 'at': 'center' }
                    });
                }
                else{
                    $('.ui-dialog').css('padding', 2)
                                   .css('border-width', 1);
                                   
                    $('#viewProgressFullScreen', $('.ui-dialog')).html('<en>View as Full Screen</en><fr>Plein écran</fr>').blur();
                    
                    var desiredWidth = $(window).width()*0.75;
                    if(window.matchMedia('(max-width: 767px)').matches){
                        desiredWidth = $(window).width()*0.99;
                    }
                    else if(window.matchMedia('(max-width: 1024px)').matches){
                        desiredWidth = $(window).width()*0.80;
                    }
                    
                    var scaleFactor = desiredWidth/initialProgressWidth;
                    if($('#progressReportDialog').is(':visible')){
                        $('#progressReportDialog').dialog({
                            height: $(window).height()*0.90,
                            width: initialProgressWidth*scaleFactor
                        });
                        $('#progressReportDialog').dialog({
                            position: { 'my': 'center', 'at': 'center' }
                        });
                    }
                }
                
                if($('#actionPlanOverview').is(':visible')){
                    $('#actionPlanOverview').dialog({
                        height: $(window).height()*0.90,
                        width: initialProgressWidth*scaleFactor
                    });
                    $('#actionPlanOverview').dialog({
                        position: { 'my': 'center', 'at': 'center' }
                    });
                }
            }).resize();
        </script>");
    }
    
    static function getNextIncompleteSection(){
        global $config;
        $me = Person::newFromWgUser();
        
        $blob = new ReportBlob(BLOB_TEXT, YEAR, $me->getId(), 0);
        $blob_address = ReportBlob::create_address("RP_AVOID", "AVOID_CHECK_EULA", "EULA", 0);
        $blob->load($blob_address);
        $consent = $blob->getData();

        if($consent != "Yes"){
            // Consent not Yes
            return"";
        }
        
        $report = new DummyReport("IntakeSurvey", $me);
        foreach($report->sections as $section){
            if($section->id == "alberta" && $config->getValue('networkFullName') != "AVOID Alberta"){
                continue;
            }
            if($section instanceof EditableReportSection){
                $percent = $section->getPercentComplete();
                if($percent < 100){
                    return $section->name;
                }
            }
        }
        return "";
    }
    
    static function isPersonAssessmentDone($userId=null){
        if($userId != null){
            $me = Person::newFromId($userId);
        }
        else{
            $me = Person::newFromWgUser();
        }
        $blob = new ReportBlob(BLOB_ARRAY, YEAR, $me->getId(), 0);
        $blob_address = ReportBlob::create_address("RP_AVOID_INPERSON", "InPersonAssessment", "DISPLAY", 0);
        $blob->load($blob_address);
        $blob_data = $blob->getData();
        $submitted = @($blob_data['display'][1] == "1");
        return $submitted;
    }
    
    static function hasSubmittedSurvey($userId=null, $report="RP_AVOID"){
        if($userId != null){
            $me = Person::newFromId($userId);
        }
        else{
            $me = Person::newFromWgUser();
        }
        if($me->isRole("Provider")){
            return true;
        }
        $blob = new ReportBlob(BLOB_TEXT, YEAR, $me->getId(), 0);
        $blob_address = ReportBlob::create_address($report, "SUBMIT", "SUBMITTED", 0);
        $blob->load($blob_address);
        $blob_data = $blob->getData();
        $submitted = ($blob_data == "Submitted");
        return $submitted;
    }
    
    static function submissionDate($userId=null, $report="RP_AVOID"){
        if($userId != null){
            $me = Person::newFromId($userId);
        }
        else{
            $me = Person::newFromWgUser();
        }
        if($me->isRole("Provider")){
            return date('Y-m-d');
        }
        $blob = new ReportBlob(BLOB_TEXT, YEAR, $me->getId(), 0);
        $blob_address = ReportBlob::create_address($report, "SUBMIT", "SUBMITTED", 0);
        $blob->load($blob_address, true);
        $blob_data = $blob->getData();
        if($blob_data == "Submitted"){
            return $blob->getLastChanged();
        }
        return date('Y-m-d');
    }
    
    static function checkAllSubmissions($userId){
        global $config;
        $me = Person::newFromId($userId);
        if(!$me->isLoggedIn()){
            return false;
        }
        
        $baseLineSubmitted = AVOIDDashboard::hasSubmittedSurvey($userId, "RP_AVOID");
        $threeMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($userId, "RP_AVOID_THREEMO");
        $sixMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($userId, "RP_AVOID_SIXMO");
        $nineMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($userId, "RP_AVOID_NINEMO");
        $twelveMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($userId, "RP_AVOID_TWELVEMO");
        
        if($baseLineSubmitted){
            Gamification::log('HealthAssessment');
        }
        if($threeMonthSubmitted){
            Gamification::log('3MonthFollowup');
        }
        
        if($me->isRole(ADMIN) || $me->isRole(STAFF) || $config->getValue('networkFullName') == "AVOID Australia" || 
                                                       $config->getValue('networkFullName') == "AVOID AB"){
            return true;
        }
        
        $baseDiff = (time() - strtotime(AVOIDDashboard::submissionDate($userId, "RP_AVOID")))/86400;
        $threeMonthDiff = (time() - strtotime(AVOIDDashboard::submissionDate($userId, "RP_AVOID_THREEMO")))/86400;
        $sixMonthDiff = (time() - strtotime(AVOIDDashboard::submissionDate($userId, "RP_AVOID_SIXMO")))/86400;
        $nineMonthDiff = (time() - strtotime(AVOIDDashboard::submissionDate($userId, "RP_AVOID_NINEMO")))/86400;
        $twelveMonthDiff = (time() - strtotime(AVOIDDashboard::submissionDate($userId, "RP_AVOID_TWELVEMO")))/86400;
        
        if(!$baseLineSubmitted || 
           //(!$threeMonthSubmitted && $baseDiff >= 30*3 && $baseDiff < 30*4) ||
           (!$sixMonthSubmitted && $baseDiff >= 30*6 && $baseDiff < 30*7) ||
           //(!$nineMonthSubmitted && $baseDiff >= 30*9 && $baseDiff < 30*10) ||
           (!$twelveMonthSubmitted && $baseDiff >= 30*12)){
            return false;
        }
        return true;
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            if(AVOIDDashboard::checkAllSubmissions($me->getId())){
                $selected = @($wgTitle->getText() == "AVOIDDashboard") ? "selected" : false;
                $GLOBALS['tabs']['Profile'] = TabUtils::createTab("<en>My Profile</en><fr>Mon Profil</fr>", "{$wgServer}{$wgScriptPath}/index.php/Special:AVOIDDashboard", $selected);
            }
        }
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            unset($tabs['Profile']['subtabs'][0]);
        }
        return true;
    }
    
    static function processPage($article, $skin){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $wgTitle, $wgRoleValues, $config, $wgMessage;
        $me = Person::newFromId($wgUser->getId());
        $nsText = ($article != null) ? str_replace("_", " ", $article->getTitle()->getNsText()) : "";
        if($me->isRole(ADMIN) || $me->isRole(STAFF) || $config->getValue('networkFullName') == "AVOID Australia" || 
                                                       $config->getValue('networkFullName') == "AVOID AB"){
            return true;
        }
        $baseLineSubmitted = AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID");
        $threeMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID_THREEMO");
        $sixMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID_SIXMO");
        $nineMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID_NINEMO");
        $twelveMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID_TWELVEMO");
        
        $baseDiff = (time() - strtotime(AVOIDDashboard::submissionDate($me->getId(), "RP_AVOID")))/86400;
        $section = AVOIDDashboard::getNextIncompleteSection();
        if($me->isLoggedIn()){
            if(!$baseLineSubmitted && ($wgTitle->getText() != "Report" || @$_GET['report'] != "IntakeSurvey")){
                redirect("{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=IntakeSurvey&section={$section}");
            }
            else if($baseLineSubmitted && !$threeMonthSubmitted && $baseDiff >= 30*3 && $baseDiff < 30*4 && ($wgTitle->getText() != "Report" || @$_GET['report'] != "ThreeMonths")){
                $wgMessage->addInfo("You are eligible to fill out the Three Month Follow-Up.  <a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=ThreeMonths'><b>Click Here</b></a> to fill out the follow-up.");
                //redirect("{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=ThreeMonths");
            }
            else if($baseLineSubmitted && !$sixMonthSubmitted && $baseDiff >= 30*6 && $baseDiff < 30*7 && ($wgTitle->getText() != "Report" || @$_GET['report'] != "SixMonths")){
                redirect("{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=SixMonths");
            }
            else if($baseLineSubmitted && !$nineMonthSubmitted && $baseDiff >= 30*9 && $baseDiff < 30*10 && ($wgTitle->getText() != "Report" || @$_GET['report'] != "NineMonths")){

                $wgMessage->addInfo("You are eligible to fill out the Nine Month Follow-Up.  <a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=NineMonths'><b>Click Here</b></a> to fill out the follow-up.");
                //redirect("{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=NineMonths");
            }
            else if($baseLineSubmitted && !$twelveMonthSubmitted && $baseDiff >= 30*12 && ($wgTitle->getText() != "Report" || @$_GET['report'] != "TwelveMonths")){
                redirect("{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=TwelveMonths");
            }
            else if($nsText == "Special" && $wgTitle->getText() == "Report" && ((@$_GET['report'] == "IntakeSurvey" && $baseLineSubmitted) || 
                                                                                (@$_GET['report'] == "ThreeMonths" && $threeMonthSubmitted) ||
                                                                                (@$_GET['report'] == "SixMonths" && $sixMonthSubmitted) ||
                                                                                (@$_GET['report'] == "NineMonths" && $nineMonthSubmitted) ||
                                                                                (@$_GET['report'] == "TwelveMonths" && $twelveMonthSubmitted))){
                redirect("{$wgServer}{$wgScriptPath}/index.php/Special:AVOIDDashboard");
            }
            else if(isset($wgRoleValues[$nsText]) || ($nsText == "" && $wgTitle->getText() == "Main Page")){
                redirect("{$wgServer}{$wgScriptPath}/index.php/Special:AVOIDDashboard");
            }
        }

        return true;
    }
    
}

?>
