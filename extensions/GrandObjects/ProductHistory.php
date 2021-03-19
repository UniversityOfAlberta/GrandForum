<?php

class ProductHistory extends BackboneModel {
    
    static $cache = array();
    
    var $id;
    var $user_id;
    var $year;
    var $type;
    var $value;
    var $created;
    var $updated;
    
    static function newFromId($id){
        if(!isset(self::$cache[$id])){
            $data = DBFunctions::select(array('grand_product_histories'),
                                        array('*'),
                                        array('id' => EQ($id)));
            $productHistory = new ProductHistory($data);
            self::$cache[$id] = $productHistory;   
        }
        return self::$cache[$id];
    }
    
    function ProductHistory($data=array()){
        if(count($data) > 0){
            $me = Person::newFromWgUser();
            $row = $data[0];
            if($row['user_id'] == $me->getId() || $me->isRoleAtLeast(CHAIR) || $me->isRoleAtLeast(EA)){
                $this->id = $row['id'];
                $this->user_id = $row['user_id'];
                $this->year = $row['year'];
                $this->type = ucwords($row['type']);
                $this->value = $row['value'];
                $this->created = $row['created'];
                $this->updated = $row['updated'];
            }
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getPerson(){
        return Person::newFromId($this->user_id);
    }
    
    function getYear(){
        return $this->year;
    }
    
    function getType(){
        return $this->type;
    }
    
    function getValue(){
        return $this->value;
    }
    
    function getCreated(){
        return $this->created;
    }
    
    function getUpdated(){
        return $this->updated;
    }
    
    function toArray(){
        return array(
            'id' => $this->id,
            'user_id' => $this->user_id,
            'year' => $this->year,
            'type' => $this->type,
            'value' => $this->value,
            'created' => $this->created,
            'updated' => $this->updated
        );
    }
    
    function create(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            if(count(DBFunctions::select(array('grand_product_histories'),
                                         array('id'),
                                         array('user_id' => $this->user_id,
                                               'year' => $this->year,
                                               'type' => $this->type))) > 0){
                return "Product History with 'user id', 'year', 'type' already exists";
            }
            DBFunctions::insert('grand_product_histories',
                                array('user_id' => $this->user_id,
                                      'year' => $this->year,
                                      'type' => $this->type,
                                      'value' => $this->value,
                                      'created' => EQ(COL('CURRENT_TIMESTAMP')),
                                      'updated' => EQ(COL('CURRENT_TIMESTAMP'))));
            $this->id = DBFunctions::insertId();
        }
        return $this;
    }
    
    function update(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            if(count(DBFunctions::select(array('grand_product_histories'),
                                         array('id'),
                                         array('id' => NEQ($this->id),
                                               'user_id' => $this->user_id,
                                               'year' => $this->year,
                                               'type' => $this->type))) > 0){
                return "Product History with 'user id', 'year', 'type' already exists";
            }
            DBFunctions::update('grand_product_histories',
                                array('user_id' => $this->user_id,
                                      'year' => $this->year,
                                      'type' => $this->type,
                                      'value' => $this->value,
                                      'updated' => EQ(COL('CURRENT_TIMESTAMP'))),
                                array('id' => EQ($this->id)));
        }
        return $this;
    }
    
    function delete(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            DBFunctions::delete('grand_product_histories',
                                array('id' => EQ($this->id)));
            $this->id = 0;
        }
        return $this;
    }
    
    function exists(){
        return ($this->id != 0);
    }
    
    function getCacheId(){
        return 'productHistory'.$this->getId();
    }
    
}

?>
