<?php

/**
 * @package Report
 * @abstract
 */

require_once("ReportConstants.php");
require_once("SpecialPages/Report.php");
require_once("SpecialPages/DummyReport.php");

autoload_register('Report');
autoload_register('Report/ReportSections');
autoload_register('Report/ReportItems');
autoload_register('Report/ReportItems/StaticReportItems');
autoload_register('Report/ReportItems/ReportItemSets');

abstract class AbstractReport extends SpecialPage {
    
    var $name;
    var $year;
    var $xmlName;
    var $extends;
    var $reportType;
    var $ajax;
    var $header;
    var $sections;
    var $currentSection;
    var $permissions;
    var $sectionPermissions;
    var $person;
    var $project;
    var $readOnly = false;
    var $topProjectOnly;
    var $generatePDF;
    var $pdfType;
    var $pdfFiles;
    var $pdfAllProjects;
    var $showInstructions = true;
    
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
        switch($type){
            case RPTP_NORMAL:
                $type = "NIReport";
                break;
            case RPTP_NI_COMMENTS:
                $type = "NIReportComments";
                break;
            case RPTP_INPUT:
                break;
            case RPTP_LEADER:
                $type = "ProjectReport";
                break;
            case RPTP_REVIEWER:
                $type = "ReviewReport";
                break;
            case RPTP_SUPPORTING:
                break;
            case RPTP_EVALUATOR:
                $type = "EvaluatorResearcherReport";
                break;
            case RPTP_EVALUATOR_PROJ:
                break;
            case RPTP_EVALUATOR_NI:
                $type = "EvalNIPDFReport";
                break;
            case RPTP_LEADER_COMMENTS:
                $type = "ProjectReportComments";
                break;
            case RPTP_LEADER_MILESTONES:
                $type = "ProjectReportMilestones";
                break;
            case RPTP_EXIT_HQP:
            case RPTP_HQP:
                $type = "HQPReport";
                break;
            case RPTP_HQP_COMMENTS:
                $type = "HQPReportComments";
                break;
            case RPTP_NI_PROJECT_COMMENTS:
                $type = "ProjectNIComments";
                break;
            case RPTP_MTG:
                $type = "MindTheGap";
                break;
        }
        
        $proj = null;
        $rp_index = new ReportIndex($pers);
        $projects = $rp_index->list_projects();
        foreach($projects as $project){
            $reports = $rp_index->list_reports($project, 0, 0);
            foreach($reports as $report){
                if($report['token'] == $tok){
                    $proj = Project::newFromId($project);
                    break;
                }
            }
            if($proj != null){
                break;
            }
        }
        return new DummyReport($type, $pers, $proj);
    }
    
    // Creates a new AbstractReport from the given $xmlFileName
    // $personId forces the report to use a specific user id as the owner of this Report
    // $projectName is the name of the Project this Report belongs to
    // $topProjectOnly means that the Report should override all ReportItemSets which use Projects as their data with the Project belonging to $projectName
    function AbstractReport($xmlFileName, $personId=-1, $projectName=false, $topProjectOnly=false, $year=REPORTING_YEAR){
        global $wgUser, $wgMessage;
        $this->name = "";
        $this->extends = "";
        $this->year = $year;
        $this->reportType = RP_RESEARCHER;
        $this->disabled = false;
        $this->ajax = false;
        $this->generatePDF = false;
        $this->pdfType = RPTP_NORMAL;
        $this->pdfFiles = array();
        $this->header = null;
        $this->sections = array();
        $this->permissions = array();
        $this->sectionPermissions = array();
        $this->topProjectOnly = $topProjectOnly;
        $this->pdfAllProjects = false;
        if($personId == -1){
            $this->person = Person::newFromId($wgUser->getId());
        }
        else{
            $this->person = Person::newFromId($personId);
        }
        if($projectName === false && isset($_GET['project'])){
            $projectName = $_GET['project'];
        }
        if($projectName != null){
            if(preg_match('/LOI/', $xmlFileName)){
                $this->project = LOI::newFromName($projectName);
            }else{
                $this->project = Project::newFromName($projectName);
            }
        }
        if(isset($_GET['generatePDF'])){
            $this->generatePDF = true;
        }
        if(file_exists($xmlFileName)){
            $exploded = explode(".", $xmlFileName);
            $exploded = explode("/", $exploded[count($exploded)-2]);
            $this->xmlName = $exploded[count($exploded)-1];
            $xml = file_get_contents($xmlFileName);
            $parser = new ReportXMLParser($xml, $this);
            if(isset($_COOKIE['showSuccess'])){
                unset($_COOKIE['showSuccess']);
                setcookie('showSuccess', 'true', time()-(60*60), '/');
                $wgMessage->addSuccess("Report Loaded Successfully.");
            }
            if(isset($_GET['saveBackup']) || isset($_GET['saveBackup'])){
                ini_set("memory_limit","1024M");
            }
            if(isset($_POST['loadBackup']) && !$this->readOnly){
                $status = $parser->loadBackup();
                if($status){
                    $parser->parse();
                    setcookie('showSuccess', 'true', time()+(60), '/');
                    redirect("{$wgServer}{$_SERVER["REQUEST_URI"]}");
                }
            }
            $parser->parse();
            if(isset($_GET['saveBackup'])){
                $parser->saveBackup();
            }
            
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
            $this->currentSection->selected = true;
            wfLoadExtensionMessages("Report");
            SpecialPage::SpecialPage("Report", HQP.'+', false);
        }
        else{
            wfLoadExtensionMessages("Report");
            SpecialPage::SpecialPage("Report", HQP.'+', false);
        }
    }
    
    function execute(){
        global $wgOut, $wgServer, $wgScriptPath, $wgUser, $wgImpersonating, $wgRealUser;
        if($this->name != ""){
            if((isset($_POST['submit']) && $_POST['submit'] == "Save") || isset($_GET['showInstructions'])){
                $managerImpersonating = false;
                if($wgImpersonating){
                    $realPerson = Person::newFromUser($wgRealUser);
                    $managerImpersonating = $realPerson->isRoleAtLeast(MANAGER);
                }
                if(!$managerImpersonating && (!$wgUser->isLoggedIn() || ($wgImpersonating && !$this->checkPermissions()) || !DBFunctions::DBWritable() || (isset($_POST['user']) && $_POST['user'] != $wgUser->getName()))){
                    header('HTTP/1.1 403 Authentication Required');
                    exit;
                }
            }
            if(!$this->checkPermissions()){
                $wgOut->setPageTitle("Permission error");
                $wgOut->addHTML("<p>You are not allowed to execute the action you have requested.</p>
                                 <p>Return to <a href='$wgServer$wgScriptPath/index.php/Main_Page'>Main Page</a>.</p>");
                return;
            }
            if(isset($_POST['submit']) && $_POST['submit'] == "Save"){
                $oldData = array();
                parse_str($_POST['oldData'], $oldData);
                $_POST['oldData'] = $oldData;
                $json = array();
                if($this->currentSection instanceof EditableReportSection){
                    $json = $this->currentSection->saveBlobs();
                }
                if($this->ajax){
                    header('Content-Type: text/json');
                    echo json_encode($json);
                    exit;
                }
            }
            if(isset($_GET['showSection'])){
                header("Expires: ".gmdate("D, d M Y H:i:s")." GMT"); // Always expired 
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");// always modified 
                header("Cache-Control: no-cache, must-revalidate");// HTTP/1.1 
                header("Pragma: nocache");// HTTP/1.0 
                session_write_close();
                $this->currentSection->render();
                echo $wgOut->getHTML();
                exit;
            }
            else if(isset($_GET['showInstructions'])){
                session_write_close();
                echo $this->currentSection->getInstructions();
                exit;
            }
            else if(isset($_GET['getProgress'])){
                session_write_close();
                $prog = array();
                foreach($this->sections as $section){
                    if($section instanceof EditableReportSection){
                        $prog[str_replace(" ", "", $section->name)] = $section->getPercentComplete();
                    }
                }
                header('Content-Type: text/json');
                echo json_encode($prog);
                exit;
            }
            else if(isset($_GET['submitReport'])){
                $me = Person::newFromId($wgUser->getId());
                foreach($this->pdfFiles as $file){
                    if($this->pdfAllProjects){
                        foreach($this->person->getProjectsDuring() as $project){
                            $report = new DummyReport($file, $this->person, $project, $this->year);
                            $report->submitReport();
                        }
                    }
                    $report = new DummyReport($file, $this->person, $this->project, $this->year);
                    $report->submitReport();
                    break; //Temporary solution to not submitting NI Report Comments PDF (2nd PDF and only 1 2nd PDF among all reports)
                }
                exit;
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
    
    function notifySupervisors($tok){
        global $wgServer, $wgScriptPath;
        $alreadySeen = array();
        $supervisors = $this->person->getSupervisors(true);
        $realSupervisors = array();
        foreach($supervisors as $supervisor){
            if(isset($alreadySeen[$supervisor->getId()])){
                continue;
            }
            $alreadySeen[$supervisor->getId()] = true;
            $hqps = $supervisor->getHQPDuring($this->year.REPORTING_CYCLE_START_MONTH, $this->year.REPORTING_CYCLE_END_MONTH);
            foreach($hqps as $hqp){
                if($hqp->getId() == $this->person->getId()){
                    $realSupervisors[] = $supervisor;
                    break;
                }
            }
        }
        foreach($realSupervisors as $supervisor){
            $alreadySeen[$supervisor->getId()] = true;
            Notification::addNotification($this->person, $supervisor, "HQP Report Complete", "{$this->person->getReversedName()} completed their HQP Report.", "$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf=$tok");
        }
    }
    
    function getPDF($submittedByOwner=false){
    	$sto = new ReportStorage($this->person);
    	if($this->project != null){
    	    if($this->pdfAllProjects){
    	        $check = $sto->list_user_project_reports($this->project->getId(), $this->person->getId(), 0, 0, $this->pdfType);
    	    }
    	    else{
    	        $check = $sto->list_project_reports($this->project->getId(), 0, 0, $this->pdfType, $this->year);
            }
    	}
    	else{
    	    $check = array_merge($sto->list_reports($this->person->getId(), SUBM, 0, 0, $this->pdfType), 
    	                         $sto->list_reports($this->person->getId(), NOTSUBM, 0, 0, $this->pdfType));
    	}
    	$largestDate = "0000-00-00 00:00:00";
    	$return = array();
    	foreach($check as $c){
    	    $tok = $c['token'];
    	    $sto->select_report($tok);
    	    $year = $c['year'];
    	    $tst = $sto->metadata('timestamp');
    	    if($year == $this->year && 
    	       strcmp($tst, $largestDate) > 0){
    	        if(($submittedByOwner &&
    	           $sto->metadata('generation_user_id') == $this->person->getId() &&
    	           $sto->metadata('submission_user_id') == $this->person->getId()) || 
    	           !$submittedByOwner){
        	        $largestDate = $tst;
        	        $return = array($c);
        	    }
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
        $this->reportType = $type;
    }
    
    // Sets the type of PDF to generate when generatePDF is called
    function setPDFType($type){
        $this->pdfType = $type;
    }
    
    // Sets the PDF Files that this Report will generate
    function setPDFFiles($files){
        $this->pdfFiles = explode(",", $files);
    }
    
    // Sets whether or not this Report should generate a different PDF for every Project this user is a member of
    function setPDFAllProjects($allProjects){
        $this->pdfAllProjects = $allProjects;
    }
    
    function setHeader($header){
        $header->setParent($this);
        $this->header = $header;
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
            $start = "0000-00-00";
        }
        if($end == null){
            $end = "2100-12-31";
        }
        $this->permissions[$type][] = array('perm' => $permission, 'start' => $start, 'end' => $end);
    }
    
    function addSectionPermission($sectionId, $role, $permissions){
        $permissions = str_split($permissions);
        foreach($permissions as $permission){
            $this->sectionPermissions[$role][$sectionId][$permission] = true;
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
    
    // Checks the permissions of the Person with the required Permissions of the Report
    function checkPermissions(){
        global $wgUser;
        $me = Person::newFromId($wgUser->getId());
        if(isset($_GET['reportingYear']) && !$me->isRoleAtLeast(MANAGER)){
            // Check that the user has a ticket for the specified report year
            $year = $_GET['reportingYear'];
            $ticket = @$_GET['ticket'];
            if(!$me->hasReportingTicket($this->project, $year, $this->reportType, $ticket)){
                return false;
            }
        }
        $me = Person::newFromId($wgUser->getId());
        $rResult = $me->isRoleAtLeast(MANAGER);
        $pResult = false;
        $nProjectTags = 0;
        foreach($this->permissions as $type => $perms){
            foreach($perms as $perm){
                switch($type){
                    case "Role":
                        if($this->project != null && ($perm['perm'] == PL || $perm['perm'] == COPL || $perm['perm'] == "Leadership") && 
                           !$me->isProjectManager()){
                            $project_objs = $me->leadershipDuring($perm['start'], $perm['end']);
                            if(count($project_objs) > 0){
                                foreach($project_objs as $project){
                                    if($project->getId() == $this->project->getId()){
                                        $rResult = true;
                                    }
                                }
                            }
                        }
                        else if($this->project != null && ($perm['perm'] == PM)){
                            if($me->isProjectManager()){
                                $rResult = true;
                            }
                        }
                        else{
                            if(strstr($perm['perm'], "+") !== false){
                                $rResult = ($rResult || $me->isRoleAtLeastDuring(constant(str_replace("+", "", $perm['perm'])), $perm['start'], $perm['end']));
                            }
                            else{
                                $rResult = ($rResult || $me->isRoleDuring($perm['perm'], $perm['start'], $perm['end']));
                            }
                        }
                        break;
                    case "Project":
                        $nProjectTags++;
                        if($this->project != null){
                            if(isset($perm['perm']['deleted'])){
                                $pResult = ($pResult || (($perm['perm']['deleted'] && 
                                           $this->project->isDeleted() && 
                                           substr($this->project->getEffectiveDate(), 0, 4) >= substr($perm['start'], 0, 4) && 
                                           substr($this->project->getEffectiveDate(), 0, 4) <= substr($perm['end'], 0, 4)) || 
                                          (!$perm['perm']['deleted'] && 
                                           !$this->project->isDeleted())));
                            }
                            else if(isset($perm['perm']['project'])){
                                $pResult = ($pResult || $this->project->getName() == $perm['project']);
                            }
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
        if($me->isRole(MANAGER)){
            return array('r' => true, 'w' => true);
        }
        $roles = $me->getRights();
        $roleObjs = $me->getRolesDuring();
        foreach($roleObjs as $role){
            $roles[] = $role->getRole();
        }
        $permissions = array();
        foreach($roles as $role){
            if(isset($this->sectionPermissions[$role][$section->id])){
                foreach($this->sectionPermissions[$role][$section->id] as $key => $perm){
                    $permissions[$key] = $perm;
                }
            }
        }
        if($this->person->getId() == 0 &&
           $this->project != null){
            if(isset($this->sectionPermissions[$this->project->getName()][$section->id])){
                foreach($this->sectionPermissions[$this->project->getName()][$section->id] as $key => $perm){
                    $permissions[$key] = $perm;
                }
            }
        }
        return $permissions;
    }
    
    // Generates the PDF for the report, and saves it to the Database
    function generatePDF($person=null, $submit=false){
        global $wgOut, $wgUser;
        session_write_close();
        $me = $person;
        if($person == null){
            $me = Person::newFromId($wgUser->getId());
        }
        $json = array();
        $preview = isset($_GET['preview']);
        if($this->pdfAllProjects && !$preview){
            foreach($this->person->getProjectsDuring() as $project){
                foreach($this->pdfFiles as $pdfFile){
                    set_time_limit(120); // Renew the execution timer
                    $wgOut->clearHTML();
                    $report = new DummyReport($pdfFile, $this->person, $project, $this->year);
                    $report->renderForPDF();
                    $data = "";
                    $pdf = PDFGenerator::generate("{$report->person->getNameForForms()}_{$report->name}", $wgOut->getHTML(), "", $me, false);
                    $sto = new ReportStorage($this->person);
                    $sto->store_report($data, $pdf['html'], $pdf['pdf'], 0, 0, $report->pdfType, $this->year);
                    if($project != null){
                        $ind = new ReportIndex($this->person);
                        $rid = $sto->metadata('report_id');
                        $ind->insert_report($rid, $report->project);
                    }
                    if($submit){
                        $report->submitReport($person);
                    }
                }
            }
        }
        foreach($this->pdfFiles as $pdfFile){
            set_time_limit(120); // Renew the execution timer
            $wgOut->clearHTML();
            $report = new DummyReport($pdfFile, $this->person, $this->project, $this->year);
            $report->renderForPDF();
            $data = "";
            $pdf = PDFGenerator::generate("{$report->person->getNameForForms()}_{$report->name}", $wgOut->getHTML(), "", $me, false);
            if($preview){
                exit;
            }
            $sto = new ReportStorage($this->person);
            $sto->store_report($data, $pdf['html'],$pdf['pdf'], 0, 0, $report->pdfType, $this->year);
            if($report->project != null){
                $ind = new ReportIndex($this->person);
                $rid = $sto->metadata('report_id');
                $ind->insert_report($rid, $report->project);
            }
		    $tok = $sto->metadata('token');
            $tst = $sto->metadata('timestamp');
            $len = $sto->metadata('pdf_len');
            $json[$pdfFile] = array('tok'=>$tok, 'time'=>$tst, 'len'=>$len, 'name'=>"{$report->name}");
        }
        if($submit){
            $this->submitReport($person);
        }
        header('Content-Type: application/json');
        header('Content-Length: '.strlen(json_encode($json)));
        echo json_encode($json);
        ob_flush();
        flush();
        exit;
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
        $check = $this->getPDF();
        if(count($check) > 0){
            $sto->mark_submitted_ns($check[0]['token']);
            if(($this->xmlName = "HQPReport" || $this->xmlName == "HQPReportPDF") && $this->project == null){
                $this->notifySupervisors($check[0]['token']);
            }
        }
    }
    
    // Checks whether or not this report has been submitted or not
    function isSubmitted(){
        //$sto = new ReportStorage($sto = new ReportStorage($this->person));
        $check = $this->getPDF();
        if(isset($check[0])){
            return ($check[0]['submitted'] == 1);
        }
    }
    
    // Renders the Report to the browser
    function render(){
        global $wgOut, $wgServer, $wgScriptPath, $wgArticle, $wgImpersonating;
        FootnoteReportItem::$nFootnotes = 0;
        if($this->disabled && !$wgImpersonating){
            $wgOut->addHTML("<div id='outerReport'>This report is currently disabled until futher notice.</div>");
            return;
        }
        $writable = "true";
        if(!DBFunctions::DBWritable()){
            $writable = "false";
        }
        $wgOut->addStyle("../extensions/Report/style/report.css");
        $wgOut->addScript("<script type='text/javascript'>
            var dbWritable = {$writable};
        </script>");
        $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/Report/scripts/report.js'></script>");
        $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/Report/scripts/instructions.js'></script>");
        $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/Report/scripts/progress.js'></script>");
        if($this->ajax){
            $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/Report/scripts/ajax.js'></script>");
        }
        else{
            $wgOut->addScript("<script type='text/javascript' src='$wgServer$wgScriptPath/extensions/Report/scripts/noAjax.js'></script>");
        }
        $wgOut->addHTML("<div id='outerReport'>
                            <div class='displayTableCell'><div id='aboveTabs'></div>
                                <div id='reportTabs'>\n");
        $this->renderTabs();
        $wgOut->addHTML("<div id='autosaveDiv'><span style='float:left;width:100%;text-align:left'><span style='float:right;' class='autosaveSpan'></span></span></div>
                            <div id='optionsDiv'>");
        $this->renderOptions();
        if($this->extends == "" && !$this->hasSubReport()){
            $this->renderBackup();  
        }
        $wgOut->addHTML("</div></div>
                            </div>");
        
        $wgOut->addHTML("   <div id='reportMain' class='displayTableCell'><div>");
        if(!$this->topProjectOnly || ($this->topProjectOnly && !$this->currentSection->private)){
            $this->currentSection->render();
        }
        $wgOut->addHTML("   </div></div>\n");
        $instructionsHide = "";
        if(!$this->showInstructions){
            $instructionsHide = "style='display:none;'";
        }
        $wgOut->addHTML("   <div $instructionsHide id='instructionsToggle'>.<br />.<br />.</div>\n");
        $wgOut->addHTML("   <div $instructionsHide id='reportInstructions' class='displayTableCell'><div><div>
                                <span id='instructionsHeader'>Instructions</span>
                                {$this->currentSection->getInstructions()}
                            </div></div></div>\n");
        
        $wgOut->addHTML("</div>\n");
        $wgOut->addHTML("<script type='text/javascript'>
            autosaveDiv = $('.autosaveSpan');
        </script>");
        //TODO: Get rid of this once we no longer have Phase 1 projects being reported
        if($this->project != null && $this->project->getPhase() > 1){
            $wgOut->addHTML("<script type='text/javascript'>
                $('#NIMilestoneComments').hide();
            </script>");
        } 
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
                                <tr id='autosaveRow'>
                                    <td width='50%' align='right' valign='top'>Autosave:</td><td width='50%' valign='middle'><input name='autosave' autosave='on' type='radio' checked>On<br /><input name='autosave' value='off' type='radio'>Off</td>
                                </tr>
                            </table>");
    }
    
    function renderBackup(){
        global $wgOut, $wgServer, $wgScriptPath, $wgTitle;
        $getParams = "";
        $i = 0;
        foreach($_GET as $key => $get){
            if($key == "title"){
                continue;
            }
            $delim = "&";
            if($i == 0) $delim = "?";
            $getParams .= "{$delim}{$key}={$get}";
            $i++;
        }
        $wgOut->addHTML("<hr />
                            <h3><a id='backupLink' style='cursor:pointer;' onClick='toggleBackup();' title='A backup can be used to Save the current state of your report to your computer, and Load it later in case you wanted to revert to a previous version.  After Loading a file, the report will be saved using the data from the backup.'>Backup</a></h3>
                            <div id='backupTable' style='display:none;'><table style='width:100%;'>
                                <tr>
                                    <td><a style='overflow: hidden;' href='javascript:saveBackup();' class='button' id='saveBackup'>Save</a></td>
                                    <td><form id='backupForm' method='post' action='{$wgTitle->getFullUrl()}{$getParams}' enctype='multipart/form-data'><input type='hidden' name='loadBackup' value='true' /><a style='overflow: hidden; position: relative;' class='button' id='downloadBackup'>Load<input class='hiddenFile' name='backup' type='file' /></a><input id='resetBackup' type='reset' style='position:absolute; left:-1000px;' /></form>
                                    <div style='display:none;' id='dialog-confirm' title='Load Report Confirmation'>
	<p><span class='ui-icon ui-icon-alert' style='float:left; margin:0 7px 20px 0;'></span>Are you sure you want to upload the file: <p nowrap='nowrap' style='font-style:italic;white-space:nowrap;' id='fileName'></p>Uploading this file will replace the current report data with the data from the backup.  The file you should be uploading should be using the file extension <b>'.report'</b>.</p>
</div></td>
                                </tr>
                            </table></div>
                            <script type='text/javascript'>
                                $('#backupLink').qtip();
                            </script>");
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
                        PDFGenerator::addChapter($number.$name);
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
    
    // Converts an integer into a roman numeral
    static function rome($N){ 
        $c='IVXLCDM'; 
        for($a=5,$b=$s='';$N;$b++,$a^=7) 
                for($o=$N%$a,$N=$N/$a^0;$o--;$s=$c[$o>2?$b+$N-($N&=-2)+$o=1:$b].$s); 
        return $s; 
    }
}

?>
