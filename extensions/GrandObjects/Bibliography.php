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
    var $editors = array();
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
    
    /**
     * Returns all of the Bibliographies
     * @return array All of the Bibliographies
     */
    static function getAllBibliographies(){
        $data = DBFunctions::select(array('grand_bibliography'),
                                    array('id'));
        $bibs = array();
        foreach($data as $row){
            $bibs[] = Bibliography::newFromId($row['id']);
        }
        return $bibs;
    }
    
    /**
     * Returns how many Bibliographies there are
     * @return int How many Bibliographies there are
     */
    static function count(){
        $data = DBFunctions::select(array('grand_bibliography'),
                                    array('id'));
        return count($data);
    }
 
    function Bibliography($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->title = $data[0]['title'];
            $this->description = $data[0]['description'];
            $this->person = Person::newFromId($data[0]['person_id']);
            $this->editors = unserialize($data[0]['editors']);
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
    
    function getUrl(){
        global $wgServer, $wgScriptPath;
        return "$wgServer$wgScriptPath/index.php/Special:BibliographyPage#/{$this->getId()}";
    }
    
    function getPerson(){
        return $this->person;
    }
    
    function getEditors(){
        $editors = array();
        foreach($this->editors as $editor){
            $editors[] = Person::newFromId($editor);
        }
        return $editors;
    }
    
    function getProducts(){
        $products = array();
        foreach($this->products as $product){
            $products[] = Product::newFromId($product);
        }
        return $products;
    }
    
    function create(){
        foreach($this->editors as $key => $editor){
            if(is_object($editor)){
                $this->editors[$key] = $editor->id;
            }
        }
        DBFunctions::insert('grand_bibliography',
                            array('title' => $this->title,
                                  'description' => $this->description,
                                  'person_id' => $this->getPerson()->getId(),
                                  'editors' => serialize($this->editors),
                                  'products' => serialize($this->products)));
        $this->id = DBFunctions::insertId();
        return $this;
    }
    
    function update(){
        foreach($this->editors as $key => $editor){
            if(is_object($editor)){
                $this->editors[$key] = $editor->id;
            }
        }
        DBFunctions::update('grand_bibliography',
                            array('title' => $this->title,
                                  'description' => $this->description,
                                  'editors' => serialize($this->editors),
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
        $editors = array();
        foreach($this->getEditors() as $editor){
            $editors[] = array('id' => $editor->getId(),
                               'name' => $editor->getNameForProduct(),
                               'fullname' => $editor->getNameForForms(),
                               'url' => $editor->getUrl());
        }
        $data = array(
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'url' => $this->getUrl(),
            'person' => array('id' => $person->getId(),
                              'name' => $person->getNameForProduct(),
                              'fullname' => $person->getNameForForms(),
                              'url' => $person->getUrl()),
            'editors' => $editors,
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
