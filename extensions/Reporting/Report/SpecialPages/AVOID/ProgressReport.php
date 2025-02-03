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
    
    function generateReport(){
        global $wgServer, $wgScriptPath, $config, $wgLang;
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
                              $this->getBlobData('SUBMIT', 'SUBMITTED', YEAR, 'RP_AVOID_SIXMO', BLOB_TEXT),
                              $this->getBlobData('SUBMIT', 'SUBMITTED', YEAR, 'RP_AVOID_NINEMO', BLOB_TEXT),
                              $this->getBlobData('SUBMIT', 'SUBMITTED', YEAR, 'RP_AVOID_TWELVEMO', BLOB_TEXT));
        
        $logosImg = ($config->getValue('networkFullName') == "AVOID Australia") ? 
            ".logos img {
                max-height: 54px;
                margin-left: 1%;
                margin-right: 1%;
                vertical-align: middle;
            }" : 
            ".logos img {
                max-height: 65px;
                margin-left: 3%;
                margin-right: 3%;
                vertical-align: middle;
            }";
        
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
                            
                            $logosImg
                            
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
                                max-width: 100%;
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
                            
                            table.summary tr th:nth-child(5),
                            table.summary tr td:nth-child(5) {
                                background: #f79233;
                            }
                            
                            table.summary tr th:nth-child(6),
                            table.summary tr td:nth-child(6) {
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
                            }";
                            
                    if($wgLang->getCode() == "en"){
	                    $html .= "fr, .fr { display: none !important; }";
	                }
	                else if($wgLang->getCode() == "fr"){
	                    $html .= "en, .en { display: none !important; }";
	                }
                    $html .= "     
                        </style>
                    </head>
                    <body>
                        <div class='body'>
                        <img src='{$wgServer}{$wgScriptPath}/skins/bg_top.png' style='z-index: -2; position: absolute; top:0; left: 0; right:0; width: 216mm;' />
                        <div class='logos'>
                            <img src='{$wgServer}{$wgScriptPath}/skins/logo4.png' />
                            <img src='{$wgServer}{$wgScriptPath}/skins/logo3.png' />
                            <img style='max-height: 100px;' src='{$wgServer}{$wgScriptPath}/skins/logo2.png' />
                            <en><img src='{$wgServer}{$wgScriptPath}/skins/logo1.png' /></en><fr><img src='{$wgServer}{$wgScriptPath}/skins/logo1fr.png' /></fr>
                        </div>
                        <div class='title-box'>
                            <div class='title'>
                                Your AVOID Frailty Progress Report
                            </div><br />
                            ";
                            if($this->submit[4] == "Submitted"){
                                $html .= "<div>Your 12 month progress is shown in your refreshed Frailty Report</div>";
                            }
                            $html .= "<div class='pdfnodisplay'>You can also print your progress report <a href='{$wgServer}{$wgScriptPath}/index.php/Special:ProgressReport' target='_blank'><b><u>here</u></b></a>.</div>
                        </div>
                        
                        <script type='text/php'>
                            \$php_code = '
                                if(\$PAGE_NUM == 1){
                                    \$note = \"{$me->getNameForForms()}\";
                                    \$font = \$fontMetrics->getFont(\"verdana\");
                                    \$size = 6;
                                    \$text_height = \$fontMetrics->getFontHeight(\$font, \$size);
                                    \$text_width = \$fontMetrics->getTextWidth(\"\$note\", \$font, \$size);
                                    \$color = array(0,0,0);
                                    \$w = \$pdf->get_width();
                                    \$h = \$pdf->get_height();
                                    \$y = \$h - \$text_height - 24;

                                    \$x = \$pdf->get_width() - \$text_width;

                                    \$pdf->text(\$x - 28, \$y+(\$text_height) - \$text_height + 4, \"\$note\", \$font, \$size, \$color);
                                }
                                ';
                             \$pdf->page_script(\$php_code);
                        </script>
                        
                        <br />
                        <div class='container'>
                            <div class='category'>
                                <div class='title' style='text-decoration: underline;'>Activity <img style='margin-left: 0.25em; height: 1.25em; vertical-align: middle;' src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/Activity.png' /></div>
                                {$this->drawChart('Sitting during the day', 
                                                  array('Some of the day', 'Most of the day', 'All day'), 
                                                  array($this->getBlobData('behaviouralassess', 'behave1_avoid', YEAR, 'RP_AVOID'), 
                                                        $this->getBlobData('behaviouralassess', 'behave1_avoid', YEAR, 'RP_AVOID_THREEMO'), 
                                                        $this->getBlobData('behaviouralassess', 'behave1_avoid', YEAR, 'RP_AVOID_SIXMO'),
                                                        $this->getBlobData('behaviouralassess', 'behave1_avoid', YEAR, 'RP_AVOID_NINEMO'),
                                                        $this->getBlobData('behaviouralassess', 'behave1_avoid', YEAR, 'RP_AVOID_TWELVEMO')))}
                                {$this->drawChart('Walking at least 10 minutes at a time', 
                                                  array('Most days (5-7 days)', 'Some days(2-4 days)', 'Rarely or not at all'), 
                                                  array($this->getBlobData('behaviouralassess', 'behave0_avoid', YEAR, 'RP_AVOID'), 
                                                        $this->getBlobData('behaviouralassess', 'behave0_avoid', YEAR, 'RP_AVOID_THREEMO'), 
                                                        $this->getBlobData('behaviouralassess', 'behave0_avoid', YEAR, 'RP_AVOID_SIXMO'),
                                                        $this->getBlobData('behaviouralassess', 'behave0_avoid', YEAR, 'RP_AVOID_NINEMO'),
                                                        $this->getBlobData('behaviouralassess', 'behave0_avoid', YEAR, 'RP_AVOID_TWELVEMO')))}
                                {$this->drawChart('Moderate physical activity', 
                                                  array('Most days (5-7 days)', 'Some days(2-4 days)', 'Rarely or not at all'), 
                                                  array($this->getBlobData('behaviouralassess', 'behave2_avoid', YEAR, 'RP_AVOID'), 
                                                        $this->getBlobData('behaviouralassess', 'behave2_avoid', YEAR, 'RP_AVOID_THREEMO'), 
                                                        $this->getBlobData('behaviouralassess', 'behave2_avoid', YEAR, 'RP_AVOID_SIXMO'),
                                                        $this->getBlobData('behaviouralassess', 'behave2_avoid', YEAR, 'RP_AVOID_NINEMO'),
                                                        $this->getBlobData('behaviouralassess', 'behave2_avoid', YEAR, 'RP_AVOID_TWELVEMO')))}
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
                                                        $this->getBlobData('behaviouralassess', 'interact7_avoid', YEAR, 'RP_AVOID_SIXMO'),
                                                        $this->getBlobData('behaviouralassess', 'interact7_avoid', YEAR, 'RP_AVOID_NINEMO'),
                                                        $this->getBlobData('behaviouralassess', 'interact7_avoid', YEAR, 'RP_AVOID_TWELVEMO')))}
                                {$this->drawChart('Feeling left out', 
                                                  array('Hardly ever', 'Some of the time', 'Often'),
                                                  array($this->getBlobData('behaviouralassess', 'interact8_avoid', YEAR, 'RP_AVOID'), 
                                                        $this->getBlobData('behaviouralassess', 'interact8_avoid', YEAR, 'RP_AVOID_THREEMO'), 
                                                        $this->getBlobData('behaviouralassess', 'interact8_avoid', YEAR, 'RP_AVOID_SIXMO'),
                                                        $this->getBlobData('behaviouralassess', 'interact8_avoid', YEAR, 'RP_AVOID_NINEMO'),
                                                        $this->getBlobData('behaviouralassess', 'interact8_avoid', YEAR, 'RP_AVOID_TWELVEMO')))}
                                {$this->drawChart('Isolated from others', 
                                                  array('Hardly ever', 'Some of the time', 'Often'),
                                                  array($this->getBlobData('behaviouralassess', 'interact9_avoid', YEAR, 'RP_AVOID'), 
                                                        $this->getBlobData('behaviouralassess', 'interact9_avoid', YEAR, 'RP_AVOID_THREEMO'), 
                                                        $this->getBlobData('behaviouralassess', 'interact9_avoid', YEAR, 'RP_AVOID_SIXMO'),
                                                        $this->getBlobData('behaviouralassess', 'interact9_avoid', YEAR, 'RP_AVOID_NINEMO'),
                                                        $this->getBlobData('behaviouralassess', 'interact9_avoid', YEAR, 'RP_AVOID_TWELVEMO')))}
                                {$this->drawTable('interact_end')}
                            </div>
                            
                            <div class='category'>
                                <div class='title' style='text-decoration: underline;'>Diet & Nutrition <img style='margin-left: 0.25em; height: 1.25em; vertical-align: middle;' src='{$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/DietAndNutrition.png' /></div>
                                {$this->dietTable()}
                                {$this->drawTable('diet_end')}
                            </div>
                        </div>
                        
                        <br />
                        <div style='width:100%; text-align:center;'>
                            <en><a href='http://avoidfrailty.ca' target='_blank'>avoidfrailty.ca</a></en>
                            <fr><a href='https://Proactifquebec.ca' target='_blank'>Proactifquebec.ca</a></fr>
                        </div>
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
        $i4 = array_search($values[3], $labels);
        $i5 = array_search($values[4], $labels);
        
        $v1 = ($i1 !== false) ? (1-$i1/4)*$height : 0;
        $v2 = ($i2 !== false) ? (1-$i2/4)*$height : 0;
        $v3 = ($i3 !== false) ? (1-$i3/4)*$height : 0;
        $v4 = ($i4 !== false) ? (1-$i4/4)*$height : 0;
        $v5 = ($i5 !== false) ? (1-$i5/4)*$height : 0;
        
        $c1 = ($i1 == 2) ? "#ff1616" : (($i1 == 1) ? "#f79233" : "#008037");
        $c2 = ($i2 == 2) ? "#ff1616" : (($i2 == 1) ? "#f79233" : "#008037");
        $c3 = ($i3 == 2) ? "#ff1616" : (($i3 == 1) ? "#f79233" : "#008037");
        $c4 = ($i4 == 2) ? "#ff1616" : (($i4 == 1) ? "#f79233" : "#008037");
        $c5 = ($i5 == 2) ? "#ff1616" : (($i5 == 1) ? "#f79233" : "#008037");
        
        $width = "75%";
        $colspan = "2";
        if($this->submit[2] == "Submitted"){
            $width = "100%";
            $colspan = "3";
        }
        if($this->submit[3] == "Submitted"){
            $width = "100%";
            $colspan = "4";
        }
        if($this->submit[4] == "Submitted"){
            $width = "100%";
            $colspan = "5";
        }
        
        $html = "<table style='width:{$width}; margin-top: 0.5em; margin-bottom: 1.5em; border-spacing: 0; border-collapse: separate;'>
                    <tr><td style='width:70%; padding: 0;'>
                         <table style='page-break-inside: avoid; border-spacing: 0; border-collapse: separate; width: 100%;'>
                            <tr>
                                <th align='center' style='font-weight: 800;color: #06619b;'>Initial</th>
                                <th align='center' style='font-weight: 800;color: #06619b;'>3 months</th>
                                ";
        if($this->submit[4] == "Submitted"){
            $html .= "          <th align='center' style='font-weight: 800;color: #06619b;'>6 months</th>
                                <th align='center' style='font-weight: 800;color: #06619b;'>9 months</th>
                                <th align='center' style='font-weight: 800;color: #06619b;'>12 months</th>";
        }
        else if($this->submit[3] == "Submitted"){
            $html .= "          <th align='center' style='font-weight: 800;color: #06619b;'>6 months</th>
                                <th align='center' style='font-weight: 800;color: #06619b;'>9 months</th>";
        }
        else if($this->submit[2] == "Submitted"){
            $html .= "          <th align='center' style='font-weight: 800;color: #06619b;'>6 months</th>";
        }
        
        $html .= "          </tr>
                            <tr>
                                <td colspan='{$colspan}'>{$title}</td>
                            </tr>
                            <tr style='height: {$height}em; image-rendering: pixelated; background: url({$wgServer}{$wgScriptPath}/extensions/Reporting/Report/SpecialPages/AVOID/images/chartbg.png); background-size: {$height}em 100%;'>
                                <td valign='bottom' style='width:20%; height: {$height}em;'><div style='width:85%;margin:auto; height: {$v1}em; background: $c1; border-radius:100em 100em 0 0;'></div></td>
                                <td valign='bottom' style='width:20%; height: {$height}em;'><div style='width:85%;margin:auto; height: {$v2}em; background: $c2; border-radius:100em 100em 0 0;'></div></td>";
        if($this->submit[4] == "Submitted"){
            $html .= "          <td valign='bottom' style='width:20%; height: {$height}em;'><div style='width:85%;margin:auto; height: {$v3}em; background: $c3; border-radius:100em 100em 0 0;'></div></td>
                                <td valign='bottom' style='width:20%; height: {$height}em;'><div style='width:85%;margin:auto; height: {$v4}em; background: $c4; border-radius:100em 100em 0 0;'></div></td>
                                <td valign='bottom' style='width:20%; height: {$height}em;'><div style='width:85%;margin:auto; height: {$v5}em; background: $c5; border-radius:100em 100em 0 0;'></div></td>";
        }
        else if($this->submit[3] == "Submitted"){
            $html .= "          <td valign='bottom' style='width:20%; height: {$height}em;'><div style='width:85%;margin:auto; height: {$v3}em; background: $c3; border-radius:100em 100em 0 0;'></div></td>
                                <td valign='bottom' style='width:20%; height: {$height}em;'><div style='width:85%;margin:auto; height: {$v4}em; background: $c4; border-radius:100em 100em 0 0;'></div></td>";
        }
        else if($this->submit[2] == "Submitted"){
            $html .= "          <td valign='bottom' style='width:20%; height: {$height}em;'><div style='width:85%;margin:auto; height: {$v3}em; background: $c3; border-radius:100em 100em 0 0;'></div></td>";
        }
        $html .= "           </tr>
                         </table>
                     </td>
                     <td style='width:30%; padding: 0; padding-top:4em; padding-left: 1em;' valign='center'>
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
                          @str_replace("/", " / ", $this->getBlobData('behaviouralassess', $barrierItem, YEAR, 'RP_AVOID_SIXMO', BLOB_ARRAY)[$barrierItem]),
                          @str_replace("/", " / ", $this->getBlobData('behaviouralassess', $barrierItem, YEAR, 'RP_AVOID_NINEMO', BLOB_ARRAY)[$barrierItem]),
                          @str_replace("/", " / ", $this->getBlobData('behaviouralassess', $barrierItem, YEAR, 'RP_AVOID_TWELVEMO', BLOB_ARRAY)[$barrierItem]));
        
        $specify = array($this->getBlobData('behaviouralassess', $specify, YEAR, 'RP_AVOID', BLOB_TEXT),
                         $this->getBlobData('behaviouralassess', $specify, YEAR, 'RP_AVOID_THREEMO', BLOB_TEXT),
                         $this->getBlobData('behaviouralassess', $specify, YEAR, 'RP_AVOID_SIXMO', BLOB_TEXT),
                         $this->getBlobData('behaviouralassess', $specify, YEAR, 'RP_AVOID_NINEMO', BLOB_TEXT),
                         $this->getBlobData('behaviouralassess', $specify, YEAR, 'RP_AVOID_TWELVEMO', BLOB_TEXT));
        
        $html = "<table class='summary' style='margin-bottom: 1.5em;'>
                    <thead>
                        <tr>
                            <th></th>
                            <th style='width: 6em; max-width: 6em;'>INITIAL</th>
                            <th style='width: 6em; max-width: 6em;'>3 MONTHS</th>";
        if($this->submit[2] == "Submitted"){
            $html .= "      <th style='width: 6em; max-width: 6em;'>6 MONTHS</th>";
        }
        if($this->submit[3] == "Submitted"){
            $html .= "      <th style='width: 6em; max-width: 6em;'>9 MONTHS</th>";
        }
        if($this->submit[4] == "Submitted"){
            $html .= "      <th style='width: 6em; max-width: 6em;'>12 MONTHS</th>";
        }
        $html .= "      </tr>
                    </thead>
                    <tr>
                        <th>MY<br />BARRIERS</th>";
        foreach($barriers as $key => $barrier){
            if($this->submit[$key] != "Submitted"){ continue; }
            if(is_array($barrier) && count($barrier) > 0 && $this->submit[$key] == "Submitted"){
                if(trim($specify[$key]) != ""){
                    $barrier = str_replace("Other", $specify[$key], $barrier);
                }
                $html .= "<td valign='top' style='font-size: 0.7em; line-height: 1.1em;'>".implode("<div style='margin-bottom:0.5em;'></div>", $barrier)."</td>";
            } else {
                $html .= "<td rowspan='2' style='font-size: 0.7em;'>You're meeting recommendations, keep up the great work!</td>";
            }
        }
        $html .= "  </tr>
                    <tr>
                        <th>MY<br />SUPPORTS</th>";
        foreach($barriers as $key => $barrier){
            if($this->submit[$key] == "Submitted"){
                if(is_array($barrier) && count($barrier) > 0){
                    $html .= "<td style='font-size: 0.7em; line-height: 1.1em;'>{$this->recommendations($barrier)}</td>";
                }
                else{
                    $html .= "<td style='display:none;'></td>";
                }
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
            'vaccinate6_avoid' => "COVID-19 Vaccine"
        );
        
        $initial = array();
        $threeMonth = array();
        $sixMonth = array();
        
        foreach($values as $key => $value){
            $initial[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID') == "No") ? $value : "";
            $threeMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_THREEMO') == "No") ? $value : "";
            $sixMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_SIXMO') == "No") ? $value : "";
            $nineMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_NINEMO') == "No") ? $value : "";
            $twelveMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_TWELVEMO') == "No") ? $value : "";
        }
        
        $html = "<p style='margin-bottom:0;'>What vaccines (if any) am I missing?</p>
                 <table style='page-break-inside: avoid; margin-bottom: 1.5em; border-spacing: 0; border-collapse: separate; width: 100%;'>
                    <tr>
                        <th align='left' style='font-weight: 800;color: #06619b;'>Initial</th>
                        <th align='left' style='font-weight: 800;color: #06619b;'>3 months</th>";
        if($this->submit[2] == "Submitted"){
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>6 months</th>";
        }
        if($this->submit[3] == "Submitted"){
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>9 months</th>";
        }
        if($this->submit[4] == "Submitted"){
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>12 months</th>";
        }
        
        $html .= "  </tr>
                    <tr>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>".implode("<br />", array_filter($initial))."</td>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>".implode("<br />", array_filter($threeMonth))."</td>";
        
        if($this->submit[2] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>".implode("<br />", array_filter($sixMonth))."</td>";
        }
        if($this->submit[3] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>".implode("<br />", array_filter($nineMonth))."</td>";
        }
        if($this->submit[4] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>".implode("<br />", array_filter($twelveMonth))."</td>";
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
        $nineMonth = array();
        $twelveMonth = array();
        
        foreach($values as $key => $value){
            $initial[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID') == "No") ? $value : "Yes";
            $threeMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_THREEMO') == "No") ? $value : "Yes";
            $sixMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_SIXMO') == "No") ? $value : "Yes";
            $nineMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_NINEMO') == "No") ? $value : "Yes";
            $twelveMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_TWELVEMO') == "No") ? $value : "Yes";
        }
        
        $html = "<p style='margin-bottom:0;'>Have you had your medications (including prescriptions, over the counter, and supplements) reviewed by a pharmacist or healthcare provider in the last year?</p>
                 <table style='page-break-inside: avoid; margin-bottom: 1.5em; border-spacing: 0; border-collapse: separate; width: 100%;'>
                    <tr>
                        <th align='left' style='font-weight: 800;color: #06619b;'>Initial</th>
                        <th align='left' style='font-weight: 800;color: #06619b;'>3 months</th>";
        if($this->submit[2] == "Submitted"){ 
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>6 months</th>";
        }
        if($this->submit[3] == "Submitted"){ 
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>9 months</th>";
        }
        if($this->submit[4] == "Submitted"){ 
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>12 months</th>";
        }
        $html .= "  </tr>
                    <tr>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>".implode("<br />", array_filter($initial))."</td>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>".implode("<br />", array_filter($threeMonth))."</td>";
        if($this->submit[2] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>".implode("<br />", array_filter($sixMonth))."</td>";
        }
        if($this->submit[3] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>".implode("<br />", array_filter($nineMonth))."</td>";
        }
        if($this->submit[4] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>".implode("<br />", array_filter($twelveMonth))."</td>";
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
        $nineMonth = 0;
        $twelveMonth = 0;
        
        foreach($values as $value){
            $initial += UserFrailtyIndexAPI::interactScore($this->getBlobData('behaviouralassess', $value, YEAR, 'RP_AVOID'));
            $threeMonth += UserFrailtyIndexAPI::interactScore($this->getBlobData('behaviouralassess', $value, YEAR, 'RP_AVOID_THREEMO'));
            $sixMonth += UserFrailtyIndexAPI::interactScore($this->getBlobData('behaviouralassess', $value, YEAR, 'RP_AVOID_SIXMO'));
            $nineMonth += UserFrailtyIndexAPI::interactScore($this->getBlobData('behaviouralassess', $value, YEAR, 'RP_AVOID_NINEMO'));
            $twelveMonth += UserFrailtyIndexAPI::interactScore($this->getBlobData('behaviouralassess', $value, YEAR, 'RP_AVOID_TWELVEMO'));
        }
        
        $initial = ($initial >= 12) ? "No" : "Yes";
        $threeMonth = ($threeMonth >= 12) ? "No" : "Yes";
        $sixMonth = ($sixMonth >= 12) ? "No" : "Yes";
        $nineMonth = ($nineMonth >= 12) ? "No" : "Yes";
        $twelveMonth = ($twelveMonth >= 12) ? "No" : "Yes";
        
        $html = "<p style='margin-bottom:0;'>Having friends and/or family with whom you can talk to, feel at ease with and call on for help is important for your overall health.<br />
                    <br />
                    Do you have a risk in this area:
                 </p>
                 <table style='page-break-inside: avoid; margin-bottom: 1.5em; border-spacing: 0; border-collapse: separate; width: 100%;'>
                    <tr>
                        <th align='left' style='font-weight: 800;color: #06619b;'>Initial</th>
                        <th align='left' style='font-weight: 800;color: #06619b;'>3 months</th>";
        if($this->submit[2] == "Submitted"){
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>6 months</th>";
        }
        if($this->submit[3] == "Submitted"){
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>9 months</th>";
        }
        if($this->submit[4] == "Submitted"){
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>12 months</th>";
        }
        $html .= "  </tr>
                    <tr>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>{$initial}</td>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>{$threeMonth}</td>";
        if($this->submit[2] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>{$sixMonth}</td>";
        }
        if($this->submit[3] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>{$nineMonth}</td>";
        }
        if($this->submit[4] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>{$twelveMonth}</td>";
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
        $nineMonth = array();
        $twelveMonth = array();
        
        foreach($values as $key => $value){
            $initial[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID') == "No") ? $value : "";
            $threeMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_THREEMO') == "No") ? $value : "";
            $sixMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_SIXMO') == "No") ? $value : "";
            $nineMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_NINEMO') == "No") ? $value : "";
            $twelveMonth[] = ($this->getBlobData('behaviouralassess', $key, YEAR, 'RP_AVOID_TWELVEMO') == "No") ? $value : "";
        }
        
        $html = "<p style='margin-bottom:0;'>Diet deficiencies</p>
                 <table style='page-break-inside: avoid; margin-bottom: 1.5em; border-spacing: 0; border-collapse: separate; width: 100%;'>
                    <tr>
                        <th align='left' style='font-weight: 800;color: #06619b;'>Initial</th>
                        <th align='left' style='font-weight: 800;color: #06619b;'>3 months</th>";
        if($this->submit[2] == "Submitted"){
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>6 months</th>";
        }
        if($this->submit[3] == "Submitted"){
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>9 months</th>";
        }
        if($this->submit[4] == "Submitted"){
            $html .= "<th align='left' style='font-weight: 800;color: #06619b;'>12 months</th>";
        }
        $html .= "  </tr>
                    <tr>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>".implode("<br />", array_filter($initial))."</td>
                        <td valign='top' style='white-space:nowrap;padding-right: 0.5em;'>".implode("<br />", array_filter($threeMonth))."</td>";
        if($this->submit[2] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>".implode("<br />", array_filter($sixMonth))."</td>";
        }
        if($this->submit[3] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>".implode("<br />", array_filter($nineMonth))."</td>";
        }
        if($this->submit[4] == "Submitted"){
            $html .= "<td valign='top' style='white-space:nowrap;'>".implode("<br />", array_filter($twelveMonth))."</td>";
        }
        $html .= "  </tr>
                </table>";
        return $html;
    }
    
    function recommendations($barriers){
        global $wgServer, $wgScriptPath, $config;
        $recommendations = array();
        foreach($barriers as $barrier){
            switch($barrier){
                // Activity
                case "I am physically and / or mentally unable to be active":
                    if($config->getValue('reportingExtras', 'AvoidPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/Otago' target='_blank'>Otago</a>";
                    }
                    break;
                case "I don't know where / how to get help in my community":
                    if($config->getValue('reportingExtras', 'CommunityPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-ACT' target='_blank'>Activity Programs</a>";
                    }
                    break;
                case "I have trouble maintaining a routine when it comes to activity":
                    if($config->getValue('reportingExtras', 'EducationResources')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/Activity' target='_blank'>Activity Module</a>";
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/IngredientsForChange' target='_blank'>Ingredients for Change Module</a>";
                    }
                    break;
                
                // Vaccinate
                case "I was not aware of the recommended vaccines":
                    if($config->getValue('reportingExtras', 'EducationResources')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/Vaccination' target='_blank'>Vaccination Module</a>";
                    }
                    break;
                case "I don't know where or how to get vaccinated":
                    if($config->getValue('reportingExtras', 'CommunityPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-VAC' target='_blank'>Vaccination Programs</a>";
                    }
                    if($config->getValue('reportingExtras', 'AvoidPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    }
                    break;
                case "I don't see the point of getting vaccinated":
                    if($config->getValue('reportingExtras', 'EducationResources')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/Vaccination' target='_blank'>Vaccination Module</a>";
                    }
                    break;
                
                // Optimize Medication
                case "I was not aware that this is recommended":
                    if($config->getValue('reportingExtras', 'EducationResources')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/OptimizeMedication' target='_blank'>Optimize Medication Module</a>";
                    }
                    break;
                case "I do not feel comfortable and / or prepared to have this conversation with a healthcare provider":
                    if($config->getValue('reportingExtras', 'AvoidPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    }
                    if($config->getValue('reportingExtras', 'EducationResources')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/EducationModules/OptimizeMedication/Resources/Medication%20Tracker%20Sheet%20Planner.pdf' target='_blank'>Medication Tracker</a>";
                    }
                    break;
                case "I do not know who to talk to about this":
                    if($config->getValue('reportingExtras', 'CommunityPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-VAC' target='_blank'>Optimize Medication Programs</a>";
                    }
                    break;
                case "I have had my medication reviewed in the past, but find it hard to remember each year":
                    if($config->getValue('reportingExtras', 'AvoidPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    }
                    break;
                case "I do not understand why this is important":
                    if($config->getValue('reportingExtras', 'EducationResources')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/OptimizeMedication' target='_blank'>Optimize Medication Module</a>";
                    }
                    break;
                    
                // Interact
                case "I have been restricted due to COVID-19 public health measures":
                    if($config->getValue('reportingExtras', 'AvoidPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/CyberSeniors' target='_blank'>Cyber Seniors</a>";
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    }
                    break;
                case "I find it physically / mentally difficult to participate in social interactions":
                    if($config->getValue('reportingExtras', 'AvoidPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    }
                    break;
                case "I am not aware of opportunities for social interaction in my community":
                    if($config->getValue('reportingExtras', 'CommunityPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-INT' target='_blank'>Interact Programs</a>";
                    }
                    if($config->getValue('reportingExtras', 'AvoidPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/CommunityConnectors' target='_blank'>Community Connectors</a>";
                    }
                    break;
                case "I have trouble maintaining social connections over time":
                    if($config->getValue('reportingExtras', 'CommunityPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-INT' target='_blank'>Interact Programs</a>";
                    }
                    break;
                case "I do not feel that I need more interaction than I already have":
                    if($config->getValue('reportingExtras', 'EducationResources')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/Interact' target='_blank'>Interact Module</a>";
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/EducationModules/Interact/Resources/Interact%20Vid.mp4' target='_blank'>Interact Video</a>";
                    }
                    break;
                    
                // Diet & Nutrition
                case "I find it physically / mentally difficult to do this":
                    if($config->getValue('reportingExtras', 'AvoidPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    }
                    if($config->getValue('reportingExtras', 'CommunityPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-DIET-DIET' target='_blank'>Dietitian Services</a>";
                    }
                    break;
                case "I was not aware of one or more of the recommendations in the questions above":
                    if($config->getValue('reportingExtras', 'EducationResources')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EducationModules/DietAndNutrition' target='_blank'>Diet & Nutrition Module</a>";
                    }
                    break;
                case "It's difficult for me to access nutritious and / or culturally appropriate food because of where I live":
                    if($config->getValue('reportingExtras', 'CommunityPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-DIET' target='_blank'>Diet & Nutrition Programs</a>";
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-TRANSPORT-DRIVP' target='_blank'>Driving Programs</a>";
                    }
                    break;
                case "I can't afford the type of food that I would like to eat":
                    if($config->getValue('reportingExtras', 'CommunityPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-DIET-FOODBANKS' target='_blank'>Food Banks and Stands</a>";
                    }
                    break;
                case "I have trouble maintaining a healthy eating routine":
                    if($config->getValue('reportingExtras', 'AvoidPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=Programs/PeerCoaching' target='_blank'>Peer Coaching</a>";
                    }
                    if($config->getValue('reportingExtras', 'CommunityPrograms')){
                        $recommendations[] = "<a href='{$wgServer}{$wgScriptPath}/index.php/Special:PharmacyMap#/CFN-DIET-DIET' target='_blank'>Dietitian Services</a>";
                    }
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
        $dompdf->setHttpContext(stream_context_create([ 
            'ssl' => [ 
                'verify_peer' => FALSE, 
                'verify_peer_name' => FALSE,
                'allow_self_signed'=> TRUE
            ] 
        ]));
        
        $dompdf->load_html($html);
        $dompdf->render();
        header("Content-Type: application/pdf");
        echo $dompdf->output();
        exit;
        
    }
    
}

?>
