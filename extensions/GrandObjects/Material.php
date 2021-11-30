<?php

/**
 * @package GrandObjects
 */

class Material {

    static $cache = array();
    
    var $id;
    var $title;
    var $type;
    var $timestamp;
    var $date;
    var $media;
    var $mediaLocal;
    var $url;
    var $description;
    var $people = array();
    var $projects = array();
    var $keywords = array();
    var $peopleWaiting;
    var $projectsWaiting;
    var $keywordsWaiting;

    // Returns the Material with the specified $id
    static function newFromId($id){
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        $sql = "SELECT *
                FROM grand_materials
                WHERE id = '".addslashes($id)."'
                AND type != 'form'";
        $data = DBFunctions::execSQL($sql);
        $material = new Material($data);
        self::$cache[$material->id] = &$material;
        self::$cache[$material->title] = &$material;
        return $material;
    }
    
    // Returns the Material with the specified $title
    static function newFromTitle($title){
        if(isset(self::$cache[$title])){
            return self::$cache[$title];
        }
        $sql = "SELECT *
                FROM grand_materials
                WHERE (title = '".addslashes($title)."'
		        OR title = '".str_replace(" ", "_", addslashes($title))."')
		        AND type != 'form'";
        $data = DBFunctions::execSQL($sql);
        $material = new Material($data);
        self::$cache[$material->id] = &$material;
        self::$cache[$material->title] = &$material;
        return $material;
    }
    
    /**
     * Returns the number of Materials there are
     * @return integer The number of Materials there are
     */
    static function countByCategory(){
        $data = DBFunctions::select(array('grand_materials'),
                                    array('COUNT(id)' => 'count'));
        return $data[0]['count'];
    }
    
    // Returns an array of all the Materials
    // If $type is specified, only those types of materials will be returned
    static function getAllMaterials($type=''){
        $sql = "SELECT id
                FROM `grand_materials`
                WHERE type != 'form'";
        if($type != ""){
            $type = str_replace("Youtube Video", "youtube", $type);
            $type = str_replace("Vimeo Video", "vimeo", $type);
            $type = str_replace("PDF Document", "pdf", $type);
            $type = str_replace("Other", "other", $type);
            $type = str_replace("Image", "img", $type);
            $type = str_replace("Video", "video", $type);
            $type = str_replace("Audio", "audio", $type);
            $type = str_replace("Presentation", "ppt", $type);
            $type = str_replace("Archive", "zip", $type);
            $sql .= "\nAND LOWER(`type`) = LOWER('{$type}')";
        }
        $data = DBFunctions::execSQL($sql);
        $materials = array();
        foreach($data as $row){
            $materials[] = Material::newFromId($row['id']);
        }
        return $materials;
    }
    
    // Returns an array with all keywords in the materials db
    static function getAllKeywords(){
        $sql = "SELECT DISTINCT keyword
                FROM `grand_materials_keywords`
                ORDER BY keyword asc";
        $data = DBFunctions::execSQL($sql);
        $array = array();
        foreach($data as $row){
            $array[] = $row['keyword'];
        }
        return $array;
    }
    
    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->title = $data[0]['title'];
            $this->type = $data[0]['type'];
            $this->timestamp = $data[0]['change_date'];
            $this->date = $data[0]['date'];
            $this->media = $data[0]['media'];
            $this->mediaLocal = $data[0]['mediaLocal'];
            $this->url = $data[0]['url'];
            $this->description = $data[0]['description'];
            $this->people = array();
            $this->projects = array();
            $this->keywords = array();
            $this->peopleWaiting = true;
            $this->projectsWaiting = true;
            $this->keywordsWaiting = true;
        }
    }
    
    // Returns the id of this Material
    function getId(){
        return $this->id;
    }
    
    // Returns the title of this Material
    function getTitle(){
        return $this->title;
    }
    
    // Returns the type of this Material
    function getType(){
        return $this->type;
    }
    
    // Returns the last time this Material was updated
    function getTimestamp(){
        return $this->timestamp;
    }
    
    // Returns the date of this Material
    function getDate(){
        return $this->date;
    }
    
    // Returns the type in human readable form
    function getHumanReadableType(){
        switch($this->type){
            case "other":
                return "Other";
                break;
            case "img":
                return "Image";
                break;
            case "Video":
            case "video":
                return "Video";
                break;
            case "audio":
                return "Audio";
                break;
            case "youtube":
                return "Youtube Video";
                break;
            case "vimeo":
                return "Vimeo Video";
                break;
            case "pdf":
                return "PDF Document";
                break;
            case "ppt":
                return "Presentation";
                break;
            case "zip":
                return "Archive";
                break;
        }
        return "None";
    }
    
    // Returns the media of this Material
    function getMedia(){
        return $this->media;
    }
    
    // Returns the local media file
    function getMediaLocal(){
        return $this->mediaLocal;
    }
    
    // Returns the URL to link to this Material
    function getUrl(){
        global $wgServer, $wgScriptPath;
        return "{$wgServer}{$wgScriptPath}/index.php/Multimedia:{$this->getId()}";
    }
    
    // Generates some html based on what type the Material is
    function getMediaLink(){
        global $wgServer;
        $html = "";
        if($this->getType() == "img"){
            $title = Title::newFromText("File:{$this->getMediaLocal()}");
            $imagePage = ImagePage::newFromID($title->getArticleId());
            $url = $wgServer.$imagePage->getFile()->getURL();
            $html .= "<a href='$url'><img style='max-width:800px;max-height:600px;' src='$url' /></a>";
        }
        else if($this->getType() == "video"){
            $title = Title::newFromText("File:{$this->getMediaLocal()}");
            $imagePage = ImagePage::newFromID($title->getArticleId());
            $url = $wgServer.$imagePage->getFile()->getURL();
            $html .= "<object CLASSID='clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B' width='800' height='500' CODEBASE='http://www.apple.com/qtactivex/qtplugin.cab'>
                        <param name='src' value='$url'>
                        <param name='qtsrc' value='$url'>
                        <param name='autoplay' value='false'>
                        <param name='loop' value='false'>
                        <param name='controller' value='true'>
                        <embed src='$url' qtsrc='$url' width='800' height='500' autoplay='false' loop='false' controller='true' pluginspage='http://www.apple.com/quicktime/'></embed>
                        </object>";
        }
        else if($this->getType() == "audio"){
            $title = Title::newFromText("File:{$this->getMediaLocal()}");
            $imagePage = ImagePage::newFromID($title->getArticleId());
            $url = $wgServer.$imagePage->getFile()->getURL();
            $html .= "<embed src='$url' autostart='false'></embed>";
        }
        else if($this->getType() == "youtube"){
            $html .= "<iframe width='853' height='480' src='https://www.youtube.com/embed/{$this->getMediaLocal()}' frameborder='0' allowfullscreen></iframe>";
        }
        else if($this->getType() == "vimeo"){
            $html .= "<iframe src='https://player.vimeo.com/video/{$this->getMediaLocal()}' width='853' height='480' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>";
        }
        else {
            if($this->getMediaLocal() != ""){
                $title = Title::newFromText("File:{$this->getMediaLocal()}");
                $imagePage = ImagePage::newFromID($title->getArticleId());
                $url = $wgServer.$imagePage->getFile()->getURL();
                $html .= "<a href='$url'>{$this->getMediaLocal()}</a>";
            }
            else{
                $html .= "<a href='{$this->getMedia()}'>{$this->getMedia()}</a>";
            }
        }
        return $html;
    }
    
    // Returns the url of this Material
    function getMaterialUrl(){
        return $this->url;
    }
    
    // Returns the description of this Material
    function getDescription(){
        return $this->description;
    }
    
    // Returns an array of Persons involved with this Material
    function getPeople(){
        if($this->peopleWaiting){
            $sql = "SELECT p.user_id
                    FROM `grand_materials_people` p, `mw_user` u
                    WHERE p.material_id = '{$this->getId()}'
                    AND p.user_id = u.user_id
                    AND u.deleted != '1'";
            $data = DBFunctions::execSQL($sql);
            $this->people = array();
            foreach($data as $row){
                $this->people[] = Person::newFromId($row['user_id']);
            }
            $this->peopleWaiting = false;
        }
        return $this->people;
    }
    
    // Returns an array of Projects involved with this Material
    function getProjects(){
        if($this->projectsWaiting){
            $sql = "SELECT project_id
                    FROM `grand_materials_projects`
                    WHERE material_id = '{$this->getId()}'";
            $data = DBFunctions::execSQL($sql);
            $this->projects = array();
            foreach($data as $row){
                $this->projects[] = Project::newFromId($row['project_id']);
            }
            $this->projectsWaiting = false;
        }
        return $this->projects;
    }
    
    // Returns the array of keywords belonging to this Material
    function getKeywords(){
        if($this->keywordsWaiting){
            $sql = "SELECT DISTINCT keyword
                    FROM `grand_materials_keywords`
                    WHERE material_id = '{$this->getId()}'";
            $data = DBFunctions::execSQL($sql);
            $this->keywords = array();
            foreach($data as $row){
                $this->keywords[] = $row['keyword'];
            }
            $this->keywordsWaiting = false;
        }
        return $this->keywords;
    }
    
    // Searches for the given phrase in the table of Materials
    // Returns an array of materials which fit the search
    static function search($phrase){
        $splitPhrase = explode(" ", $phrase);
        $sql = "SELECT title, id
                FROM(SELECT id, title
                           FROM `grand_materials`
                           WHERE title LIKE '%' 
                           AND type != 'form'\n";
        foreach($splitPhrase as $word){
            $sql .= "AND title LIKE '%$word%'\n";
        }
        $sql .= "GROUP BY id, title
                 ORDER BY id ASC) a
                 GROUP BY id";
        $data = DBFunctions::execSQL($sql);
        $materials = array();
        foreach($data as $row){
            $materials[] = array($row['id'], $row['title']);
        }
        $json = json_encode($materials);
        return $json;
    }
}
?>
