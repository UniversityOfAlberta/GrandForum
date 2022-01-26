<?php

class VideoReportItem extends StaticReportItem {

	function render(){
	    global $wgOut;
	    $url = $this->getAttr("url");
	    $id = $this->id;
	    $module = str_replace(".xml", "", "{$this->getReport()->xmlName}");
	    $item = "<video controls width='100%' src='{$url}'></video><br />
                 <span style='font-weight:bold;'>Playback Speed: </span><span><input style='vertical-align:middle;' type='range' name='speed' id='speed' min='0.25' max='2' step='0.25' value='1'>&nbsp;<output class='speed-output' for='speed'></output></span>
                <script type='text/javascript'>
                    dc.init(me.get('id'), '{$module}');
                    dc.increment('{$id}PageCount');
                    dc.timer('{$id}Time');
                    
                    var speed = document.querySelector('#speed');
                    var output = document.querySelector('.speed-output');
                    
                    output.textContent = parseFloat(speed.value).toFixed(2) + 'X';

                    speed.addEventListener('input', function() {
                      output.textContent = parseFloat(speed.value).toFixed(2) + 'X';
                      $('video')[0].playbackRate = speed.value; 
                    });
                    
                    $('video').on('ended', function(){
                        $('a.reportTab.selectedReportTab').next().click();
                    });
                    $('video').on('timeupdate', function(){
                        var {$id}Watched = dc.video('{$id}Watched', this.duration);
                        {$id}Watched[Math.floor(this.currentTime)] = 1;
                        dc.setField('{$id}Watched', {$id}Watched);
                    });
                </script>";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    $this->render();
	}
	
}

?>
