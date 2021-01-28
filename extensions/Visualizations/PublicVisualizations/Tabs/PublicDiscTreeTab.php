<?php

UnknownAction::createAction('PublicDiscTreeTab::getPublicDiscTreeData');

class PublicDiscTreeTab extends AbstractTab {
	
	function PublicDiscTreeTab(){
        parent::AbstractTab("Disciplines");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
        $tree = new TreeMap("{$wgServer}{$wgScriptPath}/index.php?action=getPublicDiscTreeData", "Count", "", "", "");
        $tree->height = 500;
        $tree->width = 1000;
        $this->html .= $tree->show();
        $this->html .= "<script type='text/javascript'>
            $('#publicVis').bind('tabsselect', function(event, ui) {
                if(ui.panel.id == 'disciplines'){
                    onLoad{$tree->index}();
                }
            });
            </script>";
	}
	
	static function getPublicDiscTreeData($action, $article){
	    global $wgServer, $wgScriptPath, $config;
	    if($action == "getPublicDiscTreeData"){
	        session_write_close();
            $data = array("name" => $config->getValue('networkName'),
                          "children" => array());
            $people = Person::getAllPeople();
            $unis = array();
            foreach($people as $person){
                if($person->isRole(NI)){
                    $disc = $person->getDiscipline();
                    @$unis[$disc][$person->getReversedName()] = 1;
                }
            }
            foreach($unis as $disc => $person){
                $discipline = Discipline::newFromName($disc);
                $color = $discipline->getColor();
                $discData = array("name" => $disc,
                                  "color" => $color,
                                  "children" => array());
                $personData = array();
                foreach($person as $name => $total){
                    $personData[] = array("name" => $name,
                                          "size" => $total);
                }
                $discData['children'] = $personData;
                $data['children'][] = $discData;
            }
            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
        }
        return true;
	}
}
?>
