<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['DiversityStats'] = 'DiversityStats'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['DiversityStats'] = $dir . 'DiversityStats.i18n.php';
$wgSpecialPageGroups['DiversityStats'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'DiversityStats::createSubTabs';

autoload_register('DiversitySurvey/Tabs');

class DiversityStats extends SpecialPage{

    function DiversityStats() {
        parent::__construct("DiversityStats", null, true);
    }
    
    function userCanExecute($user){
	    global $wgImpersonating, $wgDelegating;
        $me = Person::newFromWgUser();
        return (!$wgImpersonating && !$wgDelegating && ($me->isRole(EDI) || $me->isRole(ADMIN)));
    }

    function execute($par){
        global $wgOut;
        
        $tabbedPage = new TabbedPage("person");
        
        $tabbedPage->addTab(new CompletionTab());
        $tabbedPage->addTab(new Completion2022Tab());
        $tabbedPage->addTab(new Completion2018Tab());
        
        $tabbedPage->showPage();
        
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        $person = Person::newFromWgUser($wgUser);
        if(self::userCanExecute($person)){
            $selected = @($wgTitle->getText() == "DiversityStats") ? "selected" : false;
            $tabs["EDI"]['subtabs'][] = TabUtils::createSubTab("Stats", "$wgServer$wgScriptPath/index.php/Special:DiversityStats", $selected);
        }
        return true;
    }
    
}

?>
