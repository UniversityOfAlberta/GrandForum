<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['MyProjects'] = 'MyProjects'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['MyProjects'] = $dir . 'MyProjects.i18n.php';
$wgSpecialPageGroups['MyProjects'] = 'other-tools';

function runMyProjects($par) {
  MyProjects::run($par);
}

class MyProjects extends SpecialPage{

	function MyProjects() {
		wfLoadExtensionMessages('MyProjects');
		SpecialPage::SpecialPage("MyProjects", HQP.'+', true, 'runMyProjects');
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
		$person = Person::newFromId($wgUser->getId());
        $projects = $person->getProjects();
        if(count($projects) > 0){
		    $wgOut->addHTML("<table class='toc' summary='Contents'><tr><td><div id='toctitle'><h2>Contents</h2></div>
                                <ul>");
            $i = 1;
            foreach($projects as $project){
                $j = 1;
                $hqps = $project->getAllPeople(HQP);
                $publications = $project->getPapers("Publication");
                $artifacts = $project->getPapers("Artifact");
                $activities = $project->getPapers("Activity");
                $presses = $project->getPapers("Press");
                $wgOut->addHTML("<li class='toclevel-1'><a href='$wgServer$wgScriptPath/index.php/Special:MyProjects#{$project->getName()}'><span class='tocnumber'>$i</span> <span class='toctext'>{$project->getName()}</span></a>
                                    <ul>");
                if(count($hqps) > 0){
                    $wgOut->addHTML("<li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:MyProjects#{$project->getName()}hqp'><span class='tocnumber'>$i.$j</span> <span class='toctext'>HQP</a></li>");
                    $j++;
                }
                if(count($publications) > 0){
                    $wgOut->addHTML("<li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:MyProjects#{$project->getName()}pub'><span class='tocnumber'>$i.$j</span> <span class='toctext'>Publications</a></li>");
                    $j++;
                }
                if(count($artifacts) > 0){
                    $wgOut->addHTML("<li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:MyProjects#{$project->getName()}art'><span class='tocnumber'>$i.$j</span> <span class='toctext'>Artifacts</a></li>");
                    $j++;
                }
                if(count($activities) > 0){
                    $wgOut->addHTML("<li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:MyProjects#{$project->getName()}act'><span class='tocnumber'>$i.$j</span> <span class='toctext'>Activities</a></li>");
                    $j++;
                }
                if(count($presses) > 0){
                    $wgOut->addHTML("<li class='toclevel-2'><a href='$wgServer$wgScriptPath/index.php/Special:MyProjects#{$project->getName()}press'><span class='tocnumber'>$i.$j</span> <span class='toctext'>Press</a></li>");
                    $j++;
                }
                $wgOut->addHTML("</ul>
                                </li>");
                $i++;
            }
            $wgOut->addHTML("</ul></td></tr></table>");
        }
	    $wgOut->addHTML("All the projects that you are a member of are listed here.<br />");
	    
	    $count = 0;
	    foreach($projects as $project){
            $wgOut->addHTML("<h1><a id='{$project->getName()}' href='{$project->getUrl()}'>{$project->getName()}</a></h1>
            <div style='margin-left:25px;'>");
            $wgOut->addHTML("<h2><a style='color:#000000;text-decoration:none;' id='{$project->getName()}hqp'>HQP</a></h2>");
            $wgOut->addHTML("<ul>");
            $hqps = $project->getAllPeople(HQP);
            foreach($hqps as $hqp){
                $wgOut->addHTML("<li><a href='$wgServer$wgScriptPath/index.php/HQP:{$hqp->getName()}'>{$hqp->getNameForForms()}</a></li>");
            }
            $wgOut->addHTML("</ul>");
            $publications = $project->getPapers("Publication");
            if(count($publications) > 0){
                $wgOut->addHTML("<h2><a style='color:#000000;text-decoration:none;' id='{$project->getName()}pub'>Publications</a></h2>");
                $wgOut->addHTML("<ul>");
                foreach($publications as $publication){
                    $wgOut->addHTML("<li><a href='$wgServer$wgScriptPath/index.php/Publication:{$publication->getId()}'>{$publication->getTitle()}</a></li>");
                }
                $wgOut->addHTML("</ul>");
            }
            $artifacts = $project->getPapers("Artifact");
            if(count($artifacts) > 0){
                $wgOut->addHTML("<h2><a style='color:#000000;text-decoration:none;' id='{$project->getName()}art'>Artifacts</a></h2>");
                $wgOut->addHTML("<ul>");
                foreach($artifacts as $artifact){
                    $wgOut->addHTML("<li><a href='$wgServer$wgScriptPath/index.php/Artifact:{$artifact->getId()}'>{$artifact->getTitle()}</a></li>");
                }
                $wgOut->addHTML("</ul>");
            }
            $activities = $project->getPapers("Activity");
            if(count($activities) > 0){
                $wgOut->addHTML("<h2><a style='color:#000000;text-decoration:none;' id='{$project->getName()}act'>Activities</a></h2>");
                $wgOut->addHTML("<ul>");
                foreach($activities as $activity){
                    $wgOut->addHTML("<li><a href='$wgServer$wgScriptPath/index.php/Activity:{$activity->getId()}'>{$activity->getTitle()}</a></li>");
                }
                $wgOut->addHTML("</ul>");
            }
            $presses = $project->getPapers("Press");
            if(count($presses) > 0){
                $wgOut->addHTML("<h2><a style='color:#000000;text-decoration:none;' id='{$project->getName()}press'>Press</a></h2>");
                $wgOut->addHTML("<ul>");
                foreach($presses as $press){
                    $wgOut->addHTML("<li><a href='$wgServer$wgScriptPath/index.php/Press:{$press->getId()}'>{$press->getTitle()}</a></li>");
                }
                $wgOut->addHTML("</ul>");
            }
            $wgOut->addHTML("</div>");
            $count++;
	    }
	    if($count == 0){
	        $wgOut->addHTML("You are not a member of any projects.");
	    }
	}
	
	static function createTab() {
		global $wgServer, $wgScriptPath;
		echo <<<EOM
<li class='top-nav-element'><span class='top-nav-left'>&nbsp;</span>
<a class='top-nav-mid' href='$wgServer$wgScriptPath/index.php/Special:MyProjects' class='new'>My&nbsp;Projects</a>
<span class='top-nav-right'>&nbsp;</span></li>
EOM;
	}
}
?>
