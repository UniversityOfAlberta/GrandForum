<?php

require_once("SpecialUofANews.php");

class UofANews {

    var $id;
    var $user_id;
    var $title;
    var $url;
    var $date;
    var $firstSentences;
    var $img;

    static function getNewsForPerson($person){
        $data = DBFunctions::select(array('grand_uofa_news'),
                                    array('*'),
                                    array('user_id' => $person->getId()),
                                    array('date' => "DESC"));
        $news = array();
        foreach($data as $row){
            $news[] = new UofANews($row);
        }
        return $news;
    }
    
    static function getAllNews(){
        $data = DBFunctions::select(array('grand_uofa_news'),
                                    array('*'),
                                    array(),
                                    array('date' => "DESC"));
        foreach($data as $row){
            $news[$row['title'].$row['date']] = new UofANews($row);
        }
        return $news;
    }

    function UofANews($row){
        $this->id = $row['id'];
        $this->user_id = $row['user_id'];
        $this->title = $row['title'];
        $this->url = $row['url'];
        $this->date = $row['date'];
        $this->firstSentences = $row['first_sentences'];
        $this->img = $row['img'];
    }
    
    function getPerson(){
        return Person::newFromId($this->id);
    }

    function getTitle(){
        return $this->title;
    }
    
    function getPartialTitle(){
        $exploded = explode("|", $this->getTitle());
        return @$exploded[0];
    }
    
    function getUrl(){
        return $this->url;
    }

    function getDate(){
        return time2date($this->date);
    }
    
    function getFirstSentences(){
        return $this->firstSentences;
    }
    
    function getImg(){
        return $this->img;
    }

}

?>
