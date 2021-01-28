<?php

UnknownAction::createAction('AdminUniTreeTab::getAdminUniTreeData');

class AdminUniTreeTab extends AbstractTab {
	
	function AdminUniTreeTab(){
        parent::AbstractTab("University Funding");
    }

    function generateBody(){
	    global $wgServer, $wgScriptPath;
	    $this->html .= "The following tree map visualizations show the distribution of funding for Province/University/Person.  Clicking on a section will zoom in to that section.  If 'Funding' is selected, the area that the section takes up is based on how much funding each section gets.  If 'Count' is selected, the area is based on how many sub-sections each section has.";
	    for($year=2011; $year <= REPORTING_YEAR+1; $year++){
	        $this->html .= "<h2>$year</h2>";
	        $tree = new TreeMap("{$wgServer}{$wgScriptPath}/index.php?action=getAdminUniTreeData&date={$year}", "Funding", "Count", "$", "");
	        $tree->height = 500;
	        $tree->width = 1000;
	        $this->html .= $tree->show();
	        $this->html .= "<script type='text/javascript'>
                $('#adminVis').bind('tabsselect', function(event, ui) {
                    if(ui.panel.id == 'university-funding'){
                        onLoad{$tree->index}();
                    }
                });
                </script><br />";
	    }
	}
	
	static function getAdminUniTreeData($action, $article){
	    global $wgServer, $wgScriptPath;
	    $me = Person::newFromWgUser();
	    $year = (isset($_GET['date'])) ? $_GET['date'] : REPORTING_YEAR;
	    if($action == "getAdminUniTreeData" && $me->isRoleAtLeast(MANAGER)){
	        session_write_close();  
            $data = array("name" => "GRAND",
                          "children" => array());
            $people = Person::getAllPeopleDuring(null, $year."-01-01", $year."-12-31");
            $unis = array();
            foreach($people as $person){
                if($person->isRoleDuring(NI, $year."-01-01", $year."-12-31")){
                    $uni = $person->getUniversityDuring($year."01-01", $year."12-31");
                    if($uni['university'] == ""){
                        $uni['university'] = "Unknown";
                    }
                    $budget = $person->getRequestedBudget($year-1);
                    if($budget != null){
                        $total = str_replace('$', "", $budget->copy()->rasterize()->where(HEAD1, array("TOTALS%"))->limit(0, 1)->select(ROW_TOTAL)->toString());
                        @$unis[$uni['university']][$person->getReversedName()] = ($total == "") ? "0" : $total;
                    }
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
