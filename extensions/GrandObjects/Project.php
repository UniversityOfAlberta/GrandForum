<?php

/**
 * @package GrandObjects
 */
 
define('PROJECT_FILE_COUNT', 1);

class Project extends BackboneModel {

    static $cache = array();
    static $projectCache = array();
    static $projectDataCache = array();

    var $id;
    var $evolutionId;
    var $fullName;
    var $name;
    var $status;
    var $creationDate;
    var $startDate;
    var $endDate;
    var $type;
    var $private;
    var $parentId;
    var $people;
    var $phase;
    var $contributions;
    var $multimedia;
    var $startDates;
    var $endDates;
    var $comments;
    var $milestones;
    var $budgets;
    var $deleted;
    var $effectiveDate;
    var $themes;
    var $subProjects;
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
        global $config;
        $me = Person::newFromWgUser();
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        if($id == -1){
            $project = new Project(array());
            $project->id = $id;
            $project->name = "Other";
            $project->fullName = "Other";
            $project->clear = true;
            self::$cache[$project->id] = &$project;
            self::$cache[$project->name] = &$project;
            return $project;
        }
        self::generateProjectCache();
        $data = @self::$projectDataCache[$id.'_'];
        while($data != null && $data['new_id'] != $data['id'] && $data['clear'] != 1){
            // Find most recent version of the project
            $data = @self::$projectDataCache[$data['new_id'].'_'];
        }
        if($data != null && (($me->isLoggedIn() && !$me->isCandidate()) || ($data['status'] != 'Proposed' && $data['private'] != 1))){
            $project = new Project(array($data));
            self::$cache[$project->id] = &$project;
            self::$cache[$project->name] = &$project;
            return $project;
        }
        else {
            return null;
        }
    }
    
    /**
     * Returns a new Project from the given name
     * @param string $name The name of the Project
     * @return Project The Project with the given name
     */
    static function newFromName($name){
        global $config;
        $me = Person::newFromWgUser();
        if(isset(self::$cache[$name])){
            return self::$cache[$name];
        }
        if($name == "Other"){
            return Project::newFromId(-1);
        }
        self::generateProjectCache();
        $data = @self::$projectDataCache['h_'.trim(strtolower($name))];
        if ($data != null){
            $project = new Project(array($data));
            $succs = $project->getAllSuccs();
            if(count($succs) > 0){
                $project = $succs[count($succs)-1];
                self::$cache[$project->getId()] = &$project;
                self::$cache[$name] = &$project;
                return $project;
            }
            else if(($me->isLoggedIn() && !$me->isCandidate()) || ($data['status'] != 'Proposed' && $data['private'] != 1)){
                $project = new Project(array($data));
            }
            else{
                return null;
            }
            //self::$cache[$project->id] = &$project;
            //self::$cache[$project->name] = &$project;
            return $project;
        }
        else {
            return null;
        }
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
        if($id == -1){
            return Project::newFromId($id);
        }
        self::generateProjectCache();
        $data = @self::$projectDataCache[$id.'_'.$evolutionId];
        if($data != null && (($me->isLoggedIn() && !$me->isCandidate()) || ($data['status'] != 'Proposed' && $data['private'] != 1))){
            $project = new Project(array($data));
            if($evolutionId != null){
                $project->evolutionId = $evolutionId;
            }
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
        global $config;
        $me = Person::newFromWgUser();
        if(isset(self::$cache['h_'.$name])){
            return self::$cache['h_'.$name];
        }
        if($name == "Other"){
            return Project::newFromName($name);
        }
        self::generateProjectCache();
        $data = @self::$projectDataCache['h_'.trim(strtolower($name))];
        if ($data != null && (($me->isLoggedIn() && !$me->isCandidate()) || ($data['status'] != 'Proposed' && $data['private'] != 1))){
            $project = new Project(array($data));
            self::$cache['h_'.$name] = &$project;
            return $project;
        }
    }
    
    function generateProjectCache(){
        if(count(self::$projectDataCache) == 0){
            $data = DBFunctions::execSQL("SELECT p.id, p.name, p.phase, p.parent_id, e.new_id, e.action, e.effective_date, e.id as evolutionId, e.clear, s.type, s.status, s.start_date, s.end_date, s.private
                                          FROM grand_project p, grand_project_evolution e, grand_project_status s
                                          WHERE (e.new_id = p.id OR e.project_id = p.id)
                                          AND s.evolution_id = e.id
                                          ORDER BY e.id DESC, p.id DESC");
            foreach($data as $row){
                if(!isset(self::$projectDataCache[$row['id'].'_'])){
                    // This is the most recent evolution
                    $row['old_ids'] = array();
                    $row['new_ids'] = array();
                    self::$projectDataCache[$row['id'].'_'] = $row;
                    self::$projectDataCache['h_'.trim(strtolower($row['name']))] = $row;
                }
                self::$projectDataCache[$row['id'].'_'.$row['evolutionId']] = $row;
                if($row['id'] != $row['new_id']){
                    // Was evolved to a different project
                    self::$projectDataCache[$row['new_id'].'_']['old_ids'][] = $row['id'];
                    self::$projectDataCache[$row['id'].'_']['new_ids'][] = $row['new_id'];
                }
            }
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
        if(!isset(self::$projectCache[$subProjects[1]])){
            $data = DBFunctions::select(array('grand_project'),
                                        array('id', 'name'),
                                        array('parent_id' => $subProjects),
                                        array('name' => 'ASC'));
            $projects = array();
            foreach($data as $row){
                $project = Project::newFromId($row['id']);
                if($project != null && $project->getName() != ""){
                    if(!isset($projects[$project->name]) && !$project->isDeleted() && (($me->isLoggedIn() && !$me->isCandidate()) || ($project->getStatus() != 'Proposed' && !$project->isPrivate()))){
                        $projects[$project->getName()] = $project;
                    }
                }
            }
            knatsort($projects);
            $projects = array_values($projects);
            self::$projectCache[$subProjects[1]] = $projects;
        }
        return self::$projectCache[$subProjects[1]];
    }
    
    static function getAllProjectsDuring($startDate, $endDate, $subProjects=false){
        $me = Person::newFromWgUser();
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
                        if(substr($project->getCreated(), 0, 10) <= $endDate && (($me->isLoggedIn() && !$me->isCandidate()) || ($project->getStatus() != 'Proposed') && !$project->isPrivate())){
                            $projects[$project->getName()] = $project;
                        }
                    }
                }
            }
        }
        knatsort($projects);
        $projects = array_values($projects);
        return $projects;
    }
    
    // Same as getAllProjects, but will also return deleted projects
    static function getAllProjectsEver($subProjects=false, $historic=false){
        $me = Person::newFromWgUser();
        if($subProjects == false){
            $subProjects = EQ(0);
        }
        else{
            $subProjects = LIKE("%");
        }
        $data = DBFunctions::select(array('grand_project'),
                                    array('id'),
                                    array('parent_id' => $subProjects),
                                    array('name' => 'ASC'));
        $projects = array();
        foreach($data as $row){
            if($historic){
                $project = Project::newFromHistoricId($row['id']);
            }
            else{
                $project = Project::newFromId($row['id']);
            }
            if($project != null && $project->getName() != ""){
                if(!isset($projects[$project->name]) && (($me->isLoggedIn() && !$me->isCandidate()) || ($project->getStatus() != 'Proposed' && !$project->isPrivate()))){
                    $projects[$project->getName()] = $project;
                }
            }
        }
        knatsort($projects);
        $projects = array_values($projects);
        return $projects;
    }
    
    static function areThereDeletedProjects(){
        $me = Person::newFromWgUser();
        $data = $data = DBFunctions::select(array('grand_project_status'),
                                            array('id', 'private'),
                                            array('status' => EQ('Ended')));
        foreach($data as $key => $row){
            if(!(($me->isLoggedIn() && !$me->isCandidate()) || $row['private'] != 1)){
                unset($data[$key]);
            }
        }
        return (count($data) > 0);
    }
    
    static function areThereAdminProjects(){
        $me = Person::newFromWgUser();
        $data = $data = DBFunctions::select(array('grand_project_status'),
                                            array('id', 'project_id', 'private'),
                                            array('type' => EQ('Administrative')));
        foreach($data as $key => $row){
            $project = Project::newFromId($row['project_id']);
            if($project == null || $project->getType() != "Administrative" && !(($me->isLoggedIn() && !$me->isCandidate()) || $row['private'] != 1)){
                unset($data[$key]);
            }
        }
        return (count($data) > 0);
    }

    static function areThereNonAdminProjects(){
        $me = Person::newFromWgUser();
        $data = $data = DBFunctions::select(array('grand_project_status'),
                                            array('id', 'project_id', 'private'),
                                            array('type' => NEQ('Administrative')));
        foreach($data as $key => $row){
            $project = Project::newFromId($row['project_id']);
            if($project == null || $project->getType() == "Administrative" && !(($me->isLoggedIn() && !$me->isCandidate()) || $row['private'] != 1)){
                unset($data[$key]);
            }
        }
        return (count($data) > 0);
    }
    
    static function areThereInnovationHubs(){
        $me = Person::newFromWgUser();
        $data = $data = DBFunctions::select(array('grand_project_status'),
                                            array('id', 'project_id', 'private'),
                                            array('type' => EQ('Innovation Hub')));
        foreach($data as $key => $row){
            $project = Project::newFromId($row['project_id']);
            if($project == null || $project->getType() != "Innovation Hub" && !(($me->isLoggedIn() && !$me->isCandidate()) || $row['private'] != 1)){
                unset($data[$key]);
            }
        }
        return (count($data) > 0);
    }
    
    static function areThereProposedProjects(){
        $me = Person::newFromWgUser();
        $data = $data = DBFunctions::select(array('grand_project_status'),
                                            array('id', 'project_id', 'private'),
                                            array('status' => EQ('Proposed')));
        foreach($data as $key => $row){
            $project = Project::newFromId($row['project_id']);
            if($project == null || $project->getStatus() != "Proposed" && !(($me->isLoggedIn() && !$me->isCandidate()) || $row['private'] != 1)){
                unset($data[$key]);
            }
        }
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
            $this->startDate = $data[0]['start_date'];
            $this->endDate = $data[0]['end_date'];
            $this->type = $data[0]['type'];
            $this->private = $data[0]['private'];
            $this->phase = $data[0]['phase'];
            $this->parentId = $data[0]['parent_id'];
            $this->succ = false;
            $this->preds = false;
            $this->clear = ($data[0]['clear'] == 1);
            if($this->status == "Ended"){
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
        $leaders = $this->getLeaders();
        $leads = array();
        foreach($leaders as $leader){
            $leads[] = array('id' => $leader->getId(),
                             'name' => $leader->getNameForForms(),
                             'url' => $leader->getUrl());
        }
        $subProjects = $this->getSubProjects();
        $subs = array();
        foreach($subProjects as $sub){
            $subs[] = array('id' => $sub->getId(),
                            'name' => $sub->getName(),
                            'url' => $sub->getUrl());
        }
        $challenges = new Collection($this->getChallenges());
        $theme = implode(", ", $challenges->pluck('getAcronym()'));
        $themeName = implode(", ", $challenges->pluck('getName()'));
        $images = array();
        for($n=1;$n<=PROJECT_FILE_COUNT;$n++){
            $image = $this->getImage($n);
            if($image != ""){
                $images[] = $image;
            }
        }
        $array = array('id' => $this->getId(),
                       'name' => $this->getName(),
                       'fullname' => $this->getFullName(),
                       'description' => $this->getDescription(),
                       'longDescription' => $this->getLongDescription(),
                       'website' => $this->getWebsite(),
                       'status' => $this->getStatus(),
                       'type' => $this->getType(),
                       'private' => $this->isPrivate(),
                       'theme' => $theme,
                       'themeName' => $themeName,
                       'phase' => $this->getPhase(),
                       'url' => $this->getUrl(),
                       'deleted' => $this->isDeleted(),
                       'leaders' => $leads,
                       'subprojects' => $subs,
                       'startDate' => $this->getCreated(),
                       'endDate' => $this->getDeleted(),
                       'images' => $images);
        return $array;
    }
    
    function create(){
    
    }
    
    function update(){
        if($this->userCanEdit()){
            // Updating Acronym
            if(isset($_POST['acronym'])){ // ??? Might be wrong
                $testProj = Project::newFromName($_POST['acronym']);
                if(preg_match("/^[0-9À-Ÿa-zA-Z\-\. ]+$/", $this->name) &&
                   ($testProj == null || $testProj->getId() == 0)){
                    DBFunctions::update('grand_project',
		                                array('name' => $this->getName()),
		                                array('id' => $this->getId()));
		            DBFunctions::update('mw_an_extranamespaces',
		                                array('nsName' => str_replace(' ', '_', $this->getName())),
		                                array('nsId' => $this->getId()));
                }
                else{
                    // Name isn't valid, revert
                    $data = DBFunctions::select(array('grand_project'),
                                                array('name'),
                                                array('id' => EQ($this->getId())));
                    $this->name = @$data[0]['name'];
                }
            }
            // Updating Theme
            $themes = $this->getChallenges();
            DBFunctions::delete('grand_project_challenges',
                                array('project_id' => EQ($this->getId())));
            foreach($themes as $theme){
                DBFunctions::insert('grand_project_challenges',
                                    array('project_id' => $this->getId(),
                                          'challenge_id' => $theme->getId()));
            }
            DBFunctions::commit();
            Project::$cache = array();
            Project::$projectCache = array();
            Project::$projectDataCache = array();
        }
        return $this;
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
    
    /**
     * Returns a shortened version of the full name (first 3 words, or text before a colon)
     */
    function getShortFullName(){
        $pName = $this->getFullName();
        $pName = explode(":", $pName);
        $pName = $pName[0];
        $pName = explode(" ", $pName);
        $pName = @"{$pName[0]} {$pName[1]} {$pName[2]}";
        return trim($pName);
    }
    
    // Returns the status of this Project
    function getStatus(){
        return $this->status;
    }
    
    // Returns the type of this Project
    function getType(){
        return $this->type;
    }
    
    function isPrivate(){
        return $this->private;
    }
    
    /**
     * Returns whether or not the given feature is frozen
     * @param string $feature The feature to check
     * @returns boolean Whether or not the given feature is frozen
     */
    function isFeatureFrozen($feature){
        $freeze = Freeze::newFromProjectFeature($this, $feature);
        return ($freeze != null && $freeze->getId() != "");
    }
    
    // Returns the phase of this Project
    function getPhase(){
        return $this->phase;
    }
    
    // Returns the Predecessors of this Project
    function getPreds(){
        if($this->preds === false){
            self::generateProjectCache();
            $data = self::$projectDataCache[$this->id.'_']['old_ids'];
            $this->preds = array();
            foreach($data as $old_id){
                $pred = Project::newFromHistoricId($old_id);
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
            self::generateProjectCache();
            $data = self::$projectDataCache[$this->id.'_']['new_ids'];
            $this->succ = array();
            if(count($data) > 0){
                foreach($data as $new_id){
                    $this->succ[] = Project::newFromHistoricId($new_id);
                }
            }
        }
        return $this->succ;
    }
    
    // Returns the full Successors history of this Project
    // NOTE: this is not cached, so don't call it too much
    function getAllSuccs(){
        $succs = array();
        foreach($this->getSuccs() as $succ){
            if($succ->getId() != $this->getId()){
                $succs = array_merge($succs, array_merge(array($succ), $succ->getAllSuccs()));
            }
        }
        return $succs;
    }  
    
    // Returns the url of this Project's profile page
    function getUrl(){
        global $wgServer, $wgScriptPath;
        if($this->id == -1){
            return "{$wgServer}{$wgScriptPath}/index.php";
        }
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
        if($this->getStatus() == "Ended" || strcmp($this->effectiveDate, date('Y-m-d H:i:s')) <= 0){
            return $this->deleted;
        }
        return false;
    }
    
    // Returns when this project was initially created
    function getCreated(){
        if($this->creationDate != null){
            return $this->creationDate;
        }
        if(!$this->clear){
            $preds = $this->getPreds();
            if(count($preds) == 0){
                $data = DBFunctions::select(array('grand_project_evolution'),
                                            array('effective_date'),
                                            array('new_id' => $this->getId(),
                                                  'action' => 'CREATE'));
                $this->creationDate = @$data[0]['effective_date'];
            }
            else{
                foreach($preds as $pred){
                    $this->creationDate = $pred->getCreated();
                }
            }
        }
        else{
            $data = DBFunctions::select(array('grand_project_evolution'),
                                        array('effective_date'),
                                        array('new_id' => $this->getId(),
                                              'action' => 'CREATE'));
            $this->creationDate = @$data[0]['effective_date'];
        }
        return $this->creationDate;
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
    
    function getStartDate(){
        $date = $this->startDate;
        if($date == "0000-00-00 00:00:00"){
            $date = $this->getCreated();
        }
        return $date;
    }
    
    function getEndDate(){
        $date = $this->endDate;
        if($date == "0000-00-00 00:00:00"){
            $date = $this->getEffectiveDate();
        }
        return $date;
    }
    
    // Returns an array of Person objects which represent
    // The researchers who are in this project.
    // If $filter is included, only users of that type will be selected
    function getAllPeople($filter = null){
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
            $sql = "SELECT r.user_id, u.user_name, SUBSTR(u.user_name, LOCATE('.', u.user_name) + 1) as last_name
                    FROM grand_roles r, grand_role_projects rp, mw_user u
                    WHERE (r.end_date > CURRENT_TIMESTAMP OR r.end_date = '0000-00-00 00:00:00')
                    AND r.user_id = u.user_id
                    AND r.id = rp.role_id
                    AND rp.project_id = '{$this->id}'
                    AND u.`deleted` != '1'
                    ORDER BY u.last_name ASC";
            $data = DBFunctions::execSQL($sql);
            Cache::store("project{$this->id}_people", $data);
        }
        else{
            $data = Cache::fetch("project{$this->id}_people");
        }
        foreach($data as $row){
            $id = $row['user_id'];
            $person = Person::newFromId($id);
            if($person->getId() != 0){
                if($filter == PL){
                    if($person->isRole(PL, $this)){
                        $people[$person->getId()] = $person;
                    }
                }
                else if(($filter == null || 
                         ($person->isRole($filter, $this) && !$person->isRole(PL, $this)) || 
                         ($person->isRole($filter."-Candidate", $this) && !$person->isRole(PL, $this) && $this->getStatus() == "Proposed")) && 
                        !$person->isRole(ADMIN)){
                    $people[$person->getId()] = $person;
                }
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
            $sql = "SELECT r.user_id, u.user_name, SUBSTR(u.user_name, LOCATE('.', u.user_name) + 1) as last_name
                    FROM grand_roles r, grand_role_projects rp, mw_user u
                    WHERE ( 
                    ( (r.end_date != '0000-00-00 00:00:00') AND
                    (( r.start_date BETWEEN '$startRange' AND '$endRange' ) || ( r.end_date BETWEEN '$startRange' AND '$endRange' ) || (r.start_date <= '$startRange' AND r.end_date >= '$endRange') ))
                    OR
                    ( (r.end_date = '0000-00-00 00:00:00') AND
                    ((r.start_date <= '$endRange')))
                    )
                    AND r.user_id = u.user_id
                    AND r.id = rp.role_id
                    AND rp.project_id = '{$this->id}'
                    AND u.`deleted` != '1'
                    ORDER BY u.last_name ASC";
            $data = DBFunctions::execSQL($sql);
            Cache::store("project{$this->id}_peopleDuring$startRange.$endRange", $data);
        }
        else{
            $data = Cache::fetch("project{$this->id}_peopleDuring$startRange.$endRange");
        }
        foreach($data as $row){
            $id = $row['user_id'];
            $person = Person::newFromId($id);
            if($person->getId() != 0){
                if($filter == PL){
                    if($person->isRoleDuring(PL, $startRange, $endRange, $this)){
                        $people[$person->getId()] = $person;
                    }
                }
                else if(($filter == null || 
                        ($person->isRoleDuring(str_replace("Former-", "", $filter), $startRange, $endRange, $this) && !$person->isRole(PL, $this))) && 
                        ($includeManager || !$person->isRoleDuring(MANAGER, $startRange, $endRange))){
                    if(strstr($filter, "Former-") !== false && $person->isRole(str_replace("Former-", "", $filter))){
                        // Exclude people if they are still a member of the role if $filter contains 'Former-'
                        continue;
                    }
                    $people[$person->getId()] = $person;
                }
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
        $sql = "SELECT r.user_id, u.user_name, SUBSTR(u.user_name, LOCATE('.', u.user_name) + 1) as last_name
                FROM grand_roles r, grand_role_projects rp, mw_user u
                WHERE (('$date' BETWEEN r.start_date AND r.end_date ) OR (r.start_date <= '$date' AND r.end_date = '0000-00-00 00:00:00'))
                AND r.user_id = u.user_id
                AND r.id = rp.role_id
                AND rp.project_id = '{$this->id}'
                AND u.`deleted` != '1'
                ORDER BY u.last_name ASC";
        $data = DBFunctions::execSQL($sql);
        foreach($data as $row){
            $id = $row['user_id'];
            $person = Person::newFromId($id);
            if($person->getId() != 0){
                if($filter == PL){
                    if($person->isRoleOn(PL, $date, $this)){
                        $people[$person->getId()] = $person;
                    }
                }
                else if(($filter == null || 
                         ($person->isRoleOn($filter, $date, $this) && !$person->isRoleOn(PL, $date, $this))) && 
                        ($includeManager || !$person->isRoleOn(MANAGER, $date))){
                    $people[$person->getId()] = $person;
                }
            }
        }
        return $people;
    }
    
    /**
     * Returns the path to a photo of this Project if it exists
     * @param integer $n Which image to get
     * @return string The path to a photo of this Project
     */
    function getImage($n){
        global $wgServer, $wgScriptPath;
        if(file_exists("Photos/{$this->getId()}_{$n}.jpg")){
            return "$wgServer$wgScriptPath/Photos/{$this->getId()}_{$n}.jpg?".filemtime("Photos/{$this->getId()}_{$n}.jpg");
        }
        return "";
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
    function getContributionsDuring($startYear, $endYear=null){
        if($endYear == null){
            $endYear = $startYear;
        }
        $contribs = array();
        foreach($this->getContributions() as $contrib){
            if($startYear <= $contrib->getEndDate() && 
               $endYear >= $contrib->getStartDate()){
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
    
    function getMultimediaDuring($start, $end){
        $multimedia = array();
        $sql = "SELECT m.id
                FROM `grand_materials` m, `grand_materials_projects` p
                WHERE p.project_id = '{$this->id}'
                AND p.material_id = m.id
                AND m.date BETWEEN '$start' AND '$end'";
        $data = DBFunctions::execSQL($sql);
        foreach($data as $row){
            $multimedia[$row['id']] = Material::newFromId($row['id']);
        }
        return $multimedia;
    }
    
    /**
     * Returns an array of CRMContacts that belong to this Project
     * @return array The array of CRMContacts that belong to theis Project
     */
    function getContacts(){
        return CRMContact::getAllContacts($this);
    }

    /// Returns an array with the leaders of the project.  By default, the
    /// resulting array contains instances of Person.  If #onlyid is set to
    /// true, then the resulting array contains only numerical user IDs.
    function getLeaders($onlyid = false) {
        $leaders = $this->getAllPeople(PL);
        if($onlyid){
            $ids = array();
            foreach($leaders as $leader){
                $ids[] = $leader->getId();
            }
            return $ids;
        }
        return $leaders;
    }
    
    /**
     * Returns whether or not the logged in user can edit this project
     * @return boolean Whether or not the logged in user can edit this project
     */
    function userCanEdit(){
        $me = Person::newFromWgUser();
        if($this->getType() == "Innovation Hub" && $me->isMemberOf($this)){
            // Members of Innovation Hubs should be able to edit
            return true;
        }
        if(!$me->isRoleAtLeast(STAFF) &&
           !$me->isRole(SD) && 
           !$me->isRole("CF") &&
           (($this->isSubProject() &&
             !$me->isThemeLeaderOf($this->getParent()) && 
             !$me->isThemeCoordinatorOf($this->getParent()) &&
             !$me->isRole(PL, $this->getParent()) &&
             !$me->isThemeLeaderOf($this) &&
             !$me->isThemeCoordinatorOf($this) &&
             !$me->isRole(PL, $this) &&
             !$me->isRole(PS, $this) &&
             !$me->isRole(PA, $this)) ||
            (!$this->isSubProject() &&
             !$me->isThemeLeaderOf($this) &&
             !$me->isThemeCoordinatorOf($this) &&
             !$me->isRole(PL, $this) &&
             !$me->isRole(PS, $this) &&
             !$me->isRole(PA, $this)))){
            return false;
        }
        return true;
    }

    /**
     * Returns the first Challenge for this Project
     */
    function getChallenge(){
        $challenges = $this->getChallenges();
        return $challenges[0];
    }

    //get the project challenge
    function getChallenges(){
        if($this->themes === null){
            $this->themes = array();
            $data = DBFunctions::select(array('grand_project_challenges' => 'pc',
                                              'grand_themes' => 't'),
                                        array('t.id'),
                                        array('t.id' => EQ(COL('pc.challenge_id')),
                                              'pc.project_id' => EQ($this->id)),
                                        array('pc.id' => 'DESC'));
            if(count($data) > 0){
                foreach($data as $row){
                    $this->themes[$row['id']] = Theme::newFromId($row['id']);
                }
            }
            else{
                $this->themes[] = Theme::newFromName("Not Specified");
            }
        }
        return array_values($this->themes);
    } 
    
    /**
     * For AI4Society: Returns this Project's theme
     */
    function getTheme(){
        foreach($this->getChallenges() as $challenge){
            if(strstr($challenge->getAcronym(), "Theme - ") !== false){
                return $challenge;
            }
        }
        return Theme::newFromName("Not Specified");
    }
    
    /**
     * For AI4Society: Returns this Project's activity
     */
    function getActivity(){
        foreach($this->getChallenges() as $challenge){
            if(strstr($challenge->getAcronym(), "Activity - ") !== false){
                return $challenge;
            }
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
    
    // Returns the description of the Project
    function getLongDescription($history=false){
        $sql = "(SELECT long_description 
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'\n";
        if(!$history){
            $sql .= "AND evolution_id = '{$this->evolutionId}' 
                     ORDER BY id DESC LIMIT 1)
                    UNION
                    (SELECT long_description
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'";
        }
        $sql .= "ORDER BY id DESC LIMIT 1)";
        
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return $data[0]['long_description'];
        }
        return "";
    }
    
    function getWebsite($history=false){
        $website = "";
        $sql = "(SELECT website 
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'\n";
        if(!$history){
            $sql .= "AND evolution_id = '{$this->evolutionId}' 
                     ORDER BY id DESC LIMIT 1)
                    UNION
                    (SELECT long_description
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'";
        }
        $sql .= "ORDER BY id DESC LIMIT 1)";
        
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            $website = $data[0]['website'];
        }
        if (preg_match("#https?://#", $website) === 0) {
            $website = 'http://'.$website;
        }
        return $website;
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
    
    /**
     * Returns an array of file Articles that belong to this Project
     * @return array Returns an array of file Articles that belong to this Project
     */
    function getFiles(){
        $sql = "SELECT p.page_id
                FROM mw_an_upload_permissions u, mw_page p
                WHERE u.nsName = REPLACE('{$this->getName()}', ' ', '_')
                AND (u.upload_name = REPLACE(p.page_title, '_', ' ') OR u.upload_name = REPLACE(CONCAT('File:', p.page_title), '_', ' '))";
        $data = DBFunctions::execSQL($sql);
        $articles = array();
        foreach($data as $row){
            $article = Article::newFromId($row['page_id']);
            if($article != null){
                $articles[] = $article;
            }
        }
        return $articles;
    }
    
    /**
     * Returns an array containing responses for Technology Evaluation/Adoption
     */
    function getTechnology(){
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'TECH1', 0);
        $result = $blb->load($addr);
        $t1 = $blb->getData();
            
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'TECH2', 0);
        $result = $blb->load($addr);
        $t2 = $blb->getData();
        
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'TECH2_YES1', 0);
        $result = $blb->load($addr);
        $t2_yes1 = $blb->getData();
        
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'TECH2_YES2', 0);
        $result = $blb->load($addr);
        $t2_yes2 = $blb->getData();
            
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'TECH3', 0);
        $result = $blb->load($addr);
        $t3 = $blb->getData();
        
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'TECH4', 0);
        $result = $blb->load($addr);
        $t4 = $blb->getData();
        
        $this->technology = array('response1'      => str_replace(">", "&gt;", str_replace("<", "&lt;", str_replace("'", "&#39", $t1))),
                                  'response2'      => str_replace(">", "&gt;", str_replace("<", "&lt;", str_replace("'", "&#39", $t2))),
                                  'response2_yes1' => str_replace(">", "&gt;", str_replace("<", "&lt;", str_replace("'", "&#39", $t2_yes1))),
                                  'response2_yes2' => str_replace(">", "&gt;", str_replace("<", "&lt;", str_replace("'", "&#39", $t2_yes2))),
                                  'response3'      => str_replace(">", "&gt;", str_replace("<", "&lt;", str_replace("'", "&#39", $t3))),
                                  'response4'      => str_replace(">", "&gt;", str_replace("<", "&lt;", str_replace("'", "&#39", $t4))));
        return $this->technology;
    }
    
    /**
     * Saves the array containing responses for Technology Evaluation/Adoption
     */
    function saveTechnology(){
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'TECH1', 0);
        $blb->store($this->technology['response1'], $addr);
            
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'TECH2', 0);
        $blb->store($this->technology['response2'], $addr);
        
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'TECH2_YES1', 0);
        $blb->store($this->technology['response2_yes1'], $addr);
        
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'TECH2_YES2', 0);
        $blb->store($this->technology['response2_yes2'], $addr);
            
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'TECH3', 0);
        $blb->store($this->technology['response3'], $addr);
        
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'TECH4', 0);
        $blb->store($this->technology['response4'], $addr);
    }
    
    /**
     * Returns an array containing responses for Government Policy
     */
    function getPolicy(){
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'POLICY', 0);
        $result = $blb->load($addr);
        $p = $blb->getData();
        
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'POLICY_YES', 0);
        $result = $blb->load($addr);
        $p_yes = $blb->getData();
        
        $this->policy = array('policy'      => str_replace(">", "&gt;", str_replace("<", "&lt;", str_replace("'", "&#39", $p))),
                              'policy_yes'      => str_replace(">", "&gt;", str_replace("<", "&lt;", str_replace("'", "&#39", $p_yes))));
        return $this->policy;
    }
    
    /**
     * Saves the array containing responses for Government Policy
     */
    function savePolicy(){
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'POLICY', 0);
        $blb->store($this->policy['policy'], $addr);
            
        $blb = new ReportBlob(BLOB_TEXT, 0, 0, $this->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'POLICY_YES', 0);
        $blb->store($this->policy['policy_yes'], $addr);
    }
    
    // Returns an array of papers relating to this project
    function getPapers($category="all", $startRange = false, $endRange = false){
        return Paper::getAllPapersDuring($this->name, $category, "grand", $startRange, $endRange);
    }
    
    function getTopProductsLastUpdated(){
        $data = DBFunctions::select(array('grand_top_products'),
                                    array('changed'),
                                    array('type' => EQ('PROJECT'),
                                          'obj_id' => EQ($this->getId())),
                                    array('changed' => 'DESC'));
        if(count($data) > 0){
            return $data[0]['changed'];
        }
    }
    
    function getTopProducts(){
        $products = array();
        $data = DBFunctions::select(array('grand_top_products'),
                                    array('product_type','product_id'),
                                    array('type' => EQ('PROJECT'),
                                          'obj_id' => EQ($this->getId())));
        foreach($data as $row){
            if($row['product_type'] == "CONTRIBUTION"){
                $product = Contribution::newFromId($row['product_id']);
                $year = $product->getStartYear();
            }
            else{
                $product = Product::newFromId($row['product_id']);
            }
            if($product->getTitle() == ""){
                continue;
            }
            $year = substr($product->getDate(), 0, 4);
            $authors = $product->getAuthors();
            $name = "";
            foreach($authors as $author){
                $name = $author->getNameForForms();
                break;
            }
            $products["{$year}"][$name][] = $product;
            ksort($products["{$year}"]);
        }
        ksort($products);
        $products = array_reverse($products);
        $newProducts = array();
        foreach($products as $year => $prods){
            foreach($prods as $prod){
                $newProducts = array_merge($newProducts, $prod);
            }
        }
        return $newProducts;
    }
    
    /**
     * Returns an array of Evaluators who are evaluating this Project
     * @param string $year The evaluation year
     * @type string $type The type of evaluation
     * @return array The array of Evaluators who are evaluating this Project during $year
     */
    function getEvaluators($year, $type='Project'){
        $sql = "SELECT *
                FROM grand_eval
                WHERE sub_id = '{$this->id}'
                AND type = '{$type}'
                AND year = '{$year}'";
        $data = DBFunctions::execSQL($sql);
        $subs = array();
        foreach($data as $row){
            $subs[] = Person::newFromId($row['user_id']);
        }
        return $subs;
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
            $sql = "SELECT r.user_id, r.start_date 
                    FROM grand_roles r, grand_roles_projects rp
                    WHERE rp.project_id = '{$this->id}'
                    AND r.id = rp.role_id";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->startDates[$row['user_id']] = (isset($this->startDates[$row['user_id']])) ? min($this->startDates[$row['user_id']], $row['start_date']) : $row['start_date'];
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
            $sql = "SELECT r.user_id, r.end_date 
                    FROM grand_roles r, grand_roles_projects rp
                    WHERE rp.project_id = '{$this->id}'
                    AND r.id = rp.role_id";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->endDates[$row['user_id']] = (isset($this->endDates[$row['user_id']])) ? max($this->endDates[$row['user_id']], $row['end_date']) : $row['end_date'];
            }
        }
        return $this->endDates;
    }
    
    // Returns the endDate for the given Person
    function getLeaveDate($person){
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
    
    function getActivities(){
        $activities = array();
        if(!$this->clear){
            $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getActivities() as $activity){
                    $activities[] = $activity;
                }
            }
        }
        $data = DBFunctions::select(array('grand_activities'),
                                    array('id'),
                                    array('project_id' => $this->getId(),
                                          'deleted' => EQ(0)),
                                    array('`order`' => 'ASC',
                                          'id' => 'ASC'));
        foreach($data as $row){
            $activities[] = Activity::newFromId($row['id']);
        }
        return $activities;
    }
    
    // Returns the current milestones of this project
    // If $history is set to true, all the milestones ever for this project are included
    function getMilestones($history=false, $fesMilestones=false){
        if($this->milestones != null && !$history && !$fesMilestones){
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
        if(!$fesMilestones){
            $sql .= "\nAND activity_id != '0'";
        }
        else{
            $sql .= "\nAND activity_id = '0'";
        }
        $sql .= "\nORDER BY `order`, activity_id, milestone_id";
        $data = DBFunctions::execSQL($sql);
        
        foreach($data as $row){
            if(isset($milestoneIds[$row['milestone_id']])){
                continue;
            }
            $milestone = Milestone::newFromId($row['milestone_id']);
            $activity = $milestone->getActivity();
            if($milestone->getStatus() == 'Deleted' || ($activity != null && $milestone->getActivity()->isDeleted())){
                continue;
            }
            $milestoneIds[$milestone->getMilestoneId()] = true;
            $milestones[] = $milestone;
        }
        
        if(!$history && !$fesMilestones){
            $this->milestones = $milestones;
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
        if($this->subProjects === null){
            $this->subProjects = array();

            $data = DBFunctions::select(array('grand_project'),
                                        array('*'),
                                        array('parent_id' => EQ($this->id)),
                                        array('name' => 'ASC'));
            foreach($data as $row){
                $subproject = Project::newFromId($row['id']);
                if(!$subproject->isDeleted()){
                    $this->subProjects[] = $subproject;
                }
            }
        }
        return $this->subProjects;
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
    function getMilestonesDuring($year='0000', $fesMilestones=false){
        if($year == '0000'){
            $year = date('Y');
        }
        
        $startRange = $year.'-01-01 00:00:00';
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
                AND milestone_id NOT IN ('".implode("','", $milestoneIds)."')";
        if(!$fesMilestones){
            $sql .= "\nAND activity_id != '0'";
        }
        else{
            $sql .= "\nAND activity_id = '0'";
        }
        $sql .= "\nGROUP BY milestone_id
                ORDER BY `order`, milestone_id";
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
                $milestone = Milestone::newFromId($row2['milestone_id']);
                if($milestone->getStatus() == 'Deleted'){
                    continue;
                }
                $milestones[] = $milestone;
            }
        }
        return $milestones;
    }
    
    // Returns an array of milestones where all the milestones which were created at any time during the given year
    function getMilestonesCreated($date='0000-00-00', $fesMilestones=false){
        $milestones = array();
        $milestoneIds = array();
        if(!$this->clear){
            $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getMilestonesCreated($date) as $milestone){
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
                AND created <= '$date'";
        if(!$fesMilestones){
            $sql .= "\nAND activity_id != '0'";
        }
        else{
            $sql .= "\nAND activity_id = '0'";
        }
        $sql .= "\nGROUP BY milestone_id
                ORDER BY `order`, milestone_id";
        $data = DBFunctions::execSQL($sql);
        foreach ($data as $row){
            $id = $row['max_id'];
            $milestone_id = $row['milestone_id'];

            $milestone = Milestone::newFromId($milestone_id, $id);
            if($milestone->getStatus() == 'Deleted'){
                continue;
            }
            $milestones[] = $milestone;
        }
        return $milestones;
    }
    
    /**
     * Returns the allocated amount that this Project received for the specified $year
     * If the data is not in the DB then it falls back to checking the uploaded revised budgets
     * @param int $year The allocation year
     * @return int The amount of allocation
     */
    function getAllocatedAmount($year){
        $alloc = 0;
        $data = DBFunctions::select(array('grand_allocations'),
                                    array('amount'),
                                    array('project_id' => EQ($this->getId()),
                                          'year' => EQ($year)));
        if(count($data) > 0){
            foreach($data as $row){
                $alloc += $row['amount'];
            }
        }
        return $alloc;
    }
    
}

?>
