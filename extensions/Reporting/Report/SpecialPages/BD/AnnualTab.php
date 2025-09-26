<?php

class AnnualTab extends AbstractTab {

    var $year;

    function __construct($year){
        parent::__construct(($year-1)."/".($year));
        $this->year = (int)$year;
    }
    
    static function getHTML($year="", $project=null, $theme=null){
        $start = ($year-1).NCE_START_MONTH;
        $end = "{$year}".NCE_END_MONTH;
        
        $papers = Product::getAllPapers('all', 'Publication', 'both', $start, $end);
        $raAuthorsCount = 0;
        foreach($papers as $paper){
            foreach($paper->getAuthors() as $author){
                if($author->getId() != 0){
                    $found = false;
                    $unis = $author->getUniversitiesDuring($start, $end);
                    foreach($unis as $uni){
                        if($uni['position'] == "Research Assistant"){
                            $found = true;
                            break;
                        }
                    }
                    if($found){
                        $raAuthorsCount++;
                    }
                }
            }
        }
        
        $html = "<table>
                    <tr>
                        <td><b># of RAs who were co-authors:</b></td>
                        <td>{$raAuthorsCount}</td>
                    </tr>
                </table>";
        return $html;
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        $this->html = self::getHTML($this->year);
    }
}
?>
