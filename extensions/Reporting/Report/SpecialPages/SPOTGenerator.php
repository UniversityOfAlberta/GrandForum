<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SPOTGenerator'] = 'SPOTGenerator'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['SPOTGenerator'] = $dir . 'SPOTGenerator.i18n.php';
$wgSpecialPageGroups['SPOTGenerator'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'SPOTGenerator::createSubTabs';

class SPOTGenerator extends SpecialPage{

    function __construct() {
        parent::__construct("SPOTGenerator", STAFF.'+', true);
    }
    
    function showScript($names, $ids, $url){
        global $wgOut, $wgServer, $wgScriptPath;
        return "<script type='text/javascript'>
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
        </script>";
        }

    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath;

        $me = Person::newFromWgUser();
        $year = YEAR;
        $names = array();

	    if(isset($_GET['generatePDF'])){
	        $person = @Person::newFromId($_GET['person']);

	        $report = new DummyReport($_GET['report'], $person, null, $year);
	        $report->year = $year;
	        $submitted = $report->isSubmitted();
	        $report->generatePDF($person, $submitted);
	        exit;
	    }

	    $people = array();
	    foreach(Person::filterFaculty(Person::getAllPeople()) as $person){
	        $case = $person->getCaseNumber($year);
            if($case != ""){
                $people[$person->getId()] = $person;
                $names[] = $person->getNameForForms();
            }
	    }
        
        $url = "$wgServer$wgScriptPath/index.php/Special:SPOTGenerator?report=SPOTs&person=' + id + '&generatePDF=true";

        $html = $this->showScript($names, array_keys($people), $url);
        $html .= "<iframe name='downloadIframe' id='downloadIframe' style='display:none;'></iframe>";
        $html .= "<button type='button' id='allButton' onClick='clickAllButton();'>Generate All</button>&nbsp;<button type='button' onClick='clickStopButton();'>Stop Generation</button><br />
	    <table class='wikitable sortable' style='background:#ffffff;' cellspacing='1' cellpadding='3' frame='box' rules='all'>
	        <tr><th>Name</th><th>PDF Download</th><th>Generate PDF</th><th>Generation Status</th></tr>";
	    foreach($people as $person){
            $report = new DummyReport("SPOTs", $person);
            $report->year = $year;
            $check = $report->getLatestPDF();
            $tok = "";
            $downloadButton = "<a style='display:none;' target='downloadIframe' id='download{$person->getId()}' class='button'>Download PDF</a>";
            if(count($check) > 0){
                $tok = $check[0]['token'];
                $downloadButton = "<a id='download{$person->getId()}' target='downloadIframe' class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf=$tok&download'>Download PDF</a>";
            }
            $html .= "<tr><td>{$person->getNameForForms()}</td>
                          <td>{$downloadButton}</td>
                          <td><button type='button' id='button{$person->getId()}' onClick='clickButton(\"{$person->getNameForForms()}\", \"{$person->getId()}\", -1);'>Generate PDF</button></td>
                          <td id='status{$person->getId()}' align='center'><img style='display:none;' src='../skins/Throbber.gif' /><span></span></td>
                      </tr>";
	    }
	    $html .= "</table>";
        $wgOut->addHTML($html);
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF));
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        if(self::userCanExecute($wgUser)){
            $selected = ($wgTitle->getNSText() == "Special" && ($wgTitle->getText() == "SPOTGenerator")) ? "selected" : "";
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("SPOT Generator", 
                                                                   "$wgServer$wgScriptPath/index.php/Special:SPOTGenerator", 
                                                                   "$selected");
        }
    }
    
}

?>
