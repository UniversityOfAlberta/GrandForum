<?php

/**
 * @package GrandObjects
 */

class Paper extends BackboneModel{

    static $illegalAuthorsCache = array();
    static $oldSyncCache = array();
    static $cache = array();
    static $dataCache = array();
    static $productProjectsCache = array();

    var $id;
    var $category;
    var $description;
    var $title;
    var $type;
    var $projects = array();
    var $date;
    var $venue;
    var $status;
    var $authors;
    var $data;
    var $lastModified;
    var $authorsWaiting;
    var $projectsWaiting;
    var $deleted;
    var $access_id = 0;
    var $access = "Forum"; // Either 'Public' or 'Forum'
    var $created_by = 0;
    var $ccv_id;
    var $bibtex_id;
    var $reported = array();
    
    /**
     * Returns a new Paper from the given id
     * @param integer $id The id of the Paper
     * @return Paper The Paper with the given id
     */
    static function newFromId($id){
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        $me = Person::newFromWgUser();
        $sql = "SELECT *
                FROM grand_products
                WHERE id = '$id'
                AND (access_id = '{$me->getId()}' OR access_id = 0)
                AND (access = 'Public' OR (access = 'Forum' AND ".intVal($me->isLoggedIn())."))";
        $data = DBFunctions::execSQL($sql);
        $paper = new Paper($data);
        self::$cache[$paper->id] = &$paper;
        self::$cache[$paper->title] = &$paper;
        return $paper;
    }
    
    /**
     * Returns a new Paper from the given ccv_id
     * @param integer $ccv_id The id of the Paper
     * @return Paper The Paper with the given ccv_id
     */
    static function newFromCCVId($ccv_id){
        if(isset(self::$cache[$ccv_id])){
            return self::$cache[$ccv_id];
        }
        $me = Person::newFromWgUser();
        $sql = "SELECT *
                FROM grand_products
                WHERE ccv_id = '$ccv_id'
                AND (access_id = '{$me->getId()}' OR access_id = 0)
                AND (access = 'Public' OR (access = 'Forum' AND ".intVal($me->isLoggedIn())."))";
        $data = DBFunctions::execSQL($sql);
        $paper = new Paper($data);
        self::$cache[$paper->id] = &$paper;
        self::$cache[$paper->title] = &$paper;
        self::$cache[$paper->ccv_id] = &$paper;
        return $paper;
    }
    
    /**
     * Returns a new Paper from the given bibtex_id
     * @param integer $bibtex_id The id of the Paper
     * @param string $title The optional title for string comparison
     * @return Paper The Paper with the given bibtex_id
     */
    static function newFromBibTeXId($bibtex_id, $title=""){
        if(isset(self::$cache[$bibtex_id])){
            return self::$cache[$bibtex_id];
        }
        $me = Person::newFromWgUser();
        $bibtex_id = DBFunctions::escape($bibtex_id);
        $sql = "SELECT *
                FROM grand_products
                WHERE bibtex_id = '$bibtex_id'
                AND (access_id = '{$me->getId()}' OR access_id = 0)
                AND (access = 'Public' OR (access = 'Forum' AND ".intVal($me->isLoggedIn())."))";
        $data = DBFunctions::execSQL($sql);
        if($title != ""){
            $newData = array();
            // Check the title.  It should be at least 80% similar
            foreach($data as $row){
                $percent = 0;
                similar_text(unaccentChars($title), unaccentChars($row['title']), $percent);
                if($percent >= 80){
                    $newData[] = $row;
                    break;
                }
            }
            $data = $newData;
        }
        $paper = new Paper($data);
        self::$cache[$paper->id] = &$paper;
        self::$cache[$paper->title] = &$paper;
        self::$cache[$paper->bibtex_id] = &$paper;
        return $paper;
    }
    
    /**
     * Returns an array of new Papers from the given array of ids
     * @param array $ids The array of ids
     * @param boolean $onlyPublic Whether or not to only include Papers with access_id = 0
     * @return array The array of Papers
     */
    static function newFromIds($ids, $onlyPublic=true){
        if(count($ids) == 0){
            return array();
        }
        $me = Person::newFromWgUser();
        $ids = array_clean($ids);
        $sql = "SELECT *
                FROM grand_products
                WHERE id IN (".implode(",", $ids).")
                AND (access = 'Public' OR (access = 'Forum' AND ".intVal($me->isLoggedIn())."))";
        if(!$onlyPublic){
            $me = Person::newFromWgUser();
            $sql .= "\nAND (access_id = '{$me->getId()}' OR access_id = '0')";
        }
        else{
            $sql .= "\nAND access_id = '0'";
        }
        $data = DBFunctions::execSQL($sql);
        $papers = array();
        foreach($data as $row){
            $paper = new Paper(array($row));
            if(isset(self::$cache[$paper->id])){
                $papers[] = self::$cache[$paper->id];
            }
            else {
                self::$cache[$paper->id] = &$paper;
                self::$cache[$paper->title] = &$paper;
                $papers[] = $paper;
            }
        }
        return $papers;
    }
    
    // Returns a new Paper from the given id
    static function newFromTitle($title, $category = "%", $type = "%", $status = "%"){
        $title = str_replace("&#58;", ":", $title);
        $title = str_replace("'", "&#39;", $title);
        $category = DBFunctions::escape($category);
        $type = DBFunctions::escape($type);
        $status = DBFunctions::escape($status);
        if(isset(self::$cache[$title.$category.$type.$status])){
            return self::$cache[$title.$category.$type.$status];
        }
        $me = Person::newFromWgUser();
        $sql = "SELECT *
                FROM grand_products
                WHERE (`title` = '$title' OR
                       `title` = '".str_replace(" ", "_", $title)."')
                AND `category` LIKE '$category'
                AND `type` LIKE '$type'
                AND `status` LIKE '$status'
                AND (access_id = '0' OR access_id = '{$me->getId()}')
                AND (access = 'Public' OR (access = 'Forum' AND ".intVal($me->isLoggedIn())."))
                ORDER BY `id` desc";
        $data = DBFunctions::execSQL($sql);
        $paper = new Paper($data);
        self::$cache[$paper->id] = &$paper;
        self::$cache[$paper->getTitle().$category.$type.$status] = &$paper;
        self::$cache[$paper->getTitle().$paper->getCategory().$paper->getType().$paper->getStatus()] = &$paper;
        return $paper;
    }
    
    /**
     * Returns all the Products with the given ids
     * @param array $ids The array of ids
     * @return array The array of Products
     */
    static function getByIds($ids){
        if(count($ids) == 0){
            return array();
        }
        $papers = array();
        foreach($ids as $key => $id){
            if(isset(self::$cache[$id])){
                $paper = self::$cache[$id];
                $papers[$paper->getId()] = $paper;
                unset($ids[$key]);
            }
        }
        if(count($ids) > 0){
            $me = Person::newFromWgUser();
            $sql = "SELECT *
                    FROM grand_products
                    WHERE id IN (".implode(",", $ids).")
                    AND access_id = 0
                    AND (access = 'Public' OR (access = 'Forum' AND ".intVal($me->isLoggedIn())."))";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $paper = new Paper(array($row));
                self::$cache[$paper->getId()] = $paper;
                $papers[$paper->getId()] = $paper;
            }
        }
        return $papers;
    }
    
    /**
     * Returns the number of Products there are of the specified type
     * @param string $category The category of Product.  If 'all' then look at all products
     * @return integer The number of Products there are of the specified type
     */
    static function countByCategory($category='all'){
        if($category != 'all'){
            $data = DBFunctions::select(array('grand_products'),
                                        array('COUNT(id)' => 'count'),
                                        array('category' => EQ($category),
                                              'deleted' => EQ(0)));
        }
        else{
            $data = DBFunctions::select(array('grand_products'),
                                        array('COUNT(id)' => 'count'),
                                        array('deleted' => EQ(0)));
        }
        return $data[0]['count'];
    }
    
    /**
     * Returns all of the Products in the database
     * @param Project $project Specifies which project the returned Products should be associated with
     * @param string $category Specifies which category the returned Products should be of('Publication', 'Artifact' etc.)
     * @param string $grand Whether to include grand-only, non-grand-only or both
     * @param boolean $onlyPublic Whether or not to only include Products with access_id = 0
     * @param string $access Whether to include 'Forum' or 'Public' access
     * @param integer $start The index to start at
     * @param integer $count The max number of Products to return 
     * @return array All of the Products
     */
    static function getAllPapers($project='all', $category='all', $grand='grand', $onlyPublic=true, $access='Public', $start=0, $count=9999999999){
        global $config;
        if(!$config->getValue('projectsEnabled')){
            $grand = 'both';
        }
        $data = array();
        if(isset(self::$dataCache[$project.$category.$grand.strval($onlyPublic).$access.$start.$count])){
            return self::$dataCache[$project.$category.$grand.strval($onlyPublic).$access.$start.$count];
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
                if(!$p->clear){
                    $preds = $p->getPreds();
                    foreach($preds as $pred){
                        foreach(Paper::getAllPapers($pred->getName(), $category, $grand) as $paper){
                            $papers[$paper->getId()] = $paper;
                        }
                    }
                }
            }
            if($project instanceof Project){
                $project = $project->getName();
            }
            $me = Person::newFromWgUser();
            $sql = "SELECT *
                    FROM `grand_products` p";
            if($project != "all"){
                $p = Project::newFromName($project);
                $sql .= ", `grand_product_projects` pp
                         WHERE pp.`project_id` = '{$p->getId()}'
                         AND pp.`product_id` = p.`id`";
            }
            else {
                $sql .= "\nWHERE 1";
            }
            $sql .= "\nAND (access = '{$access}' OR (access = 'Forum' AND ".intVal($me->isLoggedIn())."))";
            $sql .= "\nAND p.`deleted` = '0'";
            if($category != "all"){
                $sql .= "\nAND p.`category` = '$category'";
            }
            if(!$onlyPublic){
                $sql .= "\nAND (access_id = '{$me->getId()}' OR access_id = '0')";
            }
            else{
                $sql .= "\nAND access_id = '0'";
            }
            $sql .= "\nORDER BY p.`type`, p.`title`";
            $data = DBFunctions::execSQL($sql);
            self::generateProductProjectsCache();
            $i = 0;
            foreach($data as $row){
                $hasProjects = (isset(self::$productProjectsCache[$row['id']]) && count(self::$productProjectsCache[$row['id']]) > 0);
                if($project != "all" || 
                   (($grand == 'grand' && $hasProjects) ||
                    ($grand == 'nonGrand' && !$hasProjects) ||
                     $grand == 'both')){
                    if($i >= $start && $i < $start + $count){
                        if(!isset(self::$cache[$row['id']])){
                            $paper = new Paper(array($row));
                            self::$cache[$paper->id] = $paper;
                        }
                        else{
                            $paper = self::$cache[$row['id']];
                        }
                        $papers[] = $paper;
                    }
                    $i++;
                }
            }
            self::$dataCache[$project.$category.$grand.strval($onlyPublic).$access.$start.$count] = $papers;
        }
        return $papers;
    }
    
    /**
     * Returns all of the Papers in the database
     * @param Project $project Specifies which project the returned papers should be associated with
     * @param string $category Specifies which category the returned papers should be of('Publication', 'Artifact' etc.)
     * @param string $grand Whether to include grand-only, non-grand-only or both
     * @param string $startRange The start date
     * @param string $endRange The end date
     * @param boolean $strict whether to stick with the date range for everything(true), or show anything 'to appear' as well (false)
     * @param boolean $onlyPublic Whether or not to only include Papers with access_id = 0
     * @return array All of the Papers
     */
    static function getAllPapersDuring($project='all', $category='all', $grand='grand', $startRange = false, $endRange = false, $strict = true, $onlyPublic = true){
        global $config;
        if(!$config->getValue('projectsEnabled')){
            $grand = 'both';
        }
        if($startRange === false || $endRange === false){
            debug("Don't use default values for Project::getAllPapersDuring");
            $startRange = date(YEAR."-01-01 00:00:00");
            $endRange = date(YEAR."-12-31 23:59:59");
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
                if(!$p->clear){
                    $preds = $p->getPreds();
                    foreach($preds as $pred){
                        foreach(Paper::getAllPapersDuring($pred, $category, $grand, $startRange, $endRange) as $paper){
                            $papers[$paper->getId()] = $paper;
                        }
                    }
                }
            }
            if($project instanceof Project){
                $project = $project->getName();
            }
            $data = array();
            $me = Person::newFromWgUser();
            $sql = "SELECT *
                    FROM `grand_products` p";
            if($project != "all"){
                $p = Project::newFromName($project);
                $sql .= ", `grand_product_projects` pp
                         WHERE pp.`project_id` = '{$p->getId()}'
                         AND pp.`product_id` = p.`id`";
            }
            else {
                $sql .= "\nWHERE 1";
            }
            $sql .= "\nAND (access = 'Public' OR (access = 'Forum' AND ".intVal($me->isLoggedIn())."))";
            $sql .= "\nAND p.`deleted` = '0'";
            if($category != "all"){
                $sql .= "\nAND p.`category` = '$category'";
            }
            if($strict){
                $sql .= "\nAND p.`date` BETWEEN '$startRange' AND '$endRange'";
            }
            else{
                $sql .= "\nAND (p.`date` BETWEEN '$startRange' AND '$endRange' OR (p.`date` >= '$startRange' AND p.`category` = 'Publication' AND p.`status` != 'Published' AND p.`status` != 'Submitted' ))";
            }
            if($category != "all"){
                $sql .= "\nAND p.`category` = '$category'";
            }
            if(!$onlyPublic){
                $sql .= "\nAND (access_id = '{$me->getId()}' OR access_id = '0')";
            }
            else{
                $sql .= "\nAND access_id = '0'";
            }
            $sql .= "\nORDER BY p.`type`, p.`title`";
            
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                if(!isset(self::$cache[$row['id']])){
                    $paper = new Paper(array($row));
                    self::$cache[$paper->id] = $paper;
                }
                else{
                    $paper = self::$cache[$row['id']];
                }
                if($project != "all"){
                    $papers[] = $paper;
                }
                else if(($grand == 'grand' && $paper->isGrandRelated()) ||
                        ($grand == 'nonGrand' && !$paper->isGrandRelated()) ||
                         $grand == 'both'){
                    $papers[] = $paper;
                }
            }
            self::$dataCache[$proj.$category.$grand.$startRange.$endRange.$str] = $papers;
            return $papers;
        }
    }
    
    static function generateIllegalAuthorsCache(){
        if(count(self::$illegalAuthorsCache) == 0){
            $data = DBFunctions::select(array('grand_illegal_authors'),
                                        array('author'));
            self::$illegalAuthorsCache[""] = "";
            foreach($data as $row){
                self::$illegalAuthorsCache[$row['author']] = $row['author'];
            }
        }
    }
    
    static function generateProductProjectsCache(){
        if(count(self::$productProjectsCache) == 0){
            $data = DBFunctions::select(array('grand_product_projects'),
                                        array('product_id', 'project_id'));
            foreach($data as $row){
                self::$productProjectsCache[$row['product_id']][] = $row['project_id'];
            }
        }
    }
    
    /**
     * Returns a php version of the Products.xml structure
     * @return array The array containing all the structure in Products.xml
     */
    static function structure(){
        global $config, $IP;
        $fileName = "$IP/extensions/GrandObjects/ProductStructures/{$config->getValue('networkName')}.xml";
        if(!file_exists($fileName)){
            $fileName = "$IP/extensions/GrandObjects/ProductStructures/NETWORK.xml";
        }
        $fileTime = filemtime($fileName);
        if(!Cache::exists("product_structure")){
            $file = file_get_contents($fileName);
            $parser = simplexml_load_string($file);
            $categories = array('categories' => array(),
                                'time' => $fileTime);
            foreach($parser->children() as $category){
                $cattrs = $category->attributes();
                $cname = "{$cattrs->category}";
                foreach($category->children() as $type){
                    $tattrs = $type->attributes();
                    $tname = "{$tattrs->type}";
                    $tname = str_replace('{$networkName}', $config->getValue('networkName'), $tname);
                    $ccvType = "{$tattrs->ccv_name}";
                    $ccvType = ($ccvType == "") ? $tname : $ccvType;
                    if(trim("{$tattrs->status}") != ""){
                        $tstatus = explode("|", "{$tattrs->status}");
                    }
                    else{
                        $tstatus = array();
                    }
                    $categories['categories'][$cname]['types'][$tname] = array('data' => array(),
                                                                               'status' => $tstatus,
                                                                               'type' => $ccvType,
                                                                               'ccv_status' => array());
                    foreach($type->children() as $child){
                        if($child->getName() == "data"){
                            foreach($child->children() as $field){
                                $fattrs = $field->attributes();
                                $fid = "$field";
                                $flabel = "{$fattrs->label}";
                                $ftype = str_replace('{$networkName}', $config->getValue('networkName'), "{$fattrs->type}");
                                $fccvtk = "{$fattrs->ccvtk}";
                                $fbibtex = "{$fattrs->bibtex}";
                                $fhidden = (strtolower("{$fattrs->hidden}") == "true");
                                $foptions = explode("|", "{$fattrs->options}");
                                
                                $categories['categories'][$cname]['types'][$tname]['data'][$fid] = array('ccvtk' => $fccvtk,
                                                                                                         'bibtex' => $fbibtex,
                                                                                                         'label' => $flabel,
                                                                                                         'type' => $ftype,
                                                                                                         'options' => $foptions,
                                                                                                         'hidden' => $fhidden);
                            }
                        }
                        else if($child->getName() == "statuses"){
                            foreach($child->children() as $status){
                                $sattrs = $status->attributes();
                                $sid = "{$sattrs->lov_id}";
                                $sname = "$status";
                                if($sid != ""){
                                    $categories['categories'][$cname]['types'][$tname]['ccv_status'][$sid] = $sname;
                                }
                            }
                        }
                    }
                    if(DBFunctions::isReady()){
                        $misc_types = Paper::getAllMiscTypes($cname);
                        foreach($misc_types as $key => $type){
                            $misc_types[$key] = str_replace("\"", "\\\"", $type);
                        }
                        $categories['categories'][$cname]['misc'] = $misc_types;
                    }
                }
            }
            Cache::store("product_structure", $categories);
        }
        else{
            $categories = Cache::fetch("product_structure");
            if(!isset($categories['time']) || $categories['time'] < $fileTime){
                Cache::delete("product_structure");
                return self::structure();
            } 
        }
        return $categories;
    }
    
    // Constructor
    function __construct($data){
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
            $this->access_id = $data[0]['access_id'];
            $this->created_by = $data[0]['created_by'];
            $this->access = $data[0]['access'];
            $this->ccv_id = $data[0]['ccv_id'];
            $this->bibtex_id = $data[0]['bibtex_id'];
            $this->projects = array();
            $this->projectsWaiting = true;
            $this->authors = $data[0]['authors'];
            $this->authorsWaiting = true;
            $this->data = unserialize($data[0]['data']);
            $this->lastModified = $data[0]['date_changed'];
        }
    }
    
    /**
     * Returns the id of this Paper
     * @return integer The id of this Paper
     */
    function getId(){
        return $this->id;
    }
    
    /**
     * Returns the ccv id of this Paper
     * @return string The ccv id of this Paper
     */
    function getCCVId(){
        return $this->ccv_id;
    }
    
    /**
     * Returns the bibtex id of this Paper
     * @return string The bibtex id of this Paper
     */
    function getBibTexId(){
        return $this->bibtex_id;
    }
    
    /**
     * Returns the category of this Paper
     * @return string The category of this Paper
     */
    function getCategory(){
        return $this->category;
    }
    
    /**
     * Returns the abstract or description of this Paper
     * @return string The abstract or description of this Paper
     */
    function getDescription(){
        return $this->description;
    }

    /**
     * Returns the title of this Paper
     * @return string The title of this Paper
     */
    function getTitle(){
        return $this->title;
    }

    /**
     * Returns the status of this Paper
     * @return string The status of this Paper
     */
    function getStatus(){
        return $this->status;
    }
    
    /**
     * Returns the id of the Person who has access to this Paper (0 if everyone)
     * @return integer The id of the Person who has access to this Paper
     */
    function getAccessId(){
        return $this->access_id;
    }
    
    /**
     * Returns the id of the Person who created this Paper
     * @return integer The id of the Person who created this Paper
     */
    function getCreatedBy(){
        return $this->created_by;
    }
    
    /**
     * Returns the access level of this Paper (either 'Public' or 'Forum')
     * @return string The access level of this Paper
     */
    function getAccess(){
        return $this->access;
    }
    
    /**
     * Returns whether or not this Paper is published or not
     * @return boolean Whether or not this Paper is published or not
     */
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
    
    /**
     * Returns the url of this Paper's page
     * @return string The url of this Paper's page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath;
        if(!isset($_GET['embed']) || $_GET['embed'] == 'false'){
            return "{$wgServer}{$wgScriptPath}/index.php/Special:Products#/{$this->getCategory()}/{$this->getId()}";
        }
        return "{$wgServer}{$wgScriptPath}/index.php/Special:Products?embed#/{$this->getCategory()}/{$this->getId()}";
    }
    
    /**
     * Returns an array of all the unique authors in the DB
     * @return array All the unique authors in the DB
     */
    static function getAllAuthors(){
        $data = DBFunctions::select(array("grand_product_authors"),
                                    array("author"));
        $authors = array();
        foreach($data as $row){
            $name = trim($row['author']);
            if(!is_numeric($name)){
                $authors[$name] = $name;
            }
        }
        return $authors;
    }
    
    /**
     * Returns an array of authors who wrote this Paper
     * @param boolean $evaluate Whether or not to ignore the cache
     * @param boolean $cache Whether or not to cache the authors
     * @return array The authors who wrote this Paper
     */
    function getAuthors($evaluate=true, $cache=true){
        if($this->authorsWaiting && $evaluate){
            $authors = array();
            $unserialized = array();
            if(is_array($this->authors)){
                // For creation/update of Product
                foreach($this->authors as $auth){
                    if(isset($auth->id)){
                        $unserialized[] = $auth->id;
                    }
                    else if(isset($auth->fullname)){
                        $unserialized[] = $auth->fullname;
                    }
                    else{
                        $unserialized[] = $auth->name;
                    }
                }
            }
            else{
                $unserialized = unserialize($this->authors);
            }
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
                self::generateIllegalAuthorsCache();
                if($person == null || 
                   $person->getName() == null || 
                   $person->getName() == "" || 
                   isset(self::$illegalAuthorsCache[$person->getNameForForms()]) ||
                   isset(self::$illegalAuthorsCache[$person->getId()])){
                    // Ok this person is not in the db, make a fake Person object
                    $pdata = array();
                    $pdata[0]['user_id'] = "";
                    $pdata[0]['user_name'] = $author;
                    $pdata[0]['user_real_name'] = $author;
                    $pdata[0]['first_name'] = "";
                    $pdata[0]['middle_name'] = "";
                    $pdata[0]['last_name'] = "";
                    $pdata[0]['prev_first_name'] = "";
                    $pdata[0]['prev_last_name'] = "";
                    $pdata[0]['honorific'] = "";
                    $pdata[0]['language'] = "";
                    $pdata[0]['user_email'] = "";
                    $pdata[0]['user_gender'] = "";
                    $pdata[0]['user_twitter'] = "";
                    $pdata[0]['user_website'] = "";
                    $pdata[0]['user_nationality'] = "";
                    $pdata[0]['user_registration'] = "";
                    $pdata[0]['user_public_profile'] = "";
                    $pdata[0]['user_private_profile'] = "";
                    $pdata[0]['candidate'] = 0;
                    $person = new Person($pdata);
                    if($cache){
                        Person::$cache[$author] = $person;
                    }
                }
                if($person->getName() == "WikiSysop"){
                    // Under no circumstances should WikiSysop be an author
                    continue;
                }
                $authors[] = $person;
            }
            //return $authors;
            $this->authorsWaiting = false;
            $this->authors = $authors;
        }
        if(!is_array($this->authors)){
            return array();
        }
        return $this->authors;
    }
    
    function getAuthorNames(){
        $authors = array();
        $unserialized = unserialize($this->authors);
        foreach($unserialized as &$author){
            if($author == ""){
                unset($author);
                continue;
            }
            if(is_numeric($author)){
                $person = Person::newFromId($author);
                if($person == null) { continue; }
                $authors[] = (strlen($person->getRealName()) > 0) ? $person->getRealName() : $person->getName();
                continue;
            } 
            else {
                $authors[] = $author;
            }
        }
        unset($author);
        return $authors;
    }
    
    /**
     * Generates a cache so that when a sync is being done 
     * it knows what the previous state was
     */
    function generateOldSyncCache(){
        if(count(self::$oldSyncCache) == 0){
            $sql = "SELECT *
                    FROM `grand_product_authors`";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                self::$oldSyncCache[$row['product_id']][] = $row;
            }
        }
    }
    
    /**
     * Synchronizes the `grand_products` table and the `grand_product_authors` table for this Paper
     * @param boolean $massSync Whether or not to run this for a massSynchronization, or just for this Paper
     * @return array If $massSync=true, returns the sql statements required to update the DB
     */
    function syncAuthors($massSync=false){
        self::generateOldSyncCache();
        $data = array();
        if(isset(self::$oldSyncCache[$this->id])){
            $data = self::$oldSyncCache[$this->id];
        }
        $deleteSQL = "DELETE FROM `grand_product_authors`
                      WHERE `product_id` = '{$this->id}'";
        $order = 0;
        $insertSQL = "INSERT INTO `grand_product_authors`
                      (`author`, `product_id`, `order`) VALUES\n";
        $authors = $this->getAuthors();
        if(!is_array($authors)){
            $authors = array();
        }
        $inserts = array();
        $alreadyDone = array();
        $invalidate = false;
        $keyOffset = 0;
        foreach($authors as $key => $author){
            if(isset($alreadyDone[$author->getName()])){
                $keyOffset++;
                continue;
            }
            if(isset($data[$key-$keyOffset])){
                $pastAuthor = $data[$key-$keyOffset];
            }
            else{
                $pastAuthor = array();
                $invalidate = true;
            }
            $alreadyDone[$author->getName()] = true;
            if($author->getId() != ""){
                if(@$pastAuthor['author'] != $author->getId()){
                    // Author has changed
                    $invalidate = true;
                }
                $inserts[] = "('{$author->getId()}','{$this->getId()}','{$order}')";
            }
            else{
                if(@$pastAuthor['author'] != substr($author->getName(), 0, 128)){
                    // Author has changed
                    $invalidate = true;
                }
                $name = DBFunctions::escape($author->getName());
                $inserts[] = "('{$name}','{$this->getId()}','{$order}')";
            }
            $order++;
        }
        if($invalidate){
            // The Author data has changed, so invalidate the cache
            Cache::delete($this->getCacheId());
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
    
    /**
     * Returns whether or not this Paper belongs to the specified Project
     * @param Project $project The project to check
     * @return boolean Whether or not this Paper belongs to the specifed Project
     */
    function belongsToProject($project){
        if($project == null){
            return false;
        }
        foreach($this->getProjects() as $p){
            if($p != null && $p->getId() == $project->getId()){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns an array or Projects which this Paper is related to
     * @return array The Projects which this Paper is related to
     */
    function getProjects(){
        if($this->projectsWaiting){
            self::generateProductProjectsCache();
            if(isset(self::$productProjectsCache[$this->id])){
                $data = self::$productProjectsCache[$this->id];
                if(is_array($data)){
                    foreach($data as $projectId){
                        $project = Project::newFromId($projectId);
                        if($project instanceof Project){
                            $this->projects[] = $project;
                        }
                    }
                }
            }
            $this->projectsWaiting = false;
        }
        return $this->projects;
    }
    
    // Returns an array of Projects which this Paper is related to
    function getProjectNames(){
        $projs = array();
        if($this->projects != null){
            foreach($this->projects as $key => $project){
                if($project == null){
                    unset($this->projects[$key]);
                } else {
                    $projs[] = $project->name;
                }
            }
        }
        return $projs;
    }
    
    /**
     * Returns the Universities which are associated with this Paper
     * @return array The Universities which are associated with this Paper
     */
    function getUniversities(){
        $people = $this->getAuthors();
        $unis = array();
        foreach($people as $person){
            if($person->getId() == 0){
                continue;
            }
            $universities = $person->getUniversitiesDuring($this->getDate(), $this->getDate());
            if(count($universities) > 0){
                foreach($universities as $university){
                    if(isset($university['university']) && $university['university'] != "Unknown"){
                        $unis[$university['university']] = $university['university'];
                    }
                }
            }
            else{
                // Get current university if the person wasn't at a university at that time
                $university = $person->getUniversity();
                if(isset($university['university']) && $university['university'] != "Unknown"){
                    $unis[$university['university']] = $university['university'];
                }
            }
        }
        return array_values($unis);
    }
    
    /**
     * Returns whether or not this Paper is related to this network (has projects)
     * @return boolean Whether or not this Paper is related to this network
     */
    function isGrandRelated(){
        return (count($this->getProjects()) > 0);
    }

    /**
     * Returns the date of this Paper
     * @return string The date of this Paper
     */
    function getDate(){
        global $config;
        $dates = $config->getValue('projectPhaseDates');
        $date = $this->date;
        $date = str_replace("0000", substr($dates[1], 0, 4), $date);
        $date = str_replace("-00", "-01", $date);
        return $date;
    }
    
    /**
     * Returns the year of this Paper
     * @return string The year of this Paper
     */
    function getYear(){
        return substr($this->getDate(), 0, 4);
    }
    
    /**
     * Returns the type of this Paper
     * @return strig The type of this Paper
     */
    function getType(){
        return $this->type;
    }
    
    /**
     * Returns the 'CCV' type of this Paper
     * @return string The 'CCV' type of this Paper
     */
    function getCCVType(){
        $structure = $this->structure();
        if(isset($structure['categories'][$this->getCategory()]['types'][$this->getType()])){
            return $structure['categories'][$this->getCategory()]['types'][$this->getType()]['type'];
        }
        return $this->getType();
    }
    
    /**
     * Returns the venue for this Paper (legacy stuff)
     * @return string The venue for this Paper
     */
    function getVenue(){
        $venue = $this->venue;
        if( empty($venue) ){
            $venue = ArrayUtil::get_string($this->data, 'venue');
        }
        
        if( empty($venue) ){
            $venue = ArrayUtil::get_string($this->data, 'event_title');
        }

        if( empty($venue) ){
            $venue = ArrayUtil::get_string($this->data, 'conference');
        }

        if( empty($venue) ){
            $venue = ArrayUtil::get_string($this->data, 'event_location');
        }

        if(empty($venue)){
            $venue = ArrayUtil::get_string($this->data, 'location');
        }
        return $venue;
    }

    /**
     * Returns the domain specific data for this Paper
     * @return array The domain specific data for this Paper
     */
    function getData(){
        return $this->data;
    }
    
    /**
     * Returns whether or not this Paper is deleted
     * @return boolean Whether or not this Paper is deleted
     */
    function isDeleted(){
        return ($this->deleted === "1");
    }

    /**
     * Returns whether or not this Paper has been reported in the given year
     * @param string $year The reporting year to check
     * @param string $reportedType The type of reporting to check (must be either 'RMC' or 'NCE')
     * @return boolean Whether or not this Paper has been reported in the given year
     */
    function hasBeenReported($year, $reportedType){
        if(($reportedType == 'RMC' || $reportedType == 'NCE')){
            if(!isset($this->reported[$reportedType])){
                $this->getReportedYears();
            }
            $years = $this->reported[$reportedType];
            if(isset($years[$year])){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns the years that this Paper was reported
     * @param string $reportedType The type of reporting to check (must be either 'RMC' or 'NCE')
     * @return array The years that this Paper was reported
     */
    function getReportedYears($reportedType){
        if(!isset($this->reported[$reportedType])){
            $this->reported['RMC'] = array();
            $this->reported['NCE'] = array();
            $sql = "SELECT DISTINCT `year`, `reported_type`
                    FROM `grand_products_reported`
                    WHERE `product_id` = '{$this->id}'";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $this->reported[$row['reported_type']][] = $row['year'];
            }
        }
        return $this->reported[$reportedType];
    }
    
    /**
     * Return a string with a citation-like format
     * @param boolean $showStatus Whether or not to show the publication status
     * @param boolean $showPeerReviewed Whether or not to show the peer reviewed status
     * @param boolean $hyperlink Whether or not to use hyperlinks in the citation
     * @return string The citation text
     */
    function getProperCitation($showStatus=true, $showPeerReviewed=true, $hyperlink=true){
        global $wgServer, $wgScriptPath;

        $data = $this->getData();
        $type = $this->getType();
        $title = $this->getTitle();
        $status = ($showStatus) ? $this->getStatus() : "";
        $category = $this->getCategory();
        $au = array();
        foreach($this->getAuthors() as $a){
            if($a->getId()){
                if($hyperlink){
                    $name = $a->getNameForProduct();
                    if($a->isRoleOn(HQP, $this->getDate()) || $a->wasLastRole(HQP)){
                        $name = "<u>{$a->getNameForProduct()}</u>";
                    }
                    else if((!$a->isRoleOn(HQP, $this->getDate()) && !$a->wasLastRole(HQP)) &&
                            (!$a->isRoleOn(NI, $this->getDate()) && !$a->wasLastRole(NI))){
                        $name = "<i>{$a->getNameForProduct()}</i>";
                    }
                    $au[] = "<a target='_blank' href='{$a->getUrl()}'><b>{$name}</b></a>";
                }
                else{
                    $au[] = "<b>". $a->getNameForProduct() ."</b>";
                }
            }else{
                $au[] = $a->getNameForProduct();
            }
        }
        $au = implode(',&nbsp;', $au);
        $vn = $this->getVenue();

        if(($type == "Proceedings Paper" || $category == "Presentation") && empty($vn)){
            $vn = "(no venue)";
        }

        //This is not really a venue, but this is how we want to put this into the proper citation
        if(($type == "Journal Paper" || $type == "Journal Abstract")){
            $vn = ArrayUtil::get_string($data, 'journal_title');
            if(empty($vn)){
                $vn = ArrayUtil::get_string($data, 'published_in');
            }
            $volume = ArrayUtil::get_string($data, 'volume');
            $number = ArrayUtil::get_string($data, 'number');
            if(!empty($volume)){
                $vn .= " $volume";
            }
            if(!empty($number)){
                $vn .= "($number)";
            }
        }
        else {
            if($vn == "") $vn .= ArrayUtil::get_string($data, 'event_title');
            if($vn == "") $vn .= ArrayUtil::get_string($data, 'journal_title');
            if($vn == "") $vn .= ArrayUtil::get_string($data, 'book_title');
            if($vn == "") $vn .= ArrayUtil::get_string($data, 'owner');
            if($vn == "") $vn .= ArrayUtil::get_string($data, 'assignor');
        }

        $pg = ArrayUtil::get_string($data, 'pages');
        if (strlen($pg) > 0){
            $pg = "{$pg}pp.";
        }
        else{
            $pg = "";
        }
        $pb = ArrayUtil::get_string($data, 'publisher', '');

        $peer_rev = "";
        if($showPeerReviewed && $category == "Publication"){
            if(isset($data['peer_reviewed']) && $data['peer_reviewed'] == "Yes"){
                $peer_rev = "&nbsp;/&nbsp;Peer Reviewed";
            }
            else if(isset($data['peer_reviewed']) && $data['peer_reviewed'] == "No"){
                $peer_rev = "&nbsp;/&nbsp;Not Peer Reviewed";
            }
        }

        if($hyperlink){
            $text = "<a href='{$this->getUrl()}'>{$title}</a>";
        }
        else{
            $text = $title;
        }
        $date = date("Y M", strtotime($this->getDate()));
        $type = str_replace("Misc: ", "", $type);
        if($vn != "" && ($pg != "" || $pb != "") && ($status != "" || $peer_rev != "")){
            $vn = "$vn,";
        }
        if($vn != ""){
            $vn = "&nbsp;{$vn}";
        }
        if($pg != ""){
            $pg = "&nbsp;{$pg}";
        }
        if($pb != ""){
            $pb = "&nbsp;{$pb}";
        }
        $citation = "{$au}&nbsp;({$date}).&nbsp;<i>{$text}.</i>{$vn}{$pg}{$pb}
       		         <div class='pdfnodisplay' style='width:85%;margin-left:15%;text-align:right;'>{$status}{$peer_rev}</div>";
        return trim($citation);
    }


    /**
     * Checks appropriate type of paper for requred venue, pages and publisher fields. If paper falls under category that
     * requires these fields, it checks them for completeness, otherwise returns them as complete.
     * @return array An associative array describing the completeness of this Paper
     */
    function getCompleteness(){
        $noVenue = $noPublisher = $noPages = false;
        $completeness = array("venue"=>true, 'pages'=>true, 'publisher'=>true);

        $data = $this->getData();
        $vn = $this->getVenue();
        if($this->getType() == "Proceedings Paper" && $vn == ""){
            $completeness['venue'] = false;
        }
        
        if(in_array($this->getType(), array('Book', 'Collections Paper', 'Proceedings Paper', 'Journal Paper'))){
            $pg = ArrayUtil::get_string($data, 'pages');
            if (!(strlen($pg) > 0)){
                $completeness['pages'] = false;
            }
            $pb = ArrayUtil::get_string($data, 'publisher', '');
            if($pb == ''){
                $completeness['publisher'] = false;
            }
        }

        return $completeness;
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
        if(!Cache::exists("{$category}_misc_types")){
            $sql = "SELECT DISTINCT SUBSTR(type, 7) as type
                    FROM grand_products
                    WHERE SUBSTR(type, 1, 6) = 'Misc: ' AND
                    category LIKE '$category'";
            $data = DBFunctions::execSQL($sql);
            $return = array();
            foreach($data as $row){
                $return[] = $row['type'];
            }
            Cache::store("{$category}_misc_types", $return);
        }
        else{
            $return = Cache::fetch("{$category}_misc_types");
        }
        return $return;
    }

    function create(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn() && trim($this->title) != ""){
            // Begin Transaction
            DBFunctions::begin();
            $authors = array();
            foreach($this->authors as $author){
                if(isset($author->id) && $author->id != 0){
                    $authors[] = $author->id;
                }
                else if(isset($author->fullname)){
                    $authors[] = $author->fullname;
                }
                else{
                    $authors[] = $author->name;
                }
            }
            foreach($this->projects as $project){
                if(!isset($project->id) || $project->id == 0){
                    $p = Project::newFromName($project->name);
                    $project->id = $p->getId();
                }
            }
            // Update products table
            $created_by = ($this->created_by == 0) ? $me->getId() : $this->created_by;
            $status = DBFunctions::insert('grand_products',
                                          array('category' => $this->category,
                                                'description' => $this->description,
                                                'type' => $this->type,
                                                'title' => $this->title,
                                                'date' => $this->date,
                                                'venue' => $this->venue,
                                                'status' => $this->status,
                                                'authors' => serialize($authors),
                                                'data' => serialize($this->data),
                                                'access_id' => $this->access_id,
                                                'created_by' => $created_by,
                                                'access' => $this->access,
                                                'ccv_id' => $this->ccv_id,
                                                'bibtex_id' => $this->bibtex_id,
                                                'date_created' => EQ(COL('CURRENT_TIMESTAMP'))),
                                          true);
            // Get the Product Id
            if($status){
                $data = DBFunctions::select(array('grand_products'),
                                            array('id'),
                                            array('title' => EQ($this->title),
                                                  'category' => EQ($this->category),
                                                  'type' => EQ($this->type)),
                                            array('id' => 'DESC'));
                if(count($data) > 0){
                    $id = $data[0]['id'];
                    $this->id = $id;
                }
            }
            // Update product_projects table
            if($status){
                $status = DBFunctions::delete("grand_product_projects", 
                                              array('product_id' => $this->id),
                                              true);
            }
            foreach($this->projects as $project){
                if($status){
                    $status = DBFunctions::insert("grand_product_projects", 
                                                  array('product_id' => $this->id,
                                                        'project_id' => $project->id),
                                                  true);
                }
            }
            if($status){
                // Commit transaction
                DBFunctions::commit();
                // Sync Authors
                $this->authorsWaiting = true;
                $this->syncAuthors();
                Cache::delete($this->getCacheId());
                if($this->getAccessId() == 0){
                    // Only send out notifications if the Product is public
                    foreach($this->getAuthors() as $author){
                        if($author instanceof Person && $me->getId() != $author->getId() && $author->getId() != 0){
                            Notification::addNotification($me, $author, "Author Added", "You have been added as an author to the ".strtolower($this->getCategory())." entitled <i>{$this->getTitle()}</i>", "{$this->getUrl()}");
                        }
                    }
                }
                self::$cache = array();
                self::$dataCache = array();
                self::$productProjectsCache = array();
            }
            return $status;
        }
        return false;
    }
    
    function update(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn() && trim($this->title) != ""){
            // Begin Transaction
            DBFunctions::begin();
            $authors = array();
            $oldProduct = new Product(DBFunctions::select(array('grand_products'),
                                                          array('*'),
                                                          array('id' => EQ($this->getId()))));
            foreach($this->authors as $author){
                if(isset($author->id) && $author->id != 0){
                    $authors[] = $author->id;
                }
                else if(isset($author->fullname)){
                    $authors[] = $author->fullname;
                }
                else{
                    // This is more for legacy purposes
                    $authors[] = $author->name;
                }
            }
            foreach($this->projects as $project){
                if(!isset($project->id) || $project->id == 0){
                    $p = Project::newFromName($project->name);
                    $project->id = $p->getId();
                }
            }
            // Update products table
            $status = DBFunctions::update('grand_products',
                                          array('category' => $this->category,
                                                'description' => $this->description,
                                                'type' => $this->type,
                                                'title' => $this->title,
                                                'date' => $this->date,
                                                'venue' => $this->venue,
                                                'status' => $this->status,
                                                'authors' => serialize($authors),
                                                'data' => serialize($this->data),
                                                'access_id' => $this->access_id,
                                                'access' => $this->access),
                                          array('id' => EQ($this->id)),
                                          array(),
                                          true);
            // Update product_projects table
            if($status){
                $status = DBFunctions::delete("grand_product_projects", 
                                              array('product_id' => EQ($this->id)),
                                              true);
            }
            foreach($this->projects as $project){
                if($status){
                    $status = DBFunctions::insert("grand_product_projects", 
                                                  array('product_id' => $this->id,
                                                        'project_id' => $project->id),
                                                  true);
                }
            }
            if($status){
                // Commit transaction
                DBFunctions::commit();
                // Sync Authors
                $this->authorsWaiting = true;
                $this->syncAuthors();
                Cache::delete($this->getCacheId());
                if($this->getAccessId() == 0){
                    // Only send out notifications if the Product was public
                    foreach($oldProduct->getAuthors() as $oldAuthor){
                        $found = false;
                        foreach($this->getAuthors() as $author){
                            if($author->getId() == $oldAuthor->getId()){
                                $found = true;
                            }
                        }
                        if(!$found && $oldAuthor instanceof Person && $me->getId() != $oldAuthor->getId() && $oldAuthor->getId() != 0){
                            // Author was Deleted
                            Notification::addNotification($me, $oldAuthor, "Author Removed", "You have been removed as an author from the ".strtolower($this->getCategory())." entitled <i>{$this->getTitle()}</i>", "{$this->getUrl()}");
                        }
                    }
                    foreach($this->getAuthors() as $author){
                        $found = false;
                        foreach($oldProduct->getAuthors() as $oldAuthor){
                            if($author->getId() == $oldAuthor->getId()){
                                $found = true;
                            }
                        }
                        if(!$found && $author instanceof Person && $me->getId() != $author->getId() && $author->getId() != 0){
                            // Author was Added
                            Notification::addNotification($me, $author, "Author Added", "You have been added as an author to the ".strtolower($this->getCategory())." entitled <i>{$this->getTitle()}</i>", "{$this->getUrl()}");
                        }
                    }
                }
                self::$cache = array();
                self::$dataCache = array();
                self::$productProjectsCache = array();
            }
            return $status;
        }
        return false;
    }
    
    function delete(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()){
            if($this->getAccessId() > 0){
                // Delete Permanently
                $status = DBFunctions::delete('grand_products',
                                              array('id' => EQ($this->getId())));
                if($status){
                    // Clean up other tables
                    DBFunctions::delete('grand_product_authors',
                                        array('product_id' => EQ($this->getId())));
                    DBFunctions::delete('grand_product_projects',
                                        array('product_id' => EQ($this->getId())));
                    DBFunctions::delete('grand_products_reported',
                                        array('product_id' => EQ($this->getId())));
                }
            }
            else{
                // Soft Delete
                $status = DBFunctions::update('grand_products',
                                        array('deleted' => '1'),
                                        array('id' => EQ($this->getId())));
            }
            if($status){
                Cache::delete($this->getCacheId());
                if($this->getAccessId() == 0){
                    // Only send out notifications if the Product was public
                    foreach($this->getAuthors() as $author){
                        if($author instanceof Person && $me->getId() != $author->getId() && $author->getId() != 0){
                            Notification::addNotification($me, $author, "{$this->getCategory()} Deleted", "Your ".strtolower($this->getCategory())." entitled <i>{$this->getTitle()}</i> has been deleted", "{$this->getUrl()}");
                        }
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
        $me = Person::newFromWgUser();
        if(Cache::exists($this->getCacheId()) && $me->isLoggedIn()){
            // Only access the cache if the user is logged in
            $json = Cache::fetch($this->getCacheId());
            return $json;
        }
        else{
            $authors = array();
            $projects = array();
            
            foreach($this->getAuthors(true, false) as $author){
                $authors[] = array('id' => $author->getId(),
                                   'name' => $author->getNameForProduct(),
				   'fullname' => $author->getNameForForms(),
                                   'url' => $author->getUrl());
            }
            if(is_array($this->getProjects())){
                foreach($this->getProjects() as $project){
                    $projects[] = array('id' => $project->getId(),
                                        'name' => $project->getName(),
                                        'url' => $project->getUrl());
                }
            }
            $data = $this->getData();
            if(count($data) == 0){
                $data = new stdClass();
            }
            $json = array('id' => $this->getId(),
                          'title' => $this->getTitle(),
                          'description' => $this->getDescription(),
                          'category' => $this->getCategory(),
                          'type' => $this->getType(),
                          'status' => $this->getStatus(),
                          'date' => $this->getDate(),
                          'url' => $this->getUrl(),
                          'data' => $data,
                          'authors' => $authors,
                          'projects' => $projects,
                          'lastModified' => $this->lastModified,
                          'deleted' => $this->isDeleted(),
                          'access_id' => $this->getAccessId(),
                          'created_by' => $this->getCreatedBy(),
                          'access' => $this->getAccess());
            if($me->isLoggedIn()){
                Cache::store($this->getCacheId(), $json, 60*60);
            }
            return $json;
        }
    }
    
    function exists(){
        return ($this->id != "");
    }
    
    function getCacheId(){
        return 'product'.$this->getId();
    }
}
?>
