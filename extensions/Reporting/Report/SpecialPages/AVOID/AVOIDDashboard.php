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
        $modules = $this->sort($modules, $tags);
        usort($modules, function($a, $b){
            return (floor(EducationResources::completion($a->id)/100) > floor(EducationResources::completion($b->id)/100));
        });
        
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
        
        $wgOut->setPageTitle("AVOID Dashboard");
        $wgOut->addHTML("<div class='modules'>");
        
        // Frailty Status
        $api = new UserFrailtyIndexAPI();
        $scores = $api->getFrailtyScore($me->getId());
        $score = $scores["Total"];
        $label = $scores["Label"];
        $frailty = "";
        if($label == "no risk"){
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
                            <div class='program-body' style='margin-top: 0.5em; margin-bottom: 0.75em; width: 100%;'>
                                {$frailty}<br />
                                <div style='margin-top:0.5em;'>
                                    <a id='viewReport' href='#'>My Personal Report and Recommendations</a> (<a href='{$wgServer}{$wgScriptPath}/index.php/Special:FrailtyReport' target='_blank'>Printable</a>)<br />
                                    <a href='https://healthyagingcentres.ca/wp-content/uploads/2022/03/What-is-frailty.pdf' target='_blank'>What is Frailty?</a>
                                </div>
                                <div style='margin-top:0.5em;'>
                                    <b>Where do I go from here?</b>
                                    <ul>
                                        <li>Review your report and recommendations</li>
                                        <li>Review the education recommended to you, or of interest</li>
                                        <li>Use the action plan template below to develop a goal around the topic(s) identified - come back to track your progress and log your accomplishments</li>
                                        <li>Use the Community Programs and AVOID Programs to support you in accomplishing your action plan</li>
                                    </ul>
                                </div>
                            </div>
                         </div>");
        
        // Upcoming Events
        $events = Wiki::newFromTitle("UpcomingEvents");
        $wgOut->addHTML("<div class='modules module-2cols-outer'>
                            <h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>Upcoming Events</h1>
                            <span class='program-body'>{$events->getText()}</span>
                         </div>");
        
        // Education
        $wgOut->addHTML("<div class='modules module-2cols-outer'>");
        $wgOut->addHTML("<h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>My AVOID Education Modules <a style='float: right; font-size: 0.75em; color:white;' href='{$wgServer}{$wgScriptPath}/index.php/Special:EducationResources'>View All</a></h1>");
        $cols = 3;
        $n = 0;
        foreach($modules as $key => $module){
            if($key >= $cols){ break; }
            $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=EducationModules/{$module->id}";
            $percent = EducationResources::completion($module->id);
            $wgOut->addHTML("<a id='module{$module->id}' title='{$module->title}' class='module module-{$cols}cols' href='{$url}'>
                <img src='{$wgServer}{$wgScriptPath}/EducationModules/{$module->id}/thumbnail.png' alt='{$module->title}' />
                <div class='module-progress'>
                    <div class='module-progress-bar' style='width:{$percent}%;'></div>
                    <div class='module-progress-text'>".number_format($percent)."% Complete</div>
                </div>
            </a>");
            $n++;
        }
        if($n % $cols > 0){
            for($i = 0; $i < $cols - ($n % $cols); $i++){
                $wgOut->addHTML("<div class='module-empty module-{$cols}cols'></div>");
            }
        }
        $wgOut->addHTML("</div>");
        
        // Programs
        $wgOut->addHTML("<div class='modules module-2cols-outer'>");
        $wgOut->addHTML("<h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>My AVOID Programs <a style='float: right; font-size: 0.75em; color:white;' href='{$wgServer}{$wgScriptPath}/index.php/Special:Programs'>View All</a></h1>");
        
        $cols = 3;
        $n = 0;
        foreach($programs as $key => $program){
            if($key >= $cols){ break; }
            $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=Programs/{$program->id}";
            $wgOut->addHTML("<a id='module{$program->id}' title='{$program->title}' class='module module-{$cols}cols' href='{$url}'>
                <img src='{$wgServer}{$wgScriptPath}/EducationModules/{$program->id}.png' alt='{$program->title}' />
                <div class='module-progress-text' style='border-top: 2px solid #005f9d;'>{$program->title}</div>
            </a>");
            $n++;
        }
        if($n % $cols > 0){
            for($i = 0; $i < $cols - ($n % $cols); $i++){
                $wgOut->addHTML("<div class='module-empty module-{$cols}cols'></div>");
            }
        }
        $wgOut->addHTML("</div>");
        
        // Education Resources
        $wgOut->addHTML("<div class='modules module-2cols-outer'>
                            <h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>My AVOID Education Resources <a style='float: right; font-size: 0.75em; color:white;' href='{$wgServer}{$wgScriptPath}/index.php/Special:EducationResources'>View All</a></h1>
                            <div class='program-body'><ul>");
        $cols = 4;
        foreach($resources as $key => $resource){
            if($key >= $cols){ break; }
            $wgOut->addHTML("<li><a class='resource' data-resource='{$resource->category}-{$resource->file}' target='_blank' href='{$wgServer}{$wgScriptPath}/EducationResources/{$resource->category}/{$resource->file}'>{$resource->title}</a></li>");
        }
        $wgOut->addHTML("</ul></div></div>");
                         
        // Community Program Library
        $wgOut->addHTML("<div class='modules module-2cols-outer'>
                            <h1 class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>My Community Programs <a style='float: right; font-size: 0.75em; color:white;' href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap'>View All</a></h1>
                            <div class='program-body'><ul>");
        $cols = 4;
        foreach($communityResources as $key => $category){
            if($key >= $cols){ break; }
            $wgOut->addHTML("<li><a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/{$category->code}'>{$category->text}</a></li>");
        }
        $wgOut->addHTML("</ul></div></div>");
        
        $wgOut->addHTML("</div>
        <div title='Frailty Report' style='display:none; overflow: hidden; padding:0 !important; background: white;' id='reportDialog'>
            <iframe id='frailtyFrame' style='transform-origin: top left; width:216mm; height: 100%; border: none;' src='{$wgServer}{$wgScriptPath}/index.php/Special:FrailtyReport?preview'></iframe>
        </div>
        <script type='text/javascript'>
            $('#bodyContent h1:not(.program-header)').hide();
            
            $('a.resource').click(function(){
                dc.init(me.get('id'), $(this).attr('data-resource'));
                dc.increment('count');
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
                    $('.ui-dialog-titlebar').append(\"<a id='viewFullScreen' href='#' style='color: white; position: absolute; top:9px; right: 35px;'>View as Full Screen</a>\");
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
    
    static function hasSubmittedSurvey(){
        $me = Person::newFromWgUser();
        $blob = new ReportBlob(BLOB_TEXT, YEAR, $me->getId(), 0);
        $blob_address = ReportBlob::create_address("RP_AVOID", "SUBMIT", "SUBMITTED", 0);
        $blob->load($blob_address);
        $blob_data = $blob->getData();
        $submitted = ($blob_data == "Submitted");
        return $submitted;
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            if(AVOIDDashboard::hasSubmittedSurvey()){
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
        if($me->isRole(ADMIN)){
            return true;
        }
        $submitted = AVOIDDashboard::hasSubmittedSurvey();
        $section = AVOIDDashboard::getNextIncompleteSection();
        if(isset($wgRoleValues[$nsText]) ||
           ($me->isLoggedIn() && $nsText == "" && $wgTitle->getText() == "Main Page")){
            if($submitted){
                redirect("{$wgServer}{$wgScriptPath}/index.php/Special:AVOIDDashboard");
            }
            else{
                redirect("{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=IntakeSurvey&section={$section}");
            }
        }
        if($nsText == "Special" && $wgTitle->getText() == "AVOIDDashboard" && !$submitted){
            redirect("{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=IntakeSurvey&section={$section}");
        }
        if($nsText == "Special" && $wgTitle->getText() == "Report" && @$_GET['report'] == "IntakeSurvey" && $submitted){
            redirect("{$wgServer}{$wgScriptPath}/index.php/Special:AVOIDDashboard");
        }
        return true;
    }
    
}

?>
