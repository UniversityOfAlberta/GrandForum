<?php

require_once("Classes/removeCommonWords/removeCommonWords.php");

class Wordle extends Visualization {
    
    
    static $commonWords = array('and', 'i', 'then', 'or', 'but', 'an', 'from', 'for', 'will', 'upon', 
                                'some', 'well', 'long', 'more', 'less', 'form', 'them', 'span', 'most', 'least',
                                'ones', 'being', 'first', 'last', 'year', 'those', 'when', 'where', 'you', 'uses',
                                'than', 'have', 'real', 'while', 'much', 'been');
    static $commonStubs = array('http', 'www');
    static $a = 0;
    var $url = "";
    var $width = "500";
    var $height = "500";
    
    function Wordle($url){
        $this->url = $url;
        self::Visualization();
    }
    
    static function init(){
        global $wgOut, $wgServer, $wgScriptPath, $visualizations;
        $wgOut->addScript('<script src="'.$wgServer.$wgScriptPath.'/extensions/Visualizations/Wordle/js/d3.layout.cloud.js" type="text/javascript" charset="utf-8"></script>');
    }
    
    static function createDataFromText($text){
        $data = array();
        $lines = explode("\n", $text);
        foreach($lines as $line){
            $words = explode(" ", $line);
            foreach($words as $word){
                $word = preg_replace("/\&lt;.*\&gt;/", '', $word); // Strip out html-like stuff
                $word = preg_replace("/[^A-Za-z0-9 ]/", '', $word);
                $word = strtolower($word);
                $skip = false;
                foreach(self::$commonStubs as $stub){
                    if(strstr($word, $stub) !== false){
                        $skip = true;
                        break;
                    }
                }
                if(!$skip && strlen($word) > 3 && 
                   !is_numeric($word) &&
                   array_search($word, CommonWords::$commonWords) === false){
                    @$data[$word]++;
                }
            }
        }
        $retData = array();
        asort($data);
        $data = array_reverse($data);
        foreach($data as $word => $freq){
            $retData[] = array('word' => $word,
                               'freq' => $freq);
        }
        return $retData;
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $halfWidth = $this->width/2;
        $halfHeight = $this->height/2;
        $string = "<div id='vis{$this->index}'></div>";
        $string .= <<<EOF
<script type='text/javascript'>
    function onLoad{$this->index}(){
        $.get('{$this->url}', function(data){
            
            var fill = d3.scale.category20();
            
            var maxF = 0;
            for(fId in data){
                var f = data[fId].freq;
                maxF = Math.max(maxF, f);
            }
            for(fId in data){
                var f = data[fId].freq;
                data[fId].size = 10 + (f/maxF)*90;
            }

            d3.layout.cloud().size([{$this->width}, {$this->height}])
              .words(data.map(function(d) {
                return {text: d.word, size: d.size};
              }))
              .rotate(function() { return ~~(Math.random() * 2) * 90; })
              .font("Impact")
              .fontSize(function(d) { return d.size; })
              .on("end", draw)
              .start();

          function draw(words) {
            d3.select("#vis{$this->index}").append("svg")
                .attr("width", {$this->width})
                .attr("height", {$this->height})
              .append("g")
                .attr("transform", "translate({$halfWidth},{$halfHeight})")
              .selectAll("text")
                .data(words)
              .enter().append("text")
                .style("font-size", function(d) { return d.size + "px"; })
                .style("font-family", "Impact")
                .style("fill", function(d, i) { return fill(i); })
                .attr("text-anchor", "middle")
                .attr("transform", function(d) {
                  return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
                })
                .text(function(d) { return d.text; });
          }
      });
  }
</script>
EOF;
        return $string;
    }
}


?>
