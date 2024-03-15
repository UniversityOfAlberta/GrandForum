<?php

class ReportXMLParser {
    
    var $xml;
    var $errors;
    var $parser;
    var $report;
    static $parserCache = array();
    static $files = array();
    static $pdfFiles = array();
    static $fileMap = array();
    static $pdfMap = array();
    static $pdfRpMap = array();
    static $time = 0;
    
    static function listFiles($dir, $path=""){
        global $config;
        $return = array();
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach($files as $file){
            if(is_dir(dirname(__FILE__)."/ReportXML/{$config->getValue('networkName')}/{$file}/")){
                $return = array_merge(self::listFiles(dirname(__FILE__)."/ReportXML/{$config->getValue('networkName')}/{$file}/", "{$path}{$file}/"), $return);
            }
            else{
                $return[] = "{$path}{$file}";
            }
        }
        return $return;
    }
    
    static function listReports(){
        global $config;
        if(count(self::$files) == 0){
            $files = array_values(self::listFiles(dirname(__FILE__)."/ReportXML/{$config->getValue('networkName')}/"));
            foreach($files as $file){
                if(strstr($file, "PDF.xml") === false){
                    self::$files[] = $file;
                }
            }
        }
        return self::$files;
    }
    
    static function listPDFs(){
        global $config;
        if(count(self::$pdfFiles) == 0){
            $files = array_values(self::listFiles(dirname(__FILE__)."/ReportXML/{$config->getValue('networkName')}/"));
            foreach($files as $file){
                if(strstr($file, "PDF.xml") !== false){
                    self::$pdfFiles[] = $file;
                }
            }
        }
        return self::$pdfFiles;
    }
    
    static function findReport($rp){
        global $config;
        if(count(self::$fileMap) == 0){
            $files = self::listReports();
            foreach($files as $file){
                $fileName = dirname(__FILE__)."/ReportXML/{$config->getValue('networkName')}/".$file;
                $xml = file_get_contents($fileName);
                $parser = simplexml_load_string($xml);
                if($parser != null){
                    if($parser->getName() == "Report"){
                        $attributes = $parser->attributes();
                        @self::$fileMap[AbstractReport::blobConstant("{$attributes->reportType}")] = $fileName;
                    }
                }
            }
        }
        if(isset(self::$fileMap[$rp])){
            return self::$fileMap[$rp];
        }
        return "";
    }
    
    static function findPDFReport($rptp, $returnRp=false){
        global $config;
        if(count(self::$pdfMap) == 0){
            $files = self::listPDFs();
            foreach($files as $file){
                $fileName = dirname(__FILE__)."/ReportXML/{$config->getValue('networkName')}/".$file;
                $xml = file_get_contents($fileName);
                $parser = simplexml_load_string($xml);
                if($parser->getName() == "Report"){
                    $attributes = $parser->attributes();
                    self::$pdfMap[AbstractReport::blobConstant("{$attributes->pdfType}")] = $fileName;
                    self::$pdfRpMap[AbstractReport::blobConstant("{$attributes->pdfType}")] = AbstractReport::blobConstant($attributes->reportType);
                }
            }
        }
        if(!$returnRp && isset(self::$pdfMap[$rptp])){
            return self::$pdfMap[$rptp];
        }
        if($returnRp && isset(self::$pdfRpMap[$rptp])){
            return self::$pdfRpMap[$rptp];
        }
        return "";
    }
    
    // Creates a new ReportXMLParser.  $xml should be a string containing the contents of an xml file, 
    // and $report should be the Report object which is being created
    function ReportXMLParser($xml, $report){
        $this->xml = $xml;
        $this->report = $report;
        $this->errors = array();
    }
    
    // Saves an encrypted backup of the current state of the report to the user's filesystem
    function saveBackup($download=true){
        $dom = dom_import_simplexml($this->parser)->ownerDocument;
        $dom->formatOutput = false;
        $trimmedXML = trim($dom->saveXML());
        $md5 = md5($trimmedXML);
        $date = new DateTime();
        $time = $date->format('Y-m-d H-i-s');
        $serialized = serialize(array('md5' => $md5, 
                                      'time' => $time,
                                      'type' => $this->report->xmlName,
                                      'xml' => $trimmedXML));
        $key = $this->getKey();
        $iv = $this->getIV();
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $serialized, MCRYPT_MODE_ECB, $iv);
        //$encrypted = $serialized;
        $encrypted = gzcompress($encrypted, 9);
        DBFunctions::insert('grand_report_backup',
                            array('report'    => $this->report->name,
                                  'time'      => $time,
                                  'person_id' => $this->report->person->getId(),
                                  'backup'    => $encrypted));
        DBFunctions::commit();
        if($download){
            header("Content-type: application/force-download");
            if($this->report->project == null){
                header("Content-disposition: attachment; filename=\"{$this->report->name}_".str_replace(".", "", $this->report->person->getName())."_$time.report\"");
            }
            else{
                header("Content-disposition: attachment; filename=\"{$this->report->name}_$time.report\"");
            }
            echo $encrypted;
            exit;
        }
    }
    
    // Loads an encrypted backup, then saves all the data to the DB
    function loadBackup(){
        global $wgMessage;
        if(isset($_FILES['backup'])){
            $file = $_FILES['backup'];
            $str = @gzuncompress(file_get_contents($file['tmp_name']));
            //$str = file_get_contents($file['tmp_name']);
            $key = $this->getKey();
            $iv = $this->getIV();
            $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $str, MCRYPT_MODE_ECB, $iv);
            //$decrypted = $str;
            $unserialized = @unserialize($decrypted);
            if($unserialized === false || 
               !isset($unserialized['md5']) || 
               !isset($unserialized['xml']) ||
               !isset($unserialized['time']) ||
               !isset($unserialized['type'])){
                $wgMessage->addError("The uploaded file is not in a report format, or is corrupt.");
                return false;
            }
            if($unserialized['type'] != $_GET['report']){
                $wgMessage->addError("The uploaded file is is not of the report type '{$_GET['report']}'.");
                return false;
            }
            $md5 = md5($unserialized['xml']);
            if($unserialized['md5'] == $md5){
                $this->parse(); // Save a backup of the latest version just incase we need to revert to that version
                $this->saveBackup(false);
                $this->report->sections = array();
                $this->xml = $unserialized['xml'];
                return true;
            }
            else{
                $wgMessage->addError("The uploaded file has been tampered with, or is corrupt.");
                return false;
            }
        }
    }
    
    private function getKey(){
        return $key = hash("SHA256", "MUROF DNARG", true);
    }
    
    private function getIV(){
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        return $iv;
    }
    
    // Shows the errors to the javascript console
    function showErrors(){
        global $wgOut;
        if(!$this->report->generatePDF){
            if(count($this->errors) > 0){
                $wgOut->addHTML("<script type='text/javascript'>
                                    if (typeof console != 'undefined' && console != null) {\n");
                foreach($this->errors as $error){
                    $wgOut->addHTML("   console.warn('".addslashes($error)."');\n");
                }
                $wgOut->addHTML("   }
                                </script>\n");
            }
        }
    }
    
    // Parses the XML document starting at the root
    function parse($quick=false){
        $md5 = md5($this->xml);
        if(isset(self::$parserCache[$md5])){
            $this->parser = self::$parserCache[$md5];
        }
        else{
            $this->parser = simplexml_load_string($this->xml);
            self::$parserCache[$md5] = $this->parser;
        }
        $this->parseReport($quick);
        $this->showErrors();
    }
    
    // Parses the <Report> element of the XML
    function parseReport($quick=false){
        global $config;
        if($this->parser->getName() == "Report"){
            $attributes = $this->parser->attributes();
            $children = $this->parser->children();
            if(isset($attributes->extends)){
                $xmlFileName = dirname(__FILE__)."/ReportXML/{$config->getValue('networkName')}/{$attributes->extends}.xml";
                if(file_exists($xmlFileName) && $this->report->xmlName != $attributes->extends){
                    $this->report->setExtends("{$attributes->extends}");
                    $exploded = explode(".", $xmlFileName);
                    $exploded = explode("/", $exploded[count($exploded)-2]);
                    $xml = file_get_contents($xmlFileName);
                    $parser = new ReportXMLParser($xml, $this->report);
                    $parser->parse($quick);
                }
                else{
                    if($this->report->xmlName == $attributes->extends){
                        $this->errors[] = "A Report cannot inherit it's self (Infinite inheritance!)";
                    }
                }
            }
            if(isset($attributes->year)){
                $this->report->year = "{$attributes->year}";
            }
            if(isset($attributes->startDate)){
                $this->report->startDate = $this->report->varSubstitute("{$attributes->startDate}");
            }
            if(isset($attributes->endDate)){
                $this->report->endDate = $this->report->varSubstitute("{$attributes->endDate}");
            }
            if(isset($attributes->name)){
                $this->report->setName("{$attributes->name}");
            }
            if(isset($attributes->headerName)){
                $this->report->setHeaderName("{$attributes->headerName}");
            }
            if(isset($attributes->reportType)){
                $type = AbstractReport::blobConstant($attributes->reportType);
                $this->report->setReportType($type);
            }
            if(isset($attributes->pdfType)){
                $type = AbstractReport::blobConstant($attributes->pdfType);
                $this->report->setPDFType($type);
            }
            if(isset($attributes->pdfFiles)){
                $this->report->setPDFFiles("{$attributes->pdfFiles}");
            }
            if(isset($attributes->pdfAllProjects) && strtolower($attributes->pdfAllProjects) == 'true'){
                $this->report->setPDFAllProjects(true);
            }
            if(isset($attributes->ajax) && strtolower($attributes->ajax) == 'true'){
                $this->report->ajax = true;
            }
            if(isset($attributes->allowIdProjects) && strtolower($attributes->allowIdProjects) == 'true'){
                $this->report->allowIdProjects = true;
            }
            if(isset($attributes->orientation)){
                $this->report->orientation = $attributes->orientation;
            }
            if(isset($attributes->disabled) && strtolower($attributes->disabled) == 'true'){
                $this->report->setDisabled(true);
            }
            else if(isset($attributes->disabled) && strtolower($attributes->disabled) == 'false'){
                $this->report->setDisabled(false);
            }
            if(isset($attributes->personId)){
                $id = "{$attributes->personId}";
                $this->report->person = Person::newFromId($id);
                $this->report->person->id = $id;
            }
            if(isset($attributes->encrypt)){
                $this->report->setEncrypt(strtolower($attributes->encrypt) == "true");
            }
            if(isset($children->Permissions)){
                $this->parsePermissions($children->Permissions);
            }
            if(!$quick){
                if(isset($children->ReportSection)){
                    $this->parseReportSection($children->ReportSection);
                }
            }
        }
    }
    
    // Parses the <Permissions> element of the XML
    function parsePermissions($node){
        $children = $node->children();
        foreach($children as $key => $child){
            if($key == "Role"){
                $attributes = $child->attributes();
                $role = (isset($attributes->role)) ? AbstractReport::blobConstant($attributes->role) : MANAGER;
                $subType = (isset($attributes->subType)) ? $attributes->subType : "";
                $subType = (isset($attributes->subRole)) ? $attributes->subRole : $subType;
                if(isset($attributes->role) && AbstractReport::blobConstant($attributes->role) == null){
                    $role = (string)$attributes->role;
                }
                $start = (isset($attributes->start)) ? AbstractReport::blobConstant($attributes->start) : "0000-00-00";
                $end = (isset($attributes->end)) ? AbstractReport::blobConstant($attributes->end) : "2100-12-31";
                if($start == null){
                    $this->errors[] = "Start time '{$attributes->start}' does not exist";
                }
                if($end == null){
                    $this->errors[] = "Start time '{$attributes->end}' does not exist";
                }
                $this->parseRoleSectionPermissions($child, $role);
                $this->report->addPermission("Role", array("role" => "{$role}", "subType" => "{$subType}"), "{$start}", "{$end}");
            }
            else if($key == "Project"){
                $attributes = $child->attributes();
                $deleted = (isset($attributes->deleted)) ? (strtolower("{$attributes->deleted}") == "true") : false;
                $projName = (isset($attributes->project)) ? "{$attributes->project}" : "";
                $start = (isset($attributes->start)) ? AbstractReport::blobConstant($attributes->start) : "0000-00-00";
                $end = (isset($attributes->end)) ? AbstractReport::blobConstant($attributes->end) : "2100-12-31";
                if($start == null){
                    $this->errors[] = "Start time '{$attributes->start}' does not exist";
                }
                if($end == null){
                    $this->errors[] = "Start time '{$attributes->end}' does not exist";
                }
                $this->parseProjectSectionPermissions($child, $projName);
                $this->report->addPermission("Project", array("deleted" => $deleted, "project" => $projName), "{$start}", "{$end}");
            }
            else if($key == "Person"){
                $attributes = $child->attributes();
                $id = (isset($attributes->id)) ? "{$attributes->id}" : 0;
                $this->parsePersonSectionPermissions($child, $id);
                $this->report->addPermission("Person", array("id" => $id));
            }
            else if($key == "If"){
                $attributes = $child->attributes();
                $if = (isset($attributes->if)) ? "{$attributes->if}" : false;
                
                $fakeReportItem = new StaticReportItem();
                $fakeReportSection = new ReportSection();
                $fakeReportSection->setParent($this->report);
                $fakeReportItem->setParent($fakeReportSection);
                $if = $fakeReportItem->varSubstitute($if);
                
                $this->parseIfSectionPermissions($child, $if);
                $this->report->addPermission("If", array("if" => $if));
            }
        }
    }
    
    // Parses the <SectionPermission> elements of a <If> element
    function parseIfSectionPermissions($node, $if){
        if($if == 1 || $if == true){
            $children = $node->children();
            foreach($children as $key => $child){
                $attributes = $child->attributes();
                $permissions = (isset($attributes->permissions)) ? "{$attributes->permissions}" : "r";
                $sectionId = (isset($attributes->id)) ? "{$attributes->id}" : "";
                $this->report->addSectionPermission($sectionId, "If_".count($this->report->sectionPermissions), $permissions);
            }
        }
    }
    
    // Parses the <SectionPermission> elements of a <Role> element
    function parseRoleSectionPermissions($node, $role){
        $children = $node->children();
        foreach($children as $key => $child){
            $attributes = $child->attributes();
            $permissions = (isset($attributes->permissions)) ? "{$attributes->permissions}" : "r";
            $sectionId = (isset($attributes->id)) ? "{$attributes->id}" : "";
            $this->report->addSectionPermission($sectionId, $role, $permissions);
        }
    }
    
    // Parses the <SectionPermission> elements of a <Project> element
    function parseProjectSectionPermissions($node, $project){
        $children = $node->children();
        foreach($children as $key => $child){
            $attributes = $child->attributes();
            $permissions = (isset($attributes->permissions)) ? "{$attributes->permissions}" : "r";
            $sectionId = (isset($attributes->id)) ? "{$attributes->id}" : "";
            $this->report->addSectionPermission($sectionId, $project, $permissions);
        }
    }
    
    // Parses the <SectionPermission> elements of a <Person> element
    function parsePersonSectionPermissions($node, $person){
        $children = $node->children();
        foreach($children as $key => $child){
            $attributes = $child->attributes();
            $permissions = (isset($attributes->permissions)) ? "{$attributes->permissions}" : "r";
            $sectionId = (isset($attributes->id)) ? "{$attributes->id}" : "";
            $this->report->addSectionPermission($sectionId, $person, $permissions);
        }
    }
    
    // Parses the <ReportSection> element of the XML
    function parseReportSection($node){
        foreach($node as $key => $n){
            $attributes = $n->attributes();
            $children = $n->children();
            $section = $this->report->getSectionById("{$attributes->id}");
            if(isset($attributes->type) || $section != null){
                if(isset($attributes->type)){
                    $type = "{$attributes->type}";
                    if(!class_exists($type) && class_exists($type."ReportSection")){
                        $type = $type."ReportSection";
                    }
                    if(!class_exists($type)){
                        $this->errors[] = "ReportSection '{$type}' does not exists";
                        continue;
                    }
                    $section = new $type();
                    $position = isset($attributes->position) ? "{$attributes->position}" : null;
                    foreach($attributes as $key => $value){
		            	$section->setAttribute("{$key}", "{$value}");
		            }
                    $this->report->addSection($section, $position);
                }
                else{
                    $type = get_class($section);
                }
                if(isset($attributes->delete) && strtolower("{$attributes->delete}") == "true"){
                    $this->report->deleteSection($section);
                }
                if(isset($attributes->id)){
                    $section->setId("{$attributes->id}");
                }
                if($this->report->project != null){
                    $section->setProjectId($this->report->project->getId());
                }
                $section->setPersonId($this->report->person->getId());
                if(isset($attributes->name)){
                    $section->setName("{$attributes->name}");
                }
                if(isset($attributes->title)){
                    $section->setTitle("{$attributes->title}");
                }
                if(isset($attributes->tooltip)){
                    $section->setTooltip(str_replace("'", "&#39;", "{$attributes->tooltip}"));
                }
                if(isset($attributes->disabled)){
                    $section->setDisabled($attributes->tooltip);
                }
                if(isset($attributes->blobSection)){
                    $sec = AbstractReport::blobConstant($attributes->blobSection);
                    $section->setBlobSection($sec);
                }
                if($type == "EditableReportSection" && isset($attributes->autosave)){
                    $section->setAutosave(strtolower($attributes->autosave) == "true");
                }
                if($type == "EditableReportSection" && isset($attributes->reportCharLimits)){
                    $section->setReportCharLimits(strtolower($attributes->reportCharLimits) == "true");
                }
                if($type == "EditableReportSection" && isset($attributes->saveText)){
                    $section->setSaveText($attributes->saveText);
                }
                if(isset($attributes->number)){
                    $section->setNumber($attributes->number);
                }
                if(isset($attributes->renderpdf)){
                    $section->setRenderPDF(strtolower($attributes->renderpdf) == "true");
                }
                if(isset($attributes->previewonly)){
                    $section->setPreviewOnly(strtolower($attributes->previewonly) == "true");
                }
                if(isset($attributes->pagebreak)){
                    $section->setPageBreak(strtolower($attributes->pagebreak) == "true");
                }
                if(isset($attributes->private)){
                    $section->setPrivate(strtolower($attributes->private) == "true");
                }
                
                foreach($children as $c){
                    if($c->getName() == "Instructions"){
                        $section->setInstructions("{$children->Instructions}");
                    }
                    else if($c->getName() == "ReportItem" ||
                            ($c->getName() == "If" ||
                             $c->getName() == "ElseIf" ||
                             $c->getName() == "Else" ||
                             $c->getName() == "Static") && trim("{$c}") !== ""){
                        $this->parseReportItem($section, $c);
                    }
                    else if($c->getName() == "ReportItemSet" || 
                            $c->getName() == "If" ||
                            $c->getName() == "ElseIf" ||
                            $c->getName() == "Else" ||
                            $c->getName() == "For"){
                        $projectId = 0;
                        if(!$this->report->topProjectOnly && $this->report->project != null){
                            $projectId = $this->report->project->getId();
                        }
                        $itemset = $this->parseReportItemSet($section, $c, array('project_id'   => $projectId,
                                                                                 'person_id'    => $this->report->person->getId(),
                                                                                 'milestone_id' => 0
                                                                                 ));
                        if(!isset($c->attributes()->value)){
                            if(isset($_GET['saveBackup'])){
                                $data = $itemset->getBlobValue();
                                $value = encode_binary_data(serialize($data));
                                $c->addAttribute("value", $value);
                                $c->addAttribute("binary", "true");
                            }
                        }
                        else{
                            if(isset($c->attributes()->binary) && strtolower($c->attributes()->binary) == "true"){
                                $itemset->setBlobValue(unserialize(decode_binary_data($c->attributes()->value)));
                            }
                            else{
                                $itemset->setBlobValue(unserialize($c->attributes()->value));
                            }
                        }
                    }
                }
            }
        }
    }

    // Parses the <ReportItemSet> element of the XML
    function parseReportItemSet(&$section, $node, $data=array(), $lazy=true, $itemset=null){
        $attributes = $node->attributes();
        $children = $node->children();
        if($lazy === true || $lazy == 'both'){
            if($node->getName() == "If" ||
               $node->getName() == "ElseIf" ||
               $node->getName() == "Else" ||
               $node->getName() == "For"){
               @$node->addAttribute("type", $node->getName());
            }
            if($itemset != null){
                $itemset->count = count($itemset->getItems())/max(1, count($itemset->getData()));
                $itemset->iteration = 0;
            }
            if(isset($attributes->type)){
                $type = "{$attributes->type}";
                if(class_exists($type)){
                    $itemset = new $type();
                }
                else if(class_exists($type."ReportItemSet")){
                    $type = $type."ReportItemSet";
                    $itemset = new $type();
                }
                else{
                    $this->errors[] = "ReportItemSet '{$attributes->type}' does not exists";
                    return;
                }
                $position = isset($attributes->position) ? "{$attributes->position}" : null;
                $section->addReportItem($itemset, $position);
            }
            else if($itemset != null){
                // DO nothing
                $type = get_class($itemset);
            }
            else{
                $this->errors[] = "ReportItemSet '' does not exists";
                return;
            }
            $itemset->parser = $this;
            $itemset->section = $section;
            $itemset->node = $node;
            $itemset->data = $data;
            if(isset($attributes->id)){
                $itemset->setId("{$attributes->id}");
            }
            else{
                $this->errors[] = "ReportItemSet does not contain an id";
            }
            if(isset($attributes->delete) && strtolower("{$attributes->delete}") == "true"){
                $section->deleteReportItem($itemset);
            }
            if(isset($attributes->delete) && strtolower("{$attributes->delete}") == "false"){
                $section->undeleteReportItem($itemset);
            }
            if(isset($attributes->blobIndex)){
                $itemset->setBlobIndex("{$attributes->blobIndex}");
            }
            if(isset($attributes->private)){
                $itemset->setPrivate(strtolower($attributes->private) == "true");
            }
            if(isset($data['project_id'])){
                $itemset->setProjectId($data['project_id']);
            }
            if(isset($data['milestone_id'])){
                $itemset->setMilestoneId($data['milestone_id']);
            }
            if(isset($data['product_id'])){
                $itemset->setProductId($data['product_id']);
            }
            if(isset($data['extra'])){
                $itemset->setExtra($data['extra']);
            }
            if(isset($data['person_id'])){
                $itemset->setPersonId($data['person_id']);
            }
            foreach($attributes as $key => $value){
                if($key != "type" &&
                   $key != "blobType" &&
                   $key != "blobItem" &&
                   $key != "blobSubItem" &&
                   $key != "id" &&
                   $key != "value" &&
                   $key != "binary"){
                    $itemset->setAttribute("{$key}", "{$value}");
                }
            }
            if($lazy === true){
                // Don't start loading the data yet
                return $itemset;
            }
        }
        $newData = $itemset->getData();
        if(count($newData) > 0){
            foreach($newData as $value){
                foreach($children as $c){
                    if($c->getName() == "ReportItem" ||
                        ($c->getName() == "If" ||
                         $c->getName() == "ElseIf" ||
                         $c->getName() == "Else" ||
                         $c->getName() == "Static") && trim("{$c}") !== ""){
                        $item = $this->parseReportItem($itemset, $c, $value);
                    }
                    else if($c->getName() == "ReportItemSet" || 
                            $c->getName() == "If" ||
                            $c->getName() == "ElseIf" ||
                            $c->getName() == "Else" ||
                            $c->getName() == "For"){
                        $item = $this->parseReportItemSet($itemset, $c, $value, 'both');
                    }
                    if($item == null){
                        continue;
                    }
                    $item->setProjectId($value['project_id']);
                    $item->setMilestoneId($value['milestone_id']);
                    $item->setProductId($value['product_id']);
                    $item->setPersonId($value['person_id']);
                    $item->setExtra($value['extra']);
                    foreach($value['misc'] as $key=>$val){
                        $item->setAttribute("{$key}", "{$val}");
                    }
                    if(!is_null($value['item_id'])){
                        $item->setId($item->id."_".$value['item_id']);
                    }
                }
                $itemset->iteration++;
            }
        }
        
        $itemset->setValue("{$node}");
        return $itemset;
    }

    // Parses the <ReportItem> element of the XML
    function parseReportItem(&$section, $node, $value=array()){
        $attributes = $node->attributes();
        $item = $section->getReportItemById("{$attributes->id}");
        if($node->getName() == "If" ||
           $node->getName() == "ElseIf" ||
           $node->getName() == "Else" ||
           $node->getName() == "Static"){
           @$node->addAttribute("type", $node->getName());
        }
        if(isset($attributes->type) || $item != null){
            if(isset($attributes->type)){
                $type = "{$attributes->type}";
                if(!class_exists($type) && class_exists($type."ReportItem")){
                    $type = $type."ReportItem";
                }
                if(!class_exists($type)){
                    $this->errors[] = "ReportItem '{$type}' does not exists";
                    $item = "StaticReportItem";
                }
                $item = new $type();
                $position = isset($attributes->position) ? "{$attributes->position}" : null;
                $section->addReportItem($item, $position);
            }
            else{
                $type = get_class($item);
            }
            if($this->report->project != null){
                $item->setProjectId($this->report->project->getId());
            }
            if(isset($attributes->id)){
                $item->setId("{$attributes->id}");
            }
            else if(!($item instanceof StaticReportItem)){
                $this->errors[] = "ReportItem does not contain an id";
            }
            if(isset($attributes->delete) && strtolower("{$attributes->delete}") == "true"){
                $section->deleteReportItem($item);
            }
            if(isset($attributes->delete) && strtolower("{$attributes->delete}") == "false"){
                $section->deleteReportItem($item);
            }
            if(isset($attributes->private)){
                $item->setPrivate(strtolower($attributes->private) == "true");
            }
            if(isset($attributes->encrypt)){
                $item->setEncrypt(strtolower($attributes->encrypt) == "true");
            }
            if(isset($value['project_id'])){
                $item->setProjectId($value['project_id']);
            }
            if(isset($value['milestone_id'])){
                $item->setMilestoneId($value['milestone_id']);
            }
            if(isset($value['product_id'])){
                $item->setProductId($value['product_id']);
            }
            if(isset($value['person_id'])){
                $item->setPersonId($value['person_id']);
            }
            if(isset($value['extra'])){
                $item->setExtra($value['extra']);
            }
            if(isset($attributes->blobType)){
                $t = AbstractReport::blobConstant($attributes->blobType);
                $item->setBlobType($t);
            }
            if(isset($attributes->blobItem)){
                $i = AbstractReport::blobConstant($attributes->blobItem);
                $item->setBlobItem($i);
            }
            if(isset($attributes->blobSubItem)){
                $i = AbstractReport::blobConstant($attributes->blobSubItem);
                $item->setBlobSubItem($i);
            }
            foreach($attributes as $key => $value){
                if($key != "type" &&
                   $key != "blobType" &&
                   $key != "blobItem" &&
                   $key != "blobSubItem" &&
                   $key != "id" &&
                   $key != "value" &&
                   $key != "binary"){
                    $item->setAttribute("{$key}", "{$value}");
                }
            }
            $item->setValue("{$node}");
            if(!isset($attributes->value) && !($section instanceof ReportItemSet)){
                if(isset($_GET['saveBackup'])){
                    $value = "";
                    $data = $item->getBlobValue();
                    $value = encode_binary_data($data);
                    $node->addAttribute("value", $value);
                    $node->addAttribute("binary", "true");
                }
            }
            else if(!($section instanceof ReportItemSet)){
                if(isset($attributes->binary) && strtolower($attributes->binary) == "true"){
                    $item->setBlobValue(decode_binary_data($attributes->value));
                }
                else{
                    $item->setBlobValue("{$attributes->value}");
                }
            }
        }
        return $item;
    }
}

function is_base64($s){
    return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s);
}

function encode_binary_data($str){
    $result = @base64_encode($str);
    if($result !== false && base64_decode($result) === $str){
        return $result;
    }
}

function decode_binary_data($str){
    if(is_base64($str)){
        return base64_decode($str);
    }
    $exploded = explode(" ", $str);
    foreach($exploded as $ord){
        $chr = chr($ord);
        $string[] = $chr;
    }
    $str = implode("", $string);
    return $str;
}
?>
