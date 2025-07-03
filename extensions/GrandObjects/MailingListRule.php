<?php

/**
 * @package GrandObjects
 */

class MailingListRule extends BackboneModel {

    static $cache = array();
    
    var $id;
    var $type;
    var $listId;
    var $value;

    static function newFromId($id){
        if(isset($cache[$id])){
            return $cache[$id];
        }
        $data = DBFunctions::select(array('wikidev_projects_rules'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $rule = new MailingListRule($data);
        $cache[$id] = &$rule;
        return $rule;
    }

    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->type = $data[0]['type'];
            $this->listId = $data[0]['project_id'];
            $this->value = $data[0]['value'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getType(){
        return $this->type;
    }

    function getList(){
        return MailingList::newFromId($this->listId);
    }
    
    function getValue(){
        return $this->value;
    }
    
    function create(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(MANAGER)){
            DBFunctions::begin();
            $status = DBFunctions::insert('wikidev_projects_rules',
                                          array('type' => $this->type,
                                                'project_id' => $this->listId,
                                                'value' => $this->value));
            $data = DBFunctions::select(array('wikidev_projects_rules'),
                                        array('id'),
                                        array('type' => EQ($this->type),
                                              'project_id' => EQ($this->listId),
                                              'value' => EQ($this->value)),
                                        array('id' => 'DESC'),
                                        array(1));
            if(count($data) > 0){
                $this->id = $data[0]['id'];
            }
            DBFunctions::commit();
            return $status;
        }
        return false;
    }
    
    function update(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(MANAGER)){
           $status = DBFunctions::update('wikidev_projects_rules',
                                         array('type' => $this->type,
                                               'project_id' => $this->listId,
                                               'value' => $this->value),
                                         array('id' => EQ($this->id)));
            return $status;
        }
        return false;
    }
    
    function delete(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(MANAGER)){
           $status = DBFunctions::delete('wikidev_projects_rules',
                                         array('id' => EQ($this->id)));
            return $status;
        }
        return false;
    }
    
    function toArray(){
        return array('id' => $this->getId(),
                     'type' => $this->getType(),
                     'listId' => $this->listId,
                     'value' => $this->value);
    }
    
    function exists(){
        return true;
    }
    
    function getCacheId(){
        
    }
} 

?>
