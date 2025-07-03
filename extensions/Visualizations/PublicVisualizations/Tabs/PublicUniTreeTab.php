<?php

UnknownAction::createAction('PublicUniTreeTab::getPublicUniTreeData');

class PublicUniTreeTab extends AbstractTab {
	
	function __construct(){
        parent::__construct("Institutions");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
        $tree = new TreeMap("{$wgServer}{$wgScriptPath}/index.php?action=getPublicUniTreeData", "Count", "", "", "");
        $tree->height = 600;
        $tree->width = "100%";
        $this->html .= $tree->show();
        $this->html .= "<script type='text/javascript'>
            $('#publicVis').bind('tabsselect', function(event, ui) {
                if(ui.panel.id == 'institutions'){
                    onLoad{$tree->index}();
                }
            });
            var lastWidth{$tree->index} = 0;
            var lastHeight{$tree->index} = 0;
            setInterval(function(){
                var newWidth = $('#institutions').width();
                var newHeight = $('#institutions').height();
                if(lastWidth{$tree->index} != newWidth){
                    onLoad{$tree->index}();
                }
                lastWidth{$tree->index} = newWidth;
                lastHeight{$tree->index} = newHeight;
            }, 100);
            </script>
            <p>This tree map shows the distribution of people in institutions.  Each level represents a different entity:</p>
            <ul type='disc'>
                <li>Provinces
                    <ul type='disc'>
                        <li>Institutions
                            <ul type='disc'>
                                <li>People</li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
            <p>Click to go down a level.  Once at the lowest level, click again to return to the top level.</p>";
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
                    @$unis[$uni][$person->getId()] = 1;
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
                    foreach($people as $id => $total){
                        $person = Person::newFromId($id);
                        $personData[] = array("name" => $person->getReversedName(),
                                              "size" => $total,
                                              "url" => $person->getUrl());
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
