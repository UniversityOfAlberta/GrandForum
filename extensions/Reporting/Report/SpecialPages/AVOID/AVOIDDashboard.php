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
    
    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;
        if(isset($_GET['generateReport'])){
            $this->generateReport();
        }
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
            $frailty = "Based on the answers in the assessment, you are classified as <span style='color: white; background: green; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$label}</span> to becoming frail.
";
        }
        else if($label == "low risk"){
            $frailty = "Based on the answers in the assessment, you are classified as <span style='color: black; background: #F6BE00; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$label}</span> to becoming frail.
";
        }
        else if($label == "medium risk"){
            $frailty = "Based on the answers in the assessment, you are at <span style='color: black; background: orange; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$label}</span> of being frail.
";
        }
        else if($label == "high risk"){
            $frailty = "Based on your answers in the assessment, you are at <span style='color: white; background: #CC0000; padding: 0 5px; border-radius: 4px; display: inline-block;'>{$label}</span> of being frail.";
        }
        
        $wgOut->addHTML("<div class='modules module-2cols-outer'>
                            <h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>My Frailty Status</h1>
                            <div class='program-body {$membersOnly}' style='width: 100%;'>
                                <p>{$frailty}</p>
                                <p><a id='viewReport' href='#'>My Personal Report and Recommendations</a><br />
                                <a href='https://healthyagingcentres.ca/wp-content/uploads/2022/03/What-is-frailty.pdf' target='_blank'>What is Frailty?</a></p>
                                <b>Where do I go from here?</b>
                                <ul>
                                    <li>Review your personal report and the education recommended, or of interest to you</li>
                                    <li>Use the action plan template below to develop a goal around the topic(s) identified - come back to track your progress and log your accomplishments</li>
                                    <li>Use the Community Programs and AVOID Programs to support you in accomplishing your action plan</li>
                                </ul>
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
            var initialFrameWidth = $('#reportDialog').width();
            $('#frailtyFrame').width('100%');
            
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
                if($('#actionPlanOverview').is(':visible')){
                    $('#actionPlanOverview').dialog({
                        height: $(window).height()*0.90,
                        width: initialFrameWidth*scaleFactor
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
            //return true;
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
