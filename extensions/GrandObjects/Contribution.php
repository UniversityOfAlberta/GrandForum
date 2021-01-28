<?php

/**
 * @package GrandObjects
 */

class Contribution {

    static $cache = array();

    var $id;
    var $name;
    var $rev_id;
    var $people = array();
    var $peopleWaiting;
    var $projects;
    var $projectsWaiting;
    var $partners;
    var $partnersWaiting;
    var $type;
    var $subtype;
    var $cash;
    var $kind;
    var $description;
    var $access_id;
    var $start_date;
    var $end_date;
    var $date;
    var $unknown;
    
    // Creates a Contribution from the given id
    // The most recent revision is grabbed
    static function newFromId($id){
        $me = Person::newFromWgUser();
        $id = addslashes($id);
        if(isset(self::$cache["id$id"])){
            return self::$cache["id$id"];
        }
        $sql = "SELECT *
                FROM grand_contributions
                WHERE id = '$id'
                ORDER BY rev_id DESC LIMIT 1";
        $data = DBFunctions::execSQL($sql);
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
        $sql = "SELECT c2.*
                FROM grand_contributions c1, grand_contributions c2
                WHERE c1.name = '$name'
                AND c1.id = c2.id
                ORDER BY c2.rev_id DESC LIMIT 1";
        $data = DBFunctions::execSQL($sql);
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
        $sql = "SELECT *
                FROM grand_contributions
                WHERE rev_id = '$id'";
        $data = DBFunctions::execSQL($sql);
        $contribution = new Contribution($data);
        if(!$contribution->isAllowedToEdit()){
            $contribution = new Contribution(array());
        }
        self::$cache["rev$id"] = &$contribution;
        return $contribution;
    }

    function __construct($data){
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
            $this->access_id = $data[0]['access_id'];
            $this->start_date = $data[0]['start_date'];
            $this->end_date = $data[0]['end_date'];
            $this->date = $data[0]['change_date'];
        }
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
    
    // Returns the wiki formatted name of this Contribution
	function getWikiName(){
		return str_replace("?", "%3F", $this->name);
	}
	
	// Returns the url of this Contribution
	function getUrl(){
	    global $wgServer, $wgScriptPath;
	    return "{$wgServer}{$wgScriptPath}/index.php/Contribution:{$this->getId()}";
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
            $sql = "SELECT *
                    FROM `grand_contributions_projects`
                    WHERE contribution_id = '{$this->rev_id}'";
            $data = DBFunctions::execSQL($sql);
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
                    $person = Person::newFromNameLike($pId);
                    if($person != null && $person->getName() != ""){
                        $people[] = $person;
                    }
                    else{
                        $people[] = $pId;
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
        $sql = "SELECT rev_id
                FROM grand_contributions
                WHERE id = '{$this->id}'
                AND rev_id < '{$this->rev_id}'";
        $data = DBFunctions::execSQL($sql);
        $contribution = new Contribution($data);
        return $contribution;
    }
    
    // Returns the array of partners
    function getPartners(){
        if($this->partnersWaiting){
            $partners = array();
            $sql = "SELECT *
                    FROM `grand_contributions_partners`
                    WHERE contribution_id = '{$this->rev_id}'";
            $data = DBFunctions::execSQL($sql);
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
                    $id = md5(serialize($p));
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
        $id = md5(serialize($partner));
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
        $id = md5(serialize($partner));
        $type0 = $this->type[$id];
        $type = "";
        switch($type0){
            default:
            case "none":
                $type = "None";
                break;
            case "cash":
                $type="Cash";
                break;
            case "caki":
                $type="Cash and In-Kind";
                break;
            case "inki":
                $type="In-Kind";
                break;
            case "grnt":
                $type="Grant";
                break;
            case "char":
                $type="Chair";
                break;
            case "scho":
                $type="Scholarship";
                break;
            case "cont":
                $type="Contract";
                break;
        }
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
        $id = md5(serialize($partner));
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
        $id = md5(serialize($partner));
        $type0 = $this->subtype[$id];
        $type = "";
        switch($type0){
            case "none":
                $type = "None";
                break;
            case "equi":
                $type="Equipment, Software";
                break;
            case "mate":
                $type="Materials";
                break;
            case "logi":
                $type="Logistical Support of Field Work";
                break;
            case "srvc":
                $type="Provision of Services";
                break;
            case "faci":
                $type="Use of Company Facilites";
                break;
            case "sifi":
                $type="Salaries of Scientific Staff";
                break;
            case "mngr":
                $type="Salaries of Managerial and Administrative Staff";
                break;
            case "trvl":
                $type="Project-related Travel";
                break;
            case "othe":
                $type="Other";
                break;
            default:
                $type = $type0;
                break;
        }
        return $type;
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
        $id = md5(serialize($partner));
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
        $id = md5(serialize($partner));
        if(isset($this->kind[$id])){
            return $this->kind[$id];
        }
        return 0;
    }
    
    // Returns whether or not the amount this partner contributed was inferred or not
    function getUnknownFor($partner){
        $this->getPartners();
        $id = md5(serialize($partner));
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
        if($me == null){
            $me = Person::newFromWgUser();
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
        if($me->isRoleAtLeast(NI)){
            foreach($this->getProjects() as $project){
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
