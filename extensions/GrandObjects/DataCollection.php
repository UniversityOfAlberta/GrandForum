<?php

/**
 * @package GrandObjects
 */

class DataCollection extends BackboneModel {
    
    var $id;
    var $userId;
    var $page;
    var $data = array();
    var $created;
    var $modified;
    
    /**
     * Returns a new DataCollection using the given id
     * @param int $id The report id of the DataCollection
     * @return DataCollection The DataCollection that matches the report_id
     */
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_data_collection'),
                                    array('*'),
                                    array('id' => $id));
        return new DataCollection($data);
    }
    
    /**
     * Returns a new DataCollection using the given userId and page
     * @param int $userId The userId of the DataCollection
     * @param int $page The 'page' of the DataCollection
     * @return DataCollection The DataCollection that matches the userId and page
     */
    static function newFromUserId($userId, $page){
        $data = DBFunctions::select(array('grand_data_collection'),
                                    array('*'),
                                    array('user_id' => $userId,
                                          'page' => $page));
        return new DataCollection($data);
    }
    
    /**
     * Returns all of the DataCollections using the given page
     * @param int $page The 'page' of the DataCollection
     * @return DataCollection The DataCollections that matche the page
     */
    static function newFromPage($page){
        $me = Person::newFromWgUser();
        $array = array();
        if($me->isRoleAtLeast(STAFF)){
            $page = str_replace("*", "%", $page);
            $data = DBFunctions::select(array('grand_data_collection'),
                                        array('*'),
                                        array('page' => LIKE($page)));
            foreach($data as $row){
                $array[] = new DataCollection(array($row));
            }
        }
        return $array;
    }
    
    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->userId = $data[0]['user_id'];
            $this->page = $data[0]['page'];
            $this->data = json_decode($data[0]['data'], true);
            $this->created = $data[0]['created'];
            $this->modified = $data[0]['modified'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getUserId(){
        return $this->userId;
    }
    
    function getPerson(){
        return Person::newFromId($this->userId);
    }
    
    function getPage(){
        return $this->page;
    }
    
    function getData(){
        return $this->data;
    }
    
    function getField($field, $default=""){
        if(isset($this->data[$field])){
            return $this->data[$field];
        }
        return $default;
    }
    
    function setField($field, $value){
        $this->data[$field] = $value;
    }
    
    function sum($field){
        return array_sum($this->getField($field, array()));
    }
    
    /**
     * Returns whether the current user can read the DataCollection or not
     * @return boolean Whether or not the current user can read this DataCollection
     */
    function canUserRead(){
        $me = Person::newFromWgUser();
        if(!$me->isLoggedIn()){
            // Not logged in?  Too bad, you can't read anything!
            return false;
        }
        else if($me->isRoleAtLeast(MANAGER)){
            // Managers should be able to see all DataCollections
            return true;
        }
        else if($me->getId() == $this->userId){
            // I should be able to read any DataCollections which was created by me
            return true;
        }
        else if($this->id == ""){
            return true;
        }

        return false;
    }
    
    function create(){
        if($this->canUserRead()){
            $me = Person::newFromWgUser();
            $this->userId = $me->getId();
            DBFunctions::insert('grand_data_collection',
                                array('user_id' => $this->userId,
                                      'page' => $this->page,
                                      'data' => json_encode($this->data),
                                      'modified' => EQ(COL('CURRENT_TIMESTAMP'))));
            $this->id = DBFunctions::insertId();
            DBFunctions::commit();
            $this->check5Logins();
        }
    }
    
    function update(){
        if($this->canUserRead()){
            DBFunctions::update('grand_data_collection',
                                array('data' => json_encode($this->data),
                                      'modified' => EQ(COL('CURRENT_TIMESTAMP'))),
                                array('id' => $this->id));
            DBFunctions::commit();
            $this->check5Logins();
        }
    }
    
    function delete(){
        if($this->canUserRead()){
            DBFunctions::delete('grand_data_collection',
                                array('id' => $this->id));
            DBFunctions::commit();
        }
    }
    
    function check5Logins(){
        if($this->getPage() == "loggedin"){
            $count = 0;
            for($d=0;$d<7;$d++){
                $date = date('Y-m-d', time() - $d*3600*24);
                if(in_array($date, $this->data->log)){
                    $count++;
                }
            }
            if($count >= 5){
                Gamification::log('LoginConsistency');
            }
        }
    }
    
    function toArray(){
        if($this->canUserRead()){
            return array('id' => $this->id,
                         'user_id' => $this->getUserId(),
                         'page' => $this->getPage(),
                         'data' => $this->getData(),
                         'created' => $this->created,
                         'modified' => $this->modified);
        }
        return array();
    }
    
    function exists(){
        return ($this->id != "" && $this->id != 0);
    }
    
    function getCacheId(){
        
    }
    
}

?>
