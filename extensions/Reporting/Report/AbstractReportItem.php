<?php

/**
 * This class is used to display widgets for reporting.
 * ReportItems are (generally) associated with a blob and 
 * the saving/loading is done automatically.
 * @package Report
 * @abstract
 */

// Other
require_once("ReportItemCallback.php");

abstract class AbstractReportItem {
    
    var $id;
    var $parent;
    var $blobType;
    var $personId;
    var $projectId;
    var $milestoneId;
    var $productId;
    var $str; // Used in StringReportItemSets
    var $extra;
    var $extraIndex;
    var $private;
    var $encrypt;
    var $deleted;
    var $blobItem;
    var $blobSubItem;
    var $value;
    var $attributes;
    var $variables = array();
    var $prev = null;
    
    // Creates a new AbstractReportItem
    function __construct(){
        $this->id = "";
        $this->value = "";
        $this->blobType = BLOB_TEXT;
        $this->projectId = 0;
        $this->blobItem = 0;
        $this->blobSubItem = 0;
        $this->attributes = array();
        $this->personId = 0;
        $this->projectId = 0;
        $this->milestoneId = 0;
        $this->productId = 0;
        $this->str = "";
        $this->extra = array();
        $this->private = false;
        $this->encrypt = false;
        $this->deleted = false;
    }
    
    function setId($id){
        $id = $this->varSubstitute($id);
        $this->id = $id;
    }
    
    function setAttr($key, $value){
        $this->setAttribute($key, $value);
    }
    
    function setAttribute($key, $value){
        $this->attributes[$key] = $value;
    }
    
    // Sets the parent ReportSection or ReportItem for this AbstractReportItem
    function setParent($parent){
        $this->parent = $parent;
    }

    function getId(){
        return $this->varSubstitute($this->id);
    }
    
    function getParent(){
        return $this->parent;
    }
    
    function getReport(){
        return $this->getSection()->getParent();
    }
    
    function getSection(){
        $parent = $this->getParent();
        while(!($parent instanceof AbstractReportSection)){
            $parent = $parent->getParent();
        }
        return $parent;
    }
    
    function getPrev(){
        if($this->prev == null){
            $items = $this->getParent()->getItems();
            $prev = null;
            foreach($items as $item){
                $item->prev = $prev;
                $prev = $item;
            }
        }
        return $this->prev;
    }   
    
    function getSet(){
        $parent = $this->getParent();
        while(!($parent instanceof ReportItemSet)){
            if($parent instanceof AbstractReport){
                break;
            }
            $parent = $parent->getParent();
        }
        return $parent;
    }
    
    // Sets the Blob Type of this AbstractReportItem
    // This determines how the data should be stored, and retrieved.
    function setBlobType($type){
        $this->blobType = $type;
    }
    
    // Sets the Project ID of this AbstractReportItem
    function setProjectId($id){
        $this->projectId = $id;
    }
    
    function setPersonId($id){
        $this->personId = $id;
    }

    // Sets the Project ID of this AbstractReportItem
    function setMilestoneId($id){
        $this->milestoneId = $id;
    }
    
    // Sets the Product ID of this AbstractReportItem
    function setProductId($id){
        $this->productId = $id;
    }
    
    // Sets the extra data of this AbstractReportItem
    function setExtra($extra){
        $this->extra = $extra;
    }
    
    // Sets whether or not this item should be treated as private or not
    function setPrivate($private){
        $this->private = $private;
    }
    
    // Whether to encrypt or not
    function setEncrypt($encrypt){
        $this->encrypt = $encrypt;
    }
    
    /**
     * Sets whether or not this AbstractReportItem should be treated as deleted or not
     * @param $deleted boolean Whether or not this AbstractReportItem should be treated as deleted or not
     */
    function setDeleted($deleted){
        $this->deleted = $deleted;
    }

    // Sets the Blob Item of this AbstractReportItem
    function setBlobItem($item){
        $item = $this->varSubstitute($item);
        $this->blobItem = $item;
    }
    
    // Sets the Blob Sub-Item of this AbstractReportItem
    function setBlobSubItem($item){
        $item = $this->varSubstitute($item);
        $this->blobSubItem = $item;
    }

    function setValue($value){
        $this->value = $value;
    }
    
    function processCData($output){
        $cdata = trim("{$this->value}");
        $cdata = $this->varSubstitute($cdata);
        $cdata = str_replace('{$item}', $output, $cdata);
        return $cdata;
    }

    //Responsible for rendering the actual widget
    abstract function render();
    
    // Returns the number of completed values (usually 1, or 0)
    function getNComplete(){
        $opt = $this->getAttr('optional', '0');
        if($opt == '1' || $opt == 'true'){
            return 0;
        }
        if($this->getReport()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        
        $blob = $this->getBlobValue();
        if($blob !== "" && $blob != null){
            return 1;
        }
        else{
            return 0;
        }
    }
    
    // Returns the number of fields which are associated with this AbstractReportItem (usually 1)
    function getNFields(){
        $opt = $this->getAttr('optional', '0');
        if($opt == '1' || $opt == 'true'){
            return 0;
        }
        if($this->getReport()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        return 1;
    }
    
    function getExtraIndex(){
        $set = $this->getSet();
        while(!($set instanceof ArrayReportItemSet)){
            if($set instanceof AbstractReport){
                return 0;
            }
            $set = $set->getParent();
        }
        foreach($set->getCachedData() as $index => $item){
            if($item['extra'] == $this->extra){
                return $index;
            }
        }
        return 0;
    }
    
    function getPostId(){
        $parent = $this->getParent();
        if($this instanceof AbstractReportItem){
            $postId = str_replace("\"", "", str_replace("]", "", str_replace("[", "", str_replace("'", "", "_{$this->getId()}"))));
        }
        if(!($parent instanceof AbstractReportSection)){
            $extraId = $this->getExtraIndex();
            $postId = @$parent->getPostId()."_p1{$this->personId}_p2{$this->projectId}_p3{$this->productId}_m{$this->milestoneId}_e{$extraId}".$postId;
        }
        else{
            $postId = str_replace(" ", "", $parent->name).$postId;
        }
        $postId = str_replace("-", "", $postId);
        $postId = str_replace(" ", "", $postId);
        return $postId;
    }
    
    // By default only calls setBlobValue using the postdata, but can
    // be overridden to do some proccessing before hand, or handle uploads etc.
    function save(){
        if(isset($_POST[$this->getPostId()])){
            if(md5(serialize($_POST[$this->getPostId()])) !== 
               md5(serialize($this->getBlobValue()))){
                $this->setBlobValue($_POST[$this->getPostId()]);
            }
        }
        return array();
    }
    
    private function stripBlob($value){
        return trim(htmlentities($value, null, 'utf-8', false));
        return $value;
    }

    // Gets the Blob of this item
    function getBlobValue(){
        $report = $this->getReport();
        $section = $this->getSection();
        $personId = $this->getAttr('personId', $report->person->getId());
        $sec = $this->getAttr('blobSection', $section->sec);
        $rep = $this->getAttr('blobReport', $section->getAttr('blobReport', $report->reportType));
        $year = $this->getAttr('blobYear', $report->year);

        $blob = new ReportBlob($this->blobType, $year, $personId, $this->projectId);
        $blob_address = ReportBlob::create_address($rep, $sec, $this->blobItem, $this->blobSubItem);
        $blob->load($blob_address);
        $blob_data = $blob->getData();
        $this->extraIndex = $this->getExtraIndex();
        switch($this->blobType){
            default:
            case BLOB_TEXT:
            case BLOB_WIKI:
            case BLOB_HTML:
                if($blob_data != null){
                    $blob_data = str_replace("\00", "", $blob_data);
                    $blob_data = str_replace("", "", $blob_data);
                    $blob_data = str_replace("", "", $blob_data);
                    $blob_data = str_replace("", "", $blob_data);
                    $blob_data = str_replace("", "fi", $blob_data);
                }
            case BLOB_EXCEL:
            case BLOB_RAW:
                $value = $blob_data;
                break;
            case BLOB_ARRAY:
                $parent = $this->getParent();
                $accessStr = "";
                if($this->id != ""){
                    $accessStr = "['{$this->id}']";
                }
                while($parent instanceof ReportItemSet){
                    if($parent->blobIndex != ""){
                        $accessStr = "['{$this->{$parent->blobIndex}}']".$accessStr;
                    }
                    $parent = $parent->getParent();
                }
                eval("\$value = @\$blob_data$accessStr;");
                break;
        }
        return $value;
    }
    
    /**
     * Returns the MD5 code for this blob
     */
    function getMD5($urlencode=true){
        $report = $this->getReport();
        $section = $this->getSection();
        $personId = $this->getAttr('personId', $this->getReport()->person->getId());
        $sec = $this->getAttr('blobSection', $section->sec);
        $rep = $this->getAttr('blobReport', $section->getAttr('blobReport', $report->reportType));

        $blob = new ReportBlob($this->blobType, $this->getReport()->year, $personId, $this->projectId);
        $blob_address = ReportBlob::create_address($rep, $sec, $this->blobItem, $this->blobSubItem);
        $blob->load($blob_address, true);
        $md5 = $blob->getMD5($urlencode);
        return $md5;
    }
    
    /**
     * Returns the download link for this blob
     */
    function getDownloadLink(){
        global $wgServer, $wgScriptPath;
        $md5 = $this->getMD5();
        $mime = $this->getAttr('mimeType', '');
        if($mime != ""){
            $mime = "&mime={$mime}";
        }
        return "{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$md5}{$mime}";
    }
    
    /**
     * Sets the Blob value for this AbstractReportItem
     * @param mixed $value The value of this AbstractReportItem
     */
    function setBlobValue($value){
        $report = $this->getReport();
        $section = $this->getSection();
        $personId = $this->getAttr('personId', $this->getReport()->person->getId());
        $sec = $this->getAttr('blobSection', $section->sec);
        $rep = $this->getAttr('blobReport', $section->getAttr('blobReport', $report->reportType));
        
        $blob = new ReportBlob($this->blobType, $this->getReport()->year, $personId, $this->projectId);
        $blob_address = ReportBlob::create_address($rep, $sec, $this->blobItem, $this->blobSubItem);
        $this->extraIndex = $this->getExtraIndex();
        switch($this->blobType){
            default:
            case BLOB_TEXT:
            case BLOB_WIKI:
            case BLOB_HTML:
                $value = str_replace("\00", "", $value); // Fixes problem with the xml backup putting in random null escape sequences
                if(is_string($value)){
                    $blob->store(trim($value), $blob_address, $this->encrypt);
                }
                break;
            case BLOB_ARRAY:
                $blob->load($blob_address);
                $blob_data = $blob->getData();
                $parent = $this->getParent();
                $accessStr = "";
                if($this->id != ""){
                    $accessStr = "['{$this->id}']";
                }
                while($parent instanceof ReportItemSet){
                    if($parent->blobIndex != ""){
                        $accessStr = "['{$this->{$parent->blobIndex}}']".$accessStr;
                    }
                    $parent = $parent->getParent();
                }
                if(is_array($value)){
                    foreach($value as $k => $v){
                        if((is_array($v) && implode("", flatten($v)) == "") || $v == ""){
                            unset($value[$k]);
                        }
                    }
                }
                eval("\$blob_data$accessStr = \$value;");
                $blob->store($blob_data, $blob_address, $this->encrypt);
                break;
            case BLOB_EXCEL:
                if(mb_check_encoding($value, 'UTF-8')){
                    $value = utf8_decode($value);
                }
                $blob->store($value, $blob_address, $this->encrypt);
                $blob->load($blob_address);
                break;
            case BLOB_RAW:
                $blob->store(utf8_decode($value), $blob_address, $this->encrypt);
                $blob->load($blob_address);
                break;
        }
    }
    
    function delete(){
        $report = $this->getReport();
        $section = $this->getSection();
        $personId = $this->getAttr('personId', $this->getReport()->person->getId());
        $sec = $this->getAttr('blobSection', $section->sec);
        $rep = $this->getAttr('blobReport', $section->getAttr('blobReport', $report->reportType));

        $blob = new ReportBlob($this->blobType, $this->getReport()->year, $personId, $this->projectId);
        $blob_address = ReportBlob::create_address($rep, $sec, $this->blobItem, $this->blobSubItem);
        $blob->delete($blob_address);
    }
    
    function getAttr($attr, $default="", $varSubstitute=true){
        if($varSubstitute){
            $value = (isset($this->attributes[$attr])) ? $this->varSubstitute($this->attributes[$attr]) : $default;
        }
        else{
            $value = (isset($this->attributes[$attr])) ? $this->attributes[$attr] : $default;
        }
        return "$value";
    }

    //Function that finds all variables in CDATA (if any) and substitutes them by finding there values with the help of RI type-specific callbacks
    function varSubstitute($cdata){
        if(defined($cdata) && strtolower($cdata) != "true" && strtolower($cdata) != "false"){
             return constant($cdata);
        }
        $matches = array();
        preg_match_all('/{\$(.+?)}/', $cdata, $matches);
        foreach($matches[1] as $k => $m){
            if(isset(ReportItemCallback::$callbacks[$m])){
                $v = str_replace("$", "\\$", ReportItemCallback::call($this, $m));
                $v = str_replace(",", "&#44;", $v);
                $cdata = str_replace("{\$".$m."}", nl2br($v), $cdata);
            }
        }
        
        // Support nested function calls
        preg_match_all('/(?={((?:[^{}]++|{(?1)})++)})/', $cdata, $matches);
        // Reverse the array so that it gets the inner most first
        //print_r($matches[1]);
        //$matches[1] = array_reverse($matches[1]);
        $recursive = false;
        $noLongerRecursive = false;
        foreach($matches[1] as $k => $m){
            $m = $matches[1][$k];
            $e = explode('(', $m);
            if(isset($e[1])){
                // Function call
                $f = $e[0];
                $a = explode(",", str_replace(")", "", $e[1]));
                foreach($a as $key => $arg){
                    $arg = trim($arg);
                    $a[$key] = AbstractReport::blobConstant($arg);
                }
                if(isset(ReportItemCallback::$callbacks[$f])){
                    if(strstr($m, "{") !== false || strstr($m, "}") !== false){
                        // Don't process yet if there are recursive calls
                        $recursive = true;
                        continue;
                    }
                    else{
                        $v = ReportItemCallback::call($this, $f, $a);
                        if(is_array($v)){
                            foreach($matches[1] as $k2 => $m2){
                                $matches[1][$k2] = str_replace("{".$m."}", serialize($v), $m2);
                            }
                            $cdata = str_replace("{".$m."}", serialize($v), $cdata);
                        }
                        else{
                            $v = str_replace(",", "&#44;", $v);
                            foreach($matches[1] as $k2 => $m2){
                                $matches[1][$k2] = str_replace("{".$m."}", $v, $m2);
                            }
                            $cdata = str_replace("{".$m."}", $v, $cdata);
                        }
                        if($recursive){
                            break;
                        }
                    }
                }
            }
        }
        if($recursive){
            // There are recursive calls, now call them
            $cdata = $this->varSubstitute($cdata);
        }
        return $cdata;
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
        else{
            return $this->getParent()->getVariable($key);
        }
    }
    
    /**
     * Sets the value of the variable with the given key to the given value
     * @param string $key The key of the variable
     * @param string $value The value of the variable
     * @param integer $depth The depth of the function call (should not need to ever pass this)
     * @return boolean Whether or not the variable was found
     */
    function setVariable($key, $value, $depth=0){
        if($this instanceof ReportItemSet && isset($this->variables[$key])){
            $this->variables[$key] = $value;
            return true;
        }
        else{
            $found = $this->getParent()->setVariable($key, $value, $depth + 1);
            if(!$found && $depth == 1 && $this instanceof ReportItemSet){
                $this->variables[$key] = $value;
                return true;
            }
        }
        return false;
    }
    
}

?>
