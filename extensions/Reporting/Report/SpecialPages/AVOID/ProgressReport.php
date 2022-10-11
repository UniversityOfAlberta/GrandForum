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
    
    var $submit = array();
    
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
        
        $this->submit = array($this->getBlobData('SUBMIT', 'SUBMITTED', YEAR, 'RP_AVOID', BLOB_TEXT),
                              $this->getBlobData('SUBMIT', 'SUBMITTED', YEAR, 'RP_AVOID_THREEMO', BLOB_TEXT),
                              $this->getBlobData('SUBMIT', 'SUBMITTED', YEAR, 'RP_AVOID_SIXMO', BLOB_TEXT));
        
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
                            
                            table.summary td a {
                                color: #00407a;
                            }
                            
                            table.summary td a:hover {
                                color: black;
                            }
                            
                            th {
                                white-space: nowrap;
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
                                {$this->drawTable('active_specify_end')}
                            </div>
                            
                            <div class='category'>
                                <div class='title' style='text-decoration: underline;'>Vaccinate <img style='margin-left: 0.25em; height: 1.25em; vertical-align: middle;' src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/Vaccination.png' /></div>
                                {$this->vaccineTable()}
                                {$this->drawTable('vax_end')}
                            </div>
                            
                            <div class='category'>
                                <div class='title' style='text-decoration: underline;'>Optimize Medications <img style='margin-left: 0.25em; height: 1.25em; vertical-align: middle;' src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/OptimizeMedication.png' /></div>
                                {$this->medicationsTable()}
                                {$this->drawTable('meds_end')}
                            </div>
                            
                            <div class='category'>
                                <div class='title' style='text-decoration: underline;'>Interact <img style='margin-left: 0.25em; height: 1.25em; vertical-align: middle;' src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/Interact.png' /></div>
                                {$this->interactTable()}
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
                                {$this->drawTable('interact_end')}
                            </div>
                            
                            <div class='category'>
                                <div class='title' style='text-decoration: underline;'>Diet & Nutrition <img style='margin-left: 0.25em; height: 1.25em; vertical-align: middle;' src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/DietAndNutrition.png' /></div>
                                {$this->dietTable()}
                                {$this->drawTable('diet_end')}
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
        
        $width = "75%";
        $colspan = "2";
        if($this->submit[2] == "Submitted"){
            $width = "100%";
            $colspan = "2";
        }
        
        $html = "<table style='width:{$width}; margin-top: 0.5em; margin-bottom: 1.5em; border-spacing: 0; border-collapse: separate;'>
                    <tr><td style='width:50%; padding: 0;'>
                         <table style='page-break-inside: avoid; border-spacing: 0; border-collapse: separate; width: 100%;'>
                            <tr>
                                <th align='left' style='font-weight: 800;color: #06619b;'>Initial</th>";
        if($this->submit[2] == "Submitted"){
            $html .= "          <th style='font-weight: 800;color: #06619b;'>3 months</th>
                                <th align='right' style='font-weight: 800;color: #06619b;'>6 months</th>";
        }
        else{
            $html .= "          <th align='right' style='font-weight: 800;color: #06619b;'>3 months</th>";
        }
        $html .= "          </tr>
                            <tr>
                                <td colspan='{$colspan}'>{$title}</td>
                            </tr>
                            <tr style='height: {$height}em; image-rendering: pixelated; background: url({$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/chartbg.png); background-size: {$height}em 100%;'>
                                <td valign='bottom' style='width:33.333%; height: {$height}em;'><div style='margin-right:30%; height: {$v1}em; background: $c1; border-radius:100em 100em 0 0;'></div></td>";
        if($this->submit[2] == "Submitted"){
            $html .= "          <td valign='bottom' style='width:33.333%; height: {$height}em;'><div style='margin-left:15%; margin-right:15%; height: {$v2}em; background: $c2; border-radius:100em 100em 0 0;'></div></td>
                                <td valign='bottom' style='width:33.333%; height: {$height}em;'><div style='margin-left:30%; height: {$v3}em; background: $c3; border-radius:100em 100em 0 0;'></div></td>";
        }
        else{
            $html .= "          <td valign='bottom' style='width:33.333%; height: {$height}em;'><div style='margin-left:30%; height: {$v2}em; background: $c2; border-radius:100em 100em 0 0;'></div></td>";
        }
        $html .= "           </tr>
                         </table>
                     </td>
                     <td style='width:50%; padding: 0; padding-top:4em; padding-left: 1em;' valign='center'>
                        <span style='color:#008037;'>{$labels[0]}</span><br /><br />
                        <span style='color:#f79233;'>{$labels[1]}</span><br /><br />
                        <span style='color:#ff1616;'>{$labels[2]}</span>
                     </td></tr>
                 </table>";
        return $html;
    }
    
    function drawTable($barrierItem){
        $specify = "";
        switch($barrierItem){
            case "active_specify_end":
                $specify = "ACTIVESPECIFY";
                break;
            case "vax_end":
                $specify = "VAXENDTEXTSPECIFY";
                break;
            case "meds_end":
                $specify = "MEDSENDTEXTSPECIFY";
                break;
            case "interact_end":
                $specify = "INTERACTENDTEXTSPECIFY";
                break;
            case "diet_end":
                $specify = "DIETENDTEXTSPECIFY";
                break;
        }
        
        $barriers = array(@str_replace("/", " / ", $this->getBlobData('behaviouralassess', $barrierItem, YEAR, 'RP_AVOID', BLOB_ARRAY)[$barrierItem]),
                          @str_replace("/", " / ", $this->getBlobData('behaviouralassess', $barrierItem, YEAR, 'RP_AVOID_THREEMO', BLOB_ARRAY)[$barrierItem]),
                          @str_replace("/", " / ", $this->getBlobData('behaviouralassess', $barrierItem, YEAR, 'RP_AVOID_SIXMO', BLOB_ARRAY)[$barrierItem]));
        
        $specify = array($this->getBlobData('behaviouralassess', $specify, YEAR, 'RP_AVOID', BLOB_TEXT),
                         $this->getBlobData('behaviouralassess', $specify, YEAR, 'RP_AVOID_THREEMO', BLOB_TEXT),
                         $this->getBlobData('behaviouralassess', $specify, YEAR, 'RP_AVOID_SIXMO', BLOB_TEXT));
        
        $html = "<table class='summary' style='margin-bottom: 1.5em;'>
                    <thead>
                        <tr>
                            <th></th>
                            <th style='width: 8em; max-width: 8em;'>INITIAL</th>
                            <th style='width: 8em; max-width: 8em;'>3 MONTHS</th>";
        if($this->submit[2] == "Submitted"){
            $html .= "      <th style='width: 8em; max-width: 8em;'>6 MONTHS</th>";
        }
        $html .= "      </tr>
                    </thead>
                    <tr>
                        <th>MY BARRIERS</th>";
        foreach($barriers as $key => $barrier){
            if($this->submit[$key] != "Submitted"){ continue; }
            if(is_array($barrier) && count($barrier) > 0 && $this->submit[$key] == "Submitted"){
                if(trim($specify[$key]) != ""){
                    $barrier = str_replace("Other", $specify[$key], $barrier);
                }
                $html .= "<td valign='top' style='font-size: 0.7em; line-height: 1.1em; width: 8em; max-width: 8em;'>".implode("<div style='margin-bottom:0.5em;'></div>", $barrier)."</td>";
            } else {
                $html .= "<td rowspan='2'>You're meeting recommendations, keep up the great work!</td>";
            }
        }
        $html .= "  </tr>
                    <tr>
                        <th>MY SUPPORTS</th>";
        foreach($barriers as $key => $barrier){
            if(is_array($barrier) && count($barrier) > 0 && $this->submit[$key] == "Submitted"){
                $html .= "<td style='font-size: 0.7em; line-height: 1.1em; width: 8em; max-width: 8em;'>{$this->recommendations($barrier)}</td>";
            }
            else{
                $html .= "<td style='display:none;'></td>";
            }
        }
        $html .= "  </tr>
                </table>";
        return $html;
    }
    
    function vaccineTable(){
        $values = array(
            'vaccinate2_avoid' => "Flu Vaccine",
            'vaccinate3_avoid' => "Shingles Vaccine",
            'vaccinate4_avoid' => "Pneumonia Vaccine",
            'vaccinate5_avoid' => "Booster Vaccines",
            'vaccinate6_avoid' => "COVID-19 Vaccine",
            'vaccinate7_avoid' => "COVID-19 Booster"
        );
        
        $initial = array();
        $threeMonth = array();
        $sixMonth = array();
        
        foreach($values as $key => $value){
            $initial[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID') == "No") ? $value : "";
            $threeMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_THREEMO') == "No") ? $value : "";
            $sixMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_SIXMO') == "No") ? $value : "";
        }
        
        $html = "<p style='margin-bottom:0;'>What vaccines (if any) am I missing?</p>
                 <table style='page-break-inside: avoid; margin-bottom: 1.5em; border-spacing: 0; border-collapse: separate; width: 50%;'>
                    <tr>
                        <th align='left' style='font-weight: 800;color: #06619b;'>Initial</th>
                        <th align='left' style='font-weight: 800;color: #06619b;'>3 months</th>";
        if($this->submit[2] == "Submitted"){
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>6 months</th>";
        }
        $html .= "  </tr>
                    <tr>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>".implode("<br />", array_filter($initial))."</td>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>".implode("<br />", array_filter($threeMonth))."</td>";
        if($this->submit[2] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>".implode("<br />", array_filter($sixMonth))."</td>";
        }
        $html .= "  </tr>
                </table>";
        return $html;
    }
    
    function medicationsTable(){
        $values = array(
            'meds3_avoid' => "No"
        );
        
        $initial = array();
        $threeMonth = array();
        $sixMonth = array();
        
        foreach($values as $key => $value){
            $initial[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID') == "No") ? $value : "Yes";
            $threeMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_THREEMO') == "No") ? $value : "Yes";
            $sixMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_SIXMO') == "No") ? $value : "Yes";
        }
        
        $html = "<p style='margin-bottom:0;'>Have you had your medications (including prescriptions, over the counter, and supplements) reviewed by a pharmacist or healthcare provider in the last year?</p>
                 <table style='page-break-inside: avoid; margin-bottom: 1.5em; border-spacing: 0; border-collapse: separate; width: 50%;'>
                    <tr>
                        <th align='left' style='font-weight: 800;color: #06619b;'>Initial</th>
                        <th align='left' style='font-weight: 800;color: #06619b;'>3 months</th>";
        if($this->submit[2] == "Submitted"){ 
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>6 months</th>";
        }
        $html .= "  </tr>
                    <tr>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>".implode("<br />", array_filter($initial))."</td>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>".implode("<br />", array_filter($threeMonth))."</td>";
        if($this->submit[2] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>".implode("<br />", array_filter($sixMonth))."</td>";
        }
        $html .= "  </tr>
                </table>";
        return $html;
    }
    
    function interactTable(){
        $values = array(
            'interact1_avoid',
            'interact2_avoid',
            'interact3_avoid',
            'interact4_avoid',
            'interact5_avoid',
            'interact6_avoid'
        );
        
        $initial = 0;
        $threeMonth = 0;
        $sixMonth = 0;
        
        foreach($values as $value){
            $initial += UserFrailtyIndexAPI::interactScore($this->getBlobData('behaviouralassess', $value, YEAR, 'RP_AVOID'));
            $threeMonth += UserFrailtyIndexAPI::interactScore($this->getBlobData('behaviouralassess', $value, YEAR, 'RP_AVOID'));
            $sixMonth += UserFrailtyIndexAPI::interactScore($this->getBlobData('behaviouralassess', $value, YEAR, 'RP_AVOID'));
        }
        
        $initial = ($initial >= 12) ? "No" : "Yes";
        $threeMonth = ($threeMonth >= 12) ? "No" : "Yes";
        $sixMonth = ($sixMonth >= 12) ? "No" : "Yes";
        
        $html = "<p style='margin-bottom:0;'>Having friends and/or family with whom you can talk to, feel at ease with and call on for help is important for your overall health.<br />
                    <br />
                    Do you have a risk in this area:
                 </p>
                 <table style='page-break-inside: avoid; margin-bottom: 1.5em; border-spacing: 0; border-collapse: separate; width: 50%;'>
                    <tr>
                        <th align='left' style='font-weight: 800;color: #06619b;'>Initial</th>
                        <th align='left' style='font-weight: 800;color: #06619b;'>3 months</th>";
        if($this->submit[2] == "Submitted"){
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>6 months</th>";
        }
        $html .= "  </tr>
                    <tr>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>{$initial}</td>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>{$threeMonth}</td>";
        if($this->submit[2] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>{$sixMonth}</td>";
        }
        $html .= "  </tr>
                </table>";
        return $html;
    }
    
    function dietTable(){
        $values = array(
            'diet1_avoid' => "Protein",
            'diet2_avoid' => "Fruits & Vegetables",
            'diet3_avoid' => "High Calcium",
            'diet4_avoid' => "Vitamin D"
        );
        
        $initial = array();
        $threeMonth = array();
        $sixMonth = array();
        
        foreach($values as $key => $value){
            $initial[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID') == "No") ? $value : "";
            $threeMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_THREEMO') == "No") ? $value : "";
            $sixMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_SIXMO') == "No") ? $value : "";
        }
        
        $html = "<p style='margin-bottom:0;'>Diet deficiencies</p>
                 <table style='page-break-inside: avoid; margin-bottom: 1.5em; border-spacing: 0; border-collapse: separate; width: 50%;'>
                    <tr>
                        <th align='left' style='font-weight: 800;color: #06619b;'>Initial</th>
                        <th align='left' style='font-weight: 800;color: #06619b;'>3 months</th>";
        if($this->submit[2] == "Submitted"){
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>6 months</th>";
        }
        $html .= "  </tr>
                    <tr>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>".implode("<br />", array_filter($initial))."</td>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>".implode("<br />", array_filter($threeMonth))."</td>";
        if($this->submit[2] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>".implode("<br />", array_filter($sixMonth))."</td>";
        }
        $html .= "  </tr>
                </table>";
        return $html;
    }
    
    function recommendations($barriers){
        global $wgServer, $wgScriptPath;
        $recommendations = array();
        foreach($barriers as $barrier){
            switch($barrier){
                // Activity
                case "I am physically and / or mentally unable to be active":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/Otago' target='_blank'>Otago</a>";
                    break;
                case "I don't know where / how to get help in my community":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-ACT' target='_blank'>Activity Programs</a>";
                    break;
                case "I have trouble maintaining a routine when it comes to activity":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/Activity' target='_blank'>Activity Module</a>";
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/IngredientsForChange' target='_blank'>Ingredients for Change Module</a>";
                    break;
                
                // Vaccinate
                case "I was not aware of the recommended vaccines":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/Vaccination' target='_blank'>Vaccination Module</a>";
                    break;
                case "I don't know where or how to get vaccinated":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-VAC' target='_blank'>Vaccination Programs</a>";
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    break;
                case "I don't see the point of getting vaccinated":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/Vaccination' target='_blank'>Vaccination Module</a>";
                    break;
                
                // Optimize Medication
                case "I was not aware that this is recommended":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/OptimizeMedication' target='_blank'>Optimize Medication Module</a>";
                    break;
                case "I do not feel comfortable and / or prepared to have this conversation with a healthcare provider":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/EducationModules/OptimizeMedication/Resources/Medication%20Tracker%20Sheet%20Planner.pdf' target='_blank'>Medication Tracker</a>";
                    break;
                case "I do not know who to talk to about this":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-VAC' target='_blank'>Optimize Medication Programs</a>";
                    break;
                case "I have had my medication reviewed in the past, but find it hard to remember each year":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    break;
                case "I do not understand why this is important":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/OptimizeMedication' target='_blank'>Optimize Medication Module</a>";
                    break;
                    
                // Interact
                case "I have been restricted due to COVID-19 public health measures":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/CyberSeniors' target='_blank'>Cyber Seniors</a>";
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    break;
                case "I find it physically / mentally difficult to participate in social interactions":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    break;
                case "I am not aware of opportunities for social interaction in my community":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-INT' target='_blank'>Interact Programs</a>";
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/CommunityConnectors' target='_blank'>Community Connectors</a>";
                    break;
                case "I have trouble maintaining social connections over time":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-INT' target='_blank'>Interact Programs</a>";
                    break;
                case "I do not feel that I need more interaction than I already have":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/Interact' target='_blank'>Interact Module</a>";
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/EducationModules/Interact/Resources/Interact%20Vid.mp4' target='_blank'>Interact Video</a>";
                    break;
                    
                // Diet & Nutrition
                case "I find it physically / mentally difficult to do this":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-DIET-DIET' target='_blank'>Dietitian Services</a>";
                    break;
                case "I was not aware of one or more of the recommendations in the questions above":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/DietAndNutrition' target='_blank'>Diet & Nutrition Module</a>";
                    break;
                case "It's difficult for me to access nutritious and / or culturally appropriate food because of where I live":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-DIET' target='_blank'>Diet & Nutrition Programs</a>";
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-TRANSPORT-DRIVP' target='_blank'>Driving Programs</a>";
                    break;
                case "I can't afford the type of food that I would like to eat":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-DIET-FOODBANKS' target='_blank'>Food Banks and Stands</a>";
                    break;
                case "I have trouble maintaining a healthy eating routine":
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-DIET-DIET' target='_blank'>Dietitian Services</a>";
                    break;
            }
        }
        return implode("<div style='margin-bottom:0.5em;'></div>", array_unique($recommendations));
    }
    
    function getBlobData($blobSection, $blobItem, $year, $rpType, $blobType=BLOB_TEXT){
        $me = Person::newFromWgUser();
        $blb = new ReportBlob($blobType, $year, $me->getId(), 0);
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
