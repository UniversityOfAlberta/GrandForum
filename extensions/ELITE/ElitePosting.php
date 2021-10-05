<?php

/**
 * @package GrandObjects
 */

class ElitePosting extends Posting {
    
    static $dbTable = 'grand_elite_postings';

    var $type;
    var $extra = array();
    var $comments;
    
    function ElitePosting($data){
        if(count($data) > 0){
            $row = $data[0];
            parent::posting($data);
            $this->type = $row['type'];
            $this->comments = $row['comments'];
            $this->extra = json_decode($row['extra'], true);
            if(!is_array($this->extra)){
                $this->extra = array();
            }
        }
    }
    
    static function isAllowedToCreate(){
        $me = Person::newFromWgUser();
        return ($me->isRoleAtLeast(EXTERNAL));
    }
    
    function isAllowedToView(){
        $me = Person::newFromWgUser();
        if($this->getVisibility() == "Accepted" || $this->getVisibility() == "Publish"){
            // Posting is Public
            return true;
        }
        if($me->getId() == $this->getUserId() ||  
           $me->isRoleAtLeast(STAFF)){
            // Posting was created by the logged in user (or is Staff)
            return true;
        }
    }
    
    function getType(){
        return $this->type;
    }
    
    /**
     * Returns the url of this Posting's page
     * @return string The url of this Posting's page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath;
        $class = get_class($this);
        if(!isset($_GET['embed']) || $_GET['embed'] == 'false'){
            return "{$wgServer}{$wgScriptPath}/index.php/Special:{$class}Page?page=".strtolower($this->getType())."#/{$this->getId()}";
        }
        return "{$wgServer}{$wgScriptPath}/index.php/Special:{$class}Page?page=".strtolower($this->getType())."&embed#/{$this->getId()}";
    }
    
    function getExtra($field=null){
        if($field == null){
            return $this->extra;
        }
        return @$this->extra[$field];
    }
    
    function getComments(){
        return ($this->isAllowedToEdit()) ? $this->comments : "";
    }
    
    function toSimpleArray(){
        $json = parent::toArray();
        $json['extra'] = $this->getExtra();
        return $json;
    }
    
    function toArray(){
        $json = parent::toArray();
        $json['type'] = $this->getType();
        $json['extra'] = $this->getExtra();
        $json['comments'] = $this->getComments();
        return $json;
    }
    
    function create(){
        $status = parent::create();
        if($status){
            $status = DBFunctions::update(self::$dbTable,
                                          array('type' => $this->type,
                                                'extra' => json_encode($this->extra),
                                                'comments' => $this->comments),
                                          array('id' => $this->id));
        }
        return $status;
    }
    
    function update(){
        $status = parent::update();
        if($status){
            $status = DBFunctions::update(self::$dbTable,
                                          array('type' => $this->type,
                                                'extra' => json_encode($this->extra),
                                                'comments' => $this->comments),
                                          array('id' => $this->id));
        }
        return $status;
    }
}

?>
