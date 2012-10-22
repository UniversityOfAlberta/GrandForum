<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialDoughnut'] = 'SpecialDoughnut';
$wgExtensionMessagesFiles['SpecialDoughnut'] = $dir . 'SpecialDoughnut.i18n.php';

$wgHooks['UnknownAction'][] = 'SpecialDoughnut::getSpecialDoughnutData';

function runSpecialDoughnut($par) {
	SpecialDoughnut::run($par);
}

class SpecialDoughnut extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('SpecialDoughnut');
		SpecialPage::SpecialPage("SpecialDoughnut", HQP.'+', true, 'runSpecialDoughnut');
	}
	
	function run(){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $doughnut = new Doughnut("{$wgServer}{$wgScriptPath}/index.php?action=getSpecialDoughnutData");
	    $string = $doughnut->show();
	    $wgOut->addHTML($string);
	}
	
	static function getSpecialDoughnutData($action, $article){
	    global $wgServer, $wgScriptPath;
	    if($action == "getSpecialDoughnutData"){
	        $array = array();
            $person = Person::newFromId(3);
            
            $legend = array();
            $legend[0]['name'] = "Year";
            $legend[0]['color'] = "#D38946";
            
            $legend[1]['name'] = "Project";
            $legend[1]['color'] = "#82D868";
            
            $legend[2]['name'] = "Co-authorship";
            $legend[2]['color'] = "#6191B3";
            
            $levels = array();
            $levels[0]['labels'] = array();
            $levels[0]['values'] = array();
            
            $labelIndicies = array();
            $index = 0;
            foreach($person->getPapers() as $paper){
                $date = $paper->getDate();
                $year = substr($date, 0, 4);
                if(!isset($labelIndicies[$year])){
                    $labelIndicies[$year] = $index;
                    $levels[0]['labels'][] = $year;
                    $index++;
                }
                @$levels[0]['values'][$labelIndicies[$year]]++;
            }
            
            $labelIndicies = array();
            $index = 0;
            foreach($person->getPapers() as $paper){
                $projects = $paper->getProjects();
                foreach($projects as $project){
                    if(!isset($labelIndicies[$project->getName()])){
                        $labelIndicies[$project->getName()] = $index;
                        $levels[1]['labels'][] = $project->getName();
                        $index++;
                    }
                    @$levels[1]['values'][$labelIndicies[$project->getName()]]++;
                }
            }
            
            $labelIndicies = array();
            $index = 0;
            foreach($person->getPapers() as $paper){
                $authors = $paper->getAuthors();
                foreach($authors as $author){
                    if($author->getId() != $person->getId()){
                        if(!isset($labelIndicies[$author->getNameForForms()])){
                            $labelIndicies[$author->getNameForForms()] = $index;
                            $levels[2]['labels'][] = $author->getNameForForms();
                            $index++;
                        }
                        @$levels[2]['values'][$labelIndicies[$author->getNameForForms()]]++;
                    }
                }
            }
            
            // Config options
            $array['data_type_singular'] = 'publication';
            $array['data_type_plural'] = 'publications';
            $array['sort'] = 'desc';
            $array['limit'] = '15';
            // Data
            $array['legend'] = $legend;
            $array['levels'] = $levels;
            
            header("Content-Type: application/json");
            echo json_encode(array($array));
            exit;
        }
        return true;
	}
}
?>
