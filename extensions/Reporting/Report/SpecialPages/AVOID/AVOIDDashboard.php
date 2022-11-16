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
    
    function userCanExecute($wgUser){
        $person = Person::newFromUser($wgUser);
        return $person->isLoggedIn();
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
    
    function generateReport(){
        $api = new UserFrailtyIndexAPI();
        $scores = $api->getFrailtyScore($me->getId());
        exit;
    }
    
    static function executeFitBitAPI($url){
        global $wgMessage;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer {$_COOKIE['fitbit']}"
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
	    $actionPlans = ActionPlan::newFromUserId($wgUser->getId());
	    $actionPlan = $actionPlans[0];
        if($actionPlan != null && 
           $actionPlan->getType() == "Fitbit Monitoring" &&
           !$actionPlan->getSubmitted() && 
           isset($_COOKIE['fitbit']) &&
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
        global $wgOut, $wgServer, $wgScriptPath;
        if(isset($_GET['fitbitApi'])){
            $wgOut->addHTML("<script type='text/javascript'>
                var search = new URLSearchParams(document.location.hash.replace('#', ''));
                var scope = search.get('scope');
                if(typeof scope == 'undefined'){
                    scope = '';
                }
                if(search.get('access_token') != null && scope.indexOf('heartrate') !== -1 &&
                                                         scope.indexOf('nutrition') !== -1 &&
                                                         scope.indexOf('sleep') !== -1 &&
                                                         scope.indexOf('activity') !== -1){
                    $.cookie('fitbit', search.get('access_token'), { expires: 365 });
                }
                window.close();
            </script>");
            return;
        }
        if(isset($_GET['generateReport'])){
            $this->generateReport();
        }
        self::importFitBit();
        $dir = dirname(__FILE__) . '/';
        $me = Person::newFromWgUser();
        $_GET['id'] = $me->getId();
        $tags = (new UserTagsAPI())->getTags($me->getId());
        
        $membersOnly = ($me->isRole("Provider")) ? "members-only" : "";
        
        $programs = json_decode(file_get_contents("{$dir}Programs/programs.json"));
        $programs = $this->sort($programs, $tags);
        
        $resources = array();
        $categories = EducationResources::JSON();
        foreach($categories as $category){
            foreach($category->resources as $resource){
                $resource->category = $category->id;
                $resources[] = $resource;
            }
        }
        $resources = $this->sort($resources, $tags);
        
        $modules = EducationResources::JSON();
        $complete = array();
        $inProgress = array();
        foreach($modules as $module){
            $completion = EducationResources::completion($module->id);
            $text = "<li><a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/{$module->id}'>{$module->title}</a></li>";
            if($completion == 100){
                $complete[] = $text;
            }
            else if($completion > 0){
                $inProgress[] = $text;
            }
        }
        
        $communityResources = array();
        $cat_json = PharmacyMap::getCategoryJSON();
        foreach($cat_json as $category){ // TODO: Ideally this should be done recursively, but I'm in a rush so...
            $communityResources[] = $category;
            if(isset($category->children)){
                foreach($category->children as $child1){
                    $communityResources[] = $child1;
                    if(isset($child1->children)){
                        foreach($child1->children as $child2){
                            $communityResources[] = $child2;
                        }
                    }
                }
            }
        }
        foreach($communityResources as $category){
            if(!isset($category->tags)){
                $category->tags = array();
            }
        }
        $communityResources = $this->sort($communityResources, $tags);
        
        $wgOut->setPageTitle("My Profile");
        $wgOut->addHTML("<div class='modules'>");
        
        // Frailty Status
        $api = new UserFrailtyIndexAPI();
        $scores = $api->getFrailtyScore($me->getId());
        $score = $scores["Total"];
        $label = $scores["Label"];
        $frailty = "";
        if($label == "very low risk"){ 
            $frailty = "Based on your answers in the assessment, you have a <span style='color: white; background: green; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$label}</span> for frailty.
";
        }
        else if($label == "low risk"){
            $frailty = "Based on your answers in the assessment, you have a <span style='color: black; background: #F6BE00; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$label}</span> for frailty.
";
        }
        else if($label == "medium risk"){
            $frailty = "Based on your answers in the assessment, you have a <span style='color: black; background: orange; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$label}</span> for frailty.
";
        }
        else if($label == "high risk"){
            $frailty = "Based on your answers in the assessment, you have a <span style='color: white; background: #CC0000; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$label}</span> for frailty.";
        }

        $progressReport = (AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID_THREEMO") ||
                           AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID_SIXMO")) ? " | <a id='viewProgressReport' href='#'>Progress Report</a>" : "";
        
        $wgOut->addHTML("<div class='modules module-2cols-outer'>
                            <h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>My Frailty Status</h1>
                            <div class='program-body {$membersOnly}' style='width: 100%;'>
                                <p>
                                    {$frailty}<br />
                                    <a href='https://healthyagingcentres.ca/wp-content/uploads/2022/03/What-is-frailty.pdf' target='_blank'>What is Frailty?</a><br />
                                    <a id='viewReport' href='#'>My Personal Report and Recommendations</a>{$progressReport}
                                </p>
                                <p>
                                    <b>How do I use this program?</b><br />
                                    Step 1. Review your personal report above to learn about your risks and recommendations<br />
                                    Step 2. Use the action plan template below to create and track a healthy change <b>this week</b><br />
                                    Step 3. Use the education, programs and resources to support your healthy aging goals
                                </p>

                                <p style='margin-bottom:0;'>
                                    Still need help? Whether it’s navigating the site or something else, we are here to help you. Please click the button below for 1-on-1 assistance.<br />
                                    <a id='helpButton' class='program-button' style='width: 4em; text-align: center;'>Help</a>
                                </p>
                            </div>
                         </div>");
        
        // Upcoming Events
        $events = Wiki::newFromTitle("UpcomingEvents");
        $wgOut->addHTML("<div class='modules module-2cols-outer'>
                            <h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>My Upcoming Events & Announcements</h1>
                            <span class='program-body' style='width: 100%;'>{$events->getText()}</span>
                         </div>");
        
        // Weekly Action Plan
        $wgOut->addHTML("<div class='modules module-2cols-outer'>");
        $wgOut->addHTML("<h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>My Weekly Action Plan</h1>");
        $wgOut->addHTML("<div class='program-body $membersOnly' style='width: 100%;'>
                            <div id='actionPlanMessages'></div>
                            <p>Action plans are small steps towards larger health goals.  Before jumping in, read the action plan <a id='viewActionPlanOverview' href='#'>Overview</a> and review the <a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/IngredientsForChange'>Ingredients for Change Module</a> to increase your chance of success.</p>
                            <p>Use the action plan template provided to develop weekly plans, track your daily progress and review your achievements in your action plans log.</p>
                        
                            <p>
                                <div id='newPlan' style='display: none;'><a id='createActionPlan' href='#'>Create NEW Action Plan</a></div>
                                <div id='currentPlan' style='display: none;'>Current Action Plan (<a id='viewActionPlan' href='#'>View</a> / <a id='submitActionPlan' href='#'>Submit and Log Accomplishment</a> / <a id='repeatActionPlan' href='#'>Repeat for another week</a>)</div>
                            </p>
                            <div id='actionPlanTracker' style='display:none;'></div>
                            <div title='My Weekly Action Plan' style='display:none;' id='createActionPlanDialog' class='actionPlanDialog'></div>
                            <div title='My Weekly Action Plan' style='display:none;' id='viewActionPlanDialog' class='actionPlanDialog'></div>
                            <div title='Action Plan Overview' style='display:none;padding:0;' id='actionPlanOverview'></div>
                        </div>");
        $wgOut->addHTML("</div>");
        
        // Progress
        $wgOut->addHTML("<div class='modules module-2cols-outer'>");
        $wgOut->addHTML("<h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>My AVOID Progress</h1>");
        $wgOut->addHTML("<div class='program-body' style='width: 100%;'>
                            <div id='pastActionPlans'></div>
                            <h3 style='margin-bottom: 0;margin-top:0;'>Education Module Progress</h3>
                            <div class='modules'>
                                 <div class='module-2cols-outer'>
                                    <b>Completed</b>
                                    <ul>".implode($complete)."</ul>
                                 </div>
                                 <div class='module-2cols-outer'>
                                    <b>In Progress</b>
                                    <ul>".implode($inProgress)."</ul>
                                 </div>
                            </div>
                        </div>");
        $wgOut->addHTML("</div>");
        
        $wgOut->addHTML("</div>
        <div title='Frailty Report' style='display:none; overflow: hidden; padding:0 !important; background: white;' id='reportDialog'>
            <iframe id='frailtyFrame' style='transform-origin: top left; width:216mm; height: 100%; border: none;' src='{$wgServer}{$wgScriptPath}/index.php/Special:FrailtyReport?preview'></iframe>
        </div>
        <div title='Progress Report' style='display:none; overflow: hidden; padding:0 !important; background: white;' id='progressReportDialog'>
            <iframe id='progressFrame' style='transform-origin: top left; width:216mm; height: 100%; border: none;' src='{$wgServer}{$wgScriptPath}/index.php/Special:ProgressReport?preview'></iframe>
        </div>
        <script type='text/javascript'>
            $('#bodyContent h1:not(.program-header)').hide();
            
            $('#viewActionPlanOverview').click(function(){
                $('#actionPlanOverview').html('<iframe style=\"width:100%;height:99%;border:none;\" src=\"{$wgServer}{$wgScriptPath}/data/Overview.pdf\"></iframe>');
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
            
            $('#viewReport').click(function(){
                $('#bodyContent').css('overflow-y', 'hidden');
                if($('#reportDialog', $('.ui-dialog')).length == 0){
                    $('#reportDialog').dialog({
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
                    $('.ui-dialog-titlebar:visible').append(\"<a id='viewFullScreen' href='#' style='color: white; position: absolute; top:9px; right: 35px;'>View as Full Screen</a>\");
                    $('#viewFullScreen', $('.ui-dialog')).click(function(){
                        viewFullScreen = !viewFullScreen;
                        $(window).resize();
                    });
                }
                else{
                    $('#reportDialog').dialog('open');
                }
                $(window).resize();
            });
            
            $('#viewProgressReport').click(function(){
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
                    $('.ui-dialog-titlebar:visible').append(\"<a id='viewProgressFullScreen' href='#' style='color: white; position: absolute; top:9px; right: 35px;'>View as Full Screen</a>\");
                    $('#viewProgressFullScreen', $('.ui-dialog')).click(function(){
                        viewProgressFullScreen = !viewProgressFullScreen;
                        $(window).resize();
                    });
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
                        addSuccess('Action Plan submitted!', false, '#actionPlanMessages');
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
            
            var viewFullScreen = false;
            var viewProgressFullScreen = false;
            var initialFrameWidth = $('#reportDialog').width();
            var initialProgressWidth = $('#progressReportDialog').width();
            $('#frailtyFrame').width('100%');
            $('#progressFrame').width('100%');
            
            $(window).resize(function(){
                if(viewFullScreen){
                    $('#viewFullScreen', $('.ui-dialog')).text('Exit Full Screen').blur();
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
                                   
                    $('#viewFullScreen', $('.ui-dialog')).text('View as Full Screen').blur();
                    
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
                
                if(viewProgressFullScreen){
                    $('#viewProgressFullScreen', $('.ui-dialog')).text('Exit Full Screen').blur();
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
                                   
                    $('#viewProgressFullScreen', $('.ui-dialog')).text('View as Full Screen').blur();
                    
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
            if($section instanceof EditableReportSection){
                $percent = $section->getPercentComplete();
                if($percent < 100){
                    return $section->name;
                }
            }
        }
        return "";
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
        $me = Person::newFromId($userId);
        if($me->isRole(ADMIN) || $me->isRole(STAFF)){
            return true;
        }
        $baseLineSubmitted = AVOIDDashboard::hasSubmittedSurvey($userId, "RP_AVOID");
        $threeMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($userId, "RP_AVOID_THREEMO");
        $sixMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($userId, "RP_AVOID_SIXMO");
        
        $baseDiff = (time() - strtotime(AVOIDDashboard::submissionDate($userId, "RP_AVOID")))/86400;
        $threeMonthDiff = (time() - strtotime(AVOIDDashboard::submissionDate($userId, "RP_AVOID_THREEMO")))/86400;
        $sixMonthDiff = (time() - strtotime(AVOIDDashboard::submissionDate($userId, "RP_AVOID_SIXMO")))/86400;
        
        if(!$baseLineSubmitted || 
           ($baseLineSubmitted && !$threeMonthSubmitted && $baseDiff >= 30*3) ||
           ($threeMonthSubmitted && !$sixMonthSubmitted && $baseDiff >= 30*6)){
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
                $GLOBALS['tabs']['Profile'] = TabUtils::createTab("My Profile", "{$wgServer}{$wgScriptPath}/index.php/Special:AVOIDDashboard", $selected);
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
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $wgTitle, $wgRoleValues, $config;
        $me = Person::newFromId($wgUser->getId());
        $nsText = ($article != null) ? str_replace("_", " ", $article->getTitle()->getNsText()) : "";
        if($me->isRole(ADMIN) || $me->isRole(STAFF)){
            return true;
        }
        $baseLineSubmitted = AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID");
        $threeMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID_THREEMO");
        $sixMonthSubmitted = AVOIDDashboard::hasSubmittedSurvey($me->getId(), "RP_AVOID_SIXMO");
        
        $baseDiff = (time() - strtotime(AVOIDDashboard::submissionDate($me->getId(), "RP_AVOID")))/86400;
        $threeMonthDiff = (time() - strtotime(AVOIDDashboard::submissionDate($me->getId(), "RP_AVOID_THREEMO")))/86400;
        $sixMonthDiff = (time() - strtotime(AVOIDDashboard::submissionDate($me->getId(), "RP_AVOID_SIXMO")))/86400;
        
        $section = AVOIDDashboard::getNextIncompleteSection();
        if($me->isLoggedIn()){
            if(!$baseLineSubmitted && ($wgTitle->getText() != "Report" || @$_GET['report'] != "IntakeSurvey")){
                redirect("{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=IntakeSurvey&section={$section}");
            }
            else if($baseLineSubmitted && !$threeMonthSubmitted && $baseDiff >= 30*3 && ($wgTitle->getText() != "Report" || @$_GET['report'] != "ThreeMonths")){
                redirect("{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=ThreeMonths");
            }
            else if($threeMonthSubmitted && !$sixMonthSubmitted && $baseDiff >= 30*6 && ($wgTitle->getText() != "Report" || @$_GET['report'] != "SixMonths")){
                redirect("{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=SixMonths");
            }
            else if($nsText == "Special" && $wgTitle->getText() == "Report" && ((@$_GET['report'] == "IntakeSurvey" && $baseLineSubmitted) || 
                                                                                (@$_GET['report'] == "ThreeMonths" && $threeMonthSubmitted) ||
                                                                                (@$_GET['report'] == "SixMonths" && $sixMonthSubmitted))){
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
