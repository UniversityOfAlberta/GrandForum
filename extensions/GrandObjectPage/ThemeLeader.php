<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ThemeLeader'] = 'ThemeLeader'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ThemeLeader'] = $dir . 'ThemeLeader.i18n.php';
$wgSpecialPageGroups['ThemeLeader'] = 'grand-tools';

function runThemeLeader($par) {
    ThemeLeader::run($par);
}

class ThemeLeader extends SpecialPage{

    function ThemeLeader() {
        wfLoadExtensionMessages('ThemeLeader');
        SpecialPage::SpecialPage("ThemeLeader", '', true, 'runThemeLeader');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isThemeLeader() || $person->isRoleAtLeast(MANAGER));
    }

    function run($par){
        global $wgOut;
        $me = Person::newFromWgUser();
        $projects = Project::getAllProjects();
        $wgOut->addHTML("<table class='tl-projects' style='display:none;' frame='box' rules='all'>
                            <thead>
                                <tr><th>Acronym</th><th>Name</th></tr>
                            </thead>
                            <tbody>");
        foreach($projects as $project){
            if($me->isThemeLeaderOf($project)){
                $wgOut->addHTML("<tr><td><a href='{$project->getUrl()}'>{$project->getName()}</a></td><td>{$project->getFullName()}</td></tr>");
            }
        }
        $wgOut->addHTML("</tbody></table>");
        $wgOut->addHTML("<script type='text/javascript'>$('.tl-projects').dataTable({'iDisplayLength': 100});$('.tl-projects').show();</script>");
    }
    
    static function createTab(){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $me = Person::newFromId($wgUser->getId());
        if($wgTitle->getNSText() == "Special" && $wgTitle->getText() == "ThemeLeader"){
            $selected = "selected";
        }
        
        echo "<li class='top-nav-element $selected'>\n";
        echo "    <span class='top-nav-left'>&nbsp;</span>\n";
        echo "    <a id='lnk-tl_projects' class='top-nav-mid' href='$wgServer$wgScriptPath/index.php/Special:ThemeLeader' class='new'>Theme Lead</a>\n";
        echo "    <span class='top-nav-right'>&nbsp;</span>\n";
        echo "</li>";
    }
    
}
?>
