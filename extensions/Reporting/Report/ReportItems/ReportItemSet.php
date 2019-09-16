<?php

abstract class ReportItemSet extends AbstractReportItem{

    protected $items;
    var $blobIndex;
    var $count;
    var $iteration;
    var $cached;
    // The following are for lazy loading the data
    var $parser;
    var $section;
    var $node;
    var $data;

    // Creates a new ReportItemSet
    function ReportItemSet(){
        $this->items = null;
        $this->blobIndex = "";
        $this->cached = null;
    }
    
    // Creates an empty tuple, with all values of this object
    function createTuple(){
        $tuple = array('milestone_id' => $this->milestoneId,
                       'project_id' => $this->projectId,
                       'person_id' => $this->personId,
                       'product_id' => $this->productId,
                       'misc' => array(),
                       'extra' => $this->extra,
                       'item_id' => null);
        return $tuple;
    }
    
    function getCachedData(){
        if($this->cached == null){
            $this->cached = $this->getData();
        }
        return $this->cached;
    }
    
    // This function must return an array of arrays in the form of array(array('project_id','person_id','milestone_id'))
    abstract function getData();

    function getItems(){
        if($this->items == null){
            $this->items = array();
            $this->parser->parseReportItemSet($this->section, $this->node, $this->data, false, $this);
        }
        return $this->items;
    }
    
    function getLimit(){
        if($this->getReport()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $limit = 0;
        foreach($this->getItems() as $item){
            if($item instanceof ReportItemSet){
                if($item->getLimit() > 0){
                    $limit += $item->getLimit();
                }
            }
            else if ($item instanceof TextareaReportItem){
                if($item->getLimit() > 0){
                    $limit += $item->getLimit();
                }
            }
        }
        return $limit;
    }
    
    function getNChars(){
        if($this->getReport()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nChars = 0;
        foreach($this->getItems() as $item){
            if($item instanceof ReportItemSet){
                if($item->getLimit() > 0){
                    $nChars += $item->getNChars();
                }
            }
            else if ($item instanceof TextareaReportItem){
                if($item->getLimit() > 0){
                    $nChars += $item->getNChars();
                }
            }
        }
        return $nChars;
    }
    
    function getActualNChars(){
        if($this->getReport()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nChars = 0;
        foreach($this->getItems() as $item){
            if($item instanceof ReportItemSet){
                if($item->getLimit() > 0){
                    $nChars += $item->getActualNChars();
                }
            }
            else if ($item instanceof TextareaReportItem){
                if($item->getLimit() > 0){
                    $nChars += $item->getActualNChars();
                }
            }
        }
        return $nChars;
    }
    
    function getExceedingFields(){
        if($this->getReport()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nFields = 0;
        foreach($this->getItems() as $item){
            if($item instanceof ReportItemSet){
                if($item->getLimit() > 0){
                    $nFields += $item->getExceedingFields();
                }
            }
            else if ($item instanceof TextareaReportItem){
                if($item->getLimit() > 0){
                    if($item->getActualNChars() > $item->getLimit()){
                        $nFields++;
                    }
                }
            }
        }
        return $nFields;
    }
    
    function getEmptyFields(){
        if($this->getReport()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nFields = 0;
        foreach($this->getItems() as $item){
            if($item instanceof ReportItemSet){
                if($item->getLimit() > 0){
                    $nFields += $item->getEmptyFields();
                }
            }
            else if ($item instanceof TextareaReportItem){
                if($item->getLimit() > 0){
                    if($item->getActualNChars() == 0){
                        $nFields++;
                    }
                }
            }
        }
        return $nFields;
    }
    
    function setBlobIndex($blobIndex){
        $this->blobIndex = $blobIndex;
    }

    function addReportItem($item, $position=null){
        $item->setParent($this);
        if($position == null){
            $this->items[] = $item;
        }
        else{
            array_splice($this->items, ($position) + ($this->count*$this->iteration) + ($this->iteration), 0, array($item));
        }
        $item->setPersonId($this->personId);
    }
    
    // Deleted the given ReportItem from this ReportItemSet
    function deleteReportItem($item){
        foreach($this->getItems() as $key => $it){
            if($item->id == $it->id){
                unset($this->items[$key]);
                return;
            }
        }
    }
    
    // Returns the ReportItem with the given id, or null if it does not exist
    function getReportItemById($itemId){
        if($this->items == null){
            $this->items = array();
        }
        foreach($this->items as $item){
            if($item->id == $itemId){
                return $item;
            }
        }
        return null;
    }
    
    function save(){
        $errors = array();
        foreach($this->getItems() as $item){
            $errors = array_merge($errors, $item->save());
        }
        return $errors;
    }
    
    function getBlobValue(){
        $values = array();
        foreach($this->getItems() as $item){
            $id = $item->id;
            $extraId = $item->getExtraIndex();
            $secondId = "{$item->personId}_{$item->projectId}_{$item->milestoneId}_extra{$extraId}";
            if($item->id == ""){
                $id = "none";
            }
            $values[$this->id][$secondId][$id] = $item->getBlobValue();
        }
        return $values;
    }
    
    function setBlobValue($values){
        foreach($this->getItems() as $item){
            $id = $item->id;
            $extraId = $item->getExtraIndex();
            $secondId = "{$item->personId}_{$item->projectId}_{$item->milestoneId}_extra{$extraId}";
            if($item->id == ""){
                $id = "none";
            }
            if(isset($values[$this->id][$secondId][$id])){
                $item->setBlobValue($values[$this->id][$secondId][$id]);
            }
        }
    }
    
    // Returns the number of completed values (usually 1, or 0)
    function getNComplete(){
        if($this->getReport()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nComplete = 0;
        foreach($this->getItems() as $item){
            $nComplete += $item->getNComplete();
        }
        return $nComplete;
    }
    
    // Returns the number of fields which are associated with this AbstractReportItem (usually 1)
    function getNFields(){
        if($this->getReport()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nFields = 0;
        foreach($this->getItems() as $item){
            $nFields += $item->getNFields();
        }
        return $nFields;
    }
    
    function getNTextareas(){
        if($this->getReport()->topProjectOnly && $this->private && $this->projectId == 0){
            return 0;
        }
        $nTextareas = 0;
        foreach($this->getItems() as $item){
            if($item instanceof ReportItemSet){
                $nTextareas += $item->getNTextareas();
            }
            else if($item instanceof TextareaReportItem){
                $nTextareas += 1;
            }
        }
        return $nTextareas;
    }
    
    function renderItems(){
        foreach($this->getItems() as $item){
            if(!$this->getReport()->topProjectOnly || ($this->getReport()->topProjectOnly && !$item->private)){
                if(!$item->deleted){
                    $item->render();
                }
            }
        }
    }
    
    function renderItemsForPDF(){
        foreach($this->getItems() as $item){
            if(!$this->getReport()->topProjectOnly || ($this->getReport()->topProjectOnly && !$item->private)){
                if(!$item->deleted){
                    $item->renderForPDF();
                }
            }
        }
    }

    function render(){
        $this->renderItems();
    }
    
    function renderForPDF(){
        $this->renderItemsForPDF();
    }
}

?>
