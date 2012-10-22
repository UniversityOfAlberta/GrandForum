<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialSimile'] = 'SpecialSimile';
$wgExtensionMessagesFiles['SpecialSimile'] = $dir . 'SpecialSimile.i18n.php';

$wgHooks['UnknownAction'][] = 'SpecialSimile::getSpecialSimileData';

function runSpecialSimile($par) {
	SpecialSimile::run($par);
}

class SpecialSimile extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('SpecialSimile');
		SpecialPage::SpecialPage("SpecialSimile", HQP.'+', true, 'runSpecialSimile');
	}
	
	function run(){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $simile = new Simile("{$wgServer}{$wgScriptPath}/index.php?action=getSpecialSimileData&person=3");
	    $string = $simile->show();
	    $wgOut->addHTML($string);
	}
	
	static function getSpecialSimileData($action, $article){
        if($action == "getSpecialSimileData" && isset($_GET['person'])){
            global $wgServer, $wgScriptPath;
            header("Content-Type: application/xml");
            $person = Person::newFromId($_GET['person']);
            $today = date("Y-m-d");
            $array = array();
            $array['id'] = 'personTimeline';
            $array['title'] = "History {$person->getNameForForms()}";
            $array['description'] = "<img src='{$person->getPhoto()}' height='66' width='50' />This is a history of events for <a href='{$person->getUrl()}' target='_blank'>{$person->getNameForForms()}</a>.";
            $array['focus_date'] = $today;
            $array['initial_zoom'] = "35";
            $array['color'] = "#82530d";
            $array['size_importance'] = "true";
            
            $legend = array();
            $legend[0]['title'] = "Roles";
            $legend[0]['icon'] = "triangle_green.png";
            $legend[1]['title'] = "Projects";
            $legend[1]['icon'] = "triangle_red.png";
            $legend[2]['title'] = "Relations";
            $legend[2]['icon'] = "triangle_blue.png";
            $legend[3]['title'] = "Products";
            $legend[3]['icon'] = "triangle_yellow.png";
            
            $array['legend'] = $legend;
            
            $id = 0;
            $events = array();
            
            echo "<data>\n";
            foreach($person->getRoles(true) as $role){
                $start = substr($role->getStartDate(), 0, 10);
                $end = substr($role->getEndDate(), 0, 10);
                if($end == "0000-00-00"){
                    $end = $today;
                }
                echo "<event start='$start' end='$end' isDuration='true' title='{$role->getRole()}' color='#4E9B05'></event>\n";
            }
       
            foreach($person->getProjects(true) as $project){
                $start = substr($project->getJoinDate($person), 0, 10);
                $end = substr($project->getEndDate($person), 0, 10);
                if($end == "0000-00-00"){
                    $end = $today;
                }
                $content = "&lt;a href='{$project->getUrl()}' target='_blank'&gt;Wiki Page&lt;/a&gt;";
                echo "<event start='$start' end='$end' isDuration='true' title='{$project->getName()}' color='#E41B05'>$content</event>\n";
            }
           
            if(count($person->getRelations('all', true)) > 0){
                foreach($person->getRelations('all', true) as $type){
                    foreach($type as $relation){
                        $start = substr($relation->getStartDate(), 0, 10);
                        $end = substr($relation->getEndDate(), 0, 10);
                        if($end == "0000-00-00"){
                            $end = $today;
                        }
                        $content = "";
                        echo "<event start='$start' end='$end' isDuration='true' title='{$relation->getUser2()->getNameForForms()}' color='#4272B2'>$content</event>\n";
                    }
                }
            }
            
            foreach($person->getPapers('all') as $paper){
                $start = $paper->getDate();
                $content = "&lt;a href='{$paper->getUrl()}' target='_blank'&gt;Wiki Page&lt;/a&gt;";
                echo "<event start='$start' title='".str_replace("&amp;#39;", "&#39;", str_replace("&", "&amp;", $paper->getTitle()))."' link='' icon='$wgServer$wgScriptPath/extensions/Visualisations/Simile/images/yellow-circle.png'>$content</event>\n";
            }
            echo "</data>";
            $array['events'] = $events;
            exit;
        }
        return true;
    }
}
?>
