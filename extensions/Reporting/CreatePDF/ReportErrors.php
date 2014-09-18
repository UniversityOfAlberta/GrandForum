<?php

$wgHooks['SubLevelTabs'][] = 'ReportErrors::createSubTabs';

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ReportErrors'] = 'ReportErrors';
$wgExtensionMessagesFiles['ReportErrors'] = $dir . 'ReportErrors.i18n.php';
$wgSpecialPageGroups['ReportErrors'] = 'report-reviewing';

function runReportErrors($par) {
	ReportErrors::run($par);
}

class ReportErrors extends SpecialPage {

    static $types = array('ni' => 'NI PDF Diff');//,
                          //'project' => 'Project PDF Diff');

	function __construct() {
		wfLoadExtensionMessages('ReportErrors');
		SpecialPage::SpecialPage("ReportErrors", STAFF.'+', true, 'runReportErrors');
	}
	
	function run(){
	    global $wgUser, $wgOut, $wgServer, $wgScriptPath;
	    $year = (isset($_GET['reportingYear'])) ? $_GET['reportingYear'] : REPORTING_YEAR;
	    $type = (isset($_GET['type'])) ? $_GET['type'] : 'ni';
	    $url = "";
	    $names = array();
	    $ids = array();
	    if(isset($_GET['calculateDiff'])){
	        $person = @Person::newFromId($_GET['person']);
	        $project = @Project::newFromId($_GET['project']);
	        if($project != null && $project->deleted){
	            $_GET['report'] = "ProjectFinalReport";
	        }
	        $report = new DummyReport($_GET['report'], $person, $project);
	        $latestPDF = $report->getPDF();
	        $previousPDF = $report->getPDF(true);
	        $sto = new ReportStorage($person);

	        $latestHTML = str_replace(">", "&gt;", str_replace("<", "&lt;", $sto->fetch_html(@$latestPDF[0]['token'])));
	        $previousHTML = str_replace(">", "&gt;", str_replace("<", "&lt;", $sto->fetch_html(@$previousPDF[0]['token'])));
	        $diff = "";
	        $newLines = array();
	        if($latestHTML == $previousHTML){
	            $nIns = 0;
	            $nDel = 0;
	            $diff = $latestHTML;
	            $newLines = explode("\n", $diff);
	        }
	        else{
	            $diff = htmlDiff($previousHTML, $latestHTML, "\n");
	            
	            $nIns = 0;
	            $nDel = 0;
	            $lines = explode("\n", $diff);
	            $state = "normal";
	            
	            foreach($lines as $line){
	                $newLine = "";
	                if(strstr($line, '<ins>') !== false){
	                    $state = "ins";
	                }
	                else if(strstr($line, '<del>') !== false){
	                    $state = "del";
	                }
	                
	                if($state == 'ins'){
	                    $nIns++;
	                    $newLine = "<ins>$line</ins>";
	                }
	                else if($state == 'del'){
	                    $nDel++;
	                    $newLine = "<del>$line</del>";
	                }
	                else if($state == 'normal'){
	                    $newLine = "$line";
	                }

	                if(strstr($line, '</ins>') !== false){
	                    $state = "normal";
	                }
	                else if(strstr($line, '</del>') !== false){
	                    $state = "normal";
	                }
	                $newLines[] = $newLine;
	            }
	        }
            $diff = "<table cellspacing=0 style='font-size:10px;'>";
            $offset = 0;
            foreach($newLines as $key => $line){
                if($key %2 == 0){
                    $color = "#eee";
                }
                else{
                    $color = "#fff";
                }
                if(strstr($line, "<del>") !== false && strstr($line, "<ins>") === false){
                    $offset++;
                    $diff .= "<tr><td id='line".($key+1)."' style='border-bottom:1px solid #ccc;vertical-align:top;font-family:monospace;'></td><td style='border-bottom:1px solid #ccc;background:$color;font-family:monospace;'>$line</td></tr>\n";
                }
                else{
                    $diff .= "<tr><td id='line".($key+1)."' style='border-bottom:1px solid #ccc;vertical-align:top;font-family:monospace;'>".($key+1-$offset).".</td><td style='border-bottom:1px solid #ccc;background:$color;font-family:monospace;'>$line</td></tr>\n";
                }
            }
            $diff .= "</table>";
	        $status = "<span style='color:#008800'>+$nIns</span> / <span style='color:#FF0000'>-$nDel</span>";
	        header('Content-Type: application/json');
	        echo json_encode(array('status' => $status,
	                               'diff' => "<span style='font-family: monospace;'>$diff</span>"));
	        exit;
	    }
	    if($type == 'ni'){
	        foreach(Person::getAllPeopleDuring(CNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH) as $person){
	            if(array_search($person->getId(), $ids) === false){
	                $names[] = $person->getName();
	                $ids[] = $person->getId();
	            }
	        }
	        foreach(Person::getAllPeopleDuring(PNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH) as $person){
	            if(array_search($person->getId(), $ids) === false){
	                $names[] = $person->getName();
	                $ids[] = $person->getId();
	            }
	        }
	        if($type == 'ni_comments'){
	            $url = "$wgServer$wgScriptPath/index.php/Special:ReportErrors?report=ProjectNIComments&person=' + id + '&calculateDiff=true";
	        }
	        else{
	            $url = "$wgServer$wgScriptPath/index.php/Special:ReportErrors?report=NIReport&person=' + id + '&calculateDiff=true";
	        }
	    }
	    else if($type == 'project'){
	        foreach(Project::getAllProjectsDuring(REPORTING_CYCLE_START, REPORTING_CYCLE_END) as $project){
	            if(array_search($project->getId(), $ids) === false){
	                $names[] = $project->getName();
	                $ids[] = $project->getId();
	            }
	        }
	        $url = "$wgServer$wgScriptPath/index.php/Special:ReportErrors?report=ProjectReport&person=3&project=' + id + '&calculateDiff=true";
	    }
	    ReportErrors::showScript($names, $ids, $url);
	    if($type == 'ni'){
	        ReportErrors::showNITable($names, $ids);
	    }
	    else if($type == 'ni_comments'){
	        //ReportErrors::showNICommentsTable($names, $ids);
	    }
	    else if($type == 'hqp'){
	        //ReportErrors::showHQPTable($names, $ids);
	    }
	    else if($type == 'project'){
	        //ReportErrors::showProjectTable($names, $ids);
	    }
	}
	
	static function showScript($names, $ids, $url){
	    global $wgOut, $wgServer, $wgScriptPath;
	    if(isset($_GET['type'])){
	        $downloadGet = "&downloadAll";
	    }
	    else{
	        $downloadGet = "?downloadAll";
	    }
	    $wgOut->addScript("<script type='text/javascript'>
	        var MAX_REQUESTS = 5;
	    
	        var evalNames = eval('".json_encode($names)."');
	        var evalIds = eval('".json_encode($ids)."');
	        
	        var objAjax = Array();
	        var stopAjax = false;
	        var nextId = MAX_REQUESTS;
	        
	        function clickAllButton(){
	            nextId = MAX_REQUESTS;
                for(i = 0; i < nextId; i++){
                    var name = evalNames[i];
                    var id = evalIds[i];
                    clickButton(name, id, 0);
                }
                return false;
	        }
	        
	        function clickStopButton(){
	            for(index in objAjax){
	                var ajax = objAjax[index];
	                stopAjax = true;
	                ajax.abort();
	            }
	            objAjax = Array();
	            stopAjax = false;
	            return false;
	        }
	    
	        function clickButton(name, id, evalIndex){
	            $('#button' + id).prop('disabled', true);
	            $('#allButton').prop('disabled', true);
	            $('#status' + id + ' > span').html('');
	            $('#status' + id + ' > img').css('display', 'block');
	            objAjax.push($.ajax({
	                    dataType: 'json',
	                    data: '',
	                    type: 'GET',
	                    url : '$url',
	                    success : function(data){
	                        if(evalIndex >= 0 && stopAjax == false){
	                            var localNextId = nextId++;
	                            if(localNextId in evalNames){
	                                var newName = evalNames[localNextId];
                                    var newId = evalIds[localNextId];
                                    clickButton(newName, newId, localNextId);
                                }
                                else if(localNextId >= evalNames.length){
                                    $('#allButton').removeAttr('disabled');
                                }
		                    }
		                    else{
		                        $('#allButton').removeAttr('disabled');
		                    }
		                    $('#button' + id).removeAttr('disabled');
	                        $('#status' + id + ' > img').css('display', 'none');
	                        if(typeof data.status != 'undefined'){
                                var status = data.status;
                                var diff = data.diff;
                                $('#status' + id + ' > span').html('<b style=\"color:#008800;\">' + status + '</b>');
                                if(diff != ''){
                                    var diffDiv = $('#diff' + id);
                                    $(diffDiv).html(diff);
                                    $(diffDiv).dialog({
                                                            autoOpen: false,
                                                            height: $(window).height()/1.25,
                                                            width: '75%'
                                                           });
                                    $('#viewDiff' + id).html('<button id=\"view' + id + '\">View Diff</button>');
                                    $('#view' + id).click(function(){
                                        var quickLinks = $('#quickLinks', $(diffDiv).parent());
                                        $(quickLinks).empty();
                                        var trs = $('tr', $(diffDiv));
                                        nLines = $(trs).length;
                                        $.each($(trs), function(index, val){
                                            var percent = ((index+1)/nLines)*100;
                                            var ins = $('ins' ,$(val));
                                            var del = $('del' ,$(val));
                                            
                                            $(ins).width('100%');
                                            $(del).width('100%');
                                            if($(ins).length >= 1){
                                                $(quickLinks).append('<a id=\'line' + (index+1) + 'Link\' style=\'background:#AAFFAA;width:5px;height:5px;position:absolute;left:0;top:' + percent + '%;cursor:pointer;border:1px solid #008800;\'></a>');
                                            }
                                            else if($(del).length >= 1){
                                                $(quickLinks).append('<a id=\'line' + (index+1) + 'Link\' style=\'background:#FFAAAA;width:5px;height:5px;position:absolute;left:6px;top:' + percent + '%;cursor:pointer;border:1px solid #FF0000;\'></a>');
                                            }
                                            $('#line' + (index+1) + 'Link', $(quickLinks)).click(function(){
                                                $(diffDiv).scrollTo('#line' + (index+1));
                                            });
                                        });
                                        $(diffDiv).dialog('open');
                                        return false;
                                    });
                                    $(diffDiv).parent().append('<div id=\'quickLinks\' style=\'position:absolute;top:40px;bottom:10px;left:0;\'></div>');
                                }
                            }
                            else{
                                $('#status' + id + ' > span').html('<b style=\"color:#FF0000;\">ERROR</b>');
                            }
		                },
		                error : function(data){
		                    if(evalIndex >= 0 && stopAjax == false){
	                            var localNextId = nextId++;
	                            if(localNextId in evalNames){
	                                var newName = evalNames[localNextId];
                                    var newId = evalIds[localNextId];
                                    clickButton(newName, newId, localNextId);
                                }
                                else if(localNextId >= evalNames.length){
                                    $('#allButton').removeAttr('disabled');
                                }
		                    }
		                    else{
		                        $('#allButton').removeAttr('disabled');
		                    }
		                    $('#button' + id).removeAttr('disabled');
	                        $('#status' + id + ' > img').css('display', 'none');
		                    $('#status' + id + ' > span').html('<b style=\"color:#FF0000;\">ERROR</b>');
		                }
	            }));
	            return false;
	        }
	    </script>");
	}
	
	static function showNITable($names, $ids){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $wgOut->setPageTitle("NI Report PDF Diff");
	    ReportErrors::tableHead();
	    $alreadyDone = array();
	    foreach($names as $pName){
	        if(isset($alreadyDone[$pName])){
	            continue;
	        }
	        $alreadyDone[$pName] = true;
	        $person = Person::newFromName($pName);
	        ReportErrors::tableRow($person->getId(), $person->getName(), $person->getReversedName());
	    }
	    ReportErrors::tableFoot();
	}
	
	static function showProjectTable($names, $ids){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $wgOut->setPageTitle("Project Report PDFs");
	    ReportErrors::tableHead();
	    $alreadyDone = array();
	    foreach($names as $pName){
	        if(isset($alreadyDone[$pName])){
	            continue;
	        }
	        $alreadyDone[$pName] = true;
	        $project = Project::newFromName($pName);
	        $leader = $project->getLeader();
	        if($leader != null){
	            $report = new DummyReport("ProjectReportPDF", $leader, $project);
	            ReportErrors::tableRow($report, $project->getId(), $project->getName(), $project->getName());
	        }
	    }
	    ReportErrors::tableFoot();
	}
	
	static function tableHead(){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $wgOut->addHTML("
	    <button id='allButton' onClick='clickAllButton();'>Calculate All</button>&nbsp;<button onClick='clickStopButton();'>Stop Calculation</button><br />
	    <table class='wikitable sortable' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
	        <tr><th>Name</th><th style='min-width:110px;'>Diff</th><th>Calculate Diff</th><th>Calculation Status</th></tr>");
	}
	
	static function tableRow($id, $name, $displayName){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $diffDiv = "<div id='diff{$id}' title='".str_replace("'", "&#39;", $displayName)." Diff'></div><div id='viewDiff{$id}'></div>";
        $wgOut->addHTML("<tr><td>{$displayName}</td>
                             <td>$diffDiv</td>
                             <td><button id='button{$id}' onClick='clickButton(\"{$name}\", \"{$id}\", -1);'>Calculate</button></td>
                             <td id='status{$id}' align='center'><img style='display:none;' src='../skins/Throbber.gif' /><span></span></td></tr>");
	}
	
	static function tableFoot(){
	    global $wgOut;
	    $wgOut->addHTML("</table>");
	}
    
    static function createSubTabs($tabs){
        global $wgTitle, $wgUser, $wgServer, $wgScriptPath;
        if($wgTitle->getText() == "ReportErrors"){
            $current_selection = (isset($_GET['type'])) ? $_GET['type'] : "ni";
            foreach(ReportErrors::$types as $key => $type){
                $selected = ($current_selection == $key) ? "selected" : false;
                $tabs['Other']['subtabs'][] = TabUtils::createSubTab($type, "$wgServer$wgScriptPath/index.php/Special:ReportErrors?type={$key}", $selected);
            }
        }
        return true;
    }
}
?>
