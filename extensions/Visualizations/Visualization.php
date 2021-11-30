<?php

// All visualizations go into this array.
$visualizations = array('Doughnut' => array("name" => "Doughnut",
                              "path" => "Doughnut/Doughnut.php",
                              "enabled" => true,
                              "initialized" => false),
                        'Chord' => array("name" => "Chord",
                              "path" => "Chord/Chord.php",
                              "enabled" => true,
                              "initialized" => false),
                        'Wordle' => array("name" => "Wordle",
                              "path" => "Wordle/Wordle.php",
                              "enabled" => true,
                              "initialized" => false),
                        'ForceDirectedGraph' => array("name" => "FDG",
                              "path" => "ForceDirectedGraph/ForceDirectedGraph.php",
                              "enabled" => true,
                              "initialized" => false),
                        'D3Map' => array("name" => "D3Map",
                              "path" => "D3Map/D3Map.php",
                              "enabled" => true,
                              "initialized" => false),
                        'TreeMap' => array("name" => "TreeMap",
                              "path" => "TreeMap/TreeMap.php",
                              "enabled" => true,
                              "initialized" => false),
                        'Cluster' => array("name" => "Cluster",
                              "path" => "Cluster/Cluster.php",
                              "enabled" => true,
                              "initialized" => false),
                        'VisTimeline' => array("name" => "VisTimeline",
                              "path" => "Vis/VisTimeline.php",
                              "enabled" => true,
                              "initialized" => false),
                        'Bar' => array("name" => "Bar",
                              "path" => "Bar/Chart.php",
                              "enabled" => true,
                              "initialized" => false)
                       );
      
// Activate all enabled visualizations                 
foreach($visualizations as $vis){
    if($vis['enabled'] === true){
        require_once($vis['path']);
    }
}

abstract class Visualization {
    
    static $visIndex = 0;
    var $initialized = false;
    var $index;
    
    function __construct(){
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
    
    // This is called when the visualization is 'required'.  Javascript libraries and whatnot should be imported here
    abstract static function init();

    // This should return a string, which is the html, javascript to show the visualization
    abstract function show();
}

?>
