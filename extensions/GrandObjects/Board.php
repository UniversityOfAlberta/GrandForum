<?php
/**
 * @package GrandObjects
 */
class Board extends BackboneModel {

    var $id;
    var $title;
    var $description;

    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->title = $data[0]['title'];
            $this->description = $data[0]['description'];
        }
    }

    /**
     * Returns a new Board from the given id
     * @param id $id The id of the Board
     * @return Board The Board with the given id
     */
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_boards'),
                                    array('*'),
                                    array('id' => $id));
        $board = new Board($data);
        return $board;
    }

    /**
     * Returns all Boards available to a user
     * @return boards An Array of Boards
     */
    static function getAllBoards(){
        global $wgRoleValues;
        $boards = array();

        $data = DBFunctions::select(array('grand_boards'),
                                    array('id'));
        if(count($data) >0){
            foreach($data as $boardId){
                $board = Board::newFromId($boardId['id']);
                $boards[] = $board;
            }
        }
        return $boards;
    }

    function getId(){
        return $this->id;
    }

    function getTitle(){
        return $this->title;
    }
    
    function getDescription(){
        return $this->description;
    }

    function getThreads(){
        return Thread::getAllThreads($this->getId());
    }
    
    function create(){
        return false;
    }

    function update(){
        return false;
    }

    function delete(){
        return false;
    }

    function canView(){
        $me = Person::newFromWgUser();
        return $me->isLoggedIn();
    }

    function toArray(){
        global $wgUser;
        if(!$wgUser->isLoggedIn()){
            return array();
        }
        $json = array('id' => $this->getId(),
                      'title' => $this->getTitle(),
                      'description' => $this->getDescription(),
                      'url' => $this->getUrl(),
                      'nThreads' => count($this->getThreads()));
        return $json;
    }

    function exists(){
        $board = Board::newFromId($this->getId());
        return ($board != null && $board->getId() != "");
    }

    function getCacheId(){
        global $wgSitename;
    }

    /**
     * Returns the url of this Board's page
     * @return string The url of this Board's page
     */
    function getUrl(){
        global $wgServer, $wgScriptPath;
        if(!isset($_GET['embed']) || $_GET['embed'] == 'false'){
            return "{$wgServer}{$wgScriptPath}/index.php/Special:MyThreads#/{$this->getId()}";
        }
        return "{$wgServer}{$wgScriptPath}/index.php/Special:MyThreads?embed#/{$this->getBoardId()}";
    }
}

?>
