<?php

class Keyword extends Grant {

    var $keywords = array();
    var $partners = array();
    
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_keywords'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $grant = new Keyword($data);
        return $grant;
    }
    
    static function newFromTitle($title){
        // Warning: There could be grants with duplicate titles
        $data = DBFunctions::select(array('grand_keywords'),
                                    array('*'),
                                    array('title' => EQ($title)));
        $grant = new Keyword($data);
        return $grant;
    }
    
    static function newFromProjectId($projectId){
        $data = DBFunctions::select(array('grand_keywords'),
                                    array('*'),
                                    array('project_id' => EQ($projectId)));
        $grant = new Keyword($data);
        return $grant;
    }
    
    static function getAllGrants(){
        $grants = array();
        $data = DBFunctions::select(array('grand_keywords'),
                                    array('*'),
                                    array('deleted' => '0'));
        foreach($data as $row){
            $grant = new Keyword(array($row));
            if($grant != null && $grant->getId() != 0){
                $grants[] = $grant;
            }
        }
        return $grants;
    }
    
    static function getAllEnteredKeywords(){
        $keywords = array();
        $data = DBFunctions::select(array('grand_keywords'),
                                    array('keywords'),
                                    array('deleted' => '0'));
        foreach($data as $row){
            foreach(json_decode($row['keywords']) as $keyword){
                $trimmed = trim(strtolower($keyword));
                $keywords[$trimmed] = $keyword;
            }
        }
        return array_values($keywords);
    }
    
    static function getAllEnteredPartners(){
        $partners = array();
        $data = DBFunctions::select(array('grand_keywords'),
                                    array('partners'),
                                    array('deleted' => '0'));
        foreach($data as $row){
            foreach(json_decode($row['partners']) as $partner){
                $trimmed = trim(strtolower($partner));
                $partners[$trimmed] = $partner;
            }
        }
        return array_values($partners);
    }
    
    function Keyword($data){
        $me = Person::newFromWgUser();
        if(count($data) > 0 && $me->isLoggedIn()){
            $row = $data[0];
            $copi = unserialize($row['copi']);
            if($me->getId() == $row['user_id'] || $me->getId() == $row['owner_id'] || $me->isRole('ViewProfile') || $me->isRoleAtLeast(MANAGER) ||
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
                $this->funds_before = $row['funds_before'];
                $this->funds_after = $row['funds_after'];
                $this->keywords = json_decode($row['keywords']);
                $this->partners = json_decode($row['partners']);
                $this->title = $row['title'];
                $this->scientific_title = $row['scientific_title'];
                $this->description = $row['description'];
                $this->role = $row['role'];
                $this->seq_no = $row['seq_no'];
                $this->prog_description = $row['prog_description'];
                $this->request = $row['request'];
                $this->start_date = $row['start_date'];
                $this->end_date = $row['end_date'];
                $this->deleted = $row['deleted'];
                $this->exclude = false;
                if($this->portions == null){
                    $this->portions = array();
                }
            }
        }
    }
    
    function getKeywords(){
        return $this->keywords;
    }
    
    function getPartners(){
        return $this->partners;
    }
    
    function getUrl(){
        global $wgServer, $wgScriptPath;
        return "$wgServer$wgScriptPath/index.php/Special:Keywords#/{$this->getId()}";
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
        DBFunctions::insert('grand_keywords',
                            array('owner_id' => $this->owner_id,
                                  'user_id' => $this->user_id,
                                  'project_id' => $this->project_id,
                                  'sponsor' => $this->sponsor,
                                  'external_pi' => $this->external_pi,
                                  'copi' => serialize($copis),
                                  'total' => str_replace(",", "", $this->total),
                                  'portions' => json_encode($this->portions),
                                  'funds_before' => str_replace(",", "", $this->funds_before),
                                  'funds_after' => str_replace(",", "", $this->funds_after),
                                  'keywords' => json_encode($this->keywords),
                                  'partners' => json_encode($this->partners),
                                  'title' => $this->title,
                                  'scientific_title' => $this->scientific_title,
                                  'description' => $this->description,
                                  'role' => $this->role,
                                  'seq_no' => $this->seq_no,
                                  'prog_description' => $this->prog_description,
                                  'request' => $this->request,
                                  'start_date' => $this->start_date,
                                  'end_date' => $this->end_date));
        $this->id = DBFunctions::insertId();
        $this->copi = $copis;
        DBFunctions::commit();
        return $this;
    }
    
    function update(){
        $me = Person::newFromWgUser();
        $copis = array();
        $keywords = array();
        $partners = array();
        foreach($this->copi as $copi){
            if(isset($copi->id) && $copi->id != 0){
                // Only add them if an id was specified
                $copis[] = $copi->id;
            }
            else if(isset($copi->fullname) && $copi->fullname != ""){
                $copis[] = $copi->fullname;
            }
        }
        foreach($this->keywords as $keyword){
            if(isset($keyword->keywords)){
                $keywords[] = $keyword->keywords;
            }
            else{
                $keywords[] = $keyword;
            }
        }
        foreach($this->partners as $partner){
            if(isset($partner->partners)){
                $partners[] = $partner->partners;
            }
            else{
                $partners[] = $partner;
            }
        }
        DBFunctions::update('grand_keywords',
                            array('user_id' => $this->user_id,
                                  'project_id' => $this->project_id,
                                  'sponsor' => $this->sponsor,
                                  'external_pi' => $this->external_pi,
                                  'copi' => serialize($copis),
                                  'total' => str_replace(",", "", $this->total),
                                  'portions' => json_encode($this->portions),
                                  'funds_before' => str_replace(",", "", $this->funds_before),
                                  'funds_after' => str_replace(",", "", $this->funds_after),
                                  'keywords' => json_encode($keywords),
                                  'partners' => json_encode($partners),
                                  'title' => $this->title,
                                  'scientific_title' => $this->scientific_title,
                                  'description' => $this->description,
                                  'role' => $this->role,
                                  'seq_no' => $this->seq_no,
                                  'prog_description' => $this->prog_description,
                                  'request' => $this->request,
                                  'start_date' => $this->start_date,
                                  'end_date' => $this->end_date),
                            array('id' => EQ($this->id)));
        $this->copi = $copis;
        self::$exclusionCache = null;
        DBFunctions::commit();
        return $this;
    }
    
    function delete(){
        $me = Person::newFromWgUser();
        DBFunctions::update('grand_keywords',
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
            'portions' => $this->portions,
            'myportion' => $this->getMyPortion(),
            'funds_before' => $this->funds_before,
            'funds_after' => $this->funds_after,
            'keywords' => $this->keywords,
            'partners' => $this->partners,
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
            'url' => $this->getUrl()
        );
        return $json;
    }
    
    function exists(){
        return ($this->id != 0);
    }
    
    function getCacheId(){
        return 'keyword'.$this->getId();
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
