<?php

/**
 * @package GrandObjects
 */

class Contribution extends BackboneModel {

    static $cache = array();
    static $typeMap = array("none" => "None",
                            "cash" => "Cash",
                            "caki" => "Cash and In-Kind",
                            "inki" => "In-Kind",
                            "grnt" => "Grant",
                            "char" => "Research Chair",
                            "scho" => "Scholarship",
                            "cont" => "Contract",
                            "fell" => "Fellowship");
    static $subTypeMap = array("none" => "None",
                               "equi" => "Equipment, Software",
                               "mate" => "Materials",
                               "logi" => "Logistical Support of Field Work",
                               "srvc" => "Provision of Services",
                               "faci" => "Use of Company Facilites",
                               "sifi" => "Salaries of Scientific Staff",
                               "mngr" => "Salaries of Managerial and Administrative Staff",
                               "trvl" => "Project-related Travel",
                               "othe" => "Other",
                               "1a"   => "Salaries: Bachelors - Canadian and Permanent Residents",
                               "1b"   => "Salaries: Bachelors - Foreign",
                               "1c"   => "Salaries: Masters - Canadian and Permanent Residents",
                               "1d"   => "Salaries: Masters - Foreign",
                               "1e"   => "Salaries: Doctorate - Canadian and Permanent Residents",
                               "1f"   => "Salaries: Doctorate - Foreign",
                               "2a"   => "Salaries: Post-doctoral Canadian and Permanent residents",
                               "2b"   => "Salaries: Postdoctoral",
                               "2c"   => "Salaries: Other",
                               "3"    => "Salary and benefits of incumbent (Canada Research Chairs only)",
                               "4"    => "Professional and technical services/contracts",
                               "5"    => "Equipment (incl. powered vehicles)",
                               "6"    => "Materials, supplies and other expenditures",
                               "7"    => "Travel",
                               "8"    => "Other expenditures");

    var $id;
    var $name;
    var $rev_id;
    var $people = array();
    var $peopleWaiting = true;
    var $projects;
    var $projectsWaiting = true;
    var $partners;
    var $partnersWaiting = true;
    var $type;
    var $subtype;
    var $cash = array();
    var $kind = array();
    var $description;
    var $institution;
    var $province;
    var $access_id;
    var $start_date;
    var $end_date;
    var $date;
    var $unknown;
    
    // Creates a Contribution from the given id
    // The most recent revision is grabbed
    static function newFromId($id){
        $me = Person::newFromWgUser();
        $id = @addslashes($id);
        if(isset(self::$cache["id$id"])){
            return self::$cache["id$id"];
        }
        $data = DBFunctions::select(array('grand_contributions'),
                                    array('*'),
                                    array('id' => $id),
                                    array('rev_id' => 'DESC'),
                                    array(1));
        $contribution = new Contribution($data);
        if(!$contribution->isAllowedToEdit()){
            $contribution = new Contribution(array());
        }
        self::$cache["id$id"] = &$contribution;
        return $contribution;
    }
    
    static function newFromName($name){
        $me = Person::newFromWgUser();
        $name = str_replace("&#58;", ":", $name);
        if(isset(self::$cache["$name"])){
            return self::$cache["$name"];
        }
        $data = DBFunctions::select(array('c1' => 'grand_contributions',
                                          'c2' => 'grand_contributions'),
                                    array('c2.*'),
                                    array('c1.name' => EQ($name),
                                          'c1.id' => EQ(COL('c2.id'))),
                                    array('c2.rev_id' => 'DESC'),
                                    array(1));
        $contribution = new Contribution($data);
        if(!$contribution->isAllowedToEdit()){
            $contribution = new Contribution(array());
        }
        self::$cache["$name"] = &$contribution;
        return $contribution;
    }
    
    // Creates a Contribution from the given revision id
    static function newFromRevId($id){
        $me = Person::newFromWgUser();
        if(isset(self::$cache["rev$id"])){
            return self::$cache["rev$id"];
        }
        $data = DBFunctions::select(array('grand_contributions'),
                                    array('*'),
                                    array('rev_id' => EQ($id)));
        $contribution = new Contribution($data);
        if(!$contribution->isAllowedToEdit()){
            $contribution = new Contribution(array());
        }
        self::$cache["rev$id"] = &$contribution;
        return $contribution;
    }

    function Contribution($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->rev_id = $data[0]['rev_id'];
            $this->name = $data[0]['name'];
            $this->people = unserialize($data[0]['users']);
            $this->peopleWaiting = true; // Lazyness
            $this->projects = array();
            $this->projectsWaiting = true; // Lazyness
            $this->partners = array();
            $this->partnersWaiting = true; // Lazyness
            $this->type = array();
            $this->subtype = array();
            $this->cash = array();
            $this->kind = array();
            $this->unknown = array();
            $this->description = $data[0]['description'];
            $this->institution = $data[0]['institution'];
            $this->province = $data[0]['province'];
            $this->access_id = $data[0]['access_id'];
            $this->start_date = $data[0]['start_date'];
            $this->end_date = $data[0]['end_date'];
            $this->date = $data[0]['change_date'];
        }
    }
    
    function toArray(){
        $partners = array();
        $projects = array();
        $authors = array();
        foreach($this->getAuthors() as $author){
            if($author instanceof Person){
                $authors[] = array('id' => $author->getId(),
                                   'name' => $author->getNameForProduct(),
                                   'fullname' => $author->getNameForForms(),
                                   'url' => $author->getUrl());
            }
            else{
                $authors[] = array('id' => 0,
                                   'name' => $author,
                                   'fullname' => $author,
                                   'url' => "");
            }
        }
        foreach($this->getProjects() as $project){
            $projects[] = array('id' => $project->getId(),
                                'name' => $project->getName(),
                                'fullname' => $project->getFullName(),
                                'url' => $project->getUrl());
        }
        foreach($this->getPartners() as $partner){
            $other_subtype = (!isset(self::$subTypeMap[$partner->subtype])) ? $partner->subtype : "";
            $subtype = ($other_subtype != "") ? "Other" : $this->getHumanReadableSubTypeFor($partner);
            $partners[] = array("name" => $partner->getOrganization(),
                                "contact" => $partner->getContact(),
                                "signatory" => $partner->getSignatory(),
                                "industry" => $partner->getIndustry(),
                                "country" => $partner->getCountry(),
                                "prov" => $partner->getProv(),
                                "city" => $partner->getCity(),
                                "level" => $partner->getLevel(),
                                "type" => $this->getHumanReadableTypeFor($partner),
                                "subtype" => $subtype,
                                "other_subtype" => $other_subtype,
                                "cash" => $this->getCashFor($partner),
                                "inkind" => $this->getKindFor($partner),
                                "total" => $this->getTotalFor($partner));
        }
        return array("id" => $this->getId(),
                     "revId" => $this->getRevId(),
                     "name" => $this->getName(),
                     "description" => $this->getDescription(),
                     "institution" => $this->getInstitution(),
                     "province" => $this->getProvince(),
                     "start" => substr($this->getStartDate(), 0, 10),
                     "end" => substr($this->getEndDate(), 0, 10),
                     "authors" => $authors,
                     "projects" => $projects,
                     "partners" => $partners,
                     "cash" => $this->getCash(),
                     "inkind" => $this->getKind(),
                     "total" => $this->getTotal(),
                     "url" => $this->getUrl());
    }
    
    function create(){
        $me = Person::newFromWgUser();
        $data = DBFunctions::select(array('grand_contributions'),
                                    array('id'),
                                    array(),
                                    array('id' => 'DESC'),
                                    array(1));
        $id = (count($data) > 0) ? $data[0]['id'] : 0;
        $this->id = $id + 1;
        $people = array();
        $projects = array();
        foreach($this->people as $person){
            if(is_object($person)){
                if(isset($person->id)){
                    $people[] = $person->id;
                }
                else{
                    $people[] = $person->fullname;
                }
            }
            else{
                $people[] = $person;
            }
        }
        foreach($this->projects as $project){
            if(is_object($project)){
                $projects[] = $project->id;
            }
            else{
                $projects[] = $project;
            }
        }
        $this->people = $people;
        $this->projects = $projects;
        
        DBFunctions::insert('grand_contributions',
                            array('id' => $this->id,
                                  'name' => $this->name,
                                  'users' => serialize($this->people),
                                  'description' => $this->description,
                                  'institution' => $this->institution,
                                  'province' => $this->province,
                                  'access_id' => $me->getId(),
                                  'start_date' => $this->start_date,
                                  'end_date' => $this->end_date));
        $this->rev_id = DBFunctions::insertId();
        if(count(DBFunctions::select(array('grand_contribution_edits'),
                                     array('*'),
                                     array('id' => $this->id,
                                           'user_id' => $me->getId()))) == 0){
            DBFunctions::insert('grand_contribution_edits',
                                array('id' => $this->id,
                                      'user_id' => $me->getId()));
        }
        foreach($this->projects as $project){
            DBFunctions::insert('grand_contributions_projects',
                                array('contribution_id' => $this->rev_id,
                                      'project_id' => $project));
        }
        $typeMap = array_flip(self::$typeMap);
        $subTypeMap = array_flip(self::$subTypeMap);
        foreach($this->partners as $key => $partner){
            $partner = (array) $partner;
            $value = @$partner['id'];
            if($value == ""){
                $value = $partner['name'];
            }
            $subType = ($partner['subtype'] == "Other") ? $partner['other_subtype'] : @$subTypeMap[$partner['subtype']];
            DBFunctions::insert('grand_contributions_partners',
                                array('contribution_id' => $this->rev_id,
                                      'partner' => $value,
                                      'contact' => json_encode($partner['contact']),
                                      'signatory' => $partner['signatory'],
                                      'industry' => $partner['industry'],
                                      'country' => @$partner['country'],
                                      'prov' => @$partner['prov'],
                                      'city' => @$partner['city'],
                                      'level' => $partner['level'],
                                      'type' => @$typeMap[$partner['type']],
                                      'subtype' => $subType,
                                      'cash' => $partner['cash'],
                                      'kind' => $partner['inkind']));
        }
        foreach($this->people as $author){
            if(is_numeric($author)){
                $person = Person::newFromId($author);
                if($person != null && $person->getName() != null){
                    Notification::addNotification($me, $person, "Contribution Created", "A new Contribution entitled <i>{$this->getName()}</i>, has been created with yourself listed as one of the researchers", "{$this->getUrl()}");
                }
            }
        }
        $this->projectsWaiting = true;
        return $this;
    }
    
    function update(){
        if(!$this->isAllowedToEdit()){
            return $this;
        }
        $me = Person::newFromWgUser();
        $people = array();
        $projects = array();
        foreach($this->people as $person){
            if(is_object($person)){
                if(isset($person->id)){
                    $people[] = $person->id;
                }
                else{
                    $people[] = $person->fullname;
                }
            }
            else{
                $people[] = $person;
            }
        }
        foreach($this->projects as $project){
            if(is_object($project)){
                $projects[] = $project->id;
            }
            else{
                $projects[] = $project;
            }
        }
        $this->people = $people;
        $this->projects = $projects;
        
        DBFunctions::insert('grand_contributions',
                            array('id' => $this->id,
                                  'name' => $this->name,
                                  'users' => serialize($this->people),
                                  'description' => $this->description,
                                  'institution' => $this->institution,
                                  'province' => $this->province,
                                  'access_id' => $me->getId(),
                                  'start_date' => $this->start_date,
                                  'end_date' => $this->end_date));
        $this->rev_id = DBFunctions::insertId();
        if(count(DBFunctions::select(array('grand_contribution_edits'),
                                     array('*'),
                                     array('id' => $this->id,
                                           'user_id' => $me->getId()))) == 0){
            DBFunctions::insert('grand_contribution_edits',
                                array('id' => $this->id,
                                      'user_id' => $me->getId()));
        }
        foreach($this->projects as $project){
            DBFunctions::insert('grand_contributions_projects',
                                array('contribution_id' => $this->rev_id,
                                      'project_id' => $project));
        }
        $typeMap = array_flip(self::$typeMap);
        $subTypeMap = array_flip(self::$subTypeMap);
        foreach($this->partners as $key => $partner){
            $partner = (array) $partner;
            $value = @$partner['id'];
            if($value == ""){
                $value = $partner['name'];
            }
            $subType = ($partner['subtype'] == "Other") ? $partner['other_subtype'] : @$subTypeMap[$partner['subtype']];
            DBFunctions::insert('grand_contributions_partners',
                                array('contribution_id' => $this->rev_id,
                                      'partner' => $value,
                                      'contact' => json_encode($partner['contact']),
                                      'signatory' => $partner['signatory'],
                                      'industry' => $partner['industry'],
                                      'country' => @$partner['country'],
                                      'prov' => @$partner['prov'],
                                      'city' => @$partner['city'],
                                      'level' => $partner['level'],
                                      'type' => @$typeMap[$partner['type']],
                                      'subtype' => $subType,
                                      'cash' => $partner['cash'],
                                      'kind' => $partner['inkind']));
        }
        // Notifications
        foreach($this->people as $author){
            if(is_numeric($author)){
                $person = Person::newFromId($author);
                if($person != null && $person->getName() != null){
                    Notification::addNotification($me, $person, "Contribution Updated", "The Contribution entitled <i>{$this->getName()}</i>, has been updated", "{$this->getUrl()}");
                }
            }
        }
        $this->projectsWaiting = true;
        return $this;
    }
    
    function delete(){
        global $wgServer, $wgScriptPath;
        if(!$this->isAllowedToEdit()){
            return $this;
        }
        $me = Person::newFromWgUser();
        foreach($this->getPeople() as $author){
            if($author instanceof Person){
                $person = $author;
                if($person != null && $person->getName() != null){
                    Notification::addNotification($me, $person, "Contribution Deleted", "The Contribution entitled <i>{$this->getName()}</i>, has been deleted", "$wgServer$wgScriptPath/index.php/Special:Contributions");
                }
            }
        }
        DBFunctions::delete('grand_contributions',
                            array('id' => $this->id));
        DBFunctions::delete('grand_contributions_partners',
                            array('contribution_id' => $this->rev_id));
        DBFunctions::delete('grand_contributions_projects',
                            array('contribution_id' => $this->rev_id));
        DBFunctions::delete('grand_contribution_edits',
                            array('id' => $this->id));
        $this->id = null;
        $this->rev_id = null;
        return true;
    }
    
    function exists(){
        return true;
    }
    
    function getCacheId(){
        
    }
    
    static function getAllContributions(){
        $me = Person::newFromWgUser();
        $sql = "SELECT DISTINCT id
                FROM `grand_contributions`";
        $data = DBFunctions::execSQL($sql);
        $contributions = array();
        foreach($data as $row){
            $c = Contribution::newFromId($row['id']);
            if($c->getId() != 0){
                $contributions[] = $c;
            }
        }
        return $contributions;
    }
    
    // Searches for the given phrase in the table of publications
	// Returns an array of publications which fit the search
	static function search($phrase, $category='all'){
	    $me = Person::newFromWgUser();
	    session_write_close();
	    $splitPhrase = explode(" ", $phrase);
	    $sql = "SELECT id, name
                FROM(SELECT id, name, rev_id
                     FROM `grand_contributions`
                     WHERE name LIKE '%'";
	    foreach($splitPhrase as $word){
	        $sql .= "AND name LIKE '%$word%'\n";
	    }
	    $sql .= "GROUP BY id, name, rev_id
                 ORDER BY id ASC, rev_id DESC) a
                 GROUP BY id";
	    $data = DBFunctions::execSQL($sql);
	    $contributions = array();
	    foreach($data as $row){
	        $contribution = Contribution::newFromId($row['id']);
	        if($contribution->getId() != 0){
	            $contributions[] = array("id" => $row['id'], "name" => $row['name']);
	        }
	    }
	    $json = json_encode($contributions);
	    return $json;
	}

    static function getContributionsDuring($type, $startDate, $endDate=-1){
        $me = Person::newFromWgUser();
        if($endDate == -1){
            $endDate = $startDate;
        }
        if(strlen($startDate) == 4){
            $startDate .= "-01-01 00:00:00";
        }
        if(strlen($endDate) == 4){
            $endDate .= "-12-31 00:00:00";
        }
        $sql = "SELECT DISTINCT id
                FROM grand_contributions
                WHERE '$startDate' <= end_date
                AND '$endDate' >= start_date ";

        if(!is_null($type) && $type != ""){
            $sql .= " AND type = '{$type}'";
        }

        $data = DBFunctions::execSQL($sql);
        $contributions = array();
        foreach($data as $row){
            $contribution = Contribution::newFromId($row['id']);
            if($contribution->getId() != 0){
                $contributions[] = $contribution;
            }
        }

        return $contributions;
    }
    
    // Returns the id of this Contribution
    function getId(){
        return $this->id;
    }
    
    // Returns the revision id of this Contribution
    function getRevId(){
        return $this->rev_id;
    }
    
    // Alias for getName
    function getTitle(){
        return $this->name;
    }
    
    // Returns the name of this Contribution
    function getName(){
        return $this->name;
    }
	
	// Returns the url of this Contribution
	function getUrl(){
	    global $wgServer, $wgScriptPath;
	    return "{$wgServer}{$wgScriptPath}/index.php/Special:Contributions#/{$this->getId()}";
	}
	
	// Returns whether or not this Contribution belongs to the specified project
	function belongsToProject($project){
	    foreach($this->getProjects() as $p){
	        if($p->getId() == $project->getId()){
	            return true;
	        }
	    }
	    return false;
	}
    
    // Returns the array of the projects relating to this contribution
    function getProjects(){
        if($this->projectsWaiting){
            $projects = array();
            $data = DBFunctions::select(array('grand_contributions_projects'),
                                        array('project_id'),
                                        array('contribution_id' => EQ($this->rev_id)));
            if(count($data) > 0){
                foreach($data as $row){
                    $projects[] = Project::newFromId($row['project_id']);
                }
            }
            $this->projects = $projects;
            $this->projectsWaiting = false;
        }
        if(!is_array($this->projects)){
            return array();
        }
        return $this->projects;
    }
    
    // Alias for getPeople()
    function getAuthors(){
        return $this->getPeople();
    }
    
    // Returns the array of the projects relating to this contribution
    function getPeople(){
        if($this->peopleWaiting){
            $people = array();
            foreach($this->people as $pId){
                if(is_numeric($pId)){
                    $people[] = Person::newFromId($pId);
                }
                else{
                    $person = Person::newFromName($pId);
                    if($person != null && $person->getName() != ""){
                        $people[] = $person;
                    }
                    else{
                        $person = Person::newFromNameLike($pId);
                        if($person != null && $person->getName() != ""){
                            $people[] = $person;
                        }
                        else{
                            $people[] = $pId;
                        }
                    }
                }
            }
            $this->people = $people;
            $this->peopleWaiting = false;
        }
        return $this->people;
    }
    
    // Returns the parent of this Contribution
    function getParent(){
        $data = DBFunctions::select(array('grand_contributions'),
                                    array('*'),
                                    array('id' => EQ($this->id),
                                          'rev_id' => LT($this->rev_id)));
        $contribution = new Contribution($data);
        return $contribution;
    }
    
    // Returns the array of partners
    function getPartners(){
        if($this->partnersWaiting){
            $partners = array();
            $data = DBFunctions::select(array('grand_contributions_partners'),
                                        array('*'),
                                        array('contribution_id' => EQ($this->rev_id)));
            if(count($data) > 0){
                foreach($data as $row){
                    $p = Partner::newFromId($row['partner']);
                    if($p != null && $p->getOrganization() != null){
                        $partners[] = $p;
                    }
                    else if($p != null && $p->getOrganization() == null && $row['partner'] != null){
                        $p->organization = $row['partner'];
                        $partners[] = $p;
                    }
                    if($p != null && $p->getContact() == null && $row['contact'] != null){
                        $p->contact = json_decode($row['contact']);
                        if($p->contact == null){
                            $p->contact = $row['contact'];
                        }
                    }
                    if($p != null && $p->getSignatory() == null && $row['signatory'] != null){
                        $p->signatory = $row['signatory'];
                    }
                    if($p != null && $p->getIndustry() == null && $row['industry'] != null){
                        $p->industry = $row['industry'];
                    }
                    if($p != null && $p->getCountry() == null && $row['country'] != null){
                        $p->country = $row['country'];
                    }
                    if($p != null && $p->getProv() == null && $row['prov'] != null){
                        $p->prov = $row['prov'];
                    }
                    if($p != null && $p->getCity() == null && $row['city'] != null){
                        $p->city = $row['city'];
                    }
                    if($p != null && $p->getLevel() == null && $row['level'] != null){
                        $p->level = $row['level'];
                    }
                    $id = $p->getOrganization();
                    $p->subtype = $row['subtype'];
                    
                    $this->type[$id] = $row['type'];
                    
                    if($row['type'] == 'caki'){
                        $this->cash[$id] = $row['cash'];
                        $this->kind[$id] = $row['kind'];
                    }
                    else if($row['type'] == 'cash' ||
                            $row['type'] == 'grnt' ||
                            $row['type'] == 'char' ||
                            $row['type'] == 'scho' ||
                            $row['type'] == 'fell' ||
                            $row['type'] == 'cont'){
                        $this->cash[$id] = $row['cash'];
                        $this->kind[$id] = 0;
                    }
                    else if($row['type'] == 'inki'){
                        $this->cash[$id] = 0;
                        $this->kind[$id] = $row['kind'];
                    }
                    else if($row['type'] == 'none'){
                        $this->cash[$id] = $row['cash'];
                        $this->kind[$id] = 0;
                    }else{
                        $this->cash[$id] = 0;
                        $this->kind[$id] = 0;
                    }
                    $this->subtype[$id] = $row['subtype'];
                    $this->unknown[$id] = $row['unknown'];
                }
            }
            
            $this->partners = $partners;
            $this->partnersWaiting = false;
        }
        return $this->partners;
    }
    
    // Returns the type of this Contribution
    // (Depricated! a contribution can potentially have many types, based on the number of partners)
    function getType(){
        $this->getPartners();
        if(isset($this->partners[0])){
            return $this->getTypeFor($this->partners[0]);
        }
        else{
            return "none";
        }
    }
    
    // Returns the type of Contribution for the given Partner
    function getTypeFor($partner){
        $this->getPartners();
        $id = ($partner instanceof Partner) ? $partner->getOrganization() : $partner;
        if(isset($this->type[$id])){
            return $this->type[$id];
        }
    }
    
    // (Depricated! a contribution can potentially have many types, based on the number of partners)
    function getHumanReadableType(){
        $this->getPartners();
        if(isset($this->partners[0])){
            return $this->getHumanReadableTypeFor($this->partners[0]);
        }
        else{
            return "None";
        }
    }
    
    // Returns the Human Readable type of Contribution for the given Partner
    function getHumanReadableTypeFor($partner){
        $this->getPartners();
        $id = ($partner instanceof Partner) ? $partner->getOrganization() : $partner;
        $type0 = @$this->type[$id];
        $type = (isset(self::$typeMap[$type0])) ? self::$typeMap[$type0] : $type0;
        return $type;
    }
    
    // Returns the sub-type of Contribution
    // (Depricated! a contribution can potentially have many types, based on the number of partners)
    function getSubType(){
        $this->getPartners();
        if(isset($this->partners[0])){
            return $this->getSubTypeFor($this->partners[0]);
        }
        else{
            return "none";
        }
    }
    
    // Returns the sub-type of Contribution for the given Partner
    function getSubTypeFor($partner){
        $this->getPartners();
        $id = ($partner instanceof Partner) ? $partner->getOrganization() : $partner;
        if(isset($this->subtype[$id])){
            return $this->subtype[$id];
        }
    }
    
    // (Depricated! a contribution can potentially have many types, based on the number of partners)
    function getHumanReadableSubType(){
        $this->getPartners();
        if(isset($this->partners[0])){
            return $this->getHumanReadableSubTypeFor($this->partners[0]);
        }
        else{
            return "None";
        }
    }
    
    // Returns the Human Readable sub-type of Contribution for the given Partner
    function getHumanReadableSubTypeFor($partner){
        $this->getPartners();
        $id = ($partner instanceof Partner) ? $partner->getOrganization() : $partner;
        $type0 = @$this->subtype[$id];
        $type = (isset(self::$subTypeMap[$type0])) ? self::$subTypeMap[$type0] : $type0;
        return $type;
    }
    
    function getContactFor($partner){
        $id = ($partner instanceof Partner) ? $partner->getOrganization() : $partner;
        return @$this->contact[$id];
    }
    
    function getIndustryFor($partner){
        $id = ($partner instanceof Partner) ? $partner->getOrganization() : $partner;
        return @$this->industry[$id];
    }
    
    function getLevelFor($partner){
        $id = ($partner instanceof Partner) ? $partner->getOrganization() : $partner;
        return @$this->level[$id];
    }
    
    function getByType($type, $partner=null){
        switch($type){
            case "All":
                // Special Case, just return the total
                return $this->getTotal();
                break;
            case "Cash":
            case "cash":
            case "Grant":
            case "grnt":
            case "Research Chair":
            case "char":
            case "Scholarship":
            case "scho":
            case "Fellowship":
            case "fell":
            case "Contract":
            case "cont":
                if($partner != null){
                    return $this->getCashFor($partner);
                }
                return $this->getCash();
            case "Cash and In-Kind":
            case "caki":
                if($partner != null){
                    return $this->getTotalFor($partner);
                }
                return $this->getTotal();
            case "In-Kind":
            case "inki":
                if($partner != null){
                    return $this->getKindFor($partner);
                }
                return $this->getKind();
        }
        return 0;
    }
    
    // Returns the cash value for this Contribution
    function getCash(){
        $this->getPartners();
        return array_sum($this->cash);
    }
    
    // Returns the cash value for the given Partner
    function getCashFor($partner){
        $this->getPartners();
        $id = ($partner instanceof Partner) ? $partner->getOrganization() : $partner;
        if(isset($this->cash[$id])){
            return $this->cash[$id];
        }
        return 0;
    }
    
    // Returns the in kind value for this Contribution
    function getKind(){
        $this->getPartners();
        return array_sum($this->kind);
    }
    
    // Returns the in kind value for the given Partner
    function getKindFor($partner){
        $this->getPartners();
        $id = ($partner instanceof Partner) ? $partner->getOrganization() : $partner;
        if(isset($this->kind[$id])){
            return $this->kind[$id];
        }
        return 0;
    }
    
    // Returns whether or not the amount this partner contributed was inferred or not
    function getUnknownFor($partner){
        $this->getPartners();
        $id = ($partner instanceof Partner) ? $partner->getOrganization() : $partner;
        if(isset($this->unknown[$id])){
            return ($this->unknown[$id] == 1);
        }
        return false;
    }
    
    // Returns the sum of cash and in kind Contributions
    function getTotal(){
        return $this->getCash() + $this->getKind();
    }
    
    // Returns the sum of cash and in kind for the given Partner
    function getTotalFor($partner){
        return $this->getCashFor($partner) + $this->getKindFor($partner);
    }
    
    // Returns the description of this Contribution
    function getDescription(){
        return $this->description;
    }
    
    function getInstitution(){
        return $this->institution;
    }
    
    function getProvince(){
        return $this->province;
    }
    
    /**
     * Returns the access id of this Contribution
     * @return int The user id who has access to this Contribution
     */
    function getAccessId(){
        return $this->access_id;
    }
    
    // Returns the Year of this Contribution
    function getYear(){
        return $this->getStartYear();
    }
    
    /**
     * Returns the start year of this Contribution
     * return int The start year of this Contribution
     */
    function getStartYear(){
        return substr($this->getStartDate(), 0, 4);
    }
    
    /**
     * Returns the end year of this Contribution
     * return int The end year of this Contribution
     */
    function getEndYear(){
        return substr($this->getEndDate(), 0, 4);
    }
    
    /**
     * Returns the Start Date of this Contribution
     * @return string The Start Date of this Contribution
     */
    function getStartDate(){
        return $this->start_date;
    }
    
    /**
     * Returns the End Date of this Contribution
     * @return string The End Date of this Contribution
     */
    function getEndDate(){
        return $this->end_date;
    }
    
    /**
     * Returns how many years this Contribution spans
     * @return int How many years this Contribution spans
     */
    function getNYears(){
        $date1 = new DateTime($this->getStartDate());
        $date2 = new DateTime($this->getEndDate());
        $interval = $date1->diff($date2);
        return max(1, $interval->y + 1);
    }
    
    /**
     * Returns the last time this Contribution was changed
     * @return string The last time this Contribution was changed
     */
    function getDate(){
        return $this->date;
    }
    
    /**
     * Returns whether or not this logged in user can edit this Contribution
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
        foreach($this->getPeople() as $person){
            if($person == $me->getName() || ($person instanceof Person && $person->getId() == $me->getId())){
                return true;
            }
        }
        $oldProjectsWaiting = $this->projectsWaiting;
        $oldProjects = $this->projects;
        $this->projectsWaiting = true;
        $projects = $this->getProjects();
        $this->projects = $oldProjects;
        $this->projectsWaiting = $oldProjectsWaiting;
        if($me->isRoleAtLeast(NI)){
            foreach($projects as $project){
                if($me->isMemberOf($project) ||
                   $me->leadershipOf($project) || 
                   $me->isThemeLeaderOf($project) ||
                   $me->isThemeCoordinatorOf($project)){
                    return true;
                }
            }
        }
        if(count(DBFunctions::select(array('grand_contribution_edits'),
                                     array('*'),
                                     array('id' => $this->getId(),
                                           'user_id' => $me->getId()))) > 0){
            return true;
        }
        $hqps = $me->getHQP(true);
        foreach($hqps as $hqp){
            if($this->isAllowedToEdit($hqp)){
                return true;
            }
        }
        return false;
    }
    
    // Returns an array of strings representing all the custom misc types
	static function getAllOtherSubTypes(){
	    $sql = "SELECT DISTINCT subtype
	            FROM grand_contributions_partners
	            WHERE subtype != 'othe' AND
	            subtype != 'none' AND
	            subtype != 'equi' AND
	            subtype != 'mate' AND
	            subtype != 'logi' AND
	            subtype != 'srvc' AND
	            subtype != 'faci' AND
	            subtype != 'sifi' AND
	            subtype != 'mngr' AND
	            subtype != 'trvl'";
	    $data = DBFunctions::execSQL($sql);
	    $return = array();
	    foreach($data as $row){
	        $return[] = $row['subtype'];
	    }
	    return $return;
	}
	
	static function getAllCustomPartners(){
	    $sql = "SELECT DISTINCT partner
	            FROM grand_contributions_partners";
	    $data = DBFunctions::execSQL($sql);
	    $return = array();
	    foreach($data as $row){
	        if(!is_numeric($row['partner'])){
	            $return[] = $row['partner'];
	        }
	    }
	    return $return;
	}
}
?>
