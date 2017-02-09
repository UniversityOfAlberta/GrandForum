<?php

/**
 * @package GrandObjects
 */

class Bibliography extends BackboneModel{

    static $cache = array();

    var $id = null;
    var $person = null;
    var $products = array();
    
    /**
     * Returns a new Paper from the given id
     * @param integer $id The id of the Paper
     * @return Paper The Paper with the given id
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
        self::$cache[$paper->id] = &$bib;
        return $bib;
    }
 
    function Bibliography($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->person = Person::newFromId($data[0]['person_id']);
            $products = unserialize($data[0]['products']);
            foreach($products as $product){
                $this->products[] = Product::newFromId($product);
            }
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getPerson(){
        return $this->person;
    }
    
    function getProducts(){
        return $this->products;
    }
    
    function create(){
        $products = array();
        foreach($this->getProducts() as $product){
            $products[] = $product->getId();
        }
        DBFunctions::insert('grand_bibliography',
                            array('person' => $this->getPerson()->getId(),
                                  'products' => serialize($products)));
        return $this;
    }
    
    function update(){
        $products = array();
        foreach($this->getProducts() as $product){
            $products[] = $product->getId();
        }
        DBFunctions::update('grand_bibliography',
                            array('products' => serialize($products)),
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
        $products = array();
        foreach($this->getProducts() as $product){
            $products[] = $product->toArray();
        }
        $data = array(
            'id' => $this->getId(),
            'person' => array('id' => $person->getId(),
                              'name' => $person->getNameForProduct(),
                              'fullname' => $person->getNameForForms(),
                              'url' => $person->getUrl()),
            'products' => $products
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
