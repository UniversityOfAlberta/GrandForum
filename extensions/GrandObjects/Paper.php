<?php

/**
 * @package GrandObjects
 */

class Paper extends BackboneModel{

    static $structure = null;
    static $illegalAuthorsCache = array();
    static $oldSyncCache = array();
    static $cache = array();
    static $dataCache = array();
    static $productProjectsCache = array();
    static $productTagsCache = array();
    static $topProductsCache = array();

    var $id;
    var $category;
    var $description;
    var $tags;
    var $title;
    var $type;
    var $projects = array();
    var $date;
    var $venue;
    var $status;
    var $authors;
    var $data = array();
    var $lastModified;
    var $authorsWaiting;
    var $projectsWaiting;
    var $deleted;
    var $access_id = 0;
    var $access = "Forum"; // Either 'Public', 'Forum' or 'Manager'
    var $created_by = 0;
    var $ccv_id;
    var $bibtex_id;
    
    /**
     * Returns a new Paper from the given id
     * @param integer $id The id of the Paper
     * @return Paper The Paper with the given id
     */
    static function newFromId($id){
        global $config;
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        $me = Person::newFromWgUser();
        $sql = "SELECT *
                FROM grand_products
                WHERE id = '$id'
                AND (access_id = '{$me->getId()}' OR access_id = 0)
                AND (access = 'Public'
                     OR (access = 'Forum' AND ".intVal($me->isLoggedIn()).")
                     OR (access = 'Manager' AND ".intVal($me->isRoleAtLeast(STAFF))."))";
        $data = DBFunctions::execSQL($sql);
        $paper = new Paper($data);
        if(!$config->getValue('showNonNetwork')){
            // Remove papers that are not associated with projects, or are made by the creator
            if(count($paper->getProjects()) == 0 && $paper->getCreatedBy() != $me->getId()){
                $paper = new Paper(array());
            }
        }
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
        global $config;
        if(isset(self::$cache[$ccv_id])){
            return self::$cache[$ccv_id];
        }
        $me = Person::newFromWgUser();
        $sql = "SELECT *
                FROM grand_products
                WHERE ccv_id = '$ccv_id'
                AND (access_id = '{$me->getId()}' OR access_id = 0)
                AND (access = 'Public'
                     OR (access = 'Forum' AND ".intVal($me->isLoggedIn()).")
                     OR (access = 'Manager' AND ".intVal($me->isRoleAtLeast(STAFF))."))";
        $data = DBFunctions::execSQL($sql);
        $paper = new Paper($data);
        if(!$config->getValue('showNonNetwork')){
            // Remove papers that are not associated with projects, or are made by the creator
            if(count($paper->getProjects()) == 0 && $paper->getCreatedBy() != $me->getId()){
                $paper = new Paper(array());
            }
        }
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
        global $config;
        if(trim($bibtex_id) == ""){
            return new Paper(array()); 
        }
        if(isset(self::$cache[$bibtex_id])){
            return self::$cache[$bibtex_id];
        }
        $me = Person::newFromWgUser();
        $bibtex_id = DBFunctions::escape($bibtex_id);
        $sql = "SELECT *
                FROM grand_products
                WHERE bibtex_id = '$bibtex_id'
                AND (access_id = '{$me->getId()}' OR access_id = 0)
                AND (access = 'Public'
                     OR (access = 'Forum' AND ".intVal($me->isLoggedIn()).")
                     OR (access = 'Manager' AND ".intVal($me->isRoleAtLeast(STAFF))."))
                LIMIT 1";
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
        if(!$config->getValue('showNonNetwork')){
            // Remove papers that are not associated with projects, or are made by the creator
            if(count($paper->getProjects()) == 0 && $paper->getCreatedBy() != $me->getId()){
                $paper = new Paper(array());
            }
        }
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
        global $config;
        if(count($ids) == 0){
            return array();
        }
        $me = Person::newFromWgUser();
        $ids = array_clean($ids);
        $sql = "SELECT *
                FROM grand_products
                WHERE id IN (".implode(",", $ids).")
                AND (access = 'Public'
                     OR (access = 'Forum' AND ".intVal($me->isLoggedIn()).")
                     OR (access = 'Manager' AND ".intVal($me->isRoleAtLeast(STAFF))."))";
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
        if(!$config->getValue('showNonNetwork')){
            // Remove papers that are not associated with projects, or are made by the creator
            foreach($papers as $key => $paper){
                if(count($paper->getProjects()) == 0 && $paper->getCreatedBy() != $me->getId()){
                    unset($papers[$key]);
                }
            }
        }
        return $papers;
    }
    
    // Returns a new Paper from the given id
    static function newFromTitle($title, $category = "%", $type = "%", $status = "%"){
        global $config;
        $title = str_replace("&#58;", ":", $title);
        $title = str_replace("'", "&#39;", $title);
        $title = DBFunctions::escape($title);
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
                AND (access = 'Public'
                     OR (access = 'Forum' AND ".intVal($me->isLoggedIn()).")
                     OR (access = 'Manager' AND ".intVal($me->isRoleAtLeast(STAFF))."))
                ORDER BY `id` desc";
        $data = DBFunctions::execSQL($sql);
        $paper = new Paper($data);
        if(!$config->getValue('showNonNetwork')){
            // Remove papers that are not associated with projects, or are made by the creator
            if(count($paper->getProjects()) == 0 && $paper->getCreatedBy() != $me->getId()){
                $paper = new Paper(array());
            }
        }
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
        global $config;
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
                    AND (access = 'Public'
                         OR (access = 'Forum' AND ".intVal($me->isLoggedIn()).")
                         OR (access = 'Manager' AND ".intVal($me->isRoleAtLeast(STAFF))."))";
            $data = DBFunctions::execSQL($sql);
            foreach($data as $row){
                $paper = new Paper(array($row));
                self::$cache[$paper->getId()] = $paper;
                $papers[$paper->getId()] = $paper;
            }
            if(!$config->getValue('showNonNetwork')){
                // Remove papers that are not associated with projects, or are made by the creator
                foreach($papers as $key => $paper){
                    if(count($paper->getProjects()) == 0 && $paper->getCreatedBy() != $me->getId()){
                        unset($papers[$key]);
                    }
                }
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
        $me = Person::newFromWgUser();
        $where = array('access_id' => EQ(0),
                       'deleted' => EQ(0));
        if(!$me->isLoggedIn()){
            $where['access'] = "Public";
        }
        else if(!$me->isRoleAtLeast(STAFF)){
            $where['access'] = NEQ("Manager");
        }
        if($category != 'all'){
            $where['category'] = EQ($category);
            $data = DBFunctions::select(array('grand_products'),
                                        array('COUNT(id)' => 'count'),
                                        $where);
        }
        else{
            $data = DBFunctions::select(array('grand_products'),
                                        array('COUNT(id)' => 'count'),
                                        $where);
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
                    if($p == null || $p->getId() == 0){
                        $p = Project::newFromId($project); // Try Id
                    }
                    if($p == null){
                        // Fail!
                        return array();
                    }
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
            $sql .= "\nAND (access = 'Public'
                            OR (access = 'Forum' AND ".intVal($me->isLoggedIn()).")
                            OR (access = 'Manager' AND ".intVal($me->isRoleAtLeast(STAFF))."))";
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
            if(!$config->getValue('showNonNetwork')){
                // Remove papers that are not associated with projects, or are made by the creator
                foreach($papers as $key => $paper){
                    if(count($paper->getProjects()) == 0 && $paper->getCreatedBy() != $me->getId()){
                        unset($papers[$key]);
                    }
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
            $startRange = date(YEAR."-01-01 00:00:00");
            $endRange = date(YEAR."-12-31 23:59:59");
        }
        $startRange = cleanDate($startRange);
        $endRange = cleanDate($endRange);
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
                $p = Project::newFromHistoricName($project);
                $sql .= ", `grand_product_projects` pp
                         WHERE pp.`project_id` = '{$p->getId()}'
                         AND pp.`product_id` = p.`id`";
            }
            else {
                $sql .= "\nWHERE 1";
            }
            $sql .= "\nAND (access = 'Public'
                            OR (access = 'Forum' AND ".intVal($me->isLoggedIn()).")
                            OR (access = 'Manager' AND ".intVal($me->isRoleAtLeast(STAFF))."))";
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
                    $papers[$paper->getId()] = $paper;
                }
                else if(($grand == 'grand' && $paper->isGrandRelated()) ||
                        ($grand == 'nonGrand' && !$paper->isGrandRelated()) ||
                         $grand == 'both'){
                    $papers[$paper->getId()] = $paper;
                }
            }
            if(!$config->getValue('showNonNetwork')){
                // Remove papers that are not associated with projects, or are made by the creator
                foreach($papers as $key => $paper){
                    if(count($paper->getProjects()) == 0 && $paper->getCreatedBy() != $me->getId()){
                        unset($papers[$key]);
                    }
                }
            }
            self::$dataCache[$proj.$category.$grand.$startRange.$endRange.$str] = $papers;
            return $papers;
        }
    }
    
    static function generateIllegalAuthorsCache(){
        if(empty(self::$illegalAuthorsCache)){
            $data = DBFunctions::select(array('grand_illegal_authors'),
                                        array('author'));
            self::$illegalAuthorsCache[""] = "";
            foreach($data as $row){
                self::$illegalAuthorsCache[$row['author']] = $row['author'];
            }
        }
    }
    
    static function generateProductProjectsCache(){
        if(empty(self::$productProjectsCache)){
            $data = DBFunctions::select(array('grand_product_projects'),
                                        array('product_id', 'project_id'));
            foreach($data as $row){
                self::$productProjectsCache[$row['product_id']][] = $row['project_id'];
            }
        }
    }
    
    static function generateProductTagsCache(){
        if(empty(self::$productTagsCache)){
            $data = DBFunctions::select(array('grand_product_tags'),
                                        array('tag', 'product_id'));
            foreach($data as $row){
                self::$productTagsCache[$row['product_id']][] = $row['tag'];
            }
        }
    }
    
    static function getAllTags(){
        $data = DBFunctions::select(array('grand_product_tags'),
                                    array('DISTINCT(`tag`)'));
        $tags = array();
        foreach($data as $row){
            $tags[] = $row['tag'];
        }
        return $tags;
    }
    
    static function generateTopProductsCache(){
        if(empty(self::$topProductsCache)){
            $data = DBFunctions::select(array('grand_top_products'),
                                        array('obj_id', 'product_id'),
                                        array('type' => EQ('PROJECT'),
                                              'product_type' => NEQ('CONTRIBUTION')));
            foreach($data as $row){
                $project = Project::newFromId($row['obj_id']);
                if($project != null && $project->getId() != 0){
                    self::$topProductsCache[$row['product_id']][] = $project;
                }
            }
        }
    }
    
    /**
     * Returns a php version of the Products.xml structure
     * @return array The array containing all the structure in Products.xml
     */
    static function structure(){
        global $config, $IP;
        if(self::$structure != null){
            return self::$structure;
        }
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
                    $citationFormat = @("{$tattrs->citationFormat}" != "") ? "{$tattrs->citationFormat}" : "{$cattrs->citationFormat}";
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
                                                                               'citationFormat' => $citationFormat,
                                                                               'ccv_status' => array(),
                                                                               'authors_label' => "Author");
                    foreach($type->children() as $child){
                        if($child->getName() == "data"){
                            foreach($child->children() as $field){
                                $fattrs = $field->attributes();
                                $fid = "$field";
                                $flabel = "{$fattrs->label}";
                                $fplaceholder = "{$fattrs->placeholder}";
                                $ftype = str_replace('{$networkName}', $config->getValue('networkName'), "{$fattrs->type}");
                                $fccvtk = "{$fattrs->ccvtk}";
                                $fbibtex = "{$fattrs->bibtex}";
                                $fhidden = (strtolower("{$fattrs->hidden}") == "true");
                                $foptions = explode("|", "{$fattrs->options}");
                                
                                $categories['categories'][$cname]['types'][$tname]['data'][$fid] = array('ccvtk' => $fccvtk,
                                                                                                         'bibtex' => $fbibtex,
                                                                                                         'label' => $flabel,
                                                                                                         'placeholder' => $fplaceholder,
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
                        else if($child->getName() == "authors"){
                            $attrs = $child->attributes();
                            $categories['categories'][$cname]['types'][$tname]["authors_label"] = ("{$attrs->label}" != "") ? "{$attrs->label}" : "Author";
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
        self::$structure = $categories;
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
            $this->tags = null;
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
     * Returns the tags for this Product
     * @return Array The tags for this Product
     */
    function getTags(){
        if($this->tags == null){
            self::generateProductTagsCache();
            $this->tags = array();
            if(isset(self::$productTagsCache[$this->getId()])){
                $this->tags = self::$productTagsCache[$this->getId()];
            }
        }
        return $this->tags;
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
                        $person = Person::newFromAlias($author);
                    }
                }
                self::generateIllegalAuthorsCache();
                if($person == null || 
                   $person->getName() == null || 
                   $person->getName() == "" || 
                   isset(self::$illegalAuthorsCache[$person->getNameForForms()]) ||
                   isset(self::$illegalAuthorsCache[$person->getId()])){
                    // Ok this person is not in the db, make a fake Person object
                    $author = preg_replace('/\s+/', ' ', $author);
                    $names = explode(" ", str_replace("<span class='noshow'>&quot;</span>", "", $author));
                    $first = @str_replace(".", "", str_replace('"', "", $names[0]));
                    $last = "";
                    $mNames = "";
                    if(count($names) > 1){
                        $last = @str_replace(".", "",  str_replace('"', "", $names[count($names)-1]));
                        if(count($names) > 2){
                            $mNames = array_slice($names, 1);
                            $mNames = array_slice($mNames, 0, count($mNames)-1);
                            $mNames = str_replace(".", "", implode(" ", $mNames));
                        }
                    }
                    if((mb_strlen($last) == 1 || strtoupper($last) == $last) && strlen($first) > 1){
                        // Simple check to see if last/first name format is reversed
                        $tmpLast = $last;
                        $last = $first;
                        $first = $tmpLast;
                    }
                    if(mb_strlen($first) > 1 && strtoupper($first) == $first){
                        // The first name is probably just initials
                        $firstTmp = $first;
                        $first = $firstTmp[0];
                        $mNames = $firstTmp[1];
                    }
                    
                    $pdata = array();
                    $pdata[0]['user_id'] = "";
                    $pdata[0]['user_name'] = $author;
                    $pdata[0]['user_real_name'] = $author;
                    $pdata[0]['first_name'] = $first;
                    $pdata[0]['middle_name'] = $mNames;
                    $pdata[0]['last_name'] = $last;
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
        if(empty(self::$oldSyncCache)){
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
            if(isset($alreadyDone[strtolower($author->getName())])){
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
            $alreadyDone[strtolower($author->getName())] = true;
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
     * Returns an array of Projects which this Paper is related to
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
    
    /**
     * Returns an array of Projects that have selected this Product as a top Product
     * @return array The Projects that have selected this Product as a top Product
     */
    function getTopProjects(){
        self::generateTopProductsCache();
        return (isset(self::$topProductsCache[$this->getId()])) ? self::$topProductsCache[$this->getId()] : array();
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
     * Returns the journal entry in the db that matches up with this Product
     * @return array The journal entry in the db that matches up with this Product
     */
    function getJournal(){
        $journal_title = DBFunctions::escape($this->getVenue());
        $issn = DBFunctions::escape($this->getData('issn'));
        if($journal_title == "" && $issn == ""){
            return array();
        }
        $data = DBFunctions::execSQL("SELECT * FROM `grand_journals` 
                                      WHERE (`title` = '{$journal_title}' 
                                             AND CONCAT(`ranking_numerator`, '/', `ranking_denominator`) = '{$this->getData('category_ranking')}')
                                      OR ((`issn` = '{$issn}' OR `eissn` = '{$issn}')
                                          AND CONCAT(`ranking_numerator`, '/', `ranking_denominator`) = '{$this->getData('category_ranking')}')
                                      LIMIT 1");
        if(count($data) > 0){
            return $data[0];
        }
        return array();
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
    
    function getStructure(){
        $structure = $this->structure();
        $category = $this->getCategory();
        $type = $this->getType();
        $types = explode(":", $this->getType());
        $type = $types[0];
        if(isset($structure['categories'][$category]['types'][$type])){
            return $structure['categories'][$category]['types'][$type];
        }
        return array();
    }
    
    /**
     * Returns the venue for this Paper (legacy stuff)
     * @return string The venue for this Paper
     */
    function getVenue(){
        $structure = $this->getStructure();
        if(isset($structure['data']['venue'])){
            return $this->getData('venue');
        }
        else if(isset($structure['data']['event_title'])){
            return $this->getData('event_title');
        }
        else if(isset($structure['data']['published_in'])){
            return $this->getData('published_in');
        }
        else if(isset($structure['data']['journal_title'])){
            return $this->getData('journal_title');
        }
        else if(isset($structure['data']['book_title'])){
            return $this->getData('book_title');
        }
        else if(isset($structure['data']['organization'])){
            return $this->getData('organization');
        }
        else if(isset($structure['data']['owner'])){
            return $this->getData('owner');
        }
        else if(isset($structure['data']['assignor'])){
            return $this->getData('assignor');
        }
        else if(isset($structure['data']['country'])){
            return $this->getData('country');
        }
        return "";
    }

    /**
     * Returns the domain specific data for this Paper
     * @return array The domain specific data for this Paper
     */
    function getData($field=null){
        if($this->data === false){
            $data = DBFunctions::select(array("grand_products"), array("data"), array("id"=>$this->getId()));
            if(count($data) >0){
                $this->data = unserialize($data[0]['data']);
            }
        }
        if($field != null){
            if(is_array($field)){
                foreach($field as $key){
                    if(isset($this->data[$key]) && $this->data[$key] != ""){
                        return $this->data[$key];
                    }
                }
                return "";
            }
            else{
                return @$this->data[$field];
            }
        }
        return $this->data;
    }
    
    /**
     * Returns whether or not this Paper is deleted
     * @return boolean Whether or not this Paper is deleted
     */
    function isDeleted(){
        return ($this->deleted === "1");
    }
    
    function getCitationFormat(){
        $categories = self::structure();
        if(@$categories['categories'][$this->getCategory()]['types'][$this->getType()]['citationFormat'] != ""){
            return $categories['categories'][$this->getCategory()]['types'][$this->getType()]['citationFormat'];
        }
        return "{%Authors} {(%YYYY %Mon).} <i>{%Title.}</i> {%Venue}{, %Volume}{(%Issue)}{:%Pages.} {%Publisher}"; // Default
    }
    
    /**
     * Return a string with a citation-like format
     * @param boolean $showStatus Whether or not to show the publication status
     * @param boolean $showPeerReviewed Whether or not to show the peer reviewed status
     * @param boolean $hyperlink Whether or not to use hyperlinks in the citation
     * @return string The citation text
     */
    function getCitation($showStatus=true, $showPeerReviewed=true, $hyperlink=true, $wrapAuthors=false){
        $me = Person::newFromWgUser();
        $citationFormat = $this->getCitationFormat();
        $format = $citationFormat;
        $regex = "/\{.*?\}/";
        $that = $this;
        $format = preg_replace_callback($regex, function($matches) use ($showStatus, $showPeerReviewed, $hyperlink, $wrapAuthors, $that) {
            return $that->formatCitation($matches, $showStatus, $showPeerReviewed, $hyperlink, $wrapAuthors);
        }, $format);
        
        $peerDiv = "";
        if($showPeerReviewed){
            $status = ($showStatus) ? $this->getStatus() : "";
            $peer_rev = "";
            $reported = "";
            $ifranking = array();
            $ranking = $this->getData(array('category_ranking'));
            $if = $this->getData(array('impact_factor'));
            $ranking_override = $this->getData(array('category_ranking_override'));
            $if_override = $this->getData(array('impact_factor_override'));
            $ratio = $this->getData(array('acceptance_ratio'));
            
            if($if_override != ""){
                $if = "<i>$if_override</i>";
            }
            if($ranking_override != ""){
                $ranking = "<i>$ranking_override</i>";
            }
            
            if($this->getCategory() == "Publication"){
                if($this->getData('peer_reviewed') == "Yes"){
                    $peer_rev = "&nbsp;/&nbsp;Peer Reviewed";
                }
                else if($this->getData('peer_reviewed') == "No"){
                    $peer_rev = "&nbsp;/&nbsp;Not Peer Reviewed";
                }
            }
            if($if != "" || $ranking != ""){
                if($if != ""){
                    $ifranking[] = "IF: {$if}";
                }
                
                if($ranking != ""){
                    $fraction = explode("/", $ranking);
                    $numerator = preg_replace("/[^0-9,.]/", "", @$fraction[0]);
                    $denominator = preg_replace("/[^0-9,.]/", "", @$fraction[1]);
                    $percent = number_format(($numerator/max(1, $denominator))*100, 2);
                    $ranking = $ranking." = {$percent}%";
                    $journal = $this->getJournal();
                    $jType = "";
                    if(isset($journal['description'])){
                        $jType = " ({$journal['description']})";
                    }
                    $ifranking[] = "Ranking: {$ranking}{$jType}";
                }
                $ifranking = implode("; ", $ifranking)."<br />";
            }
            else if(str_replace("/", "", $ratio) != ""){
                $ifranking = "Acceptance Rate: {$ratio}<br />";
            }
            else{
                $ifranking = "";
            }
            $peerDiv = "<div style='width:85%;margin-left:15%;text-align:right;'>{$ifranking}{$status}{$peer_rev}</div>";
        }
        return trim("{$format}{$peerDiv}");
    }
    
    function formatCitation($matches, $showStatus=true, $showPeerReviewed=true, $hyperlink=true, $wrapAuthors=false){
        $match1 = $matches[0];
        $match2 = $matches[0];
        $match = strtolower($matches[0]);
        if(strstr($match, "authors") !== false){
            $authors = array();
            foreach($this->getAuthors() as $a){
                if($a->getId()){
                    $name = $a->getNameForProduct();
                    if($a->isRoleOn(HQP, $this->getDate()) || $a->wasLastRole(HQP)){
                        $name = "<u>{$a->getNameForProduct()}</u>";
                    }
                    else if((!$a->isRoleOn(HQP, $this->getDate()) && !$a->wasLastRole(HQP)) &&
                            (!$a->isRoleOn(NI, $this->getDate()) && !$a->wasLastRole(NI))){
                        $name = "<i>{$a->getNameForProduct()}</i>";
                    }
                    if($a->isRoleOn(NI, $this->getDate())){
                        // Overwrite previous formatting if they are an NI
                        $name = "<b>{$a->getNameForProduct()}</b>";
                    }
                    if($hyperlink){
                        $authors[] = "<a target='_blank' href='{$a->getUrl()}'><b>{$name}</b></a>";
                    }
                    else{
                        $authors[] = "{$name}";
                    }
                } else {
                    $authors[] = $a->getNameForProduct();
                }
            }
            $authors = implode(", ", $authors);
            if($wrapAuthors){
                $authors = "<div class='authors'>{$authors}</div>";
            }
            $match1 = str_ireplace("%authors",   $authors,   $match1);
            $match2 = str_ireplace("%authors",   "",         $match2);
        }
        
        if(strstr($match, "yyyy") !== false ||
           strstr($match, "yy") !== false ||
           strstr($match, "mm") !== false ||
           strstr($match, "dd") !== false ||
           strstr($match, "month") !== false ||
           strstr($match, "mon") !== false){
            $date = $this->getDate();
            $yyyy = substr($date, 0, 4);
            $yy = substr($date, 2, 2);
            $mm = substr($date, 5, 2);
            $dd = substr($date, 8, 2);
            
            $month = date('F', strtotime($date));
            $mon = date('M', strtotime($date));
            
            $match1 = str_ireplace("%yyyy",      $yyyy,      $match1);
            $match1 = str_ireplace("%yy",        $yy,        $match1);
            $match1 = str_ireplace("%mm",        $mm,        $match1);
            $match1 = str_ireplace("%dd",        $dd,        $match1);
            $match1 = str_ireplace("%month",     $month,     $match1);
            $match1 = str_ireplace("%mon",       $mon,       $match1);
            
            $match2 = str_ireplace("%yyyy",      "",         $match2);
            $match2 = str_ireplace("%yy",        "",         $match2);
            $match2 = str_ireplace("%mm",        "",         $match2);
            $match2 = str_ireplace("%dd",        "",         $match2);
            $match2 = str_ireplace("%month",     "",         $match2);
            $match2 = str_ireplace("%mon",       "",         $match2);
        }
        
        if(strstr($match, "title") !== false){
            if($hyperlink){
                $title = "<a href='{$this->getUrl()}'>{$this->title}</a>";
            }
            else{
                $title = $this->title;
            }
            $match1 = str_ireplace("%title",     $title,     $match1);
            $match2 = str_ireplace("%title",     "",         $match2);
        }
        
        if(strstr($match, "type") !== false){
            $type = $this->type;
            $match1 = str_ireplace("%type",      $type,      $match1);
            $match2 = str_ireplace("%type",      "",         $match2);
        }

        if(strstr($match, "pages") !== false){
            $pages = $this->getData(array('ms_pages', 'pages'));
            $match1 = str_ireplace("%pages",     $pages,     $match1);
            $match2 = str_ireplace("%pages",     "",         $match2);
        }
        
        if(strstr($match, "publisher") !== false){
            $publisher = $this->getData(array('publisher'));
            $match1 = str_ireplace("%publisher", $publisher, $match1);
            $match2 = str_ireplace("%publisher", "",         $match2);
        }
        
        if(strstr($match, "venue") !== false){
            $venue = $this->getVenue();
            $match1 = str_ireplace("%venue",     $venue,     $match1);
            $match2 = str_ireplace("%venue",     "",         $match2);
        }
        
        if(strstr($match, "editor") !== false){
            $editor = $this->getData(array('editors'));
            $match1 = str_ireplace("%editor",    $editor,    $match1);
            $match2 = str_ireplace("%editor",    "",         $match2);
        }
        
        if(strstr($match, "volume") !== false){
            $volume = $this->getData(array('volume'));
            $match1 = str_ireplace("%volume",    $volume,    $match1);
            $match2 = str_ireplace("%volume",    "",         $match2);
        }
        
        if(strstr($match, "issue") !== false){
            $issue = $this->getData(array('number'));
            $match1 = str_ireplace("%issue",     $issue,     $match1);
            $match2 = str_ireplace("%issue",     "",         $match2);
        }
        
        if(strstr($match, "institution") !== false){
            $institution = $this->getData(array('institution'));
            $match1 = str_ireplace("%institution", $institution,     $match1);
            $match2 = str_ireplace("%institution", "",               $match2);
        }
        
        if(strstr($match, "frequency") !== false){
            $frequency = $this->getData(array('frequency'));
            $match1 = str_ireplace("%frequency", $frequency,     $match1);
            $match2 = str_ireplace("%frequency", "",             $match2);
        }
        
        if(strstr($match, "value") !== false){
            $value = $this->getData(array('value'));
            $match1 = str_ireplace("%value",    $value,        $match1);
            $match2 = str_ireplace("%value",    "",            $match2);
        }
        
        if(strstr($match, "url") !== false){
            $url = $this->getData(array('url'));
            $match1 = str_ireplace("%url",      $url,        $match1);
            $match2 = str_ireplace("%url",    "",            $match2);
        }

        if($match1 == $match2){
             $matches[0] = "";
        }
        else{
            $matches[0] = str_replace("}","",str_replace("{","",$match1));
        }
        return implode("", $matches);
    }

    /**
     * Checks appropriate type of paper for requred venue, pages and publisher fields. If paper falls under category that
     * requires these fields, it checks them for completeness, otherwise returns them as complete.
     * @return array An associative array describing the completeness of this Paper
     */
    function getCompleteness(){
        $noVenue = $noPublisher = $noPages = false;
        $completeness = array("venue"=>true, 'pages'=>true, 'publisher'=>true);
        $vn = $this->getVenue();
        if($this->getType() == "Proceedings Paper" && $vn == ""){
            $completeness['venue'] = false;
        }
        
        if(in_array($this->getType(), array('Book', 'Collections Paper', 'Proceedings Paper', 'Journal Paper'))){
            $pg = (isset($this->data['pages'])) ? "{$this->data['pages']}" : null;
            if ($pg == ''){
                $completeness['pages'] = false;
            }
            $pb = (isset($this->data['publisher'])) ? "{$this->data['publisher']}" : null;
            if($pb == ''){
                $completeness['publisher'] = false;
            }
            if($vn == ''){
                $completness['venue'] = false;
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
        if($me->isLoggedIn() && !$me->isCandidate() && trim($this->title) != ""){
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
            $this->bibtex_id = @$this->data['doi'];
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
                $this->id = DBFunctions::insertId();
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
            if(is_array($this->tags) && count($this->tags) > 0){
                foreach($this->tags as $tag){
                    DBFunctions::insert('grand_product_tags',
                                        array('tag' => $tag,
                                              'product_id' => $this->id));
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
        if($me->isLoggedIn() && !$me->isCandidate() && trim($this->title) != ""){
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
            $this->bibtex_id = @$this->data['doi'];
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
                                                'deleted' => $this->deleted,
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
            DBFunctions::delete('grand_product_tags',
                                array('product_id' => EQ($this->id)));
            if(is_array($this->tags) && count($this->tags) > 0){
                foreach($this->tags as $tag){
                    DBFunctions::insert('grand_product_tags',
                                        array('tag' => $tag,
                                              'product_id' => $this->id));
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
        if($me->isLoggedIn() && !$me->isCandidate()){
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
            $topProjects = array();
            foreach($this->getAuthors(true, false) as $author){
                $authors[] = array('id' => $author->getId(),
                                   'name' => $author->getNameForProduct(),
                                   'email' => $author->getEmail(),
                                   'fullname' => $author->getNameForForms(),
                                   'url' => $author->getUrl());
            }
            if(is_array($this->getProjects())){
                foreach($this->getProjects() as $project){
                    $url = "";
                    if($project->getId() != -1){
                        $url = $project->getUrl();
                    }
                    $projects[] = array('id' => $project->getId(),
                                        'name' => $project->getName(),
                                        'url' => $url);
                }
            }
            if(is_array($this->getTopProjects())){
                foreach($this->getTopProjects() as $project){
                    $url = "";
                    if($project->getId() != -1){
                        $url = $project->getUrl();
                    }
                    $topProjects[] = array('id' => $project->getId(),
                                           'name' => $project->getName(),
                                           'url' => $url);
                }
            }
            $data = $this->getData();
            if(empty($data) || count($data) == 0){
                $data = new stdClass();
            }
            $json = array('id' => $this->getId(),
                          'title' => $this->getTitle(),
                          'description' => $this->getDescription(),
                          'tags' => $this->getTags(),
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
                          'access' => $this->getAccess(),
                          'topProjects' => $topProjects,
                          'citation' => $this->getCitation(false,false,false));
            if($me->isLoggedIn()){
                Cache::store($this->getCacheId(), $json, 60*60);
            }
            return $json;
        }
    }
    
    /**
     * Exports this Paper as a BibTeX
     * @return string This Paper's BibTeX
     */
    function toBibTeX(){
        $dir = dirname(__FILE__);
        require_once($dir."/../../Classes/CCCVTK/bibtex-bib.lib.php");
        $hash = ImportBibTeXAPI::$bibtexHash;
        foreach($hash as $key => $types){
            if($types == $this->getType() ||
               (is_array($types) && in_array($this->getType(), $types))){
                // Compatable with BibTeX
                $pStructure = Product::structure();
                $structure = null;
                if(isset($pStructure['categories'][$this->getCategory()]['types'][$this->getType()])){
                    // Make sure that the type actually exists
                    $structure = $pStructure['categories'][$this->getCategory()]['types'][$this->getType()];
                }
                else{
                    $found = false;
                    foreach($pStructure['categories'] as $cat => $cats){
                        if(isset($cats['types'][$this->getType()])){
                            // Then check if the type might exist in a different category
                            $structure = $pStructure['categories'][$cat]['types'][$this->getType()];
                            break;
                        }
                    }
                    if(!$found){
                        return false;
                    }
                }
                $authors = new Collection($this->getAuthors());
                $bibtex = array('bibtex_type' => $key,
                                'raw' => array('title' => $this->getTitle()));
                $bibtex['raw']['author'] = trim(preg_replace('/\s+/', ' ', str_replace("<span class='noshow'>&quot;</span>", "", implode(" and ", $authors->pluck('getNameForProduct("{%First} {%M.} {%Last}")')))));
                $bibtex['raw']['year'] = substr($this->getDate(), 0, 4);
                $bibtex['raw']['month'] = date("M", strtotime($this->getDate()));
                $bibtex['raw']['abstract'] = $this->getDescription();
                
                if($structure != null){
                    foreach($structure['data'] as $dkey => $dfield){
                        if(isset($dfield['bibtex']) && $dfield['bibtex'] != ""){
                            $bibtex['raw'][$dfield['bibtex']] = $this->getData($dkey);
                        }
                    }
                }
                
                $bib = new Bibliography();
                $bib->m_entries[$key] = $bibtex;
                return $bib->toBibTeX();
            }
        }
        return "";
    }
    
    function exists(){
        return ($this->id != "" && $this->id != 0);
    }
    
    function getCacheId(){
        return 'product'.$this->getId();
    }
}
?>
