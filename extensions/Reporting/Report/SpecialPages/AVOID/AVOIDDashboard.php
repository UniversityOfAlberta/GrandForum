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
    
    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;
        $dir = dirname(__FILE__) . '/';
        $me = Person::newFromWgUser();
        $_GET['id'] = $me->getId();
        $tags = (new UserTagsAPI())->getTags($me->getId());
        
        $programs = json_decode(file_get_contents("{$dir}Programs/programs.json"));
        $programs = $this->sort($programs, $tags);
        
        $resources = array();
        $categories = json_decode(file_get_contents("{$dir}EducationResources/resources.json"));
        foreach($categories as $category){
            foreach($category->resources as $resource){
                $resource->category = $category->id;
                $resources[] = $resource;
            }
        }
        $resources = $this->sort($resources, $tags);
        
        $modules = EducationModules::modulesJSON();
        $modules = $this->sort($modules, $tags);
        usort($modules, function($a, $b){
            return (floor(EducationModules::completion($a->id)/100) > floor(EducationModules::completion($b->id)/100));
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
        $score = $api->getFrailtyScore($me->getId());
        $frailty = "";
        if($score >= 0 && $score <= 3){
            $frailty = "Based on the answers in the assessment, you are classified as <u style='color: green;'>no risk</u> to becoming frail. This program can provide you with information and support about healthy behaviour to help you maintain a low level of risk, and mitigate the onset of frailty as you age. We will ask again in 6 months so you can see how you have progressed.
";
        }
        else if($score > 3 && $score <= 8){
            $frailty = "Based on the answers in the assessment, you are classified as <u style='color: #F6BE00;'>low risk</u> to becoming frail. Always consult your physician for clinical support and use this program to find information and support for healthy behaviour that will help prevent the onset of frailty as you age. We will ask again in 6 months so you can see how you have progressed.
";
        }
        else if($score > 8 && $score <= 16){
            $frailty = "Based on the answers in the assessment, you are at <u style='color: darkorange;'>medium risk</u> of being frail. Always consult your physician for clinical support and use this program to find information and support for healthy behaviour that will help prevent and mitigate the onset of frailty as you age. We will ask again in 6 months so you can see how you have progressed.
";
        }
        else if($score > 16){
            $frailty = "Based on your answers in the assessment, you are at <u style='color: red;'>high risk</u> of being frail.  Please  consult your physician before using any of the behavioural supports provided in this program. We will ask again in 6 months so you can see how you have progressed.";
        }
        $wgOut->addHTML("<div class='modules module-2cols'>
                            <div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>My Frailty Status</div>
                            <div class='program-body' style='margin-top: 0.5em; margin-bottom: 0.75em; width: 100%;'>
                                {$frailty}<br />
                                <a style='margin-top:0.5em; display:inline-block' href='https://healthyagingcentres.ca/wp-content/uploads/2022/03/What-is-frailty.pdf' target='_blank'>What is Frailty</a>
                            </div>
                         </div>");
        
        // Upcoming Events
        $events = Wiki::newFromTitle("UpcomingEvents");
        $wgOut->addHTML("<div class='modules module-2cols'>
                            <div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>Upcoming Events</div>
                            <span class='program-body' style='font-size: 1.5em; line-height: 1em;'>{$events->getText()}</span>
                         </div>");
        
        // Education
        $wgOut->addHTML("<div class='modules module-2cols'>");
        $wgOut->addHTML("<div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>My AVOID Education Modules <a style='float: right; font-size: 0.75em; color:white;' href='{$wgServer}{$wgScriptPath}/index.php/Special:EducationModules'>View All</a></div>");
        $cols = 3;
        $n = 0;
        foreach($modules as $key => $module){
            if($key >= $cols){ break; }
            $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=EducationModules/{$module->id}";
            $percent = EducationModules::completion($module->id);
            $wgOut->addHTML("<div id='module{$module->id}' class='module module-{$cols}cols' href='{$url}'>
                <img src='{$wgServer}{$wgScriptPath}/EducationModules/{$module->id}/thumbnail.png' />
                <div class='module-progress'>
                    <div class='module-progress-bar' style='width:{$percent}%;'></div>
                    <div class='module-progress-text'>".number_format($percent)."% Complete</div>
                </div>
            </div>");
            $n++;
        }
        if($n % $cols > 0){
            for($i = 0; $i < $cols - ($n % $cols); $i++){
                $wgOut->addHTML("<div class='module-empty module-{$cols}cols'></div>");
            }
        }
        $wgOut->addHTML("</div>");
        
        // Programs
        $wgOut->addHTML("<div class='modules module-2cols'>");
        $wgOut->addHTML("<div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>My AVOID Programs <a style='float: right; font-size: 0.75em; color:white;' href='{$wgServer}{$wgScriptPath}/index.php/Special:Programs'>View All</a></div>");
        
        $cols = 3;
        $n = 0;
        foreach($programs as $key => $program){
            if($key >= $cols){ break; }
            $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=Programs/{$program->id}";
            $wgOut->addHTML("<div id='module{$program->id}' class='module module-{$cols}cols' href='{$url}'>
                <img src='{$wgServer}{$wgScriptPath}/EducationModules/{$program->id}.png' />
                <div class='module-progress-text' style='border-top: 2px solid #548ec9;'>{$program->title}</div>
            </div>");
            $n++;
        }
        if($n % $cols > 0){
            for($i = 0; $i < $cols - ($n % $cols); $i++){
                $wgOut->addHTML("<div class='module-empty module-{$cols}cols'></div>");
            }
        }
        $wgOut->addHTML("</div>");
        
        // Education Resources
        $wgOut->addHTML("<div class='modules module-2cols'>
                            <div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>My AVOID Education Resources <a style='float: right; font-size: 0.75em; color:white;' href='{$wgServer}{$wgScriptPath}/index.php/Special:EducationResources'>View All</a></div>
                            <div class='program-body'><ul>");
        $cols = 4;
        foreach($resources as $key => $resource){
            if($key >= $cols){ break; }
            $wgOut->addHTML("<li><a class='resource' data-resource='{$resource->category}-{$resource->file}' target='_blank' href='{$wgServer}{$wgScriptPath}/EducationResources/{$resource->category}/{$resource->file}'>{$resource->title}</a></li>");
        }
        $wgOut->addHTML("</ul></div></div>");
                         
        // Community Program Library
        $wgOut->addHTML("<div class='modules module-2cols'>
                            <div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>My Community Programs <a style='float: right; font-size: 0.75em; color:white;' href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap'>View All</a></div>
                            <div class='program-body'><ul>");
        $cols = 4;
        foreach($communityResources as $key => $category){
            if($key >= $cols){ break; }
            $wgOut->addHTML("<li><a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/{$category->code}'>{$category->text}</a></li>");
        }
        $wgOut->addHTML("</ul></div></div>");
        
        $wgOut->addHTML("</div>
        <script type='text/javascript'>
            $('#bodyContent h1').hide();
            $('.module').click(function(){
                document.location = $(this).attr('href');
            });
            
            $('a.resource').click(function(){
                dc.init(me.get('id'), $(this).attr('data-resource'));
                dc.increment('count');
            });
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
