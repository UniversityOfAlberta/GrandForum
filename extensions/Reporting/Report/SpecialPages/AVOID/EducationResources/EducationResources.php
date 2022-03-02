<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['EducationResources'] = 'EducationResources'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Programs'] = $dir . 'EducationResources.i18n.php';
$wgSpecialPageGroups['EducationResources'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'EducationResources::createTab';

class EducationResources extends SpecialPage {
    
    function __construct() {
		SpecialPage::__construct("EducationResources", null, false);
	}
	
	function userCanExecute($user){
	    return ($user->isLoggedIn());
	}
	
	function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;
        $dir = dirname(__FILE__) . '/';
        $wgOut->setPageTitle("AVOID Education Resources");
        $json = file_get_contents("{$dir}resources.json");
        $categories = json_decode($json);
        
        $cols = 5;
        $wgOut->addHTML("<div class='modules' style='margin-bottom: 1em;'>");
        $n = 0;
        foreach($categories as $category){
            $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=Programs/{$category->id}";
            $percent = rand(0,100);
            $wgOut->addHTML("<div id='category{$category->id}' data-id='{$category->id}' class='module module-{$cols}cols' href='{$url}'>
                <img src='{$wgServer}{$wgScriptPath}/EducationResources/{$category->id}.png' />
                <div class='module-progress-text' style='border-top: 2px solid #548ec9;'>{$category->title}</div>
            </div>");
            $n++;
        }
        if($n % $cols > 0){
            for($i = 0; $i < $cols - ($n % $cols); $i++){
                $wgOut->addHTML("<div class='module-empty module-{$cols}cols'></div>");
            }
        }
        
        foreach($categories as $category){
            $wgOut->addHTML("<div id='resources{$category->id}' class='program-body resources' style='display:none; position: relative; width: 100%;'>
            <div class='program-box' style='position: absolute; left:0; right:0;'>{$category->title} Resources</div>
            <ul style='margin-top: 3em;'>");
            foreach($category->resources as $resource){
                $wgOut->addHTML("<li><a target='_blank' href='{$wgServer}{$wgScriptPath}/EducationResources/{$category->id}/{$resource->file}'>{$resource->title}</a></li>");
            }
            $wgOut->addHTML("</ul></div>");
        }
        $wgOut->addHTML("</div><script type='text/javascript'>
            $('.module').click(function(){
                var id = $(this).attr('data-id');
                $('.resources').hide();
                $('#resources' + id).show();
            });
        </script>");
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $selected = @($wgTitle->getText() == "EducationResources") ? "selected" : false;
        $tabs["EducationResources"] = TabUtils::createTab("AVOID Education Resources", "{$wgServer}{$wgScriptPath}/index.php/Special:EducationResources", $selected);
        return true;
    }
    
}

?>
