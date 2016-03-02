<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['EditRelations'] = 'EditRelations'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['EditRelations'] = $dir . 'EditRelations.i18n.php';
$wgSpecialPageGroups['EditRelations'] = 'network-tools';

$wgHooks['ToolboxLinks'][] = 'EditRelations::createToolboxLinks';

function runEditRelations($par) {
  EditRelations::execute($par);
}

class EditRelations extends SpecialPage{

    function EditRelations() {
        if(FROZEN){
            SpecialPage::__construct("EditRelations", STAFF.'+', true, 'runEditRelations');
        }
        else{
            SpecialPage::__construct("EditRelations", NI.'+', true, 'runEditRelations');
        }
    }
    
    function updateRelations($type, $name){
        global $wgUser;
        $person = Person::newFromId($wgUser->getId());
        $_POST['name1'] = $person->getNameForForms();
        $_POST['type'] = str_replace("'", "&#39;", $type);
        $currentNames = array();
        foreach($person->getRelations($type) as $relation){
            $currentNames[] = $relation->getUser2()->getNameForForms();
        }
        $names = array();
        if(isset($_POST[$name]) && is_array($_POST[$name])){
            foreach($_POST[$name] as $name){
                $names[] = $name;
            }
        }
        foreach($names as $name){
            $user2 = Person::newFromNameLike($name);
            if(array_search($name, $currentNames) === false){
                $_POST['name2'] = $name;
                APIRequest::doAction('AddRelation', true);
            }
            $relations = $person->getRelations($type);
            $_POST['id'] = null;
            foreach($relations as $relation){
                if($user2->getNameForForms() == $relation->getUser2()->getNameForForms()){
                    $_POST['id'] = $relation->getId();
                    break;
                }
            }
        }
        foreach($currentNames as $name){
            if(array_search(str_replace(".", " ", $name), $names) === false){
                $_POST['name2'] = $name; 
                APIRequest::doAction('DeleteRelation', true);
            }
        }
    }

    function execute($par){
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
            $this->updateRelations(SUPERVISES, 'hqps');
            $this->updateRelations(MENTORS, 'mentors');
            $this->updateRelations(WORKS_WITH, 'coworkers');
            redirect("$wgServer$wgScriptPath/index.php/Special:EditRelations");
        }
        $wgOut->addHTML("<form action='$wgServer$wgScriptPath/index.php/Special:EditRelations' method='post'>");
        $wgOut->addHTML("<div id='tabs'>
                    <ul>
                        <li><a href='#tabs-1'>Supervises</a></li>
                        <li><a href='#tabs-2'>Mentors</a></li>
                        <li><a href='#tabs-3'>Works With</a></li>
                    </ul>
                    <div id='tabs-1'>");
                        EditRelations::generateSupervisesHTML($person, $wgOut);
        $wgOut->addHTML("</div>
                    <div id='tabs-2'>");
                        EditRelations::generateMentorsHTML($person, $wgOut);
        $wgOut->addHTML("</div>
                    <div id='tabs-3'>");
                        EditRelations::generateWorksWithHTML($person, $wgOut);
        $wgOut->addHTML("</div>
                    </div>");
        $wgOut->addHTML("<br /><input type='submit' name='submit' value='Save Relations' />
                         </form>");
    }
    
    function generateSupervisesHTML($person, $wgOut){
        global $wgServer, $wgScriptPath;
        $names = array();
        $list = array();
        $relations = $person->getRelations(SUPERVISES);
        foreach($relations as $relation){
            $names[] = $relation->getUser2()->getNameForForms();
        }
        $allHQP = array_merge(Person::getAllPeople(HQP), Person::getAllCandidates(HQP));
        foreach($allHQP as $hqp){
            if($person->getId() != $hqp->getId()){
                if(array_search($hqp->getNameForForms(), $names) === false){
                    $list[] = $hqp->getNameForForms();
                }
            }
        }
        $wgOut->addHTML("<div class='switcheroo noCustom' name='HQP' id='hqps'>
                            <div class='left'><span>".implode("</span>\n<span>", $names)."</span></div>
                            <div class='right'><span>".implode("</span>\n<span>", $list)."</span></div>
                        </div>");
    }
    
    function generateMentorsHTML($person, $wgOut){
        global $wgServer, $wgScriptPath;
        $names = array();
        $list = array();
        $relations = $person->getRelations(MENTORS);
        foreach($relations as $relation){
            $names[] = $relation->getUser2()->getNameForForms();
        }
        $allHQP = array_merge(Person::getAllPeople(HQP), Person::getAllCandidates(HQP));
        foreach($allHQP as $hqp){
            if($person->getId() != $hqp->getId()){
                if(array_search($hqp->getNameForForms(), $names) === false){
                    $list[] = $hqp->getNameForForms();
                }
            }
        }
        $wgOut->addHTML("<div class='switcheroo noCustom' name='Mentors' id='mentors'>
                            <div class='left'><span>".implode("</span>\n<span>", $names)."</span></div>
                            <div class='right'><span>".implode("</span>\n<span>", $list)."</span></div>
                        </div>");
    }
    
    function generateWorksWithHTML($person, $wgOut){
        global $wgServer, $wgScriptPath;
        $names = array();
        $list = array();
        $relations = $person->getRelations(WORKS_WITH);
        foreach($relations as $relation){
            $names[] = $relation->getUser2()->getNameForForms();
        }
        $all = Person::getAllPeople();
        foreach($all as $p){
            if($person->getId() != $p->getId()){
                if(array_search($p->getNameForForms(), $names) === false){
                    $list[] = $p->getNameForForms();
                }
            }
        }
        $wgOut->addHTML("<div class='switcheroo noCustom' name='CoWorker' id='coworkers'>
                            <div class='left'><span>".implode("</span>\n<span>", $names)."</span></div>
                            <div class='right'><span>".implode("</span>\n<span>", $list)."</span></div>
                        </div>");
    }
    
    static function createToolboxLinks(&$toolbox){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(NI)){
            $toolbox['People']['links'][2] = TabUtils::createToolboxLink("Edit Relations", "$wgServer$wgScriptPath/index.php/Special:EditRelations");
        }
        return true;
    }
}
?>
