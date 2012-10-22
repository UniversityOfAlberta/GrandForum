<?php

function ajaxGetBudget(){
    global $wgOut;
    $year = 2010;
    if(isset($_GET['id'])){
        $id = $_GET['id'];
        if(strstr($id, "proj") !== false){
            $id = str_replace("proj", "", $id);
            $project = Project::newFromId($id);
            $budget = $project->getSupplBudget($year);
            if($budget != null){
                echo $budget->render();
            }
            else{
                echo "{$project->getName()} did not submit a budget last year";
            }
        }
        else if(strstr($id, "pers") !== false){
            $id = str_replace("pers", "", $id);
            $person = Person::newFromId($id);
            $budget = $person->getSupplementalBudget($year);
            if($budget != null){
                echo $budget->copy()->filterCols(V_PROJ, array(""))->render();
            }
            else{
                echo "{$person->getNameForForms()} did not submit a budget last year";
            }
        }
        else{
            echo "Unknown Id";
        }
    }
    else{
        echo "No Id Specified";
    }
}

function showEvalBudgets($type="Project"){
    global $wgOut, $reporteeId, $wgServer, $wgScriptPath;
	    $person = Person::newFromId($reporteeId);
	    $subs = $person->getEvaluateSubs();
	    
	    $projectBudgets = array();
	    $personBudgets = array();
		
		// Pass 2: to display researcher budgets
	    foreach($subs as $project){
	        if($project instanceof Project && $type == "Project"){
	            $projectBudget = $project->getRequestedBudget(2011);
	            if($projectBudget != null){
                    $projectBudgets[$project->getName()] = $projectBudget;
                }
	        }
		    else if ($project instanceof Person && $type == "Researcher") {
			    // This is a person actually, so include his/her budget.
			    $budget = $project->getRequestedBudget(2011);
			    if($budget != null){
				    $personBudgets[$project->getName()] = $budget->copy()->filterCols(V_PROJ, array(""));
				}
		    }
        }
        $wgOut->addScript("<script type='text/javascript'>
                            $(function() {
                                $('#dialog').dialog({
			                        autoOpen: false,
			                        width: 'auto'
		                        });
		                        
		                        $('button.budget').click(function(){
		                            $('#dialog').html('Loading...');
		                            $('#dialog').dialog('option', 'title', this.name);
		                            $('#dialog').dialog('open');
		                            $.get('$wgServer$wgScriptPath/index.php/Special:Evaluate?ajaxGetBudget&id=' + this.id, function(response){
		                                $('#dialog').html(response);
		                            });
		                        });
		                    });
		                   </script>");
        $wgOut->addHTML("<div style='font-size:12px;' id='dialog' title=''>
	                        
                        </div>");
        foreach($projectBudgets as $key => $budget){
            $project = Project::newFromName($key);
            $wgOut->addHTML("<h2><a style='cursor:pointer;' onClick='show(\"{$key}\")'>Budget for {$key}</a></h2>");
            $wgOut->addHTML("<div style='display:none' id='sub{$key}'>");
            $wgOut->addHTML("<button type='button' class='budget' id='proj{$project->getId()}' name='{$project->getName()} 2011 Budget'>View Last Year's Budget</button>");
            $wgOut->addHTML($budget->render());
            $wgOut->addHTML("</div>");
        }
        foreach($personBudgets as $key => $budget){
            $person = Person::newFromName($key);
            $wgOut->addHTML("<h2><a style='cursor:pointer;' onClick='show(\"".str_replace(".", "", $key)."\")'>Budget for {$key}</a></h2>");
            $wgOut->addHTML("<div style='display:none' id='sub".str_replace(".", "", $key)."'>");
            $wgOut->addHTML("<button type='button' class='budget' id='pers{$person->getId()}' name='{$person->getNameForForms()} 2011 Budget'>View Last Year's Budget</button>");
            $wgOut->addHTML($budget->render());
            $wgOut->addHTML("</div>");
        }
}

?>
