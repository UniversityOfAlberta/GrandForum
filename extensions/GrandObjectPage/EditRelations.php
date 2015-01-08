<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['EditRelations'] = 'EditRelations'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['EditRelations'] = $dir . 'EditRelations.i18n.php';
$wgSpecialPageGroups['EditRelations'] = 'network-tools';

$wgHooks['ToolboxLinks'][] = 'EditRelations::createToolboxLinks';

function runEditRelations($par) {
  EditRelations::run($par);
}

class EditRelations extends SpecialPage{

	function EditRelations() {
		wfLoadExtensionMessages('EditRelations');
		if(FROZEN){
		    SpecialPage::SpecialPage("EditRelations", STAFF.'+', true, 'runEditRelations');
	    }
	    else{
	        SpecialPage::SpecialPage("EditRelations", CNI.'+', true, 'runEditRelations');
	    }
	}

	function run($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle;
		$wgOut->addHTML("Here you can edit all the relations relevant to your role.");
	    $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/scripts/switcheroo.js'></script>");
	    $wgOut->addScript("<script type='text/javascript'>
	                        $(document).ready(function(){
		                        $(function() {
		                            $('#tabs').tabs({
		                                                cookie: {
				                                            expires: 1
			                                            }
			                                        });
	                            });
		                    });
		                   </script>");
	    $person = Person::newFromId($wgUser->getId());
	    if(isset($_POST['submit']) &&
	       $_POST['submit'] == "Save Relations"){
	        // Process Submit
	        if(!isset($_GET['editProjects'])){
	            $currentHQPNames = array();
	            foreach($person->getHQP() as $hqp){
	                $currentHQPNames[] = str_replace("'", "&#39;", $hqp->getNameForForms());
	            }
	            $names = array();
	            if(isset($_POST['hqps']) && is_array($_POST['hqps'])){
	                foreach($_POST['hqps'] as $name){
	                    $names[] = str_replace("'", "&#39;", $name);
	                }
	            }
	            $_POST['name1'] = str_replace("'", "&#39;", $person->getNameForForms());
	            // 'Supervises' Relation
	            $_POST['type'] = 'Supervises';
	            foreach($names as $name){
	                $user2 = Person::newFromNameLike($name);
	                if(array_search($name, $currentHQPNames) === false){
	                    $_POST['name2'] = $name;
	                    APIRequest::doAction('AddRelation', true);
	                }
	                $relations = $person->getRelations('Supervises');
                    $_POST['id'] = null;
                    foreach($relations as $relation){
                        if($user2->getNameForForms() == $relation->getUser2()->getNameForForms()){
                            $_POST['id'] = $relation->getId();
                            break;
                        }
                    }
                    $_POST['project_relations'] = '';
	                if(isset($_POST['s_projects'][$user2->getId()])){
	                    $projects = array();
                        foreach($_POST['s_projects'][$user2->getId()] as $proj){
                            $projects[] = $proj;
                        }
                        $_POST['project_relations'] = implode(",", $projects);
	                }
	                if($_POST['id'] != null){
	                    APIRequest::doAction('UpdateProjectRelation', true);
	                }
	            }
	            foreach($currentHQPNames as $name){
	                if(array_search(str_replace(".", " ", $name), $names) === false){
	                    $_POST['name2'] = $name; 
	                    APIRequest::doAction('DeleteRelation', true);
	                }
	            }
	            // 'Works With' Relation
	            $currentWorksWithNames = array();
	            foreach($person->getRelations('Works With') as $relation){
	                $currentWorksWithNames[] = str_replace("'", "&#39;", $relation->getUser2()->getNameForForms());
	            }
	            $names = array();
	            if(isset($_POST['coworkers']) && is_array($_POST['coworkers'])){
	                foreach($_POST['coworkers'] as $name){
	                    $names[] = str_replace("'", "&#39;", $name);
	                }
	            }
	            $_POST['type'] = 'Works With';
	            foreach($names as $name){
	                $user2 = Person::newFromNameLike($name);
	                if(array_search($name, $currentWorksWithNames) === false){
	                    $_POST['name2'] = $name; 
	                    APIRequest::doAction('AddRelation', true);
	                }
	                $relations = $person->getRelations('Works With');
                    $_POST['id'] = null;
                    foreach($relations as $relation){
                        if($user2->getNameForForms() == $relation->getUser2()->getNameForForms()){
                            $_POST['id'] = $relation->getId();
                            break;
                        }
                    }
                    $_POST['project_relations'] = '';
	                if(isset($_POST['w_projects'][$user2->getId()])){
	                    $projects = array();
                        foreach($_POST['w_projects'][$user2->getId()] as $proj){
                            $projects[] = $proj;
                        }
                        $_POST['project_relations'] = implode(",", $projects);
	                }
	                if($_POST['id'] != null){
	                    APIRequest::doAction('UpdateProjectRelation', true);
	                }
	            }
	            foreach($currentWorksWithNames as $name){
	                if(array_search(str_replace(".", " ", $name), $names) === false){
	                    $_POST['name2'] = $name; 
	                    APIRequest::doAction('DeleteRelation', true);
	                }
	            }
	            redirect("$wgServer$wgScriptPath/index.php/Special:EditRelations");
	        }
	        else{
	            redirect("$wgServer$wgScriptPath/index.php/Special:EditRelations?editProjects");
	        }
	    }
	    $editProjects = "";
	    if(isset($_GET['editProjects'])){
	        $editProjects = "?editProjects";
	    }
	    $wgOut->addHTML("<form action='$wgServer$wgScriptPath/index.php/Special:EditRelations$editProjects' method='post'>");
	    $wgOut->addHTML("<div id='tabs'>
                    <ul>
                        <li><a href='#tabs-1'>Supervises</a></li>
                        <li><a href='#tabs-2'>Works With</a></li>
                        <!--li><a href='#tabs-3'>Qualify Relations w. Projects</a></li-->
                    </ul>
                    <div id='tabs-1'>");
                        EditRelations::generateSupervisesHTML($person, $wgOut);
                        
        $wgOut->addHTML("</div>
                    <div id='tabs-2'>");
                        EditRelations::generateWorksWithHTML($person, $wgOut);
        /*$wgOut->addHTML("</div>
                    <div id='tabs-3'>");
                        EditRelations::generateQualifyRelationHTML($person, 'Supervises', $wgOut);
                        EditRelations::generateQualifyRelationHTML($person, 'Works With', $wgOut);*/
        $wgOut->addHTML("</div>
                    </div>");
        $wgOut->addHTML("<br /><input type='submit' name='submit' value='Save Relations' />
                         </form>");
	}
	
	function generateSupervisesHTML($person, $wgOut){
	    global $wgServer, $wgScriptPath;
	    $names = array();
	    $list = array();
	    $relations = $person->getRelations('Supervises');
	    foreach($relations as $relation){
	        $names[] = $relation->getUser2()->getNameForForms();
	    }
	    $allHQP = Person::getAllPeople(HQP);
	    foreach($allHQP as $hqp){
	        if(array_search($hqp->getNameForForms(), $names) === false){
	            $list[] = $hqp->getNameForForms();
	        }
	    }
        $wgOut->addHTML("<div class='switcheroo noCustom' name='HQP' id='hqps'>
                            <div class='left'><span>".implode("</span>\n<span>", $names)."</span></div>
                            <div class='right'><span>".implode("</span>\n<span>", $list)."</span></div>
                        </div>");
	}
	
	function generateWorksWithHTML($person, $wgOut){
	    global $wgServer, $wgScriptPath;
	    $names = array();
	    $list = array();
	    $relations = $person->getRelations('Works With');
	    foreach($relations as $relation){
	        $names[] = $relation->getUser2()->getNameForForms();
	    }
	    $all = Person::getAllPeople();
	    foreach($all as $person){
	        if(array_search($person->getNameForForms(), $names) === false){
	            $list[] = $person->getNameForForms();
	        }
	    }
        $wgOut->addHTML("<div class='switcheroo noCustom' name='CoWorker' id='coworkers'>
                            <div class='left'><span>".implode("</span>\n<span>", $names)."</span></div>
                            <div class='right'><span>".implode("</span>\n<span>", $list)."</span></div>
                        </div>");
	}
	
	function generateQualifyRelationHTML($person, $relationType, $wgOut){
	    global $wgServer, $wgScriptPath, $wgUser;
	    $me = Person::newFromId($wgUser->getId());
	    $relations = $person->getRelations($relationType);
	    $wgOut->addHTML("<h2>{$relationType}</h2>");
        $projects = Project::getAllProjects();
        if(count($relations) == 0){
            $wgOut->addHTML("There are no relations entered");
        }
        $wgOut->addHTML("<table>");
        foreach($relations as $relation){
            $i = 0;
            $prefix = 's_';
            if($relation->getType() == "Works With"){
                $wgOut->addHTML("<tr><td><b>{$relation->getUser2()->getNameForForms()}</b></td><td>works with me on</td>");
                $prefix = 'w_';
            }
            else if($relation->getType() == "Supervises"){
                $wgOut->addHTML("<tr><td>I supervise <b>{$relation->getUser2()->getNameForForms()}</b> on</td>");
                $prefix = 's_';
            }
            $pNames = array();
            foreach($relation->getProjects() as $proj){
                $pNames[] = $proj->getName();
            }
            foreach($me->getProjects() as $proj){
                if(array_search($proj->getName(), $pNames) !== false){
                    $wgOut->addHTML("<td>&nbsp;&nbsp;&nbsp;&nbsp;{$proj->getName()} <input type='checkbox' name='{$prefix}projects[{$relation->getUser2()->getId()}][$i]' value='{$proj->getName()}' checked='checked' /></td>");
                }
                else{
                    $wgOut->addHTML("<td>&nbsp;&nbsp;&nbsp;&nbsp;{$proj->getName()} <input type='checkbox' name='{$prefix}projects[{$relation->getUser2()->getId()}][$i]' value='{$proj->getName()}' /></td>");
                }
                $i++;
            }
            $wgOut->addHTML("</tr>");
            
        }
        $wgOut->addHTML("</table>");
	}
	
	static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(CNI)){
            $toolbox['People']['links'][2] = TabUtils::createToolboxLink("Edit Relations", "$wgServer$wgScriptPath/index.php/Special:EditRelations");
        }
        return true;
    }
}
?>
