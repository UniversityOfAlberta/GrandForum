<?php

autoload_register("../Classes/removeCommonWords");

class Wordle extends Visualization {
    
    static $commonStubs = array('http', 'www');
    static $a = 0;
    var $url = "";
    var $width = "500";
    var $height = "500";
    var $clickable = "false";
    var $fn = "";
    
    /**
     * Creates a new Wordle visualization
     * @param string $url The data url
     * @param boolean $clickable Whether or not the words should respond to click events (and hover events)
     * @param string $fn The javascript code to run when a word is clicked.  A 'text' variable can be accessed for this code
     */
    function __construct($url, $clickable=false, $fn=""){
        $this->url = $url;
        $this->clickable = ($clickable) ? "true" : "false";
        $this->fn = $fn;
        parent::__construct();
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
                $word = preg_replace("/[^A-Za-z0-9 ]/", ' ', $word);
                $word = trim($word);
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
            $retData[] = array('word' => ucfirst($word),
                               'freq' => $freq);
        }
        return $retData;
    }

    function show(){
        global $wgOut, $wgServer, $wgScriptPath;
        $halfHeight = $this->height/2;
        $string = "<div id='vis{$this->index}' style='height:{$this->height}px;'></div>";
        $string .= <<<EOF
<script type='text/javascript'>
    function onLoad{$this->index}(){
        $.get('{$this->url}', function(data){
            
            var clickable = {$this->clickable};
            
            var fill = d3.scale.ordinal()
                               .range(["#432724", 
                                       "#2f4340", 
                                       "#566663", 
                                       "#6c6f5e", 
                                       "#938e73"]);
            
            var maxF = 0;
            var minF = 100000;
            if(data.length == 0){
                $("#vis{$this->index}").next().remove();
                $("#vis{$this->index}").next().remove();
                $("#vis{$this->index}").remove();
                return;
            }
            for(fId in data){
                var f = data[fId].freq;
                maxF = Math.max(maxF, f);
                minF = Math.min(minF, f);
            }
            if(maxF == minF){
                maxF = 1;
                minF = 0;
            }
            for(fId in data){
                var f = data[fId].freq;
                data[fId].size = 10 + (f - minF)/(maxF - minF)*Math.max(50, Math.min(100, ($("#vis{$this->index}").width()*0.1)));
            }
            
            var maxWidth = '{$this->width}';
            if(maxWidth == "100%"){
                maxWidth = $("#vis{$this->index}").width();
                setInterval(function(){
                    if($("#vis{$this->index}").is(":visible") && maxWidth != $("#vis{$this->index}").width() && Math.abs(maxWidth - $("#vis{$this->index}").width()) > 25){
                        maxWidth = $("#vis{$this->index}").width();
                        if(maxWidth > 0){
                            for(fId in data){
                                var f = data[fId].freq;
                                data[fId].size = 10 + (f - minF)/(maxF - minF)*Math.max(50, Math.min(100, (maxWidth*0.1)));
                            }
                            doCloud();
                        }
                    }
                }, 100);
            }
            
            var started = new Array();
            var cloud = null;
            var doCloud = function(){
                $("#vis{$this->index} svg").width(maxWidth);
                started.push(true);
                cloud = d3.layout.cloud().size([maxWidth, {$this->height}])
                  .words(data.map(function(d) {
                    return {text: d.word, size: d.size};
                  }))
                  .timeInterval(Infinity)
                  .rotate(0)
                  .font("Times New Roman, Times")
                  .fontSize(function(d) { return d.size; })
                  .on("end", draw)
                  .start();

                function draw(words) {
                  started.pop();
                  if(started.length > 0){
                      return false;
                  }
                  $("#vis{$this->index}").empty();
                  d3.select("#vis{$this->index}").append("svg")
                    .style("position", "absolute")
                    .attr("width", maxWidth)
                    .attr("height", {$this->height})
                  .append("g")
                    .attr("transform", "translate(" + (maxWidth/2) + ",{$halfHeight})")
                  .selectAll("text")
                    .data(words)
                  .enter().append('text')
                    .style("font-size", function(d) { return d.size + "px"; })
                    .style("font-family", "Times New Roman, Times")
                    .style("fill", function(d, i) { return fill(i); })
                    .attr("text-anchor", "middle")
                    .attr("transform", function(d) {
                      return "translate(" + [d.x, d.y] + ")";
                    })
                    .text(function(d) { return d.text; });
                    
                    if(clickable){
                        d3.select("#vis{$this->index}")
                          .selectAll("text")
                          .style("cursor", "pointer")
                          .on('mouseover', function(d, i){
                              d3.select(this).style("fill", d3.rgb(fill(i)).brighter(1));
                          })
                          .on('mouseout', function(d, i){
                              d3.select(this).style("fill", fill(i));
                          })
                          .on('click', function(d){
                              var text = d3.select(this).text();
                              {$this->fn}
                          });
                      }
                  }
              }
              if($("#vis{$this->index}").is(":visible")){
                doCloud();
              }
      });
  }
</script>
EOF;
        return $string;
    }
}


?>
