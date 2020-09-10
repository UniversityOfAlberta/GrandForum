<?php

class ProjectPDFTab extends AbstractTab {

    var $project;
    var $rp;
    var $year;

    function ProjectPDFTab($project, $title, $rp, $year){
        parent::AbstractTab($title);
        $this->project = $project;
        $this->rp = (is_array($rp)) ? $rp : array($rp);
        $this->year = $year;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $me = Person::newFromWgUser();
        $this->html .= "<div id='accordion{$this->year}'>";
        foreach($this->rp as $rp){
            $report = new DummyReport($rp, $me, null, $this->year);
            $pdf = $report->getPDF();
            $this->html .= "<h3><a href='#'>{$report->name}</a></h3>";
            if(count($pdf) > 0){
                $this->html .= "<div>
                    <iframe src='{$wgServer}{$wgScriptPath}/scripts/ViewerJS/#{$wgServer}{$wgScriptPath}/index.php/Special:ReportArchive?getpdf={$pdf[0]['token']}&/' style='width:100%; height:600px;' frameborder='0' allowfullscreen='true'></iframe>
                </div>";
            }
            else {
                $this->html .= "<div>No Report generated for {$this->year}</div>";
            }
        }
        $this->html .= "</div>";
        $this->html .= "<script type='text/javascript'>
            var interval{$this->year} = setInterval(function(){
                if($('#accordion{$this->year}').is(':visible')){
                    $('#accordion{$this->year}').accordion({
                        heightStyle: 'content'
                    });
                    clearInterval(interval{$this->year});
                }
            }, 100);
        </script>";
    }

}    
    
?>
