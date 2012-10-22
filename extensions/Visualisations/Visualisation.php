<?php

// All visualisations go into this array.
$visualisations = array(array("name" => "Timeline",
                              "path" => "Timeline/Timeline.php",
                              "enabled" => false,
                              "initialized" => false),
                        array("name" => "Simile",
                              "path" => "Simile/Simile.php",
                              "enabled" => true,
                              "initialized" => false),
                        array("name" => "Doughnut",
                              "path" => "Doughnut/Doughnut.php",
                              "enabled" => true,
                              "initialized" => false),
                        array("name" => "Graph",
                              "path" => "Graph/Graph.php",
                              "enabled" => true,
                              "initialized" => false)
                       );
      
// Activate all enabled visualisations                 
foreach($visualisations as $vis){
    if($vis['enabled'] === true){
        require_once($vis['path']);
    }
}

abstract class Visualisation {
    
    static $visIndex = 0;
    var $initialized = false;
    var $index;
    
    function Visualisation(){
        if(!$this->initialized){
            $this->init();
            $this->initialized = true;
        }
        $this->index = self::$visIndex++;
    }
    
    // This is called when the visualisation is 'required'.  Javascript libraries and whatnot should be imported here
    abstract static function init();

    // This should return a string, which is the html, javascript to show the visualisation
    abstract function show();
}

?>
