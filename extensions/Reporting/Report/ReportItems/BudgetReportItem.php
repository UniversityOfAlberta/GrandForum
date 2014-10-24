<?php

class BudgetReportItem extends AbstractReportItem {

	function render(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath;
		if(isset($_GET['downloadBudget'])){
		    $data = $this->getBlobValue();
		    if($data != null){
		        $person = Person::newFromId($wgUser->getId());
		        header('Content-Type: application/vnd.ms-excel');
		        header("Content-disposition: attachment; filename='{$person->getNameForForms()}_Budget.xls'");
		        echo $data;
		        exit;
		    }
		}
		if(isset($_GET['budgetUploadForm'])){
		    $this->budgetUploadForm();
		}
		$projectGet = "";
		if(isset($_GET['project'])){
		    $projectGet = "&project={$_GET['project']}";
		}
		$year = "";
        if(isset($_GET['reportingYear']) && isset($_GET['ticket'])){
            $year = "&reportingYear={$_GET['reportingYear']}&ticket={$_GET['ticket']}";
        }
        $wgOut->addHTML("<script type='text/javascript'>
                                var frameId = 0;
                                function alertreload(){
                                    var lastHeight = $('#budgetFrame' + frameId).height();
                                    $('#budgetFrame' + frameId).remove();
                                    frameId++;
                                    $('#budgetDiv').html(\"<iframe name='budget' id='budgetFrame\" + frameId + \"' style='border-width:0;width:100%;' frameborder='0' src='../index.php/Special:Report?report=NIReport&section=Budget&budgetUploadForm{$projectGet}{$year}'></iframe>\");
                                    $('#budgetFrame' + frameId).height(lastHeight);
                                }
                                function alertsize(pixels){
                                    $('#reportMain > div').stop();
                                    $('#budgetFrame' + frameId).height(pixels);
                                    $('#budgetFrame' + frameId).css('max-height', pixels);
                                }
                            </script>");
		$wgOut->addHTML("<h2>Budget Preview</h2>");
		$wgOut->addHTML("<div>");
		$wgOut->addHTML("<h2>Download Budget Template</h2> <ul><li><a href='$wgServer$wgScriptPath/data/GRAND Researcher Budget Request (2015-16).xls'>".(REPORTING_YEAR+1)."-".(REPORTING_YEAR+2)." Budget Template</a></li></ul>");
		$wgOut->addHTML("<h2>Budget Upload</h2>
		                 <div id='budgetDiv'><iframe name='budget' id='budgetFrame0' frameborder='0' style='border-width:0;height:100px;width:100%;' scrolling='none' src='../index.php/Special:Report?report=NIReport&section=Budget&budgetUploadForm{$projectGet}{$year}'></iframe></div>");
		$wgOut->addHTML("</div>");
	}
	
	function renderForPDF(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        $data = $this->getBlobValue();
		if($data !== null){
		    $budget = new Budget("XLS", REPORT2_STRUCTURE, $data);
		    $budget = $this->filterCols($budget);
		    $budget = $budget->copy()->filterCols(V_PROJ, array(""));
		    self::checkTotals($budget, $this->getReport()->person, $this->getReport()->year);
		    $errors = self::checkDeletedProjects($budget, $this->getReport()->person, $this->getReport()->year);
		    foreach($errors as $key => $error){
	            $budget->errors[0][] = $error;
	        }
	        $budget = $this->colorBudget($budget);
		    $wgOut->addHTML($budget->renderForPDF());
		}
		else{
		    $wgOut->addHTML("You have not yet uploaded a budget");
		}
	}
	
	function budgetUploadForm(){
	    global $wgServer, $wgScriptPath;
	    if(isset($_POST['upload'])){
	        $this->save();
	    }
	    $projectGet = "";
		if(isset($_GET['project'])){
		    $projectGet = "&project={$_GET['project']}";
		}
		$year = "";
        if(isset($_GET['reportingYear']) && isset($_GET['ticket'])){
            $year = "&reportingYear={$_GET['reportingYear']}&ticket={$_GET['ticket']}";
        }
        echo "<html>
                <head>
                    <script type='text/javascript' src='$wgServer$wgScriptPath/scripts/jquery.min.js'></script>
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/basetemplate.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/template.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/main.css' type='text/css' />
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/cavendish.css' type='text/css' />
                    <script type='text/javascript'>
                        function load_page() {
                            parent.alertsize($(\"#bodyContent\").height()+38);
                        }
                    </script>
                    <style type='text/css'>
                        body {
                            background: none;
                            padding-bottom:25px;
                            overflow-y: hidden;
                        }
                        
                        #bodyContent {
                            position: relative;
                            font-size: 9pt;
                            font-family: Verdana, sans-serif;
                            -moz-border-radius: 0px;
                            -webkit-border-radius: 0px;
                            border-radius: 0px;
                            
                            -webkit-box-shadow: none;
	                        -moz-box-shadow: none;
	                        box-shadow: none;
	                        border-width:0;
	                        padding:0;
	                        top: 0;
	                        left: 0;
                        }
                        
                        table {
                            line-height: 1.5em;
                            font-size: 9pt;
                            font-family: Verdana, sans-serif;
                        }
                    </style>";
        if(isset($_POST['upload'])){
            echo "<script type='text/javascript'>
                        parent.alertreload();
                    </script>";
        }
        echo "</head>
              <body style='margin:0;'>
                    <div id='bodyContent'>
                        <form action='$wgServer$wgScriptPath/index.php/Special:Report?report=NIReport&section=Budget&budgetUploadForm{$projectGet}{$year}' method='post' enctype='multipart/form-data'>
                            <input type='file' name='budget' />
	                        <input type='submit' name='upload' value='Upload' />
	                    </form>";
	            
	    $data = $this->getBlobValue();
	    if($data !== null){
	        echo "<br /><a href='$wgServer$wgScriptPath/index.php/Special:Report?report=NIReport&section=Budget&downloadBudget{$projectGet}{$year}'>Download Uploaded Budget</a>";
		    $budget = new Budget("XLS", REPORT2_STRUCTURE, $data);
		    $budget = $this->filterCols($budget);
		    $budget = $budget->copy()->filterCols(V_PROJ, array(""));
		    $person = Person::newFromId($this->personId);
		    if($person->isRoleDuring(CNI, ($this->getReport()->year+1).REPORTING_NCE_START_MONTH, ($this->getReport()->year+2).REPORTING_NCE_END_MONTH) && 
		       !$person->isRoleDuring(PNI, ($this->getReport()->year+1).REPORTING_NCE_START_MONTH, ($this->getReport()->year+2).REPORTING_NCE_END_MONTH)){
		        $errors = self::addWorksWithRelation($data, $this->getReport()->year, true);
		        foreach($errors as $key => $error){
		            $budget->errors[0][] = $error;
		        }
		    }
		    self::checkTotals($budget, $person, $this->getReport()->year);
		    $errors = self::checkDeletedProjects($budget, $person, $this->getReport()->year);
		    foreach($errors as $key => $error){
	            $budget->errors[0][] = $error;
	        }
	        $budget = $this->colorBudget($budget);
		    echo $budget->render();
		}
		else{
		    echo "You have not yet uploaded a budget";
		}
		echo "      </div>
		        </body>
		        <script type='text/javascript'>
		            $(document).ready(function(){
		                load_page();
		                setTimeout(load_page, 200);
		            });
		        </script>
	          </html>";
	    exit;
	}
	
	function colorBudget($budget){
	    foreach($budget->xls[7] as $cell){
	        $cell->style .= "background: #DDDDDD;";
	    }
	    foreach($budget->xls[12] as $cell){
	        $cell->style .= "background: #DDDDDD;";
	    }
	    foreach($budget->xls[16] as $cell){
	        $cell->style .= "background: #DDDDDD;";
	    }
	    foreach($budget->xls[17] as $cell){
	        $cell->style .= "background: #DDDDDD;";
	    }
	    foreach($budget->xls[18] as $cell){
	        $cell->style .= "background: #DDDDDD;";
	    }
	    foreach($budget->xls[22] as $cell){
	        $cell->style .= "font-weight: bold;";
	    }
	    return $budget;
	}
	
	function filterCols($budget){
	    $person = $this->getReport()->person;
	    
	    if($this->getReport()->topProjectOnly){
	        $project = $this->getReport()->project;
            $budget = $budget->copy();
            $personRow = $budget->copy()->where(HEAD1, array("Name of network investigator submitting request:"));
            foreach(Project::getAllProjects() as $proj){
                if($proj->getId() != $project->getId()){
                    $budget = $budget->filterCols(V_PROJ, array($proj->getName()));
                }
            }
            $personRow->limitCols(0, $budget->nCols());
            $budget = $budget->filter(HEAD1, array("Name of network investigator submitting request:"));
            $budget = $personRow->union($budget);
        }
        if(!($person->isRoleDuring(CNI, ($this->getReport()->year+1).REPORTING_NCE_START_MONTH, ($this->getReport()->year+2).REPORTING_NCE_END_MONTH) && 
	         !$person->isRoleDuring(PNI, ($this->getReport()->year+1).REPORTING_NCE_START_MONTH, ($this->getReport()->year+2).REPORTING_NCE_END_MONTH))){
	        $budget->filter(HEAD1, array("%PNI with whom I collaborate%"));
	    }
        return $budget;
	}
	
	function save(){
	    if(isset($_FILES['budget']) && $_FILES['budget']['tmp_name'] != ""){
	        $contents = utf8_encode(file_get_contents($_FILES['budget']['tmp_name']));
	        $this->setBlobValue($contents);
	    }
	    return array();
	}
	
	static function checkTotals($budget, $person, $year){
        $projects = @$budget->copy()->select(V_PROJ, array())->where(V_PROJ)->xls[1];
        $total = 0;
        $alreadyUsed = array();
        $i = 0;
        if(count($projects) > 0){
            foreach($projects as $proj){
                $project = Project::newFromName($proj->toString());
                if(isset($alreadyUsed[$proj->toString()])){
                    $budget->xls[1][1+$i]->error = "'{$proj->toString()}' has already been used in another column";
                }
                $alreadyUsed[$proj->toString()] = true;
                if($project != null && !$project->isBigBet()){
                    $total += intval(str_replace("$", "", $budget->copy()->rasterize()->select(V_PROJ, array($proj->toString()))->where(COL_TOTAL)->toString()));
                }
                $i++;
            }
        }
        $name = $budget->copy()->where(V_PERS_NOT_NULL)->select(V_PERS_NOT_NULL)->toString();
        if($person->isRoleDuring(PNI, ($year+1).REPORTING_NCE_START_MONTH, ($year+2).REPORTING_NCE_END_MONTH)){
            if($total > 65000){
                $budget->xls[22][7]->error = "'\$$total' is greater than the maximum $65000 for PNIs (excluding Big-Bet Projects)";
            }
        }
        else if($person->isRoleDuring(CNI, ($year+1).REPORTING_NCE_START_MONTH, ($year+2).REPORTING_NCE_END_MONTH) &&
           $person->isProjectCoLeaderDuring(($year+1).REPORTING_NCE_START_MONTH, ($year+2).REPORTING_NCE_END_MONTH)){
            $bigbet = true;
            foreach($person->leadershipDuring(($year+1).REPORTING_NCE_START_MONTH, ($year+2).REPORTING_NCE_END_MONTH) as $project){
                if(!$project->isBigBet()){
                    $bigbet = false;
                }
            }
            if(!$bigbet){
                if($total > 45000){
                    $budget->xls[22][7]->error = "'\$$total' is greater than the maximum $45000 for CNIs who are co-Leaders (excluding Big-Bet Projects)";
                }
            }
            else{
                if($total > 35000){
                    $budget->xls[22][7]->error = "'\$$total' is greater than the maximum $35000 for CNIs (excluding Big-Bet Projects)";
                }
            }
        }
        else if($person->isRoleDuring(CNI, ($year+1).REPORTING_NCE_START_MONTH, ($year+2).REPORTING_NCE_END_MONTH)){
            if($total > 35000){
                $budget->xls[22][7]->error = "'\$$total' is greater than the maximum $35000 for CNIs (excluding Big-Bet Projects)";
            }
        }

        if(strstr($name, ",") !== false){
            $v_pers = Person::newFromReversedName($name);
        }
        else{
            $v_pers = Person::newFromNameLike($name);
        }
        if($v_pers->getName() != $person->getName()){
            if(isset($budget->xls[0][1])){
                $budget->xls[0][1]->error = "'$name' does not match your own name";
            }
        }
        if(!isset($budget->xls[0][1])){
            $budget->errors[0][] = "There is something wrong with the structure of your budget.";
        }
	}
	
	static function checkDeletedProjects($budget, $person, $year){
	    $errors = array();
        $projects = $budget->copy()->select(V_PROJ, array())->where(V_PROJ)->xls;
        foreach($projects as $rowN => $row){
            foreach($row as $colN => $proj){
                $project = Project::newFromName($proj->getValue());
                if($project != null && $project->getName() != null){
                    if($project->deleted && substr($project->getEffectiveDate(), 0, 4) == REPORTING_YEAR){
                        $budget->xls[$rowN][$colN]->error = "'{$project->getName()}' is not continuing next year";
                    }
                    if($project->getPhase() != PROJECT_PHASE){
                        $budget->xls[$rowN][$colN]->error = "'{$project->getName()}' is not a phase ".PROJECT_PHASE." project";
                    }
                    if($project->isSubProject()){
                        $budget->xls[$rowN][$colN]->error = "'{$project->getName()}' is not a primary project";
                    }
                    if(!$person->isMemberOfDuring($project, ($year+1).REPORTING_NCE_START_MONTH, ($year+2).REPORTING_NCE_END_MONTH)){
                        $budget->xls[$rowN][$colN]->error = "You are not a member of '{$project->getName()}' between ".($year+1).REPORTING_NCE_START_MONTH." and ".($year+2).REPORTING_NCE_END_MONTH;
                    }
                }
            }
        }
        return $errors;
	}
	
	static function addWorksWithRelation($data, $year, $dryRun=false){
	    global $wgUser;
	    $errors = array();
	    $me = Person::newFromId($wgUser->getId());
        $budget = new Budget("XLS", REPORT2_STRUCTURE, $data);
        
        // First select the projects
        $projects = $budget->copy()->select(V_PROJ, array())->where(V_PROJ)->xls;
        foreach($projects as $row){
            foreach($row as $proj){
                $project = Project::newFromName($proj->getValue());
                if($project != null && $project->getName() != null){
                    if($project->deleted && substr($project->getEffectiveDate(), 0, 4) == REPORTING_YEAR){
                        continue;
                    }
                    if($project->getPhase() != PROJECT_PHASE){
                        continue;
                    }
                    if($project->isSubProject()){
                        continue;
                    }
                    // Now look for the people
                    $people = $budget->copy()->select(V_PROJ, array($project->getName()))->where(V_PERS)->xls;
                    $nPeople = 0;
                    foreach($people as $row){
                        foreach($row as $pers){
                            $person = null;
                            $pers = str_replace("'", "", $pers->getValue());
                            $names = explode(',', $pers);
                            if(count($names) > 1){
                                $name = $names[1].' '.$names[0];
                                $person = Person::newFromNameLike($name);
                                if($person == null || $person->getName() == null){
                                    try{
                                        $person = Person::newFromAlias($name);
                                    }
                                    catch(Exception $e){

                                    }
                                }
                            }
                            if($person == null || $person->getName() == null){
                                $person = Person::newFromNameLike($pers);
                            }
                            if($person == null || $person->getName() == null){
                                try{
                                    $person = Person::newFromAlias($pers);
                                }
                                catch(Exception $e){
                                
                                }
                            }
                            if(!$dryRun && $person != null && $person->getName() != null && 
                               $person->isRoleDuring(PNI, ($year+1).REPORTING_NCE_START_MONTH, ($year+2).REPORTING_NCE_END_MONTH) && 
                               $person->isMemberOfDuring($project, ($year+1).REPORTING_NCE_START_MONTH, ($year+2).REPORTING_NCE_END_MONTH)){
                                // Ok, it is safe to add this person as a relation
                                $_POST['type'] = WORKS_WITH;
                                $_POST['name1'] = $me->getName();
                                $_POST['name2'] = $person->getName();
                                $_POST['project_relations'] = $project->getName();
                                APIRequest::doAction('AddRelation', true);
                                $nPeople++;
                            }
                            else{
                                if($dryRun){
                                    $nPeople++;
                                }
                            }
                            
                            if($person != null && $person->getName() != null){
                                if(!$person->isRoleDuring(PNI, ($year+1).REPORTING_NCE_START_MONTH, ($year+2).REPORTING_NCE_END_MONTH)){
                                    $errors[] = "'{$pers}' is not a PNI";
                                }
                                if(!$person->isMemberOfDuring($project, ($year+1).REPORTING_NCE_START_MONTH, ($year+2).REPORTING_NCE_END_MONTH)){
                                    $errors[] = "'{$pers}' is not on {$project->getName()}";
                                }
                            }
                        }
                    }
                    if($nPeople == 0){
                        $errors[] = "You have not specified any PNIs that you collaborate with on {$project->getName()}";
                    }
                }
            }
        }
        return $errors;
	}
	
	function getNFields(){
	    return 0;
	}
	
	function getNComplete(){
	    return 0;
	}
}

?>
