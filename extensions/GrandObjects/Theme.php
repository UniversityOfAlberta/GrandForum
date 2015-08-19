<?php

/**
 * @package GrandObjects
 */

class Theme {
    
    static $cache = array();
    
    var $id;
    var $acronym = "";
    var $name = "";
    var $description;
    var $phase;
    var $color;
    var $leader = null;
    
    /**
     * Returns a new Theme from the given Id
     * @param int @id The id of the Theme
     * @return Theme the new Theme
     */
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
    
    /**
     * Returns a new Theme from the given Name
     * @param string $name The name of the Theme
     * @return Theme the new Theme
     */
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
    
    /**
     * Returns all the Themes from the database
     * @param string $phase which phase themes to select ("%" to select all)
     * @return array An array of Themes
     */
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
            $this->color = $data[0]['color'];
        }
    }
    
    /**
     * Returns this Theme's id
     * @return int This Theme's id
     */
    function getId(){
        return $this->id;
    }
    
    /**
     * Returns this Theme's acronym
     * @return int This Theme's acronym
     */
    function getAcronym(){
        return $this->acronym;
    }
    
    /**
     * Returns this Theme's name
     * @return This Theme's name
     */
    function getName(){
        return $this->name;
    }
    
    /**
     * Returns this Theme's Description
     * @return string This Theme's Description
     */
    function getDescription(){
        return $this->description;
    }
    
    /**
     * Returns this Theme's phase
     * @return int This Theme's phase
     */
    function getPhase(){
        return $this->phase;
    }
    
    /**
     * Returns this Theme's Url
     * @return This Theme's url
     */
    function getUrl(){
        global $wgServer, $wgScriptPath, $config;
        return "{$wgServer}{$wgScriptPath}/index.php/{$this->getAcronym()}:Information";
    }
    
    /**
     * Returns this Theme's color
     * @return string This Theme's color
     */
    function getColor(){
        return $this->color;
    }
    
    /*
     * Returns all of the leaders regardless of their type
     * @return array An array of all leaders
     */
    function getLeaders(){
        $leaders = array();
        $data = DBFunctions::select(array("grand_theme_leaders"),
                                    array("user_id"),
                                    array("theme" => $this->getId(),
                                          "coordinator" => EQ("False"),
                                          "end_date" => EQ("0000-00-00 00:00:00")));
        if(count($data) > 0){
            foreach($data as $row){
                $leader = Person::newFromId($row['user_id']);
                $leaders[$leader->getReversedName()] = $leader;
            }
        }
        return $leaders;
    }
    
    /*
     * Returns all of the leaders regardless of their type
     * @return array An array of all leaders
     */
    function getCoordinators(){
        $leaders = array();
        $data = DBFunctions::select(array("grand_theme_leaders"),
                                    array("user_id"),
                                    array("theme" => $this->getId(),
                                          "coordinator" => EQ("True"),
                                          "end_date" => EQ("0000-00-00 00:00:00")));
        if(count($data) > 0){
            foreach($data as $row){
                $leader = Person::newFromId($row['user_id']);
                $leaders[$leader->getReversedName()] = $leader;
            }
        }
        return $leaders;
    }
    
    /*
     * Returns all of the Projects in this Theme
     * @return array The Projects in this Theme
     */
    function getProjects(){
        $return = array();
        $projects = Project::getAllProjects();
        foreach($projects as $project){
            if($project->getChallenge()->getAcronym() == $this->getAcronym()){
                $return[$project->getName()] = $project;
            }
        }
        ksort($return);
        return $return;
    }

}

?>
