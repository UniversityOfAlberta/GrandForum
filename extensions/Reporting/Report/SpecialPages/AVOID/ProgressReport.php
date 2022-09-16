<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ProgressReport'] = 'ProgressReport'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ProgressReport'] = $dir . 'ProgressReport.i18n.php';
$wgSpecialPageGroups['ProgressReport'] = 'reporting-tools';

function runProgressReport($par) {
    ProgressReport::execute($par);
}

class ProgressReport extends SpecialPage {
    
    function __construct() {
        SpecialPage::__construct("ProgressReport", null, true, 'runProgressReport');
    }
    
    function userCanExecute($wgUser){
        $person = Person::newFromUser($wgUser);
        return $person->isLoggedIn();
    }
    
    function drawRow($key, $row, $scores){
        global $wgServer, $wgScriptPath;
        $need = "N";
        $education = "";
        $programs = "";
        $community = "";
        if($scores[$key] > 0){
            $need = "Y";
            foreach($row['education'] as $k => $e){
                $education .= "<p><a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/{$e}' target='_blank'>{$k}</a></p>";
            }
            foreach($row['programs'] as $k => $p){
                if(is_array($p)){
                    $links = array();
                    foreach($p as $k1 => $p1){
                        $links[] = "<a href='{$p1}' target='_blank'>{$k1}</a>";
                    }
                    $programs .= "<p>{$k} ".implode(", ", $links)."</p>";
                }
                else{
                    $programs .= "<p><a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/{$p}' target='_blank'>{$k}</a></p>";
                }
            }
            foreach($row['community'] as $k => $p){
                $k = preg_replace("/(.*(^|→|↴))(?!.*(→|↴))(.*)/", "$1<a style='vertical-align:top;' target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/{$p}'>$4</a>", $k);
                $k = str_replace("→", "</span>→<span class='cb'>", $k);
                $k = str_replace("↴", "</span>↴<span class='cb' style='width: 100%; text-align: right;'>", $k);
                $community .= "<p><span class='cb'>{$k}</span></p>";
            }
        }
        $html = "<tr>
                    <td align='center' style='padding-top: 1em; font-style: initial;'>{$need}</td>
                    <td align='center'><img src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/{$row['img']}' alt='{$key}' /><br />{$key}</td>";
        if($need == "Y"){
            $html .= "<td align='center'>{$education}</td>
                    <td align='center' style='font-size: 0.8em;'>{$programs}</td>
                    <td style='font-size: 0.9em;'>{$community}</td>";
        }
        else{
            $html .= "<td colspan='3'>{$row['no']}</td>";
        }
        $html .= "</tr>";
        return $html;
    }
    
    function generateReport(){
        global $wgServer, $wgScriptPath, $config;
        $dir = dirname(__FILE__) . '/';
        require_once($dir . '/../../../../../Classes/SmartDomDocument/SmartDomDocument.php');
        $me = Person::newFromWgUser();
        $api = new UserFrailtyIndexAPI();
        $scores = $api->getFrailtyScore($me->getId());

        $margins = array('top'     => 1,
                         'right'   => 1,
                         'bottom'  => 1,
                         'left'    => 1);

        $pdfNoDisplay = (!isset($_GET['preview'])) ? ".pdfnodisplay { display:none }" : "";
        $bodyMargins = (!isset($_GET['preview'])) ? "margin-top: {$margins['top']}cm;
                                                     margin-right: {$margins['right']}cm;
                                                     margin-bottom: {$margins['bottom']}cm;
                                                     margin-left: {$margins['left']}cm;" : "margin: 0;";
        $bodyPadding = (!isset($_GET['preview'])) ? "" : "padding-top: {$margins['top']}cm;
                                                          padding-right: {$margins['right']}cm;
                                                          padding-bottom: {$margins['bottom']}cm;
                                                          padding-left: {$margins['left']}cm;";
        
        $html = "<html>
                    <head>
                        <script language='javascript' type='text/javascript' src='{$wgServer}{$wgScriptPath}/scripts/jquery.min.js?version=3.4.1'></script>
                        <link href='https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&family=Nunito+Sans:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600;1,700;1,800&display=swap' rel='stylesheet'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0' />
                        <style>
                            @page {
                                margin-top: 0cm;
                                margin-right: 0cm;
                                margin-bottom: 0cm;
                                margin-left: 0cm;
                            }
                            
                            html {
                                width: 216mm;
                                position: relative;
                                overflow-x: hidden;
                            }
                            
                            body {
                                {$bodyMargins}
                                font-family: 'Nunito Sans';
                                font-weight: 600;
                                line-height: 1em;
                            }
                            
                            div.body {
                                transform-origin: top left;
                                {$bodyPadding}
                            }
                            
                            small {
                                font-size: 0.8em;
                                line-height: 1em;
                            }
                            
                            .logos {
                                white-space: nowrap;
                                width: 100%;
                                text-align: center;
                            }
                            
                            .logos img {
                                max-height: 65px;
                                margin-left: 3%;
                                margin-right: 3%;
                                vertical-align: middle;
                            }
                            
                            $pdfNoDisplay
                            
                            .title-box {
                                text-align: center;
                                color: #06619b;
                                margin-top: 5em;
                            }
                            
                            .title {
                                font-weight: 700;
                                font-size: 1.25em;
                                line-height: 1em;
                                color: #06619b;
                            }
                            
                            .container {
                                margin-left: 4em;
                                margin-right: 1em;
                                margin-top: 4em;
                            }
                            
                            table.summary {
                                page-break-inside: avoid;
                                margin-top: 1em;
                                margin-bottom: 1em;
                                border-spacing: 0;
                                border-collapse: separate;
                            }
                            
                            table.summary td, table.summary th {
                                padding: 8px;
                                border-bottom: 1px solid white;
                            }
                            
                            table.summary thead th {
                                border-bottom: 2px solid white;
                            }
                            
                            table.summary tr th:nth-child(1),
                            table.summary tr td:nth-child(1) {
                                background: #3bb095;
                            }
                            
                            table.summary tr th:nth-child(2),
                            table.summary tr td:nth-child(2) {
                                background: #2cace3;
                            }
                            
                            table.summary tr th:nth-child(3),
                            table.summary tr td:nth-child(3) {
                                background: #f79233;
                            }
                            
                            table.summary tr th:nth-child(4),
                            table.summary tr td:nth-child(4) {
                                background: #c6db56;
                            }
                            
                            a, a:visited {
                                color: #005f9d;
                                text-decoration: none;
                            }
                            
                            a:hover, a:focus {
                                color: #e97936;
                            }
                            
                            .cb {
                                display: inline-block;
                                vertical-align: top;
                                padding: 0;
                                margin: 0;
                                line-height: 1em;
                            }
                            
                        </style>
                    </head>
                    <body>
                        <div class='body'>
                        <img src='{$wgServer}{$wgScriptPath}/skins/bg_top.png' style='z-index: -2; position: absolute; top:0; left: 0; right:0; width: 216mm;' />
                        <div class='logos'>
                            <img src='{$wgServer}{$wgScriptPath}/skins/logo3.png' />
                            <img style='max-height: 100px;' src='{$wgServer}{$wgScriptPath}/skins/logo2.png' />
                            <img src='{$wgServer}{$wgScriptPath}/skins/logo1.png' />
                        </div>
                        <div class='title-box'>
                            <div class='title'>
                                Your AVOID Frailty Progress Report
                            </div>
                        </div>
                        <br />
                        <br />
                        <div class='container'>
                            <div class='category'>
                                <div class='title' style='text-decoration: underline;'>Activity <img style='margin-left: 0.25em; height: 1.25em; vertical-align: middle;' src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/Activity.png' /></div>
                                {$this->drawChart('Sitting during the day', 
                                                  array('Some of the day', 'Most of the day', 'All day'), 
                                                  array($this->getBlobData('behaviouralassess', 'behave1_avoid', YEAR, 'RP_AVOID'), 
                                                        $this->getBlobData('behaviouralassess', 'behave1_avoid', YEAR, 'RP_AVOID_THREEMO'), 
                                                        $this->getBlobData('behaviouralassess', 'behave1_avoid', YEAR, 'RP_AVOID_SIXMO')))}
                                {$this->drawChart('Walking at last 10 minutes at a time', 
                                                  array('Most days (5-7 days)', 'Some days(2-4 days)', 'Rarely or not at all'), 
                                                  array($this->getBlobData('behaviouralassess', 'behave0_avoid', YEAR, 'RP_AVOID'), 
                                                        $this->getBlobData('behaviouralassess', 'behave0_avoid', YEAR, 'RP_AVOID_THREEMO'), 
                                                        $this->getBlobData('behaviouralassess', 'behave0_avoid', YEAR, 'RP_AVOID_SIXMO')))}
                                {$this->drawChart('Moderate physical activity', 
                                                  array('Most days (5-7 days)', 'Some days(2-4 days)', 'Rarely or not at all'), 
                                                  array($this->getBlobData('behaviouralassess', 'behave2_avoid', YEAR, 'RP_AVOID'), 
                                                        $this->getBlobData('behaviouralassess', 'behave2_avoid', YEAR, 'RP_AVOID_THREEMO'), 
                                                        $this->getBlobData('behaviouralassess', 'behave2_avoid', YEAR, 'RP_AVOID_SIXMO')))}
                                <table class='summary'>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>BASELINE</th>
                                            <th>3 MONTHS</th>
                                            <th>6 MONTHS</th>
                                        </tr>
                                    </thead>
                                    <tr>
                                        <th>MY BARRIERS</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>MY SUPPORTS</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class='category'>
                                <div class='title' style='text-decoration: underline;'>Vaccinate <img style='margin-left: 0.25em; height: 1.25em; vertical-align: middle;' src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/Vaccination.png' /></div>
                                <table class='summary'>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>BASELINE</th>
                                            <th>3 MONTHS</th>
                                            <th>6 MONTHS</th>
                                        </tr>
                                    </thead>
                                    <tr>
                                        <th>MY BARRIERS</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>MY SUPPORTS</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class='category'>
                                <div class='title' style='text-decoration: underline;'>Optimize Medications <img style='margin-left: 0.25em; height: 1.25em; vertical-align: middle;' src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/OptimizeMedication.png' /></div>
                                <table class='summary'>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>BASELINE</th>
                                            <th>3 MONTHS</th>
                                            <th>6 MONTHS</th>
                                        </tr>
                                    </thead>
                                    <tr>
                                        <th>MY BARRIERS</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>MY SUPPORTS</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class='category'>
                                <div class='title' style='text-decoration: underline;'>Interact <img style='margin-left: 0.25em; height: 1.25em; vertical-align: middle;' src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/Interact.png' /></div>
                                {$this->drawChart('Lack Companionship', 
                                                  array('Hardly ever', 'Some of the time', 'Often'),
                                                  array($this->getBlobData('behaviouralassess', 'interact7_avoid', YEAR, 'RP_AVOID'), 
                                                        $this->getBlobData('behaviouralassess', 'interact7_avoid', YEAR, 'RP_AVOID_THREEMO'), 
                                                        $this->getBlobData('behaviouralassess', 'interact7_avoid', YEAR, 'RP_AVOID_SIXMO')))}
                                {$this->drawChart('Feeling left out', 
                                                  array('Hardly ever', 'Some of the time', 'Often'),
                                                  array($this->getBlobData('behaviouralassess', 'interact8_avoid', YEAR, 'RP_AVOID'), 
                                                        $this->getBlobData('behaviouralassess', 'interact8_avoid', YEAR, 'RP_AVOID_THREEMO'), 
                                                        $this->getBlobData('behaviouralassess', 'interact8_avoid', YEAR, 'RP_AVOID_SIXMO')))}
                                {$this->drawChart('Isolated from others', 
                                                  array('Hardly ever', 'Some of the time', 'Often'),
                                                  array($this->getBlobData('behaviouralassess', 'interact9_avoid', YEAR, 'RP_AVOID'), 
                                                        $this->getBlobData('behaviouralassess', 'interact9_avoid', YEAR, 'RP_AVOID_THREEMO'), 
                                                        $this->getBlobData('behaviouralassess', 'interact9_avoid', YEAR, 'RP_AVOID_SIXMO')))}
                                <table class='summary'>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>BASELINE</th>
                                            <th>3 MONTHS</th>
                                            <th>6 MONTHS</th>
                                        </tr>
                                    </thead>
                                    <tr>
                                        <th>MY BARRIERS</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>MY SUPPORTS</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class='category'>
                                <div class='title' style='text-decoration: underline;'>Diet & Nutrition <img style='margin-left: 0.25em; height: 1.25em; vertical-align: middle;' src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/DietAndNutrition.png' /></div>
                                <table class='summary'>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>BASELINE</th>
                                            <th>3 MONTHS</th>
                                            <th>6 MONTHS</th>
                                        </tr>
                                    </thead>
                                    <tr>
                                        <th>MY BARRIERS</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>MY SUPPORTS</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <br />
                        <div style='width:100%; text-align:center;'><a href='https://HealthyAgingCentres.ca' target='_blank'>HealthyAgingCentres.ca</a></div>
                        <br /><br /><br /><br /><br />
                        <img src='{$wgServer}{$wgScriptPath}/skins/bg_bottom.png' style='z-index: -2; position: absolute; bottom:0; left: 0; right:0; width: 216mm;' />
                        <script type='text/javascript'>
                            var initialWidth = $(window).width();
                            
                            $(window).resize(function(){
                                $('html').width('100%');
                                var desiredWidth = $(window).width();
                                $('html').width('216mm');
                                var scaleFactor = desiredWidth/initialWidth;
                                $('div.body').css('transform', 'scale(' + scaleFactor + ')');
                                $('div.stickyContainer').css('top', 643*scaleFactor);
                                $('table.sticky').css('transform', 'scale(' + scaleFactor + ')')
                                                 .css('margin-left', scaleFactor - 1 + 'cm');
                                $('body').height($('div.body').outerHeight()*scaleFactor);
                            }).resize();
                        </script>
                        </div>
                    </body>
                </html>";
        
        if(!isset($_GET['preview'])){
            $dom = new SmartDomDocument();
            $dom->loadHTML($html);
            $as = $dom->getElementsByTagName("a");
            for($i=0; $i<$as->length; $i++){
                $a = $as->item($i);
                if($a->getAttribute('class') != 'anchor' && 
                   $a->getAttribute('class') != 'mce-item-anchor' &&
                   $a->getAttribute('class') != 'externalLink' && 
                   $a->textContent != ""){
                    $i--;
                    DOMRemove($a);
                }
            }
            $html = "$dom";
        }
        
        return $html;
    }
    
    function drawChart($title, $labels, $values){
        global $wgServer, $wgScriptPath;
        $height = 8;
        
        $i1 = array_search($values[0], $labels);
        $i2 = array_search($values[1], $labels);
        $i3 = array_search($values[2], $labels);
        
        $v1 = (1-$i1/4)*$height;
        $v2 = (1-$i2/4)*$height;
        $v3 = (1-$i3/4)*$height;
        
        $c1 = ($i1 == 2) ? "#ff1616" : (($i1 == 1) ? "#f79233" : "#008037");
        $c2 = ($i2 == 2) ? "#ff1616" : (($i2 == 1) ? "#f79233" : "#008037");
        $c3 = ($i3 == 2) ? "#ff1616" : (($i3 == 1) ? "#f79233" : "#008037");
        
        $html = "<table style='width:100%; margin-top: 0.5em;'>
                    <tr><td style='width:50%;'>
                         <table style='page-break-inside: avoid; border-spacing: 0; border-collapse: separate; width: 100%;'>
                            <tr>
                                <th align='left' style='font-weight: 800;color: #06619b;'>Baseline</th>
                                <th style='font-weight: 800;color: #06619b;'>3 months</th>
                                <th align='right' style='font-weight: 800;color: #06619b;'>6 months</th>
                            </tr>
                            <tr>
                                <td colspan='3'>{$title}</td>
                            </tr>
                            <tr style='height: {$height}em; image-rendering: pixelated; background: url({$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/chartbg.png); background-size: {$height}em 100%;'>
                                <td valign='bottom' style='height: {$height}em;'><div style='margin-right:30%; height: {$v1}em; background: $c1; border-radius:100em 100em 0 0;'></div></td>
                                <td valign='bottom' style='height: {$height}em;'><div style='margin-left:15%; margin-right:15%; height: {$v2}em; background: $c2; border-radius:100em 100em 0 0;'></div></td>
                                <td valign='bottom' style='height: {$height}em;'><div style='margin-left:30%; height: {$v3}em; background: $c3; border-radius:100em 100em 0 0;'></div></td>
                            </tr>
                         </table>
                     </td>
                     <td style='width:50%; padding-top:4em; padding-left: 1em;' valign='center'>
                        <span style='color:#008037;'>{$labels[0]}</span><br /><br />
                        <span style='color:#f79233;'>{$labels[1]}</span><br /><br />
                        <span style='color:#ff1616;'>{$labels[2]}</span>
                     </td></tr>
                 </table>";
        return $html;
    }
    
    function getBlobData($blobSection, $blobItem, $year, $rpType){
        $me = Person::newFromWgUser();
        $blb = new ReportBlob(BLOB_TEXT, $year, $me->getId(), 0);
        $addr = ReportBlob::create_address($rpType, $blobSection, $blobItem, 0);
        $result = $blb->load($addr);
        return $blb->getData();
    }
    
    function execute($par){
        global $wgServer, $wgScriptPath, $dompdfOptions;
        $dir = dirname(__FILE__);
        require_once($dir . '/../../../../../config/dompdf_config.inc.php');
        $html = $this->generateReport();
        if(isset($_GET['preview'])){
            echo $html;
            exit;
        }
        $dompdfOptions->setFontHeightRatio(1.0);
        $dompdfOptions->setDpi(96);
        $dompdf = new Dompdf\Dompdf($dompdfOptions);
        $dompdf->setPaper('letter', 'portrait');
        
        $dompdf->load_html($html);
        $dompdf->render();
        header("Content-Type: application/pdf");
        echo $dompdf->output();
        exit;
        
    }
    
}

?>
