<?php

class ReportXMLParser {
    
    var $xml;
    var $errors;
    var $parser;
    var $report;
    static $time = 0;
    
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
        $md5 = md5(trim($dom->saveXML()));
        $date = new DateTime();
        $time = $date->format('Y-m-d H-i-s');
        $serialized = serialize(array('md5' => $md5, 
                                      'time' => $time,
                                      'type' => $this->report->xmlName,
                                      'xml' => trim($dom->saveXML())));
        $key = $this->getKey();
        $iv = $this->getIV();
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $serialized, MCRYPT_MODE_ECB, $iv);
        $encrypted = gzcompress($encrypted, 9);
        $sql = "INSERT INTO `grand_report_backup`
                (`report`,`time`,`person_id`,`backup`)
                VALUES ('".addslashes($this->report->name)."','$time','{$this->report->person->getId()}','".mysql_real_escape_string($encrypted)."')";
        DBFunctions::execSQL($sql, true);
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
            $key = $this->getKey();
            $iv = $this->getIV();
            $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $str, MCRYPT_MODE_ECB, $iv);
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
            $wgOut->addHTML("<script type='text/javascript'>
                                if (typeof console != 'undefined' && console != null) {\n");
            foreach($this->errors as $error){
                $wgOut->addHTML("   console.warn('".addslashes($error)."');\n");
            }
            $wgOut->addHTML("   }
                            </script>\n");
        }
    }
    
    // Parses the XML document starting at the root
    function parse(){
        $this->parser = simplexml_load_string($this->xml);
        $this->parseReport();
        $this->showErrors();
    }
    
    // Parses the <Report> element of the XML
    function parseReport(){
        if($this->parser->getName() == "Report"){
            $attributes = $this->parser->attributes();
            $children = $this->parser->children();
            if(isset($attributes->name)){
                $this->report->setName("{$attributes->name}");
            }
            if(isset($attributes->reportType)){
                if(!defined($attributes->reportType)){
                    $this->errors[] = "Report Type '{$attributes->reportType}' does not exist for Report, using RP_RESEARCHER";
                }
                $type = (defined($attributes->reportType)) ? constant($attributes->reportType) : RP_RESEARCHER;
                $this->report->setReportType($type);
            }
            if(isset($attributes->pdfType)){
                if(!defined($attributes->pdfType)){
                    $this->errors[] = "PDF Type '{$attributes->pdfType}' does not exist for Report, using RPTP_NORMAL";
                }
                $type = (defined($attributes->pdfType)) ? constant($attributes->pdfType) : RPTP_NORMAL;
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
            if(isset($attributes->disabled) && strtolower($attributes->disabled) == 'true'){
                $this->report->setDisabled(true);
            }
            if(isset($attributes->personId)){
                $id = "{$attributes->personId}";
                $this->report->person = Person::newFromId($id);
                $this->report->person->id = $id;
            }
            if(isset($children->Permissions)){
                $this->parsePermissions($children->Permissions);
            }
            if(isset($children->ReportSection)){
                $this->parseReportSection($children->ReportSection);
            }
        }
    }
    
    // Parses the <Permissions> element of the XML
    function parsePermissions($node){
        $children = $node->children();
        foreach($children as $key => $child){
            if($key == "Role"){
                $attributes = $child->attributes();
                $role = (isset($attributes->role)) ? @constant($attributes->role) : "MANAGER";
                $start = (isset($attributes->start)) ? @constant($attributes->start) : "0000-00-00";
                $end = (isset($attributes->end)) ? @constant($attributes->end) : "2100-12-31";
                if($start == null){
                    $this->errors[] = "Start time '{$attributes->start}' does not exist";
                }
                if($end == null){
                    $this->errors[] = "Start time '{$attributes->end}' does not exist";
                }
                $this->parseRoleSectionPermissions($child, $role);
                $this->report->addPermission("Role", "{$role}", "{$start}", "{$end}");
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
    
    // Parses the <ReportSection> element of the XML
    function parseReportSection($node){
        foreach($node as $key => $n){
            $attributes = $n->attributes();
            $children = $n->children();
            if(isset($attributes->type)){
                $type = "{$attributes->type}";
                if(!class_exists($type)){
                    $this->errors[] = "ReportSection '{$type}' does not exists";
                    continue;
                }
                $section = new $type();
                $this->report->addSection($section);
                if(isset($attributes->id)){
                    $section->setId("{$attributes->id}");
                }
                if(isset($attributes->name)){
                    $section->setName("{$attributes->name}");
                }
                if(isset($attributes->tooltip)){
                    $section->setTooltip(str_replace("'", "&#39;", "{$attributes->tooltip}"));
                }
                if(isset($attributes->blobSection)){
                    if(!defined($attributes->blobSection)){
                        $this->errors[] = "Blob Section '{$attributes->blobSection}' does not exist for ReportSection, using SEC_NONE";
                    }
                    $sec = (defined($attributes->blobSection)) ? constant($attributes->blobSection) : SEC_NONE;
                    $section->setBlobSection($sec);
                }
                if($type == "EditableReportSection" && isset($attributes->autosave)){
                    $section->setAutosave(strtolower($attributes->autosave) == "true");
                }
                if($type == "EditableReportSection" && isset($attributes->reportCharLimits)){
                    $section->setReportCharLimits(strtolower($attributes->reportCharLimits) == "true");
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
                if($this->report->project != null){
                    $section->setProjectId($this->report->project->getId());
                }
                $section->setPersonId($this->report->person->getId());
                foreach($children as $c){
                    if($c->getName() == "Instructions"){
                        $section->setInstructions("{$children->Instructions}");
                    }
                    else if($c->getName() == "ReportItem"){
                        $this->parseReportItem($section, $c);
                    }
                    else if($c->getName() == "ReportItemSet"){
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
    function parseReportItemSet(&$section, $node, $data=array()){
        $attributes = $node->attributes();
        $children = $node->children();
        if(isset($attributes->type)){
            $type = "{$attributes->type}";
            if(class_exists($type)){
                $itemset = new $type();
            }
            else{
                $this->errors[] = "ReportItemSet '{$attributes->type}' does not exists";
                return;
            }
        }
        else{
            $this->errors[] = "ReportItemSet '' does not exists";
            return;
        }
        $section->addReportItem($itemset);
        if(isset($attributes->id)){
            $itemset->setId("{$attributes->id}");
        }
        else{
            $this->errors[] = "ReportItemSet does not contain an id";
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
        if(isset($data['person_id'])){
            $itemset->setPersonId($data['person_id']);
        }
        
        $newData = $itemset->getData();
        if(count($newData) > 0){
            foreach($newData as $value){
                foreach($children as $c){
                    if($c->getName() == "ReportItem"){
                        $item = $this->parseReportItem($itemset, $c);
                    }
                    else if($c->getName() == "ReportItemSet"){
                        $item = $this->parseReportItemSet($itemset, $c, $value);
                    }
                    if($item == null){
                        continue;
                    }
                    $item->setProjectId($value['project_id']);
                    $item->setMilestoneId($value['milestone_id']);
                    $item->setProductId($value['product_id']);
                    $item->setPersonId($value['person_id']);
                }
            }
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
        $itemset->setValue("{$node}");
        return $itemset;
    }

    // Parses the <ReportItem> element of the XML
    function parseReportItem(&$section, $node){
        $attributes = $node->attributes();
        if(isset($attributes->type)){
            $type = "{$attributes->type}";
            if(!class_exists($type)){
                $this->errors[] = "ReportItem '{$type}' does not exists";
                return;
            }
            $item = new $type();
            $section->addReportItem($item);
            if(!$this->report->topProjectOnly && $this->report->project != null){
                $item->setProjectId($this->report->project->getId());
            }
            if(isset($attributes->id)){
                $item->setId("{$attributes->id}");
            }
            else{
                $this->errors[] = "ReportItem does not contain an id";
            }
            if(isset($attributes->private)){
                $item->setPrivate(strtolower($attributes->private) == "true");
            }
            if(isset($attributes->blobType)){
                if(!defined($attributes->blobType)){
                    $this->errors[] = "Blob Type '{$attributes->blobType}' does not exist for ReportItem, using BLOB_TEXT";
                }
                $t = (defined($attributes->blobType)) ? constant($attributes->blobType) : BLOB_TEXT;
                $item->setBlobType($t);
            }
            if(isset($attributes->blobItem)){
                if(!defined($attributes->blobItem)){
                    $this->errors[] = "Blob Item '{$attributes->blobItem}' does not exist for ReportItem, using null";
                }
                $i = (defined($attributes->blobItem)) ? constant($attributes->blobItem) : null;
                $item->setBlobItem($i);
            }
            if(isset($attributes->blobSubItem)){
                if(!defined($attributes->blobSubItem)){
                    $this->errors[] = "Blob Sub-Item '{$attributes->blobSubItem}' does not exist for ReportItem, using null";
                }
                $i = (defined($attributes->blobSubItem)) ? constant($attributes->blobSubItem) : null;
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

function encode_binary_data($str){
    $string = array();
    $value = utf8_encode($str);
    for($i = 0; $i < strlen($value); $i++){
        $ord = ord($value[$i]);
        $string[] = $ord;
    }
    return implode(" ", $string);
}

function decode_binary_data($str){
    $exploded = explode(" ", $str);
    foreach($exploded as $ord){
        $chr = chr($ord);
        $string[] = $chr;
    }
    return implode("", $string);
}
?>
