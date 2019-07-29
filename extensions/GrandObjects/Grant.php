<?php

class Grant extends BackboneModel {

    static $exclusionCache = null;

    var $id;
    var $user_id;
    var $project_id;
    var $sponsor;
    var $external_pi;
    var $copi = array();
    var $total;
    var $funds_before;
    var $funds_after;
    var $title;
    var $description;
    var $role;
    var $seq_no;
    var $prog_description;
    var $request;
    var $start_date;
    var $end_date;
    var $deleted;
    var $contributions = null;
    var $exclude = false; // This is sort of a weird one since it relates to the current logged in user
    
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_grants'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $grant = new Grant($data);
        return $grant;
    }
    
    static function newFromTitle($title){
        // Warning: There could be grants with duplicate titles
        $data = DBFunctions::select(array('grand_grants'),
                                    array('*'),
                                    array('title' => EQ($title)));
        $grant = new Grant($data);
        return $grant;
    }
    
    static function newFromProjectId($projectId){
        $data = DBFunctions::select(array('grand_grants'),
                                    array('*'),
                                    array('project_id' => EQ($projectId)));
        $grant = new Grant($data);
        return $grant;
    }
    
    static function getAllGrants(){
        $grants = array();
        $data = DBFunctions::select(array('grand_grants'),
                                    array('*'),
                                    array('deleted' => '0'));
        foreach($data as $row){
            $grant = new Grant(array($row));
            if($grant != null && $grant->getId() != 0){
                $grants[] = $grant;
            }
        }
        return $grants;
    }
    
    function Grant($data){
        $me = Person::newFromWgUser();
        if(count($data) > 0){
            $row = $data[0];
            $copi = unserialize($row['copi']);
            if($me->getId() == $row['user_id'] || $me->isRoleAtLeast(STAFF) ||
               array_search($me->getId(), $copi) !== false){
                $this->id = $row['id'];
                $this->user_id = $row['user_id'];
                $this->project_id = $row['project_id'];
                $this->sponsor = $row['sponsor'];
                $this->external_pi = $row['external_pi'];
                $this->copi = $copi;
                $this->total = $row['total'];
                $this->funds_before = $row['funds_before'];
                $this->funds_after = $row['funds_after'];
                $this->title = $row['title'];
                $this->description = $row['description'];
                $this->role = $row['role'];
                $this->seq_no = $row['seq_no'];
                $this->prog_description = $row['prog_description'];
                $this->request = $row['request'];
                $this->start_date = $row['start_date'];
                $this->end_date = $row['end_date'];
                $this->deleted = $row['deleted'];
                $this->exclude = false;
                foreach($this->getExclusions() as $exclusion){
                    if($exclusion->getId() == $me->getId()){
                        $this->exclude = true;
                    }
                }
            }
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getUserId(){
        return $this->user_id;
    }
    
    function getProjectId(){
        return $this->project_id;
    }
    
    function getSponsor(){
        return $this->sponsor;
    }
    
    function getExternalPI(){
        return $this->external_pi;
    }
    
    function getPI(){
        return Person::newFromId($this->user_id);
    }
    
    function getCoPI(){
        $copis = array();
        foreach($this->copi as $copi){
            $person = Person::newFromId($copi);
            if($person != null && $person->getId() != 0){
                $copis[] = $person;
            }
            else{
                $copis[] = $copi;
            }
        }
        return $copis;
    }
    
    function getGrantAward(){
        $data = DBFunctions::select(array('grand_new_grants'),
                                    array('id'),
                                    array('grant_id' => EQ($this->getId())));
        if(count($data) > 0){
            return GrantAward::newFromId($data[0]['id']);
        }
        return null;
    }
    
    function getTotal(){
        return $this->total;
    }
    
    function getFundsBefore(){
        return $this->funds_before;
    }
    
    function getFundsAfter(){
        return $this->funds_after;
    }
  
    function getTitle(){
        return $this->title;
    }
    
    function getDescription(){
        return $this->description;
    }
    
    function getRole(){
        return $this->role;
    }
    
    function getSeqNo(){
        return $this->seq_no;
    }
    
    function getProgDescription(){
        return $this->prog_description;
    }
    
    function getRequest(){
        return $this->request;
    }
    
    function getStartDate(){
        return $this->start_date;
    }
    
    function getEndDate(){
        return $this->end_date;
    }
    
    function getUrl(){
        global $wgServer, $wgScriptPath;
        return "$wgServer$wgScriptPath/index.php/Special:GrantPage#/{$this->getId()}";
    }
    
    /**
     * Returns a list of People who want this Product to be exluded from them
     * @return array the list of People who want this Product to be excluded from them
     */
    function getExclusions(){
        if(self::$exclusionCache === null){
            self::$exclusionCache = array();
            $data = DBFunctions::select(array('grand_grants_exclude'),
                                        array('*'));
            self::$exclusionCache = array();
            foreach($data as $row){
                self::$exclusionCache[$row['grant_id']][] = Person::newFromId($row['user_id']);
            }
        }
        return (isset(self::$exclusionCache[$this->getId()])) ? self::$exclusionCache[$this->getId()] : array();
    }
    
    function getContributions(){
        if($this->contributions == null){
            $this->contributions = array();
            $data = DBFunctions::select(array('grand_grant_contributions'),
                                        array('contribution_id'),
                                        array('grant_id' => EQ($this->getId())));
            foreach($data as $row){
                $this->contributions[] = $row['contribution_id'];
            }
        }
        return $this->contributions;
    }
    
    function create(){
        $me = Person::newFromWgUser();
        $copis = array();
        foreach($this->copi as $copi){
            if(isset($copi->id) && $copi->id != 0){
                // Only add them if an id was specified
                $copis[] = $copi->id;
            }
            else if(isset($copi->fullname) && $copi->fullname != ""){
                $copis[] = $copi->fullname;
            }
        }
        DBFunctions::insert('grand_grants',
                            array('user_id' => $this->user_id,
                                  'project_id' => $this->project_id,
                                  'sponsor' => $this->sponsor,
                                  'external_pi' => $this->external_pi,
                                  'copi' => serialize($copis),
                                  'total' => str_replace(",", "", $this->total),
                                  'funds_before' => str_replace(",", "", $this->funds_before),
                                  'funds_after' => str_replace(",", "", $this->funds_after),
                                  'title' => $this->title,
                                  'description' => $this->description,
                                  'role' => $this->role,
                                  'seq_no' => $this->seq_no,
                                  'prog_description' => $this->prog_description,
                                  'request' => $this->request,
                                  'start_date' => $this->start_date,
                                  'end_date' => $this->end_date));
        $this->id = DBFunctions::insertId();
        if($this->exclude){
            DBFunctions::insert('grand_grants_exclude',
                                array('grant_id' => $this->id,
                                      'user_id' => $me->id));
        }
        DBFunctions::delete('grand_grant_contributions',
                            array('grant_id' => EQ($this->getId())));
        foreach($this->getContributions() as $contribution){
            DBFunctions::insert('grand_grant_contributions',
                                array('grant_id' => $this->getId(),
                                      'contribution_id' => $contribution));
        }
        $this->copi = $copis;
        self::$exclusionCache = null;
        DBFunctions::commit();
        return $this;
    }
    
    function update(){
        $me = Person::newFromWgUser();
        $copis = array();
        foreach($this->copi as $copi){
            if(isset($copi->id) && $copi->id != 0){
                // Only add them if an id was specified
                $copis[] = $copi->id;
            }
            else if(isset($copi->fullname) && $copi->fullname != ""){
                $copis[] = $copi->fullname;
            }
        }
        DBFunctions::update('grand_grants',
                            array('user_id' => $this->user_id,
                                  'project_id' => $this->project_id,
                                  'sponsor' => $this->sponsor,
                                  'external_pi' => $this->external_pi,
                                  'copi' => serialize($copis),
                                  'total' => str_replace(",", "", $this->total),
                                  'funds_before' => str_replace(",", "", $this->funds_before),
                                  'funds_after' => str_replace(",", "", $this->funds_after),
                                  'title' => $this->title,
                                  'description' => $this->description,
                                  'role' => $this->role,
                                  'seq_no' => $this->seq_no,
                                  'prog_description' => $this->prog_description,
                                  'request' => $this->request,
                                  'start_date' => $this->start_date,
                                  'end_date' => $this->end_date),
                            array('id' => EQ($this->id)));
        DBFunctions::delete('grand_grants_exclude',
                            array('grant_id' => $this->id,
                                  'user_id' => $me->id));
        if($this->exclude){
            DBFunctions::insert('grand_grants_exclude',
                                array('grant_id' => $this->id,
                                      'user_id' => $me->id));
        }
        DBFunctions::delete('grand_grant_contributions',
                            array('grant_id' => EQ($this->getId())));
        foreach($this->getContributions() as $contribution){
            DBFunctions::insert('grand_grant_contributions',
                                array('grant_id' => $this->getId(),
                                      'contribution_id' => $contribution));
        }
        $this->copi = $copis;
        self::$exclusionCache = null;
        DBFunctions::commit();
        return $this;
    }
    
    function delete(){
        $me = Person::newFromWgUser();
        $pi = $this->getPI();
        if($pi instanceof Person && $pi->getId() != $me->getId()){
            Notification::addNotification($me, $pi, "Revenue Account Deleted", "Your Revenue Account entitled <i>{$this->getTitle()}</i> has been deleted", "{$this->getUrl()}");
        }
        foreach($this->getCoPI() as $copi){
            if($copi instanceof Person && $copi->getId() != $me->getId()){
                Notification::addNotification($me, $copi, "Revenue Account Deleted", "Your Revenue Account entitled <i>{$this->getTitle()}</i> has been deleted", "{$this->getUrl()}");
            }
        }
        DBFunctions::update('grand_grants',
                            array('deleted' => 1),
                            array('id' => EQ($this->id)));
        DBFunctions::commit();
        $this->deleted = 1;
        return $this;
    }
    
    function toArray(){
        $copis = array();
        $copis_array = array();
        foreach($this->getCoPI() as $copi){
            if($copi instanceof Person){
                $copis[] = $copi->getNameForForms();
                $copis_array[] = array('id' => $copi->getId(),
                                       'name' => $copi->getNameForProduct(),
                                       'fullname' => $copi->getNameForForms(),
                                       'url' => $copi->getUrl());
            }
            else{
                $copis[] = $copi;
                $copis_array[] = array('name' => $copi, 
                                       'fullname' => $copi);
            }
        }
        $grantAward = $this->getGrantAward();
        $grantAwardId = ($grantAward != null) ? $grantAward->getId() : 0;
        $json = array(
            'id' => $this->id,
            'user_id' => $this->user_id,
            'pi' => $this->getPI()->toArray(),
            'project_id' => $this->project_id,
            'grant_award_id' => $grantAwardId,
            'sponsor' => $this->sponsor,
            'external_pi' => $this->external_pi,
            'copi' => $copis_array,
            'copi_string' => implode("; ", $copis),
            'total' => $this->total,
            'funds_before' => $this->funds_before,
            'funds_after' => $this->funds_after,
            'title' => $this->title,
            'description' => $this->description,
            'role' => $this->role,
            'seq_no' => $this->seq_no,
            'prog_description' => $this->prog_description,
            'request' => $this->request,
            'start_date' => time2date($this->getStartDate(), "Y-m-d"),
            'end_date' => time2date($this->getEndDate(), "Y-m-d"),
            'deleted' => $this->deleted,
            'url' => $this->getUrl(),
            'contributions' => $this->getContributions(),
            'exclude' => $this->exclude
        );
        return $json;
    }
    
    function exists(){
        return ($this->id != 0);
    }
    
    function getCacheId(){
        return 'grant'.$this->getId();
    }

    // this is for when type is different than grant
    function getGrantType(){
        return "Grant";
    }

    //TODO: if status awarded etc.
    function getStatus(){
        return false;
    }

}

?>