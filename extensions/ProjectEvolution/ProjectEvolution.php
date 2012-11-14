<?php

autoload_register('ProjectEvolution/Tabs');

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ProjectEvolution'] = 'ProjectEvolution'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ProjectEvolution'] = $dir . 'ProjectEvolution.i18n.php';
$wgSpecialPageGroups['ProjectEvolution'] = 'grand-tools';

function runProjectEvolution($par){
    ProjectEvolution::run($par);
}

class ProjectEvolution extends SpecialPage {
    
    function ProjectEvolution(){
        wfLoadExtensionMessages('ProjectEvolution');
		SpecialPage::SpecialPage("ProjectEvolution", MANAGER.'+', true, 'runProjectEvolution');
    }    
    
    function run(){
        global $wgOut;
        $tabbedPage = new TabbedPage("project");
        $tabbedPage->addTab(new CreateProjectTab());
        //$tabbedPage->addTab(new EvolveProjectTab());
        //$tabbedPage->addTab(new MergeProjectTab());
        //$tabbedPage->addTab(new ProjectBudgetTab($project, $visibility));
        //$tabbedPage->addTab(new ProjectVisualisationsTab($project, $visibility));
        $tabbedPage->showPage();
        
        $wgOut->output();
        $wgOut->disable();
        return true;
    }
}
?>
