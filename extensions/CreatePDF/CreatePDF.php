<?php
$wgHooks['SkinTemplateContentActions'][] = 'CreatePDF::showTabs';

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['CreatePDF'] = 'CreatePDF';
$wgExtensionMessagesFiles['CreatePDF'] = $dir . 'CreatePDF.i18n.php';
$wgSpecialPageGroups['CreatePDF'] = 'report-reviewing';

function runCreatePDF($par) {
	CreatePDF::run($par);
}

class CreatePDF extends SpecialPage {

    static $types = array('ni' => 'NI',
                          'hqp' => 'HQP',
                          'project' => 'Project');

	function __construct() {
		wfLoadExtensionMessages('CreatePDF');
		SpecialPage::SpecialPage("CreatePDF", STAFF.'+', true, 'runCreatePDF');
	}
	
	function run(){
	    global $wgUser, $wgOut, $wgServer, $wgScriptPath;
	    $year = (isset($_GET['reportingYear'])) ? $_GET['reportingYear'] : REPORTING_YEAR;
	    $type = (isset($_GET['type'])) ? $_GET['type'] : 'ni';
	    if(isset($_GET['generatePDF'])){
	        $person = @Person::newFromId($_GET['person']);
	        $project = @Project::newFromId($_GET['project']);
	        $report = new DummyReport($_GET['report'], $person, $project);
	        if($project != null){
	            $leader = $project->getLeader();
	            $report->person = $leader;
	            $report->generatePDF(null);
	        }
	        else{
	            $report->generatePDF($person);
	        }
	        exit;
	    }
	    if(isset($_GET['downloadAll'])){
	        $people = array();
	        $me = Person::newFromId($wgUser->getId());
	        if($type == 'ni'){
	            foreach(Person::getAllPeopleDuring(CNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH) as $person){
	                $people[] = $person;
	            }
	            foreach(Person::getAllPeopleDuring(PNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH) as $person){
	                $people[] = $person;
	            }
	            $command = "zip -9 /tmp/NIReports.zip";
	            foreach($people as $person){
                    $sto = new ReportStorage($person);
                    $report = new DummyReport("NIReportPDF", $person, null, $year);
                    $check = $report->getPDF();
                    if(count($check) > 0){
                        $tok = $check[0]['token'];
                        $sto->select_report($tok);
                        $year = REPORTING_YEAR;
                        $pdf = $sto->fetch_pdf($tok, false);
                        $fileName = "/tmp/{$person->getReversedName()} NI Report {$year}.pdf";
                        file_put_contents($fileName, $pdf);
                        $command .= " \"$fileName\"";
                    }
	            }
	            chdir("/tmp");
	            exec(str_replace("/tmp/", "", $command));
	            $data = "";
	            $zip = file_get_contents("/tmp/NIReports.zip");
	            $sto = new ReportStorage($me);
                $sto->store_report($data, $zip, 0, 0, RPTP_NI_ZIP);
		        $tok = $sto->metadata('token');
                $tst = $sto->metadata('timestamp');
                $len = $sto->metadata('pdf_len');
                $json = array('tok'=>$tok, 'time'=>$tst, 'len'=>$len, 'name'=>"{$report->name}");
                exec("rm -f ".str_replace("zip -9 ", "", $command));
                header('Content-Type: application/json');
                header('Content-Length: '.strlen(json_encode($json)));
                echo json_encode($json);
	        }
	        else if($type == 'hqp'){
	            foreach(Person::getAllPeopleDuring(HQP, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH) as $hqp){
	                $people[] = $hqp;
	            }
	            $command = "zip -9 /tmp/HQPReports.zip";
	            foreach($people as $person){
                    $sto = new ReportStorage($person);
                    $report = new DummyReport("HQPReportPDF", $person, null, $year);
                    $check = $report->getPDF();
                    if(count($check) == 0){
                        $report = new DummyReport("NIReportPDF", $person, null, $year);
                        $check = $report->getPDF();
                        $report->setName("HQP Report");
                    }
                    if(count($check) > 0){
                        $tok = $check[0]['token'];
                        $sto->select_report($tok);
                        $year = REPORTING_YEAR;
                        $pdf = $sto->fetch_pdf($tok, false);
                        $fileName = "/tmp/{$person->getReversedName()} HQP Report {$year}.pdf";
                        file_put_contents($fileName, $pdf);
                        $command .= " \"$fileName\"";
                    }
	            }
	            chdir("/tmp");
	            exec(str_replace("/tmp/", "", $command));
	            $data = "";
	            $zip = file_get_contents("/tmp/HQPReports.zip");
	            $sto = new ReportStorage($me);
                $sto->store_report($data, $zip, 0, 0, RPTP_NI_ZIP);
		        $tok = $sto->metadata('token');
                $tst = $sto->metadata('timestamp');
                $len = $sto->metadata('pdf_len');
                $json = array('tok'=>$tok, 'time'=>$tst, 'len'=>$len, 'name'=>"{$report->name}");
                exec("rm -f ".str_replace("zip -9 ", "", $command));
                header('Content-Type: application/json');
                header('Content-Length: '.strlen(json_encode($json)));
                echo json_encode($json);
	        }
	        else if($type == 'project'){
	            $projects = Project::getAllProjects();
	            $command = "zip -9 /tmp/ProjectReports.zip";
	            foreach($projects as $project){
	                $leader = $project->getLeader();
	                if($leader != null){
	                    $sto = new ReportStorage($leader);
	                    $report = new DummyReport("ProjectReport", $leader, $project, $year);
	                    $check = $report->getPDF();
	                    if(count($check) > 0){
	                        $tok = $check[0]['token'];
	                        $sto->select_report($tok);
	                        $year = REPORTING_YEAR;
	                        $pdf = $sto->fetch_pdf($tok, false);
	                        $fileName = "/tmp/Project Report: {$project->getName()} {$year}.pdf";
	                        file_put_contents($fileName, $pdf);
	                        $command .= " \"$fileName\"";
	                    }
	                }
	            }
	            chdir("/tmp");
	            exec(str_replace("/tmp/", "", $command));
	            $data = "";
	            $zip = file_get_contents("/tmp/ProjectReports.zip");
	            $sto = new ReportStorage($me);
                $sto->store_report($data, $zip, 0, 0, RPTP_PROJ_ZIP);
		        $tok = $sto->metadata('token');
                $tst = $sto->metadata('timestamp');
                $len = $sto->metadata('pdf_len');
                $json = array('tok'=>$tok, 'time'=>$tst, 'len'=>$len, 'name'=>"{$report->name}");
                exec("rm -f ".str_replace("zip -9 ", "", $command));
                header('Content-Type: application/json');
                header('Content-Length: '.strlen(json_encode($json)));
                echo json_encode($json);
	        }
	        exit;
	    }
	    $url = "";
	    $names = array();
	    $ids = array();
	    if($type == 'ni'){
	        foreach(Person::getAllPeopleDuring(CNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH) as $person){
	            $names[] = $person->getName();
	            $ids[] = $person->getId();
	        }
	        foreach(Person::getAllPeopleDuring(PNI, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH) as $person){
	            $names[] = $person->getName();
	            $ids[] = $person->getId();
	        }
	        $url = "$wgServer$wgScriptPath/index.php/Special:CreatePDF?report=NIReport&person=' + id + '&generatePDF=true&reportingYear={$year}&ticket=0";
	    }
	    else if($type == 'hqp'){
	        foreach(Person::getAllPeopleDuring(HQP, $year.REPORTING_CYCLE_START_MONTH, $year.REPORTING_CYCLE_END_MONTH) as $person){
	            $names[] = $person->getName();
	            $ids[] = $person->getId();
	        }
	        $url = "$wgServer$wgScriptPath/index.php/Special:CreatePDF?report=HQPReport&person=' + id + '&generatePDF=true&reportingYear={$year}&ticket=0";
	    }
	    else if($type == 'project'){
	        foreach(Project::getAllProjects() as $project){
	            $names[] = $project->getName();
	            $ids[] = $project->getId();
	        }
	        $url = "$wgServer$wgScriptPath/index.php/Special:CreatePDF?report=ProjectReport&person=3&project=' + id + '&generatePDF=true&reportingYear={$year}&ticket=0";
	    }
	    $wgOut->addHTML("<iframe name='downloadIframe' id='downloadIframe' style='display:none;'></iframe>");
	    CreatePDF::showScript($names, $ids, $url);
	    if($type == 'ni'){
	        CreatePDF::showNITable($names, $ids);
	    }
	    else if($type == 'hqp'){
	        CreatePDF::showHQPTable($names, $ids);
	    }
	    else if($type == 'project'){
	        CreatePDF::showProjectTable($names, $ids);
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
	        
	        function clickDownloadAllButton(){
	            $('#downloadAllThrobber').css('display', 'inline-block');
	            $('#downloadAllButton').prop('disabled', true);
	            
	            $.get('{$_SERVER["REQUEST_URI"]}{$downloadGet}', function(data){
	                val = data;
	                if(typeof val.tok != 'undefined'){
	                    $('#downloadIframe').attr('src', '{$wgServer}{$wgScriptPath}/index.php/Special:ReportArchive?getpdf=' + val.tok);
	                }
	                $('#downloadAllThrobber').css('display', 'none');
	                $('#downloadAllButton').removeAttr('disabled');
	            });
	        }
	    
	        function clickButton(name, id, evalIndex){
	            $('#button' + id).prop('disabled', true);
	            $('#allButton').prop('disabled', true);
	            $('#downloadAllButton').prop('disabled', true);
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
                                    $('#downloadAllButton').removeAttr('disabled');
                                }
		                    }
		                    else{
		                        $('#allButton').removeAttr('disabled');
		                        $('#downloadAllButton').removeAttr('disabled');
		                    }
		                    $('#button' + id).removeAttr('disabled');
	                        $('#status' + id + ' > img').css('display', 'none');
	                        for(index in data){
		                        val = data[index];
		                        if(typeof val.tok != 'undefined'){
                                    var tok = val.tok;
                                    $('#status' + id + ' > span').html('<b style=\"color:#008800;\">SUCCESS</b>');
                                    $('#download' + id).attr('href', '$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf=' + tok);
                                    $('#download' + id).css('display', 'block');
                                }
                                else{
                                    $('#status' + Id + ' > span').html('<b style=\"color:#FF0000;\">ERROR</b>');
                                }
                                break;
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
                                    $('#downloadAllButton').removeAttr('disabled');
                                }
		                    }
		                    else{
		                        $('#allButton').removeAttr('disabled');
		                        $('#downloadAllButton').removeAttr('disabled');
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
	    $wgOut->setPageTitle("NI Report PDFs");
	    CreatePDF::tableHead();
	    foreach($names as $pName){
	        $person = Person::newFromName($pName);
	        $report = new DummyReport("NIReport", $person);
	        CreatePDF::tableRow($report, $person->getId(), $person->getName(), $person->getReversedName());
	    }
	    CreatePDF::tableFoot();
	}
	
	static function showHQPTable($names, $ids){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $wgOut->setPageTitle("HQP Report PDFs");
	    CreatePDF::tableHead();
	    foreach($names as $pName){
	        $person = Person::newFromName($pName);
	        $report = new DummyReport("HQPReport", $person);
	        $check = $report->getPDF();
            if(count($check) == 0){
                $report = new DummyReport("NIReport", $person);
                $check = $report->getPDF();
                $report->setName("HQP Report");
            }
	        CreatePDF::tableRow($report, $person->getId(), $person->getName(), $person->getReversedName());
	    }
	    CreatePDF::tableFoot();
	}
	
	static function showProjectTable($names, $ids){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $wgOut->setPageTitle("Project Report PDFs");
	    CreatePDF::tableHead();
	    foreach($names as $pName){
	        $project = Project::newFromName($pName);
	        $leader = $project->getLeader();
	        if($leader != null){
	            $report = new DummyReport("ProjectReportPDF", $leader, $project);
	            CreatePDF::tableRow($report, $project->getId(), $project->getName(), $project->getName());
	        }
	    }
	    CreatePDF::tableFoot();
	}
	
	static function tableHead(){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $wgOut->addHTML("
	    <button id='allButton' onClick='clickAllButton();'>Generate All</button>&nbsp;<button onClick='clickStopButton();'>Stop Generation</button>&nbsp;<button id='downloadAllButton' onClick='clickDownloadAllButton();'>Download All</button><img id='downloadAllThrobber' style='display:none;' src='../skins/Throbber.gif' /><br />
	    <table class='wikitable sortable' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
	        <tr><th>Name</th><th>PDF Download</th><th>Generate PDF</th><th>Generation Status</th></tr>");
	}
	
	static function tableRow($report, $id, $name, $displayName){
	    global $wgOut, $wgServer, $wgScriptPath;
	    $check = $report->getPDF();
	    $tok = "";
	    $downloadButton = "<a style='display:none;' target='downloadIframe' id='download{$id}' class='button'>Download Report PDF</a>";
	    if(count($check) > 0){
            $tok = $check[0]['token'];
            $downloadButton = "<a id='download{$id}' target='downloadIframe' class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf=$tok'>Download Report PDF</a>";
        }
        $wgOut->addHTML("<tr><td>{$displayName}</td>
                             <td>{$downloadButton}</td>
                             <td><button id='button{$id}' onClick='clickButton(\"{$name}\", \"{$id}\", -1);'>Generate Report PDF</button></td>
                             <td id='status{$id}' align='center'><img style='display:none;' src='../skins/Throbber.gif' /><span></span></td></tr>");
	}
	
	static function tableFoot(){
	    global $wgOut;
	    $wgOut->addHTML("</table>");
	}
    
    static function showTabs(&$content_actions){
        global $wgTitle, $wgUser, $wgServer, $wgScriptPath;
        $current_selection = (isset($_GET['type'])) ? $_GET['type'] : "ni";
        
        if($wgTitle->getText() == "CreatePDF"){
            $content_actions = array();
            
            foreach(CreatePDF::$types as $key => $type){
                if($current_selection == $key){
                    $class = "selected";
                }
                else{
                    $class = false;
                }
                $content_actions[] = array (
                     'class' => $class,
                     'text'  => $type,
                     'href'  => "$wgServer$wgScriptPath/index.php/Special:CreatePDF?type={$key}",
                    );
            }
        }
        return true;
    }
}
?>
