<?php

/**
 * @package GrandObjects
 */

class Bibliography extends BackboneModel{

    static $cache = array();

    var $id = null;
    var $title = "";
    var $description = "";
    var $person = null;
    var $products = array();
    
    /**
     * Returns a new Bibliography from the given id
     * @param integer $id The id of the Bibliography
     * @return Bibliography The Bibliography with the given id
     */
    static function newFromId($id){
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        $me = Person::newFromWgUser();
        
        $data = DBFunctions::select(array('grand_bibliography'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $bib = new Bibliography($data);
        self::$cache[$bib->id] = &$bib;
        return $bib;
    }
 
    function Bibliography($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->title = $data[0]['title'];
            $this->description = $data[0]['description'];
            $this->person = Person::newFromId($data[0]['person_id']);
            $this->products = unserialize($data[0]['products']);
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getTitle(){
        return $this->title;
    }
    
    function getDescription(){
        return $this->description;
    }
    
    function getPerson(){
        return $this->person;
    }
    
    function getProducts(){
        $products = array();
        foreach($this->products as $product){
            $products[] = Product::newFromId($product);
        }
        return $products;
    }
    
    function create(){
        DBFunctions::insert('grand_bibliography',
                            array('title' => $this->title,
                                  'description' => $this->description,
                                  'person_id' => $this->getPerson()->getId(),
                                  'products' => serialize($this->products)));
        $this->id = DBFunctions::insertId();
        return $this;
    }
    
    function update(){
        DBFunctions::update('grand_bibliography',
                            array('title' => $this->title,
                                  'description' => $this->description,
                                  'products' => serialize($this->products)),
                            array('id' => EQ($this->getId())));
        return $this;
    }
    
    function delete(){
        DBFunctions::delete('grand_bibliography',
                            array('id' => EQ($this->getId())));
        $this->id = null;
        return $this;
    }
    
    function toArray(){
        $person = $this->getPerson();
        $data = array(
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'person' => array('id' => $person->getId(),
                              'name' => $person->getNameForProduct(),
                              'fullname' => $person->getNameForForms(),
                              'url' => $person->getUrl()),
            'products' => $this->products
        );
        return $data;
    }
    
    function exists(){
        return ($this->id != "" && $this->id != 0);
    }
    
    function getCacheId(){
        return 'bibliography'.$this->getId();
    }
    
}
