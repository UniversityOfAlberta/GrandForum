<?php

/**
 * @package GrandObjects
 */

class Journal extends BackboneModel {

    var $id;
    var $year;
    var $short_title;
    var $iso_abbrev;
    var $title;
    var $issn;
    var $eissn;
    var $description;
    var $ranking_numerator;
    var $ranking_denominator;
    var $impact_factor;
    var $cited_half_life;
    var $eigenfactor;

    function Journal($data){
        if (count($data) > 0){
            $row = $data[0]; // since we're passing the entire result set
            $this->id = $row['id'];
            $this->year = $row['year'];
            $this->short_title = $row['short_title'];
            $this->iso_abbrev = $row['iso_abbrev'];
            $this->title = $row['title'];
            $this->issn = $row['issn'];
            $this->eissn = $row['eissn'];
            $this->description = $row['description'];
            $this->ranking_numerator = $row['ranking_numerator'];
            $this->ranking_denominator = $row['ranking_denominator'];
            $this->impact_factor = $row['impact_factor'];
            $this->cited_half_life = $row['cited_half_life'];
            $this->eigenfactor = $row['eigenfactor'];
        }
    }

    /**
     * Returns a Journal from the given id
     * @param int $id The id of the journal
     * @return Journal The Journal from the given id
     */
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_journals'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $journal = new Journal($data);
        return $journal;
    }

    /**
     * Returns Journals from the given issn
     * @param string $issn The issn of the journal
     * @return array The Journals from the given issn. 
     * (Will only return journals from the more recent year)
     */
    static function newFromIssn($issn){
        $journals = array();
        if($issn != ""){
            $data = DBFunctions::select(array('grand_journals'),
                                        array('*'),
                                        array('issn' => $issn,
                                              WHERE_OR('eissn') => $issn),
                                        array('year' => 'DESC'));
            $lastYear = "0000";
            foreach($data as $row){
                if($row['year'] >= $lastYear){
                    $journals[] = new Journal(array($row));
                    $lastYear = $row['year'];
                }
                else{
                    break;
                }
            }
        }
        return $journals;
    }

    /**
     * Returns all Journals
     * @return Journals The array of all Journal objects
     */
    static function getAllJournals(){
        $journals = array();
        $data = DBFunctions::select(array('grand_journals'),
                                     array('*'));
        
        foreach($data as $row){
            $journal = new Journal(array($row));
            if ($journal != null && $journal->getId() != 0){
                $journals[] = $journal;
            }
        }
        return $journals;
    }

    static function getAllJournalsBySearch($string){
        $journals = array();
        $strings = explode(" ", unaccentChars($string));
        $data = DBFunctions::select(array('grand_journals'),
                                    array('MAX(year)'));
        $year = $data[0]['MAX(year)'];
        $data = DBFunctions::select(array('grand_journals'),
                                    array('*'),
                                    array('year' => EQ($year)));
        
        foreach($data as $row){
            $found = true;
            foreach($strings as $string){
                $title = unaccentChars($row['title']); // removes accented chars + lowers <-- exists in Javascript
                $description = unaccentChars($row['description']);
                $year = $row['year'];
                $short_title = unaccentChars($row['short_title']);
                $iso_abbrev = unaccentChars($row['iso_abbrev']);
                $issn = $row['issn'];
                $eissn = $row['eissn'];
                $results = preg_grep("/".preg_quote($string)."/", array($title, $year, $description, $short_title, $iso_abbrev, $issn, $eissn));
                if(empty($results)){ // everything must match
                    $found = false;
                    break;
                }
            }
            if($found){
                $journal = new Journal(array($row));
                if($journal != null && $journal->getId() != 0){
                    $journals[] = $journal;
                }
            }
        }
        return $journals;         
    }

    function getId(){
        return $this->id;
    }

    function getImpactFactor(){
        return $this->impact_factor;
    }

    function getRankNum(){
        return $this->ranking_numerator;
    }

    function getRankDenom(){
        return $this->ranking_denominator;
    }

    function getRank(){
        return "{$this->ranking_numerator}/{$this->ranking_denominator}";
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

    function toArray(){ //whatever's here, add to joural.js
        $json = array(
            'id' => $this->id,
            'year' => $this->year,
            'short_title' => $this->short_title,
            'iso_abbrev' => $this->iso_abbrev,
            'title' => $this->title,
            'issn' => $this->issn,
            'eissn', $this->eissn,
            'description' => $this->description,
            'ranking_numerator' => $this->ranking_numerator,
            'ranking_denominator' => $this->ranking_denominator,
            'category_ranking' => $this->getRank(),
            'impact_factor' => $this->impact_factor,
            'cited_half_life' => $this->cited_half_life,
            'eigenfactor' => $this->eigenfactor
        );
        return $json;
    }

}

?>
