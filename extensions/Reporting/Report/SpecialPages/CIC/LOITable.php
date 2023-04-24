<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['LOITable'] = 'LOITable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['LOITable'] = $dir . 'LOITable.i18n.php';
$wgSpecialPageGroups['LOITable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'LOITable::createSubTabs';

function runLOITable($par) {
    LOITable::execute($par);
}

class LOITable extends SpecialPage{

    function __construct() {
        SpecialPage::__construct("LOITable", null, false, 'runLOITable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF));
    }
    
    function handleEdit(){
        $this->saveBlobValue($_POST['blobItem'], 
                             $_POST['user_id'], 
                             $_POST['project_id'], 
                             $_POST['value']);
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        if(isset($_POST['value'])){
            $this->handleEdit();
            exit;
        }
        $people = Person::getAllCandidates();
        $this->getOutput()->setPageTitle("LOI Table");
        $wgOut->addHTML("<style>
            .dates td {
                padding: 1px !important;
                border: none !important;
            }
        </style>
        <table id='loi_table' class='wikitable'>
            <thead>
                <th style='width:20%;'>Applicant</th>
                <th style='width:60%;'>Application</th>
                <th style='width:1%;'>Dates</th>
                <th style='width:20%;'>Actions</th>
            </thead>
            <tbody>");
        foreach($people as $person){
            $projectId = -1;
            while(true){
                $report = new DummyReport("LOI", $person, ++$projectId, 0, true);
                if(!$report->isGenerated()){
                    break;
                }
                $pdf = $report->getPDF();
                $wgOut->addHTML("<tr class='loiRow' data-person='{$person->getId()}' data-project='{$projectId}'>
                    <td>{$person->getNameForForms()}</td>
                    <td>
                        {$this->getBlobValue('TITLE', $person, $projectId)}<br />
                        <a class='button' target='_blank' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf=".urlencode($pdf[0]['token'])."'>Download</a>
                    </td>
                    <td style='white-space: nowrap;'>
                        <table class='dates' cellspacing='0' cellpadding='0'>
                            <tr>
                                <td>LOI Submitted:</td>
                                <td><input style='width: 5em;' type='datepicker' name='LOI_SUBMITTED' value='{$this->getBlobValue('LOI_SUBMITTED', $person, $projectId)}' /></td>
                            </tr>
                            <tr>
                                <td>Committee 1 Approved:</td>
                                <td><input style='width: 5em;' type='datepicker' name='COMMITTEE1_APPROVED' value='{$this->getBlobValue('COMMITTEE1_APPROVED', $person, $projectId)}' /></td>
                            </tr>
                            <tr>
                                <td>Committee 2 Approved:</td>
                                <td><input style='width: 5em;' type='datepicker' name='COMMITTEE2_APPROVED' value='{$this->getBlobValue('COMMITTEE2_APPROVED', $person, $projectId)}' /></td>
                            </tr>
                            <tr>
                                <td>NSERC Alliance Submitted:</td>
                                <td><input style='width: 5em;' type='datepicker' name='NSERC_SUBMITTED' value='{$this->getBlobValue('NSERC_SUBMITTED', $person, $projectId)}' /></td>
                            </tr>
                            <tr>
                                <td>NSERC Alliance Resubmitted:&nbsp;</td>
                                <td><input style='width: 5em;' type='datepicker' name='NSERC_RESUBMITTED' value='{$this->getBlobValue('NSERC_RESUBMITTED', $person, $projectId)}' /></td>
                            </tr>
                            <tr>
                                <td>NSERC Alliance Rejected:</td>
                                <td><input style='width: 5em;' type='datepicker' name='NSERC_REJECTED' value='{$this->getBlobValue('NSERC_REJECTED', $person, $projectId)}' /></td>
                            </tr>
                            <tr>
                                <td>NSERC Alliance Approved:</td>
                                <td><input style='width: 5em;' type='datepicker' name='NSERC_APPROVED' value='{$this->getBlobValue('NSERC_APPROVED', $person, $projectId)}' /></td>
                            </tr>
                            <tr>
                                <td>Project End:</td>
                                <td><input style='width: 5em;' type='datepicker' name='PROJECT_END' value='{$this->getBlobValue('PROJECT_END', $person, $projectId)}' /></td>
                            </tr>
                        </table>
                    </td>
                    <td></td>
                </tr>");
            }
        }
        $wgOut->addHTML("</tbody></table>
        <script type='text/javascript'>
            $('#loi_table').DataTable({
                'aLengthMenu': [[100,-1], [100,'All']], 
                'iDisplayLength': -1, 
                'autoWidth': false
             });
             
             $.each($('input[type=datepicker]'), function(index, val){
                 $(val).datepicker({
                    'dateFormat': 'yy-mm-dd',
                    'defaultDate': '',
                    'changeMonth': true,
                    'changeYear': true,
                    'showOn': 'both',
                    'buttonImage': '{$wgServer}{$wgScriptPath}/skins/calendar.gif',
                    'buttonImageOnly': true
                });
            });
            
            $('#loi_table input, #loi_table select, #loi_table textarea').on('change', function(){
                var data = {
                    blobItem: $(this).attr('name'),
                    user_id: $(this).closest('.loiRow').attr('data-person'), 
                    project_id: $(this).closest('.loiRow').attr('data-project'),
                    value: $(this).val()
                };
                    
                $.post('{$wgServer}{$wgScriptPath}/index.php/Special:LOITable', data, function(response){
                    
                });
            });
        </script>");
    }
    
    function getBlobValue($blobItem, $person, $projectId=0, $blobType=BLOB_TEXT){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $person->getId();
        
        $blb = new ReportBlob($blobType, $year, $personId, $projectId);
        $addr = ReportBlob::create_address('RP_LOI', 'INTENT', $blobItem, 0);
        $result = $blb->load($addr);
        $data = $blb->getData();
        
        return $data;
    }
    
    function saveBlobValue($blobItem, $personId, $projectId, $value){
        $year = 0; // Don't have a year so that it remains the same each year
        
        $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
        $addr = ReportBlob::create_address('RP_LOI', 'INTENT', $blobItem, 0);
        $blb->store($value, $addr);
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "LOITable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("LOI Table", "{$wgServer}{$wgScriptPath}/index.php/Special:LOITable", $selected);
        }
        
        return true;
    }

}

?>
