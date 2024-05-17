<?php

class BudgetReportItem extends AbstractReportItem {

	function render(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath;
		$structure = @constant($this->getAttr('structure', 'REPORT2_STRUCTURE'));
		$template = $this->getAttr('template', 'GRAND Researcher Budget Request (2015-16).xls');
		$budgetText = $this->getAttr('budgetText', 'Budget');
		if(strtolower($this->getAttr("downloadOnly", "false")) == "true"){
	        $data = $this->getBlobValue();
            $link = $this->getDownloadLink();
            $html = "";
            if($data !== null && $data != ""){
                $html = "<a class='externalLink' href='{$link}&fileName=Budget.xls&mime=application/vnd.ms-excel'>Download&nbsp;<b>{$budgetText}</b></a>";
            }
            $item = $this->processCData($html);
            $wgOut->addHTML($item);
            return;
        }
		if(isset($_GET['downloadBudget']) && $_GET['downloadBudget'] == $this->getPostId()){
		    $data = $this->getBlobValue();
		    if($data != null){
		        $person = Person::newFromId($wgUser->getId());
		        header('Content-Type: application/vnd.ms-excel');
		        header("Content-disposition: attachment; filename=\"Budget.xls\"");
		        echo $data;
		        exit;
		    }
		}
		if(isset($_GET['budgetUploadForm']) && $_GET['budgetUploadForm'] == $this->getPostId()){
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
        $html = "<script type='text/javascript'>
                                var frameId = 0;
                                function alertreload{$this->getPostId()}(){
                                    var lastHeight = $('frame[name={$this->getPostId()}]').height();
                                    $('#budgetFrame{$this->getPostId()}').remove();
                                    frameId++;
                                    $('#budgetDiv{$this->getPostId()}').html(\"<iframe name='{$this->getPostId()}' id='budgetFrame{$this->getPostId()}' style='border-width:0;width:100%;' frameborder='0' src='../index.php/Special:Report?report={$this->getReport()->xmlName}&section={$this->getSection()->name}&budgetUploadForm={$this->getPostId()}{$projectGet}{$year}'></iframe>\");
                                    $('#budgetFrame{$this->getPostId()}').height(lastHeight);
                                    
                                }
                                function alertsize{$this->getPostId()}(pixels){
                                    $('#reportMain > div').stop();
                                    $('#budgetFrame{$this->getPostId()}').height(pixels);
                                    $('#budgetFrame{$this->getPostId()}').css('max-height', pixels);
                                }
                            </script>
                        <div>";
		if($template != ""){
		    $html .= "<h4>Download {$budgetText} Template</h4><ul><li><a href='$wgServer$wgScriptPath/data/{$template}'><b>{$budgetText} Template</b></a></li></ul>";
		}
		$html .= "<h4>{$budgetText} Upload</h4>
		                 <div id='budgetDiv{$this->getPostId()}'><iframe name='{$this->getPostId()}' id='budgetFrame{$this->getPostId()}' frameborder='0' style='border-width:0;height:100px;width:100%;' scrolling='none' src='../index.php/Special:Report?report={$this->getReport()->xmlName}&section={$this->getSection()->name}&budgetUploadForm={$this->getPostId()}{$projectGet}{$year}'></iframe></div>";
		$html .= "</div>";
		$item = $this->processCData($html);
        $wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    global $wgOut, $wgUser, $wgServer, $wgScriptPath;
	    $budgetText = $this->getAttr('budgetText', 'Budget');
	    $html = "";
	    if(strtolower($this->getAttr("downloadOnly", "false")) == "true"){
	        $data = $this->getBlobValue();
            $link = $this->getDownloadLink();
            if($data !== null && $data != ""){
                $html = "<a class='externalLink' href='{$link}&fileName=Budget.xls&mime=application/vnd.ms-excel'>Download&nbsp;<b>{$budgetText}</b></a>";
            }
	    }
	    else{
	        $structure = @constant($this->getAttr('structure', 'REPORT2_STRUCTURE'));
            $data = $this->getBlobValue();
		    if($data !== null){
		        $budget = new Budget("XLS", $structure, $data);
		        $budget = $this->filterCols($budget);
		        $budget = $budget->copy()->filterCols(V_PROJ, array(""));
		        if($structure == REPORT2_STRUCTURE){
		            self::checkTotals($budget, $this->getReport()->person, $this->getReport()->year);
		        }
		        $errors = self::checkDeletedProjects($budget, $this->getReport()->person, $this->getReport()->year);
		        foreach($errors as $key => $error){
	                $budget->errors[0][] = $error;
	            }
	            if($structure == REPORT2_STRUCTURE){
	                $budget = $this->colorBudget($budget);
	            }
		        $html .= "<div class='small'>{$budget->renderForPDF()}</div>";
		    }
		    else{
		        $html .= "You have not yet uploaded a ".strtolower($budgetText);
		    }
		}
		$item = $this->processCData($html);
		$wgOut->addHTML($item);
	}
	
	function budgetUploadForm(){
	    global $wgServer, $wgScriptPath;
	    $structure = @constant($this->getAttr('structure', 'REPORT2_STRUCTURE'));
	    $budgetText = $this->getAttr('budgetText', 'Budget');
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
                    <link rel='stylesheet' href='$wgServer$wgScriptPath/skins/cavendish/highlights.css.php' type='text/css' />
                    <script type='text/javascript'>
                        function load_page() {
                            var interval = setInterval(function(){
                                parent.alertsize{$this->getPostId()}($(\"#bodyContent\").height()+38);
                                if($(\"#bodyContent\").height() > 0){
                                    clearInterval(interval);
                                }
                            }, 100);
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
                        parent.alertreload{$this->getPostId()}();
                    </script>";
        }
        if(!$this->getSection()->checkPermission('w')){
            echo "<script type='text/javascript'>
                $(document).ready(function(){
                    $('textarea').prop('disabled', 'disabled');
                    $('input').prop('disabled', 'disabled');
                    $('button').prop('disabled', 'disabled');
                });
            </script>";
        }
        echo "</head>
              <body style='margin:0;'>
                    <div id='bodyContent'>
                        <form action='$wgServer$wgScriptPath/index.php/Special:Report?report={$this->getReport()->xmlName}&section={$this->getSection()->name}&budgetUploadForm={$this->getPostId()}{$projectGet}{$year}' method='post' enctype='multipart/form-data'>
                            <input type='file' name='budget' />
	                        <input type='submit' name='upload' value='Upload' />
	                    </form>";
	            
	    $data = $this->getBlobValue();
	    if($data !== null){
	        echo "<br /><a href='$wgServer$wgScriptPath/index.php/Special:Report?report={$this->getReport()->xmlName}&section={$this->getSection()->name}&downloadBudget={$this->getPostId()}{$projectGet}{$year}'>Download Uploaded $budgetText</a>";
		    $budget = new Budget("XLS", $structure, $data);
		    $budget = $this->filterCols($budget);
		    $budget = $budget->copy()->filterCols(V_PROJ, array(""));
		    $person = Person::newFromId($this->personId);
		    if($structure == REPORT2_STRUCTURE){
		        self::checkTotals($budget, $person, $this->getReport()->year);
		    }
		    $errors = self::checkDeletedProjects($budget, $person, $this->getReport()->year);
		    foreach($errors as $key => $error){
	            $budget->errors[0][] = $error;
	        }
	        if($structure == REPORT2_STRUCTURE){
	            $budget = $this->colorBudget($budget);
	        }
		    echo $budget->render();
		}
		else{
		    echo "You have not yet uploaded a ".strtolower($budgetText);
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
                $project = Project::newFromHistoricName($proj->toString());
                if(isset($alreadyUsed[$proj->toString()])){
                    $budget->xls[1][1+$i]->error = "'{$proj->toString()}' has already been used in another column";
                }
                $alreadyUsed[$proj->toString()] = true;
                if($project != null){
                    $total += intval(str_replace("$", "", $budget->copy()->rasterize()->select(V_PROJ, array($proj->toString()))->where(COL_TOTAL)->toString()));
                }
                $i++;
            }
        }
        $name = $budget->copy()->where(V_PERS_NOT_NULL)->select(V_PERS_NOT_NULL)->toString();

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
	}
	
	static function checkDeletedProjects($budget, $person, $year){
	    global $config;
	    $errors = array();
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
