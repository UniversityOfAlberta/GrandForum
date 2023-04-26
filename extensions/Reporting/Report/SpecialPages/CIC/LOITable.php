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
    
    function saveDates(){
        $this->saveBlobValue($_POST['blobItem'], 
                             $_POST['user_id'], 
                             $_POST['project_id'], 
                             $_POST['value']);
    }
    
    function createProject(){
        $person = Person::newFromId($_POST['user_id']);
        $projectId = $_POST['project_id'];
        
        // First create the project
        $data = DBFunctions::execSQL("SELECT name 
                                      FROM `grand_project` 
                                      WHERE name LIKE 'P%'
                                      ORDER BY id DESC
                                      LIMIT 1");
        $pId = intval(str_replace("P", "", $data[0]['name'])) + 1;
        $_POST['acronym'] = "P{$pId}";
        $_POST['fullName'] = $this->getBlobValue('TITLE', $person, $projectId);
        $_POST['status'] = "Active";
        $_POST['type'] = "Research";
        $_POST['phase'] = PROJECT_PHASE;
        $_POST['effective_date'] = date('Y-m-d');
        $_POST['description'] = $this->getBlobValue('PROBLEM', $person, $projectId);
        
        APIRequest::doAction('CreateProject', false);
        $project = Project::newFromName("P{$pId}");
        
        if($project == null || $project->getId() == 0){
            echo "There was an error creating the project";
            exit;
        }
        
        $this->saveBlobValue('CREATED', 
                             $person->getId(), 
                             $projectId, 
                             1);
        
        // Add Leader
        $_POST['userId'] = $person->getId();
        $_POST['name'] = PL;
        $_POST['projects'] = array((object) array('id' => $project->getId(), 'name' => $project->getName()));
        $_POST['startDate'] = date('Y-m-d');
        $api = new RoleAPI();
        $api->doPOST();
        
        // Copy LOI data to Project Table
        $data = DBFunctions::execSQL("SELECT *
                                      FROM grand_report_blobs 
                                      WHERE user_id = '{$person->getId()}'
                                      AND proj_id = '{$projectId}'
                                      AND rp_type = 'RP_LOI'");
        foreach($data as $row){
            $blb = new ReportBlob($row['blob_type'], $row['year'], $row['user_id'], $row['proj_id']);
            $addr = ReportBlob::create_address($row['rp_type'], $row['rp_section'], $row['rp_item'], $row['rp_subitem']);
            $result = $blb->load($addr);
            $blbdata = $blb->getData();
        
            $blb = new ReportBlob($row['blob_type'], 0, 0, $project->getId());
            $addr = ReportBlob::create_address('RP_PROJECT_TABLE', 'PROJECTS', $row['rp_item'], $row['rp_subitem']);
            $blb->store($blbdata, $addr);
        }
        
        // Copy LOI PDF
        $data = DBFunctions::execSQL("SELECT *
                                      FROM grand_pdf_report 
                                      WHERE user_id = '{$person->getId()}'
                                      AND proj_id = '{$projectId}'
                                      AND type = 'RP_LOI'");
        if(isset($data[0])){   
            $sto = new ReportStorage(new Person(array()), $project);
            $rpData = "";
            $sto->store_report($rpData, $data[0]['html'], $data[0]['pdf'], 0, 0, 'RP_LOI', 0, 0);
        }
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        if(isset($_GET['saveDates'])){
            $this->saveDates();
            exit;
        }
        if(isset($_GET['createProject'])){
            $this->createProject();
            header('Content-Type: text/html');
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
                $projectId++;
                $report = new DummyReport("LOI", $person, $projectId, 0, true);
                if(!$report->isGenerated()){
                    break;
                }
                if($this->getBlobValue('CREATED', $person, $projectId) == 1){
                    //continue;
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
                    <td><button class='createProject' type='button'>Create Project</button></td>
                </tr>");
            }
        }
        $wgOut->addHTML("</tbody></table>
        <script type='text/javascript'>
             var createTable = function(){
                 return $('#loi_table').DataTable({
                    'aLengthMenu': [[100,-1], [100,'All']], 
                    'iDisplayLength': -1, 
                    'autoWidth': false
                 });
             }
             
             var order = '';
             var search = '';
             var table = createTable();
             
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
                    
                $.post('{$wgServer}{$wgScriptPath}/index.php/Special:LOITable?saveDates', data, function(response){
                    
                });
            });
            
            $('.createProject').click(function(){
                var tr = $(this).closest('.loiRow');
                var data = {
                    user_id: $(tr).attr('data-person'),
                    project_id: $(tr).attr('data-project')
                };
                
                $.post('{$wgServer}{$wgScriptPath}/index.php/Special:LOITable?createProject', data, function(response){
                    clearError();
                    clearSuccess();
                    if(response != ''){
                        addError(response);
                    }
                    else{
                        addSuccess('The project was created');
                        order = table.order();
                        search = table.search();
                        table.destroy();
                        $(tr).remove();
                        table = createTable();
                        table.order(order);
	                    table.search(search);
                    }
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
