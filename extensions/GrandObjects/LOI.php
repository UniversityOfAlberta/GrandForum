<?php

class LOI extends BackboneModel {
    static $cache = array();

	var $id;
	var $name;
	var $year;
	var $revision;
	var $full_name;
	var $type;
	var $related_loi;
	var $description;
	var $lead;
	var $colead;
	var $champion;
	var $primary_challenge;
	var $secondary_challenge;
	var $loi_pdf;
	var $supplemental_pdf;
	var $manager_comments;
	

	// Returns a new Project from the given id
	static function newFromId($id){

		$sql = "SELECT * FROM grand_loi WHERE id={$id}";
		$data = DBFunctions::execSQL($sql);
		if (DBFunctions::getNRows() > 0){
			$loi = new LOI($data);
            
		    return $loi;
		}
		else
			return null;
	}

	// Returns a new LOI from the given name
	static function newFromName($name){

		$sql = "SELECT * FROM grand_loi WHERE name='{$name}'";
		$data = DBFunctions::execSQL($sql);
		if (DBFunctions::getNRows() > 0){
			$loi = new LOI($data);
            
		    return $loi;
		}
		else
			return null;
	}

	// Constructor
	// Takes in a resultset containing the 'project id' and 'project name'
	function LOI($data){
		if(isset($data[0])){
			$this->id = $data[0]['id'];
			$this->name = $data[0]['name'];
			$this->year = $data[0]['year'];
			$this->revision = $data[0]['revision'];
			$this->full_name = $data[0]['full_name'];
			$this->type = $data[0]['type'];
			$this->related_loi = $data[0]['related_loi'];
			$this->description = $data[0]['description'];
			$this->lead = $data[0]['lead'];
			$this->colead = $data[0]['colead'];
			$this->champion = $data[0]['champion'];
			$this->primary_challenge = $data[0]['primary_challenge'];
			$this->secondary_challenge = $data[0]['secondary_challenge'];
			$this->loi_pdf = $data[0]['loi_pdf'];
			$this->supplemental_pdf = $data[0]['supplemental_pdf'];
			$this->manager_comments = $data[0]['manager_comments'];
			
		}
	}

	static function getAllLOIs($year=REPORTING_YEAR, $revision=1){
		$sql = "SELECT * FROM grand_loi WHERE year={$year} AND revision={$revision} ORDER BY name";
		$results = DBFunctions::execSQL($sql);
		$lois = array();
		$data = array();
		foreach ($results as $res) {
			$data[0] = $res;
			$lois[] = new LOI($data);
		}
		
		return $lois;
	}

	static function getNonConflictingLOIs($evaluator_id, $year=REPORTING_YEAR){
		$sql = "SELECT l.*, lc.conflict 
				FROM grand_loi l
				LEFT JOIN grand_loi_conflicts lc ON(l.id=lc.loi_id AND lc.reviewer_id={$evaluator_id})
				WHERE l.year={$year} 
				ORDER BY l.name";

		$results = DBFunctions::execSQL($sql);

		$lois = array();
		$data = array();
		foreach ($results as $res) {
			if(isset($res['conflict']) && $res['conflict'] == "1" ){
				continue;
			}
			else{
				$data[0] = $res;
				$lois[] = new LOI($data);
			}
		}
		
		return $lois;
	}

	static function getAssignedLOIs($year=REPORTING_YEAR){
		$sql = "SELECT DISTINCT l.* 
				FROM grand_loi l
				INNER JOIN mw_eval e ON(l.id=e.sub_id AND e.type IN('LOI', 'OPT_LOI') AND e.year={$year} AND l.year={$year}) 
				ORDER BY l.name";
		$results = DBFunctions::execSQL($sql);
		$lois = array();
		$data = array();
		foreach ($results as $res) {
			$data[0] = $res;
			$lois[] = new LOI($data);
		}
		
		return $lois;
	}

	// Returns the id of this LOI
	function getId(){
		return $this->id;
	}

	function getType(){
		return $this->type;
	}	
	
	function getName(){
		return $this->name;
	}
	
	function getFullName(){
		return $this->full_name;
	}

	function getDescription(){
		return $this->description;
	}	

	function getRelatedLOI(){
		return $this->related_loi;
	}	

	function getLead(){
		//Lead name
		$lead = "";
		$lead_arr = explode("<br />", $this->lead, 2);
		$lead_person = Person::newFromNameLike($lead_arr[0]);
		if($lead_person->getId()){
			$lead = "<a href='".$lead_person->getUrl()."'>".$lead_person->getNameForForms() ."</a>";
		}
		else{
			$lead = $lead_arr[0];
		}
		if(isset($lead_arr[1])){
			$lead .= "<br />".$lead_arr[1];
		}

		return $lead;
	}

	function getLeadEmail(){
		//Lead name
		$lead = array();
		$lead_arr = explode("<br />", $this->lead, 2);
		$lead_person = Person::newFromNameLike($lead_arr[0]);
		if($lead_person->getId()){
			$lead['name'] = $lead_person->getNameForForms();
			$lead['email'] = $lead_person->getEmail();
		}
		else{
			$lead['name'] = $lead_arr[0];
			$lead['email'] = "";
		}
		

		return $lead;
	}

	function getCoLead(){
		$colead = "";
		$colead_arr = explode("<br />", $this->colead, 2);
		$colead_person = Person::newFromNameLike($colead_arr[0]);

		if($colead_person->getId()){
			$colead = "<a href='".$colead_person->getUrl()."'>".$colead_person->getNameForForms() ."</a>";
		}
		else{
			$colead = $colead_arr[0];
		}
		if(isset($colead_arr[1])){
			$colead .= "<br />".$colead_arr[1];
		}

		return $colead;
	}

	function getCoLeadEmail(){
		//Lead name
		$lead = array();
		$lead_arr = explode("<br />", $this->colead, 2);
		$lead_person = Person::newFromNameLike($lead_arr[0]);
		if($lead_person->getId()){
			$lead['name'] = $lead_person->getNameForForms();
			$lead['email'] = $lead_person->getEmail();
		}
		else{
			$lead['name'] = $lead_arr[0];
			$lead['email'] = "";
		}
		

		return $lead;
	}

	function getChampion(){
		$champion = "";
		$champion_arr = explode("<br />", $this->champion, 2);
		$champion_person = Person::newFromNameLike($champion_arr[0]);

		if($champion_person->getId()){
			$champion = "<a href='".$champion_person->getUrl()."'>".$champion_person->getNameForForms() ."</a>";
		}
		else{
			$champion = $champion_arr[0];
		}
		if(isset($champion_arr[1])){
			$champion .= "<br />".$champion_arr[1];
		}

		return $champion;
	}

	function getPrimaryChallenge(){
		return $this->primary_challenge;
	}

	function getSecondaryChallenge(){
		return $this->secondary_challenge;
	}

	function getLoiPdf(){
		global $wgServer, $wgScriptPath;

		$loi_pdf = "";
		$rev = $this->revision;
		if(!empty($this->loi_pdf)){
			$loi_pdf = "<a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:LoiProposals?revision={$rev}&getpdf={$this->loi_pdf}'>{$this->loi_pdf}</a>";
		}else{
			$loi_pdf = "N/A";
		}

		return $loi_pdf;
	}

	function getSupplementalPdf(){
		global $wgServer, $wgScriptPath;

		$supplemental_pdf = "";
		$rev = $this->revision;
		if(!empty($this->supplemental_pdf)){
			$supplemental_pdf = "<a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:LoiProposals?revision={$rev}&getpdf={$this->supplemental_pdf}'>{$this->supplemental_pdf}</a>";
		}else{
			$supplemental_pdf = "N/A";
		}
		return $supplemental_pdf;
	}

	function getManagerComments(){
		return $this->manager_comments;
	}

	function getEvaluators($type=null, $year = REPORTING_YEAR){
	    $eTable = getTableName("eval");
	    $sql = "SELECT *
	            FROM $eTable
	            WHERE sub_id = '{$this->id}'
	            AND year = '{$year}'";

	    if(!is_null($type)){
	    	$type = mysql_real_escape_string($type);
	    	$sql .= " AND type = '{$type}'";
	    }

	    $data = DBFunctions::execSQL($sql);
	    $evals = array();

        foreach($data as $row){
            if($row['type'] == "LOI" || $row['type'] == "OPT_LOI"){
            	$evals[] = Person::newFromId($row['eval_id']);
            }
        }
        return $evals;
	}

	function toArray(){
	    $array = array('id' => $this->getId(),
	                   'name' => $this->getName(),
	                   'full_name' => $this->getFullName());
	    return $array;
	}
	
	function create(){
	
	}
	
	function update(){
	
	}
	
	function delete(){
	
	}
	
	function exists(){
	
	}
	
	function getCacheId(){
	
	}
	
}

?>
