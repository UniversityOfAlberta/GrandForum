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
    var $contributions;
    var $multimedia;
    var $startDates;
    var $endDates;
    var $comments;
    var $milestones;
    var $budgets;
    var $deleted;
    var $effectiveDate;
    private $themes;
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
            $project = new Project($data);
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
                                    array('p.name' => $name,
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
        if (DBFunctions::getNRows() > 0){
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
        if (DBFunctions::getNRows() > 0){
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
                if(!isset($projects[$project->name]) && !$project->isDeleted()){
                    $projects[$project->getName()] = $project;
                }
            }
        }
        ksort($projects);
        $projects = array_values($projects);
        return $projects;
    }
    
    static function getAllProjectsDuring($startDate, $endDate, $subProjects=false){
        if($subProjects == false){
            $subProjects = EQ(0);
        }
        else{
            $subProjects = LIKE("%");
        }
        $data = DBFunctions::select(array('grand_project'),
                                    array('name'),
                                    array('parent_id' => $subProjects),
                                    array('name' => 'ASC'));
        $projects = array();
        foreach($data as $row){
            $project = Project::newFromHistoricName($row['name']);
            if($project != null && $project->getName() != ""){
                if(!isset($projects[$project->name])){
                    if(($project->deleted &&
                        substr($project->effectiveDate, 0, 10) >= $endDate || 
                        (substr($project->effectiveDate, 0, 10) <= $endDate && substr($project->effectiveDate, 0, 10) >= $startDate)) ||
                       !$project->deleted){
                        if(substr($project->getCreated(), 0, 10) <= $endDate){
                            $projects[$project->getName()] = $project;
                        }
                    }
                }
            }
        }
        ksort($projects);
        $projects = array_values($projects);
        return $projects;
    }
    
    // Same as getAllProjects, but will also return deleted projects
    static function getAllProjectsEver($subProjects=false){
        if($subProjects == false){
            $subProjects = EQ(0);
        }
        else{
            $subProjects = LIKE("%");
        }
        $data = DBFunctions::select(array('grand_project'),
                                    array('name'),
                                    array('parent_id' => $subProjects),
                                    array('name' => 'ASC'));
        $projects = array();
        foreach($data as $row){
            $project = Project::newFromHistoricName($row['name']);
            if($project != null && $project->getName() != ""){
                if(!isset($projects[$project->name])){
                    $projects[$project->getName()] = $project;
                }
            }
        }
        ksort($projects);
        $projects = array_values($projects);
        return $projects;
    }
    
    static function areThereDeletedProjects(){
        $data = DBFunctions::select(array('grand_project_evolution'),
                                    array('project_id'),
                                    array('action' => EQ('DELETE')));
        return (count($data) > 0);
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
            $this->clear = ($data[0]['clear'] == 1);
            
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
            $this->themes = null;
        }
    }
    
    function toArray(){
        $subProjects = $this->getSubProjects();
        $subs = array();
        foreach($subProjects as $sub){
            $subs[] = array('id' => $sub->getId(),
                            'name' => $sub->getName(),
                            'url' => $sub->getUrl());
        }
        $array = array('id' => $this->getId(),
                       'name' => $this->getName(),
                       'fullname' => $this->getFullName(),
                       'description' => $this->getDescription(),
                       'status' => $this->getStatus(),
                       'type' => $this->getType(),
                       'bigbet' => $this->isBigBet(),
                       'phase' => $this->getPhase(),
                       'url' => $this->getUrl(),
                       'deleted' => $this->isDeleted(),
                       'subprojects' => $subs,
                       'startDate' => $this->getCreated(),
                       'endDate' => $this->getDeleted());
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
    
    static function getHQPDistributionDuring($startRange, $endRange){
        $sql = <<<EOF
        SELECT s.num_projects, COUNT(s.user_id) as user_count
        FROM 
        (SELECT p.user_id, COUNT(DISTINCT p.project_id) as num_projects
        FROM grand_project_members p
        INNER JOIN mw_user u ON (p.user_id=u.user_id) 
        INNER JOIN grand_roles r ON (p.user_id=r.user_id)
        INNER JOIN grand_project pp ON (p.project_id=pp.id)
        WHERE r.role = 'HQP'
        AND ( 
                ( (r.end_date != '0000-00-00 00:00:00') AND
                (( r.start_date BETWEEN '$startRange' AND '$endRange' ) || ( r.end_date BETWEEN '$startRange' AND '$endRange' ) || (r.start_date <= '$startRange' AND r.end_date >= '$endRange') ))
                OR
                ( (r.end_date = '0000-00-00 00:00:00') AND
                ((r.start_date <= '$endRange')))
                )              
        AND u.deleted != '1'
        AND pp.parent_id = 0
        GROUP BY p.user_id) AS s
        GROUP BY s.num_projects
EOF;
        $data = DBFunctions::execSQL($sql);
        $distribution = array();
        foreach($data as $row){
            $distribution[$row['num_projects']] = $row['user_count'];
        }
        
        return $distribution;
    }
    
    // Returns the id of this Project
    function getId(){
        return $this->id;
    }
    
    // Returns the evolutionId of this Project
    // The evolution id is like a revision of the project since projects can merge, change status/type etc.
    function getEvolutionId(){
        return $this->evolutionId;
    }
    
    // Returns the name of this Project
    function getName(){
        return $this->name;
    }    
    
    // Returns the full name of this Project
    function getFullName(){
        if($this->fullName === false){
            $sql = "(SELECT d.full_name
                     FROM `grand_project_descriptions` d
                     WHERE d.evolution_id = '{$this->evolutionId}'
                     ORDER BY d.id DESC LIMIT 1)
                    UNION
                    (SELECT d.full_name
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'
                     ORDER BY d.evolution_id LIMIT 1)";
            $data = DBFunctions::execSQL($sql);
            if(DBFunctions::getNRows() > 0){
                $this->fullName = $data[0]['full_name'];
            }
            else{
                $this->fullName = $this->name;
            }
        }
        return $this->fullName;
    }
    
    // Returns the status of this Project
    function getStatus(){
        return $this->status;
    }
    
    // Returns the type of this Project
    function getType(){
        return $this->type;
    }
    
    function isBigBet(){
        return $this->bigbet;
    }
    
    // Returns the phase of this Project
    function getPhase(){
        return $this->phase;
    }
    
    // Returns the Predecessors of this Project
    function getPreds(){
        if($this->preds === false){
            $sql = "SELECT DISTINCT e.project_id, e.last_id
                    FROM `grand_project_evolution` e
                    WHERE e.new_id = '{$this->id}'
                    AND (e.id = '{$this->evolutionId}' OR e.action = 'MERGE' OR e.action = 'EVOLVE')
                    AND '{$this->evolutionId}' > e.last_id
                    ORDER BY e.id DESC";
            $data = DBFunctions::execSQL($sql);
            $this->preds = array();
            foreach($data as $row){
                $pred = Project::newFromHistoricId($row['project_id'], $row['last_id']);
                if($pred != null && $pred->getName() != ""){
                    if($pred->getId() == $this->id){
                        // These are the same project id, just different evolution id.  Copy over some of the data
                        $pred->milestones = $this->milestones;
                        $pred->people = $this->people;
                        $pred->contributions = $this->contributions;
                        $pred->multimedia = $this->multimedia;
                        $pred->startDates = $this->startDates;
                        $pred->endDates = $this->endDates;
                        $pred->comments = $this->comments;
                        $pred->budgets = $this->budgets;
                    }
                    $this->preds[] = $pred;
                }
            }
        }
        return $this->preds;
    }
    
    // Returns the full Predecessor history of this Project
    // NOTE: this is not cached, so don't call it too much
    function getAllPreds(){
        $preds = array();
        foreach($this->getPreds() as $pred){
            $preds = array_merge($preds, array_merge(array($pred), $pred->getAllPreds()));
        }
        return $preds;
    }   
    
    // Returns the Successor Projects
    function getSuccs(){
        if(!is_array($this->succ) && $this->succ == false){
            $this->succ = array();
            $sql = "SELECT e.new_id FROM
                    `grand_project_evolution` e
                    WHERE e.project_id = '{$this->id}'";
            $data = DBFunctions::execSQL($sql);
            if(count($data) > 0){
                foreach($data as $row){
                    $this->succ[] = Project::newFromHistoricId($row['new_id']);
                }
            }
        }
        return $this->succ;
    }
    
    // Returns the url of this Project's profile page
    function getUrl(){
        global $wgServer, $wgScriptPath;
        return "{$wgServer}{$wgScriptPath}/index.php/{$this->getName()}:Main";
    }
    
    // Returns the full name of this Project
    function getLastHistoryId(){
        $sql = "SELECT d.id
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'
                ORDER BY d.id DESC";
        $data = DBFunctions::execSQL($sql);
        if(count($data) > 0){
            return $data[0]['id'];
        }
        else {
            return null;
        }
    }
    
    // Returns whether or not this project had been deleted or not
    function isDeleted(){
        if(strcmp($this->effectiveDate, date('Y-m-d H:i:s')) <= 0){
            return $this->deleted;
        }
        return false;
    }
    
    // Returns when this project was initially created
    function getCreated(){
        $created = null;
        if(!$this->clear){
            $preds = $this->getPreds();
            if(count($preds) == 0){
                return $this->getEffectiveDate();
            }
            else{
                foreach($preds as $pred){
                    $created = $pred->getCreated();
                }
            }
        }
        else{
            $created = $this->getEffectiveDate();
        }
        return $created;
    }
    
    function getDeleted(){
        if($this->isDeleted()){
            return $this->getEffectiveDate();
        }
        else{
            return "0000-00-00 00:00:00";
        }
    }
    
    // Returns when the evolution state took place
    function getEffectiveDate(){
        return $this->effectiveDate;
    }
    
    // Returns an array of Person objects which represent
    // The researchers who are in this project.
    // If $filter is included, only users of that type will be selected
    function getAllPeople($filter = null){
        $currentDate = date('Y-m-d H:i:s');
        $created = $this->getCreated();
        $people = array();
        if(!$this->clear){
            $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getAllPeople($filter) as $person){
                    $people[$person->getId()] = $person;
                }
            }
        }
        
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
            if(($filter == null || ($currentDate >= $created && $person->isRole($filter)) || $person->isRoleDuring($filter, $created, "9999")) && !$person->isRole(MANAGER)){
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
        $people = array();
        if(!$this->clear){
            $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getAllPeopleDuring($filter, $startRange, $endRange, $includeManager) as $person){
                    $people[$person->getId()] = $person;
                }
            }
        }
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
            if(($filter == null || $person->isRoleDuring($filter, $startRange, $endRange)) && ($includeManager || !$person->isRoleDuring(MANAGER, $startRange, $endRange))){
                $people[$person->getId()] = $person;
            }
        }
        return $people;
    }
    
    function getAllPeopleOn($filter, $date, $includeManager=false){
        $people = array();
        if(!$this->clear){
            $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getAllPeopleOn($filter, $date, $includeManager) as $person){
                    $people[$person->getId()] = $person;
                }
            }
        }
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
            if(($filter == null || $person->isRoleOn($filter, $date)) && ($includeManager || !$person->isRoleOn(MANAGER, $date))){
                $people[$person->getId()] = $person;
            }
        }
        return $people;
    }
    
    // Returns the contributions this relevant to this project
    function getContributions(){
        if($this->contributions == null){
            $this->contributions = array();
            if(!$this->clear){
                $preds = $this->getPreds();
                foreach($preds as $pred){
                    foreach($pred->getContributions() as $contribution){
                        $this->contributions[$contribution->getId()] = $contribution;
                    }
                }
            }
            $sql = "SELECT id
                    FROM(SELECT c.id, c.name, c.rev_id
                         FROM grand_contributions c, grand_contributions_projects p
                         WHERE p.project_id = '{$this->id}'
                         AND p.contribution_id = c.rev_id
                         GROUP BY c.id, c.name, c.rev_id
                         ORDER BY c.id ASC, c.rev_id DESC) a
                    GROUP BY id";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $contribution = Contribution::newFromId($row['id']);
                if($contribution->belongsToProject($this)){
                    $this->contributions[$row['id']] = $contribution;
                }
            }
        }
        return $this->contributions;
    }
    
    // Returns the contributions relevant to this project during the given year
    function getContributionsDuring($year){
        $contribs = array();
        foreach($this->getContributions() as $contrib){
            if($contrib->getYear() == $year){
                $contribs[] = $contrib;
            }
        }
        return $contribs;
    }
    
    // Returns an array of Materials for this Project
    function getMultimedia(){
        if($this->multimedia == null){
            $this->multimedia = array();
            if(!$this->clear){
                $preds = $this->getPreds();
                foreach($preds as $pred){
                    foreach($pred->getMultimedia() as $multimedia){
                        $this->multimedia[$multimedia->getId()] = $multimedia;
                    }
                }
            }
            $sql = "SELECT m.id
                    FROM `grand_materials` m, `grand_materials_projects` p
                    WHERE p.project_id = '{$this->id}'
                    AND p.material_id = m.id";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->multimedia[$row['id']] = Material::newFromId($row['id']);
            }
        }
        return $this->multimedia;
    }
    
    /**
     * Returns all the current Champions
     * @param array The current Champions array(user, org, title, dept)
     */
    function getChampions(){
        $champs = array();
        $people = $this->getAllPeople(CHAMP);
        foreach($people as $champ){
            $champs[] = array('user' => $champ,
                              'org' => $champ->getPartnerName(),
                              'title' => $champ->getPartnerTitle(),
                              'dept' => $champ->getPartnerDepartment());
        }
        return $champs;
    }
    
    /**
     * Returns all the Champions between the given time frame
     * @param string $start the starting date of the range
     * @param string $end the ending date of the range
     * @param array The People who were Champions during the given date array(user, org, title, dept)
     */
    function getChampionsDuring($start, $end){
        $champs = array();
        $people = $this->getAllPeopleDuring(CHAMP, $start, $end);
        foreach($people as $champ){
            $champs[] = array('user' => $champ,
                              'org' => $champ->getPartnerName(),
                              'title' => $champ->getPartnerTitle(),
                              'dept' => $champ->getPartnerDepartment());
        }
        return $champs;
    }
    
    /**
     * Returns all the People who were Champions on the given date
     * @param string $date The date to check
     * @return array The People who were Champions on the given date array(user, org, title, dept)
     */
    function getChampionsOn($date){
        $champs = array();
        $people = $this->getAllPeopleOn(CHAMP, $date);
        foreach($people as $champ){
            $champs[] = array('user' => $champ,
                              'org' => $champ->getPartnerName(),
                              'title' => $champ->getPartnerTitle(),
                              'dept' => $champ->getPartnerDepartment());
        }
        return $champs;
    }

    // Returns the leader of this Project
    function getLeader(){
        $sql = "SELECT pl.*
                FROM grand_project_leaders pl, mw_user u
                WHERE pl.project_id = '{$this->id}'
                AND pl.type = 'leader'
                AND pl.user_id <> '4'
                AND pl.user_id <> '150'
                AND u.user_id = pl.user_id
                AND u.deleted != '1'
                AND (pl.end_date = '0000-00-00 00:00:00'
                     OR pl.end_date > CURRENT_TIMESTAMP)";
        $data = DBFunctions::execSQL($sql);
        if(count($data) > 0){
            return Person::newFromId($data[0]['user_id']);
        }
        else {
            return null;
        }
    }
    
    // Returns the co-leader of this Project
    function getCoLeader(){
        $sql = "SELECT pl.*
                FROM grand_project_leaders pl, mw_user u
                WHERE pl.project_id = '{$this->id}'
                AND pl.type = 'co-leader'
                AND pl.user_id <> '4'
                AND pl.user_id <> '150'
                AND u.user_id = pl.user_id
                AND u.deleted != '1'
                AND (pl.end_date = '0000-00-00 00:00:00'
                     OR pl.end_date > CURRENT_TIMESTAMP)";
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return Person::newFromId($data[0]['user_id']);
        }
        else {
            return null;
        }
    }
    
    /// Returns an array with the coleaders of the project.  By default, the
    /// resulting array contains instances of Person.  If #onlyid is set to
    /// true, then the resulting array contains only numerical user IDs.
    function getCoLeaders($onlyid = false){
        $onlyIdStr = ($onlyid) ? 'true' : 'false';
        if(isset($this->leaderCache['coleaders'.$onlyIdStr])){
            return $this->leaderCache['coleaders'.$onlyIdStr];
        }
        $ret = array();
        if(!$this->clear){
            $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getCoLeaders($onlyid) as $leader){
                    if($onlyid){
                        $ret[$leader] = $leader;
                    }
                    else{
                        $ret[$leader->getId()] = $leader;
                    }
                }
            }
        }
        $sql = "SELECT pl.user_id FROM grand_project_leaders pl, mw_user u
                WHERE pl.project_id = '{$this->id}'
                AND pl.type = 'co-leader'
                AND pl.user_id NOT IN (4, 150)
                AND u.user_id = pl.user_id
                AND u.deleted != '1'
                AND (pl.end_date = '0000-00-00 00:00:00'
                     OR pl.end_date > CURRENT_TIMESTAMP)";
        $data = DBFunctions::execSQL($sql);
        if ($onlyid) {
            foreach ($data as &$row)
                $ret[$row['user_id']] = $row['user_id'];
        }
        else {
            foreach ($data as &$row)
                $ret[$row['user_id']] = Person::newFromId($row['user_id']);
        }
        $this->leaderCache['coleaders'.$onlyIdStr] = $ret;
        return $ret;
    }
    
    function getCoLeadersHistory(){
        $ret = array();
        if(!$this->clear){
            $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getCoLeadersHistory() as $leader){
                    $ret[$leader->getId()] = $leader;
                }
            }
        }
        $sql = "SELECT pl.user_id FROM grand_project_leaders pl, mw_user u
                WHERE pl.project_id = '{$this->id}'
                AND pl.type = 'co-leader'
                AND pl.user_id NOT IN (4, 150)
                AND u.user_id = pl.user_id
                AND u.deleted != '1'";
        $data = DBFunctions::execSQL($sql);
        foreach ($data as &$row){
            $ret[$row['user_id']] = Person::newFromId($row['user_id']);
        }
        return $ret;
    }

    /// Returns an array with the leaders of the project.  By default, the
    /// resulting array contains instances of Person.  If #onlyid is set to
    /// true, then the resulting array contains only numerical user IDs.
    function getLeaders($onlyid = false) {
        $onlyIdStr = ($onlyid) ? 'true' : 'false';
        if(isset($this->leaderCache['leaders'.$onlyIdStr])){
            return $this->leaderCache['leaders'.$onlyIdStr];
        }
        $ret = array();
        if(!$this->clear){
            $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getLeaders($onlyid) as $leader){
                    if($onlyid){
                        $ret[$leader] = $leader;
                    }
                    else{
                        $ret[$leader->getId()] = $leader;
                    }
                }
            }
        }
        $sql = "SELECT pl.user_id FROM grand_project_leaders pl, mw_user u
                WHERE pl.project_id = '{$this->id}'
                AND pl.type = 'leader'
                AND pl.user_id NOT IN (4, 150)
                AND u.user_id = pl.user_id
                AND u.deleted != '1'
                AND (pl.end_date = '0000-00-00 00:00:00'
                     OR pl.end_date > CURRENT_TIMESTAMP)";
        $data = DBFunctions::execSQL($sql);
        if ($onlyid) {
            foreach ($data as &$row)
                $ret[$row['user_id']] = $row['user_id'];
        }
        else {
            foreach ($data as &$row)
                $ret[$row['user_id']] = Person::newFromId($row['user_id']);
        }
        $this->leaderCache['leaders'.$onlyIdStr] = $ret;
        return $ret;
    }
    
    function getLeadersHistory(){
        $ret = array();
        if(!$this->clear){
            $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getLeadersHistory() as $leader){
                    $ret[$leader->getId()] = $leader;
                }
            }
        }
        $sql = "SELECT pl.user_id FROM grand_project_leaders pl, mw_user u
                WHERE pl.project_id = '{$this->id}'
                AND pl.type = 'leader'
                AND pl.user_id NOT IN (4, 150)
                AND u.user_id = pl.user_id
                AND u.deleted != '1'";
        $data = DBFunctions::execSQL($sql);
        foreach ($data as &$row){
            $ret[$row['user_id']] = Person::newFromId($row['user_id']);
        }
        return $ret;
    }
    
    /// Returns an array with the leaders of the project.  By default, the
    /// resulting array contains instances of Person.  If #onlyid is set to
    /// true, then the resulting array contains only numerical user IDs.
    function getManagers($onlyid = false) {
        $onlyIdStr = ($onlyid) ? 'true' : 'false';
        if(isset($this->leaderCache['managers'.$onlyIdStr])){
            return $this->leaderCache['managers'.$onlyIdStr];
        }
        $ret = array();
        if(!$this->clear){
            $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getManagers($onlyid) as $leader){
                    if($onlyid){
                        $ret[$leader] = $leader;
                    }
                    else{
                        $ret[$leader->getId()] = $leader;
                    }
                }
            }
        }
        $sql = "SELECT pl.user_id FROM grand_project_leaders pl, mw_user u
                WHERE pl.project_id = '{$this->id}'
                AND pl.type = 'manager'
                AND pl.user_id NOT IN (4, 150)
                AND u.user_id = pl.user_id
                AND u.deleted != '1'
                AND (pl.end_date = '0000-00-00 00:00:00'
                     OR pl.end_date > CURRENT_TIMESTAMP)";
        $data = DBFunctions::execSQL($sql);
        if ($onlyid) {
            foreach ($data as &$row)
                $ret[$row['user_id']] = $row['user_id'];
        }
        else {
            foreach ($data as &$row)
                $ret[$row['user_id']] = Person::newFromId($row['user_id']);
        }
        $this->leaderCache['managers'.$onlyIdStr] = $ret;
        return $ret;
    }
    
    // Returns the theme percentage of this project of the given theme index $i
    // OLD: Only used for phase1 projects, should be refactored somehow
    function getTheme($i, $history=false){
        if(!($i >= 1 && $i <= 5)) return 0; // Fail Gracefully if the index was out of bounds, and return 0
        if($this->themes == null){
            $this->themes = array();
            
            $sql = "(SELECT themes 
                    FROM grand_project_descriptions d
                    WHERE d.project_id = '{$this->id}'\n";
            if(!$history){
                $sql .= "AND evolution_id = '{$this->evolutionId}'
                         ORDER BY id DESC LIMIT 1)
                        UNION
                        (SELECT themes
                         FROM grand_project_descriptions d
                         WHERE d.project_id = '{$this->id}'";
            }
            $sql .= "ORDER BY id DESC LIMIT 1)";
            
            $data = DBFunctions::execSQL($sql);
            if(DBFunctions::getNRows() > 0){
                $themes = explode("\n", $data[0]['themes']);
                $this->themes = $themes;
            }
        }
        if(isset($this->themes[$i-1])){
            return $this->themes[$i-1];
        }
        return 0;
    }

    /// Returns all themes for the project as an associative array.
    // OLD: Only used for phase1 projects, should be refactored somehow
    function getThemes() {
        $ret = array('names' => array(), 'values' => array());
        // Put up the associative array.  Absent values default to 0.
        list($ret['names'][1], $ret['names'][2], $ret['names'][3], $ret['names'][4], $ret['names'][5]) =
            array('nMEDIA', 'GamSim', 'AnImage', 'SocLeg', 'TechMeth');
        list($ret['values'][1], $ret['values'][2], $ret['values'][3], $ret['values'][4], $ret['values'][5]) =
            array($this->getTheme(1), $this->getTheme(2), $this->getTheme(3), $this->getTheme(4), $this->getTheme(5));
        return $ret;
    }

    //get the project challenge
    function getChallenge(){
        $data = DBFunctions::select(array('grand_project_challenges' => 'pc',
                                          'grand_themes' => 't'),
                                    array('t.id'),
                                    array('t.id' => EQ(COL('pc.challenge_id')),
                                          'pc.project_id' => EQ($this->id)),
                                    array('pc.id' => 'DESC'),
                                    array(1));
        if(count($data) > 0){
            return Theme::newFromId($data[0]['id']);
        }
        else{
            // TODO: This should be refactored in the database so that the above query also determines this information
            // Will need to migrate the data from grand_project_themes into grand_project_challenges
            $themes = $this->getThemes();
            $values = $themes['values'];
            $names = $themes['names'];
            $largest = 0;
            $largestKey = 0;
            foreach($values as $key => $value){
                if($value > $largest){
                    $largest = $value;
                    $largestKey = $key;
                }
            }
            return Theme::newFromId($largestKey);
        }
        return Theme::newFromName("Not Specified");
    } 
    
    // Returns the description of the Project
    function getDescription($history=false){
        $sql = "(SELECT description 
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'\n";
        if(!$history){
            $sql .= "AND evolution_id = '{$this->evolutionId}' 
                     ORDER BY id DESC LIMIT 1)
                    UNION
                    (SELECT description
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'";
        }
        $sql .= "ORDER BY id DESC LIMIT 1)";
        
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return $data[0]['description'];
        }
        return "";
    }

    // Returns the problem summary of the Project
    function getProblem($history=false){
        $sql = "(SELECT problem 
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'\n";
        if(!$history){
            $sql .= "AND evolution_id = '{$this->evolutionId}' 
                     ORDER BY id DESC LIMIT 1)
                    UNION
                    (SELECT problem
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'";
        }
        $sql .= "ORDER BY id DESC LIMIT 1)";
        
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return $data[0]['problem'];
        }
        return "";
    }

    // Returns the solution summary of the Project
    function getSolution($history=false){
        $sql = "(SELECT solution 
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'\n";
        if(!$history){
            $sql .= "AND evolution_id = '{$this->evolutionId}' 
                     ORDER BY id DESC LIMIT 1)
                    UNION
                    (SELECT solution
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'";
        }
        $sql .= "ORDER BY id DESC LIMIT 1)";
        
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return $data[0]['solution'];
        }
        return "";
    }
    
    /**
     * Returns an array of Articles that belong to this Project
     * @return array Returns an array of Articles that belong to this Project
     */
    function getWikiPages(){
        $sql = "SELECT page_id
                FROM mw_page
                WHERE page_namespace = '{$this->getId()}'";
        $data = DBFunctions::execSQL($sql);
        $articles = array();
        foreach($data as $row){
            $article = Article::newFromId($row['page_id']);
            if($article != null && strstr($article->getTitle()->getText(), "MAIL ") === false){
                $articles[] = $article;
            }
        }
        return $articles;
    }
    
    // Returns an array of papers relating to this project
    function getPapers($category="all", $startRange = false, $endRange = false){
        return Paper::getAllPapersDuring($this->name, $category, "grand", $startRange, $endRange);
    }
    
    /**
     * Returns an array of Evaluators who are evaluating this Project
     * @param string $year The evaluation year
     * @return array The array of Evaluators who are evaluating this Project during $year
     */
    function getEvaluators($year){
        $sql = "SELECT *
                FROM grand_eval
                WHERE sub_id = '{$this->id}'
                AND type = 'Project'
                AND year = '{$year}'";
        $data = DBFunctions::execSQL($sql);
        $subs = array();
        foreach($data as $row){
            $subs[] = Person::newFromId($row['user_id']);
        }
        return $subs;
    }
    
    // Returns the comments for the Project when a user moved from this Project
    function getComments(){
        if($this->comments == null){
            $this->comments = array();
            if(!$this->clear){
                $preds = $this->getPreds();
                foreach($preds as $pred){
                    foreach($pred->getComments() as $uId => $comment){
                        $this->comments[$uId] = $comment;
                    }
                }
            }
            $sql = "SELECT user_id, comment 
                    FROM grand_project_members
                    WHERE project_id = '{$this->id}'";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->comments[$row['user_id']] = $row['comment'];
            }
        }
        return $this->comments;
    }
    
    // Returns the startDates for the Project
    function getStartDates(){
        if($this->startDates == null){
            $this->startDates = array();
            if(!$this->clear){
                $preds = $this->getPreds();
                foreach($preds as $pred){
                    foreach($pred->getStartDates() as $uId => $date){
                        $this->startDates[$uId] = $date;
                    }
                }
            }
            $sql = "SELECT user_id, start_date 
                    FROM grand_project_members
                    WHERE project_id = '{$this->id}'";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->startDates[$row['user_id']] = $row['start_date'];
            }
        }
        return $this->startDates;
    }
    
    // Returns the startDate for the given Person
    function getJoinDate($person){
        if($person != null && $person instanceof Person){
            $this->getStartDates();
            if(isset($this->startDates[$person->getId()])){
                return $this->startDates[$person->getId()];
            }
            else{
                return "";
            }
        }
        else{
            return "";
        }
    }
    
    // Returns the endDates for the Project
    function getEndDates(){
        if($this->endDates == null){
            $this->endDates = array();
            if(!$this->clear){
                $preds = $this->getPreds();
                foreach($preds as $pred){
                    foreach($pred->getEndDates() as $uId => $date){
                        $this->endDates[$uId] = $date;
                    }
                }
            }
            $sql = "SELECT user_id, end_date 
                    FROM grand_project_members 
                    WHERE project_id = '{$this->id}'";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->endDates[$row['user_id']] = $row['end_date'];
            }
        }
        return $this->endDates;
    }
    
    // Returns the startDate for the given Person
    function getEndDate($person){
        if($person != null && $person instanceof Person){
            $this->getEndDates();
            if(isset($this->endDates[$person->getId()])){
                if(($this->getDeleted() > "0000-00-00 00:00:00" && 
                    $this->endDates[$person->getId()] == "0000-00-00 00:00:00") ||
                   ($this->getDeleted() <= $this->endDates[$person->getId()] &&
                    $this->getDeleted() != "0000-00-00 00:00:00")){
                    return $this->getDeleted();
                }
                else{
                    return $this->endDates[$person->getId()];
                }
            }
            else{
                return "";
            }
        }
        else{
            return "";
        }
    }
    
    // Returns the current milestones of this project
    // If $history is set to true, all the milestones ever for this project are included
    function getMilestones($history=false){
        if($this->milestones != null && !$history){
            return $this->milestones;
        }
        $milestones = array();
        $milestonesIds = array();
        if(!$this->clear){
            $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getMilestones($history) as $milestone){
                    if(isset($milestoneIds[$milestone->getMilestoneId()])){
                        continue;
                    }
                    $milestoneIds[$milestone->getMilestoneId()] = true;
                    $milestones[] = $milestone;
                }
            }
        }
        $sql = "SELECT DISTINCT milestone_id
                FROM grand_milestones
                WHERE project_id = '{$this->id}'";
        if(!$history){
            $sql .= "\nAND start_date > end_date
                     AND status != 'Abandoned' AND status != 'Closed'";
        }
        $sql .= "\nORDER BY projected_end_date";
        $data = DBFunctions::execSQL($sql);
        
        foreach($data as $row){
            if(isset($milestoneIds[$row['milestone_id']])){
                continue;
            }
            $milestone = Milestone::newFromId($row['milestone_id']);
            $milestoneIds[$milestone->getMilestoneId()] = true;
            $milestones[] = $milestone;
        }
        
        if(!$history){
            $this->milestones = $milestones;
        }
        return $milestones;
    }

    // Returns the past milestones of this project
    function getPastMilestones(){
        $milestoneIds = array();
        $milestones = array();
        if(!$this->clear){
            $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getPastMilestones() as $milestone){
                    if(isset($milestoneIds[$milestone->getMilestoneId()])){
                        continue;
                    }
                    $milestoneIds[$milestone->getMilestoneId()] = true;
                    $milestones[] = $milestone;
                }
            }
        }
        $sql = "SELECT DISTINCT milestone_id
                FROM grand_milestones
                WHERE project_id = '{$this->id}'
                AND status IN ('Abandoned','Closed') 
                ORDER BY projected_end_date";
        
        $data = DBFunctions::execSQL($sql);
        foreach($data as $row){
            if(isset($milestoneIds[$row['milestone_id']])){
                continue;
            }
            $milestone = Milestone::newFromId($row['milestone_id']);
            $milestoneIds[$milestone->getMilestoneId()] = true;
            $milestones[] = $milestone;
        }
        return $milestones;
    }

    // Determine whether this is a Sub-Project
    function isSubProject(){
        return ($this->parentId != 0);
    }

    /**
     * Returns an array of this Project's current Sub-Projects
     * @return array An array of this Project's current Sub-Projects
     */
    function getSubProjects(){
        $subprojects = array();

        $data = DBFunctions::select(array('grand_project'),
                                    array('*'),
                                    array('parent_id' => EQ($this->id)),
                                    array('name' => 'ASC'));
        foreach($data as $row){
            $subproject = Project::newFromId($row['id']);
            if(!$subproject->isDeleted()){
                $subprojects[] = $subproject;
            }
        }
        return $subprojects;
    }
    
    /**
     * Returns an array of Sub-Projects during the given date range
     * @param string $startDate The start date of the range
     * @param string $endDate The end date of the range
     * @return array An array of Sub-Projects during the given date range
     */
    function getSubProjectsDuring($startDate, $endDate){
        $subprojects = array();

        $data = DBFunctions::select(array('grand_project'),
                                    array('*'),
                                    array('parent_id' => EQ($this->id)),
                                    array('name' => 'ASC'));
        foreach($data as $row){
            $subproject = Project::newFromId($row['id']);
            if((($subproject->deleted &&
                 $subproject->effectiveDate <= $endDate &&
                 $subproject->effectiveDate >= $startDate) ||
                !$subproject->deleted) && 
               $subproject->getCreated() <= $endDate){
                $subprojects[] = $subproject;
            }
        }
        return $subprojects;
    }
    
    function getParent(){
        return Project::newFromId($this->parentId);
    }
    
    // Returns an array of milestones where all the milestones which were active at any time during the given year
    function getMilestonesDuring($year='0000'){
        if($year == '0000'){
            $year = date('Y');
        }
        
        $startRange = $year.'01-01 00:00:00';
        $endRange = $year.'-12-31 23:59:59';
        
        $milestones = array();
        $milestoneIds = array();
        if(!$this->clear){
            $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getMilestonesDuring($year) as $milestone){
                    if(isset($milestoneIds[$milestone->getMilestoneId()])){
                        continue;
                    }
                    $milestoneIds[$milestone->getMilestoneId()] = $milestone->getMilestoneId();
                    $milestones[] = $milestone;
                }
            }
        }
        $sql = "SELECT MAX(id) as max_id, milestone_id
                FROM grand_milestones
                WHERE project_id ='{$this->id}'
                AND milestone_id NOT IN ('".implode("','", $milestoneIds)."')
                GROUP BY milestone_id
                ORDER BY milestone_id";
        $data = DBFunctions::execSQL($sql);
        foreach ($data as $row){
            $max_id = $row['max_id'];
            $sql2 = "SELECT milestone_id
                     FROM grand_milestones
                     WHERE id = '{$max_id}'
                     AND ( 
                        ( (end_date != '0000-00-00 00:00:00') AND
                        (( start_date BETWEEN '$startRange' AND '$endRange' ) || ( end_date BETWEEN '$startRange' AND '$endRange' ) || (start_date <= '$startRange' AND end_date >= '$endRange') ))
                        OR
                        ( (end_date = '0000-00-00 00:00:00') AND
                        ((start_date <= '$endRange')))
                     )
                     AND ( (start_date > end_date AND status != 'Closed' AND status != 'Abandoned') OR ( $year BETWEEN YEAR(start_date)  AND YEAR(end_date) ) )";
            
            $data2 = DBFunctions::execSQL($sql2);
            if( count($data2) > 0 ){
                $row2 = $data2[0];
                if(isset($milestoneIds[$row2['milestone_id']])){
                    continue;
                }
                $milestoneIds[$row2['milestone_id']] = true;
                $milestones[] = Milestone::newFromId($row2['milestone_id']);
            }
        }
        return $milestones;
    }
    
    function getGoalsDuring($year){
        $milestones = array();
        $milestoneIds = array();
        if(!$this->clear){
            $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getGoalsDuring($year) as $milestone){
                    if(isset($milestoneIds[$milestone->getMilestoneId()])){
                        continue;
                    }
                    $milestoneIds[$milestone->getMilestoneId()] = $milestone->getMilestoneId();
                    $milestones[] = $milestone;
                }
            }
        }
        $sql = "SELECT MAX(id) as max_id, milestone_id
                FROM grand_milestones
                WHERE project_id ='{$this->id}'
                AND milestone_id NOT IN ('".implode("','", $milestoneIds)."')
                GROUP BY milestone_id
                ORDER BY milestone_id";
        $data = DBFunctions::execSQL($sql);
        foreach ($data as $row){
            $max_id = $row['max_id'];
            $sql2 = "SELECT milestone_id
                     FROM grand_milestones
                     WHERE id = '{$max_id}'
                     AND (projected_end_date LIKE '%{$year}%')";
            
            $data2 = DBFunctions::execSQL($sql2);
            if(count($data2) > 0){
                $row2 = $data2[0];
                if(isset($milestoneIds[$row2['milestone_id']])){
                    continue;
                }
                $milestoneIds[$row2['milestone_id']] = true;
                $milestones[] = Milestone::newFromId($row2['milestone_id']);
            }
        }
        return $milestones;
    }
    
    function getAllocatedBudget($year){
        global $config;
        $projectBudget = null;
        if(isset($this->budgets['s'.$year])){
            return unserialize($this->budgets['s'.$year]);
        }
        $projectBudget = array();
        $nameBudget = array();
        $projectNames = array($this->name);
        if(!$this->clear){
            foreach($this->getAllPreds() as $pred){
                $projectNames[] = $pred->getName();
            }
        }
        foreach($this->getAllPeopleDuring(null, ($year+1)."-00-00 00:00:00", ($year + 2)."-00-00 00:00:00") as $member){
            if($member->isRole(PNI, ($year+1)."-00-00 00:00:00", ($year + 2)."-00-00 00:00:00") || 
               $member->isRole(CNI, ($year+1)."-00-00 00:00:00", ($year + 2)."-00-00 00:00:00")){
                $budget = $member->getAllocatedBudget($year);
                if($budget != null){
                    $budget = $budget->copy();
                    if(count($projectBudget) == 0){
                        $nameBudget[] = new Budget(array(array(HEAD1),
                                                         array(BLANK)),
                                                   array(array("Name of network investigator submitting request:"),
                                                         array("")));
                        $projectBudget[] = new Budget(array(array(HEAD1),
                                                           array(HEAD1),
                                                           array(HEAD2),
                                                           array(HEAD2),
                                                           array(HEAD2),
                                                           array(HEAD2),
                                                           array(HEAD1),
                                                           array(HEAD2),
                                                           array(HEAD2),
                                                           array(HEAD2),
                                                           array(HEAD1),
                                                           array(HEAD1),
                                                           array(HEAD1),
                                                           array(HEAD2),
                                                           array(HEAD2),
                                                           array(HEAD2)),
                                                     array(array("Budget Categories for April 1, ".($year+1).", to March 31, ".($year+2).""),
                                                           array("1) Salaries and stipends"),
                                                           array("a) Graduate students"),
                                                           array("b) Postdoctoral fellows"),
                                                           array("c) Technical and professional assistants"),
                                                           array("d) Undergraduate students"),
                                                           array("2) Equipment"),
                                                           array("a) Purchase or rental"),
                                                           array("b) Maintenance costs"),
                                                           array("c) Operating costs"),
                                                           array("3) Materials and supplies"),
                                                           array("4) Computing costs"),
                                                           array("5) Travel expenses"),
                                                           array("a) Field trips"),
                                                           array("b) Conferences"),
                                                           array("c) {$config->getValue('networkName')} annual conference")));
                    }
                    $nBudget = $budget->copy()->limit(0, 1)->select(V_PERS_NOT_NULL)->union(new Budget());
                    $pBudget = $budget->copy()->select(V_PROJ, $projectNames)->limit(6, 16);
                    if($pBudget->nRows()*$pBudget->nCols() > 0){
                        $nameBudget[] = $nBudget;
                        $projectBudget[] = $pBudget;
                    }
                }
            }
        }
        $nameBudget = Budget::join_tables($nameBudget)->join(Budget::union_tables(array(new Budget(), new Budget())));
        
        $projectBudget = Budget::join_tables($projectBudget);
        if($projectBudget != null){
            $this->budgets['s'.$year] = $nameBudget->union($projectBudget->cube());
            $this->budgets['s'.$year] = serialize($this->budgets['s'.$year]);
            return unserialize($this->budgets['s'.$year]);
        }
        else{
            return null;
        }
    }
    
    function getRevisedBudget($year){
        // This is a special Budget with no strongly defined structure, 
        // though should ideally look exactly like the requested project budget.
        // After reading the budget, it attempts to change the structure to show 
        // the same was as the other requested project budgets
        $rep_addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, LDR_BUD_REVISED, 0);
        $budget_blob = new ReportBlob(BLOB_EXCEL, $year, 0, $this->getId());
        $budget_blob->load($rep_addr);
        $data = $budget_blob->getData();
        if(!empty($data)){
            $budget = new Budget($data);
            $newStructure = array();
            foreach($budget->structure as $rowN => $row){
                if($rowN == $budget->nRows()-1){
                    continue;
                }
                foreach($row as $colN => $col){
                    if($colN == $budget->nCols()-1){
                        continue;
                    }
                    if($colN == 0){
                        $type = HEAD1;
                        switch($rowN){
                            case 0: $type = HEAD1; break;
                            case 1: $type = HEAD1; break;
                            case 2: $type = HEAD1; break;
                            case 3: $type = HEAD2; break;
                            case 4: $type = HEAD2; break;
                            case 5: $type = HEAD2; break;
                            case 6: $type = HEAD2; break;
                            case 7: $type = HEAD1; break;
                            case 8: $type = HEAD2; break;
                            case 9: $type = HEAD2; break;
                            case 10: $type = HEAD2; break;
                            case 11: $type = HEAD1; break;
                            case 12: $type = HEAD1; break;
                            case 13: $type = HEAD1; break;
                            case 14: $type = HEAD2; break;
                            case 15: $type = HEAD2; break;
                            case 16: $type = HEAD2; break;
                        }
                        $newStructure[$rowN][$colN] = $type;
                    }
                    else if($rowN == 0){
                        $newStructure[$rowN][$colN] = V_PERS;
                    }
                    else{
                        $newStructure[$rowN][$colN] = MONEY;
                    }
                }
            }
            $budget = new Budget("XLS", $newStructure, $data);
            $budget = $budget->cube();
            return $budget;
        }
        return null;
    }
    
    function getRequestedBudget($year, $role='all'){
        global $config;
        $projectBudget = null;
        if(isset($this->budgets['r'.$role.$year])){
            return unserialize($this->budgets['r'.$role.$year]);
        }
        $year_fr = $year+1;
        $year_to = $year+2;
        $projectBudget = array();
        $nameBudget = array();
        $projectNames = array($this->name);
        if(!$this->clear){
            foreach($this->getAllPreds() as $pred){
                $projectNames[] = $pred->getName();
            }
        }

        $alreadySeen = array();
        $nameBudget[] = new Budget(array(array(HEAD1)),
                                   array(array("Name of network investigator submitting request:")));
        $projectBudget[] = new Budget(array(array(HEAD1),
                                           array(HEAD1),
                                           array(HEAD2),
                                           array(HEAD2),
                                           array(HEAD2),
                                           array(HEAD2),
                                           array(HEAD1),
                                           array(HEAD2),
                                           array(HEAD2),
                                           array(HEAD2),
                                           array(HEAD1),
                                           array(HEAD1),
                                           array(HEAD1),
                                           array(HEAD2),
                                           array(HEAD2),
                                           array(HEAD2)),
                                     array(array("Budget Categories for April 1, {$year_fr}, to March 31, {$year_to}"),
                                           array("1) Salaries and stipends"),
                                           array("a) Graduate students"),
                                           array("b) Postdoctoral fellows"),
                                           array("c) Technical and professional assistants"),
                                           array("d) Undergraduate students"),
                                           array("2) Equipment"),
                                           array("a) Purchase or rental"),
                                           array("b) Maintenance costs"),
                                           array("c) Operating costs"),
                                           array("3) Materials and supplies"),
                                           array("4) Computing costs"),
                                           array("5) Travel expenses"),
                                           array("a) Field trips"),
                                           array("b) Conferences"),
                                           array("c) {$config->getValue('networkName')} annual conference")));
        foreach($this->getAllPeopleDuring(null, ($year+1).NCE_START_MONTH, ($year+2).NCE_END_MONTH) as $member){
            $isPNI = $member->isRoleDuring(PNI, ($year+1).NCE_START_MONTH, ($year+2).NCE_END_MONTH);
            $isCNI = $member->isRoleDuring(CNI, ($year+1).NCE_START_MONTH, ($year+2).NCE_END_MONTH);
            if(($role == PNI && $isPNI && !$isCNI) || 
               ($role == CNI && $isCNI && !$isPNI) ||
               ($role == 'all' && ($isPNI || $isCNI))){
                if(isset($alreadySeen[$member->getId()])){
                    continue;
                }
                $alreadySeen[$member->getId()] = true;
                $budgets = array();
                $budgets[] = $member->getRequestedBudget($year);
                
                foreach($budgets as $budget){
                    if($budget != null){
                        $nBudget = $budget->copy()->limit(0, 1)->select(V_PERS_NOT_NULL)->union(new Budget());
                        $pBudget = $budget->copy()->select(V_PROJ, $projectNames)->limit(6, 16);
                        if($pBudget->nRows()*$pBudget->nCols() > 0){
                            $nameBudget[] = $nBudget;
                            $projectBudget[] = $pBudget;
                        }
                    }
                }
            }
        }
        $nameBudget = Budget::join_tables($nameBudget)->join(Budget::union_tables(array(new Budget(), new Budget())));
        $projectBudget = Budget::join_tables($projectBudget);
        if($projectBudget != null){
            $this->budgets['r'.$role.$year] = $nameBudget->union($projectBudget->cube());
            $this->budgets['r'.$role.$year] = serialize($this->budgets['r'.$role.$year]);
            return unserialize($this->budgets['r'.$role.$year]);
        }
        else{
            return null;
        }
    }
}

?>
