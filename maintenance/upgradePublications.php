<?php
    require_once('commandLine.inc');
class PaperOld{

    static $cache = array();

    var $id;
	var $title;
	var $type;
	
	// Returns a new Paper from the given id
	static function newFromId($id){
	    if(isset(self::$cache[$id])){
	        return self::$cache[$id];
	    }
		$pTable = getTableName("page");
		$nsTable = getTableName("an_extranamespaces");
		$sql = "SELECT *
			    FROM $pTable p, $nsTable ns
			    WHERE p.page_id = '$id'
			    AND p.page_namespace = ns.nsId";
		$data = PaperOld::execSQL($sql);
		$paper = new PaperOld($data);
        self::$cache[$paper->id] = &$paper;
		return $paper;
	}
	
	// Returns all of the papers in the database
	// written by users of the type $userType
	static function getAllPapers($userType=""){
	  $nsTable = getTableName("an_extranamespaces");
	    $pTable = getTableName("page");
	    $lTable = getTableName("pagelinks");
	    $rTable = getTableName("user_create_request");
	    $tTable = getTableName("templatelinks");
	    $sql = "SELECT DISTINCT fromP.page_id as page_id
	            FROM $nsTable fromNs, $pTable fromP, $rTable r, $tTable t
	            WHERE fromP.page_namespace = fromNs.nsId
	            AND (fromNs.nsName = 'Paper' OR fromNs.nsName = 'Book' OR fromNs.nsName = 'Poster')
	            AND t.tl_title <> 'Poster'
	            AND fromP.page_id = t.tl_from";
	    $data = DBFunctions::execSQL($sql);
	    $papers = array();
	    foreach($data as $row){
	        $papers[] = PaperOld::newFromId($row['page_id']);
	    }
	    return $papers;
	}
	
	// Constructor
	function PaperOld($data){
		if(count($data) > 0){
			$this->id = $data[0]['page_id'];
			$this->title = $data[0]['page_title'];
			$this->type = $data[0]['nsName'];
		}
	}
	
	// Returns the Id of this Paper
	function getId(){
		return $this->id;
	}
	
	// Returns the title of this Paper
	function getTitle(){
    $title = str_replace("_", " ", $this->title);
    $title = ucwords($title);
		return $title;
	}
	
	// Returns an array of authors who wrote this paper
	function getAuthors(){
	  $authors = array();
	  $article = Article::newFromId($this->id);
	  if($article != null){
	    $text = $article->getRawText();
	    $scraped = ResearcherProductivity::scrape_publication($text);
	    $people = $scraped['authors'];
	    foreach($people as $person){
	      try{
	        $author = Person::newFromAlias($person);
	        if($author != null && $author->getName() != null){
	          $authors[] = $author;
	        }
	      }
	      catch(DomainException $e){
	      
	      }
	    }
	  }
    return $authors;
	}
	
	// Returns the wiki formatted title of this Paper (The page that it resides)
	function getWikiTitle(){
		return $this->title;
	}
	
	// Returns the type of this Paper
	function getType(){
	    return $this->type;
	}
	
	static function execSQL($sql){
		$dbr = wfGetDB(DB_SLAVE);
		$result = $dbr->query($sql);
		$rows = array();
		while($row = $dbr->fetchRow($result)){
			$rows[] = $row;
		}
		return $rows;
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
}

$pds = array();
    $arr_a1 = array();
	$arr_a2 = array();
	$arr_b = array();
	$arr_c = array();
    foreach(Project::getAllProjects() as $p){
        $pd = new ProjectProductivity($p);
        $tmp = $pd->get_metric(PJST_PUBLICATIONS);
		$tmparr = ArrayUtils::get_array($tmp, 'table7');
		$arr_a1 = $arr_a1 + ArrayUtils::get_array($tmparr, 'a1');
		$arr_a2 = $arr_a2 + ArrayUtils::get_array($tmparr, 'a2');
		$arr_b = $arr_b + ArrayUtils::get_array($tmparr, 'b');
		$arr_c = $arr_c + ArrayUtils::get_array($tmparr, 'c');
		foreach ($arr_a1 as &$elem) {
            $title = str_replace("'", "&#39;", ucwords(ArrayUtils::get_field($elem, 'title')));
			$issub = ArrayUtils::get_field($elem, 'submitted');
			if(!isset($pds[$title])){
			    $pds[$title] = $issub;
			}
			else{
			    $pds[$title] = ($issub || $pds[$title]);
			}
        }
        foreach ($arr_a2 as &$elem) {
            $title = str_replace("'", "&#39;", ucwords(ArrayUtils::get_field($elem, 'title')));
			$issub = ArrayUtils::get_field($elem, 'submitted');
			if(!isset($pds[$title])){
			    $pds[$title] = $issub;
			}
			else{
			    $pds[$title] = ($issub || $pds[$title]);
			}
        }
        foreach ($arr_b as &$elem) {
            $title = str_replace("'", "&#39;", ucwords(ArrayUtils::get_field($elem, 'title')));
			$issub = ArrayUtils::get_field($elem, 'submitted');
			if(!isset($pds[$title])){
			    $pds[$title] = $issub;
			}
			else{
			    $pds[$title] = ($issub || $pds[$title]);
			}
        }
        foreach ($arr_c as &$elem) {
            $title = str_replace("'", "&#39;", ucwords(ArrayUtils::get_field($elem, 'title')));
			$issub = ArrayUtils::get_field($elem, 'submitted');
			if(!isset($pds[$title])){
			    $pds[$title] = $issub;
			}
			else{
			    $pds[$title] = ($issub || $pds[$title]);
			}
        }
    }

    $papers = PaperOld::getAllPapers();
    $nis = Person::getAllPeople(PNI);
    $crs = Person::getAllPeople(CNI);
    $people = array();
    foreach ($nis as $ni)
		$people[$ni->getId()] = $ni;
	foreach ($crs as $cr)
		$people[$cr->getId()] = $cr;
	// Load submitted and unsubmitted reports.
	$users = array_keys($people);
	$subm = ReportStorage::list_latest_reports($users, SUBM, 0, RPTP_NORMAL);
	$nsub = ReportStorage::list_latest_reports($users, NOTSUBM, 0, RPTP_NORMAL);

	// Reindex the reports.
	// FIXME: this should be refactored for easier use.
	$reports = array();
	foreach ($subm as $rep) {
		$id = $rep['user_id'];
		$repo = new ReportStorage($people[$id]);
		$reports[$id] = $repo->fetch_data($rep['token']);
	}
	foreach ($nsub as $rep) {
		$id = $rep['user_id'];
		if (! array_key_exists($id, $reports)) {
			$repo = new ReportStorage($people[$id]);
			$reports[$id] = $repo->fetch_data($rep['token']);
		}
	}
	// Fill in the blanks, if any.
	foreach ($users as $id) {
		if (! array_key_exists($id, $reports))
			$reports[$id] = array();
	}
	
	$paperProjects = array();
	foreach ($reports as $k => $rep) {
		$publi = ProjectProductivity::restructure($rep, '_IVq1pId');

		$prim = ArrayUtils::get_array($publi, 'prim');
		$sec = ArrayUtils::get_array($publi, 'sec');
		$ter = ArrayUtils::get_array($publi, 'ter');
		
		$na = ArrayUtils::get_array($publi, 'na');
		$possibles = array_keys($na, 'null', true);

		foreach ($possibles as $possible){
		    $v1 = ArrayUtils::get_string($prim, $possible);
			$v2 = ArrayUtils::get_string($sec, $possible);
			$v3 = ArrayUtils::get_string($ter, $possible);
			if(!isset($paperProjects[$possible])){
			    $paperProjects[$possible] = array();
			}
			if($v1 != "none"){
		        $paperProjects[$possible][] = $v1;
		    }
		    if($v2 != "none"){
		        $paperProjects[$possible][] = $v2;
		    }
		    if($v3 != "none"){
		        $paperProjects[$possible][] = $v3;
		    }
		    $paperProjects[$possible] = array_unique($paperProjects[$possible]);
		}
    }
	
    foreach($papers as $paper){
        echo "== {$paper->getTitle()} ==\n";
        $article = Article::newFromId($paper->getId());
        $text = $article->getRawText();
        $splitText = explode("\n|", $text);
        $newData = array();
        $i = 0;
        $date = "2010-00-00";
        $authorArray = array();
        $projectArray = array();
        foreach($splitText as $attr){
            if($i == 0){
                $i++;
                continue;
            }
            $splitAttr = explode("=", $attr);
            $name = str_replace(" ", "", $splitAttr[0]);
            $value = str_replace("}}", "", $splitAttr[1]);
            if($name == "year"){
                if(str_replace(" ", "", $value) != ""){
                    $date = trim($value."-00-00");
                }
            }
            else if($name == "authors"){
                $authors = $value;
                $authors = str_replace("[", "", $authors);
                $authors = str_replace("]", "", $authors);
                $splitAuthors = explode(",", $authors);
                foreach($splitAuthors as $author){
                    if(strstr($author, "|")){
                        $splitAuthor = explode(" | ", $author);
                        $author = $splitAuthor[1];
                    }
                    $authorArray[] = stripslashes(str_replace("'", "&#39", trim($author)));
                }
            }
            else{
                $newData[$name] = stripslashes(str_replace("<", "&lt;", str_replace(">", "&gt;", str_replace("'", "&#39;", trim($value)))));
            }
        }
        
        if(isset($paperProjects[$paper->getId()])){
            $projectArray = $paperProjects[$paper->getId()];
        }

        if(@$pds[str_replace("'", "&#39;", $paper->getTitle())] != 1){
            $status = "Published";
        }
        else{
            $status = "Submitted";
        }
        $sql = "INSERT INTO grand_publications (`id`,`category`,`projects`, `type`, `title`, `date`, `venue`,`status`,`authors`, `data`)
                VALUES ('{$paper->getId()}','Publication','".serialize($projectArray)."','{$paper->getType()}','".str_replace("'", "&#39;", $paper->getTitle())."','$date','','$status','".serialize($authorArray)."','".serialize($newData)."')";
        execSQLStatement($sql, true);
        echo "Upgraded!\n";
    }

	// Traverse all reports
	$artifacts = array();
	foreach ($reports as $k => $rep) {
	    $person = Person::newFromId($k);
		$artif = ProjectProductivity::restructure($rep, '_IVq2aId');
		
		// Use titles for counting, but avoid trusting them blindly.
		$titles = ArrayUtils::get_array($artif, 'title');
		
        $prim = ArrayUtils::get_array($artif, 'prim');
		$sec = ArrayUtils::get_array($artif, 'sec');
		$ter = ArrayUtils::get_array($artif, 'ter');
		
		foreach($titles as $key => $artifact){
		    if(!isset($artifacts[$artifact])){
			    $artifacts[$artifact] = array();
            }
		    if(!isset($artifacts[$artifact]['projects'])){
			    $artifacts[$artifact]['projects'] = array();
	        }
	        if(!isset($artifacts[$artifact]['authors'])){
			    $artifacts[$artifact]['authors'] = array();
	        }
			if($prim[$key] != "none"){
		        $artifacts[$artifact]['projects'][] = $prim[$key];
		    }
		    if($sec[$key] != "none"){
		        $artifacts[$artifact]['projects'][] = $sec[$key];
		    }
		    if($ter[$key] != "none"){
		        $artifacts[$artifact]['projects'][] = $ter[$key];
		    }
		    $artifacts[$artifact]['authors'][] = $person->getName();
	    }
	}
	foreach($artifacts as $key => $artifact){
	    echo "== $key ==\n";
	    $sql = "INSERT INTO grand_publications (`category`,`projects`, `type`, `title`, `date`, `authors`, `data`)
                VALUES ('Artifact','".serialize($artifact['projects'])."','Misc','".str_replace("'", "&#39;", $key)."','2010-00-00','".serialize($artifact['authors'])."','".serialize(array())."')";
        execSQLStatement($sql, true);
        echo "Upgraded!\n";
	}

    function execSQLStatement($sql, $update=false){
		if($update == false){
			$dbr = wfGetDB(DB_SLAVE);
		}
		else {
			$dbr = wfGetDB(DB_MASTER);
			return $dbr->query($sql);
		}
		$result = $dbr->query($sql);
		$rows = null;
		if($update == false){
			$rows = array();
			while ($row = $dbr->fetchRow($result)) {
				$rows[] = $row;
			}
		}
		return $rows;
	}

?>
