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
    var $knowledgeUser = false;

    var $person = null;
    
    /**
     * Returns a new Collaboration from the given id
     * @param integer $id The id of the Collaboration
     * @return Collaboration The Collaboration with the given id
     */
    static function newFromId($id){
        $me = Person::newFromWgUser();
        if (!$me->isLoggedIn()) {
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
            $collabs[] = Collaboration::newFromId($row['id']);
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
                self::$cache[$collab->getId()] = $collab;
                $collabs[$collab->getId()] = $collab;
            }
        }
        return $collabs;
    }
 
    function Collaboration($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->title = $data[0]['organization_name'];
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

    function create(){
        if(($this->getTitle() == null) || ($this->getTitle() == "")) {
            return false;
        }

        DBFunctions::insert('grand_collaborations',
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
                                  'funding' => $this->funding,
                                  'knowledge_user' => $this->knowledgeUser));
        $this->id = DBFunctions::insertId();
        return $this;
    }
    
    function update(){
        if(($this->getTitle() == null) || ($this->getTitle() == "")) {
            return false;
        }
        
        DBFunctions::update('grand_collaborations',
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
                                  'funding' => $this->funding,
                                  'knowledge_user' => $this->knowledgeUser),
                            array('id' => EQ($this->getId())));
        return $this;
    }
    
    function delete(){
        $me = Person::newFromWgUser();
        //if ((in_array($me, $this->getEditors())) || ($me == $this->person) || ($me->isRoleAtLeast(STAFF))) {
            DBFunctions::delete('grand_collaborations',
                                array('id' => EQ($this->getId())));
            $this->id = null;
        //}
        return $this;
    }
    
    function toArray(){
        $data = array(
            'id' => $this->getId(),
            'title' => $this->getTitle(),
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
            'knowledgeUser' => $this->getKnowledgeUser()
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
