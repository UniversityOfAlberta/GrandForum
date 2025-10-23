<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['PoolEstimator'] = 'PoolEstimator'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['PoolEstimator'] = $dir . 'PoolEstimator.i18n.php';
$wgSpecialPageGroups['PoolEstimator'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'PoolEstimator::createSubTabs';

class PoolEstimator extends SpecialPage {
    
    function __construct(){
        parent::__construct("PoolEstimator", null, true);
    }
    
    function userCanExecute($user){
        $me = Person::newFromUser($user);
        return ($me->isRole(DEAN) || $me->isRole(DEANEA) || $me->isRole(VDEAN) || $me->isRoleAtLeast(STAFF) || $me->isRole(HR));
    }
    
    function isAtCap($person){
        $report = new DummyReport("", $person, null, YEAR);
        $section = new EditableReportSection();
        $item = new IncrementReportItem();
        $section->setParent($report);
        $item->setParent($section);
        $item->setBlobSubItem($person->getId());
        $options = $item->parseOptions();
        return (in_array("0.00 (PTC)", $options));
    }
    
    function execute($par){
        global $wgOut;
        $this->getOutput()->setPageTitle("Pool Estimator");
        $me = Person::newFromWgUser();
        $wgOut->addHTML("
                <p><b>NOTE:</b> This table is a work in progress.</p>
                <table id='table' class='wikitable'>
                    <thead>
                        <th>Case#</th>
                        <th>Role</th>
                        <th>Special Cases</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Pool Contribution</th>
                        <th>Reason</th>
                    </thead>
                    <tbody>");
        $people = Person::getAllFullPeople();
        $people = Person::filterFaculty($people);
        $facultyPool = 0;
        $atsPool = 0;
        foreach($people as $person){
            $case = $person->getCaseNumber(YEAR);
            $subroles = implode(", ", $person->getSubRoles());
            $role = "Faculty/FSO";
            if(strstr($case, "T") !== false){
                $role = "ATS";
            }
            if($case != ""){
                $contribution = 1.2;
                $reason = "";
                if($this->isAtCap($person)){ $contribution = 0; $reason = "At Cap"; }
                if($person->isRoleDuring(DEAN, START, END)){ $contribution = 0; $reason = "Dean"; }
                if(strstr($subroles, "NoPool") !== false){ $contribution = 0; $reason = "No Pool"; }
                $wgOut->addHTML("<tr>
                    <td>{$case}</td>
                    <td align='center'>{$role}</td>
                    <td>{$subroles}</td>
                    <td><a href='{$person->getUrl()}'>{$person->getReversedName()}</a></td>
                    <td>{$person->getDepartment()}</td>
                    <td>".number_format($contribution, 1)."</td>
                    <td>{$reason}</td>
                </tr>");
                if($role == "Faculty/FSO"){ $facultyPool += $contribution; }
                if($role == "ATS"){ $atsPool += $contribution; }
            }
        }
        $wgOut->addHTML("</tbody>
            <tfoot style='border-top: 5px solid #aaaaaa;'>
                <tr>
                    <th colspan='5' style='text-align:right;'>Faculty/FSO Pool:</th>
                    <td colspan='2' style='text-align: left;'>".number_format($facultyPool, 1)."</td>
                </tr>
                <tr>
                    <th colspan='5' style='text-align:right;'>ATS Pool:</th>
                    <td colspan='2' style='text-align: left;'>".number_format($atsPool, 1)."</td>
                </tr>
            </tfoot>
        </table>
        <script type='text/javascript'>
            $('#table').dataTable({
                aLengthMenu: [
                    [-1],
                    ['All']
                ],
                iDisplayLength: -1,
                'dom': 'Blfrtip',
                'buttons': [
                    'excel'
                ]
            });
        </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $person = Person::newFromWgUser();
        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "PoolEstimator") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Pool Estimator", "$wgServer$wgScriptPath/index.php/Special:PoolEstimator", $selected);
        }
        return true;
    }
}

?>
