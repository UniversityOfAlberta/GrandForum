<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Programs'] = 'Programs'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Programs'] = $dir . 'Programs.i18n.php';
$wgSpecialPageGroups['Programs'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'Programs::createTab';
$wgHooks['SubLevelTabs'][] = 'Programs::createSubTabs';

class Programs extends SpecialPage {
    
    function __construct() {
		SpecialPage::__construct("Programs", null, false);
	}
	
	function userCanExecute($user){
	    return ($user->isRegistered());
	}
	
	static function getProgramsJSON(){
        global $config;
        $dir = dirname(__FILE__) . '/';
        $n = "";
        switch($config->getValue('networkFullName')){
            case "AVOID KFLA":
                $n = "";
                break;
            case "AVOID Alberta":
                $n = "2";
                break;
            case "AVOID Pacific":
                $n = "3";
                break;
            case "AVOID Quebec":
                $n = "4";
                break;
            case "AVOID Australia":
                $n = "5";
                break;
            case "AVOID AB":
                $n = "6";
                break;
        }
        $json = json_decode(file_get_contents("{$dir}programs{$n}.json"));
        return $json;
    }
	
	function execute($par){
        global $wgOut, $wgServer, $wgScriptPath, $wgLang, $config;
        $me = Person::newFromWgUser();
        if($wgLang->getCode() == 'en'){
            $wgOut->setPageTitle("AVOID Programs");
        }
        else{
            $wgOut->setPageTitle("Programmes PROACTIF");
        }
        $programs = self::getProgramsJSON();
        $categories = array();
        foreach($programs as $program){
            $categories[$program->category] = $program->category;
        }
        
        $cols = 3;
        $wgOut->addHTML("<style>
            @media only screen and (min-width: 1024px) {
                .module-2cols-outer {
                    max-width: 50%;
                }
            }
        </style>");
        $clickProgram = "Click on the program that you are interested in and sign up using the orange link at the bottom of the page.";
        if($config->getValue("networkFullName") == "AVOID Pacific" || 
           $config->getValue("networkFullName") == "AVOID AB"){
            $clickProgram = "";
        }
        $wgOut->addHTML("<p class='program-body'>
                            <en>The AVOID Frailty programs are designed to keep you connected with your peers and community as well as support the development of healthy behaviour. You can choose to participate as a volunteer or find the help you need to be empowered to take control of your health. {$clickProgram}</en>
                            <fr>Les programmes PROACTIF visent à renforcer le sentiment de communauté et l’entraide entre pairs, et à contribuer à l’adoption de saines habitudes de vie. Vous pouvez participer en tant que bénévole ou bien y trouvez l’aide dont vous avez besoin pour vous motiver à prendre le contrôle de votre santé. Cliquez sur le programme qui vous intéresse et inscrivez-vous ci-dessous.</fr>
                        </p><div class='modules' style='justify-content: center;'>");
        foreach($categories as $category){
            $wgOut->addHTML("<div class='modules module-2cols-outer'>");
            if(count($categories) > 1){
                $wgOut->addHTML("<div class='program-header' style='width: 100%; border-radius: 0.5em; padding: 0.5em;'>{$category}</div>");
            }
            $n = 0;
            foreach($programs as $program){
                $membersOnly = ($me->isRole("Provider") && $program->id == "PeerCoaching") ? "members-only" : "";
                if($program->category == $category){
                    $url = (isset($program->href)) ? $program->href : "$wgServer$wgScriptPath/index.php/Special:Report?report=Programs/{$program->id}";
                    if($program->id == ""){
                        // Placeholder text
                        $wgOut->addHTML("<span class='program-body'>{$program->title}</span>");
                    }
                    else{
                        $wgOut->addHTML("<a id='module{$program->id}' title='{$program->title}' class='module module-{$cols}cols $membersOnly' href='{$url}'>
                            <img src='{$wgServer}{$wgScriptPath}/EducationModules/{$program->id}.png' alt='{$program->title}' />
                            <div class='module-progress-text' style='border-top: 2px solid {$config->getValue("hyperlinkColor")};'>{$program->title}</div>
                        </a>");
                        $n++;
                    }
                }
            }
            if($n % $cols > 0){
                for($i = 0; $i < $cols - ($n % $cols); $i++){
                    $wgOut->addHTML("<div class='module-empty module-{$cols}cols'></div>");
                }
            }
            $wgOut->addHTML("</div>");
        }
        $wgOut->addHTML("</div>");
        $wgOut->addHTML("<script type='text/javascript'>
            var pageDC = new DataCollection();
            pageDC.init(me.get('id'), 'Programs-Hit');
            pageDC.append('log', new Date().toISOString().slice(0, 10), false);
        </script>");
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $tabs["Programs"] = TabUtils::createTab("<span class='desktop-text'><en>AVOID Programs</en><fr>Programmes PROACTIF</fr></span>
                                                 <span class='mobile-text'><en>Programs</en><fr>Programmes</fr></span>");
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        if($person->isLoggedIn()){
            if(AVOIDDashboard::checkAllSubmissions($wgUser->getId())){
                $programs = self::getProgramsJSON();
                
                $selected = @($wgTitle->getText() == "Programs") ? "selected" : false;
                $tabs["Programs"]['subtabs'][] = TabUtils::createSubTab("All Programs", "$wgServer$wgScriptPath/index.php/Special:Programs", $selected);
                
                foreach($programs as $program){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "Programs/{$program->id}")) ? "selected" : false;
                    $tabs["Programs"]['subtabs'][] = TabUtils::createSubTab("{$program->title}", "{$url}Programs/{$program->id}", $selected);
                }
            }
        }
        return true;
    }
    
}

?>
