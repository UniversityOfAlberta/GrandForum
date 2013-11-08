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
                                    $('#budgetDiv').html(\"<iframe id='budgetFrame\" + frameId + \"' style='border-width:0;width:100%;' frameborder='0' src='../index.php/Special:Report?report=NIReport&section=Budget+Request&budgetUploadForm{$projectGet}{$year}'></iframe>\");
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
		$wgOut->addHTML("<h2>Download Budget Template</h2> <ul><li><a href='$wgServer$wgScriptPath/data/GRAND Researcher Budget Request (2013-14).xls'>".(REPORTING_YEAR+1)."-".(REPORTING_YEAR+2)." Budget Template</a></li></ul>" );
		$wgOut->addHTML("<h2>Budget Upload</h2>  
		                    Uploading is currently disabled, but will be available shortly.  In the meantime you can download the template and fill in your budget locally.");
		/*
		$wgOut->addHTML("<h2>Budget Upload</h2>
		                 <div id='budgetDiv'><iframe id='budgetFrame0' frameborder='0' style='border-width:0;height:100px;width:100%;' scrolling='none' src='../index.php/Special:Report?report=NIReport&section=Budget+Request&budgetUploadForm{$projectGet}{$year}'></iframe></div>");*/
		$wgOut->addHTML("</div>");
	}
	
	function renderForPDF(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath;
        $data = $this->getBlobValue();
		if($data !== null){
		    $budget = new Budget("XLS", REPORT2_STRUCTURE, $data);
		    $budget = $this->filterCols($budget);
		    $wgOut->addHTML($budget->copy()->filterCols(V_PROJ, array(""))->renderForPDF());
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
                            parent.alertsize($(\"body\").height()+38);
                        }
                    </script>
                    <style type='text/css'>
                        body {
                            background: none;
                            padding-bottom:25px;
                            overflow-y: hidden;
                        }
                        
                        #bodyContent {
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
                        <form action='$wgServer$wgScriptPath/index.php/Special:Report?report=NIReport&section=Budget+Request&budgetUploadForm{$projectGet}{$year}' method='post' enctype='multipart/form-data'>
                            <input type='file' name='budget' />
	                        <input type='submit' name='upload' value='Upload' />
	                    </form>";
	            
	    $data = $this->getBlobValue();
	    if($data !== null){
	        echo "<br /><a href='$wgServer$wgScriptPath/index.php/Special:Report?report=NIReport&section=Budget+Request&downloadBudget{$projectGet}{$year}'>Download Uploaded Budget</a>";
		    $budget = new Budget("XLS", REPORT2_STRUCTURE, $data);
		    $budget = $this->filterCols($budget);
		    $budget = $budget->copy()->filterCols(V_PROJ, array(""));
		    $person = Person::newFromId($this->personId);
		    
		    if($person->isRoleDuring(CNI) && !$person->isRole(PNI)){
		        $errors = self::addWorksWithRelation($data, true);
		        foreach($errors as $key => $error){
		            $budget->errors[0][] = $error;
		        }
		    }
		    $errors = self::checkDeletedProjects($data);
		    foreach($errors as $key => $error){
	            $budget->errors[0][] = $error;
	        }
		    
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
	
	function filterCols($budget){
	    if($this->getReport()->topProjectOnly){
	        $person = $this->getReport()->person;
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
        return $budget;
	}
	
	function save(){
	    if(isset($_FILES['budget']) && $_FILES['budget']['tmp_name'] != ""){
	        $contents = utf8_encode(file_get_contents($_FILES['budget']['tmp_name']));
	        $this->setBlobValue($contents);
	    }
	    return array();
	}
	
	static function checkDeletedProjects($data){
	    $errors = array();
        $budget = new Budget("XLS", REPORT2_STRUCTURE, $data);
        $projects = $budget->copy()->select(V_PROJ, array())->where(V_PROJ)->xls;
        foreach($projects as $row){
            foreach($row as $proj){
                $project = Project::newFromName($proj->getValue());
                if($project != null && $project->getName() != null){
                    if($project->deleted && substr($project->getEffectiveDate(), 0, 4) == REPORTING_YEAR){
                        $errors[] = "'{$project->getName()}' is not continuing next year";
                    }
                }
            }
        }
        return $errors;
	}
	
	static function addWorksWithRelation($data, $dryRun=false){
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
                        $errors[] = "'{$project->getName()}' is not continuing next year";
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
                            if(!$dryRun && $person != null && $person->getName() != null && $person->isRoleDuring(PNI) && $person->isMemberOfDuring($project)){
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
                                if(!$person->isRoleDuring(PNI)){
                                    $errors[] = "'{$pers}' is not a PNI";
                                }
                                if(!$person->isMemberOfDuring($project)){
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
