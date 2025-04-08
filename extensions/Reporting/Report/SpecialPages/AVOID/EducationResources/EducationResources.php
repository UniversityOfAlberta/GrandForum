<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['EducationResources'] = 'EducationResources'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Programs'] = $dir . 'EducationResources.i18n.php';
$wgSpecialPageGroups['EducationResources'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'EducationResources::createTab';

class EducationResources extends SpecialPage {
    
    static $json = "";
    
    function __construct() {
		SpecialPage::__construct("EducationResources", null, false);
	}
	
	function userCanExecute($user){
        global $config;
        $me = Person::newFromUser($user);
        if(!$user->isLoggedIn() && $config->getValue("networkFullName") != "AVOID AB"){
	        AVOIDDashboard::permissionError();
	    }
        if($config->getValue('networkFullName') == "AVOID Australia" &&
            !($me->isRoleAtLeast(STAFF) ||
              $me->isRole("Member") ||
              $me->isRole("GroupA") && !$me->isRoleOn("GroupA", date('Y-m-d', time() - 86400*30.5*7)) || // Allow A until 7 months
              $me->isRole("GroupB") && !$me->isRoleOn("GroupB", date('Y-m-d', time() - 86400*30.5*7)) || // Allow B until 7 months
              $me->isRole("GroupC") && !$me->isRoleOn("GroupC", date('Y-m-d', time() - 86400*30.5*7)) || // Allow C until 7 months
              $me->isRole("GroupD") && !$me->isRoleOn("GroupD", date('Y-m-d', time() - 86400*30.5*7)) // Allow D until 7 months
            )){
            return false;
        }
        return true;
    }
	
	static function JSON(){
	    global $config;
        if(self::$json == null){
            $dir = dirname(__FILE__) . '/';
            if($config->getValue('networkFullName') == "AVOID Australia"){
                self::$json = json_decode(file_get_contents("{$dir}resources_australia.json"));
            }
            else{
                self::$json = json_decode(file_get_contents("{$dir}resources.json"));
            }
        }
        return self::$json;
    }
    
    static function completion($page, $person=null){
        $json = self::JSON();
        
        if($person == null){
            $person = Person::newFromWgUser();
        }
        $data = DataCollection::newFromUserId($person->getId(), $page);
        
        $completed = 0;
        foreach($json as $module){
            if($module->id == $page){
                for($i = 1; $i <= $module->videos; $i++){
                    if(round($data->sum("video{$i}Watched")/count($data->getField("video{$i}Watched", [0]))*100) > 90){
                        $completed++;
                    }
                }
                for($i = 1; $i <= $module->questions; $i++){
                    if(!empty($data->getField("q{$i}"))){
                        $completed += 1/$module->questions;
                    }
                }
                $percent = ($completed / ($module->videos + 1))*100;
                return round($percent);
            }
        }
        return 0;
    }
	
	function execute($par){
        global $wgOut, $wgServer, $wgScriptPath, $wgLang, $config; 
        $me = Person::newFromWgUser();       
        $dir = dirname(__FILE__) . '/';
        $wgOut->setPageTitle(showLanguage("AVOID Education", "PROACTIF pour éviter la fragilisation – Éducation"));
        $categories = self::JSON();
        
        $cols = 8;
        $wgOut->addHTML("<p class='program-body'>
                            <en>Click the topic that you want to learn about.</en>
                            <fr>Cliquez sur la rubrique concernant laquelle vous souhaitez obtenir des renseignements.</fr>
                         </p>");
        $wgOut->addHTML("<div class='modules' style='margin-bottom: 1em;'>");
        $n = 0;
        foreach($categories as $category){
            $wgOut->addHTML("<a id='category{$category->id}' title='".showLanguage($category->title, $category->titleFr)."' data-id='{$category->id}' class='category module module-{$cols}cols-outer' href='#'>
                <img src='{$wgServer}{$wgScriptPath}/EducationModules/{$category->id}.png' alt='".showLanguage($category->title, $category->titleFr)."' />
                <div class='module-progress-text' style='border-top: 2px solid {$config->getValue("hyperlinkColor")};'>".showLanguage($category->title, $category->titleFr)."</div>
            </a>");
            $n++;
        }
        if(max($n % $cols, $n % 4) > 0){
            for($i = 0; $i < $cols - max($n % $cols, $n % 4); $i++){
                $wgOut->addHTML("<div class='module-empty module-{$cols}cols-outer'></div>");
            }
        }
        
        foreach($categories as $category){
            $lang = ($wgLang->getCode() == "fr") ? "FR" : "";
            $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=EducationModules/{$category->id}{$lang}";
            $wgOut->addHTML("");
            
            $percent = self::completion($category->id);
            if($percent == 100){
                Gamification::log("EducationModule/{$category->id}");
            }
            $wgOut->addHTML("<div id='resources{$category->id}' class='resources modules' style='display:none; position: relative; width: 100%;'>
            <div class='modules module-3cols-outer'>
                <div class='program-box program-body' style='width:100%;'><en>Education Module</en><fr>Module d'éducation</fr></div>");
            if($category->videos > 0){
                $wgOut->addHTML("<a id='module{$category->id}' class='module' style='text-decoration: none;' title='".showLanguage($category->title, $category->titleFr)."' href='{$url}'>
                    <img src='{$wgServer}{$wgScriptPath}/EducationModules/{$category->id}{$lang}/thumbnail.png' alt='".showLanguage($category->title, $category->titleFr)."' />
                    <div class='module-progress'>
                        <div class='module-progress-bar' style='width:{$percent}%;'></div>
                        <div class='module-progress-text'>".number_format($percent)."% Complete</div>
                    </div>
                </a>
                <div class='program-body'>To 100% complete <u>this Education Module</u>, watch every video to the end and complete the quiz.</div>");
            }
            else {
                $wgOut->addHTML("<p class='program-body' style='margin-top: 0; width: 100%;'>
                                    <en>This category does not have an education module</en>
                                    <span style='display:block; text-align: center; width: 100%;'>
                                        <img src='{$wgServer}{$wgScriptPath}/EducationModules/{$category->id}.png' style='max-height: 10em;' alt='".showLanguage($category->title, $category->titleFr)."' />
                                    </span>
                                 </p>");
            }
            $wgOut->addHTML("</div>");
            
            $wgOut->addHTML("<div class='modules module-3cols-outer program-body' style='width: 60%;'>
                <div class='program-box' style='width:100%;'><en>Resource Library</en><fr>Ressources externes</fr></div>");
                $resources = ($wgLang->getCode() == "en") ? $category->resources : $category->resourcesFr;
                if(@count($resources) > 0){
                    if(is_object($resources)){
                        $wgOut->addHTML("<div class='accordion' style='width: 100%;'>");
                        foreach($resources as $subCategory => $subResources){
                            if(count($subResources) > 0){
                                $wgOut->addHTML("<h4 id='resources".str_replace(" ", "", $subCategory)."' style='margin-top: 0; padding-top: 0;'>{$subCategory}</h4>");
                                $wgOut->addHTML("<div style='padding:1em !important;'>");
                                if($subCategory == "Pharmacy Medication Review Tab"){
                                    $wgOut->addHTML("Enquire at your regular pharmacy if a Medication Review is available");
                                }
                                $wgOut->addHTML("<ul style='margin-top: 0;'>");
                                foreach($subResources as $resource){
                                    if(isset($_GET['clickedResource']) && $_GET['clickedResource'] == "{$category->id}-{$resource->file}"){
                                        Gamification::log("EducationResource/".md5("{$category->id}-{$resource->file}"));
                                        exit;
                                    }
                                    if($resource->file == ""){
                                        $wgOut->addHTML("<li>{$resource->title}</li>");
                                    }
                                    else{
                                        $url = (strstr($resource->file, "http") !== false) ? $resource->file : "{$wgServer}{$wgScriptPath}/EducationModules/{$category->id}{$lang}/Resources/{$resource->file}";
                                        $wgOut->addHTML("<li><a class='resource' data-resource='{$category->id}-{$resource->file}' target='_blank' href='{$url}'>{$resource->title}</a></li>");
                                    }
                                }
                                $wgOut->addHTML("</ul></div>");
                            }
                        }
                        $wgOut->addHTML("</div>");
                    }
                    else{
                        $wgOut->addHTML("<ul style='margin-top: 0;'>");
                        foreach($resources as $resource){
                            if(isset($_GET['clickedResource']) && $_GET['clickedResource'] == "{$category->id}-{$resource->file}"){
                                Gamification::log("EducationResource/".md5("{$category->id}-{$resource->file}"));
                                exit;
                            }
                            if($resource->file == ""){
                                $wgOut->addHTML("<li>{$resource->title}</li>");
                            }
                            else{
                                $url = (strstr($resource->file, "http") !== false) ? $resource->file : "{$wgServer}{$wgScriptPath}/EducationModules/{$category->id}{$lang}/Resources/{$resource->file}";
                                $wgOut->addHTML("<li><a class='resource' data-resource='{$category->id}-{$resource->file}' target='_blank' href='{$url}'>{$resource->title}</a></li>");
                            }
                        }
                        $wgOut->addHTML("</ul>");
                    }
                }
                else{
                    $wgOut->addHTML("<p style='margin-top: 0;'>
                                        <en>This module does not have any additional education resources</en>
                                        <fr>Ce module ne contient pas de ressources externes</fr>
                                     </p>");
                }
            $wgOut->addHTML("
                </div>
            </div>");
        }
        
        $wgOut->addHTML("</div><script type='text/javascript'>
            $('.category').click(function(e){
                e.preventDefault();
                var id = $(this).attr('data-id');
                $('.resources').hide();
                $('#resources' + id).show();
                $(this).blur();
                
                var scrollTop = $('#resources' + id).position().top + $('#bodyContent').scrollTop();
                $('#bodyContent').scrollTop(scrollTop);
                $('.accordion:visible').accordion({
                    autoHeight: false,
                    collapsible: true,
                    active: false
                });
            });
            
            $('a.resource').click(function(){
                dc.init(me.get('id'), $(this).attr('data-resource'));
                dc.increment('count');
                $.get(wgServer + wgScriptPath + '/index.php/Special:EducationResources?clickedResource=' + encodeURI($(this).attr('data-resource')));
            });
            
            $('a.category').click(function(){
                dc.init(me.get('id'), 'Topic-' + $(this).attr('data-id'));
                dc.increment('count');
            });
            
            var pageDC = new DataCollection();
            pageDC.init(me.get('id'), 'EducationResources-Hit');
            pageDC.append('log', new Date().toISOString().slice(0, 10), false);
            ");
            
        @$wgOut->addHTML("
            $(document).ready(function(){
                $('#category{$_GET['topic']}').click();
                $('#resources{$_GET['resources']}').click();
            });"
        );
            
        $wgOut->addHTML("</script>");
        
        if(!$me->isLoggedIn()){
            $wgOut->addHTML("<style>
                .module-progress {
                    display:none;
                }
            </style>");
        }
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        if(AVOIDDashboard::checkAllSubmissions($wgUser->getId()) && (new self())->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "EducationResources" || ($wgTitle->getText() == "Report" && (strstr($_GET['report'], "EducationModules/") !== false))) ? "selected" : false;
            $tabs["EducationResources"] = TabUtils::createTab("<en>Education</en>
                                                               <fr>Éducation</fr>", "{$wgServer}{$wgScriptPath}/index.php/Special:EducationResources", $selected);
        }
        return true;
    }
    
}

?>
