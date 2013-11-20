<?php

/**
 * This class is used to display widgets for reporting.
 * ReportItems are (generally) associated with a blob and 
 * the saving/loading is done automatically.
 * @package Report
 * @abstract
 */

// ReportItems
require_once("ReportItems/StaticReportItem.php");
require_once("ReportItems/TextReportItem.php");
require_once("ReportItems/BudgetReportItem.php");
require_once("ReportItems/RadioReportItem.php");
require_once("ReportItems/TextareaReportItem.php");
require_once("ReportItems/AutoCompleteTextareaReportItem.php");
require_once("ReportItems/CalendarReportItem.php");

require_once("ReportItems/ReportItemSet.php");

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
    var $private;
    var $deleted;
    var $blobItem;
    var $blobSubItem;
    var $value;
    var $reportCallback;
    var $attributes;
    
    // Creates a new AbstractReportItem
    function AbstractReportItem(){
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
        $this->private = false;
        $this->deleted = false;
        $this->reportCallback = new ReportItemCallback($this);
    }
    
    function setId($id){
        $this->id = $id;
    }
    
    function setAttribute($key, $value){
        $this->attributes[$key] = $value;
    }
    
    // Sets the parent ReportSection or ReportItem for this AbstractReportItem
    function setParent($parent){
        $this->parent = $parent;
    }

    function getId(){
        return $this->id;
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
    
    // Sets the Product ID of this AbstractRepotItem
    function setProductId($id){
        $this->productId = $id;
    }
    
    // Sets whether or not this item should be treated as private or not
    function setPrivate($private){
        $this->private = $private;
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
        $this->blobItem = $item;
    }
    
    // Sets the Blob Sub-Item of this AbstractReportItem
    function setBlobSubItem($item){
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
        if($this->getReport()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        return 1;
    }
    
    function getPostId(){
        $parent = $this->getParent();
        if($this instanceof AbstractReportItem){
            $postId = str_replace("\"", "", str_replace("]", "", str_replace("[", "", str_replace("'", "", "_{$this->id}"))));
        }
        if(!($parent instanceof AbstractReportSection)){
            $postId = @$parent->getPostId()."_person{$this->personId}_project{$this->projectId}_milestone{$this->milestoneId}".$postId;
        }
        else{
            $postId = str_replace(" ", "", $parent->name).$postId;
        }
        return $postId;
    }
    
    // By default only calls setBlobValue using the postdata, but can
    // be overridden to do some proccessing before hand, or handle uploads etc.
    function save(){
        if(isset($_POST[$this->getPostId()])){
            if(!isset($_POST[$this->getPostId().'_ignoreConflict']) ||
               $_POST[$this->getPostId().'_ignoreConflict'] != "true"){
                if(isset($_POST['oldData'][$this->getPostId()]) &&
                   trim($_POST['oldData'][$this->getPostId()]) == trim($_POST[$this->getPostId()])){
                   // Don't save, but also don't display an error
                   return array();
                }
                else if(isset($_POST['oldData'][$this->getPostId()]) && 
                   trim($_POST['oldData'][$this->getPostId()]) != trim($this->getBlobValue()) &&
                   trim($_POST[$this->getPostId()]) != trim($this->getBlobValue())){
                    if(trim($_POST['oldData'][$this->getPostId()]) != trim($_POST[$this->getPostId()])){
                        // Conflict in blob values
                        return array(array('postId' => $this->getPostId(), 
                                           'value' => trim($this->getBlobValue()),
                                           'postValue' => trim($_POST[$this->getPostId()]),
                                           'oldValue' => trim($_POST['oldData'][$this->getPostId()]),
                                           'diff' => @htmlDiffNL(str_replace("\n", "\n ", $this->getBlobValue()), str_replace("\n", "\n ", $_POST[$this->getPostId()]))));
                    }
                }
            }
            $this->setBlobValue($_POST[$this->getPostId()]);
        }
        return array();
    }

    // Gets the Blob of this item
    function getBlobValue(){
        $report = $this->getReport();
        $section = $this->getSection();
        // !!! 
        //I think there is a bug here. I think ReportBlob should really be given $this-personId, instead of ID of person who created the report 
        // This needs to be checked
        // !!!
        //$blob = new ReportBlob($this->blobType, $this->getReport()->year, $this->getReport()->person->getId(), $this->projectId);
        $blob = new ReportBlob($this->blobType, $this->getReport()->year, $this->personId, $this->projectId);
	    $blob_address = ReportBlob::create_address($report->reportType, $section->sec, $this->blobItem, $this->blobSubItem);
	    $blob->load($blob_address);
	    $blob_data = $blob->getData();
        switch($this->blobType){
            default:
            case BLOB_TEXT:
            case BLOB_WIKI:
            case BLOB_HTML:
                $blob_data = str_replace("\00", "", $blob_data);
                $blob_data = str_replace("", "", $blob_data);
                $blob_data = str_replace("", "", $blob_data);
                $blob_data = str_replace("", "", $blob_data);
                $blob_data = str_replace("", "fi", $blob_data);
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
    
    // Sets the Blob value for this item
    function setBlobValue($value){
        $report = $this->getReport();
        $section = $this->getSection();
        $blob = new ReportBlob($this->blobType, $this->getReport()->year, $this->getReport()->person->getId(), $this->projectId);
	    $blob_address = ReportBlob::create_address($report->reportType, $section->sec, $this->blobItem, $this->blobSubItem);
	    $blob->load($blob_address);
	    $blob_data = $blob->getData();
	    switch($this->blobType){
            default:
            case BLOB_TEXT:
            case BLOB_WIKI:
            case BLOB_HTML:
                $value = str_replace("\00", "", $value); // Fixes problem with the xml backup putting in random null escape sequences
                if(is_string($value)){
                    $blob->store(trim($value), $blob_address);
                }
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
                $value = str_replace("\00", "", $value); // Fixes problem with the xml backup putting in random null escape sequences
                eval("\$blob_data$accessStr = \$value;");
                $blob->store($blob_data, $blob_address);
                break;
            case BLOB_EXCEL:
                if(mb_check_encoding($value, 'UTF-8')){
                    $value = utf8_decode($value);
                }
                $blob->store($value, $blob_address);
	            $blob->load($blob_address);
	            $this->addWorksWithRelation($blob->getData(), false);
	            break;
	        case BLOB_RAW:
	            $blob->store(utf8_decode($value), $blob_address);
	            $blob->load($blob_address);
	            break;
        }
	    
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
        $matches = array();
        preg_match_all('/{\$(.+?)}/', $cdata, $matches);
        
        foreach($matches[1] as $k => $m){
            if(isset(ReportItemCallback::$callbacks[$m])){
                $v = str_replace("$", "\\$", call_user_func(array($this->reportCallback, ReportItemCallback::$callbacks[$m])));
                $regex = '/{\$'.$m.'}/';
                $cdata = preg_replace($regex, $v, $cdata);
            }
        }
        
        return $cdata;
    }
    
}

?>
