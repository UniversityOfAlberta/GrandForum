<?php

/**
 * @package GrandObjects
 */

class Collaboration extends BackboneModel{

    static $cache = array();

    var $id = null;
    var $title = "";
    var $sector = "";
    var $country = "";
    var $planning = "";
    var $designDataCollection = "";
    var $analysisOfResults = "";
    var $exchangeKnowledge = "";
    var $userKnowledge = "";
    var $other = "";
    var $personName = "";
    var $position = "";
    var $funding = 0;
    var $year = YEAR;
    var $endYear = 0;
    var $knowledgeUser = false;
    var $accessId = 0;
    var $projects = array();
    var $projectsWaiting = true;
    
    /**
     * Returns a new Collaboration from the given id
     * @param integer $id The id of the Collaboration
     * @return Collaboration The Collaboration with the given id
     */
    static function newFromId($id){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()) {
            return new Collaboration(array());
        }

        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        $me = Person::newFromWgUser();
        
        $data = DBFunctions::select(array('grand_collaborations'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $collab = new Collaboration($data);
        if(!$collab->isAllowedToEdit()){
            $collab = new Contribution(array());
        }
        self::$cache[$collab->id] = &$collab;
        return $collab;
    }
    
    /**
     * Returns all of the Collaborations
     * @return array All of the Collaborations
     */
    static function getAllCollaborations(){
        $me = Person::newFromWgUser();
        if (!$me->isLoggedIn()) {
            return array();
        }

        $data = DBFunctions::select(array('grand_collaborations'),
                                    array('id'));
        $collabs = array();
        foreach($data as $row){
            $collab = Collaboration::newFromId($row['id']);
            if($collab->getId() != 0){
                $collabs[] = $collab;
            }
        }
        return $collabs;
    }


    /**
     * Returns all the Collaborations with the given ids
     * @param array $ids The array of ids
     * @return array The array of Collaborations
     */
    static function getByIds($ids){
        $me = Person::newFromWgUser();
        if (!$me->isLoggedIn()) {
            return array();
        }
        if(count($ids) == 0){
            return array();
        }
        $collabs = array();
        foreach($ids as $key => $id){
            if(isset(self::$cache[$id])){
                $collab = self::$cache[$id];
                $collabs[$collab->getId()] = $collab;
                unset($ids[$key]);
            }
        }
        if(count($ids) > 0){
            $sql = "SELECT *
                    FROM grand_collaborations
                    WHERE id IN (".implode(",", $ids)."))";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $collab = new Collaboration(array($row));
                if($collab->isAllowedToEdit()){
                    self::$cache[$collab->getId()] = $collab;
                    $collabs[$collab->getId()] = $collab;
                }
            }
        }
        return $collabs;
    }
 
    function Collaboration($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->title = $data[0]['organization_name'];
            $this->year = $data[0]['year'];
            $this->endYear = $data[0]['end_year'];
            $this->sector = $data[0]['sector'];
            $this->country = $data[0]['country'];
            $this->planning = $data[0]['planning'];
            $this->designDataCollection = $data[0]['design'];
            $this->analysisOfResults = $data[0]['analysis'];
            $this->exchangeKnowledge = $data[0]['dissemination'];
            $this->userKnowledge = $data[0]['user'];
            $this->personName = $data[0]['person_name'];
            $this->position = $data[0]['position'];
            $this->other = $data[0]['other'];
            $this->funding = $data[0]['funding'];
            $this->knowledgeUser = $data[0]['knowledge_user'];
            $this->projectsWaiting = true;
            $this->accessId = $data[0]['access_id'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getTitle(){
        return $this->title;
    }
    
    function getSector(){
        return $this->sector;
    }

    function getCountry(){
        return $this->country;
    }

    function getPlanning(){
        return $this->planning;
    }

    function getDesignDataCollection(){
        return $this->designDataCollection;
    }

    function getAnalysisOfResults(){
        return $this->analysisOfResults;
    }

    function getExchangeKnowledge(){
        return $this->exchangeKnowledge;
    }

    function getUserKnowledge(){
        return $this->userKnowledge;
    }

    function getOther(){
        return $this->other;
    }

    function getPersonName() {
        return $this->personName;
    }

    function getPosition() {
        return $this->position;
    }

    function getFunding() {
        return $this->funding;
    }

    function getKnowledgeUser() {
        return $this->knowledgeUser;
    }

    function getYear() {
        return $this->year;
    }
    
    function getEndYear() {
        return $this->endYear;
    }
    
    function getAccessId(){
        return $this->accessId;
    }
    
    function getCreator(){
        return Person::newFromId($this->accessId);
    }
    
    /**
     * Returns whether or not this logged in user can edit this Collaboration
     */
    function isAllowedToEdit($me=null){
        // There might be some inefficiencies in this function.
        // There could probably be some stuff cached to speed it up.
        if($this->getId() == ""){
            return false;
        }
        if($me == null){
            $me = Person::newFromWgUser();
        }
        if(!$me->isLoggedIn()){
            return false;
        }
        if($me->isRoleAtLeast(STAFF)){
            return true;
        }
        if($this->getAccessId() == $me->getId()){
            return true;
        }

        $oldProjectsWaiting = $this->projectsWaiting;
        $oldProjects = $this->projects;
        $this->projectsWaiting = true;
        $projects = $this->getProjects();
        $this->projects = $oldProjects;
        $this->projectsWaiting = $oldProjectsWaiting;
        foreach($projects as $project){
            if($me->leadershipOf($project) || 
               $me->isThemeLeaderOf($project) ||
               $me->isThemeCoordinatorOf($project)){
                return true;
            }
        }
        
        $hqps = $me->getHQP(true);
        foreach($hqps as $hqp){
            if($this->isAllowedToEdit($hqp)){
                return true;
            }
        }
        return false;
    }

    function create(){
        $me = Person::newFromWgUser();
        if(($this->getTitle() == null) || ($this->getTitle() == "")) {
            return false;
        }

        foreach($this->projects as $project){
            if(!isset($project->id) || $project->id == 0){
                $p = Project::newFromName($project->name);
                $project->id = $p->getId();
            }
        }

        $status = DBFunctions::insert('grand_collaborations',
                            array('organization_name' => $this->title,
                                  'sector' => $this->sector,
                                  'country' => $this->country,
                                  'planning' => $this->planning,
                                  'design' => $this->designDataCollection,
                                  'analysis' => $this->analysisOfResults,
                                  'dissemination' => $this->exchangeKnowledge,
                                  'user' => $this->userKnowledge,
                                  'other' => $this->other,
                                  'person_name' => $this->personName,
                                  'position' => $this->position,
                                  'year' => $this->year,
                                  'end_year' => $this->endYear,
                                  'funding' => $this->funding,
                                  'knowledge_user' => $this->knowledgeUser,
                                  'access_id' => $me->getId()));
        if($status){
            $this->id = DBFunctions::insertId();
        }
        // Update collaboration_projects table
        if($status){
            $status = DBFunctions::delete("grand_collaboration_projects", 
                                          array('collaboration_id' => $this->id),
                                          true);
        }
        foreach($this->projects as $project){
            if($status){
                $status = DBFunctions::insert("grand_collaboration_projects", 
                                              array('collaboration_id' => $this->id,
                                                    'project_id' => $project->id),
                                              true);
            }
        }
        $this->projectsWaiting = true;
        DBFunctions::commit();
        return $this;
    }
    
    function update(){
        if(!$this->isAllowedToEdit()){
            return $this;
        }
        if(($this->getTitle() == null) || ($this->getTitle() == "")) {
            return false;
        }
        
        foreach($this->projects as $project){
            if(!isset($project->id) || $project->id == 0){
                $p = Project::newFromName($project->name);
                $project->id = $p->getId();
            }
        }
        $status = DBFunctions::update('grand_collaborations',
                            array('organization_name' => $this->title,
                                  'sector' => $this->sector,
                                  'country' => $this->country,
                                  'planning' => $this->planning,
                                  'design' => $this->designDataCollection,
                                  'analysis' => $this->analysisOfResults,
                                  'dissemination' => $this->exchangeKnowledge,
                                  'user' => $this->userKnowledge,
                                  'other' => $this->other,
                                  'person_name' => $this->personName,
                                  'position' => $this->position,
                                  'year' => $this->year,
                                  'end_year' => $this->endYear,
                                  'funding' => $this->funding,
                                  'knowledge_user' => $this->knowledgeUser),
                            array('id' => EQ($this->getId())));

        // Update collaboration_projects table
        if($status){
            $status = DBFunctions::delete("grand_collaboration_projects", 
                                          array('collaboration_id' => $this->id),
                                          true);
        }
        foreach($this->projects as $project){
            if($status){
                $status = DBFunctions::insert("grand_collaboration_projects", 
                                              array('collaboration_id' => $this->id,
                                                    'project_id' => $project->id),
                                              true);
            }
        }
        $this->projectsWaiting = true;
        DBFunctions::commit();
        return $this;
    }

    /**
     * Returns an array or Projects which this Paper is related to
     * @return array The Projects which this Paper is related to
     */
    function getProjects(){
        if($this->projectsWaiting){
            $this->projects = array();
            $data = DBFunctions::select(array('grand_collaboration_projects'), 
                                        array('project_id'),
                                        array('collaboration_id' => $this->getId()));
            foreach($data as $row){
                $project = Project::newFromId($row['project_id']);
                if($project instanceof Project){
                    $this->projects[] = $project;
                }
            }
            $this->projectsWaiting = false;
        }
        return $this->projects;
    }
    
    function delete(){
        $me = Person::newFromWgUser();
        if($this->isAllowedToEdit()){
            DBFunctions::delete('grand_collaborations',
                                array('id' => EQ($this->getId())));
            DBFunctions::delete('grand_collaboration_projects',
                                array('collaboration_id' => EQ($this->getId())));
            $this->id = null;
        }
        return $this;
    }
    
    function toArray(){
        $projects = array();
        if(is_array($this->getProjects())){
            foreach($this->getProjects() as $project){
                $url = "";
                if($project->id != -1){
                    $url = $project->getUrl();
                }
                $projects[] = array('id' => $project->getId(),
                                    'name' => $project->getName(),
                                    'url' => $url);
            }
        }
        
        $creator = $this->getCreator();

        $data = array(
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'creator' => array('id' => $creator->getId(),
                               'name' => $creator->getName(),
                               'fullname' => $creator->getNameForForms(),
                               'url' => $creator->getUrl()),
            'sector' => $this->getSector(),
            'country' => $this->getCountry(),
            'planning' => $this->getPlanning(),
            'designDataCollection' => $this->getDesignDataCollection(),
            'analysisOfResults' => $this->getAnalysisOfResults(),
            'exchangeKnowledge' => $this->getExchangeKnowledge(),
            'userKnowledge' => $this->getUserKnowledge(),
            'other' => $this->getOther(),
            'personName' => $this->getPersonName(),
            'position' => $this->getPosition(),
            'url' => $this->getUrl(),
            'funding' => $this->getFunding(),
            'year' => $this->getYear(),
            'endYear' => $this->getEndYear(),
            'knowledgeUser' => $this->getKnowledgeUser(),
            'projects' => $projects
        );
        return $data;
    }
    
    function exists(){
        return ($this->id != "" && $this->id != 0);
    }
    
    function getCacheId(){
        return 'collaboration'.$this->getId();
    }

    function getUrl(){
        global $wgServer, $wgScriptPath;
        return "$wgServer$wgScriptPath/index.php/Special:CollaborationPage#/{$this->getId()}";
    }
    
}
