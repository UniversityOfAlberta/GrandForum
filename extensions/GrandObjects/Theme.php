<?php

class Theme {
    
    static $cache = array();
    
    var $id;
    var $acronym;
    var $name;
    var $description;
    var $phase;
    
    static function newFromId($id){
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        $data = DBFunctions::select(array('grand_themes'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $theme = new Theme($data);
        self::$cache[$id] = &$theme;
        return self::$cache[$id];
    }
    
    static function newFromName($name){
        if(isset(self::$cache[$name])){
            return self::$cache[$name];
        }
        $data = DBFunctions::select(array('grand_themes'),
                                    array('*'),
                                    array('acronym' => EQ($name)));
        $theme = new Theme($data);
        self::$cache[$name] = &$theme;
        return self::$cache[$name];
    }
    
    static function getAllThemes($phase="%"){
        $data = DBFunctions::select(array('grand_themes'),
                                    array('*'),
                                    array('phase' => LIKE($phase)));
        $themes = array();
        foreach($data as $row){
            $themes[] = new Theme(array($row));
        }
        return $themes;
    }
    
    function Theme($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->acronym = $data[0]['acronym'];
            $this->name = $data[0]['name'];
            $this->description = $data[0]['description'];
            $this->phase = $data[0]['phase'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getAcronym(){
        return $this->acronym;
    }
    
    function getName(){
        return $this->name;
    }
    
    function getDescription(){
        return $this->description;
    }
    
    function getPhase(){
        return $this->phase;
    }

}

?>
