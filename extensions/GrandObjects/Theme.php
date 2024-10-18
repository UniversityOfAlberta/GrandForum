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
    var $resources;
    var $wiki;
    var $phase;
    var $color;
    
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
    
    static function getAllowedThemes(){
        $return = array();
        $themes = self::getAllThemes();
        foreach($themes as $theme){
            if($theme->phase != 0){
                $return[] = $theme->acronym;
            }
        }
        return $return;
    }
    
    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->acronym = $data[0]['acronym'];
            $this->name = $data[0]['name'];
            $this->description = $data[0]['description'];
            $this->resources = $data[0]['resources'];
            $this->wiki = $data[0]['wiki'];
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
     * Returns this Theme's Resources
     * @return string This Theme's Resources
     */
    function getResources(){
        return $this->resources;
    }
    
    /**
     * Returns this Theme's Wiki
     * @return string This Theme's Wiki
     */
    function getWiki(){
        return $this->wiki;
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
        return "{$wgServer}{$wgScriptPath}/index.php/{$this->getAcronym()}:Main";
    }
    
    /**
     * Returns this Theme's color
     * @return string This Theme's color
     */
    function getColor(){
        return ($this->color != "") ? $this->color : "#888888";
    }
    
    function getCreated(){
        $start = "9999";
        foreach($this->getProjects() as $project){
            $start = min($start, $project->getCreated());
        }
        return $start;
    }
    
    function isDeleted(){
        $isDeleted = true;
        foreach($this->getProjects() as $project){
            $isDeleted = ($isDeleted && $project->isDeleted());
        }
        return $isDeleted;
    }
    
    function getDeleted(){
        $deleted = "0000";
        foreach($this->getProjects() as $project){
            $deleted = max($deleted, $project->getDeleted());
        }
        return $deleted;
    }
    
    /**
     * Returns all of the leaders regardless of their type
     * @return array An array of all leaders
     */
    function getLeaders(){
        $leaders = array();
        $data = DBFunctions::execSQL("SELECT user_id
                                      FROM grand_theme_leaders
                                      WHERE theme = '{$this->getId()}'
                                      AND coordinator = 'False'
                                      AND (end_date = '0000-00-00 00:00:00' OR
                                           end_date > CURRENT_TIMESTAMP)");
        if(count($data) > 0){
            foreach($data as $row){
                $leader = Person::newFromId($row['user_id']);
                $leaders[$leader->getReversedName()] = $leader;
            }
        }
        return $leaders;
    }
    
    /**
     * Returns all of the leaders regardless of their type
     * @return array An array of all leaders
     */
    function getCoordinators(){
        $leaders = array();
        $data = DBFunctions::execSQL("SELECT user_id
                                      FROM grand_theme_leaders
                                      WHERE theme = '{$this->getId()}'
                                      AND coordinator = 'True'
                                      AND (end_date = '0000-00-00 00:00:00' OR
                                           end_date > CURRENT_TIMESTAMP)");
        if(count($data) > 0){
            foreach($data as $row){
                $leader = Person::newFromId($row['user_id']);
                $leaders[$leader->getReversedName()] = $leader;
            }
        }
        return $leaders;
    }
    
    function getPapers($category="all", $startRange = false, $endRange = false){
        $papers = array();
        foreach($this->getProjects() as $project){
            foreach($project->getPapers($category, $startRange, $endRange) as $paper){
                $papers[$paper->getId()] = $paper;
            }
        }
        /* TODO: Don't do this yet, maybe re-enable it later if needed
        $data = DBFunctions::execSQL("SELECT id
                                      FROM grand_products
                                      WHERE data LIKE '%\"theme\"%:\"{$this->acronym}%'
                                         OR data LIKE '%\"theme\"%:\"All Themes%'");
        foreach($data as $row){
            $paper = Product::newFromId($row['id']);
            $papers[$paper->getId()] = $paper;
        }*/
        return $papers;
    }
    
    function getMultimedia(){
        $multimedia = array();
        foreach($this->getProjects() as $project){
            foreach($project->getMultimedia() as $mult){
                $multimedia[$mult->getId()] = $mult;
            }
        }
        return $multimedia;
    }
    
    function getContributions(){
        $contributions = array();
        foreach($this->getProjects() as $project){
            foreach($project->getContributions() as $contribution){
                $contributions[$contribution->getId()] = $contribution;
            }
        }
        return $contributions;
    }
    
    /**
     * Returns all of the Projects in this Theme
     * @return array The Projects in this Theme
     */
    function getProjects($all=false){
        $return = array();
        $projects = Project::getAllProjects();
        foreach($projects as $project){
            if($all || $project->getStatus() == "Active"){
                foreach($project->getChallenges() as $challenge){
                    if($challenge->getAcronym() == $this->getAcronym()){
                        $return[$project->getName()] = $project;
                        break;
                    }
                }
            }
        }
        knatsort($projects);
        return $return;
    }
    
    function getSubProjects(){
        $subProjects = array();
        foreach($this->getProjects() as $project){
            foreach($project->getSubProjects() as $sub){
                $subProjects[$sub->getId()] = $sub;
            }
        }
        return $subProjects;
    }
    
    function getAllPeople($filter = null){
        $people = array();
        foreach($this->getProjects() as $project){
            foreach($project->getAllPeople($filter) as $person){
                $people[$person->getName()] = $person;
            }
        }
        return $people;
    }
    
    function getAllPeopleDuring($filter = null, $startRange = false, $endRange = false, $includeManager=false){
        $people = array();
        foreach($this->getProjects() as $project){
            foreach($project->getAllPeopleDuring($filter, $startRange, $endRange, $includeManager) as $person){
                $people[$person->getName()] = $person;
            }
        }
        return $people;
    }
    
    /**
     * Returns whether or not the current user can edit this Theme
     * @return boolean Whether or not the current user can edit this Theme
     */
    function userCanEdit(){
        $me = Person::newFromWgUser();
        return ($me->isThemeLeaderOf($this) || $me->isThemeCoordinatorOf($this) || $me->isRoleAtLeast(STAFF) || $me->isRole(SD));
    }
}

?>
