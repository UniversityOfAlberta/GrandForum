<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['InPersonFollowup'] = 'InPersonFollowup'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['InPersonFollowup'] = $dir . 'InPersonFollowup.i18n.php';
$wgSpecialPageGroups['InPersonFollowup'] = 'reporting-tools';

//$wgHooks['SubLevelTabs'][] = 'InPersonFollowup::createSubTabs';

function runInPersonFollowup($par) {
    InPersonFollowup::execute($par);
}

class InPersonFollowup extends SpecialPage {
    static $pageTitle = "In-Person Assessment";
    static $reportName = "InPersonAssessment";
    static $rpType = "RP_AVOID_INPERSON";
    
    static $map = array(
        'avoid_vision' => 'Age',
        'avoid_hearing' => 'Gender',
        'avoid_hearing_whisper1' => 'Gender (Specify)',
        'avoid_hearing_whisper2' => 'Postal Code',
    );


    function __construct() {
        SpecialPage::__construct("InPersonFollowup", null, true);
    }
    
    function userCanExecute($wgUser){
        $person = Person::newFromUser($wgUser);
        return $person->isRole('Assessor');
    }

    static function getHeader($report, $type=false, $simple=false){
        $html = "";
        if(!$simple){
            $html = "<thead>
                        <tr>
                            <th>User Id</th>";
            if($type != false){
                $html .= "<th>Type</th>";
            }
        }
        foreach($report->sections as $section){
            foreach($section->items as $item){
                if($item->blobItem != "" && $item->blobItem !== 0){
                    if (isset(self::$map[$item->blobItem])) {
                        $label = str_replace("_", " ", $item->blobItem);
                        $html .= "<th>{$label}</th>";
                    }
                }
            }
        }
        if(!$simple){                      
            $html .= "  </tr>
                      </thead>";
        }
        return $html;
    }
    
    static function getRow($person, $report, $type=false, $simple=false){
        global $wgServer, $wgScriptPath;
        $html = "";
        foreach($report->sections as $section){
            foreach($section->items as $item){
                if ($item->blobItem != "" && $item->blobItem !== 0) {
                    if (isset(self::$map[$item->blobItem])) {
                        $value = $item->getBlobValue();
                        if (!isset($value)) {
                            $html .= "<td></td>";
                            continue;
                        } elseif (is_array($value)) {
                            $html .= "<td>" . implode(", ", $value) . "</td>";
                        } else {
                            $html .= "<td>{$value}</td>";
                        }
                    }
                }
            }
        }
        if(!$simple){
            $html .= "</tr>";
        }
        return $html;
    }

    function execute($par){
        global $wgServer, $wgScriptPath, $wgOut;
        $wgOut->addScript(" <style>
                                #personHeader{font-size: 160%;
				    margin-bottom: 11px;
				padding-top:30px;
                                    display: block;
                                    margin-left: 10px;
                                    margin-right: 10px;
                                }
                            </style>");
        $me = Person::newFromWgUser();
        $wgOut->setPageTitle("Assessor");
        $wgOut->addHTML("<span id='pageDescription'>Select a user from the list below to view In-Person Assessment Summary</span><table>
        <tr><td>
            <select id='names' data-placeholder='Chose a Person...' name='name' size='10' style='width:100%'>");
        $rels = $me->getRelations("Assesses");
        foreach($rels as $rel){
            $option = $rel->getUser2();
            $wgOut->addHTML("<option value=\"{$option->getId()}\">".str_replace(".", " ", $option->getNameForForms())."</option>\n");
        }
        $wgOut->addHTML("</select>
        </td></tr>
        <tr><td>
        <input type='button' id='button' name='next' value='View In-Person Summary' disabled='disabled' /></td></tr></table>
        <script type='text/javascript'>
        $('#names').chosen();
        $(document).ready(function(){
        $('#names').change(function(){
            var page = $('#names').val();
            if(page != ''){
                $('#button').prop('disabled', false);
            }
        });
        $('#button').click(function(){
        var page = $('#names').val();
        if(typeof page != 'undefined'){
            document.location = '".$wgServer.$wgScriptPath."/index.php/Special:InPersonFollowup?&personid=' + page;
        }
        });
        });
        </script>");

        if(isset($_GET['personid'])){
            $personid = $_GET['personid'];
            $person = Person::newFromId($personid);
            $wgOut->setPageTitle("{$person->getNameForForms()} In-Person Assessment Summary");
            $wgOut->setPageTitle(static::$pageTitle);
            $report = new DummyReport(static::$reportName, $me, null, YEAR);
            $wgOut->setPageTitle("Assessor");
            $wgOut->addHTML("<div id='personHeader'><span style='font-size: 200%'>{$person->getNameForForms()}</span> <br /><a class='program-button' href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=InPersonAssessment&person={$person->getId()}'>In-Person Assessment Form</a></div>");

            $wgOut->addHTML("<table id='summary' class='wikitable' frame='box' rules='all'>
                <thead>
                    <tr>
                        <th>Person</th>
                        <th>In Person Assessment</th>
                        ".InPersonFollowup::getHeader($report, false, true)."
                    </tr>
                </thead>
                <tbody>");
            $report->person = $person;
            $wgOut->addHTML("<tr>
                <td>{$person->getNameForForms()}</td>
                <td><a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=InPersonAssessment&person={$person->getId()}'>Form</a></td>
                ".InPersonFollowup::getRow($person, $report, false, true)."
            </tr>");
            $wgOut->addHTML("</tbody>
                            </table>
            <script type='text/javascript'>
                $('#summary').DataTable({
                    'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                    'scrollX': true,
                    'iDisplayLength': -1,
                    'dom': 'Blfrtip',
                    'buttons': [
                        'excel'
                    ],
                    scrollX: true,
                    scrollY: $('#bodyContent').height() - 400
                });
            </script>");
        }

    }

    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
	    $person = Person::newFromWgUser();
	    if($me->isRole("Assessor")){
	        $selected = @($wgTitle->getText() == "In-Person Followup") ? "selected" : false;
            $tabs['Assessor']['subtabs'][] = TabUtils::createSubTab("In-Person Followup", "{$wgServer}{$wgScriptPath}/index.php/Special:InPersonFollowup", $selected);
        }
        return true;
    }

    function getBlobData($blobSection, $blobItem, $person, $year, $rpType=null){
        $rpType = ($rpType == null) ? static::$rpType : $rpType;
        $blb = new ReportBlob(BLOB_TEXT, $year, $person->getId(), 0);
        $addr = ReportBlob::create_address($rpType, $blobSection, $blobItem, 0);
        $result = $blb->load($addr);
        return $blb->getData();
    }

}

?>
