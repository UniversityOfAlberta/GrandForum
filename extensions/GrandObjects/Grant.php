<?php

class Grant extends BackboneModel {

    static $exclusionCache = null;

    var $id;
    var $owner_id;
    var $user_id;
    var $project_id;
    var $sponsor;
    var $external_pi;
    var $copi = array();
    var $total;
    var $portions = array();
    var $adjusted_amount;
    var $funds_before;
    var $funds_after;
    var $title;
    var $scientific_title;
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
    
    static function getAllMyGrants(){
        $grants = array();
        $data = DBFunctions::select(array('grand_grants'),
                                    array('*'),
                                    array('deleted' => '0'));
        foreach($data as $row){
            $grant = new Grant(array($row));
            if($grant != null && $grant->getId() != 0 && $grant->isMine()){
                $grants[] = $grant;
            }
        }
        return $grants;
    }
    
    function __construct($data){
        $me = Person::newFromWgUser();
        if(count($data) > 0 && $me->isLoggedIn()){
            $row = $data[0];
            $copi = unserialize($row['copi']);
            if($me->getId() == $row['user_id'] || $me->getId() == $row['owner_id'] || $me->isRoleAtLeast(STAFF) ||
               array_search($me->getId(), $copi) !== false){
                $this->id = $row['id'];
                $this->owner_id = $row['owner_id'];
                $this->user_id = $row['user_id'];
                $this->project_id = $row['project_id'];
                $this->sponsor = $row['sponsor'];
                $this->external_pi = $row['external_pi'];
                $this->copi = $copi;
                $this->total = $row['total'];
                $this->portions = json_decode($row['portions'], true);
                $this->adjusted_amount = $row['adjusted_amount'];
                $this->funds_before = $row['funds_before'];
                $this->funds_after = $row['funds_after'];
                $this->title = $row['title'];
                $this->scientific_title = $row['scientific_title'];
                $this->description = $row['description'];
                $this->role = $row['role'];
                $this->seq_no = $row['seq_no'];
                $this->prog_description = $row['prog_description'];
                $this->request = $row['request'];
                $this->start_date = ZERO_DATE($row['start_date']);
                $this->end_date = ZERO_DATE($row['end_date']);
                $this->deleted = $row['deleted'];
                $this->exclude = false;
                if($this->portions == null){
                    $this->portions = array();
                }
                foreach($this->getExclusions() as $exclusion){
                    if($exclusion->getId() == $me->getId()){
                        $this->exclude = true;
                    }
                }
            }
        }
    }
    
    function isMine(){
        $me = Person::newFromWgUser();
        return ($me->getId() == $this->user_id || 
                $me->getId() == $this->owner_id ||
                array_search($me->getId(), $this->copi) !== false);
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
    
    function getAverage(){
        $start = new DateTime(substr($this->getStartDate(), 0, 10));
        $end = new DateTime(substr($this->getEndDate(), 0, 10));
        $interval = intval($start->diff($end)->format('%a')); // Difference in days
        $years = round($interval/365);
        return $this->getTotal()/max(1, $years);
    }
    
    function getPortions(){
        return $this->portions;
    }
    
    function getMyPortion(){
        $me = Person::newFromWgUser();
        return (isset($this->portions[$me->getId()])) ? $this->portions[$me->getId()] : $this->total;
    }
    
    function getAdjustedAmount(){
        return $this->adjusted_amount;
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
    
    function getScientificTitle(){
        return $this->scientific_title;
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
                            array('owner_id' => $this->owner_id,
                                  'user_id' => $this->user_id,
                                  'project_id' => $this->project_id,
                                  'sponsor' => $this->sponsor,
                                  'external_pi' => $this->external_pi,
                                  'copi' => serialize($copis),
                                  'total' => str_replace(",", "", $this->total),
                                  'portions' => json_encode($this->portions),
                                  'adjusted_amount' => $this->adjusted_amount,
                                  'funds_before' => str_replace(",", "", $this->funds_before),
                                  'funds_after' => str_replace(",", "", $this->funds_after),
                                  'title' => $this->title,
                                  'scientific_title' => $this->scientific_title,
                                  'description' => $this->description,
                                  'role' => $this->role,
                                  'seq_no' => $this->seq_no,
                                  'prog_description' => $this->prog_description,
                                  'request' => $this->request,
                                  'start_date' => ZERO_DATE($this->start_date, zull),
                                  'end_date' => ZERO_DATE($this->end_date, zull)));
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
                                  'portions' => json_encode($this->portions),
                                  'adjusted_amount' => $this->adjusted_amount,
                                  'funds_before' => str_replace(",", "", $this->funds_before),
                                  'funds_after' => str_replace(",", "", $this->funds_after),
                                  'title' => $this->title,
                                  'scientific_title' => $this->scientific_title,
                                  'description' => $this->description,
                                  'role' => $this->role,
                                  'seq_no' => $this->seq_no,
                                  'prog_description' => $this->prog_description,
                                  'request' => $this->request,
                                  'start_date' => ZERO_DATE($this->start_date, zull),
                                  'end_date' => ZERO_DATE($this->end_date, zull)),
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
            Notification::addNotification($me, $pi, "Grant Deleted", "Your Grant entitled <i>{$this->getTitle()}</i> has been deleted", "{$this->getUrl()}");
        }
        foreach($this->getCoPI() as $copi){
            if($copi instanceof Person && $copi->getId() != $me->getId()){
                Notification::addNotification($me, $copi, "Grant Deleted", "Your Grant entitled <i>{$this->getTitle()}</i> has been deleted", "{$this->getUrl()}");
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
        $me = Person::newFromWgUser();
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
        //$grantAward = $this->getGrantAward();
        //$grantAwardId = ($grantAward != null) ? $grantAward->getId() : 0;
        $json = array(
            'id' => $this->id,
            'user_id' => $this->user_id,
            'pi' => $this->getPI()->toArray(),
            'project_id' => $this->project_id,
            'grant_award_id' => 0, //$grantAwardId,
            'sponsor' => $this->sponsor,
            'external_pi' => $this->external_pi,
            'copi' => $copis_array,
            'copi_string' => implode("; ", $copis),
            'total' => $this->total,
            'portions' => $this->portions,
            'myportion' => $this->getMyPortion(),
            'adjusted_amount' => $this->getAdjustedAmount(),
            'funds_before' => $this->funds_before,
            'funds_after' => $this->funds_after,
            'title' => $this->title,
            'scientific_title' => $this->scientific_title,
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
