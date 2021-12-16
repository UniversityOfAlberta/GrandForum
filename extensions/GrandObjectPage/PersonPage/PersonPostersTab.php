<?php

class PersonPostersTab extends AbstractTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("Posters/Slides");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgUser;
        $papers = $this->person->getPapers('all', true, 'both', true, 'Public');
        $posters = array();
        foreach($papers as $paper){
            $structure = $paper->getStructure();
            if(isset($structure['data'])){
                foreach($structure['data'] as $key => $field){
                    if($field['type'] == "PPT"){
                        if(isset($paper->getData($key)->filename)){
                            $posters[] = $paper;
                        }
                    }
                }
            }
        }
        if(count($posters) > 0){
            $posters = new Collection($posters);
            $this->html .= "<div id='carousel'></div>
                <script type='text/javascript'>
                    var carouselInterval = setInterval(function(){
                        if($('#carousel').is(':visible')){
                            createPosterCarousel('#carousel', {$posters->toJSON()});
                            clearInterval(carouselInterval);
                        }
                    }, 100);
                </script>";
        }
        return $this->html;
    }
}
?>
