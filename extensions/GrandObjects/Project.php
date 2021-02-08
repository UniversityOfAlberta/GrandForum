<?php

/**
 * @package GrandObjects
 */

class Project extends BackboneModel {

    static $cache = array();
    static $projectCache = array();

    var $id;
    var $evolutionId;
    var $fullName;
    var $shortName;
    var $name;
    var $status;
    var $type;
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
    var $theme;
    var $subProjects;
    var $photo;
    var $logo;
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
        $sql = "(SELECT p.id, p.name, p.phase, p.parent_id, e.action, e.effective_date, e.id as evolutionId, e.clear, s.status, s.type
                 FROM grand_project p, grand_project_evolution e, grand_project_status s
                 WHERE e.`project_id` = '{$id}'
                 AND e.`new_id` != '{$id}'
                 AND e.new_id = p.id
                 AND s.evolution_id = e.id
                 AND e.clear != 1
                 ORDER BY `date` DESC LIMIT 1)
                UNION 
                (SELECT p.id, p.name, p.phase, p.parent_id, e.action, e.effective_date, e.id as evolutionId, e.clear, s.status, s.type
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
            else if(($me->isLoggedIn() && !$me->isCandidate()) || $data[0]['status'] != 'Proposed'){
                $project = new Project($data);
            }
            else{
                return null;
            }
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
                                          's.status'),
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
            else if(($me->isLoggedIn() && !$me->isCandidate()) || $data[0]['status'] != 'Proposed'){
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
        else {
            return null;
        }
    }
    
    /**
     * Returns a new Project from the given title (may not be unique)
     * @param string $title The title (fullName) of the Project
     * @return Project The Project with the given title
     */
    static function newFromTitle($title){
        global $config;
        $me = Person::newFromWgUser();
        if(isset(self::$cache[$title])){
            return self::$cache[$title];
        }
        if($title == "Other"){
            return Project::newFromName($title);
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
                                          's.status'),
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
            else if(($me->isLoggedIn() && !$me->isCandidate()) || $data[0]['status'] != 'Proposed'){
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
        if($id == -1){
            return Project::newFromId($id);
        }
        $sqlExtra = ($evolutionId != null) ? $sqlExtra = "AND e.id = $evolutionId" : "";
        $sql = "SELECT p.id, p.name, p.phase, p.parent_id, e.action, e.effective_date, e.id as evolutionId, e.clear, s.type, s.status
                FROM grand_project p, grand_project_evolution e, grand_project_status s
                WHERE p.id = '$id'
                AND e.new_id = p.id
                AND s.evolution_id = e.id
                $sqlExtra
                ORDER BY e.id DESC LIMIT 1";
        $data = DBFunctions::execSQL($sql);
        if (DBFunctions::getNRows() > 0 && (($me->isLoggedIn() && !$me->isCandidate()) || $data[0]['status'] != 'Proposed')){
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
        global $config;
        $me = Person::newFromWgUser();
        if(isset(self::$cache['h_'.$name])){
            return self::$cache['h_'.$name];
        }
        if($name == "Other"){
            return Project::newFromName($name);
        }
        $name = DBFunctions::escape($name);
        $sql = "SELECT p.id, p.name, p.phase, p.parent_id, e.action, e.effective_date, e.id as evolutionId, e.clear, s.type, s.status
                FROM grand_project p, grand_project_evolution e, grand_project_status s
                WHERE p.name = '$name'
                AND e.new_id = p.id
                AND s.evolution_id = e.id
                ORDER BY e.id DESC LIMIT 1";
        $data = DBFunctions::execSQL($sql);
        if (DBFunctions::getNRows() > 0 && (($me->isLoggedIn() && !$me->isCandidate()) || $data[0]['status'] != 'Proposed')){
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
        if(!isset(self::$projectCache[$subProjects[1]])){
            $data = DBFunctions::select(array('grand_project'),
                                        array('id', 'name'),
                                        array('parent_id' => $subProjects),
                                        array('name' => 'ASC'));
            $projects = array();
            foreach($data as $row){
                $project = Project::newFromId($row['id']);
                if($project != null && $project->getName() != ""){
                    if(!isset($projects[$project->name]) && !$project->isDeleted() && (($me->isLoggedIn() && !$me->isCandidate()) || $project->getStatus() != 'Proposed')){
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
                        if(substr($project->getCreated(), 0, 10) <= $endDate && (($me->isLoggedIn() && !$me->isCandidate()) || $project->getStatus() != 'Proposed')){
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
    static function getAllProjectsEver($subProjects=false){
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
            $project = Project::newFromId($row['id']);
            if($project != null && $project->getName() != ""){
                if(!isset($projects[$project->name]) && (($me->isLoggedIn() && !$me->isCandidate()) || $project->getStatus() != 'Proposed')){
                    $projects[$project->getName()] = $project;
                }
            }
        }
        knatsort($projects);
        $projects = array_values($projects);
        return $projects;
    }
    
    /**
     * Returns all of the current Projects from the database that have been modified since the specified date
     * @return array An array of Projects
     */
    static function getNewProjects($date){
        $projects = self::getAllProjects();
        $return = array();
        foreach($projects as $project){
            if($project->getModifiedDate() >= $date){
                $return[] = $project;
            }
        }
        return $return;
    }
    
    static function areThereDeletedProjects(){
        $data = $data = DBFunctions::select(array('grand_project_status'),
                                            array('id'),
                                            array('status' => EQ('Ended')));
        return (count($data) > 0);
    }
    
    static function areThereAdminProjects(){
        $data = $data = DBFunctions::select(array('grand_project_status'),
                                            array('id'),
                                            array('type' => EQ('Administrative')));
        return (count($data) > 0);
    }

    static function areThereNonAdminProjects(){
        $data = $data = DBFunctions::select(array('grand_project_status'),
                                            array('id'),
                                            array('type' => NEQ('Administrative')));
        return (count($data) > 0);
    }
    
    static function areThereInnovationHubs(){
        $data = $data = DBFunctions::select(array('grand_project_status'),
                                            array('id'),
                                            array('type' => EQ('Innovation Hub')));
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
            $this->phase = $data[0]['phase'];
            $this->parentId = $data[0]['parent_id'];
            $this->succ = false;
            $this->preds = false;
            $this->clear = ($data[0]['clear'] == 1);
            
            if((isset($data[0]['action']) && $data[0]['action'] == 'DELETE') || $this->status == "Ended"){
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
            $this->shortName = false;
        }
    }
    
    function toArray(){
        $admins = array();
        $techs = array();
        $leads = array();
        foreach($this->getLeaders() as $leader){
            $leads[] = array('id' => $leader->getId(),
                             'name' => $leader->getNameForForms(),
                             'url' => $leader->getUrl(),
                             'email' => $leader->getEmail());
        }
        foreach($this->getAllPeople(PA) as $admin){
            $admins[] = array('id' => $admin->getId(),
                              'name' => $admin->getNameForForms(),
                              'url' => $admin->getUrl(),
                              'email' => $admin->getEmail());
        }
        foreach($this->getAllPeople(PS) as $tech){
            $admins[] = array('id' => $tech->getId(),
                              'name' => $tech->getNameForForms(),
                              'url' => $tech->getUrl(),
                              'email' => $tech->getEmail());
        }
        $subProjects = $this->getSubProjects();
        $subs = array();
        foreach($subProjects as $sub){
            $subs[] = array('id' => $sub->getId(),
                            'name' => $sub->getName(),
                            'url' => $sub->getUrl());
        }
        $challenge = $this->getChallenge();
        $theme = $challenge->getAcronym();
        $addr = $this->getMailingAddress();
        $contact = array(
            'line1' => $addr->getLine1(),
            'line2' => $addr->getLine2(),
            'line3' => $addr->getLine3(),
            'line4' => $addr->getLine4(),
            'city' => $addr->getCity(),
            'province' => $addr->getProvince(),
            'country' => $addr->getCountry(),
            'code' => $addr->getPostalCode(),
            'phone' => $addr->getPhone(),
            'fax' => $addr->getFax(),
            'email' => $addr->getEmail(),
            'twitter' => $addr->getTwitter(),
            'facebook' => $addr->getFacebook(),
            'linkedin' => $addr->getLinkedIn(),
            'youtube' => $addr->getYoutube()
        );
        
        $array = array('id' => $this->getId(),
                       'name' => $this->getName(),
                       'uniname' => $this->getUniName(),
                       'fullname' => $this->getFullName(),
                       'shortname' => $this->getShortName(),
                       'description' => $this->getDescription(),
                       'longDescription' => $this->getLongDescription(),
                       'memberStatus' => $this->getMemberStatus(),
                       'facultyList' => $this->getFacultyList(),
                       'photo' => $this->getPhoto(),
                       'cachedPhoto' => $this->getPhoto(true),
                       'logo' => $this->getLogo(),
                       'cachedLogo' => $this->getLogo(true),
                       'website' => $this->getWebsite(),
                       'dept_website' => $this->getDeptWebsite(),
                       'email' => $this->getEmail(),
                       'useGeneric' => $this->getUseGeneric(),
                       'adminEmail' => $this->getAdminEmail(),
                       'adminUseGeneric' => $this->getAdminUseGeneric(),
                       'techEmail' => $this->getTechEmail(),
                       'techUseGeneric' => $this->getTechUseGeneric(),
                       'status' => $this->getStatus(),
                       'type' => $this->getType(),
                       'theme' => $theme,
                       'contact' => $contact,
                       'programs' => $this->getPrograms(),
                       'phase' => $this->getPhase(),
                       'url' => $this->getUrl(),
                       'deleted' => $this->isDeleted(),
                       'leaders' => $leads,
                       'admins' => $admins,
                       'techs' => $techs,
                       'subprojects' => $subs,
                       'startDate' => $this->getCreated(),
                       'endDate' => $this->getDeleted());
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
            $theme = $this->getChallenge();
            if(count(DBFunctions::select(array('grand_project_challenges'),
                                         array('id', 'challenge_id'),
                                         array('project_id' => EQ($this->getId())))) == 0){
                // Theme hasen't been added yet
                DBFunctions::insert('grand_project_challenges',
                                    array('project_id' => $this->getId(),
                                          'challenge_id' => $theme->getId()));
            }
            else{
                // Update the Theme
                DBFunctions::update('grand_project_challenges',
                                    array('challenge_id' => $theme->getId()),
                                    array('project_id' => $this->getId()));
            }
            Project::$cache = array();
            Project::$projectCache = array();
        }
        return $this;
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
    
    // Returns the name, but without text from parenthesis
    function getUniName(){
        return trim(preg_replace("/\(.*\)/", "", $this->getName()));
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
    
    // Returns the short name of this Project
    function getShortName(){
        if($this->shortName === false){
            $sql = "(SELECT d.short_name
                     FROM `grand_project_descriptions` d
                     WHERE d.evolution_id = '{$this->evolutionId}'
                     ORDER BY d.id DESC LIMIT 1)
                    UNION
                    (SELECT d.short_name
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'
                     ORDER BY d.evolution_id LIMIT 1)";
            $data = DBFunctions::execSQL($sql);
            if(DBFunctions::getNRows() > 0){
                $this->shortName = $data[0]['short_name'];
            }
            else{
                $this->shortName = $this->name;
            }
        }
        return $this->shortName;
    }
    
    // Returns the status of this Project
    function getStatus(){
        return $this->status;
    }
    
    // Returns the type of this Project
    function getType(){
        return $this->type;
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
            $sql = "SELECT DISTINCT e.project_id, e.last_id
                    FROM `grand_project_evolution` e
                    WHERE e.new_id = '{$this->id}'
                    AND (e.id = '{$this->evolutionId}' OR e.action = 'MERGE' OR e.action = 'EVOLVE')
                    AND '{$this->evolutionId}' > e.last_id
                    ORDER BY e.id DESC";
            $data = DBFunctions::execSQL($sql);
            $this->preds = array();
            foreach($data as $row){
                if($row['project_id'] == -1){
                    $row['project_id'] = 0;
                    $row['last_id'] = 0;
                }
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
        if($this->id == -1){
            return "{$wgServer}{$wgScriptPath}/index.php";
        }
        $nsName = str_replace("'", "&#39;", $this->getName());
        return "{$wgServer}{$wgScriptPath}/index.php/{$nsName}:Main";
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
    
    function getModifiedDate(){
        $data = DBFunctions::select(array('grand_project_descriptions'),
                                    array('start_date'),
                                    array('project_id' => EQ($this->getId()),
                                          'id' => EQ($this->getLastHistoryId())));
        if(count($data) > 0){
            return $data[0]['start_date'];
        }
        return "0000-00-00 00:00:00";
    }
    
    // Returns an array of Person objects which represent
    // The researchers who are in this project.
    // If $filter is included, only users of that type will be selected
    function getAllPeople($filter = null){
        $currentDate = date('Y-m-d H:i:s');
        $year = date('Y');
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
            if($person->getId() != 0){
                if($filter == PL){
                    if($person->leadershipOf($this)){
                        $people[$person->getId()] = $person;
                    }
                }
                else if(($filter == null || 
                         ($person->isRole($filter, $this)) || 
                         ($person->isRole($filter."-Candidate", $this) && $this->getStatus() == "Proposed"))){
                    $people[$person->getId()] = $person;
                }
            }
        }
        usort($people, function($a, $b){
            return ($a->getReversedName() > $b->getReversedName());
        });
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
            if($person->getId() != 0){
                if($filter == PL){
                    if($person->leadershipOf($this)){
                        $people[$person->getId()] = $person;
                    }
                }
                else if(($filter == null || 
                         ($person->isRoleDuring($filter, $startRange, $endRange, $this))) && 
                        ($includeManager || !$person->isRoleDuring(MANAGER, $startRange, $endRange))){
                    $people[$person->getId()] = $person;
                }
            }
        }
        return $people;
    }
    
    function getAllPeopleOn($filter, $date, $includeManager=false){
        $year = substr($date, 0, 4);
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
            if($person->getId() != 0){
                if($filter == PL){
                    if($person->leadershipOf($this)){
                        $people[$person->getId()] = $person;
                    }
                }
                else if(($filter == null || 
                         ($person->isRoleOn($filter, $date, $this))) && 
                        ($includeManager || !$person->isRoleOn(MANAGER, $date))){
                    $people[$person->getId()] = $person;
                }
            }
        }
        return $people;
    }
    
    /**
     * Returns the path to a photo of this Project if it exists
     * @param boolen $cached Whether or not to use a cached version
     * @return string The path to a photo of this Project
     */
    function getPhoto($cached=false){
        global $wgServer, $wgScriptPath;
        if($this->photo == null){
            if(file_exists("Photos/{$this->getName()}_{$this->getId()}.jpg")){
                $this->photo = "$wgServer$wgScriptPath/Photos/{$this->getName()}_{$this->getId()}.jpg";
            }
        }
        if(!$cached){
            if(file_exists("Photos/{$this->getName()}_{$this->getId()}.jpg")){
                $md5 = md5_file("Photos/{$this->getName()}_{$this->getId()}.jpg");
                return "{$wgServer}{$wgScriptPath}/index.php?action=api.project/{$this->getId()}/image&{$md5}";
            }
        }
        return $this->photo;
    }
    
    /**
     * Returns the path to a logo of this Project if it exists
     * @param boolen $cached Whether or not to use a cached version
     * @return string The path to a logo of this Project
     */
    function getLogo($cached=false){
        global $wgServer, $wgScriptPath;
        if($this->logo == null){
            if(file_exists("Photos/{$this->getName()}_Logo_{$this->getId()}.png")){
                $this->logo = "$wgServer$wgScriptPath/Photos/{$this->getName()}_Logo_{$this->getId()}.png";
            }
        }
        if(!$cached){
            if(file_exists("Photos/{$this->getName()}_Logo_{$this->getId()}.png")){
                $md5 = md5_file("Photos/{$this->getName()}_Logo_{$this->getId()}.png");
                return "{$wgServer}{$wgScriptPath}/index.php?action=api.project/{$this->getId()}/logo&{$md5}";
            }
        }
        return $this->logo;
    }
    
    function getMailingAddress(){
        $data = DBFunctions::select(array('grand_project_contact'),
                                    array('id'),
                                    array('proj_id' => EQ($this->getId()),
                                          'type' => "Mailing"));
        foreach($data as $row){
            $address = ProjectAddress::newFromId($row['id']);
            return $address;
        }
        return new ProjectAddress(array());
    }
    
    function updateMailingAddress($address){
        DBFunctions::delete('grand_project_contact',
                            array('proj_id' => EQ($this->getId())));
        DBFunctions::insert('grand_project_contact',
                            array(
                                'proj_id' => $this->getId(),
                                'type' => $address->getType(),
                                'line1' => $address->getLine1(),
                                'line2' => $address->getLine2(),
                                'line3' => $address->getLine3(),
                                'line4' => $address->getLine4(),
                                'line5' => $address->getLine5(),
                                'city' => $address->getCity(),
                                'province' => $address->getProvince(),
                                'country' => $address->getCountry(),
                                'code' => $address->getPostalCode(),
                                'phone' => $address->getPhone(),
                                'fax' => $address->getFax(),
                                'email' => $address->getEmail(),
                                'twitter' => $address->getTwitter(),
                                'facebook' => $address->getFacebook(),
                                'linkedin' => $address->getLinkedIn(),
                                'youtube' => $address->getYoutube()
                            ));
    }
    
    /**
     * Returns an array of Address objects that this Person is from
     * @return array The Address objects that this Person is from
     */
    function getAddresses(){
        $data = DBFunctions::select(array('grand_project_contact'),
                                    array('id'),
                                    array('proj_id' => EQ($this->getId())));
        $addresses = array();
        foreach($data as $row){
            $address = ProjectAddress::newFromId($row['id']);
            $addresses[$address->getId()] = $address;
        }
        return $addresses;
    }
    
    function updatePrograms($programs){
        DBFunctions::delete('grand_project_programs',
                            array('proj_id' => EQ($this->getId())));
        foreach($programs as $program){
            DBFunctions::insert('grand_project_programs',
                                array(
                                    'proj_id' => $this->getId(),
                                    'name' => $program['name'],
                                    'url' => $program['url']
                                ));
        }
    }
    
    /**
     * Returns an array of Program objects that this Person is from
     * @return array The Program objects that this Person is from
     */
    function getPrograms(){
        $data = DBFunctions::select(array('grand_project_programs'),
                                    array('name', 'url'),
                                    array('proj_id' => EQ($this->getId())));
        $programs = array();
        foreach($data as $row){
            $programs[] = $row;
        }
        return $programs;
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
     * Returns all the current Champions
     * @param array The current Champions array(user, org, title, dept)
     */
    function getChampions(){
        $champs = array();
        $people = $this->getAllPeople(CHAMP);
        foreach($people as $champ){
            $champs[] = array('user' => $champ,
                              'org' => $champ->getUni(),
                              'title' => $champ->getPosition(),
                              'dept' => $champ->getDepartment());
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
                              'org' => $champ->getUni(),
                              'title' => $champ->getPosition(),
                              'dept' => $champ->getDepartment());
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
                              'org' => $champ->getUni(),
                              'title' => $champ->getPosition(),
                              'dept' => $champ->getDepartment());
        }
        return $champs;
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
                        $person = Person::newFromId($leader);
                        $ret[$person->getReversedName()] = $leader;
                    }
                    else{
                        $ret[$leader->getReversedName()] = $leader;
                    }
                }
            }
        }
        $sql = "SELECT pl.user_id FROM grand_project_leaders pl, mw_user u
                WHERE pl.project_id = '{$this->id}'
                AND pl.type = 'leader'
                AND u.user_id = pl.user_id
                AND u.deleted != '1'
                AND (pl.end_date = '0000-00-00 00:00:00'
                     OR pl.end_date > CURRENT_TIMESTAMP)";
        $data = DBFunctions::execSQL($sql);
        if ($onlyid) {
            foreach ($data as &$row){
                $person = Person::newFromId($row['user_id']);
                $ret[$person->getReversedName()] = $row['user_id'];
            }
        }
        else {
            foreach($data as &$row){
                $person = Person::newFromId($row['user_id']);
                $ret[$person->getReversedName()] = $person;
            }
        }
        ksort($ret);
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
                AND u.user_id = pl.user_id
                AND u.deleted != '1'";
        $data = DBFunctions::execSQL($sql);
        foreach ($data as &$row){
            $ret[$row['user_id']] = Person::newFromId($row['user_id']);
        }
        return $ret;
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
             !$me->leadershipOf($this->getParent()) &&
             !$me->isThemeLeaderOf($this) &&
             !$me->isThemeCoordinatorOf($this) &&
             !$me->leadershipOf($this) &&
             !$me->isRole(PS, $this) &&
             !$me->isRole(PA, $this) &&
             !$me->isRole(ACHAIR)) ||
            (!$this->isSubProject() &&
             !$me->isThemeLeaderOf($this) &&
             !$me->isThemeCoordinatorOf($this) &&
             !$me->leadershipOf($this) &&
             !$me->isRole(PS, $this) &&
             !$me->isRole(PA, $this) &&
             !$me->isRole(ACHAIR)))){
            return false;
        }
        return true;
    }

    //get the project challenge
    function getChallenge(){
        if($this->theme == null){
            $data = DBFunctions::select(array('grand_project_challenges' => 'pc',
                                              'grand_themes' => 't'),
                                        array('t.id'),
                                        array('t.id' => EQ(COL('pc.challenge_id')),
                                              'pc.project_id' => EQ($this->id)),
                                        array('pc.id' => 'DESC'),
                                        array(1));
            if(count($data) > 0){
                $this->theme = Theme::newFromId($data[0]['id']);
            }
            else{
                $this->theme = Theme::newFromName("Not Specified");
            }
        }
        return $this->theme;
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
    
    // Returns the member status of the Project
    function getMemberStatus($history=false){
        $sql = "(SELECT member_status
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'\n";
        if(!$history){
            $sql .= "AND evolution_id = '{$this->evolutionId}' 
                     ORDER BY id DESC LIMIT 1)
                    UNION
                    (SELECT member_status
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'";
        }
        $sql .= "ORDER BY id DESC LIMIT 1)";
        
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return $data[0]['member_status'];
        }
        return "";
    }
    
    // Returns the faculty list status of the Project
    function getFacultyList($history=false){
        $sql = "(SELECT faculty_list
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'\n";
        if(!$history){
            $sql .= "AND evolution_id = '{$this->evolutionId}' 
                     ORDER BY id DESC LIMIT 1)
                    UNION
                    (SELECT faculty_list
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'";
        }
        $sql .= "ORDER BY id DESC LIMIT 1)";
        
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return $data[0]['faculty_list'];
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
                    (SELECT website
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
    
    function getDeptWebsite($history=false){
        $website = "";
        $sql = "(SELECT dept_website 
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'\n";
        if(!$history){
            $sql .= "AND evolution_id = '{$this->evolutionId}' 
                     ORDER BY id DESC LIMIT 1)
                    UNION
                    (SELECT dept_website
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'";
        }
        $sql .= "ORDER BY id DESC LIMIT 1)";
        
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            $website = $data[0]['dept_website'];
        }
        if (preg_match("#https?://#", $website) === 0) {
            $website = 'http://'.$website;
        }
        return $website;
    }
    
    function getEmail($history=false){
        $sql = "(SELECT email 
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'\n";
        if(!$history){
            $sql .= "AND evolution_id = '{$this->evolutionId}' 
                     ORDER BY id DESC LIMIT 1)
                    UNION
                    (SELECT email
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'";
        }
        $sql .= "ORDER BY id DESC LIMIT 1)";
        
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return $data[0]['email'];
        }
        return "";
    }
    
    function getAdminEmail($history=false){
        $sql = "(SELECT admin_email 
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'\n";
        if(!$history){
            $sql .= "AND evolution_id = '{$this->evolutionId}' 
                     ORDER BY id DESC LIMIT 1)
                    UNION
                    (SELECT admin_email
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'";
        }
        $sql .= "ORDER BY id DESC LIMIT 1)";
        
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return $data[0]['admin_email'];
        }
        return "";
    }
    
    function getTechEmail($history=false){
        $sql = "(SELECT tech_email 
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'\n";
        if(!$history){
            $sql .= "AND evolution_id = '{$this->evolutionId}' 
                     ORDER BY id DESC LIMIT 1)
                    UNION
                    (SELECT tech_email
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'";
        }
        $sql .= "ORDER BY id DESC LIMIT 1)";
        
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return $data[0]['tech_email'];
        }
        return "";
    }
    
    function getUseGeneric($history=false){
        $sql = "(SELECT use_generic 
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'\n";
        if(!$history){
            $sql .= "AND evolution_id = '{$this->evolutionId}' 
                     ORDER BY id DESC LIMIT 1)
                    UNION
                    (SELECT use_generic
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'";
        }
        $sql .= "ORDER BY id DESC LIMIT 1)";
        
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return $data[0]['use_generic'];
        }
        return "";
    }
    
    function getAdminUseGeneric($history=false){
        $sql = "(SELECT admin_use_generic 
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'\n";
        if(!$history){
            $sql .= "AND evolution_id = '{$this->evolutionId}' 
                     ORDER BY id DESC LIMIT 1)
                    UNION
                    (SELECT admin_use_generic
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'";
        }
        $sql .= "ORDER BY id DESC LIMIT 1)";
        
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return $data[0]['admin_use_generic'];
        }
        return "";
    }
    
    function getTechUseGeneric($history=false){
        $sql = "(SELECT tech_use_generic 
                FROM grand_project_descriptions d
                WHERE d.project_id = '{$this->id}'\n";
        if(!$history){
            $sql .= "AND evolution_id = '{$this->evolutionId}' 
                     ORDER BY id DESC LIMIT 1)
                    UNION
                    (SELECT tech_use_generic
                     FROM `grand_project_descriptions` d
                     WHERE d.project_id = '{$this->id}'";
        }
        $sql .= "ORDER BY id DESC LIMIT 1)";
        
        $data = DBFunctions::execSQL($sql);
        if(DBFunctions::getNRows() > 0){
            return $data[0]['tech_use_generic'];
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
    
    /**
     * Returns an array of file Articles that belong to this Project
     * @return array Returns an array of file Articles that belong to this Project
     */
    function getFiles(){
        $nsName = DBFunctions::escape($this->getName());
        $sql = "SELECT p.page_id
                FROM mw_an_upload_permissions u, mw_page p
                WHERE u.nsName = REPLACE('{$nsName}', ' ', '_')
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
    
    // Returns the endDate for the given Person
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
    
    function getActivities(){
        $activities = array();
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
    
    /**
     * Returns the allocated Budget for this Project
     * @param integer $year The allocation year
     * @return Budget A new allocated Budget
     */
    function getAllocatedBudget($year){
        global $config;

        $structure = constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_BUDGET_STRUCTURE');

        $budget = null;
        $type = BLOB_EXCEL;
        $report = RP_LEADER;
        $section = LDR_BUDGET;
        $item = LDR_BUD_ALLOC;
        $subitem = 0;
        $blob = new ReportBlob($type, $year, 0, $this->getId());
        $blob_address = ReportBlob::create_address($report, $section, $item, $subitem);
        $blob->load($blob_address);
        $data = $blob->getData();
        if($data != null){
            $budget = new Budget("XLS", $structure, $data);
        }
        return $budget;
    }
    
    function getRequestedBudget($year, $role='all'){
        global $config;
        $structure = constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_BUDGET_STRUCTURE');

        $budget = null;
        $type = BLOB_EXCEL;
        $report = RP_LEADER;
        $section = LDR_BUDGET;
        $item = LDR_BUD_UPLOAD;
        $subitem = 0;
        $blob = new ReportBlob($type, $year, 0, $this->getId());
        $blob_address = ReportBlob::create_address($report, $section, $item, $subitem);
        $blob->load($blob_address);
        $data = $blob->getData();
        if($data != null){
            $budget = new Budget("XLS", $structure, $data);
        }
        return $budget;
    }
}

?>
