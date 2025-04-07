<?php

/**
 * @package Report
 * @abstract
 */

$wgHooks['CheckImpersonationPermissions'][] = 'AbstractReport::checkImpersonationPermissions';
$wgHooks['ImpersonationMessage'][] = 'AbstractReport::impersonationMessage';
UnknownAction::createAction('AbstractReport::downloadBlob');
UnknownAction::createAction('AbstractReport::downloadReportZip');
UnknownAction::createAction('AbstractReport::tinyMCEUpload');

require_once("ReportConstants.php");
require_once("SpecialPages/Report.php");
require_once("SpecialPages/{$config->getValue('networkName')}/Report.php");
require_once("SpecialPages/AnnualReportTable.php");
require_once("SpecialPages/ServiceRoles.php");
require_once("SpecialPages/GraduateStudents.php");
require_once("SpecialPages/FECReflections.php");
require_once("SpecialPages/SPOTGenerator.php");
require_once("SpecialPages/{$config->getValue('networkName')}/DummyReport.php");

autoload_register('Reporting/Report');
autoload_register('Reporting/Report/ReportSections');
autoload_register('Reporting/Report/ReportItems');
autoload_register('Reporting/Report/ReportItems/StaticReportItems');
autoload_register('Reporting/Report/ReportItems/ReportItemSets');

abstract class AbstractReport extends SpecialPage {
    
    var $name;
    var $year;
    var $startYear;
    var $startMonth;
    var $endMonth;
    var $xmlName;
    var $extends;
    var $reportType;
    var $ajax;
    var $header;
    var $footer;
    var $headerName;
    var $sections;
    var $currentSection;
    var $permissions;
    var $sectionPermissions;
    var $scripts = array();
    var $person;
    var $project;
    var $readOnly = false;
    var $topProjectOnly;
    var $pageCount = true;
    var $generatePDF;
    var $pdfType;
    var $pdfFiles;
    var $showInstructions = true;
    var $encrypt = false;
    var $variables = array();
    
    /**
     * @param string $tok
     * @return DummyReport Returns the DummyReport associated with the given $tok
     */
    static function newFromToken($tok){
        global $wgUser;
        $person = Person::newFromId($wgUser->getId());
        $sto = new ReportStorage($person);
        $sto->select_report($tok, false);
        
        $pers = Person::newFromId($sto->metadata('user_id'));
        $pers->id = $sto->metadata('user_id');
        $type = $sto->metadata('type');
        $year = $sto->metadata('year');
        
        $type = ReportXMLParser::findPDFReport($type, true);
        
        $proj = null;
        return new DummyReport($type, $pers, $proj, $year, true);
    }
    
    // Creates a new AbstractReport from the given $xmlFileName
    // $personId forces the report to use a specific user id as the owner of this Report
    // $projectName is the name of the Project this Report belongs to
    // $topProjectOnly means that the Report should override all ReportItemSets which use Projects as their data with the Project belonging to $projectName
    function __construct($xmlFileName, $personId=-1, $projectName=false, $topProjectOnly=false, $year=REPORTING_YEAR, $quick=false){
        global $wgUser, $wgMessage, $config;
        $this->name = "";
        $this->extends = "";
        $this->year = $year; // Default, can be overriden
        $this->startYear = $year - 1; // Default, can be overriden
        $this->startMonth = substr(CYCLE_START_MONTH, 1); // Default, can be overriden
        $this->endMonth = substr(CYCLE_END_MONTH, 1); // Default, can be overriden
        $this->reportType = '';
        $this->disabled = false;
        $this->ajax = false;
        $this->generatePDF = false;
        $this->pdfType = '';
        $this->pdfFiles = array();
        $this->header = null;
        $this->sections = array();
        $this->permissions = array();
        $this->sectionPermissions = array();
        $this->topProjectOnly = $topProjectOnly;
        if($personId == -1){
            $this->person = Person::newFromId($wgUser->getId());
        }
        else{
            $this->person = Person::newFromId($personId);
        }
        if(isset($_GET['generatePDF'])){
            $this->generatePDF = true;
        }
        if(file_exists($xmlFileName)){
            $exploded = explode(".", $xmlFileName);
            $exploded = explode("/ReportXML/{$config->getValue('networkName')}/", $exploded[count($exploded)-2]);
            $this->xmlName = $exploded[count($exploded)-1];
            if(isset(ReportXMLParser::$parserCache[$this->xmlName])){
                $xml = "";
            }
            else{
                $xml = file_get_contents($xmlFileName);
            }
            $parser = new ReportXMLParser($xml, $this);
            if(isset($_COOKIE['showSuccess'])){
                unset($_COOKIE['showSuccess']);
                setcookie('showSuccess', 'true', time()-(60*60), '/');
                $wgMessage->addSuccess("Report Loaded Successfully.");
            }
            $parser->parse($quick);
            if(!$quick){
                $currentSection = @$_GET['section'];
                foreach($this->sections as $section){
                    if($section->name == $currentSection && $currentSection != ""){
                        $this->currentSection = $section;
                        break;
                    }
                }
                if($this->currentSection == null){
                    $i = 0;
                    if(isset($this->sections[$i])){
                        $permissions = $this->getSectionPermissions($this->sections[$i]);
                        while(isset($this->sections[$i]) && 
                              (
                                (($this->sections[$i] instanceof HeaderReportSection) || !isset($permissions['r'])) ||
                                ($this->topProjectOnly && $this->sections[$i]->private))
                              ){
                            $i++;
                            if(isset($this->sections[$i])){
                                $permissions = $this->getSectionPermissions($this->sections[$i]);
                            }
                        }
                        $this->currentSection = @$this->sections[$i];
                    }
                }
                if($this->currentSection == null){
                    // If this gets run, it will probably result in a permissions error, but atleast it error out later
                    $this->currentSection = @$this->sections[0];
                }
                @$this->currentSection->selected = true;
            }
        }
        if(!$quick){
            SpecialPage::__construct("Report", '', false);
        }
    }
    
    function execute($par){
        global $wgOut, $wgServer, $wgScriptPath, $wgUser, $wgImpersonating, $wgRealUser;
        if($this->name != ""){
            if((isset($_POST['submit']) && $_POST['submit'] == "Save") || isset($_GET['showInstructions'])){
                $managerImpersonating = false;
                if($wgImpersonating){
                    $realPerson = Person::newFromUser($wgRealUser);
                    $managerImpersonating = $realPerson->isRoleAtLeast(MANAGER);
                }
                if(!$managerImpersonating && (!$wgUser->isRegistered() || ($wgImpersonating && !$this->checkPermissions()) || !DBFunctions::DBWritable() || (isset($_POST['user']) && $_POST['user'] != $wgUser->getName()))){
                    header('HTTP/1.1 403 Authentication Required');
                    close();
                }
            }
            if(!$this->checkPermissions()){
                $wgOut->setPageTitle("Permission error");
                $wgOut->addHTML("<p>You are not allowed to execute the action you have requested.</p>
                                 <p>Return to <a href='$wgServer$wgScriptPath/index.php/Main_Page'>Main Page</a>.</p>");
                return;
            }
            if(isset($_POST['submit']) && ($_POST['submit'] == "Save" || $_POST['submit'] == "Next")){
                $oldData = array();
                parse_str(@$_POST['oldData'], $oldData);
                $_POST['oldData'] = $oldData;
                $json = array();
                if($this->currentSection instanceof EditableReportSection){
                    $json = $this->currentSection->saveBlobs();
                }
                if($this->ajax){
                    header('Content-Type: text/json');
                    echo json_encode($json);
                    close();
                }
            }
            if(isset($_GET['showSection'])){
                header("Expires: ".gmdate("D, d M Y H:i:s")." GMT"); // Always expired 
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");// always modified 
                header("Cache-Control: no-cache, must-revalidate");// HTTP/1.1 
                header("Pragma: nocache");// HTTP/1.0
                session_write_close();
                ob_start("ob_gzhandler");
                $this->currentSection->render();
                echo $wgOut->getHTML();
                close();
            }
            else if(isset($_GET['showInstructions'])){
                session_write_close();
                echo $this->currentSection->getInstructions();
                close();
            }
            else if(isset($_GET['getProgress'])){
                session_write_close();
                $prog = array();
                foreach($this->sections as $section){
                    if($section instanceof EditableReportSection){
                        $prog[str_replace("&", "", str_replace(" ", "", $section->name))] = $section->getPercentComplete();
                    }
                }
                header('Content-Type: text/json');
                echo json_encode($prog);
                close();
            }
            else if(isset($_GET['submitReport'])){
                $me = Person::newFromId($wgUser->getId());
                foreach($this->pdfFiles as $file){
                    $report = new DummyReport($file, $this->person, $this->project, $this->year);
                    $report->submitReport();
                    break; //Temporary solution to not submitting NI Report Comments PDF (2nd PDF and only 1 2nd PDF among all reports)
                }
                close();
            }
            else if(isset($_GET['getPDF'])){
                header('Content-Type: application/json');
                echo json_encode($this->getPDF());
                close();
            }
            if(!$this->generatePDF){
                $wgOut->setPageTitle($this->name);
                $this->render();
            }
            else{
                $this->generatePDF();
            }
        }
        else{
            // File not found
            $wgOut->setPageTitle("Report not Found");
            $wgOut->addHTML("The report specified does not exist");
        }
    }
    
    function addScript($script){
        $this->scripts[] = $script;
    }
    
    function getLatestPDF(){
        if(isset($this->pdfFiles[0]) && $this->pdfFiles[0] != $this->xmlName){
            $file = $this->pdfFiles[0];
            $report = new DummyReport($file, $this->person, $this->project, $this->year, true);
            return $report->getLatestPDF();
        }
        $sto = new ReportStorage($this->person);
        if($this->project != null){
            $check = $sto->list_project_reports($this->project->getId(), 0, 0, $this->pdfType, $this->year);
        }
        else{
            $check = $sto->list_reports($this->person->getId(), 0, 0, $this->pdfType, $this->year);
        }
        $largestDate = ZOT;
        $return = array();
        foreach($check as $c){
            $tst = $c['timestamp'];
            if(strcmp($tst, $largestDate) > 0){
                $largestDate = $tst;
                $return = array($c);
            }
        }
        return $return;
    }
    
    function getPDF($submittedByOwner=false, $section=""){
        if(isset($this->pdfFiles[0]) && $this->pdfFiles[0] != $this->xmlName){
            $file = $this->pdfFiles[0];
            $report = new DummyReport($file, $this->person, $this->project, $this->year, true);
            return $report->getPDF($submittedByOwner, $section);
        }
        $sto = new ReportStorage($this->person);
        $foundSameUser = false;
        $foundSubmitted = false;
        if($this->project != null){
            $check = $sto->list_project_reports($this->project->getId(), 0, 0, $this->pdfType.$section, $this->year);
            $check2 = array();
            foreach($check as $c){
                if($c['submitted'] == 1){
                    $foundSubmitted = true;
                }
                else{
                    $check2[] = $c;
                }
            }
        }
        else{
            // First check submitted
            $check = $sto->list_reports($this->person->getId(), 0, 0, $this->pdfType.$section, $this->year);
            foreach($check as $c){
                if($c['generation_user_id'] == $c['user_id']){
                   $foundSameUser = true;
                   break;
                }
            }
        }
        foreach($check as $key => $c){
            if($foundSameUser && $c['generation_user_id'] != $c['user_id']){
                unset($check[$key]);
            }
            if($foundSubmitted && $c['submitted'] != 1){
                unset($check[$key]);
            }
        }
        $largestDate = ZOT;
        $return = array();
        foreach($check as $c){
            $tst = $c['timestamp'];
            if($c['submitted'] == 1){
                $c['status'] = "Generated/Submitted";
            }
            else if($foundSameUser){
                $c['status'] = "Generated/Not Submitted";
            }
            else if(!$foundSameUser){
                $c['status'] = "Generated/Not Submitted";
            }
            else{
                $c['status'] = "Generated/Not Submitted";
            }
            $c['name'] = $this->name;
            if(strcmp($tst, $largestDate) > 0){
                $largestDate = $tst;
                $return = array($c);
            }
        }
        return $return;
    }
    
    // Sets the name of this Report
    function setName($name){
        if($this->project != null){
            $this->name = $name.": {$this->project->getName()}";
        }
        else{
            $this->name = $name;
        }
    }
    
    function setHeaderName($name){
        $section = new ReportSection();
        $item = new StaticReportItem();
        $section->parent = $this;
        $item->parent = $section;
        if($this->person != null){
            $item->setPersonId($this->person->getId());
        }
        if($this->project != null){
            $item->setProjectId($this->project->getId());
        }
        $this->headerName = $item->varSubstitute($name);
    }
    
    function setFooter($footer){
        $section = new ReportSection();
        $item = new StaticReportItem();
        $section->parent = $this;
        $item->parent = $section;
        if($this->person != null){
            $item->setPersonId($this->person->getId());
        }
        if($this->project != null){
            $item->setProjectId($this->project->getId());
        }
        $this->footer = $item->varSubstitute($footer);
    }
    
    // Specifies which report this one inherits from
    function setExtends($extends){
        $this->extends = $extends;
    }
    
    // Sets whether or not this Report should be disabled or not
    function setDisabled($disabled){
        $this->disabled = $disabled;
    }
    
    // Sets the type of Report
    function setReportType($type){
        $this->reportType = $this->varSubstitute($type);
    }
    
    // Sets the type of PDF to generate when generatePDF is called
    function setPDFType($type){
        $this->pdfType = $this->varSubstitute($type);
    }
    
    // Sets the PDF Files that this Report will generate
    function setPDFFiles($files){
        $this->pdfFiles = explode(",", $files);
    }
    
    function setHeader($header){
        $header->setParent($this);
        $this->header = $header;
    }
    
    function setEncrypt($encrypt){
        $this->encrypt = $encrypt;
    }
    
    // Adds a new section to this Report
    function addSection($section, $position=null){
        $section->setParent($this);
        if($position == null){
            $this->sections[] = $section;
        }
        else{
            array_splice($this->sections, $position, 0, array($section));
        }
    }
    
    // Deleted the given ReportItem from this AbstractReport
    function deleteSection($section){
        foreach($this->sections as $key => $sec){
            if($section->id == $sec->id){
                unset($this->sections[$key]);
                return;
            }
        }
    }
    
    // Returns the section with the given id, or null if there is no such section
    function getSectionById($sectionId){
        foreach($this->sections as $section){
            if($section->id == $sectionId){
                return $section;
            }
        }
        return null;
    }
    
    // Returns whether or not this AbstractReport has a section of type SubReportSection
    function hasSubReport(){
        foreach($this->sections as $section){
            if(get_class($section) == "SubReportSection"){
                return true;
            }
        }
        return false;
    }
    
    // Adds a new Permission to this Report
    function addPermission($type, $permission, $start=null, $end=null){
        if($start == null){
            $start = SOT;
        }
        if($end == null){
            $end = EOT;
        }
        $this->permissions[$type][] = array('perm' => $permission, 'start' => $start, 'end' => $end);
    }
    
    function addSectionPermission($sectionId, $role, $permissions){
        $permissions = str_split($permissions);
        if(count($permissions) > 0){
            foreach($permissions as $permission){
                $this->sectionPermissions[$role][$sectionId][$permission] = true;
            }
        }
        else{
            // No permissions were explicitly defined, so add 'empty' permission (signals that the user has no permissions)
            $this->sectionPermissions[$role][$sectionId][""] = true;
        }
    }
    
    // Returns the percent of the number of chars used in all the limited textareas
    function getPercentChars(){
        $percents = array();
        foreach($this->sections as $section){
            $percents["{$section->name}"] = $section->getPercentChars();
        }
        return $percents;
    }
    
    // Returns an array of the ReportSections which are to be rendered when generating a PDF
    function getPDFRenderableSections(){
        $sections = array();
        foreach($this->sections as $section){
            if($section->renderPDF){
                $sections[] = $section;
            }
        }
        return $sections;
    }
    
    /**
     * Returns whether or not the Person has started the report yet
     * @return boolean Whether or not the Person has started the report yet
     */
    function hasStarted(){
        $personId = $this->person->getId();
        $projectId = ($this->project != null) ? $this->project->getId() : 0;
        $data = DBFunctions::select(array('grand_report_blobs'),
                                    array('*'),
                                    array('user_id' => EQ($personId),
                                          'proj_id' => EQ($projectId),
                                          'rp_type' => EQ($this->reportType),
                                          'year' => EQ($this->year)));
        return (count($data) > 0);
    }
    
    // Checks the permissions of the Person with the required Permissions of the Report
    function checkPermissions(){
        global $wgUser;
        $me = Person::newFromId($wgUser->getId());
        $rResult = $me->isRoleAtLeast(MANAGER);
        $pResult = false;
        $nProjectTags = 0;
        $me->getFecPersonalInfo();
        if(!$rResult && !$me->inFaculty() && !$me->isRole("FEC ".getFaculty())){
            return false;
        }
        foreach($this->permissions as $type => $perms){
            foreach($perms as $perm){
                switch($type){
                    case "Role":
                        if($perm['perm']['role'] == INACTIVE && !$me->isActive()){
                            $rResultTmp = true;
                        }
                        else{
                            if(strstr($perm['perm']['role'], "+") !== false){
                                $rResultTmp = ($rResult || $me->isRoleAtLeastDuring(constant(str_replace("+", "", $perm['perm']['role'])), $perm['start'], $perm['end']));
                            }
                            else{
                                $rResultTmp = ($rResult || $me->isRoleDuring($perm['perm']['role'], $perm['start'], $perm['end']));
                            }
                        }
                        if($perm['perm']['subType'] != ""){
                            $rResultTmp = ($rResultTmp && ($me->isSubRole($perm['perm']['subType'])));
                        }
                        $rResult = ($rResult || $rResultTmp);
                        break;
                    case "Person":
                        if($me->getId() == $perm['perm']['id']){
                            $rResult = true;
                        }
                        break;
                }
            }
        }
        if($nProjectTags == 0){
            $pResult = true;
        }
        return ($pResult && $rResult);
    }
    
    function getSectionPermissions($section){
        global $wgUser;
        $me = Person::newFromId($wgUser->getId());
        if($me->isRoleAtLeast(MANAGER)){
            return array('r' => true, 'w' => true);
        }
        $found = false;
        $roles = $me->getRights();
        $roleObjs = $me->getRolesDuring(REPORTING_CYCLE_START, REPORTING_CYCLE_END);
        foreach($roleObjs as $role){
            $roles[] = $role->getRole();
        }
        $permissions = array();
        foreach($roles as $role){
            $subRoles = array("");
            $subRoles = array_merge($subRoles, $me->getSubRoles());
            foreach($subRoles as $subRole){
                $index = ($subRole != "") ? "{$role}_{$subRole}" : $role;
                if(isset($this->sectionPermissions[$index][$section->id])){
                    $found = true;
                    foreach($this->sectionPermissions[$index][$section->id] as $key => $perm){
                        $permissions[$key] = $perm;
                        if($key == "-"){
                            return array();
                        }
                    }
                }
            }
        }
        if($this->person->getId() == 0 &&
           $this->project != null){
            if(isset($this->sectionPermissions[$this->project->getName()][$section->id])){
                $found = true;
                foreach($this->sectionPermissions[$this->project->getName()][$section->id] as $key => $perm){
                    $permissions[$key] = $perm;
                    if($key == "-"){
                        return array();
                    }
                }
            }
        }
        if(isset($this->sectionPermissions[$me->getId()])){
            $found = true;
            foreach($this->sectionPermissions[$me->getId()][$section->id] as $key => $perm){
                $permissions[$key] = $perm;
                if($key == "-"){
                    return array();
                }
            }
        }
        foreach($this->sectionPermissions as $if => $sections){
            if(strstr($if, "If_") !== false){
                $found = true;
                if(isset($this->sectionPermissions[$if][$section->id])){
                    foreach($this->sectionPermissions[$if][$section->id] as $key => $perm){
                        $permissions[$key] = $perm;
                        if($key == "-"){
                            return array();
                        }
                    }
                }
            }
        }
        if(!$found){
            // If neither the section permissions were never defined, initialize them here as true
            $permissions['r'] = true;
            $permissions['w'] = true;
        }
        return $permissions;
    }
    
    // Generates the PDF for the report, and saves it to the Database
    function generatePDF($person=null, $submit=false, $batch=false){
        global $wgOut, $wgUser;
        session_write_close();
        $me = $person;
        if($person == null){
            $me = Person::newFromId($wgUser->getId());
        }
        $json = array();
        $preview = isset($_GET['preview']);
        if(count($this->pdfFiles) > 0){
            foreach($this->pdfFiles as $pdfFile){
                set_time_limit(120); // Renew the execution timer
                if(!$wgOut->isDisabled()){
                    $wgOut->clearHTML();
                }
                $report = new DummyReport($pdfFile, $this->person, $this->project, $this->year);
                $report->renderForPDF();
                $data = "";
                $pdf = PDFGenerator::generate("{$report->person->getNameForForms()}_{$report->name}", $wgOut->getHTML(), "", $me, $this->project, false, $report);
                if(!$preview){
                    $_GET['preview'] = true;
                    $_GET['dpi'] = 120;
                    calculateDPI();
                    $html = PDFGenerator::generate("{$report->person->getNameForForms()}_{$report->name}", $wgOut->getHTML(), "", $me, $this->project, true, $this, false, true);
                    $_GET['preview'] = false;
                    calculateDPI();
                }
                if($preview){
                    close();
                }
                $this_person = $this->person;
                if(isset($_GET['userId'])){
                    $this_person = Person::newFromId($_GET['userId']);
                }
                $sto = new ReportStorage($this_person);
                @$sto->store_report($data, $html,$pdf['pdf'], 0, 0, $report->pdfType.$_GET['section'], $this->year, $this->encrypt);
                if($report->project != null){
                    $ind = new ReportIndex($this_person);
                    $rid = $sto->metadata('report_id');
                    $ind->insert_report($rid, $report->project);
                }
                $tok = $sto->metadata('token');
                $tst = $sto->metadata('timestamp');
                $len = $sto->metadata('pdf_len');
                $json[$pdfFile] = array('tok'=>$tok, 'time'=>$tst, 'len'=>$len, 'name'=>"{$report->name}");
            }
        }
        else{
            set_time_limit(120); // Renew the execution timer
            if(!$wgOut->isDisabled()){
                $wgOut->clearHTML();
            }
            $this->renderForPDF();
            $data = "";
            $pdf = PDFGenerator::generate("{$this->person->getNameForForms()}_{$this->name}", $wgOut->getHTML(), "", $me, $this->project, false, $this);
            if($preview){
                close();
            }
            $this_person = $this->person;
            if(isset($_GET['userId'])){
                $this_person = Person::newFromId($_GET['userId']);
            }
            $sto = new ReportStorage($this_person);
            @$sto->store_report($data, $pdf['html'],$pdf['pdf'], 0, 0, $report->pdfType.$_GET['section'], $this->year, $this->encrypt);
            if($this->project != null){
                $ind = new ReportIndex($this_person);
                $rid = $sto->metadata('report_id');
                $ind->insert_report($rid, $this->project);
            }
            $tok = $sto->metadata('token');
            $tst = $sto->metadata('timestamp');
            $len = $sto->metadata('pdf_len');
            $json[] = array('tok'=>$tok, 'time'=>$tst, 'len'=>$len, 'name'=>"{$this->name}");
        }
        
        if($submit){
            $this->submitReport($person);
        }
        if(!$batch){
            header('Content-Type: application/json');
            header('Content-Length: '.strlen(json_encode($json)));
            echo json_encode($json);
            ob_flush();
            flush();
            close();
        }
    }
    
    // Marks the report as submitted
    function submitReport($person=null){
        global $wgUser;
        if($person == null){
            $me = Person::newFromId($wgUser->getId());
        }
        else{
            $me = $person;
        }
        $sto = new ReportStorage($me);
        $check = $this->getLatestPDF();
        if(count($check) > 0){
            $sto->mark_submitted_ns($check[0]['token']);
        }
    }
    
    // Checks whether or not this report has been submitted or not
    function isSubmitted(){
        //$sto = new ReportStorage($sto = new ReportStorage($this->person));
        $check = $this->getPDF();
        if(isset($check[0])){
            return (isset($check[0]['submitted']) && $check[0]['submitted'] == 1);
        }
    }
    
    // Renders the Report to the browser
    function render(){
        global $wgOut, $wgServer, $wgScriptPath, $wgArticle, $wgImpersonating, $wgMessage;
        FootnoteReportItem::$nFootnotes = 0;
        if($this->disabled && !$wgImpersonating){
            $wgOut->addHTML("<div id='outerReport'>This report is currently disabled until futher notice.</div>");
            return;
        }
        $writable = "true";
        if(!DBFunctions::DBWritable()){
            $writable = "false";
        }
        $wgOut->addScript("<link rel='stylesheet' type='text/css' href='$wgServer$wgScriptPath/extensions/Reporting/Report/style/report.css?".filemtime(dirname(__FILE__)."/style/report.css")."' />");
        $wgOut->addScript("<script type='text/javascript'>
            var dbWritable = {$writable};
        </script>");
        $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/Reporting/Report/scripts/report.js?".filemtime(dirname(__FILE__)."/scripts/report.js")."'></script>");
        $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/Reporting/Report/scripts/instructions.js?".filemtime(dirname(__FILE__)."/scripts/instructions.js")."'></script>");
        $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/Reporting/Report/scripts/progress.js?".filemtime(dirname(__FILE__)."/scripts/progress.js")."'></script>");
        $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/Reporting/Report/scripts/sticky.js?".filemtime(dirname(__FILE__)."/scripts/sticky.js")."'></script>");
        if($this->ajax){
            $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/Reporting/Report/scripts/ajax.js?".filemtime(dirname(__FILE__)."/scripts/ajax.js")."'></script>");
        }
        else{
            $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/Reporting/Report/scripts/noAjax.js?".filemtime(dirname(__FILE__)."/scripts/noAjax.js")."'></script>");
            
        }
        $wgOut->addHTML("<div id='outerReport'>
                            <div class='displayTableCell'><div id='aboveTabs'></div>
                                <div id='reportTabs'>\n");
        $this->renderTabs();
        $wgOut->addHTML("<div id='autosaveDiv'><span style='float:left;width:100%;text-align:left'><span style='float:right;' class='autosaveSpan'></span></span></div>
                            <div id='optionsDiv'>");
        $this->renderOptions();
        $wgOut->addHTML("</div></div>
                            </div>");
        foreach($this->scripts as $script){
            $wgOut->addHTML($this->varSubstitute($script));
        }
        $wgOut->addHTML("   <div id='reportMain' class='displayTableCell'><div>");
        if(!$this->topProjectOnly || ($this->topProjectOnly && !$this->currentSection->private)){
            $this->currentSection->render();
        }
        $wgOut->addHTML("   </div></div>\n");
        $instructionsHide = "";
        if(!$this->showInstructions){
            $instructionsHide = "style='display:none;'";
        }
        $instructions = trim($this->currentSection->getInstructions());
        if($instructions == ""){
            $instructionsHide = "style='display:none;'";
        }
        $wgOut->addHTML("   <div $instructionsHide id='instructionsToggle' class='highlights-text'>.<br />.<br />.</div>\n");
        $wgOut->addHTML("   <div $instructionsHide id='reportInstructions' class='displayTableCell'><div><div>
                                <span id='instructionsHeader'>Instructions</span>
                                {$instructions}
                            </div></div></div>\n");
        
        $wgOut->addHTML("</div>\n");
        $wgOut->addHTML("<script type='text/javascript'>
            autosaveDiv = $('.autosaveSpan');
        </script>");
    }
    
    function renderTabs(){
        foreach($this->sections as $section){
            $permissions = $this->getSectionPermissions($section);
            if(!isset($permissions['r'])){
                continue;
            }
            if($this->topProjectOnly && $section->private){
                continue;
            }
            $section->renderTab();
        }
    }
    
    function renderOptions(){
        global $wgOut;
        $wgOut->addHTML("<hr />
                            <h3>Options</h3>
                            <table>
                                <tr id='fullScreenRow'>
                                    <td width='50%' align='right' valign='top' style='white-space:nowrap;'>Full-Window&nbsp;Mode:</td><td width='50%' valign='middle'><input type='checkbox' name='toggleFullscreen'></td>
                                </tr>
                                <tr id='autosaveRow' style='display:none;'>
                                    <td width='50%' align='right' valign='top'>Autosave:</td><td width='50%' valign='middle'><input name='autosave' autosave='on' type='radio' checked>On<br /><input name='autosave' value='off' type='radio'>Off</td>
                                </tr>
                            </table>");
    }
    
    // Renders the Report for use in a PDF
    function renderForPDF(){
        global $wgOut;
        FootnoteReportItem::$nFootnotes = 0;
        $sections = $this->getPDFRenderableSections();
        
        $count = count($sections);
        $i = 0;
        if($this->header != null){
            $this->header->render();
        }
        foreach($sections as $section){
            $permissions = $this->getSectionPermissions($section);
            if(!isset($permissions['r'])){
                continue;
            }
            if(isset($_GET['section']) && $section->name != $_GET['section']){
                continue;
            }
            if(isset($_GET['preview']) || (!isset($_GET['preview']) && !$section->previewOnly)){
                if(!$this->topProjectOnly || ($this->topProjectOnly && !$section->private)){
                    if(!($section instanceof HeaderReportSection)){
                        $number = "";
                        if(count($section->number) > 0){
                            $numbers = array();
                            foreach($section->number as $n){
                                $numbers[] = AbstractReport::rome($n);
                            }
                            $number = implode(', ', $numbers).'. ';
                        }
                        $name = $section->varSubstitute($section->name);
                        $title = $section->getAttr("title", $number.$name, true);
                        if(strtolower($section->getAttr("subBookmark", "false")) == "true"){
                            PDFGenerator::addSubChapter($title);
                        }
                        else{
                            PDFGenerator::addChapter($title);
                        }
                    }
                    $section->renderForPDF();
                    if(!($section instanceof HeaderReportSection)){
                        PDFGenerator::changeSection();
                    }
                    $i++;
                    if($count >= $i && $section->pageBreak){
                        $wgOut->addHTML("<div class='pagebreak'></div>");
                    }
                }
            }
        }
    }
    
    // A function to return the Roman Numeral, given an integer
    function rome($num){
        // Make sure that we only use the integer portion of the value
        $n = intval($num);
        $result = '';
    
        // Declare a lookup array that we will use to traverse the number:
        $lookup = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
        'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
        'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
    
        foreach ($lookup as $roman => $value){
            // Determine the number of matches
            $matches = intval($n / $value);
    
            // Store that many characters
            $result .= str_repeat($roman, $matches);
    
            // Substract that from the number
            $n = $n % $value;
        }
    
        // The Roman numeral should be built, return it
        return $result;
    }
    
    static function checkImpersonationPermissions($person, $realPerson, $ns, $title, $pageAllowed){
        if($person->isRoleDuring(HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END)){
            $hqps = $realPerson->getHQPDuring(REPORTING_CYCLE_START, REPORTING_CYCLE_END);
            foreach($hqps as $hqp){
                if($hqp->getId() == $person->getId()){
                    if(("$ns:$title" == "Special:Report" &&
                       @$_GET['report'] == "HQPReport") || ("$ns:$title" == "Special:ReportArchive" && checkSupervisesImpersonee())){
                        $pageAllowed = true;
                    }
                    break;
                }
            }
        }
        return true;
    }
    
    static function impersonationMessage($person, $realPerson, $ns, $title, $message){
        $isSupervisor = false;
        if($person->isRoleDuring(HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END)){
            if(checkSupervisesImpersonee()){
                $message = str_replace(" in read-only mode", "", $message);
            }
            $isSupervisor = false;
        }
        if($isSupervisor){
            $message .= "<br />As a supervisor, you are able to edit, generate and submit the report of your HQP.  The user who edits, generates and submits the report is recorded.";
        }
        return true;
    }
    
    static function downloadBlob($action){
        $me = Person::newFromWgUser();
        if($action == "downloadBlob" && isset($_GET['id'])){
            if(!$me->isLoggedIn()){
                permissionError();
            }
            ini_set("memory_limit","256M");
            $blob = new ReportBlob();
            $blob->loadFromMD5($_GET['id']);
            $data = $blob->getData();
            if($data != null){
                $fileName = $_GET['id'];
                $json = json_decode($data);
                if(isset($_GET['mime'])){
                    header("Content-Type: {$_GET['mime']}");
                }
                if($json != null){
                    $fileName = $json->name;
                    if(isset($_GET['stream'])){
                        header("Content-disposition: inline; filename=\"".addslashes($fileName)."\"");
                    }
                    else{
                        header("Content-disposition: attachment; filename=\"".addslashes($fileName)."\"");
                    }
                    echo base64_decode($json->file);
                    close();
                }
                else{
                    if(isset($_GET['fileName'])){
                        $fileName = $_GET['fileName'];
                    }
                    if(isset($_GET['stream'])){
                        header("Content-disposition: inline; filename=\"".addslashes($fileName)."\"");
                    }
                    else{
                        header("Content-disposition: attachment; filename=\"".addslashes($fileName)."\"");
                    }
                    echo $data;
                    close();
                }
            }
            close();
        }
        return true;
    }
    
    static function downloadReportZip($action){
        $me = Person::newFromWgUser();
        $files = array();
        if($action == "downloadReportZip" && isset($_POST['pdfs'])){
            if(!$me->isLoggedIn()){
                permissionError();
            }
            ini_set("memory_limit","1024M");
            $fileName = '/tmp/'.md5($me->getId().'_'.rand(0,9999)).'.zip';
            $md5s = explode(",", $_POST['pdfs']);
            $zip = new ZipArchive;
            $res = $zip->open($fileName, ZipArchive::CREATE);
            foreach($md5s as $md5){
                if($md5 != ""){
                    $pdf = PDF::newFromToken($md5);
                    if($pdf->getId() == ""){
                        // Try Blobs
                        $blob = new ReportBlob();
                        $blob->loadFromMD5(urldecode($md5));
                        $address = $blob->getAddress();
                        $data = $blob->getData();
                        $json = json_decode($data);
                        $person = Person::newFromId($address['rp_subitem']);
                        @$files[$person->getId()]++;
                        $caseNumber = strip_tags($report->person->getCaseNumber($report->year));
                        $caseNumber = ($caseNumber != "") ? "{$caseNumber}-" : "";
                        $firstName = $report->person->getFirstName();
                        $lastName = $report->person->getLastName();
                        $name = str_replace(" ", "-", $caseNumber."{$lastName}".substr($lastName, 0, 1)."-File{$files[$person->getId()]}");
                        $zip->addFromString(utf8_decode("{$name}.pdf"), base64_decode($json->file));
                    }
                    else{
                        // In Report PDF
                        $type = $pdf->getType();
                        $report = AbstractReport::newFromToken($pdf->getId());
                        
                        if(strstr($type, "RP_LETTER") !== false){
                            $blob = new ReportBlob(BLOB_TEXT, $report->year, 1, 0);
                            $blob_address = ReportBlob::create_address($type, "TABLE", "TEMPLATE", $report->person->getId());
                            $blob->load($blob_address);
                            $blob_data = $blob->getData();
                            $type = "Template{$blob_data}";
                        }
                        
                        $caseNumber = strip_tags($report->person->getCaseNumber($report->year));
                        $caseNumber = ($caseNumber != "") ? "{$caseNumber}-" : "";
                        $firstName = $report->person->getFirstName();
                        $lastName = $report->person->getLastName();
                        $name = str_replace(" ", "-", $caseNumber."{$lastName}".substr($firstName, 0, 1)."-".trim(str_replace(":", "", $type)));
                        $zip->addFromString(utf8_decode("{$name}.pdf"), $pdf->getPDF());
                    }
                }
            }
            $zip->close();
            $contents = file_get_contents($fileName);
            unlink($fileName);

            $zipName = isset($_GET['zipName']) ? $_GET['zipName'] : "Reports.zip";
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="'.$zipName.'"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            ini_set('zlib.output_compression','0');
            
            echo $contents;
            close();
        }
        return true;
    }
    
    static function blobConstant($constant){
        if(defined("{$constant}")){
            return constant("{$constant}");
        }
        return "{$constant}";
    }
    
    static function tinyMCEUpload($action){
        global $DPI, $DPI_CONSTANT;
        $me = Person::newFromWgUser();
        if($action == "tinyMCEUpload"){
            if(!$me->isLoggedIn() || 
                $_FILES['image']['size'] >= 512*1024){
                echo "alert('There was a problem with the uploaded file.  Make sure that it is a valid image and under 512KB.');";
                close();
            }
            $src = $_FILES['image']['tmp_name'];
            $hash = md5($src);
            system("convert +antialias -background transparent $src /tmp/$hash.png");
            list($width, $height) = getimagesize("/tmp/$hash.png");
            $imgConst = $DPI_CONSTANT*72/96;
            $width = $width/$imgConst;
            $height = $height/$imgConst;
            $png = file_get_contents("/tmp/$hash.png");
            unlink("/tmp/$hash.png");
            $str = "top.$('input[aria-label=Width]').val($width);
                    top.$('input[aria-label=Height]').val($height);
                    top.$('.mce-btn.mce-open').parent().find('.mce-textbox').val('data:image/png;base64,".base64_encode($png)."').closest('.mce-window').find('.mce-primary').click();";
            echo $str;
            close();
        }
        return true;
    }
    
    function varSubstitute($value){
        $item = new StaticReportItem();
        $section = new ReportSection();
        $item->setParent($section);
        $section->setParent($this);
        return $item->varSubstitute($value);
    }
    
    /**
     * Returns the value of the variable with the given key
     * @param string $key The key of the variable
     * @return string The value of the variable if found
     */
    function getVariable($key){
        if(isset($this->variables[$key])){
            return $this->variables[$key];
        }
        return "";
    }
    
    /**
     * Sets the value of the variable with the given key to the given value
     * @param string $key The key of the variable
     * @param string $value The value of the variable
     * @param integer $depth The depth of the function call (should not need to ever pass this)
     * @return boolean Whether or not the variable was found
     */
    function setVariable($key, $value, $depth=0){
        if(isset($this->variables[$key])){
            $this->variables[$key] = $value;
            return true;
        }
        return false;
    }
}

?>
