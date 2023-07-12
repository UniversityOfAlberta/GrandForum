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
	    return ($user->isLoggedIn());
	}
	
	static function JSON(){
        if(self::$json == null){
            $dir = dirname(__FILE__) . '/';
            self::$json = json_decode(file_get_contents("{$dir}resources.json"));
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
        global $wgOut, $wgServer, $wgScriptPath, $wgLang;
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
                <div class='module-progress-text' style='border-top: 2px solid #005f9d;'>".showLanguage($category->title, $category->titleFr)."</div>
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
                <div class='program-box program-body' style='width:100%;'><en>Education Module</en><fr>Module d'éducation</fr></div>
                <a id='module{$category->id}' class='module' title='".showLanguage($category->title, $category->titleFr)."' href='{$url}'>
                    <img src='{$wgServer}{$wgScriptPath}/EducationModules/{$category->id}{$lang}/thumbnail.png' alt='".showLanguage($category->title, $category->titleFr)."' />
                    <div class='module-progress'>
                        <div class='module-progress-bar' style='width:{$percent}%;'></div>
                        <div class='module-progress-text'>".number_format($percent)."% Complete</div>
                    </div>
                </a>
            </div>");
            
            $wgOut->addHTML("<div class='modules module-3cols-outer program-body' style='width: 60%;'>
                <div class='program-box' style='width:100%;'><en>Resource Library</en><fr>Ressources externes</fr></div>");
                $resources = ($wgLang->getCode() == "en") ? $category->resources : $category->resourcesFr;
                if(count($resources) > 0){
                    $wgOut->addHTML("<ul style='margin-top: 0;'>");
                    foreach($resources as $resource){
                        $url = (strstr($resource->file, "http") !== false) ? $resource->file : "{$wgServer}{$wgScriptPath}/EducationModules/{$category->id}{$lang}/Resources/{$resource->file}";
                        $wgOut->addHTML("<li><a class='resource' data-resource='{$category->id}-{$resource->file}' target='_blank' href='{$url}'>{$resource->title}</a></li>");
                    }
                    $wgOut->addHTML("</ul>");
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
            });
            
            $('a.resource').click(function(){
                dc.init(me.get('id'), $(this).attr('data-resource'));
                dc.increment('count');
            });
            
            $('a.category').click(function(){
                dc.init(me.get('id'), 'Topic-' + $(this).attr('data-id'));
                dc.increment('count');
            });
            
            var pageDC = new DataCollection();
            pageDC.init(me.get('id'), 'EducationResources-Hit');
            pageDC.append('log', new Date().toISOString().slice(0, 10), false);
        </script>");
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        if(AVOIDDashboard::checkAllSubmissions($wgUser->getId())){
            $selected = @($wgTitle->getText() == "EducationResources" || ($wgTitle->getText() == "Report" && (strstr($_GET['report'], "EducationModules/") !== false))) ? "selected" : false;
            $tabs["EducationResources"] = TabUtils::createTab("<span class='en'>Education</span>
                                                               <span class='fr'>Éducation</span>", "{$wgServer}{$wgScriptPath}/index.php/Special:EducationResources", $selected);
        }
        return true;
    }
    
}

?>
