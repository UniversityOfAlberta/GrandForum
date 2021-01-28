<?php

UnknownAction::createAction('PublicUniTreeTab::getPublicUniTreeData');

class PublicUniTreeTab extends AbstractTab {
	
	function PublicUniTreeTab(){
        parent::AbstractTab("Universities");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
        $tree = new TreeMap("{$wgServer}{$wgScriptPath}/index.php?action=getPublicUniTreeData", "Count", "", "", "");
        $tree->height = 500;
        $tree->width = 1000;
        $this->html .= $tree->show();
        $this->html .= "<script type='text/javascript'>
            $('#publicVis').bind('tabsselect', function(event, ui) {
                if(ui.panel.id == 'universities'){
                    onLoad{$tree->index}();
                }
            });
            </script><br />";
	}
	
	static function getPublicUniTreeData($action, $article){
	    global $wgServer, $wgScriptPath, $config;
	    if($action == "getPublicUniTreeData"){
	        session_write_close();  
            $data = array("name" => $config->getValue('networkName'),
                          "children" => array());
            $people = Person::getAllPeople();
            $unis = array();
            foreach($people as $person){
                if($person->isRole(NI)){
                    $uni = $person->getUni();
                    @$unis[$uni][$person->getReversedName()] = 1;
                }
            }
            $provinces = array();
            foreach($unis as $uni => $people){
                $university = University::newFromName($uni);
                $province = str_replace("Saskatchewan", "Sask", $university->getProvince());
                $provinces[$province][$uni] = $people;
            }
            foreach($provinces as $province => $universities){
                $provData = array("name" => $province,
                                  "color" => "#888888",
                                  "children" => array());
                $unisData = array();
                foreach($universities as $uni => $people){
                    $university = University::newFromName($uni);
                    $provData['color'] = $university->getColor();
                    $uniData = array("name" => $uni,
                                     "color" => $university->getColor(),
                                     "children" => array());
                    $personData = array();
                    foreach($people as $name => $total){
                        $personData[] = array("name" => $name,
                                              "size" => $total);
                    }
                    $uniData['children'] = $personData;
                    $provData['children'][] = $uniData;
                }
                $data['children'][] = $provData;
            }
            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
        }
        return true;
	}
}
?>
