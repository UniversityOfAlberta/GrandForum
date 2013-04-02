<?php

require_once("AdminVisualizations/AdminVisualizations.php");

// All visualisations go into this array.
$visualisations = array('Timeline' => array("name" => "Timeline",
                              "path" => "Timeline/Timeline.php",
                              "enabled" => false,
                              "initialized" => false),
                        'Simile' => array("name" => "Simile",
                              "path" => "Simile/Simile.php",
                              "enabled" => true,
                              "initialized" => false),
                        'Doughnut' => array("name" => "Doughnut",
                              "path" => "Doughnut/Doughnut.php",
                              "enabled" => true,
                              "initialized" => false),
                        'Chord' => array("name" => "Chord",
                              "path" => "Chord/Chord.php",
                              "enabled" => true,
                              "initialized" => false),
                        'Graph' => array("name" => "Graph",
                              "path" => "Graph/Graph.php",
                              "enabled" => true,
                              "initialized" => false),
                        'HighCharts' => array("name" => "HighCharts",
                              "path" => "HighCharts/HighChart.php",
                              "enabled" => true,
                              "initialized" => false),
                        'Map' => array("name" => "Map",
                              "path" => "Map/Map.php",
                              "enabled" => true,
                              "initialized" => false),
                        'Wordle' => array("name" => "Wordle",
                              "path" => "Wordle/Wordle.php",
                              "enabled" => true,
                              "initialized" => false),
                        'ForceDirectedGraph' => array("name" => "FDG",
                              "path" => "ForceDirectedGraph/ForceDirectedGraph.php",
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
    
    function hashCode($str) {
        $hash = 0;
        $chars = str_split($str);
        for($i = 0; $i < count($chars); $i++) {
           $hash = ord($chars[$i]) + (($hash << 5) - $hash);
        }
        return $hash;
    } 

    function intToRGB($i){
        return "#".str_pad((($i>>16)&0xFF), 2, '0')
                  .str_pad((($i>>8)&0xFF), 2, '0')
                  .str_pad((($i)&0xFF), 2, '0');
    }
    
    // This is called when the visualisation is 'required'.  Javascript libraries and whatnot should be imported here
    abstract static function init();

    // This should return a string, which is the html, javascript to show the visualisation
    abstract function show();
}

?>
