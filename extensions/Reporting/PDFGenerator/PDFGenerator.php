<?php
$dir = dirname(__FILE__);
if(isset($_GET['generatePDF'])){
    require_once($dir . '/../../../Classes/SmartDomDocument/SmartDomDocument.php');
}
require_once('PDFParams.php');
require_once('ReportStorage.php');
$GLOBALS['attachedPDFs'] = array();
$GLOBALS['chapters'] = array();
$GLOBALS['footnotes'] = array();
$GLOBALS['nFootnotes'] = 0;
$GLOBALS['nFootnotesProcessed'] = 0;
$GLOBALS['section'] = 0;
if(isset($_GET['dpi'])){
    define('DPI', $_GET['dpi']);
}
else if(isset($_GET['preview'])){
    define('DPI', 150);
}
else{
    define('DPI', 300);
}
define('DPI_CONSTANT', DPI/72);

if(isset($_GET['generatePDF'])){
    require_once($dir . '/../../../config/dompdf_config.inc.php');
}

PDFGenerator::$preview = isset($_GET['preview']);

/**
 * This class helps with the generation of a PDF document.
 * @package PDFGenerator
 */
abstract class PDFGenerator {

    static $preview = false;
    
    static function cmToPixels($cm, $dpi=72){
        return $cm*$dpi/2.54;
    }
    
    static function replaceSpecial($str){
        $specials = array("/(&fnof;)/",
"/(&Alpha;)/",
"/(&Beta;)/",
"/(&Gamma;)/",
"/(&Delta;)/",
"/(&Epsilon;)/",
"/(&Zeta;)/",
"/(&Eta;)/",
"/(&Theta;)/",
"/(&Iota;)/",
"/(&Kappa;)/",
"/(&Lambda;)/",
"/(&Mu;)/",
"/(&Nu;)/",
"/(&Xi;)/",
"/(&Omicron;)/",
"/(&Pi;)/",
"/(&Rho;)/",
"/(&Sigma;)/",
"/(&Tau;)/",
"/(&Upsilon;)/",
"/(&Phi;)/",
"/(&Chi;)/",
"/(&Psi;)/",
"/(&Omega;)/",
"/(&alpha;)/",
"/(&beta;)/",
"/(&gamma;)/",
"/(&delta;)/",
"/(&epsilon;)/",
"/(&zeta;)/",
"/(&eta;)/",
"/(&theta;)/",
"/(&iota;)/",
"/(&kappa;)/",
"/(&lambda;)/",
"/(&mu;)/",
"/(&nu;)/",
"/(&xi;)/",
"/(&omicron;)/",
"/(&pi;)/",
"/(&rho;)/",
"/(&sigmaf;)/",
"/(&sigma;)/",
"/(&tau;)/",
"/(&upsilon;)/",
"/(&phi;)/",
"/(&chi;)/",
"/(&psi;)/",
"/(&omega;)/",
"/(&thetasym;)/",
"/(&upsih;)/",
"/(&piv;)/",
"/(&bull;)/",
"/(&prime;)/",
"/(&Prime;)/",
"/(&oline;)/",
"/(&frasl;)/",
"/(&weierp;)/",
"/(&image;)/",
"/(&real;)/",
"/(&trade;)/",
"/(&alefsym;)/",
"/(&larr;)/",
"/(&uarr;)/",
"/(&rarr;)/",
"/(&darr;)/",
"/(&harr;)/",
"/(&crarr;)/",
"/(&lArr;)/",
"/(&uArr;)/",
"/(&rArr;)/",
"/(&dArr;)/",
"/(&hArr;)/",
"/(&forall;)/",
"/(&part;)/",
"/(&exist;)/",
"/(&empty;)/",
"/(&nabla;)/",
"/(&isin;)/",
"/(&notin;)/",
"/(&ni;)/",
"/(&prod;)/",
"/(&sum;)/",
"/(&minus;)/",
"/(&lowast;)/",
"/(&radic;)/",
"/(&prop;)/",
"/(&infin;)/",
"/(&ang;)/",
"/(&and;)/",
"/(&or;)/",
"/(&cap;)/",
"/(&cup;)/",
"/(&int;)/",
"/(&there4;)/",
"/(&sim;)/",
"/(&cong;)/",
"/(&asymp;)/",
"/(&ne;)/",
"/(&equiv;)/",
"/(&le;)/",
"/(&ge;)/",
"/(&sub;)/",
"/(&sup;)/",
"/(&nsub;)/",
"/(&sube;)/",
"/(&supe;)/",
"/(&oplus;)/",
"/(&otimes;)/",
"/(&perp;)/",
"/(&sdot;)/",
"/(&lceil;)/",
"/(&rceil;)/",
"/(&lfloor;)/",
"/(&rfloor;)/",
"/(&lang;)/",
"/(&rang;)/",
"/(&loz;)/",
"/(&spades;)/",
"/(&clubs;)/",
"/(&hearts;)/",
"/(&#9210;)/",
"/(&#10003;)/",
"/(&#10004;)/",
"/(&diams;)/",
"/(&#9679;)/",
"/(&#9651;)/");
        $str = preg_replace($specials, "<span style='font-family: dejavu sans !important; line-height:50%;'>$1</span>", $str);
        $str = str_replace("&#8209;", "-", $str);
        $str = str_replace("&#61485;", "~", $str);
        $str = str_replace("&#8208;", "-", $str);
        $str = str_replace("&#9472;", "-", $str);
        $str = str_replace("&#64257;", "fi", $str);
        $str = str_replace("<sup>&#9702;</sup>", "&#176;", $str);
        $str = str_replace("‐", "-", $str);
        $str = str_replace("—", "-", $str);
        $str = str_replace("–", "-", $str);
        $str = str_replace("&lang;", "&#10216;", $str);
        $str = str_replace("&rang;", "&#10217;", $str);
        $str = str_replace("&hellip;", "...", $str);
        $str = str_replace("</html>", "", $str);
        $str = str_replace("<html>", "", $str);
        $str = str_replace("<body>", "", $str);
        $str = str_replace("</body>", "", $str);
        /*preg_match_all("/(<strong>.*?<\/strong>)/", $str, $matches);
        foreach($matches[1] as $match){
            $match1 = str_replace(" ", "</strong> &nbsp;<strong>", $match);
            $str = str_replace($match, $match1, $str);
        }
        preg_match_all("/(<em>.*?<\/em>)/", $str, $matches);
        foreach($matches[1] as $match){
            $match1 = str_replace(" ", "</em> &nbsp;<em>", $match);
            $match1 = str_replace(" &nbsp;<em>&nbsp;", " &nbsp;<em>", $match1);
            $str = str_replace($match, $match1, $str);
        }
        preg_match_all("/(<span style=\"text-decoration: underline;\">.*?<\/span>)/", $str, $matches);
        foreach($matches[1] as $match){
            $match1 = str_replace("<span style=\"text-decoration: underline;\">", "<u>", $match);
            $match1 = str_replace_last("</span>", "</u>", $match1);
            $match1 = str_replace(" ", "</u> &nbsp;<u>", $match1);
            //$match1 = str_replace(" &nbsp;<u>&nbsp;", " &nbsp;<u>", $match1);
            $str = str_replace($match, $match1, $str);
        }*/
        return $str;
    }
    
    /**
     * Generates a PDF based on html input
     * @param string $name The name of the PDF File
     * @param string $html The html input string
     * @param string $head Any extra html header information to include
     * @param Person $person The Person that this Report is being generated by
     * @param Project $project The Project that this Report belongs for (generally only for Project Reports)
     * @param boolean $preview Whether or not this should be a preview
     * @param AbstractReport $report The report that this PDF is for (optionally used to add extra information)
     * @returns array Returns an array containing the final html, as well as the pdf string
     */
    static function generate($name, $html, $head, $person=null, $project=null, $preview=false, $report=null, $stream=false, $orientation='portrait'){
        global $wgServer, $wgScriptPath, $wgUser, $config;
        
        ini_set("max_execution_time","1600");
        ini_set("memory_limit","2024M");
        
        if(self::$preview){
            $preview = true;
        }
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
        
        $imgs = $dom->getElementsByTagName("img");
        for($i=0; $i<$imgs->length; $i++){
            $img = $imgs->item($i);
            if(strstr($img->getAttribute('style'), "display: block;")){
                // Image centering
                $img->setAttribute('style', str_replace("margin-left: auto;", "", 
                                            str_replace("margin-right: auto;", "", 
                                            str_replace("display: block;", "", $img->getAttribute('style')))));
                
                // Create wrapper div                            
                $div = $dom->createElement('div');
                $div->setAttribute('style', 'text-align: center;');
                $img->parentNode->replaceChild($div,$img);
                
                //Append this image to wrapper div
                $div->appendChild($img);
            }
        }
        
        $tds = $dom->getElementsByTagName("td");
        for($i=0; $i<$tds->length; $i++){
            $td = $tds->item($i);
            $td->setAttribute('width', '');
        }
        
        $divs = $dom->getElementsByTagName('div');
        $nInfo = 0;
        foreach($divs as $div){
            if($div->getAttribute('class') == 'report_info'){
                $value = explode("\n", $div->nodeValue);
                $nInfo = count($value);
                break;
            }
            if($div->getAttribute('class') == 'tinymce'){
                $tables = $div->getElementsByTagName('table');
                foreach($tables as $table){
                    $table->setAttribute('width', "100%");
                }
            }
        }
        $nInfo = max(5, $nInfo);
        
        $tables = $dom->getElementsByTagName('table');
        foreach($tables as $table){
            $brs = $table->getElementsByTagName('br');
            for($i=0; $i<$brs->length; $i++){
                $br = $brs->item($i);
                if($br->getAttribute('style') == 'font-size:1em;'){
                    $i--;
                    $br->parentNode->removeChild($br);
                }
            }
        }
        
        $html = "$dom";
        if($person == null || $person->getId() == 0){
            $person = @$report->person;
        }
        if($person == null || $person->getId() == 0){
            $person = Person::newFromId($wgUser->getId());
        }
        
        $margins = $config->getValue('pdfMargins');
        
        $previewScript = "";
        if($preview){
            $previewScript = "
            <script type='text/javascript' src='$wgServer$wgScriptPath/scripts/jquery.min.js'></script>
            <script type='text/javascript' src='$wgServer$wgScriptPath/scripts/jquery.backwards.js'></script>
            <script type='text/javascript' src='$wgServer$wgScriptPath/scripts/jquery-ui.min.js'></script>
            <script type='text/javascript' src='$wgServer$wgScriptPath/scripts/jquery.qtip.min.js'></script>
            <link type='text/css' href='$wgServer$wgScriptPath/skins/cavendish/jquery.qtip.min.css' rel='Stylesheet' />
            <script type='text/javascript'>
                function hideProgress(){
                    if(parent.location == window.location){
                        return;
                    }
                    parent.hideProgress();
                    load_page();
                }
                
                function load_page() {
                    var interval = setInterval(function(){
                        if($(document).height() > 0){
                            $('body').width($(document).width() - 50);
                            parent.alertsize($(document).height());
                            $('body').width('auto');
                            clearInterval(interval);
                        }
                    }, 33);
                }
            </script>
            <script type='text/javascript'>
		        $(document).ready(function(){
		            $('.tooltip').qtip();
		            hideProgress();
		        });
		    </script>";
        }
        else{
            global $dompdfOptions;
            $dompdf = new Dompdf\Dompdf($dompdfOptions);
            $dompdf->setPaper('A4', $orientation);
        }
        
        $header = <<<EOF
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>$name</title>
        <style type='text/css'>
EOF;
        $fontSize = ($config->getValue('pdfFontSize')*DPI_CONSTANT);
        if($preview){
            $header .= "
            #pdfBody .pagebreak {
		        border-width: 0 0 ".max(1, (0.5*DPI_CONSTANT))."px 0;
		        border-style: dashed;
		        border-color: #000000;
		        margin-bottom: ".(5*DPI_CONSTANT)."px;
		        margin-top:".(5*DPI_CONSTANT)."px;
		    }
		    
		    /*#pdfBody .logo {
		        background-image: url('../skins/{$config->getValue('networkName')}_Logo.png');
		        background-size: 100% Auto;
		        background-repeat: no-repeat;
		    }*/
		    
		    #pdfBody .report_name {
		        margin-top:".(17*DPI_CONSTANT)."px;
		        margin-right: 100px;
		    }
		    
		    #pdfBody .report_info {
		        margin-top:".(12*DPI_CONSTANT)."px;
		        margin-right: 100px;
		    }
		    
		    /*#pdfBody img.logo {
		        display: none;
		    }*/
		    
		    #pdfBody #page_header {
		        width:100%;
		        text-align:right;
		        margin-top:-".(15*DPI_CONSTANT)."px;
		        font-size:smaller;
		    }
		    
		    #pdfBody .belowLine {
		        margin-bottom:".(15*DPI_CONSTANT)."px;
		    }
		    
		    #pdfBody {
		        margin: ".(20*DPI_CONSTANT)."px ".(20*DPI_CONSTANT)."px !important;
		        position: relative;
		        white-space: normal !important;
		    }
		    
		    .ui-dialog-content {
		        white-space: normal !important;
		    }";
        }
        else{
            $header .= "
            #pdfBody .pagebreak {
		        page-break-after:always;
		    }
		    
		    /*#pdfBody .logo {
		        background-image: url('skins/{$config->getValue('networkName')}_Logo.png');
		        background-repeat: no-repeat;
		    }*/
		    
		    #pdfBody .belowLine {
		        display: none;
		    }
		    
		    #pdfBody sup {
		        font-size: 0.8em;
		        vertical-align: top;
		    }
		    
		    #pdfBody sub {
		        font-size: 0.8em;
		    }
		    ";
        }
        
		$header .= "
		    @page {
                margin-top: {$margins['top']}cm;
                margin-right: {$margins['right']}cm;
                margin-bottom: {$margins['bottom']}cm;
                margin-left: {$margins['left']}cm;
            }
		
		    #pdfBody  {
		        margin: ".PDFGenerator::cmToPixels(0.5)."px 0;
		        margin-bottom: ".PDFGenerator::cmToPixels(0.5)."px;
		        font-family: {$config->getValue('pdfFont')} !important;
		        font-size: {$fontSize}px;
		        text-align: justify;
		    }
		    
		    #pdfBody table {
		        text-align: initial;
		    }
		    
		    #pdfBody .wikitable {
		        border: none;
		    }
		    
		    #pdfBody .wikitable th {
		        background: #EEEEEE;
		        padding: ".(1*DPI_CONSTANT)."px;
		    }
		    
		    #pdfBody .wikitable td {
		        background: #FFFFFF;
		        padding: ".(1*DPI_CONSTANT)."px;
		    }
		    
		    #pdfBody th {
		        text-align: center;
		    }
		    
		    /* Messages */
		    
            #pdfBody .inlineError, #pdfBody .inlineWarning, #pdfBody .inlineSuccess, #pdfBody .inlineInfo {
                margin: 0 0;
                padding: 0 ".(2*DPI_CONSTANT)."px;
            }

            #pdfBody .inlineError {
                color: #D50013;
                background: #FEB8B8;
                border-radius: 3px;
                -moz-border-radius: 3px;
	            -webkit-border-radius: 3px;
            }

            #pdfBody .inlineWarning {
                color: #9C600D;
                background: #FDEEB2;
                border-radius: 3px;
                -moz-border-radius: 3px;
	            -webkit-border-radius: 3px;
            }

            #pdfBody .inlineSuccess {
                color: #51881D;
                background: #DEF1BE;
                border-radius: 3px;
                -moz-border-radius: 3px;
                -webkit-border-radius: 3px;
            }

            #pdfBody .inlineInfo {
                color: #0A5398;
                background: #BCE4F7;
                border-radius: 3px;
                -moz-border-radius: 3px;
                -webkit-border-radius: 3px;
            }
            
            #pdfBody .budgetError {
                color: #D50013;
                background: #FEB8B8;
            }
		    
		    #pdfBody td, #pdfBody th {
		        font-family: {$config->getValue('pdfFont')} !important;
		        background-color: #FFFFFF;
		    }
		    
		    #pdfBody .report_name {
		        position:absolute;
		        right: 0;
		        top: 0;
		        margin-right:0 !important;
		    }
		    
		    #pdfBody .report_info {
		        width: 100%;
		        height: ".(($fontSize+4)*($nInfo) + (20*DPI_CONSTANT))."px;
		        font-size: ".($fontSize+(-3*DPI_CONSTANT))."px;
		        top:0;
		        margin-right:0 !important;
		    }
		    
		    #pdfBody .report_info > tbody > tr > td {
		        vertical-align: top;
		    }
		    
		    #pdfBody .progress_table {
		        white-space: nowrap;
		        font-size: ".($fontSize+(-3*DPI_CONSTANT))."px;
		        border-spacing:".max(1, (0.5*DPI_CONSTANT))."px;
		        border-width:".max(1, (0.5*DPI_CONSTANT))."px;
		        border-color: #000000;
		        margin-top:".(5*DPI_CONSTANT)."px;
		    }
		    
		    #pdfBody .report_info > table {
		        height: ".(($fontSize+4)*($nInfo) + (20*DPI_CONSTANT))."px;
		    }
		    
		    #pdfBody hr {
		        border-width: ".max(1, (0.5*DPI_CONSTANT))."px 0 0 0;
		        border-style: solid;
		        border-color: #000000;
		    }
		    
		    #pdfBody h1 {
		        background-color: #333333;
		        color: #FFFFFF;
		        margin-top:0;
		        margin-bottom: 0.25em;
		        font-size: ".($fontSize+(4*DPI_CONSTANT))."px;
		        font-weight:normal;
		        border: ".max(1, (0.5*DPI_CONSTANT))."px solid #000000;
		        padding: ".max(1, (0.5*DPI_CONSTANT))."px ".(3*DPI_CONSTANT)."px ".(2*DPI_CONSTANT)."px ".(3*DPI_CONSTANT)."px;
		    }
		    
		    #pdfBody h2 {
		        background-color: #666666;
		        color: #FFFFFF;
		        font-size: ".($fontSize+(2*DPI_CONSTANT))."px;
		        font-weight:normal;
		        border: ".max(1, (0.5*DPI_CONSTANT))."px solid #000000;
		        padding: ".max(1, (0.5*DPI_CONSTANT))."px ".(3*DPI_CONSTANT)."px ".(2*DPI_CONSTANT)."px ".(3*DPI_CONSTANT)."px;
		        margin-bottom: ".(2*DPI_CONSTANT)."px;
		        margin-top: ".(2*DPI_CONSTANT)."px;
		    }
		    
		    #pdfBody h3 {
		        background-color: #999999;
		        color: #FFFFFF;
		        font-size: ".($fontSize+(1*DPI_CONSTANT))."px;
		        font-weight:normal;
		        border: ".max(1, (0.5*DPI_CONSTANT))."px solid #000000;
		        padding: ".max(1, (0.5*DPI_CONSTANT))."px ".(3*DPI_CONSTANT)."px ".(2*DPI_CONSTANT)."px ".(3*DPI_CONSTANT)."px;
		        margin-bottom: ".(2*DPI_CONSTANT)."px;
		        margin-top: ".($config->getValue('pdfFontSize')*DPI_CONSTANT)."px;
		    }
		    
		    #pdfBody h4 {
		        margin-top:0;
		        margin-bottom:0;
		        font-size: ".($fontSize+(1*DPI_CONSTANT))."px;
		    }
		    
		    #pdfBody h1, #pdfBody h2, #pdfBody h3, #pdfBody h4 {
		        page-break-inside: avoid;
		    }
		    
		    #pdfBody #ni_report_wrapper, #pdfBody #hqp_report_wrapper, #pdfBody #ldr_report_wrapper, #pdfBody #ldr_comments_wrapper, #pdfBody #ldr_budget_wrapper {
		        width: 100%;
		    }
		    
		    #pdfBody #ni_budget_wrapper {
		        width:100%;
		    }
		    
		    .pdfnodisplay {
		        display:none;
		    }
		    
		    #pdfBody p {
		        margin: 0;
		    }
		    
		    #pdfBody small, #pdfBody .small {
		        font-size: ".max(10, ($fontSize+(-3*DPI_CONSTANT)))."px;
		        display:inline;
		    }
		    
		    #pdfBody table.small {
		        font-size: ".max(10, ($fontSize+(-3*DPI_CONSTANT)))."px;
		        display: table;
		    }
		    
		    #pdfBody td.small {
		        font-size: ".max(10, ($fontSize+(-3*DPI_CONSTANT)))."px;
		        display:table-cell;
		    }
		    
		    #pdfBody .smaller {
		        font-size: ".max(9, ($fontSize+(-4*DPI_CONSTANT)))."px;
		    }
		    
		    #pdfBody .smallest {
		        font-size: ".max(8, ($fontSize+(-6*DPI_CONSTANT)))."px;
		    }
		    
		    #pdfBody ul {
		        margin-top: ".max(9, ($fontSize+(-4*DPI_CONSTANT)))."px;
		        margin-bottom: ".max(9, ($fontSize+(-4*DPI_CONSTANT)))."px;
		    }
		    
		    #pdfBody ul ul {
		        margin-top: 0;
		        margin-bottom: 0;
		    }
		    
		    #pdfBody li {
		        font-weight: normal !important;
		        margin-bottom: 0;
		    }
		    
		    #pdfBody .tinymce li {
		        margin-bottom: 0;
		    }
		    
		    #pdfBody .tinymce ul {
		        margin-top: 0;
		        margin-bottom: ".max(9, ($fontSize+(DPI_CONSTANT)))."px;
		    }
		    
		    #pdfBody .tinymce ul ul {
		        margin-top: 0;
		        margin-bottom: 0;
		    }
		    
		    #pdfBody .tinymce table p {
		        margin-bottom: 0 !important;
		    }
		    
		    #pdfBody b, #pdfBody strong {
                font-weight: bold !important;
            }
		    
		    #pdfBody .label {
		        font-weight: bold;
		    }
		    
		    #pdfBody ins {
                background: #AAFFAA;
                display: inline;
                text-decoration: none;
                vertical-align:top;
                position:relative;
            }

            #pdfBody del {
                background: #FFAAAA;
                display: inline;
                text-decoration: none;
                vertical-align:top;
                position:relative;
            }
            
            #pdfBody .logo {
                width:".(198.333*DPI_CONSTANT)."px;
                height:".(68*DPI_CONSTANT)."px;
                position:absolute;
                margin-top: ".($config->getValue('pdfFontSize')*DPI_CONSTANT)."px;
            }
            
            #pdfBody .logo_div {
                margin-bottom: ".DPI_CONSTANT."px;
                height: ".(($fontSize+4)*$nInfo + (20*DPI_CONSTANT))."px;
            }
            
            #pdfBody br {
                font-size: 0.5em;
            }
            
            #pdfBody .tinymce p {
                margin-bottom: ".($fontSize)."px;
            }
            
            #pdfBody .tinymce strong {
                page-break-after: avoid;
                page-break-before: avoid;
            }
            
            #pdfBody .tinymce ol, #pdfBody .tinymce ul {
                padding-left: ".($fontSize*2)."px;
            }
            
            #pdfBody .externalLink {
                color: ".$config->getValue("hyperlinkColor").";
                text-decoration: none;
            }
            
            #pdfBody .tinymce table {
                max-width: 100%;
                border: none;
            }
		    
		</style>
		<!--[if lt IE 9]>
		    <style type='text/css'>
		        /*#pdfBody .logo {
		            background-image: none;
		            filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='../skins/{$config->getValue('networkName')}_Logo.png',sizingMethod='scale');
                    -ms-filter: \"progid:DXImageTransform.Microsoft.AlphaImageLoader(src='../skins/{$config->getValue('networkName')}_Logo.png', sizingMethod='scale')\";
                }*/
            </style>
        <![endif]-->
		$head
		$previewScript
		</head>";
		$headerName = @$report->headerName;
		if($headerName == ""){
            if(isset($_GET['headerName'])){
                $headerName = $_GET['headerName'];
            }
            else if($project != null){
                if($project->getName() == ""){
                    $headerName = "{$person->getReversedName()}";
                }
                else {
                    $headerName = "{$project->getName()}";
                }
            }
            else {
                $headerName = "{$person->getReversedName()}";
            }
        }
        $pages = '
        <script type="text/php">

if ( isset($pdf) ) {

  $font = $fontMetrics->getFont("'.$config->getValue('pdfFont').'");
  $size = "10";
  $size2 = 6;
  $color = array(0,0,0);
  $text_height = $fontMetrics->getFontHeight($font, $size);
  $text_height2 = $fontMetrics->getFontHeight($font, $size2);
  
  $foot = $pdf->open_object();
  
  $w = $pdf->get_width();
  $h = $pdf->get_height();

  // Draw a line along the bottom
  $y = $h - $text_height2 - '.PDFGenerator::cmToPixels($margins['bottom']).';
  if(!'.var_export($report->skipTopLine, true).'){
          $pdf->line('.PDFGenerator::cmToPixels($margins['left']).', 
                     '.PDFGenerator::cmToPixels($margins['top']).', 
                     $w - '.PDFGenerator::cmToPixels($margins['right']).', 
                     '.PDFGenerator::cmToPixels($margins['top']).', 
                     $color, 0.5);
  }
  $pdf->line('.PDFGenerator::cmToPixels($margins['left']).', 
             $h - '.PDFGenerator::cmToPixels($margins['bottom']).', 
             $w - '.PDFGenerator::cmToPixels($margins['right']).', 
             $h - '.PDFGenerator::cmToPixels($margins['bottom']).', 
             $color, 0.5);
  $pdf->close_object();
  $pdf->add_object($foot, "all");
  $text = "Page {PAGE_NUM} of {PAGE_COUNT}";

  // Center the text
  $nameWidth = $fontMetrics->getTextWidth("'.utf8_encode($headerName).' ", $font, $size);
  $width = $fontMetrics->getTextWidth("Page 1 of 50", $font, $size2);
  
  $pdf->page_text($w - $nameWidth - '.PDFGenerator::cmToPixels($margins['right']).', '.PDFGenerator::cmToPixels($margins['top']).' - $text_height - 1, "'.utf8_encode($headerName).'", $font, $size, $color, 0.01);
  $pdf->page_text($w - $width - '.PDFGenerator::cmToPixels($margins['right']).', $h+2 - '.PDFGenerator::cmToPixels($margins['bottom']).', $text, $font, $size2, $color, 0.01);
}
</script>';
        $dateStr = date("Y-m-d H:i:s T", time());
        $html = str_replace("line-height:inherit;", "", $html);
        $html = str_replace("line-height: inherit;", "", $html);
        $html = str_replace("line-height:inherit", "", $html);
        $html = str_replace("line-height: inherit", "", $html);
        if($preview){
            $html = PDFGenerator::replaceSpecial($html);
            echo $header."<body><div id='pdfBody'><div id='page_header'>{$headerName}</div><hr style='border-width:1px 0 0 0;position:absolute;left:".(0*DPI_CONSTANT)."px;right:".(0*DPI_CONSTANT)."px;top:".($config->getValue('pdfFontSize')*DPI_CONSTANT)."px;' /><div style='position:absolute;top:0;font-size:smaller;'><i>Generated: $dateStr</i></div><div class='belowLine'></div>$html</div></body></html>";
            return;
        }
        
        $html = str_replace("–", '-', $html);
        $html = str_replace("’", '\'', $html);
        $html = str_replace("“", '"', $html);
        $html = str_replace("”", '"', $html);
        $html = str_replace("…", '...', $html);
        $html = PDFGenerator::replaceSpecial($html);
        //$html = utf8_encode($html);
        $html = preg_replace('/\cP/', '', $html);
        $finalHTML = utf8_decode($header."<body id='pdfBody'><div style='position:absolute;left:0;top:-".(PDFGenerator::cmToPixels($margins['top'] + 0.5)+($fontSize*0.5))."px;font-size:smaller;'><i>Generated: $dateStr</i></div>$pages$html</body></html>");
        $dompdf->load_html($finalHTML);
        $dompdf->render();
        //$pdfStr = $dompdf->output();
        $pdfStr = PDFGenerator::processChapters($dompdf, $name);
        unset($dompdf);
        Dompdf\Image\Cache::clear();
        $GLOBALS['footnotes'] = array();
        $GLOBALS["nFootnotesProcessed"] = 0;
        if(!$stream){
            return array('html' => $finalHTML, 'pdf' => $pdfStr);
        }
        PDFGenerator::stream($pdfStr);
    }
    
    /**
     * Concatenates the chapters to the pdf document using GhostScript
     * @param DOMPDF $dompdf The instantiated DOMPDF object
     * @param string $name The name of the PDF document
     * @returns string Returns the pdf string
     */
    static function processChapters($dompdf, $name){
        global $IP;
        $str = "";
        $attached = array();
        $name = md5($name);
        foreach($GLOBALS['attachedPDFs'] as $pdf){
            $blob = new ReportBlob();
            $blob->loadFromMD5($pdf);
            $data = json_decode($blob->getData());
            $md5 = md5($pdf);
            if($data != null){
                file_put_contents("/tmp/{$md5}", base64_decode($data->file));
                exec("$IP/extensions/Reporting/PDFGenerator/gs \\
                      -q \\
                      -dNOPAUSE \\
                      -dBATCH \\
                      -sDEVICE=pdfwrite \\
                      -sOutputFile=\"/tmp/{$md5}unencrypted\" \\
                      -c .setpdfwrite \\
                      -f \"/tmp/{$md5}\"");
                $attached[] = "\"/tmp/{$md5}unencrypted\"";
            }
        }
        $attached = implode(" ", $attached);
        foreach($GLOBALS['chapters'] as $chapter){
            if(count($chapter['subs']) > 0){
                $str .= "[/Count ".count($chapter['subs'])." /Title ({$chapter['title']}) /Page {$chapter['page']} /OUT pdfmark\n";
                foreach($chapter['subs'] as $sub){
                    if(count($sub['subs']) > 0){
                        $str .= "[/Count -".count($sub['subs'])." /Title ({$sub['title']}) /Page {$sub['page']} /OUT pdfmark\n";
                        foreach($sub['subs'] as $sub1){
                            $str .= "[/Title ({$sub1['title']}) /Page {$sub1['page']} /OUT pdfmark\n";
                        }
                    }
                    else{
                        $str .= "[/Title ({$sub['title']}) /Page {$sub['page']} /OUT pdfmark\n";
                    }
                }
            }
            else{
                $str .= "[/Title ({$chapter['title']}) /Page {$chapter['page']} /OUT pdfmark\n";
            }
        }
        $rand = rand(0, 100000000);
        $nRetries = 0;
        while(file_exists("/tmp/{$name}{$rand}pdfmarks") && $nRetries < 5){
            // File is already in use, wait one second and try again, but don't try more than 5 times
            $nRetries++;
            sleep(1);
        }
        file_put_contents("/tmp/{$name}{$rand}pdfmarks", $str);
        file_put_contents("/tmp/{$name}{$rand}pdf", $dompdf->output());
        exec("pdftk \"/tmp/{$name}{$rand}pdf\" {$attached} cat output \"/tmp/{$name}{$rand}nomarks\"");

        exec("$IP/extensions/Reporting/PDFGenerator/gs \\
                -q \\
                -dBATCH \\
                -dNOPAUSE \\
                -sDEVICE=pdfwrite \\
                -dPDFSETTINGS=/prepress \\
                -sOutputFile=\"/tmp/{$name}{$rand}withmarks\" \"/tmp/{$name}{$rand}nomarks\" \"/tmp/{$name}{$rand}pdfmarks\""); // Add Bookmarks
        
        $pdfStr = file_get_contents("/tmp/{$name}{$rand}withmarks");
        unlink("/tmp/{$name}{$rand}pdfmarks");
        unlink("/tmp/{$name}{$rand}nomarks");
        unlink("/tmp/{$name}{$rand}pdf");
        unlink("/tmp/{$name}{$rand}withmarks");
        foreach($GLOBALS['attachedPDFs'] as $pdf){
            $md5 = md5($pdf);
            if(file_exists("/tmp/{$md5}")){
                unlink("/tmp/{$md5}");
                unlink("/tmp/{$md5}unencrypted");
            }
        }
        $GLOBALS['chapters'] = array();
        $GLOBALS['nFootnotes'] = 0;
        $GLOBALS['section'] = 0;
        $GLOBALS['attachedPDFs'] = array();
        return $pdfStr;
    }
    
    /*
     * Adds a pdf to the end of the PDF
     * @param string $pdf The id of the pdf
     */
    static function attachPDF($pdf){
        global $wgOut;
        $pdf = strip_tags($pdf);
        $wgOut->addHTML("<script type='text/php'>
                            \$GLOBALS['attachedPDFs'][] = \"{$pdf}\";
                        </script>");
    }
    
    /**
     * Adds a top level bookmark to the document
     * @param string $title The title of the bookmark
     * @param integer $pageOffset The offset of the page index (useful for pdf attachments)
     */
    static function addChapter($title, $pageOffset=0){
        global $wgOut;
        $title = strip_tags($title);
        if($pageOffset == 0){
            $wgOut->addHTML("<div></div>");
        }
        $wgOut->addHTML("<script type='text/php'>
                            \$GLOBALS['chapters'][] = array('title' => \"{$title}\", 
                                                            'page' => \$pdf->get_page_number() + {$pageOffset},
                                                            'subs' => array());
                        </script>");
    }
    
    /**
     * Adds a second level Chapter bookmark to the document
     * @param string $title The title of the sub-bookmark
     * @param integer $pageOffset The offset of the page index (useful for pdf attachments)
     */
    static function addSubChapter($title, $pageOffset=0){
        global $wgOut;
        $title = strip_tags($title);
        if($pageOffset == 0){
            $wgOut->addHTML("<div></div>");
        }
        $wgOut->addHTML("<script type='text/php'>
                            \$GLOBALS['chapters'][count(\$GLOBALS['chapters'])-1]['subs'][] = array('title' => \"{$title}\", 
                                                            'page' => \$pdf->get_page_number() + {$pageOffset},
                                                            'subs' => array());
                        </script>");
    }
    
    /**
     * Adds a third level Chapter bookmark to the document
     * @param string $title The title of the sub-bookmark
     * @param integer $pageOffset The offset of the page index (useful for pdf attachments)
     */
    static function addSubSubChapter($title, $pageOffset=0){
        global $wgOut;
        $title = strip_tags($title);
        if($pageOffset == 0){
            $wgOut->addHTML("<div></div>");
        }
        $wgOut->addHTML("<script type='text/php'>
                            \$GLOBALS['chapters'][count(\$GLOBALS['chapters'])-1]['subs'][count(\$GLOBALS['chapters'][count(\$GLOBALS['chapters'])-1]['subs'])-1]['subs'][] = array('title' => \"{$title}\", 
                                                            'page' => \$pdf->get_page_number() + {$pageOffset},
                                                            'subs' => array());
                        </script>");
    }
    
    /**
     * Adds a footnote to the PDF
     * @param string $note The text for the footnote
     */
    static function addFootNote($note){
        global $wgOut;
        $wgOut->addHTML("<script type='text/php'>
                            if(!isset(\$GLOBALS[\"nFootnotes\"])){
                                \$GLOBALS[\"nFootnotes\"] = 0;
                                \$GLOBALS[\"nFootnotesProcessed\"] = 0;
                            }
                            \$GLOBALS[\"nFootnotes\"]++;
                            \$GLOBALS[\"footnotes\"][\$PAGE_NUM][".(FootnoteReportItem::$nFootnotes-1)."] = array(\"id\" => ".FootnoteReportItem::$nFootnotes.", \"note\" => \"{$note}\", \"processed\" => false);
                            \$php_code = '
                                if(isset(\$GLOBALS[\"footnotes\"][\$PAGE_NUM])){
                                    \$font = \$fontMetrics->getFont(\"verdana\");
                                    \$size = 6;
                                    \$text_height = \$fontMetrics->getFontHeight(\$font, \$size);
                                    \$color = array(0,0,0);
                                    \$w = \$pdf->get_width();
                                    \$h = \$pdf->get_height();
                                    \$y = \$h - \$text_height - 24;
                                    
                                    \$maxX = array();
                                    ksort(\$GLOBALS[\"footnotes\"][\$PAGE_NUM]);
                                    foreach(\$GLOBALS[\"footnotes\"][\$PAGE_NUM] as \$key => \$footnote){
                                        \$key -= \$GLOBALS[\"nFootnotesProcessed\"];
                                        \$id = \$footnote[\"id\"];
                                        \$note = \$footnote[\"note\"];
                                        \$xOffset = floor(\$key / 3);
                                        \$text_width = \$fontMetrics->getTextWidth(\"[\$id] \$note\", \$font, \$size);
                                        if(!isset(\$maxX[\$xOffset])){
                                            \$maxX[\$xOffset] = 0;
                                        }
                                        \$xOffsetAlready = 0;
                                        if(\$xOffset > 0){
                                            \$xOffsetAlready = \$maxX[\$xOffset-1];
                                        }
                                        \$maxX[\$xOffset] = max(\$maxX[\$xOffset], \$xOffsetAlready+\$text_width);
                                    }
                                    \$i = 0;
                                    foreach(\$GLOBALS[\"footnotes\"][\$PAGE_NUM] as \$key => \$footnote){
                                        if(!\$footnote[\"processed\"]){
                                            \$GLOBALS[\"footnotes\"][\$PAGE_NUM][\$key][\"processed\"] = true;
                                            \$key -= \$GLOBALS[\"nFootnotesProcessed\"];
                                            \$id = \$footnote[\"id\"];
                                            \$note = \$footnote[\"note\"];
                                            \$xOffset = floor(\$key / 3);
                                            \$x = 0;
                                            if(isset(\$maxX[\$xOffset-1])){
                                                \$x = \$maxX[\$xOffset-1];
                                            }
                                            \$extraHeight = 0;
                                            if((\$key + 1) > 6){
                                                \$extraHeight = \$text_height;
                                            }
                                            \$pdf->text(22 + \$x + 8*\$xOffset, \$y+(\$extraHeight + \$text_height*(\$key - (\$xOffset)*3)) - \$text_height + 4, \"[\$id] \$note\", \$font, \$size, \$color);
                                            \$i++;
                                        }
                                    }
                                    \$GLOBALS[\"nFootnotesProcessed\"] += \$i;
                                }
                                ';
                             \$pdf->page_script(\$php_code);
                        </script>");
    }
    
    static function changeSection(){
        global $wgOut;
        // It doesn't look like dompdf supports this yet.  We want to display the page numbers like {section#} - {page#}
        $wgOut->addHTML("<script type='text/php'>
            \$php_code = '\$GLOBALS[\"section\"]++;';
            \$pdf->page_script(\$php_code);
        </script>");  
    }
    
    /**
     * Streams the pdf to the browser
     * @param string $pdfStr The pdf string
     */
    function stream($pdfStr){
        $len = strlen($pdfStr);
        header("Content-Type: application/pdf");
        header("Content-Length: $len");
        echo $pdfStr;
        exit;
    }
}

?>
