<?php

class Theme {
    
    static $cache = array();
    
    var $id;
    var $acronym;
    var $name;
    var $description;
    var $phase;
    var $leader = null;
    var $coleader = null;
    
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
    
    function getUrl(){
        global $wgServer, $wgScriptPath;
        return "{$wgServer}{$wgScriptPath}/index.php/GRAND:{$this->getAcronym()} - {$this->getName()}";
    }
    
    function getDescription(){
        return $this->description;
    }
    
    function getPhase(){
        return $this->phase;
    }
    
    function getLeader(){
        if($this->leader == null){
            $data = DBFunctions::select(array("grand_theme_leaders"),
                                        array("user_id"),
                                        array("theme" => $this->getId(),
                                              "co_lead" => EQ(0),
                                              "end_date" => EQ("0000-00-00 00:00:00")));
            if(count($data) > 0){
                $this->leader = Person::newFromId($data[0]['user_id']);
            }
        }
        return $this->leader;
    }
    
    function getCoLeader(){
        if($this->coleader == null){
            $data = DBFunctions::select(array("grand_theme_leaders"),
                                        array("user_id"),
                                        array("theme" => $this->getId(),
                                              "co_lead" => EQ(1),
                                              "end_date" => EQ("0000-00-00 00:00:00")));
            if(count($data) > 0){
                $this->coleader = Person::newFromId($data[0]['user_id']);
            }
        }
        return $this->coleader;
    }

}

?>
