<?php
header('Content-Type: text/css');
if(file_exists("../../test.tmp")){
    define("TESTING", true);
}
else{
    define("TESTING", false);
}
require_once("../../config/ForumConfig.php");

$hl = $config->getValue("highlightColor");
$brighterhl = $config->getValue("brighterHighlightColor");
$hc = $config->getValue("headerColor");
$iconPath = $config->getValue("iconPath");
$iconPathHighlighted = $config->getValue("iconPathHighlighted");

echo <<<EOF

/* General */

.highlights-text {
    color: $hl !important;
}

.highlights-text-hover:hover {
    color: $hl !important;
}

.highlights-background {
    background: $hl !important;
    font-color: #FFFFFF !important;
}

.highlights-background-hover:hover {
    background: $hl !important;
    font-color: #FFFFFF !important;
}

.underlined:hover {
    text-decoration: underline !important;
}

h1 {
    color:#37B0E1;
}


h2, h3, h4, h5, h6, h7 {
    color: $hc !important;
}

/* Input */

.selected .highlights-tab, .highlights-tab:hover {
    border-width: 0px 0 0 0;
    border-style: solid;
    border-color: $hl;
    background:transparent;
}

.actions.selected .highlights-tab {
    color: white !important;
}

.highlights-tab:hover {
    color: white !important;
}

select {
    color: $hl !important;
}

select option {
    color: $hl !important;
}

input:focus, textarea:focus, select:focus {
    outline: none;
    border: 1px solid $brighterhl !important;
}

input[type=button]:active, input[type=submit]:active, .button:active, .ui-button:active, .ui-state button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]):active {
    color: $hl !important;
}

input[type=button]:hover, input[type=submit]:hover, .button:hover, .ui-button:hover, button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]):hover {
    color: $hl !important;
}

input[type=button], input[type=submit], .button, .button:visited, .ui-button, .button:link , button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]) {
    color:#ffffff !important;
    border-radius: 20px;
}

input[disabled] , input[disabled]:hover , input[disabled]:active , select[disabled], button[disabled], a.disabledButton, a.disabledButton:hover, a.disabledButton:active {
    color:#ffffff !important;
}

/* Icons */
.edit-icon {
    cursor: pointer;
    display: inline-block;
    vertical-align: bottom;
    width: 20px;
    height: 17px;
    margin-left: 1px;
    margin-right:1px;
    background: url("../../{$iconPath}glyphicons_150_edit_small.png");
}

.edit-icon:hover {
    background: url("../../{$iconPathHighlighted}glyphicons_150_edit_small.png");
}

.delete-icon {
    cursor: pointer;
    display: inline-block;
    vertical-align: bottom;
    width: 16px;
    height: 16px;
    margin-left: 1px;
    margin-right:1px;
    background: url("../../{$iconPath}glyphicons_207_remove_2.png");
}

.delete-icon:hover {
    background: url("../../{$iconPathHighlighted}glyphicons_207_remove_2.png");
}

/* JQuery UI */

.ui-tabs .ui-tabs-nav li.ui-tabs-selected a{
	color: $hl;
	font-weight:bold;
	background:#76C9EB;
	border-radius:15px;
}

.ui-datepicker {
    border: 1px solid $hl !important;
}

.ui-progressbar .ui-progressbar-value {
    background: $hl;
}

.ui-tabs .ui-tabs-nav li:hover:not(.ui-state-disabled) a{
    color: $hl;
}

.ui-widget-content a {
    color: $hl;
}

.ui-dialog .ui-dialog-titlebar {
	background: #018183;
}

li.ui-menu-item:hover > a, li.ui-menu-item > a#ui-active-menuitem, li.ui-menu-item > a.ui-state-focus {
    background: #4AB4BD !important;
}

/* Other */

.purpleInfo a, .purpleInfo a:visited, .inlinePurpleInfo a, .inlinePurpleInfo a:visited {
    color: $hl;
}
/*
.small_card:hover, .small_card_hover {
    background: $hl !important;
    color: #ffffff !important;
}
*/
.qtip-light .qtip-content a:hover {
    color: $hl;
    text-decoration: none;
}

#bodyContent a.extiw,#bodyContent a.extiw:active {
    color: $hl;
    background: none;
    padding: 0;
}

#bodyContent a.external {
    color: $hl;
}

#sideToggle {
    color: $hl;
}

#sideToggle:hover {
    background: #99D6F2;
}

a {
    color: $hl;
}

a:hover {
    color: $hl;
}

a:visited {
    color: $hl;
}

a:active {
    color: $hl;
}

EOF;
?>
