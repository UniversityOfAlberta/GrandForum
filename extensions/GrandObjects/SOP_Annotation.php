<?php

/**
 * @package GrandObjects
 */

class SOP_Annotation {
    
    static $cache = array();
    
    var $id;
    var $content;
    var $user_id;
    var $user;
    var $created;
    var $updated;
    var $ranges;
    var $context;
    var $text;
    var $quote;
    var $sop_id;
    var $tags;

  /**
   * newFromId Creates a new sop annotation object from a given id
   * @param $id
   * @return SOP_Annotation
   */
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_sop_annotation'),
                                    array('*'),
                                    array('id' => EQ($id)));
        if(count($data)>0){
            $person = Person::newFromId($data[0]["user_id"]);
            $data[0]["user"] = $person->getName();
        }
        $sop = new SOP_Annotation($data);
        return $sop;
    }

  /**
   * SOP_Annotation SOP_Annotation constructor.
   * @param $data
   */
    function SOP_Annotation($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->user = isset($row["user"]) ? $row['user'] : "";
            $this->sop_id = $row['sop_id'];
//            $this->created = $row['date_created'];
            $this->updated = $row['date_updated'];
            $this->ranges = json_decode($row['ranges'],true);
            $this->content = $row['content'];
            $this->text = $row['text'];
            $this->quote = $row['quote'];
            $this->tags = json_decode($row['tags']);
            // permissions are dynamically generate and not saved in the database. Used when returning annotation
            $this->permissions = array(
              'read' => array(),
              'update' => array(),
              'delete' => array(),
              'admin' => array(),
            );
            $me = Person::newFromWgUser();
            $this->permissions['read'][] = $me->getId(); // everyone who is logged in should be able to read
            if($this->user_id == $me->getId()){ // if current user is also annotation creator, allow delete and update
                $this->permissions['update'][] = $me->getId();
                $this->permissions['delete'][] = $me->getId();
            }
            if($me->getRoleString() == "Admin"){ // admin gets special privileges
                $this->permissions['admin'][] = $me->getId();
            }
        }
    }


    /**
     * getAllSOPAnnotations Returns all SOP_Annotations available to a user if sop_id specified, use id
     * @return sop An Array of SOP_Annotations
     */
    static function getAllSOPAnnotations($sop_id = false){
        global $wgRoleValues;
        $sops = array();
        $data = null;
        $me = Person::newFromWgUser();
        $sql = "SELECT sa.*, mu.user_name
                    FROM grand_sop_annotation sa, mw_user mu WHERE sa.user_id = mu.user_id";
                    //WHERE sa.user_id = u.user_id";
        //if($me->isRoleAtLeast(MANAGER)){
        if($sop_id){
            $sql .= " AND sop_id = {$sop_id}";
        }
        $data = DBFunctions::execSQL($sql);
        /*if($sop_id){
            $data = DBFunctions::select(array('grand_sop_annotation', ''),
              array('id'),
              array('sop_id' => EQ($sop_id)));
        } else {
            $data = DBFunctions::select(array('grand_sop_annotation'),
              array('id'));
        }*/
        //}
        if(count($data) >0){
            foreach($data as $sop_data){
//		            $sop = SOP_Annotation::newFromId($sop_data['id']);
//                $sops[] = $sop;
		            $sop_data["user"] = $sop_data["user_name"];
                $sop = new SOP_Annotation(array($sop_data));
                $sops[] = $sop;
            }
        }
	return $sops;
    }


  /**
   * setSopId sets the sop id for the sop annotation object
   * @param $sop_id
   * @return mixed
   */
    function setSopId($sop_id){
        $this->sop_id = $sop_id;
        return $this->sop_id;
    }

  /**
   * sets the user id for the sop annotation object
   * @param $user_id
   * @return mixed
   */
    function setUserId($user_id){
        $this->user_id = $user_id;
        return $this->user_id;
    }

  /**
   * setContent sets the content for the sop annotation object
   * @param $content
   * @return mixed
   */
    function setContent($content){
        $this->content = $content;
        return $this->content;
    }

  /**
   * setRanges sets the ranges for the sop annotation object
   * @param $ranges
   * @return mixed|string
   */
    function setRanges($ranges){
        if (is_array($ranges)){
	   // $ranges[0]['start'] = "";
	   // $ranges[0]['end'] = "";
            $ranges = json_encode($ranges);
        }
        $this->ranges = $ranges;
        return $this->ranges;
    }

    /**
     * setRanges sets the tags for the sop annotation object
     * @param $tags
     * @return mixed|string
     */
    function setTags($tags){
        if (is_array($tags)){
            $tags = json_encode($tags);
        }
        $this->tags = $tags;
        return $this->$tags;
    }

  /**
   * setText sets the text for the sop annotation object
   * @param $text
   * @return mixed
   */
    function setText($text){
        $this->text = $text;
        return $this->text;
    }

  /**
   * setQuote sets the quote for the sop annotation object
   * @param $quote
   * @return mixed
   */
    function setQuote($quote){
        $this->quote = $quote;
        return $this->quote;
    }

  /**
   * getId gets the sop annotation id
   * @return mixed
   */
    function getId(){
        return $this->id;
    }

  /**
   * getContent gets the sop annootation content
   * @return mixed
   */
    function getContent(){
        return $this->content;
    }

  /**
   * getText gets the sop annotation text
   * @return mixed
   */
    function getText(){
        return $this->text;
    }

  /**
   * gets the sop annotation quote
   * @return mixed
   */
    function getQuote(){
        return $this->quote;
    }

  /**
   * getUser gets the sop annotation user id
   * @return mixed
   */
    function getUser(){
	      return $this->user_id;
    }

  /**
   * getDateCreated gets the sop annotation date created
   * @return mixed
   */
    function getDateCreated(){
	      return $this->created;
    }

    //-----Db methods----//


  /**
   * create creates a new sop annotation object in the database
   * @return bool|SOP_Annotation
   */
    function create(){
        $me = Person::newFromWgUser();
        // TODO:: add field for user_id of annotator creator
        $users = array();
        if($me->isRoleAtLeast(AR)){
            DBFunctions::begin();
            $status = DBFunctions::insert('grand_sop_annotation',
              array(
                'user_id' => $this->user_id,
                'ranges' => $this->ranges,
                'tags' => $this->tags,
                'sop_id' => $this->sop_id,
                'content' => $this->content,
                'text' => $this->text,
                'quote' => $this->quote),
              true
            );
            $this->quote = DBFunctions::escape($this->quote);
            $this->text = DBFunctions::escape($this->text);
            $statement = "SELECT * FROM `grand_sop_annotation` WHERE `quote` LIKE '%".$this->quote."%' AND `text` LIKE '%".$this->text."%'";
            $data = DBFunctions::execSQL($statement);
            if($status){
                DBFunctions::commit();
                return SOP_Annotation::newFromId($data[0]['id']);
            }
        }
        return false;
    }

  /**
   * delete deletes the sop annotation object from the database
   * @return bool
   */
    function delete(){
        $status = DBFunctions::delete('grand_sop_annotation',
          array('id' => EQ($this->getId())));

        if($status){
            return $status;
        }
        return false;
    }
    
}

?>
