<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['AnnualReportTable'] = 'AnnualReportTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AnnualReportTable'] = $dir . 'AnnualReportTable.i18n.php';
$wgSpecialPageGroups['AnnualReportTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'AnnualReportTable::createSubTabs';

function runAnnualReportTable($par) {
    AnnualReportTable::execute($par);
}

class AnnualReportTable extends SpecialPage{

    function AnnualReportTable() {
        SpecialPage::__construct("AnnualReportTable", null, false, 'runAnnualReportTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(HR));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        AnnualReportTable::generateHTML($wgOut);
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        $me = Person::newFromWgUser();
        $wgOut->addHTML("<div id='arTable'>
            <ul>");
        for($y=YEAR; $y >= 2018; $y--){
            $wgOut->addHTML("<li><a href='#tabs-$y'>$y</a></li>");
        }
        $wgOut->addHTML("</ul>");
        $people = Person::getAllPeople();
        for($y=YEAR; $y >= 2018; $y--){
            $wgOut->addHTML("<div id='tabs-$y'>
                <table id='table-$y' class='wikitable'>
                    <thead>
                        <th>Case#</th>
                        <th>Name</th>
                        <th>Report</th>
                        <th>Recommendation</th>
                    </thead>
                    <tbody>");
            $ar = new DummyReport("FEC", $me, null, $y);
            $rec = new DummyReport("ChairTable", $me, null, $y);
            foreach($people as $person){
                $case = $person->getCaseNumber($y);
                if($case != ""){
                    $ar->person = $person;
                    $rec->person = $person;
                    $pdf = $ar->getPDF();
                    $recPdf = $rec->getPDF(false, "Recommendations");
                    $pdfButton = (count($pdf) > 0) ? "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$pdf[0]['token']}' target='_blank'>Download</a>" : "";
                    $recButton = (count($recPdf) > 0) ? "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$recPdf[0]['token']}' target='_blank'>Download</a>" : "";
                    $wgOut->addHTML("<tr>
                        <td>{$case}</td>
                        <td><a href='{$person->getUrl()}'>{$person->getReversedName()}</a></td>
                        <td align='middle'>{$pdfButton}</td>
                        <td align='middle'>{$recButton}</td>
                    </tr>");
                }
            }
            $wgOut->addHTML("
                    </tbody>
                </table></div>
            <script type='text/javascript'>
                $('#table-$y').dataTable({
                    aLengthMenu: [
                        [-1],
                        ['All']
                    ],
                    iDisplayLength: -1
                });
            </script>");
        }
        $wgOut->addHTML("</div>
            <script>
                $('#arTable').tabs();
            </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "AnnualReportTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Annual Reports", "$wgServer$wgScriptPath/index.php/Special:AnnualReportTable", $selected);
        }
        return true;
    }

}

?>