<?php

/**
 * @package GrandObjects
 */

class Project extends BackboneModel {

    static $cache = array();

    var $id;
    var $evolutionId;
    var $fullName;
    var $name;
    var $status;
    var $type;
    var $parentId;
    var $bigbet;
    var $people;
    var $multimedia;
    var $startDates;
    var $endDates;
    var $comments;
    var $deleted;
    var $effectiveDate;
    private $succ;
    private $preds;
    private $peopleCache = null;
    private $leaderCache = array();

    /**
     * Returns a new Project from the given id
     * @param integer $id The id of the project
     * @return Project The Project with the given id
     */
    static function newFromId($id){
        $me = Person::newFromWgUser();
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        $sql = "(SELECT p.id, p.name, p.phase, p.parent_id, e.action, e.effective_date, e.id as evolutionId, e.clear, s.status, s.type, s.bigbet
                 FROM grand_project p, grand_project_evolution e, grand_project_status s
                 WHERE e.`project_id` = '{$id}'
                 AND e.`new_id` != '{$id}'
                 AND e.new_id = p.id
                 AND s.evolution_id = e.id
                 AND e.clear != 1
                 ORDER BY `date` DESC LIMIT 1)
                UNION 
                (SELECT p.id, p.name, p.phase, p.parent_id, e.action, e.effective_date, e.id as evolutionId, e.clear, s.status, s.type, s.bigbet
                 FROM grand_project p, grand_project_evolution e, grand_project_status s
                 WHERE p.id = '$id'
                 AND e.new_id = p.id
                 AND s.evolution_id = e.id
                 ORDER BY e.id DESC LIMIT 1)";
        $data = DBFunctions::execSQL($sql);
        if (DBFunctions::getNRows() > 0){
            if(count($data) > 1){
                // This project has a history
                $project = Project::newFromHistoricId($data[0]['id']);
            }
            else if($me->isLoggedIn() || $data[0]['status'] != 'Proposed'){
                $project = new Project($data);
            }
            else{
                return null;
            }
            self::$cache[$project->id] = &$project;
            self::$cache[$project->name] = &$project;
            return $project;
        }
        else
            return null;
    }
    
    /**
     * Returns a new Project from the given name
     * @param string $name The name of the Project
     * @return Project The Project with the given name
     */
    static function newFromName($name){
        $me = Person::newFromWgUser();
        if(isset(self::$cache[$name])){
            return self::$cache[$name];
        }
        $data = DBFunctions::select(array('grand_project' => 'p',
                                          'grand_project_evolution' => 'e',
                                          'grand_project_status' => 's'),
                                    array('p.id',
                                          'p.name',
                                          'p.phase',
                                          'p.parent_id',
                                          'e.action',
                                          'e.effective_date',
                                          'e.id' => 'evolutionId',
                                          'e.clear',
                                          's.type',
                                          's.status',
                                          's.bigbet'),
                                    array('LOWER(p.name)' => strtolower(trim($name)),
                                          'e.new_id' => EQ(COL('p.id')),
                                          's.evolution_id' => EQ(COL('e.id'))),
                                    array('e.id' => 'DESC'),
                                    array(1));
        if (count($data) > 0){
            $data1 = DBFunctions::select(array('grand_project_evolution'),
                                         array('new_id',
                                               'project_id'),
                                         array('project_id' => $data[0]['id'],
                                               'new_id' => $data[0]['id']),
                                         array('date' => 'DESC'),
                                         array(1));
            if(count($data1) > 0){
                $project = Project::newFromId($data1[0]['new_id']);
                self::$cache[$data1[0]['project_id']] = &$project;
                self::$cache[$name] = &$project;
                return $project;
            }
            else if($me->isLoggedIn() || $data[0]['status'] != 'Proposed'){
                $project = new Project($data);
            }
            else{
                return null;
            }
            $project = new Project($data);
            //self::$cache[$project->id] = &$project;
            //self::$cache[$project->name] = &$project;
            return $project;
        }
        else
            return null;
    }
    
    /**
     * Returns a new Project from the given title (may not be unique)
     * @param string $title The title (fullName) of the Project
     * @return Project The Project with the given title
     */
    static function newFromTitle($title){
        $me = Person::newFromWgUser();
        if(isset(self::$cache[$title])){
            return self::$cache[$title];
        }
        $data = DBFunctions::select(array('grand_project' => 'p',
                                          'grand_project_evolution' => 'e',
                                          'grand_project_status' => 's',
                                          'grand_project_descriptions' => 'd'),
                                    array('p.id',
                                          'p.name',
                                          'p.phase',
                                          'p.parent_id',
                                          'e.action',
                                          'e.effective_date',
                                          'e.id' => 'evolutionId',
                                          'e.clear',
                                          's.type',
                                          's.status',
                                          's.bigbet'),
                                    array('LOWER(d.full_name)' => strtolower(trim($title)),
                                          'p.id' => EQ(COL('d.project_id')),
                                          'e.new_id' => EQ(COL('p.id')),
                                          's.evolution_id' => EQ(COL('e.id'))),
                                    array('e.id' => 'DESC'),
                                    array(1));
        if (count($data) > 0){
            $data1 = DBFunctions::select(array('grand_project_evolution'),
                                         array('new_id',
                                               'project_id'),
                                         array('project_id' => $data[0]['id'],
                                               'new_id' => $data[0]['id']),
                                         array('date' => 'DESC'),
                                         array(1));
            if(count($data1) > 0){
                $project = Project::newFromId($data1[0]['new_id']);
                self::$cache[$data1[0]['project_id']] = &$project;
                self::$cache[$name] = &$project;
                return $project;
            }
            else if($me->isLoggedIn() || $data[0]['status'] != 'Proposed'){
                $project = new Project($data);
            }
            else{
                return null;
            }
            $project = new Project($data);
            //self::$cache[$project->id] = &$project;
            //self::$cache[$project->name] = &$project;
            return $project;
        }
        else
            return null;
    }
    
    /**
     * Returns a new Project from the given historic id
     * @param integer $id The historic id of the project
     * @param integer $evolutionId The id of the evolution entry
     * @return Project The Project with the given historic id
     */
    static function newFromHistoricId($id, $evolutionId=null){
        $me = Person::newFromWgUser();
        if(isset(self::$cache[$id.'_'.$evolutionId])){
            return self::$cache[$id.'_'.$evolutionId];
        }
        $sqlExtra = ($evolutionId != null) ? $sqlExtra = "AND e.id = $evolutionId" : "";
        $sql = "SELECT p.id, p.name, p.phase, p.parent_id, e.action, e.effective_date, e.id as evolutionId, e.clear, s.type, s.status, s.bigbet
                FROM grand_project p, grand_project_evolution e, grand_project_status s
                WHERE p.id = '$id'
                AND e.new_id = p.id
                AND s.evolution_id = e.id
                $sqlExtra
                ORDER BY e.id DESC LIMIT 1";
        $data = DBFunctions::execSQL($sql);
        if (DBFunctions::getNRows() > 0 && ($me->isLoggedIn() || $data[0]['status'] != 'Proposed')){
            $project = new Project($data);
            $project->evolutionId = $evolutionId;
            self::$cache[$id.'_'.$evolutionId] = $project;
            return $project;
        }
    }
    
    /**
     * Returns a new Project from the given historic name
     * @param string $name The historic name of the Project
     * @return Project The Project with the given historic name
     */
    static function newFromHistoricName($name){
        $me = Person::newFromWgUser();
        if(isset(self::$cache['h_'.$name])){
            return self::$cache['h_'.$name];
        }
        $sql = "SELECT p.id, p.name, p.phase, p.parent_id, e.action, e.effective_date, e.id as evolutionId, e.clear, s.type, s.status, s.bigbet
                FROM grand_project p, grand_project_evolution e, grand_project_status s
                WHERE p.name = '$name'
                AND e.new_id = p.id
                AND s.evolution_id = e.id
                ORDER BY e.id DESC LIMIT 1";
        $data = DBFunctions::execSQL($sql);
        if (DBFunctions::getNRows() > 0 && ($me->isLoggedIn() || $data[0]['status'] != 'Proposed')){
            $project = new Project($data);
            self::$cache['h_'.$name] = &$project;
            return $project;
        }
    }
    
    /**
     * Returns all of the current Projects from the database
     * @param boolean $subProjects Whether or not to include sub-projects
     * @return array An array of Projects
     */
    static function getAllProjects($subProjects=false){
        $me = Person::newFromWgUser();
        if($subProjects == false){
            $subProjects = EQ(0);
        }
        else{
            $subProjects = LIKE("%");
        }
        $data = DBFunctions::select(array('grand_project'),
                                    array('id', 'name'),
                                    array('parent_id' => $subProjects),
                                    array('name' => 'ASC'));
        $projects = array();
        foreach($data as $row){
            $project = Project::newFromId($row['id']);
            if($project != null && $project->getName() != ""){
                if(!isset($projects[$project->name]) && ($me->isLoggedIn() || $project->getStatus() != 'Proposed')){
                    $projects[$project->getName()] = $project;
                }
            }
        }
        ksort($projects);
        $projects = array_values($projects);
        return $projects;
    }
    
    // Constructor
    // Takes in a resultset containing the 'project id' and 'project name'
    function Project($data){
        if(isset($data[0])){
            $this->id = $data[0]['id'];
            $this->name = $data[0]['name'];
            $this->evolutionId = $data[0]['evolutionId'];
            $this->status = $data[0]['status'];
            $this->type = $data[0]['type'];
            $this->bigbet = $data[0]['bigbet'];
            $this->phase = $data[0]['phase'];
            $this->parentId = $data[0]['parent_id'];
            $this->succ = false;
            $this->preds = false;
            
            if(isset($data[0]['action']) && $data[0]['action'] == 'DELETE'){
                $this->deleted = true;
            }
            else{
                $this->deleted = false;
            }
            if(isset($data[0]['effective_date'])){
                $this->effectiveDate = $data[0]['effective_date'];
            }
            else{
                $this->effectiveDate = "0000-00-00 00:00:00";
            }
            $this->fullName = false;
        }
    }
    
    function toArray(){
        $array = array('id' => $this->getId(),
                       'name' => $this->getName(),
                       'status' => $this->getStatus(),
                       'type' => $this->getType(),
                       'url' => $this->getUrl());
        return $array;
    }
    
    function create(){
    
    }
    
    function update(){
    
    }
    
    function delete(){
    
    }
    
    function exists(){
    
    }
    
    function getCacheId(){
    
    }
    
    // Returns the id of this Project
    function getId(){
        return $this->id;
    }
    
    // Returns the name of this Project
    function getName(){
        return $this->name;
    }    
    
    // Returns the status of this Project
    function getStatus(){
        return $this->status;
    }
    
    // Returns the type of this Project
    function getType(){
        return $this->type;
    }
    
    // Returns the url of this Project's profile page
    function getUrl(){
        global $wgServer, $wgScriptPath;
        return "{$wgServer}{$wgScriptPath}/index.php/{$this->getName()}:Main";
    }
    
    // Returns an array of Person objects which represent
    // The researchers who are in this project.
    // If $filter is included, only users of that type will be selected
    function getAllPeople($filter = null){
        $currentDate = date('Y-m-d H:i:s');
        $year = date('Y');
        $people = array();
        
        if(!Cache::exists("project{$this->id}_people")){
            $sql = "SELECT m.user_id, u.user_name, SUBSTR(u.user_name, LOCATE('.', u.user_name) + 1) as last_name
                    FROM grand_project_members m, mw_user u
                    WHERE (m.end_date > CURRENT_TIMESTAMP OR m.end_date = '0000-00-00 00:00:00')
                    AND m.user_id = u.user_id
                    AND m.project_id = '{$this->id}'
                    AND u.`deleted` != '1'
                    ORDER BY last_name ASC";
            $data = DBFunctions::execSQL($sql);
            Cache::store("project{$this->id}_people", $data);
        }
        else{
            $data = Cache::fetch("project{$this->id}_people");
        }
        foreach($data as $row){
            $id = $row['user_id'];
            $person = Person::newFromId($id);
            if($filter == PL){
                if($person->leadershipOf($this)){
                    $people[$person->getId()] = $person;
                }
            }
            else if(($filter == null || 
                     ($person->isRole($filter) && !$person->leadershipOf($this)) || 
                     ($person->isRole($filter) && !$person->leadershipOf($this))) && 
                    !$person->isRole(ADMIN)){
                $people[$person->getId()] = $person;
            }
        }
        return $people;
    }
    
    // Returns an array of Person objects which represent
    // The researchers who are in this project or were in the project during the specified period
    // If $filter is included, only users of that type will be selected
    function getAllPeopleDuring($filter = null, $startRange = false, $endRange = false, $includeManager=false){
        if($startRange === false || $endRange === false){
            $startRange = date("Y-01-01 00:00:00");
            $endRange = date("Y-12-31 23:59:59");
        }
        $year = substr($endRange, 0, 4);
        $people = array();
        if(!Cache::exists("project{$this->id}_peopleDuring$startRange.$endRange")){
            $sql = "SELECT p.user_id, u.user_name, SUBSTR(u.user_name, LOCATE('.', u.user_name) + 1) as last_name
                    FROM grand_project_members p, mw_user u
                    WHERE p.user_id = u.user_id
                    AND p.project_id = '{$this->id}'
                    AND ( 
                    ( (p.end_date != '0000-00-00 00:00:00') AND
                    (( p.start_date BETWEEN '$startRange' AND '$endRange' ) || ( p.end_date BETWEEN '$startRange' AND '$endRange' ) || (p.start_date <= '$startRange' AND p.end_date >= '$endRange') ))
                    OR
                    ( (p.end_date = '0000-00-00 00:00:00') AND
                    ((p.start_date <= '$endRange')))
                    )
                    AND u.`deleted` != '1'
                    ORDER BY last_name ASC";
            $data = DBFunctions::execSQL($sql);
            Cache::store("project{$this->id}_peopleDuring$startRange.$endRange", $data);
        }
        else{
            $data = Cache::fetch("project{$this->id}_peopleDuring$startRange.$endRange");
        }
        foreach($data as $row){
            $id = $row['user_id'];
            $person = Person::newFromId($id);
            if($filter == PL){
                if($person->leadershipOf($this)){
                    $people[$person->getId()] = $person;
                }
            }
            else if(($filter == null || 
                     ($person->isRoleDuring($filter, $startRange, $endRange) && !$person->leadershipOf($this))) && 
                    ($includeManager || !$person->isRoleDuring(MANAGER, $startRange, $endRange))){
                $people[$person->getId()] = $person;
            }
        }
        return $people;
    }
    
    function getAllPeopleOn($filter, $date, $includeManager=false){
        $year = substr($date, 0, 4);
        $people = array();
        $sql = "SELECT p.user_id, u.user_name, SUBSTR(u.user_name, LOCATE('.', u.user_name) + 1) as last_name
                FROM grand_project_members p, mw_user u
                WHERE p.user_id = u.user_id
                AND p.project_id = '{$this->id}'
                AND (('$date' BETWEEN p.start_date AND p.end_date ) OR (p.start_date <= '$date' AND p.end_date = '0000-00-00 00:00:00'))
                AND u.`deleted` != '1'
                ORDER BY last_name ASC";
        $data = DBFunctions::execSQL($sql);
        foreach($data as $row){
            $id = $row['user_id'];
            $person = Person::newFromId($id);
            if($filter == PL){
                if($person->leadershipOf($this)){
                    $people[$person->getId()] = $person;
                }
            }
            else if(($filter == null || 
                     ($person->isRoleOn($filter, $date) && !$person->leadershipOf($this))) && 
                    ($includeManager || !$person->isRoleOn(MANAGER, $date))){
                $people[$person->getId()] = $person;
            }
        }
        return $people;
    }

}

?>
