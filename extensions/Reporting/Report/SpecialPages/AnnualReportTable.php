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
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(HR) || 
                $person->isRole(CHAIR) || $person->isRole(EA) ||
                $person->isRole(DEAN) || $person->isRole(DEANEA) || $person->isRole(VDEAN));
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
        $people = Person::getAllFullPeople();
        $people = Person::filterFaculty($people);
        
        $decisionHeader = ($me->isRole(DEAN)) ? "<th>Dean's Decision</th>" : "";
        
        for($y=YEAR; $y >= 2018; $y--){
            $wgOut->addHTML("<div id='tabs-$y'>
                <table id='table-$y' class='wikitable'>
                    <thead>
                        <th>Case#</th>
                        <th>Name</th>
                        <th class='deptCol'>Department</th>
                        <th>Report</th>
                        <th>Locked</th>
                        <th>Recommendation</th>
                        {$decisionHeader}
                    </thead>
                    <tbody>");
            $ar = new DummyReport("FEC", $me, null, $y);
            $rec = new DummyReport("ChairTable", $me, null, $y);
            if($me->isRole(CHAIR) || $me->isRole(EA)){
                $departments = @array_keys($me->departments);
                $report = new DummyReport("", $me, null, $y);
                $section = new EditableReportSection();
                $set = new DepartmentPeopleReportItemSet();
                $section->setParent($report);
                $set->setParent($section);
                $set->setAttr('start', ($y-1)."-07-01");
                $set->setAttr('end', ($y)."-07-01");
                $set->setAttr('excludeMe', 'true');
                $set->setAttr('department', @$departments[0]);
                $data = $set->getData();
                $people = array();
                foreach($data as $row){
                    $people[] = Person::newFromId($row['person_id']);
                }
            }
            foreach($people as $person){
                $case = $person->getCaseNumber($y);
                if($case != ""){
                    $ar->person = $person;
                    $rec->person = $person;
                    $pdf = $ar->getPDF();
                    $recPdf = $rec->getPDF(false, "Recommendations");
                    
                    $pdfButton = (count($pdf) > 0) ? "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$pdf[0]['token']}' target='_blank'>Download</a>" : "";
                    $recButton = (count($recPdf) > 0) ? "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$recPdf[0]['token']}' target='_blank'>Download</a>" : "";
                    $decisionRow = "";
                    if($me->isRole(DEAN)){
                        $decisionPdf = $rec->getPDF(false, "Dean Decision");
                        $decisionButton = (count($decisionPdf) > 0) ? "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$decisionPdf[0]['token']}' target='_blank'>Download</a>" : "";
                        $decisionRow = "<td align='middle'>{$decisionButton}</td>";
                    }
                    
                    $blob = new ReportBlob(BLOB_ARRAY, $y, $person->getId(), 0);
                    $blob_address = ReportBlob::create_address("RP_FEC", "FEC_SUBMIT", "LOCK", 0);
                    $blob->load($blob_address);
                    $locked = $blob->getData();
                    $locked = (isset($locked['lock']) && implode($locked['lock']) == "Lock") ? "Locked" : "";
                    
                    $blob = new ReportBlob(BLOB_TEXT, $y, 0, 0);
                    $blob_address = ReportBlob::create_address("RP_CHAIR", "FEC_REVIEW", "SUBMITTED", $person->getId());
                    $blob->load($blob_address);
                    $submitted = $blob->getData();
                    
                    $wgOut->addHTML("<tr>
                        <td>{$case}</td>
                        <td><a href='{$person->getUrl()}'>{$person->getReversedName()}</a></td>
                        <td class='deptCol'>{$person->getDepartment()}</td>
                        <td align='middle'>{$pdfButton}</td>
                        <td align='middle'>{$locked}</td>
                        <td align='middle'>{$recButton}{$submitted}</td>
                        {$decisionRow}
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
        if($me->isRole(CHAIR) || $me->isRole(EA)){
            $wgOut->addHTML("<style>
                .deptCol {
                    display: none;
                }
            </style>");
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
            $tab = "Manager";
            if($person->isRole(CHAIR) || $person->isRole(EA)){
                $tab = "Chair";
            }
            $selected = @($wgTitle->getText() == "AnnualReportTable") ? "selected" : false;
            $tabs[$tab]['subtabs'][] = TabUtils::createSubTab("Report Archives", "$wgServer$wgScriptPath/index.php/Special:AnnualReportTable", $selected);
        }
        return true;
    }

}

?>