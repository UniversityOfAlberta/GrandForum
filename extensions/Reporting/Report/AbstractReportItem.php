<?php

/**
 * This class is used to display widgets for reporting.
 * ReportItems are (generally) associated with a blob and 
 * the saving/loading is done automatically.
 * @package Report
 * @abstract
 */

// Other

abstract class AbstractReportItem extends Callbackable {
    
    var $id;
    var $parent;
    var $blobType;
    var $personId;
    var $projectId;
    var $milestoneId;
    var $productId;
    var $extra;
    var $extraIndex;
    var $private;
    var $encrypt;
    var $deleted;
    var $blobSection;
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
        $this->blobSection = null;
        $this->blobItem = 0;
        $this->blobSubItem = 0;
        $this->attributes = array();
        $this->personId = 0;
        $this->projectId = 0;
        $this->milestoneId = 0;
        $this->productId = 0;
        $this->extra = array();
        $this->private = false;
        $this->encrypt = false;
        $this->deleted = false;
    }
    
    function setId($id){
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
    
    // Sets the Blob Section of this AbstractReportItem (optional)
    function setBlobSection($item){
        $this->blobSection = $item;
    }

    // Sets the Blob Item of this AbstractReportItem
    function setBlobItem($item){
        $this->blobItem = $this->varSubstitute($item);
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
    
    // Returns the 'pdf' rendered version of the widget in text form
    function getText(){
        global $wgOut;
        $oldWgOut = $wgOut;
        $oldValue = $this->value;
        $context = new RequestContext();
        $wgOut = $context->getOutput();
        $this->value = '{$item}';
        $this->renderForPDF();
        $this->value = $oldValue;
        $text = $wgOut->getHTML();
        $wgOut = $oldWgOut;
        return $text;
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
            $postId = @$parent->getPostId()."_person{$this->personId}_project{$this->projectId}_milestone{$this->milestoneId}_extra{$extraId}".$postId;
        }
        else{
            $postId = str_replace("&", "", str_replace("'", "", str_replace(" ", "", strip_tags($parent->getPostId())))).$postId;
        }
        $postId = str_replace("-", "", $postId);
        $postId = str_replace(" ", "", $postId);
        $postId = str_replace("/", "", $postId);
        return $postId;
    }
    
    // By default only calls setBlobValue using the postdata, but can
    // be overridden to do some proccessing before hand, or handle uploads etc.
    function save(){
        $this->render();
        if(isset($_POST[$this->getPostId()])){
            if(strtolower($this->getAttr('default', '')) == ''){
                if(!isset($_POST[$this->getPostId().'_ignoreConflict']) ||
                   $_POST[$this->getPostId().'_ignoreConflict'] != "true"){
                    if(isset($_POST['oldData'][$this->getPostId()]) && is_array($_POST['oldData'][$this->getPostId()])){
                        // Don't handle arrays, but save anyways
                        $this->setBlobValue($_POST[$this->getPostId()]);
                        return array();
                    }
                    if(isset($_POST['oldData'][$this->getPostId()]) &&
                       $this->stripBlob($_POST['oldData'][$this->getPostId()]) == $this->stripBlob($_POST[$this->getPostId()])){
                       // Don't save, but also don't display an error
                       return array();
                    }
                    /*
                    else if(isset($_POST['oldData'][$this->getPostId()]) && 
                       $this->stripBlob($_POST['oldData'][$this->getPostId()]) != $this->stripBlob($this->getBlobValue()) &&
                       $this->stripBlob($_POST[$this->getPostId()]) != $this->stripBlob($this->getBlobValue())){
                        if($this->stripBlob($_POST['oldData'][$this->getPostId()]) != $this->stripBlob($_POST[$this->getPostId()])){
                            // Conflict in blob values
                            //echo $this->stripBlob($_POST['oldData'][$this->getPostId()])."\n\n";
                            //echo $this->stripBlob($this->getBlobValue());
                            return array(array('postId' => $this->getPostId(), 
                                               'value' => $this->stripBlob($this->getBlobValue()),
                                               'postValue' => $this->stripBlob($_POST[$this->getPostId()]),
                                               'oldValue' => $this->stripBlob($_POST['oldData'][$this->getPostId()]),
                                               'diff' => @htmlDiffNL(str_replace("\n", "\n ", $this->getBlobValue()), str_replace("\n", "\n ", $_POST[$this->getPostId()]))));
                        }
                    }
                    */
                }
            }
            $this->setBlobValue($_POST[$this->getPostId()]);
        }
        return array();
    }
    
    protected function stripBlob($value){
        $value = str_replace(" </p>", "</p>", $value); // TinyMCE sometimes adds this
        $value = preg_replace("/&([0-9]*;)/", '&amp;$1', $value); // In-case an invalid html code is used (ie. &11;)
        $value = trim(htmlentities($value, null, 'utf-8', false));
        $value = str_replace("⟨", "&lang;", $value);
        $value = str_replace("⟩", "&rang;", $value);
        $value = preg_replace("~(&lt;)!--(.*?)--(&gt;)~s", "", $value);
        $value = preg_replace("/&(?!amp)([a-zA-Z]*;)/", '&amp;$1', $value); // In-case an invalid html code is used (ie. &asdf;)
        return $value;
    }

    // Gets the Blob of this item
    function getBlobValue(){
        $report = $this->getReport();
        $section = $this->getSection();
        $personId = $this->getAttr('personId', $this->getReport()->person->getId());
        $projectId = $this->getAttr('projectId', $this->projectId);
        $sectionId = ($this->blobSection != null) ? $this->blobSection : $section->sec;
        
        $sec = $this->getAttr('blobSection', $sectionId);
        $rep = $this->getAttr('blobReport', $report->reportType);
        $year = $this->getAttr('blobYear', $this->getReport()->year);
        
        $blob = new ReportBlob($this->blobType, $year, $personId, $projectId);
	    $blob_address = ReportBlob::create_address($rep, $sec, $this->blobItem, $this->blobSubItem);
	    $blob->load($blob_address);
	    $blob_data = $blob->getData();
	    $this->extraIndex = $this->getExtraIndex();
        switch($this->blobType){
            default:
            case BLOB_TEXT:
            case BLOB_WIKI:
            case BLOB_HTML:
                if($blob_data !== null){
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
                    $id = $this->varSubstitute($this->id);
                    $accessStr = "['{$id}']";
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
        $projectId = $this->getAttr('projectId', $this->projectId);
        $sectionId = ($this->blobSection != null) ? $this->blobSection : $section->sec;
        
        $sec = $this->getAttr('blobSection', $sectionId);
        $rep = $this->getAttr('blobReport', $report->reportType);
        $year = $this->getAttr('blobYear', $this->getReport()->year);
        
        $blob = new ReportBlob($this->blobType, $year, $personId, $projectId);
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
        $projectId = $this->getAttr('projectId', $this->projectId);
        $sectionId = ($this->blobSection != null) ? $this->blobSection : $section->sec;
        
        $sec = $this->getAttr('blobSection', $sectionId);
        $rep = $this->getAttr('blobReport', $report->reportType);
        $year = $this->getAttr('blobYear', $this->getReport()->year);
        
        $blob = new ReportBlob($this->blobType, $year, $personId, $projectId);
	    $blob_address = ReportBlob::create_address($rep, $sec, $this->blobItem, $this->blobSubItem);
	    $blob->load($blob_address);
	    $blob_data = $blob->getData();
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
                $parent = $this->getParent();
                $accessStr = "";
                if($this->id != ""){
                    $id = $this->varSubstitute($this->id);
                    $accessStr = "['{$id}']";
                }
                while($parent instanceof ReportItemSet){
                    if($parent->blobIndex != ""){
                        $accessStr = "['{$this->{$parent->blobIndex}}']".$accessStr;
                    }
                    $parent = $parent->getParent();
                }
                $value = str_replace("\00", "", $value); // Fixes problem with the xml backup putting in random null escape sequences
                if(is_array($value)){
                    foreach($value as $k => $v){
                        if((is_array($v) && recursive_implode("", $v) == "") || $v == ""){
                            unset($value[$k]);
                        }
                    }
                }
                if(is_string($blob_data)){
                    // Convert to array
                    $blob_data = array();
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
        $projectId = $this->getAttr('projectId', $this->projectId);
        $sectionId = ($this->blobSection != null) ? $this->blobSection : $section->sec;
        
        $sec = $this->getAttr('blobSection', $sectionId);
        $rep = $this->getAttr('blobReport', $report->reportType);
        $year = $this->getAttr('blobYear', $this->getReport()->year);
        
        $blob = new ReportBlob($this->blobType, $year, $personId, $projectId);
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
            if(!$found && $this instanceof ReportItemSet){
                $this->variables[$key] = $value;
                return true;
            }
            return $found;
        }
        return false;
    }
    
}

?>
