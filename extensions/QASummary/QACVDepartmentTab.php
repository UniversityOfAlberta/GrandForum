<?php

class QACVDepartmentTab extends AbstractTab {

    var $department;
    var $depts;

    function __construct($department, $depts){
        parent::__construct($depts[0]);
        $this->department = $department;
        $this->depts = $depts;
    }
    
    function showScript($names, $ids, $url){
	    global $wgOut, $wgServer, $wgScriptPath;
	    return "<script type='text/javascript'>
	        var MAX_REQUESTS = 5;
	    
	        var evalNames{$this->id} = eval('".json_encode($names)."');
	        var evalIds{$this->id} = eval('".json_encode($ids)."');
	        
	        var objAjax{$this->id} = Array();
	        var stopAjax{$this->id} = false;
	        var nextId{$this->id} = MAX_REQUESTS;
	        
	        function clickAllButton{$this->id}(){
	            nextId{$this->id} = MAX_REQUESTS;
                for(i = 0; i < nextId{$this->id}; i++){
                    var name = evalNames{$this->id}[i];
                    var id = evalIds{$this->id}[i];
                    clickButton{$this->id}(name, id, 0);
                }
                return false;
	        }
	        
	        function clickStopButton{$this->id}(){
	            for(index in objAjax{$this->id}){
	                var ajax = objAjax{$this->id}[index];
	                stopAjax{$this->id} = true;
	                ajax.abort();
	            }
	            objAjax{$this->id} = Array();
	            stopAjax{$this->id} = false;
	            return false;
	        }
	        
	        function clickDownloadAllButton{$this->id}(){
	            $('#downloadAllThrobber{$this->id}').css('display', 'inline-block');
	            $('#downloadAllButton{$this->id}').prop('disabled', true);
	            
	            $.get('{$_SERVER["REQUEST_URI"]}&downloadAll', function(data){
	                val = data;
	                if(typeof val.tok != 'undefined'){
	                    $('#downloadIframe{$this->id}').attr('src', '{$wgServer}{$wgScriptPath}/index.php/Special:ReportArchive?getpdf=' + val.tok);
	                }
	                $('#downloadAllThrobber{$this->id}').css('display', 'none');
	                $('#downloadAllButton{$this->id}').removeAttr('disabled');
	            });
	        }
	    
	        function clickButton{$this->id}(name, id, evalIndex){
	            $('#button' + id).prop('disabled', true);
	            $('#allButton{$this->id}').prop('disabled', true);
	            $('#downloadAllButton{$this->id}').prop('disabled', true);
	            $('#status' + id + ' > span').html('');
	            $('#status' + id + ' > img').css('display', 'block');
	            objAjax{$this->id}.push($.ajax({
	                    dataType: 'json',
	                    data: '',
	                    type: 'GET',
	                    url : '$url',
	                    success : function(data){
	                        if(evalIndex >= 0 && stopAjax{$this->id} == false){
	                            var localNextId = nextId{$this->id}++;
	                            if(localNextId in evalNames{$this->id}){
	                                var newName = evalNames{$this->id}[localNextId];
                                    var newId = evalIds{$this->id}[localNextId];
                                    clickButton{$this->id}(newName, newId, localNextId);
                                }
                                else if(localNextId >= evalNames{$this->id}.length){
                                    $('#allButton{$this->id}').removeAttr('disabled');
                                    $('#downloadAllButton{$this->id}').removeAttr('disabled');
                                }
		                    }
		                    else{
		                        $('#allButton{$this->id}').removeAttr('disabled');
		                        $('#downloadAllButton{$this->id}').removeAttr('disabled');
		                    }
		                    $('#button' + id).removeAttr('disabled');
	                        $('#status' + id + ' > img').css('display', 'none');
	                        for(index in data){
		                        val = data[index];
		                        if(typeof val.tok != 'undefined'){
                                    var tok = val.tok;
                                    $('#status' + id + ' > span').html('<b style=\"color:#008800;\">SUCCESS</b>');
                                    $('#download' + id).attr('href', '$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf=' + tok + '&download');
                                    $('#download' + id).css('display', 'block');
                                }
                                else{
                                    $('#status' + id + ' > span').html('<b style=\"color:#FF0000;\">ERROR</b>');
                                }
                                break;
		                    }
		                },
		                error : function(data){
		                    if(evalIndex >= 0 && stopAjax{$this->id} == false){
	                            var localNextId = nextId{$this->id}++;
	                            if(localNextId in evalNames{$this->id}){
	                                var newName = evalNames{$this->id}[localNextId];
                                    var newId = evalIds{$this->id}[localNextId];
                                    clickButton{$this->id}(newName, newId, localNextId);
                                }
                                else if(localNextId >= evalNames{$this->id}.length){
                                    $('#allButton{$this->id}').removeAttr('disabled');
                                    $('#downloadAllButton').removeAttr('disabled');
                                }
		                    }
		                    else{
		                        $('#allButton{$this->id}').removeAttr('disabled');
		                        $('#downloadAllButton{$this->id}').removeAttr('disabled');
		                    }
		                    $('#button' + id).removeAttr('disabled');
	                        $('#status' + id + ' > img').css('display', 'none');
		                    $('#status' + id + ' > span').html('<b style=\"color:#FF0000;\">ERROR</b>');
		                }
	            }));
	            return false;
	        }
	    </script>";
	}
    
    function generateBody(){
        global $wgOut, $config, $wgServer, $wgScriptPath;

        $me = Person::newFromWgUser();
        $year = YEAR;
        $people = array();
        $names = array();

	    if(isset($_GET['generatePDF']) && isset($_GET['showTab']) && $_GET['showTab'] == $this->id){
	        $person = @Person::newFromId($_GET['person']);

	        $report = new DummyReport($_GET['report'], $person, null, $year);
	        $report->year = $year;
	        $submitted = $report->isSubmitted();
	        $report->generatePDF($person, $submitted);
	        close();
	    }
	    
        foreach(array_merge(Person::getAllPeopleDuring(NI, ($year-6).CYCLE_START_MONTH, EOT), 
                            Person::getAllPeopleDuring("ATS", ($year-6).CYCLE_START_MONTH, EOT)) as $person){
            foreach($person->getUniversitiesDuring(($year-6).CYCLE_START_MONTH, EOT) as $uni){
                if($uni['department'] == $this->department && 
                   $uni['university'] == "University of Alberta"){
                    $people[$person->getId()] = $person;
                    $names[] = $person->getNameForForms();
                    break;
                }
            }
        }
        
        if(isset($_GET['downloadAll']) && isset($_GET['showTab']) && $_GET['showTab'] == $this->id){
            $command = "zip -9 /tmp/{$this->id}.zip";
            foreach($people as $person){
                $sto = new ReportStorage($person);
                $report = new DummyReport("QACV2", $person, null, $year);
                $report->year = $year;
                $check = $report->getLatestPDF();
                if(count($check) > 0){
                    $tok = $check[0]['token'];
                    $sto->select_report($tok);
                    $pdf = $sto->fetch_pdf($tok, false);
                    $fileName = "/tmp/{$person->getNameForForms()}.pdf";
                    file_put_contents($fileName, $pdf);
                    $command .= " \"$fileName\"";
                }
            }
            chdir("/tmp");
            exec(str_replace("/tmp/", "", $command));
            $data = "";
            $zip = file_get_contents("/tmp/{$this->id}.zip");
            $sto = new ReportStorage($me);
            $str = "";
            $sto->store_report($data, $str, $zip, 0, 0, strtoupper("RPTP_QACVS_{$this->id}"));
	        $tok = $sto->metadata('token');
            $tst = $sto->metadata('timestamp');
            $len = $sto->metadata('pdf_len');
            $json = array('tok'=>$tok, 'time'=>$tst, 'len'=>$len, 'name'=>"{$report->name}");
            exec("rm -f /tmp/{$this->id}.zip");
            header('Content-Type: application/json');
            header('Content-Length: '.strlen(json_encode($json)));
            echo json_encode($json);
	        close();
	    }
        
        $url = "$wgServer$wgScriptPath/index.php/Special:QACVGenerator?showTab={$this->id}&report=QACV2&person=' + id + '&generatePDF=true";
        
        $html = $this->showScript($names, array_keys($people), $url);
        $html .= "<iframe name='downloadIframe{$this->id}' id='downloadIframe{$this->id}' style='display:none;'></iframe>";
        $html .= "<button type='button' id='allButton{$this->id}' onClick='clickAllButton{$this->id}();'>Generate All</button>&nbsp;<button type='button' onClick='clickStopButton{$this->id}();'>Stop Generation</button>&nbsp;<button type='button' id='downloadAllButton{$this->id}' onClick='clickDownloadAllButton{$this->id}();'>Download All</button><img id='downloadAllThrobber{$this->id}' style='display:none;' src='../skins/Throbber.gif' /><br />
	    <table class='wikitable sortable' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
	        <tr><th>Name</th><th>PDF Download</th><th>Generate PDF</th><th>Generation Status</th></tr>";
	    foreach($people as $person){
	        $report = new DummyReport("QACV2", $person);
	        $report->year = $year;
	        $check = $report->getLatestPDF();
	        $tok = "";
	        $downloadButton = "<a style='display:none;' target='downloadIframe{$this->id}' id='download{$person->getId()}' class='button'>Download Report PDF</a>";
	        if(count($check) > 0){
                $tok = $check[0]['token'];
                $downloadButton = "<a id='download{$person->getId()}' target='downloadIframe{$this->id}' class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf=$tok&download'>Download Report PDF</a>";
            }
            $html .= "<tr><td>{$person->getNameForForms()}</td>
                          <td>{$downloadButton}</td>
                          <td><button type='button' id='button{$person->getId()}' onClick='clickButton{$this->id}(\"{$person->getNameForForms()}\", \"{$person->getId()}\", -1);'>Generate Report PDF</button></td>
                          <td id='status{$person->getId()}' align='center'><img style='display:none;' src='../skins/Throbber.gif' /><span></span></td>
                      </tr>";
	    }
	    $html .= "</table>";
        
        $this->html .= $html;
    }

}
?>
