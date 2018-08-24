<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['DiversityStats'] = 'DiversityStats'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['DiversityStats'] = $dir . 'DiversityStats.i18n.php';
$wgSpecialPageGroups['DiversityStats'] = 'network-tools';

autoload_register('DiversitySurvey/Tabs');

class DiversityStats extends SpecialPage{

    function DiversityStats() {
        parent::__construct("DiversityStats", ADMIN.'+', true);
    }

    function execute($par){
        global $wgOut;
        
        $tabbedPage = new TabbedPage("person");
        
        $tabbedPage->addTab(new CompletionTab());
        
        $tabbedPage->showPage();
        
    }
    
}

?>
