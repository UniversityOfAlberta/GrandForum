<?php

class Paper extends BackboneModel{

    static $cache = array();
    static $dataCache = array();

    var $id;
    var $category;
    var $description;
	var $title;
	var $type;
	var $projects;
	var $date;
	var $venue;
	var $status;
	var $authors;
	var $data;
	var $lastModified;
	var $authorsWaiting;
	var $deleted;
	
	// Returns a new Paper from the given id
	static function newFromId($id){
	    if(isset(self::$cache[$id])){
	        return self::$cache[$id];
	    }
		$sql = "SELECT *
			    FROM grand_products
			    WHERE id = '$id'";
		$data = DBFunctions::execSQL($sql);
		$paper = new Paper($data);
        self::$cache[$paper->id] = &$paper;
        self::$cache[$paper->title] = &$paper;
		return $paper;
	}
	
	// Returns a new Paper from the given id
	static function newFromTitle($title, $category = "%", $type = "%", $status = "%"){
	    $title = str_replace("&#58;", ":", $title);
	    $title = str_replace("'", "&#39;", $title);
	    $category = mysql_real_escape_string($category);
	    $type = mysql_real_escape_string($type);
	    $status = mysql_real_escape_string($status);
	    if(isset(self::$cache[$title.$category.$type.$status])){
	        return self::$cache[$title.$category.$type.$status];
	    }
	    
		$sql = "SELECT *
			    FROM grand_products
			    WHERE (`title` = '$title' OR
			           `title` = '".str_replace(" ", "_", $title)."')
				AND `category` LIKE '$category'
				AND `type` LIKE '$type'
				AND `status` LIKE '$status'
				ORDER BY `id` desc";
		$data = DBFunctions::execSQL($sql);
		$paper = new Paper($data);
        self::$cache[$paper->id] = &$paper;
        self::$cache[$paper->getTitle().$category.$type.$status] = &$paper;
        self::$cache[$paper->getTitle().$paper->getCategory().$paper->getType().$paper->getStatus()] = &$paper;
		return $paper;
	}
	
	//Fetch all product types
	static function getTypes(){
		$types = array();
		$sql = "SELECT DISTINCT type FROM grand_product_types";
		$data = DBFunctions::execSQL($sql);
		foreach($data as $row){
			$types[] = $row['type'];
		}
		
		return $types;
	}
	
	//Fetch all product subtypes for a given type
	static function getSubTypes($type){
		$subtypes = array();
		if($type == ''){
			return $subtypes;
		}
		
		$sql = "SELECT subtype FROM grand_product_types WHERE type = '$type'";
		$data = DBFunctions::execSQL($sql);
		foreach($data as $row){
			$subtypes[] = $row['subtype'];
		}
		
		return $subtypes;
	}
	
	// Returns all of the papers in the database
	// from the given project
	// $project: specifies which project the returned papers should be associated with
	// $category: specifies which category the returned papers should be of('Publication', 'Artifact' etc.)
	// $grand: whether to include grand-only, non-grand-only or both
	static function getAllPapers($project='all', $category='all', $grand='grand'){
	    $data = array();
	    if(isset(self::$dataCache[$project.$category.$grand])){
	        return self::$dataCache[$project.$category.$grand];
	    }
	    else{
	        $papers = array();
	        if($project != "all"){
	            if($project instanceof Project){
	                $p = $project;
	            }
	            else{
                    $p = Project::newFromHistoricName($project);
                }
	            $preds = $p->getPreds();
	            foreach($preds as $pred){
	                foreach(Paper::getAllPapers($pred->getName(), $category, $grand) as $paper){
	                    $papers[$paper->getId()] = $paper;
	                }
	            }
	        }
	        if($project instanceof Project){
                $project = $project->getName();
            }
	        $sql = "SELECT *
			        FROM `grand_products`
			        WHERE `deleted` = '0' ";
            if($project != "all"){
	            $sql .= "\nAND projects LIKE '%$project%'";
            }
            if($category != "all"){
                //if($project != "all"){
                //    $sql .= "\nAND ";
                //}
                $sql .= "AND category = '$category'";
            }
            $sql .= "\nORDER BY `type`, `title`";
	        $data = DBFunctions::execSQL($sql);
	        foreach($data as $row){
	            $rowA = array();
	            $rowA[0] = $row;
	            $unserialized = unserialize($row['projects']);
                if(($grand == 'grand' && count($unserialized) > 0) ||
                   ($grand == 'nonGrand' && count($unserialized) == 0) ||
                    $grand == 'both'){
                    if(isset(self::$cache[$row['id']])){
                        $papers[] = self::$cache[$row['id']];
                    }
                    else{
                        $paper = new Paper($rowA);
                        self::$cache[$paper->id] = $paper;
                        $papers[$paper->getId()] = $paper;
                    }
                }
	        }
	        self::$dataCache[$project.$category.$grand] = $papers;
	    }
	    return $papers;
	}
	
	// Returns all of the papers in the database
	// from the given project
	// $project: specifies which project the returned papers should be associated with
	// $category: specifies which category the returned papers should be of('Publication', 'Artifact' etc.)
	// $grand: whether to include grand-only, non-grand-only or both
	// $strict: whether to stick with the date range for everything(true), or show anything 'to appear' as well (false)
	static function getAllPapersDuring($project='all', $category='all', $grand='grand', $startRange = false, $endRange = false, $strict = true){
		if( $startRange === false || $endRange === false ){
	        $startRange = date(REPORTING_YEAR."-01-01 00:00:00");
	        $endRange = date(REPORTING_YEAR."-12-31 23:59:59");
	    }
	    $str = ($strict) ? 'true' : 'false';
	    $proj = $project;
	    if($project instanceof Project){
	        $proj = $project->getName();
	    }
	    if(isset(self::$dataCache[$proj.$category.$grand.$startRange.$endRange.$str])){
	        return self::$dataCache[$proj.$category.$grand.$startRange.$endRange.$str];
	    }
	    else{
	        $papers = array();
	        if($project != "all"){
	            if($project instanceof Project){
	                $p = $project;
	            }
	            else{
                    $p = Project::newFromHistoricName($project);
                }
                $preds = $p->getPreds();
                foreach($preds as $pred){
                    foreach(Paper::getAllPapersDuring($pred, $category, $grand, $startRange, $endRange) as $paper){
                        $papers[$paper->getId()] = $paper;
                    }
                }
            }
            if($project instanceof Project){
                $project = $project->getName();
            }
	        $data = array();
	        
            $sql = "SELECT *
		            FROM grand_products
		            WHERE deleted = '0' AND ";
            if($project != "all" || $category != "all"){
                //$sql .= "WHERE ";
            }
            if($project != "all"){
                $sql .= "projects LIKE '%$project%' AND ";
            }
            if($category != "all"){
                //if($project != "all"){
                //    $sql .= "\nAND ";
                //}
                $sql .= "category = '$category' AND ";
            }
            if($strict){
                $sql .= "\n date BETWEEN '$startRange' AND '$endRange'";
            }
            else{
                $sql .= "\n(date BETWEEN '$startRange' AND '$endRange' OR (date >= '$startRange' AND category = 'Publication' AND status != 'Published' AND status != 'Submitted' ))";
            }
            $sql .= "\nORDER BY `type`, `title`";
            
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $rowA = array();
                $rowA[0] = $row;
                $unserialized = unserialize($row['projects']);
                if(($grand == 'grand' && count($unserialized) > 0) ||
                   ($grand == 'nonGrand' && count($unserialized) == 0) ||
                    $grand == 'both'){
                    $paper = new Paper($rowA);
                    $papers[$paper->getId()] = $paper;
                }
            }
	        self::$dataCache[$proj.$category.$grand.$startRange.$endRange.$str] = $papers;
	        return $papers;
	    }
	}

	// Returns all Papers in the DB.
	// This is pretty slow, and should not be used often.
	static function getAllPapersForThesis($person){
	    $similarNames = $person->getSimilarNames();
	    $sql = "SELECT *
			    FROM grand_products
			    WHERE deleted = '0' ";
		if(count($similarNames) > 0){
		    $names = array();
            foreach($similarNames as $name){
                $names[] = "authors LIKE '%$name%'";
            }
            $sql .= " AND (".implode("OR\n", $names).')';
        }
	    $data = DBFunctions::execSQL($sql);
	    $papers = array();
	    foreach($data as $row){
	        $rowA = array();
	        $rowA[0] = $row;
	        $papers[] = new Paper($rowA);
	    }
	    return $papers;
	}
	
	// Searches for the given phrase in the table of publications
	// Returns an array of publications which fit the search
	static function search($phrase, $category='all'){
	    session_write_close();
	    $splitPhrase = explode(" ", $phrase);
	    $sql = "SELECT id, title, date, projects FROM grand_products
	            WHERE title LIKE '%'
	            AND deleted != '1'\n";
	    foreach($splitPhrase as $word){
	        $sql .= "AND title LIKE '%$word%'\n";
	    }
	    if($category != "all"){
	        $sql .= "AND category = '$category'";
	    }
	    $data = DBFunctions::execSQL($sql);
	    $papers = array();
	    foreach($data as $row){
	    	$projects = array();
	    	foreach(unserialize($row['projects']) as $p){
	    		$projects[] = $p;
	    	}
	    	$projects = implode(', ', $projects);
	        $papers[] = array("id"=>$row['id'], "title"=>$row['title'], "date"=>$row['date'], "projects"=>$projects);
	    }
	    $json = json_encode($papers);
	    return $json;
	}
	
	// Constructor
	function Paper($data){
		if(count($data) > 0){
			$this->id = $data[0]['id'];
			$this->category = $data[0]['category'];
			$this->description = $data[0]['description'];
			$this->title = $data[0]['title'];
			$this->type = $data[0]['type'];
			$this->date = $data[0]['date'];
			$this->venue = $data[0]['venue'];
			$this->status = $data[0]['status'];
			$this->deleted = $data[0]['deleted'];
			$this->projects = array();
			$this->authors = $data[0]['authors'];
			$this->authorsWaiting = true;
			foreach(unserialize($data[0]['projects']) as $project){
			    if(is_numeric($project)){
			        $proj = Project::newFromId($project);
			    }
                else{
                    $proj = Project::newFromName($project);
                }
	            $this->projects[] = $proj;
            }
			$this->data = unserialize($data[0]['data']);
			$this->lastModified = $data[0]['last_modified'];
		}
	}
	
	// Returns the Id of this Paper
	function getId(){
		return $this->id;
	}
	
	// Returns the category of this Paper
	// Either: Publication or Artifact
	function getCategory(){
	    return $this->category;
	}
	
	// Returns the abstract or description of this Paper
	function getDescription(){
	    return $this->description;
	}
	
	// Returns the title of this Paper
	function getTitle(){
		return $this->title;
	}
	
	// Returns the title of this Paper
	function getStatus(){
		return $this->status;
	}
	
	function isPublished(){
	    $status = $this->getStatus();
	    switch ($this->getType()) {
            case 'Journal Paper':
            case 'Magazine/Newspaper Article':
                if($status != "Published" && $status != "Submitted"){
                    return false;
                }
                return true;
                break;
            case 'Masters Thesis':
            case 'PhD Thesis':
            case 'Tech Report':
            case 'Misc':
            case 'Poster':
            case 'Book':
            case 'Book Chapter':
            case 'Collections Paper':
            case 'Proceedings Paper':
            default:
                if($status != "Published"){
                    return false;
                }
                return true; 
                break;
        }
	}
	
    // Returns the wiki formatted title of this Paper (The page that it resides)
	function getWikiTitle(){
		return str_replace("?", "%3F", $this->title);
	}
	
	// Returns the url of this Paper's page
	function getUrl(){
	    global $wgServer, $wgScriptPath;
	    return "{$wgServer}{$wgScriptPath}/index.php/{$this->getCategory()}:{$this->getId()}";
	    //return "{$wgServer}{$wgScriptPath}/index.php/Special:Products#/{$this->getCategory()}/{$this->getId()}";
	}
	
	// Returns an array of authors who wrote this Paper
	function getAuthors($evaluate=true, $cache=true){
	    if($this->authorsWaiting && $evaluate){
	        $authors = array();
	        $unserialized = unserialize($this->authors);
	        foreach(@$unserialized as $author){
	            if($author == ""){
	                continue;
	            }
			    $person = null;
			    if(is_numeric($author)){
			        $person = Person::newFromId($author);
			    }
			    else{
                    $person = Person::newFromNameLike($author);
                    if($person == null || $person->getName() == null || $person->getName() == ""){
                        // The name might not match exactly what is in the db, try aliases
                        try{
                            $person = Person::newFromAlias($author);
                        }
                        catch(DomainException $e){
                            $person = null;
                        }
                    }
                }
	            if($person == null || $person->getName() == null || $person->getName() == ""){
	                // Ok this person is not in the db, make a fake Person object
	                $pdata = array();
	                $pdata[0]['user_id'] = "";
	                $pdata[0]['user_name'] = $author;
	                $pdata[0]['user_real_name'] = $author;
	                $pdata[0]['user_email'] = "";
	                $pdata[0]['user_gender'] = "";
	                $pdata[0]['user_twitter'] = "";
	                $pdata[0]['user_nationality'] = "";
	                $pdata[0]['user_public_profile'] = "";
	                $pdata[0]['user_private_profile'] = "";
	                $person = new Person($pdata);
	                if($cache){
	                    Person::$cache[$author] = $person;
	                }
	            }
	            $authors[] = $person;
            }
            return $authors;
            $this->authorsWaiting = false;
            $this->authors = $authors;
	    }
        return $this->authors;
	}
	
	/**
	 * Synchronizes the `grand_products` table and the `grand_product_authors` table for this Paper
	 * @param boolean $massSync Whether or not to run this for a massSynchronization, or just for this Paper
	 * @return array If $massSync=true, returns the sql statements required to update the DB
	 */
	function syncAuthors($massSync=false){
	    $deleteSQL = "DELETE FROM `grand_product_authors`
	            WHERE `product_id` = '{$this->id}'";
	    $order = 0;
        $insertSQL = "INSERT INTO `grand_product_authors`
                      (`author`, `product_id`, `order`) VALUES\n";
	    $authors = $this->getAuthors();
	    $inserts = array();
	    $alreadyDone = array();
	    foreach($authors as $key => $author){
	        if(isset($alreadyDone[$author->getName()])){
	            continue;
	        }
	        $alreadyDone[$author->getName()] = true;
	        if($author->getId() != ""){
	            $inserts[] = "('{$author->getId()}','{$this->getId()}','{$order}')";
	        }
	        else{
	            $name = mysql_real_escape_string($author->getName());
	            $inserts[] = "('{$name}','{$this->getId()}','{$order}')";
	        }
	        $order++;
	    }
	    if(!$massSync){
	        DBFunctions::begin();
	        DBFunctions::execSQL($deleteSQL, true, true);
	        if(count($authors) > 0){
	            DBFunctions::execSQL($insertSQL.implode(",\n", $inserts), true, true);
	        }
	        DBFunctions::commit();
	    }
	    else{
	        return array($deleteSQL, $inserts);
	    }
	}
	
	// Returns whether or not this paper belongs to the specified project
	function belongsToProject($project){
	    if($project == null){
	        return false;
	    }
	    foreach($this->projects as $p){
	        if($p != null && $p->getId() == $project->getId()){
	            return true;
	        }
	    }
	    return false;
	}
	
	// Returns an array of Projects which this Paper is related to
	function getProjects(){
	    if($this->projects != null){
	        foreach($this->projects as $key => $project){
	            if($project == null){
	                unset($this->projects[$key]);
	            }
	        }
	    }
	    return $this->projects;
	}
	
	// Returns the date of this Paper
	function getDate(){
	    return $this->date;
	}
	
	// Returns the type of this Paper
	function getType(){
	    return $this->type;
	}
	
	// Returns the venue for this Paper
	function getVenue(){
		$venue = $this->venue;

		if( empty($venue) ){
			$venue = ArrayUtils::get_string($this->data, 'event_title');
		}

		if( empty($venue) ){
			$venue = ArrayUtils::get_string($this->data, 'conference');
		}

		if( empty($venue) ){
			$venue = ArrayUtils::get_string($this->data, 'event_location');
		}

		if(empty($venue)){
			$venue = ArrayUtils::get_string($this->data, 'location');
		}

	    return $venue;
	}
	
	// Returns the domain specific data for this Paper
	function getData(){
	    return $this->data;
	}
	
	// Return the deleted flag for this Paper
	function isDeleted(){
		return ($this->deleted === "1");
	}

	// Returns whether or not this Paper has been reported in the given year, with the reported type (must be either 'RMC' or 'NCE')
	function hasBeenReported($year, $reportedType){
	    if(($reportedType == 'RMC' || $reportedType == 'NCE')){
	        $years = $this->getReportedYears($reportedType);
	        if(isset($years[$year])){
	            return true;
	        }
	    }
	    return false;
	}
	
	function getReportedYears($reportedType){
	    $years = array();
        if(($reportedType == 'RMC' || $reportedType == 'NCE')){
	        $sql = "SELECT DISTINCT `year`
	                FROM `grand_products_reported`
	                WHERE `product_id` = '{$this->id}'
	                AND `reported_type` = '{$reportedType}'";
	        $data = DBFunctions::execSQL($sql);
	        foreach($data as $row){
	            $years[$row['year']] = $row['year'];
	        }
	    }
	    return $years;
    }
	
	function getProperCitation(){
		global $wgServer, $wgScriptPath;

		$data = $this->getData();
        $type = $this->getType();
        $title = $this->getTitle();
        $status = $this->getStatus();
        $category = $this->getCategory();
        $au = array();
        foreach($this->getAuthors() as $a){
            if($a->getId()){
                $au[] = "<a target='_blank' href='{$a->getUrl()}'><strong>". $a->getNameForForms() ."</strong></a>";
            }else{
                $au[] = $a->getNameForForms();
            }
        }
        $au = implode(',&nbsp;', $au);
        $yr = substr($this->getDate(), 0, 4);
        $vn = $this->getVenue();

        if(($type == "Proceedings Paper" || $category == "Presentation") && empty($vn)){
            $vn = "(no venue)";
        }

        //This is not really a venue, but this is how we want to put this into the proper citation
        if(($type == "Journal Paper" || $type == "Journal Abstract") && empty($vn)){
            $vn = ArrayUtils::get_string($data, 'journal_title');
            if(empty($vn)){
            	$vn = ArrayUtils::get_string($data, 'published_in');
            }
        }
        if(($type == "Journal Paper")){
            $volume = ArrayUtils::get_string($data, 'volume');
            $number = ArrayUtils::get_string($data, 'number');
            if(!empty($volume)){
                $vn .= " $volume";
            }
            if(!empty($number)){
                $vn .= "($number)";
            }
        }


        $pg = ArrayUtils::get_string($data, 'pages');
        if (strlen($pg) > 0){
            $pg = "{$pg}pp.";
        }
        else{
            $pg = "(no pages)";
        }
        $pb = ArrayUtils::get_string($data, 'publisher', '(no publisher)');
        

        $peer_rev = "";
        if($category == "Publication"){
        	if(isset($data['peer_reviewed']) && $data['peer_reviewed'] == "Yes"){
        		$peer_rev = ",&nbsp;Peer Reviewed";
        	}
        	else if(isset($data['peer_reviewed']) && $data['peer_reviewed'] == "No"){
        		$peer_rev = ",&nbsp;Not Peer Reviewed";
        	}
        }

        if( in_array($type, array('Book', 'Collections Paper', 'Proceedings Paper', 'Journal Paper'))){
            if($vn != ""){
                $vn .= ".";
            }
       		$citation = "{$au}.&nbsp;{$yr}.&nbsp;<i><a href='$wgServer$wgScriptPath/index.php/{$category}:{$this->getId()}'>{$title}</a>.</i>&nbsp;{$type}:&nbsp;{$vn}&nbsp;{$pg}&nbsp;{$pb}<span class='pdfnodisplay'>,&nbsp;{$status}{$peer_rev}</span>";
    	}
    	else{
    	    if($vn != ""){
                $vn .= "<span class='pdfnodisplay'>,</span>";
            }
        	$citation = "{$au}.&nbsp;{$yr}.&nbsp;<i><a href='$wgServer$wgScriptPath/index.php/{$category}:{$this->getId()}'>{$title}</a>.</i>&nbsp;{$type}:&nbsp;{$vn}&nbsp;<span class='pdfnodisplay'>{$status}{$peer_rev}</span>";
        }

		return trim($citation);
	}

	static function friendly_type($type) {
		switch ($type) {
		case 'Book':
			return 'Book/Book chapter';
		case 'Collection':
		case 'Proceedings_Paper':
			return 'Proceedings paper';
		case 'Journal_Paper':
			return 'Article';
		case 'Manual':
			return $type;
		case 'MastersThesis_Paper':
			return 'M.Sc. thesis';
		case 'Misc_Paper':
			return 'Miscellaneous';
		case 'PHDThesis_Paper':
			return 'Ph.D. thesis';
		case 'Poster':
		case 'Poster_Ref':
			return 'Poster';
		case 'TechReport':
			return 'Technical report';
		}

		return '';
	}
	
	static function getPublicationTypes(){
		$pub_types = array();
		
		$sql = "SELECT DISTINCT type
			    FROM grand_products
			    WHERE category = 'Publication'";
		$data = DBFunctions::execSQL($sql);
		
		foreach ($data as $row){
			$pub_types[] = $row['type'];
		}
		
		return $pub_types;
	}

	static function getCategoryTypes($category){
		$types = array();
		
		$sql = "SELECT DISTINCT type
			    FROM grand_products
			    WHERE category = '{$category}'";
		$data = DBFunctions::execSQL($sql);
		
		foreach ($data as $row){
			$types[] = $row['type'];
		}
		
		return $types;
	}
	
	// Returns an array of strings representing all the custom misc types
	static function getAllMiscTypes($category="%"){
	    $sql = "SELECT DISTINCT SUBSTR(type, 7) as type
	            FROM grand_products
	            WHERE SUBSTR(type, 1, 6) = 'Misc: ' AND
	            category LIKE '$category'";
	    $data = DBFunctions::execSQL($sql);
	    $return = array();
	    foreach($data as $row){
	        $return[] = $row['type'];
	    }
	    return $return;
	}

	function create(){
	    
	}
	
	function update(){
	
	}
	
	function delete(){
	    $me = Person::newFromWGUser();
	    if($me->isLoggedIn()){
	        $status = DBFunctions::update('grand_products',
	                                array('deleted' => '1'),
	                                array('id' => $this->getId()));
	        if($status){
	            if(function_exists('apc_delete')){
	                apc_delete($this->getCacheId());
	            }
                foreach($this->getAuthors() as $author){
                    if($author instanceof Person && $me->getId() != $author->getId()){
                        Notification::addNotification($me, $author, "{$this->getCategory()} Deleted", "Your ".strtolower($this->getCategory())." entitled <i>{$this->getTitle()}</i> has been deleted", "{$this->getUrl()}");
                    }
                }
                self::$cache = array();
                self::$dataCache = array();
            }
	        return $status;
	    }
	    return false;
	}

	function toArray(){
	    if(function_exists('apc_exists') && apc_exists($this->getCacheId())){
	        $json = apc_fetch($this->getCacheId());
	        $authors = $json['authors'];
	        $change = false;
	        foreach($authors as $key => $author){
	            // Make sure new authors have not been added, and if so re-cache
	            if($author['id'] == 0){
	                $person = Person::newFromName($author['name']);
	                if($person == null || $person->getName() == ""){
	                    $person = Person::newFromNameLike($author['name']);
	                }
	                if($person == null || $person->getName() == ""){
	                    $person = Person::newFromAlias($author['name']);
	                }
	                
	                if($person != null && $person->getName() != ""){
	                    $change = true;
	                    $authors[$key] = array('id' => $person->getId(),
	                                           'name' => $person->getNameForForms(),
	                                           'url' => $person->getUrl());
	                }
	            }
	        }
	        $json['authors'] = $authors;
	        if($change && function_exists('apc_store')){
	            apc_store($this->getCacheId(), $json, 60*60);
	        }
	        return $json;
	    }
	    else{
	        $authors = array();
	        $projects = array();
	        foreach($this->getAuthors(true, false) as $author){
	            $authors[] = array('id' => $author->getId(),
	                               'name' => $author->getNameForForms(),
	                               'url' => $author->getUrl());
	        }
	        foreach($this->getProjects() as $project){
	            $projects[] = array('id' => $project->getId(),
	                                'name' => $project->getName(),
	                                'url' => $project->getUrl());
	        }
	        
            $json = array('id' => $this->getId(),
	                      'title' => $this->getTitle(),
	                      'description' => $this->getDescription(),
	                      'category' => $this->getCategory(),
	                      'type' => $this->getType(),
	                      'status' => $this->getStatus(),
	                      'date' => $this->getDate(),
	                      'url' => $this->getUrl(),
	                      'data' => $this->getData(),
	                      'authors' => $authors,
	                      'projects' => $projects,
	                      'lastModified' => $this->lastModified,
	                      'deleted' => $this->isDeleted());
	        
	        if(function_exists('apc_store')){
	            apc_store($this->getCacheId(), $json, 60*60);
	        }
	        return $json;
	    }
	}
	
	function exists(){

	}
	
	function getCacheId(){
	    global $wgSitename;
	    return $wgSitename.'product'.$this->getId();
	}
}
?>
