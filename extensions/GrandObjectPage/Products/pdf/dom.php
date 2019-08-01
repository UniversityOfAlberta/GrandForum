<?php

$dir = dirname(__FILE__);
require_once(dirname(__FILE__) . '/../../../../Classes/dompdf/dompdf_config.inc.php');

// reference the Dompdf namespace
use Dompdf\Dompdf;

// instantiate and use the dompdf class
$dompdf = new Dompdf();

//Format the item date in the header
$formatDate = date('M d, Y',strtotime($_POST['date']));

// Set the checkboxes
$approval = ($_POST['action_requested'] == 'Approval')? ☒ : ☐ ;
$recommendation = ($_POST['action_requested'] == 'Recommendation')? ☒ : ☐ ;
$enrolment_management = ($_POST['enrolment_management'])? ☒ : ☐ ;
$faculty_staff = ($_POST['faculty_staff'])? ☒ : ☐ ;
$funding_management = ($_POST['funding_management'])? ☒ : ☐ ;
$it_services = ($_POST['it_services'])? ☒ : ☐ ;
$leadership_change = ($_POST['leadership_change'])? ☒ : ☐ ;
$physical_infrastructure = ($_POST['physical_infrastructure'])? ☒ : ☐ ;
$relationship_stakeholders = ($_POST['enrolment_management'])? ☒ : ☐ ;
$research_enterprise = ($_POST['research_enterprise'])? ☒ : ☐ ;
$reputation = ($_POST['reputation'])? ☒ : ☐ ;
$safety = ($_POST['safety'])? ☒ : ☐ ;
$student_success = ($_POST['student_success'])? ☒ : ☐ ;


// Maintain line breaks from the textarea
foreach ($_POST as &$value) {
    $value = nl2br($value);
}

// Creates the html for motions based on how many there are
$motionHTML2 = '';
$multipleMotions = false;
for($i=2; $i<11; $i++) {
	if($_POST['motion' . $i] != "" && $_POST['motion' . $i] != null) {
		$motionHTML2 .= '</table><p class="p-p1">&nbsp;</p><p class="p-p3">Motion ';
		$motionHTML2 .= $i;
		$motionHTML2 .= '</p><table class="t-table"><col class="tc-table2_a"></col><tr class="tr-table1_"><td class="td-table1_a"><p class="p-p4">' ;
		$motionHTML2 .= nl2br($_POST['motion' . $i]);
		$motionHTML2 .= '</p></td></tr></table>';
		$multipleMotions = true;
	}
}

// Only call it motion 1 if there are multiple motions
if($multipleMotions){
	$motionHTML = '</table><p class="p-p1">&nbsp;</p><p class="p-p3">Motion 1';
} else {
	$motionHTML = '</table><p class="p-p1">&nbsp;</p><p class="p-p3">Motion';
}
$motionHTML .= '</p><table class="t-table"><col class="tc-table2_a"></col><tr class="tr-table1_"><td class="td-table1_a"><p class="p-p4">';
$motionHTML .= $_POST['motion1'];
$motionHTML .= '</p></td></tr></table>';
$motionHTML .= $motionHTML2;

if (strlen($_POST["executive_summary"]) < 2) {
    $_POST["executive_summary"] = "<br>";
}

if (strlen($_POST["supplementary"]) < 2) {
    $_POST["supplementary"] .= "<br><br><br>";
} else if (strlen($_POST["supplementary"]) < 100) {
    $_POST["supplementary"] .= "<br><br>";
}

if (strlen($_POST["approval_route"]) < 2) {
    $_POST["approval_route"] = "<br><br><br><br><br>";
} else if (strlen($_POST["approval_route"]) < 100) {
    $_POST["approval_route"] .= "<br><br><br><br>";
} else if (strlen($_POST["approval_route"]) < 200) {
    $_POST["approval_route"] .= "<br><br><br>";
} else if (strlen($_POST["approval_route"]) < 300) {
    $_POST["approval_route"] .= "<br><br>";
} else if (strlen($_POST["approval_route"]) < 400) {
    $_POST["approval_route"] .= "<br>";
}
// String contains all CSS and HTML to form the pdf
$html = <<<EOD
<html><head>
<style>
.calibre {
    display: block;
    font-size: 1.125em;
    line-height: 1.2;
    margin-bottom: 0;
    margin-left: 5pt;
    margin-right: 5pt;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0
    }
.calibre1 {
    display: list-item;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0
    }
.calibre2 {
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0
    }
.p-p {
    display: block;
    font-family: helvetica;
    font-size: 0.88889em;
    font-weight: bold;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    word-wrap:normal;
    text-align: center
    }
.p-p1 {
    display: block;
    font-family: helvetica;
    font-size: 0.88889em;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: left;
    word-wrap:normal;
    text-indent: 0.318cm
    }
.p-p2 {
    display: block;
    font-family: helvetica;
    font-size: 0.88889em;
    font-weight: bold;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    word-wrap:normal;
    text-align: left
    }
.p-p3 {
    display: block;
    font-family: helvetica;
    font-size: 0.88889em;
    font-weight: bold;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: left;
    word-wrap:normal;
    text-indent: 0.318cm
    }
.p-p4 {
    display: block;
    font-family: helvetica;
    font-size: 0.88889em;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    word-wrap:normal;
    text-align: left

    }
.p-p5 {
    display: block;
    font-family: helvetica;
    font-size: 0.88889em;
    margin-bottom: 0;
    margin-left: 0.318cm;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: left;
    word-wrap:normal;
    text-indent: 0
    }
.p-p6 {
    display: block;
    font-family: helvetica;
    font-size: 0.88889em;
    margin-bottom: 0.212cm;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    word-wrap:normal;
    text-align: left
    }
.p-p7 {
    display: block;
    font-family: "Arial (W1)";
    font-size: 0.88889em;
    font-style: italic;
    margin-bottom: 0;
    margin-left: 0.63cm;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: left;
    word-wrap:normal;
    text-indent: -0.63cm
    }
.p-p8 {
    display: block;
    font-family: "Arial (W1)";
    font-size: 0.88889em;
    font-style: italic;
    margin-bottom: 0;
    margin-left: 1.27cm;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: left;
    word-wrap:normal;
    text-indent: 0
    }
.p-p9 {
    display: block;
    font-family: helvetica;
    font-size: 0.88889em;
    font-style: italic;
    margin-bottom: 0.494cm;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0.494cm;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    word-wrap:normal;
    text-align: left
    }
.p-p10 {
    display: block;
    font-size: 0.88889em;
    margin-bottom: 0;
    margin-left: 1.27cm;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: left;
    word-wrap:normal;
    text-indent: -0.953cm
    }
.p-p11 {
    display: block;
    font-family: helvetica;
    font-size: 0.88889em;
    margin-bottom: 0;
    margin-left: 1.27cm;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: left;
    word-wrap:normal;
    text-indent: 0
    }
.p-p12 {
    background-color: transparent;
    display: block;
    font-family: helvetica;
    font-size: 0.88889em;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    word-wrap:normal;
    text-align: left
    }
.p-p13 {
    background-color: transparent;
    display: block;
    font-family: helvetica;
    font-size: 0.88889em;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: left;
    word-wrap:normal;
    text-indent: 0.028cm
    }
.p-p14 {
    display: block;
    font-family: helvetica;
    font-size: 0.88889em;
    font-weight: bold;
    margin-bottom: 0;
    margin-left: 1.27cm;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: left;
    word-wrap:normal;
    text-indent: -0.953cm
    }
.p-p15 {
    display: block;
    font-family: helvetica;
    font-size: 0.88889em;
    margin-bottom: 0.212cm;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: left;
    word-wrap:normal;
    text-indent: 0.319cm
    }
.p-p16 {
    display: block;
    font-size: 0.88889em;
    margin-bottom: 0;
    margin-left: 0.318cm;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: left;
    word-wrap:normal;
    text-indent: 0
    }
.p-p17 {
    display: block;
    font-family: helvetica;
    font-size: 0.51852em;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: left;
    word-wrap:normal;
    text-indent: 0.318cm
    }
.p-standard {
    display: block;
    font-size: 0.88889em;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    word-wrap:normal;
    text-align: left
    }
.s-internet_20_link {
    color: #00f;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    word-wrap:normal;
    text-decoration: underline
    }
.s-t {
    font-family: "MS Gothic", serif;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0
    }
.s-t1 {
    font-family: helvetica;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0
    }
.s-t2 {
    font-family: helvetica;
    font-style: italic;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0
    }
.s-t3 {
    font-family: helvetica;
    font-weight: bold;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0
    }
.s-t4 {
    font-family: helvetica;
    font-style: italic;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-decoration: underline
    }
.s-t5 {
    font-family: helvetica;
    font-style: italic;
    font-weight: bold;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-decoration: underline
    }
.s-t6 {
    font-family: "MS Gothic", serif;
    font-size: 0.75em;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0
    }
.s-t7 {
    font-family: helvetica;
    font-size: 0.75em;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0
    }
.t-table {
    border-collapse: collapse;
    border-spacing: 2px;
    table-layout: fixed;
    display: table;
    margin-bottom: 0;
    margin-left: 0.309cm;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-indent: 0;
    page-break-inside: auto;
    width: 18.733cm
    }
.tc-table1_a {
    display: table-column;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    width: 25%
    }
.tc-table1_b {
    display: table-column;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    width: 75%
    }
.tc-table2_a {
    display: table-column;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    width: 18.733cm
    }
.tc-table4_a {
    display: table-column;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    width: 5.801cm
    }
.tc-table4_b {
    display: table-column;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    width: 12.931cm
    }
.tc-table6_b {
    display: table-column;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    width: 6.886cm
    }
.tc-table6_c {
    display: table-column;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    width: 6.033cm
    }
.td-table1_a {
    border-bottom-color: #000;
    border-bottom-style: solid;
    border-bottom-width: 0.5pt;
    border-left-color: #000;
    border-left-style: solid;
    border-left-width: 0.5pt;
    border-right-color: #000;
    border-right-style: solid;
    border-right-width: 0.5pt;
    border-top-color: #000;
    border-top-style: solid;
    border-top-width: 0.5pt;
    display: table-cell;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0.199cm;
    padding-right: 0.191cm;
    padding-top: 0;
    text-align: inherit;
    vertical-align: top
    }
.tr-table1_ {
    display: table-row;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    page-break-inside:auto;
    vertical-align: middle
    }
.wwnum37_ {
    display: block;
    font-family: Symbol, sans-serif;
    list-style-type: disc;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 2em;
    padding-right: 0;
    padding-top: 0
    }
.wwnum37_1 {
    display: block;
    font-family: "Courier New", sans-serif;
    list-style-type: circle;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 2em;
    padding-right: 0;
    padding-top: 0
    }
.wwnum41_ {
    display: block;
    list-style-type: decimal;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 2em;
    padding-right: 0;
    padding-top: 0
    }
.Hcalibre {
    display: block;
    font-size: 1.125em;
    line-height: 1.2;
    margin-bottom: 0;
    margin-left: 5pt;
    margin-right: 5pt;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0
    }
.Hcalibre1 {
    display: block;
    line-height: 1.2;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0
    }
.Hp-p {
    display: block;
    font-family: Arial, serif;
    font-size: 0.88889em;
    font-weight: bold;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: center
    }
.Hp-p1 {
    display: block;
    font-family: "Arial (W1)";
    font-size: 0.88889em;
    line-height: 1.2;
    margin-bottom: 0.318cm;
    margin-left: 5.239cm;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: right;
    text-indent: 0
    }
.Hp-p2 {
    display: block;
    font-family: "Arial (W1)";
    font-size: 1.25926em;
    line-height: 1.2;
    margin-bottom: 0.212cm;
    margin-left: 0;
    margin-right: 0.3cm;
    margin-top: 0.423cm;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0;
    text-align: right;
    text-indent: 0
    }
.Hs-placeholder_20_text {
    color: #808080;
    line-height: 1.2;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0
    }
.Hs-t {
    font-family: Arial, serif;
    line-height: 1.2;
    margin-bottom: 0;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
    padding-bottom: 0;
    padding-left: 0;
    padding-right: 0;
    padding-top: 0
    }

@page {
    margin-bottom: 5pt;
    margin-top: 80pt
    }
body {
	font-size: 11pt;
}


</style>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" /><title>Unknown</title></head><body>
<header style="position: fixed;top: -115px; left: 0px; right: 0px;">
<div class="Hcalibre" style="display: inline;" id="calibre_link-0">
<p class="Hp-p">&nbsp;</p>
<p class="p-p1" style="text-align: right;font-size: 11pt;" align="right"><img align="left" src="uLogo.png" alt="governance" height="33 width="238"><b><u>BOARD OF GOVERNORS</u></b></p>
<p class="p-p4" style="text-align: right;" align="right">For the Meeting of {$formatDate}</p>
<hr style="border-top: 0px;">
<p class="p-p4" style="text-align: right;font-size: 14pt;" align="right">
Item No.{$_POST['item_no']}</p>

</div>
</header>
<div class="calibre" id="calibre_link-0">
<p class="p-p" style="font-family: helvetica">Governance Executive Summary</p>
<p class="p-p">Action Item</p>
<p class="p-p1">&nbsp;</p>
<table class="t-table"><col class="tc-table1_a" ></col>
<col class="tc-table1_b"></col>
<tr class="tr-table1_"><td class="td-table1_a" style="width:25%;"><p class="p-p2">Agenda Title</p>
</td>
<td class="td-table1_a" style="width:75%;"><p class="p-p2">{$_POST["title"]}</p>
</td>
</tr>
</table>
{$motionHTML}
<p class="p-p5">&nbsp;</p>
<p class="p-p3">Item</p>
<table class="t-table"><col class="tc-table4_a"></col>
<col class="tc-table4_b"></col>
<tr class="tr-table1_"><td class="td-table1_a" style="width:25%;"><p class="p-p4">Action Requested</p>
<!-- These are where the checkboxes go. Set variable to either ☒ or ☐ -->
<td class="td-table1_a" style="width:75%;"><p class="p-standard"><span class="s-t" style="font-family: dejavu sans">{$approval}</span><span class="s-t1">&nbsp;Approval</span><span class="s-t" style="font-family: dejavu sans">&nbsp;&nbsp;&nbsp;&nbsp;{$recommendation}</span><span class="s-t1">&nbsp;Recommendation</span>&nbsp;</p>
</td>
</tr>
<tr class="tr-table1_"><td class="td-table1_a" style="width:25%;"><p class="p-p4">Proposed by</p>
</td>
<td class="td-table1_a" style="width:75%;"><p class="p-p4">{$_POST["proposed"]}</p>
</td>
</tr>
<tr class="tr-table1_"><td class="td-table1_a" style="width:25%;"><p class="p-p4">Presenter(s)</p>
</td>
<td class="td-table1_a" style="width:75%;">
<p class="p-p4">{$_POST["presenters"]}</p>
</td>
</tr>
</table>

<div style="page-break-inside:avoid;">
<p class="p-p5">&nbsp;</p>
<p class="p-p3">Details</p>
<table class="t-table"><col class="tc-table4_a"></col>
<col class="tc-table4_b"></col>
<tr class="tr-table1_"><td class="td-table1_a" style="width:25%;"><p class="p-p4">Responsibility</p>
</td>
<td class="td-table1_a" style="width:75%;"><p class="p-standard"><span class="s-t2">{$_POST["responsibility"]}</span>&nbsp;</p>
</td>
</tr>
</table>
</div>

<table class="t-table"><col class="tc-table4_a">
<tr class="tr-table1_"><td class="td-table1_a" style="width:25%;"><p class="p-standard"><span class="s-t1">The Purpose of the Proposal is (</span><span class="s-t2">please be specific</span><span class="s-t1">)</span>&nbsp;</p>
</td>
<td class="td-table1_a" style="width:75%;"><p class="p-p4">{$_POST["purpose"]}</p>
</td>
</tr>
</table>
<div style="width: 100.8%;border-left: 1px solid black; position: relative;border-right: 1px solid black; border-bottom: 0.5px solid black;border-top: 0.5px solid black;">
<div style="position:absolute;top: 0;left:0; width: 22%;padding-left: 0.199cm;padding-right: 0.191cm;">
<p class="p-p4">
<p class="p-p4">Executive Summary</p>
</p>
</div>
<div style="width: 73.6%;border-left: 1px solid black;padding-left: 0.199cm;padding-right: 0.191cm; margin-left: 24.9%;">
<p class="p-p4" style="text-align: justify;padding-right: 0.191cm;">
{$_POST["executive_summary"]}
</p>
</div>
</div>

<div style="width: 100.8%;border-left: 1px solid black; position: relative;border-right: 1px solid black; border-bottom: 1px solid black;border-top: 0.5px solid black;">
<div style="position:absolute;top: 0;left:0; width: 22%;padding-left: 0.199cm;padding-right: 0.191cm;">
<p class="p-p4">
<p class="p-p4">Supplementary Notes and context</p>
</p>
</div>
<div style="width: 73.6%;border-left: 1px solid black;padding-left: 0.199cm;padding-right: 0.191cm; margin-left: 24.9%;">
<p class="p-p4" style="text-align: justify;padding-right: 0.191cm;">
{$_POST["supplementary"]}
</p>
</div>
</div>

<div id="e-table" style="page-break-inside: avoid;">
<p class="p-p5">&nbsp;</p>
<p class="p-p10"><span class="s-t3">Engagement and Routing </span><span class="s-t1">(Include meeting dates)</span>&nbsp;</p>
<table class="t-table" style="page-break-inside: avoid;"><col class="tc-table1_a"></col>
<col class="tc-table1_b"></col>
<tr class="tr-table1_"><td class="td-table1_a" rowspan="3" style="width:25%;"><p class="p-p4">&nbsp;</p>
<p class="p-p4">Consultation and Stakeholder Participation </p>
<p class="p-p4">(parties who have seen the proposal and in what capacity)</p>
<p class="p-p4">&nbsp;</p>

</td>
<td class="td-table1_a" style="width:75%;height:50px;"><p class="p-standard"><span class="s-t4">Those who are actively </span><span class="s-t5">participating</span><span class="s-t4">:</span>&nbsp;</p>
<ul class="wwnum37_"><li class="calibre1"><p class="p-p4">{$_POST["participating"]}</p>
</li>
</ul>
</td>
</tr>
<tr class="tr-table1_"><td class="td-table1_a" style="width:75%;height:50px;"><p class="p-standard"><span class="s-t4">Those who have been </span><span class="s-t5">consulted</span><span class="s-t4">:</span>&nbsp;</p>
<ul class="wwnum37_"><li class="calibre1"><p class="p-p4">{$_POST["consulted"]}</p>
</li>
</ul>
</td>
</tr>
<tr class="tr-table1_"><td class="td-table1_a" style="width:75%;align:height:50px;"><p class="p-standard"><span class="s-t4">Those who have been </span><span class="s-t5">informed</span><span class="s-t4">:</span>&nbsp;</p>
<ul class="wwnum37_"><li class="calibre1"><p class="p-p4">{$_POST["informed"]}</p>
</li>
</ul>
</td>
</tr>
</table>
</div>

<div style="width: 100.8%;border-left: 1px solid black; position: relative;border-right: 1px solid black; border-bottom: 1px solid black;border-top: 0.5px solid black;">
<div style="position:absolute;top: 0;left:0; width: 22%;padding-left: 0.199cm;padding-right: 0.191cm;">
<p class="p-p12">Approval Route (Governance)</p>
<p class="p-p12">(including meeting dates)</p>
</div>
<div style="width: 73.6%;border-left: 1px solid black;padding-left: 0.199cm;padding-right: 0.191cm; margin-left: 24.9%;">
<p class="p-p4" style="text-align: justify;padding-right: 0.191cm;">
{$_POST["approval_route"]}
</p>
</div>
</div>


<div id="e-table" style="page-break-inside:avoid;">
<p class="p-p5">&nbsp;</p>
<p class="p-p14">Strategic Alignment</p>
<table class="t-table"><col class="tc-table1_a"></col>
<col class="tc-table6_b"></col>
<col class="tc-table6_c"></col>
<tr class="tr-table1_"><td class="td-table1_a" style="width:25%;"><p class="p-standard"><span class="s-t1">Alignment with </span><span class="s-t2">For the Public Good</span>&nbsp;</p>
</td>
<td class="td-table1_a" colspan="2" style="width:75%;"><p class="p-p4">{$_POST["public_good"]}</p>
</td>
</tr>
</table>
</div>
<p class="p-p1"></p>
<table class="t-table" style="page-break-inside: avoid;">
<tr class="tr-table1_"><td class="td-table1_a" rowspan="2" style="width:25%;"><p class="p-p4">Alignment with Institutional Risk Indicator</p>
</td>
<td class="td-table1_a" colspan="2" style="width:75%;"><p class="p-p4">Please note below the specific institutional risk(s) this proposal is addressing.</p>
</td>
</tr>
<tr class="tr-table1_">

<td class="td-table1_a" style="width:37.5%;font-family:dejavu sans;"><p class="p-standard"><span class="s-t6" style="font-family:dejavu sans;">{$enrolment_management}</span><span class="s-t7"> Enrolment Management</span>&nbsp;</p>
<p class="p-standard"><span class="s-t6" style="font-family:dejavu sans;">{$faculty_staff}</span><span class="s-t7"> Faculty and Staff</span>&nbsp;</p>
<p class="p-standard"><span class="s-t6" style="font-family:dejavu sans;">{$funding_management}</span><span class="s-t7"> Funding and Resource Management</span>&nbsp;</p>
<p class="p-standard"><span class="s-t6" style="font-family:dejavu sans;">{$it_services}</span><span class="s-t7"> IT Services, Software and Hardware</span>&nbsp;</p>
<p class="p-standard"><span class="s-t6" style="font-family:dejavu sans;">{$leadership_change}</span><span class="s-t7"> Leadership and Change</span>&nbsp;</p>
<p class="p-standard"><span class="s-t6" style="font-family:dejavu sans;">{$physical_infrastructure}</span><span class="s-t7"> Physical Infrastructure</span>&nbsp;</p>
</td>
<td class="td-table1_a" style="width:37.5%;"><p class="p-standard"><span class="s-t6" style="font-family:dejavu sans;">{$relationship_stakeholders}</span><span class="s-t7"> Relationship with Stakeholders</span>&nbsp;</p>
<p class="p-standard"><span class="s-t6" style="font-family:dejavu sans;">{$reputation}</span><span class="s-t7"> Reputation</span>&nbsp;</p>
<p class="p-standard"><span class="s-t6" style="font-family:dejavu sans;">{$research_enterprise}</span><span class="s-t7"> Research Enterprise</span>&nbsp;</p>
<p class="p-standard"><span class="s-t6" style="font-family:dejavu sans;">{$safety}</span><span class="s-t7"> Safety</span>&nbsp;</p>
<p class="p-standard"><span class="s-t6" style="font-family:dejavu sans;">{$student_success}</span><span class="s-t7"> Student Success</span>&nbsp;</p>
</td>
</tr>
</table>
<p class="p-p1"></p>
<table class="t-table" style="page-break-inside: avoid;">
<tr class="tr-table1_"><td class="td-table1_a" style="width:25%;"><p class="p-p4">Legislative Compliance and jurisdiction</p>
</td>
<td class="td-table1_a" colspan="2" style="width:75%;"><p class="p-p4">{$_POST["compliance"]}</p>
</td>
</tr>
</table>


</div>


</body></html>
EOD;


// Create a PDF from the html string
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output the generated PDF to Browser
echo base64_encode($dompdf->output());
?>
