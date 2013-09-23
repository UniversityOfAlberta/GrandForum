<?php

class Project extends BackboneModel {

    static $cache = array();

	var $id;
	var $evolutionId;
	var $fullName;
	var $name;
	var $status;
	var $type;
	var $people;
	var $contributions;
	var $multimedia;
	var $startDates;
	var $endDates;
	var $comments;
	var $milestones;
	var $budgets;
	var $deleted;
	var $effectiveDate;
	private $themes;
	private $succ;
	private $preds;
	private $peopleCache = null;
	private $leaderCache = array();

	// Returns a new Project from the given id
	static function newFromId($id){
	    if(isset(self::$cache[$id])){
	        return self::$cache[$id];
	    }
		$sql = "(SELECT p.id, p.name, e.action, e.effective_date, e.id as evolutionId, s.status, s.type
	             FROM grand_project p, grand_project_evolution e, grand_project_status s
	             WHERE e.`project_id` = '{$id}'
	             AND e.`new_id` != '{$id}'
	             AND e.new_id = p.id
				 AND s.evolution_id = e.id
	             ORDER BY `date` DESC LIMIT 1)
	            UNION 
	            (SELECT p.id, p.name, e.action, e.effective_date, e.id as evolutionId, s.status, s.type
				 FROM grand_project p, grand_project_evolution e, grand_project_status s
				 WHERE p.id = '$id'
				 AND e.new_id = p.id
				 AND s.evolution_id = e.id
				 ORDER BY e.id DESC LIMIT 1)";
		$data = DBFunctions::execSQL($sql);
		if (DBFunctions::getNRows() > 0){
			$project = new Project($data);
            self::$cache[$project->id] = &$project;
            self::$cache[$project->name] = &$project;
		    return $project;
		}
		else
			return null;
	}
	
	// Returns a new Project from the given name
	static function newFromName($name){
	    if(isset(self::$cache[$name])){
	        return self::$cache[$name];
	    }
	    $data = DBFunctions::select(array('grand_project' => 'p',
	                                      'grand_project_evolution' => 'e',
	                                      'grand_project_status' => 's'),
	                                array('p.id',
	                                      'p.name',
	                                      'e.action',
	                                      'e.effective_date',
	                                      'e.id' => 'evolutionId',
	                                      's.type',
	                                      's.status'),
	                                array('p.name' => $name,
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
			$project = new Project($data);
            self::$cache[$project->id] = &$project;
            self::$cache[$project->name] = &$project;
		    return $project;
		}
		else
			return null;
	}
	
	// Returns a Project from the given historic ID
	static function newFromHistoricId($id, $evolutionId=null){
	    if(isset(self::$cache[$id.'_'.$evolutionId])){
	        return self::$cache[$id.'_'.$evolutionId];
	    }
	    $sql = "SELECT p.id, p.name, e.action, e.effective_date, e.id as evolutionId, s.type, s.status
				FROM grand_project p, grand_project_evolution e, grand_project_status s
				WHERE p.id = '$id'
				AND e.new_id = p.id
				AND s.evolution_id = e.id
				ORDER BY e.id DESC LIMIT 1";
        $data = DBFunctions::execSQL($sql);
		if (DBFunctions::getNRows() > 0){
		    $project = new Project($data);
		    $project->evolutionId = $evolutionId;
		    self::$cache[$id.'_'.$evolutionId] = $project;
		    return $project;
		}
	}
	
	// Returns a Project from the given historic name
	static function newFromHistoricName($name){
	    if(isset(self::$cache['h_'.$name])){
	        return self::$cache['h_'.$name];
	    }
	    $sql = "SELECT p.id, p.name, e.action, e.effective_date, e.id as evolutionId, s.type, s.status
				FROM grand_project p, grand_project_evolution e, grand_project_status s
				WHERE p.name = '$name'
				AND e.new_id = p.id
				AND s.evolution_id = e.id
				ORDER BY e.id DESC LIMIT 1";
        $data = DBFunctions::execSQL($sql);
		if (DBFunctions::getNRows() > 0){
		    $project = new Project($data);
		    self::$cache['h_'.$name] = &$project;
		    return $project;
		}
	}
	
	// Gets all of the Projects from the database
	static function getAllProjects(){
	    $data = DBFunctions::select(array('grand_project'),
	                                array('id'),
	                                array(),
	                                array('name' => 'ASC'));
		$projects = array();
		$projectNames = array();
		foreach($data as $row){
		    $project = Project::newFromId($row['id']);
		    if($project != null && $project->getName() != ""){
		        if(!isset($projectNames[$project->name]) && !$project->isDeleted()){
		            $projectNames[$project->name] = true;
			        $projects[] = $project;
			    }
			}
		}
		return $projects;
	}
	
	static function getAllProjectsDuring($startDate=false, $endDate=false){
	    if($startDate == false){
	        $startDate = REPORTING_CYCLE_START;
	    }
	    if($endDate == false){
	        $endDate = REPORTING_CYCLE_END;
	    }
	    $data = DBFunctions::select(array('grand_project'),
	                                array('id'),
	                                array(),
	                                array('name' => 'ASC'));
		$projects = array();
		$projectNames = array();
		foreach($data as $row){
		    $project = Project::newFromId($row['id']);
		    if($project != null && $project->getName() != ""){
		        if(!isset($projectNames[$project->name])){
		            if(($project->deleted &&
		                strcmp($project->effectiveDate, $endDate) <= 0 &&
		                strcmp($project->effectiveDate, $startDate) >= 0) ||
		               !$project->deleted){
		                $projectNames[$project->name] = true;
			            $projects[] = $project;
			        }
			    }
			}
		}
		return $projects;
	}
	
	// Orders the projects alphabetically so that they are sorted vertically in columns rather than rows.
	// Returns an array in a format which is useful for outputing a 3 column html table.
	static function orderProjects($projects){
		$nPerCol = ceil(count($projects)/3);
		$remainder = count($projects) % 3;
		$col1 = array();
		$col2 = array();
		$col3 = array();
		if($remainder == 0){
			$j = $nPerCol;
			$k = $nPerCol*2;
			$jEnd = $nPerCol*2;
			$kEnd = $nPerCol*3;
		}
		else if($remainder == 1){
			$j = $nPerCol;
			$k = $nPerCol*2 - 1;
			$jEnd = $nPerCol*2 - 1;
			$kEnd = $nPerCol*3 - 2;
		}
		else if($remainder == 2){
			$j = $nPerCol;
			$k = $nPerCol*2;
			$jEnd = $nPerCol*2;
			$kEnd = $nPerCol*3 - 1;
		}
		for($i = 0; $i < $nPerCol; $i++){
			if(isset($projects[$i])){
				$col1[] = $projects[$i];
			}
			if(isset($projects[$j]) && $j < $jEnd){
				$col2[] = $projects[$j];
			}
			if(isset($projects[$k]) && $k < $kEnd){
				$col3[] = $projects[$k];
			}
			$j++;
			$k++;
		}
		
		$projects = array();
		$i = 0;
		foreach($col1 as $row){
			if(isset($col1[$i])){
				$projects[] = $col1[$i];
			}
			if(isset($col2[$i])){
				$projects[] = $col2[$i];
			}
			if(isset($col3[$i])){
				$projects[] = $col3[$i];
			}
			$i++;
		}
		return $projects;
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
			$this->succ = false;
			$this->preds = false;
			
			if(isset($data[0]['action']) && $data[0]['action'] == 'DELETE'){
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
			$this->themes = null;
		}
	}
	
	function toArray(){
	    $array = array('id' => $this->getId(),
	                   'name' => $this->getName(),
	                   'fullname' => $this->getFullName(),
	                   'description' => $this->getDescription(),
	                   'status' => $this->getStatus(),
	                   'type' => $this->getType(),
	                   'url' => $this->getUrl(),
	                   'deleted' => $this->isDeleted());
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
	
    static function getHQPDistributionDuring($startRange = false, $endRange = false){
         //If no range end are provided, assume it's for the current year.
        if( $startRange === false || $endRange === false ){
            $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
            $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
        }
        
        $sql = <<<EOF
        SELECT s.num_projects, COUNT(s.user) as user_count
        FROM 
        (SELECT p.user_id, COUNT(p.project_id) as num_projects
        FROM grand_project_members p
        INNER JOIN mw_user u ON (p.user_id=u.user_id) 
        INNER JOIN grand_roles r ON (p.user_id=r.user)
        WHERE r.role = 'HQP'
        AND ( 
                ( (r.end_date != '0000-00-00 00:00:00') AND
                (( r.start_date BETWEEN '$startRange' AND '$endRange' ) || ( r.end_date BETWEEN '$startRange' AND '$endRange' ) || (r.start_date <= '$startRange' AND r.end_date >= '$endRange') ))
                OR
                ( (r.end_date = '0000-00-00 00:00:00') AND
                ((r.start_date <= '$endRange')))
                )              
        AND u.deleted != '1'
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
	
	// Returns the status of this Project
	function getStatus(){
	    return $this->status;
	}
	
	// Returns the type of this Project
	function getType(){
	    return $this->type;
	}
	
	// Returns the Predecessors of this Project
	function getPreds(){
        if($this->preds === false){
	        $sql = "SELECT DISTINCT e.project_id, e.last_id
	                FROM `grand_project_evolution` e
	                WHERE e.new_id = '{$this->id}'
	                AND (e.id = '{$this->evolutionId}' OR e.action = 'MERGE')
	                AND '{$this->evolutionId}' > e.last_id
	                ORDER BY e.id DESC";
	        $data = DBFunctions::execSQL($sql);
	        $this->preds = array();
            foreach($data as $row){
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
	
	// Returns the Successor Project
	function getSucc(){
	    if(!is_array($this->succ) && $this->succ == false){
	        $sql = "SELECT e.new_id FROM
	                `grand_project_evolution` e
	                WHERE e.project_id = '{$this->id}'";
	        $data = DBFunctions::execSQL($sql);
	        if(count($data) > 0){
	            $this->succ = Project::newFromHistoricId($data[0]['new_id']);
	        }
	    }
	    return $this->succ;
	}
	
	// Returns the url of this Project's profile page
	function getUrl(){
	    global $wgServer, $wgScriptPath;
	    return "{$wgServer}{$wgScriptPath}/index.php/{$this->getName()}:Main";
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
	    if(strcmp($this->effectiveDate, date('Y-m-d H:i:s')) <= 0){
	        return $this->deleted;
	    }
	    return false;
	}
	
	// Returns when the evolution state took place
	function getEffectiveDate(){
	    return $this->effectiveDate;
	}
	
	// Returns an array of Person objects which represent
	// The researchers who are in this project.
	// If $filter is included, only users of that type will be selected
	function getAllPeople($filter = null){
	    $people = array();
	    $preds = $this->getPreds();
        foreach($preds as $pred){
            foreach($pred->getAllPeople($filter) as $person){
                $people[$person->getId()] = $person;
            }
        }
        if($this->peopleCache == null){
	        $sql = "SELECT m.user_id, u.user_name, SUBSTR(u.user_name, LOCATE('.', u.user_name) + 1) as last_name
                    FROM grand_project_members m, mw_user u
                    WHERE (m.end_date > CURRENT_TIMESTAMP OR m.end_date = '0000-00-00 00:00:00')
                    AND m.user_id = u.user_id
                    AND m.project_id = '{$this->id}'
                    AND `u.deleted` != '1'
                    ORDER BY last_name ASC";
	        $this->peopleCache = DBFunctions::execSQL($sql);
	    }
	    foreach($this->peopleCache as $row){
	        $id = $row['user_id'];
	        $person = Person::newFromId($id);
	        if(($filter == null || $person->isRole($filter)) && !$person->isRole(MANAGER)){
	            $people[$person->getId()] = $person;
	        }
	    }
	    return $people;
	}
	
	// Returns an array of Person objects which represent
	// The researchers who are in this project or were in the project during the specified period
	// If $filter is included, only users of that type will be selected
	function getAllPeopleDuring($filter = null, $startRange = false, $endRange = false, $includeManager=false){
	    if( $startRange === false || $endRange === false ){
	        $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
	        $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
	    }
	    $people = array();
	    $preds = $this->getPreds();
	    foreach($preds as $pred){
	        foreach($pred->getAllPeopleDuring($filter, $startRange, $endRange, $includeManager) as $person){
	            $people[$person->getId()] = $person;
	        }
	    }
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
	    foreach($data as $row){
	        $id = $row['user_id'];
	        $person = Person::newFromId($id);
	        if(($filter == null || $person->isRoleDuring($filter, $startRange, $endRange)) && ($includeManager || !$person->isRoleDuring(MANAGER, $startRange, $endRange))){
	            $people[$person->getId()] = $person;
	        }
	    }
	    return $people;
	}
	
	// Returns the contributions this relevant to this project
	function getContributions(){
	    if($this->contributions == null){
	        $this->contributions = array();
	        $preds = $this->getPreds();
	        foreach($preds as $pred){
	            foreach($pred->getContributions() as $contribution){
	                $this->contributions[$contribution->getId()] = $contribution;
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
	            $this->contributions[$row['id']] = Contribution::newFromId($row['id']);
	        }
	    }
	    return $this->contributions;
	}
	
	// Returns an array of Materials for this Project
	function getMultimedia(){
	    if($this->multimedia == null){
	        $this->multimedia = array();
	        $preds = $this->getPreds();
	        foreach($preds as $pred){
	            foreach($pred->getMultimedia() as $multimedia){
	                $this->multimedia[$multimedia->getId()] = $multimedia;
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
	
	// Returns the leader of this Project
	function getLeader(){
	    $sql = "SELECT pl.*
	            FROM grand_project_leaders pl, mw_user u
	            WHERE pl.project_id = '{$this->id}'
	            AND pl.type = 'leader'
	            AND pl.user_id <> '4'
	            AND pl.user_id <> '150'
	            AND u.user_id = pl.user_id
	            AND u.deleted != '1'
	            AND (pl.end_date = '0000-00-00 00:00:00'
                     OR pl.end_date > CURRENT_TIMESTAMP)";
	    $data = DBFunctions::execSQL($sql);
	    if(count($data) > 0){
	        return Person::newFromId($data[0]['user_id']);
	    }
	    else {
	        return null;
	    }
	}
	
	// Returns the co-leader of this Project
	function getCoLeader(){
	    $sql = "SELECT pl.*
	            FROM grand_project_leaders pl, mw_user u
	            WHERE pl.project_id = '{$this->id}'
	            AND pl.type = 'co-leader'
	            AND pl.user_id <> '4'
	            AND pl.user_id <> '150'
	            AND u.user_id = pl.user_id
	            AND u.deleted != '1'
	            AND (pl.end_date = '0000-00-00 00:00:00'
                     OR pl.end_date > CURRENT_TIMESTAMP)";
	    $data = DBFunctions::execSQL($sql);
	    if(DBFunctions::getNRows() > 0){
	        return Person::newFromId($data[0]['user_id']);
	    }
	    else {
	        return null;
	    }
	}
	
	/// Returns an array with the coleaders of the project.  By default, the
	/// resulting array contains instances of Person.  If #onlyid is set to
	/// true, then the resulting array contains only numerical user IDs.
	function getCoLeaders($onlyid = false){
	    $onlyIdStr = ($onlyid) ? 'true' : 'false';
	    if(isset($this->leaderCache['coleaders'.$onlyIdStr])){
	        return $this->leaderCache['coleaders'.$onlyIdStr];
	    }
	    $ret = array();
	    $preds = $this->getPreds();
        foreach($preds as $pred){
            foreach($pred->getCoLeaders($onlyid) as $leader){
                if($onlyid){
                    $ret[$leader] = $leader;
                }
                else{
                    $ret[$leader->getId()] = $leader;
                }
            }
        }
	    $sql = "SELECT pl.user_id FROM grand_project_leaders pl, mw_user u
				WHERE pl.project_id = '{$this->id}'
				AND pl.type = 'co-leader'
				AND pl.user_id NOT IN (4, 150)
				AND u.user_id = pl.user_id
				AND u.deleted != '1'
				AND (pl.end_date = '0000-00-00 00:00:00'
                     OR pl.end_date > CURRENT_TIMESTAMP)";
	    $data = DBFunctions::execSQL($sql);
		if ($onlyid) {
			foreach ($data as &$row)
				$ret[$row['user_id']] = $row['user_id'];
		}
		else {
			foreach ($data as &$row)
				$ret[$row['user_id']] = Person::newFromId($row['user_id']);
		}
        $this->leaderCache['coleaders'.$onlyIdStr] = $ret;
		return $ret;
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
	    $preds = $this->getPreds();
        foreach($preds as $pred){
            foreach($pred->getLeaders($onlyid) as $leader){
                if($onlyid){
                    $ret[$leader] = $leader;
                }
                else{
                    $ret[$leader->getId()] = $leader;
                }
            }
        }
        $sql = "SELECT pl.user_id FROM grand_project_leaders pl, mw_user u
				WHERE pl.project_id = '{$this->id}'
				AND pl.type = 'leader'
				AND pl.user_id NOT IN (4, 150)
				AND u.user_id = pl.user_id
				AND u.deleted != '1'
				AND (pl.end_date = '0000-00-00 00:00:00'
                     OR pl.end_date > CURRENT_TIMESTAMP)";
		$data = DBFunctions::execSQL($sql);
		if ($onlyid) {
			foreach ($data as &$row)
				$ret[$row['user_id']] = $row['user_id'];
		}
		else {
			foreach ($data as &$row)
				$ret[$row['user_id']] = Person::newFromId($row['user_id']);
		}
        $this->leaderCache['leaders'.$onlyIdStr] = $ret;
		return $ret;
	}
	
	// Returns the theme percentage of this project of the given theme index $i
	function getTheme($i, $history=false){
	    if(!($i >= 1 && $i <= 5)) return 0; // Fail Gracefully if the index was out of bounds, and return 0
	    if($this->themes == null){
	        $this->themes = array();
	        
	        $sql = "(SELECT themes 
	                FROM grand_project_descriptions d
	                WHERE d.project_id = '{$this->id}'\n";
	        if(!$history){
                $sql .= "AND evolution_id = '{$this->evolutionId}'
                         ORDER BY id DESC LIMIT 1)
                        UNION
                        (SELECT themes
                         FROM grand_project_descriptions d
                         WHERE d.project_id = '{$this->id}'";
            }
		    $sql .= "ORDER BY id DESC LIMIT 1)";
            
		    $data = DBFunctions::execSQL($sql);
            if(DBFunctions::getNRows() > 0){
                $themes = explode("\n", $data[0]['themes']);
                $this->themes = $themes;
            }
        }
        if(isset($this->themes[$i-1])){
            return $this->themes[$i-1];
        }
        return 0;
	}

	/// Returns all themes for the project as an associative array.
	function getThemes() {
		$ret = array('names' => array(), 'values' => array());
		// Put up the associative array.  Absent values default to 0.
		list($ret['names'][1], $ret['names'][2], $ret['names'][3], $ret['names'][4], $ret['names'][5]) =
			array('nMEDIA', 'GamSim', 'AnImage', 'SocLeg', 'TechMeth');
		list($ret['values'][1], $ret['values'][2], $ret['values'][3], $ret['values'][4], $ret['values'][5]) =
			array($this->getTheme(1), $this->getTheme(2), $this->getTheme(3), $this->getTheme(4), $this->getTheme(5));
		return $ret;
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
	
	/**
	 * Returns an array of Articles that belong to this Project
	 * @returns array Returns an array of Articles that belong to this Project
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
	
	// Returns an array of papers relating to this project
	function getPapers($category="all", $startRange = false, $endRange = false){
        return Paper::getAllPapersDuring($this->name, $category, "grand", $startRange, $endRange);
	}

	// Returns an array of the theme names.
	static function getDefaultThemeNames() {
		return array(1 => 'nMEDIA', 2 => 'GamSim', 3 => 'AnImage', 4 => 'SocLeg', 5 => 'TechMeth');
	}
	
	// Returns accronym the name of the theme
	static function getThemeName($themeId){
        switch($themeId){
            default:
            case 1:
                return "nMEDIA";
                break;
            case 2:
                return "GamSim";
                break;
            case 3:
                return "AnImage";
                break;
            case 4:
                return "SocLeg";
                break;
            case 5:
                return "TechMeth";
                break;
        }
	}
	
	// Returns a list of the evaluators who are evaluating this Project
	function getEvaluators($year = REPORTING_YEAR){
	    $eTable = getTableName("eval");
	    $sql = "SELECT *
	            FROM $eTable
	            WHERE sub_id = '{$this->id}'
				AND type = 'Project'
				AND year = '{$year}'";
	    $data = DBFunctions::execSQL($sql);
	    $subs = array();
        foreach($data as $row){
            $subs[] = Person::newFromId($row['eval_id']);
        }
        return $subs;
	}
	
	// Returns the comments for the Project when a user moved from this Project
	function getComments(){
	    if($this->comments == null){
	        $this->comments = array();
	        $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getComments() as $uId => $comment){
                    $this->comments[$uId] = $comment;
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
	        $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getStartDates() as $uId => $date){
                    $this->startDates[$uId] = $date;
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
	        $preds = $this->getPreds();
            foreach($preds as $pred){
                foreach($pred->getEndDates() as $uId => $date){
                    $this->endDates[$uId] = $date;
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
	
	// Returns the startDate for the given Person
	function getEndDate($person){
	    if($person != null && $person instanceof Person){
	        $this->getEndDates();
	        if(isset($this->endDates[$person->getId()])){
	            return $this->endDates[$person->getId()];
	        }
	        else{
	            return "";
	        }
	    }
	    else{
	        return "";
	    }
	}
	
	// Returns the current milestones of this project
	// If $history is set to true, all the milestones ever for this project are included
	function getMilestones($history=false){
	    if($this->milestones != null && !$history){
	        return $this->milestones;
	    }
	    $milestones = array();
	    $milestonesIds = array();
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
	    $sql = "SELECT DISTINCT milestone_id
	            FROM grand_milestones
	            WHERE project_id = '{$this->id}'";
	    if(!$history){
            $sql .= "\nAND start_date > end_date
                     AND status != 'Abandoned' AND status != 'Closed'";
        }
        $sql .= "\nORDER BY projected_end_date";
	    $data = DBFunctions::execSQL($sql);
	    
	    foreach($data as $row){
	        if(isset($milestoneIds[$row['milestone_id']])){
                continue;
            }
            $milestone = Milestone::newFromId($row['milestone_id']);
            $milestoneIds[$milestone->getMilestoneId()] = true;
            $milestones[] = $milestone;
	    }
	    
	    if(!$history){
	        $this->milestones = $milestones;
	    }
	    return $milestones;
	}

	// Returns the past milestones of this project
	function getPastMilestones(){
	    $milestoneIds = array();
	    $milestones = array();
	    $preds = $this->getPreds();
        foreach($preds as $pred){
            foreach($pred->getPastMilestones() as $milestone){
                if(isset($milestoneIds[$milestone->getMilestoneId()])){
                    continue;
                }
                $milestoneIds[$milestone->getMilestoneId()] = true;
                $milestones[] = $milestone;
            }
        }
	    $sql = "SELECT DISTINCT milestone_id
	            FROM grand_milestones
	            WHERE project_id = '{$this->id}'
                AND status IN ('Abandoned','Closed') 
                ORDER BY projected_end_date";
	    
	    $data = DBFunctions::execSQL($sql);
	    foreach($data as $row){
	        if(isset($milestoneIds[$row['milestone_id']])){
                continue;
            }
            $milestone = Milestone::newFromId($row['milestone_id']);
            $milestoneIds[$milestone->getMilestoneId()] = true;
            $milestones[] = $milestone;
	    }
	    return $milestones;
	}
	
	// Returns an array of milestones where all the milestones which were active at any time during the given year
	function getMilestonesDuring($year='0000'){
	    if($year == '0000'){
	        $year = date('Y');
	    }
	    $startRange = $year.'01-01 00:00:00';
	    $endRange = $year.'-12-31 23:59:59';
	    
	    $milestones = array();
	    $milestoneIds = array();
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
	    $sql = "SELECT MAX(id) as max_id, milestone_id
	            FROM grand_milestones
	            WHERE project_id ='{$this->id}'
	            AND milestone_id NOT IN ('".implode("','", $milestoneIds)."')
	            GROUP BY milestone_id
	            ORDER BY milestone_id";
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
                     AND ( (start_date > end_date AND status != 'Closed') OR ( $year BETWEEN YEAR(start_date)  AND YEAR(end_date) ) )";
            
            $data2 = DBFunctions::execSQL($sql2);
            if( count($data2) > 0 ){
                $row2 = $data2[0];
                if(isset($milestoneIds[$row2['milestone_id']])){
                    continue;
                }
                $milestoneIds[$row2['milestone_id']] = true;
                $milestones[] = Milestone::newFromId($row2['milestone_id']);
            }
	    }
	    return $milestones;
	}
    
    function getAllocatedBudget($year){
	    $projectBudget = null;
	    if(isset($this->budgets['s'.$year])){
	        return unserialize($this->budgets['s'.$year]);
	    }
	    $projectBudget = array();
	    $nameBudget = array();
	    $projectNames = array($this->name);
	    foreach($this->getAllPreds() as $pred){
	        $projectNames[] = $pred->getName();
	    }
	    foreach($this->getAllPeopleDuring(null, ($year+1)."-00-00 00:00:00", ($year + 2)."-00-00 00:00:00") as $member){
            if($member->isRole(PNI, ($year+1)."-00-00 00:00:00", ($year + 2)."-00-00 00:00:00") || 
               $member->isRole(CNI, ($year+1)."-00-00 00:00:00", ($year + 2)."-00-00 00:00:00")){
                $budget = $member->getAllocatedBudget($year);
                if($budget != null){
                    $budget = $budget->copy();
                    if(count($projectBudget) == 0){
                        $nameBudget[] = new Budget(array(array(HEAD1),
                                                         array(BLANK)),
                                                   array(array("Name of network investigator submitting request:"),
                                                         array("")));
                        $projectBudget[] = new Budget(array(array(HEAD1),
                                                           array(HEAD1),
                                                           array(HEAD2),
                                                           array(HEAD2),
                                                           array(HEAD2),
                                                           array(HEAD2),
                                                           array(HEAD1),
                                                           array(HEAD2),
                                                           array(HEAD2),
                                                           array(HEAD2),
                                                           array(HEAD1),
                                                           array(HEAD1),
                                                           array(HEAD1),
                                                           array(HEAD2),
                                                           array(HEAD2),
                                                           array(HEAD2)),
                                                     array(array("Budget Categories for April 1, ".($year).", to March 31, ".($year+1).""),
                                                           array("1) Salaries and stipends"),
                                                           array("a) Graduate students"),
                                                           array("b) Postdoctoral fellows"),
                                                           array("c) Technical and professional assistants"),
                                                           array("d) Undergraduate students"),
                                                           array("2) Equipment"),
                                                           array("a) Purchase or rental"),
                                                           array("b) Maintenance costs"),
                                                           array("c) Operating costs"),
                                                           array("3) Materials and supplies"),
                                                           array("4) Computing costs"),
                                                           array("5) Travel expenses"),
                                                           array("a) Field trips"),
                                                           array("b) Conferences"),
                                                           array("c) GRAND annual conference")));
                    }
                    $nBudget = $budget->copy()->limit(0, 1)->select(V_PERS_NOT_NULL)->union(new Budget());
                    $pBudget = $budget->copy()->select(V_PROJ, $projectNames)->limit(6, 16);
                    if($pBudget->nRows()*$pBudget->nCols() > 0){
                        $nameBudget[] = $nBudget;
                        $projectBudget[] = $pBudget;
                    }
                }
            }
        }
        $nameBudget = Budget::join_tables($nameBudget)->join(Budget::union_tables(array(new Budget(), new Budget())));
        
        $projectBudget = Budget::join_tables($projectBudget);
        if($projectBudget != null){
            $this->budgets['s'.$year] = $nameBudget->union($projectBudget->cube());
            $this->budgets['s'.$year] = serialize($this->budgets['s'.$year]);
            return unserialize($this->budgets['s'.$year]);
        }
        else{
            return null;
        }
    }
    
    function getRequestedBudget($year){
	    $projectBudget = null;
	    if(isset($this->budgets['r'.$year])){
	        return unserialize($this->budgets['r'.$year]);
	    }
	    $year_fr = $year+1;
	    $year_to = $year+2;
	    $projectBudget = array();
	    $nameBudget = array();
	    $projectNames = array($this->name);
	    foreach($this->getAllPreds() as $pred){
	        $projectNames[] = $pred->getName();
	    }

	    $alreadySeen = array();

	    foreach($this->getAllPeopleDuring(null, ($year)."-00-00 00:00:00", ($year + 1)."-00-00 00:00:00") as $member){
            if($member->isRoleDuring(PNI, ($year)."-00-00 00:00:00", ($year + 1)."-00-00 00:00:00") || 
               $member->isRoleDuring(CNI, ($year)."-00-00 00:00:00", ($year + 1)."-00-00 00:00:00")){
                if(isset($alreadySeen[$member->getId()])){
                    continue;
                }
                $alreadySeen[$member->getId()] = true;
                $budgets = array();
                $budgets[] = $member->getRequestedBudget($year);
                
                foreach($budgets as $budget){
                    if($budget != null){
                        if(count($projectBudget) == 0){
                            $nameBudget[] = new Budget(array(array(HEAD1)),
                                                       array(array("Name of network investigator submitting request:")));
                            $projectBudget[] = new Budget(array(array(HEAD1),
                                                               array(HEAD1),
                                                               array(HEAD2),
                                                               array(HEAD2),
                                                               array(HEAD2),
                                                               array(HEAD2),
                                                               array(HEAD1),
                                                               array(HEAD2),
                                                               array(HEAD2),
                                                               array(HEAD2),
                                                               array(HEAD1),
                                                               array(HEAD1),
                                                               array(HEAD1),
                                                               array(HEAD2),
                                                               array(HEAD2),
                                                               array(HEAD2)),
                                                         array(array("Budget Categories for April 1, {$year_fr}, to March 31, {$year_to}"),
                                                               array("1) Salaries and stipends"),
                                                               array("a) Graduate students"),
                                                               array("b) Postdoctoral fellows"),
                                                               array("c) Technical and professional assistants"),
                                                               array("d) Undergraduate students"),
                                                               array("2) Equipment"),
                                                               array("a) Purchase or rental"),
                                                               array("b) Maintenance costs"),
                                                               array("c) Operating costs"),
                                                               array("3) Materials and supplies"),
                                                               array("4) Computing costs"),
                                                               array("5) Travel expenses"),
                                                               array("a) Field trips"),
                                                               array("b) Conferences"),
                                                               array("c) GRAND annual conference")));
                        }
                        $nBudget = $budget->copy()->limit(0, 1)->select(V_PERS_NOT_NULL)->union(new Budget());
                        $pBudget = $budget->copy()->select(V_PROJ, $projectNames)->limit(6, 16);
                        if($pBudget->nRows()*$pBudget->nCols() > 0){
                            $nameBudget[] = $nBudget;
                            $projectBudget[] = $pBudget;
                        }
                    }
                }
            }
        }
        $nameBudget = Budget::join_tables($nameBudget)->join(Budget::union_tables(array(new Budget(), new Budget())));
        $projectBudget = Budget::join_tables($projectBudget);
        if($projectBudget != null){
            $this->budgets['r'.$year] = $nameBudget->union($projectBudget->cube());
            $this->budgets['r'.$year] = serialize($this->budgets['r'.$year]);
            return unserialize($this->budgets['r'.$year]);
        }
        else{
            return null;
        }
    }
    
    static function createTab(){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle;
        $me = Person::newFromId($wgUser->getId());
        $projects = $me->getProjects();
        
        $selected = "";
        foreach($me->getProjects() as $project){
		    if($wgTitle->getNSText() == $project->getName()){
		        $selected = "selected";
		        break;
		    }
		}
        
        echo "<li class='top-nav-element $selected'>\n";
		echo "	<span class='top-nav-left'>&nbsp;</span>\n";
		echo "	<a id='lnk-my_projects' class='top-nav-mid' href='{$projects[0]->getUrl()}' class='new'>My Projects</a>\n";
		echo "	<span class='top-nav-right'>&nbsp;</span>\n";
		echo "</li>";
    }
}

?>
