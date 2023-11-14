<?php

class StickyNotesReportItem extends AbstractReportItem {

	function render(){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath;
		$me = $this->getReport()->person;
        $reportType = $this->getAttr("reportType", 'HQPReport');
        $year = $this->getAttr("year", $this->getReport()->year);
        $height = $this->getAttr("height", "700px");
        $project = null;
        $person = Person::newFromId($this->personId);
        $report = new DummyReport($reportType, $person, $project, $year, true);
        $report->person = $person;
        $tok = false;
        $check = $report->getPDF(false);
        if (count($check) > 0) {
            $tok = $check[0]['token'];
            $item = "
            <style type='text/css'>
                #pdfHTML {
                    background: #3c3c3c;
                    padding: 15px;
                }
                
                #pdfHTML #pdfBody {
                    background: #FFFFFF;
                    padding: 25px;
                    margin: 25px;
                    box-shadow: 5px 5px 10px black;
                }
                
                #pdfHTML #pdfBody h1, h2, h3 {
                    line-height: 1.25em;
                }
                
                #pdfHTML #pdfBody hr {
                    left: 25px !important;
                    right: 25px !important;
                    top: 20px !important;
                }
                
                #pdfHTML #pdfBody #page_header {
                    margin-top: -22px !important;
                }
                
                .word {
                    position: relative;
                }
                
                .word:hover, .word.highlighted {
                    background: #ffef93;
                    cursor: pointer;
                }
                
                .word.highlighted:hover {
                    background: #ecd96f;
                }
                
                .sticky {
                    cursor: auto;
                    font-size: 11px;
                    font-weight: normal;
                    line-height: 1em;
                    padding: 5px;
                    background: #ffef93;
                    color: #444444;
                    box-shadow: 1px 1px 2px black;
                    position: absolute;
                    top: 0;
                    left: 0;
                    z-index: 1000;
                    text-align: left;
                }
                
                .sticky textarea {
                    font-size: 11px;
                    font-weight: normal;
                    line-height: 1.1em;
                    padding: 5px;
                    background: #ffef93;
                    color: #444444;
                    width: 150px;
                    min-width: 150px;
                    height: 75px;
                    min-height: 75px;
                    border: none !important;
                    outline: none !important;
                    box-shadow: none !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    margin-top: 5px !important;
                }
                
                .sticky a.X {
                    cursor: pointer;
                    float: right;
                    padding-left: 3px;
                    font-weight: bold;
                    font-size: 13px;
                }
                
                #pdfHTML #pdfBody table table.wikitable > tr > th, 
                #pdfHTML #pdfBody table table.wikitable > tr > td, 
                #pdfHTML #pdfBody table table.wikitable > * > tr > th, 
                #pdfHTML #pdfBody table table.wikitable > * > tr > td {
                    border: none !important;
                }
                
            </style>
            <script type='text/javascript'>
                $.get('$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$tok}&html', function(response){
                    var stickies = ".json_encode($this->getBlobValue()).";
                    if(stickies == null){
                        stickies = new Array();
                    }
                    sticky(response, '{$this->getPostId()}', stickies); 
                });
            </script>";
            $item .= "<a id='showAllStickies' style='cursor:pointer;font-weight:bold;'>Show All Stickies</a> | <a id='hideAllStickies' style='cursor:pointer;font-weight:bold;'>Hide All Stickies</a><div id='pdfHTML'></div>";
            $item = $this->processCData($item);
            $wgOut->addHTML($item);
        }
	}
	
	function renderForPDF(){
	    global $wgOut;
	    //$wgOut->addHTML($this->processCData(""));
	}
}

?>
