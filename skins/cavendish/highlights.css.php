<?php

header('Content-Type: text/css');
if(file_exists("../../test.tmp")){
    define("TESTING", true);
}
else{
    define("TESTING", false);
}
require_once("../../config/Config.php");

$hl = $config->getValue("highlightColor");
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

h1, h2, h3, h4, h5, h6, h7 {
    color: $hc !important;
}

/* Input */

.selected .highlights-tab, .highlights-tab:hover {
    border-width: 3px 0 0 0;
    border-style: solid;
    border-color: $hl;
    color: $hl !important;
}

input:focus, textarea:focus {
    outline: none;
    border: 1px solid $hl !important;
	box-shadow: inset 0 0 2px $hl;
    -moz-box-shadow: inset 0 0 2px $hl;
    -webkit-box-shadow: inset 0 0 2px $hl;
}

input[type=button]:active, input[type=submit]:active, .button:active, .ui-button:active, .ui-state button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]):active {
    color: $hl !important;
}

input[type=button]:hover, input[type=submit]:hover, .button:hover, .ui-button:hover, button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]):hover {
    color: $hl !important;
}

input[type=button], input[type=submit], .button, .button:visited, .ui-button, .button:link , button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]) {
    color:#606060 !important;
}

input[disabled] , input[disabled]:hover , input[disabled]:active , select[disabled], button[disabled], a.disabledButton, a.disabledButton:hover, a.disabledButton:active {
    color:#606060 !important;
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
	background: $hl;
}

li.ui-menu-item:hover > a, li.ui-menu-item > a#ui-active-menuitem, li.ui-menu-item > a.ui-state-focus {
    background: $hl !important;
}

/* Other */

.purpleInfo a, .purpleInfo a:visited, .inlinePurpleInfo a, .inlinePurpleInfo a:visited {
    color: $hl;
}

.small_card:hover, .small_card_hover {
    background: $hl !important;
    color: #ffffff !important;
}

.qtip-light .qtip-content a:hover {
    color: $hl;
    text-decoration: none;
}

ul.tagit li.tagit-choice {
    background: white !important;
    color: $hl !important;
    border-color: $hl !important;
}

ul.tagit li.tagit-choice .tagit-label:not(a) {
    color: $hl;
}

ul.tagit li.tagit-choice .tagit-close .text-icon {
    color: $hl;
}

#bodyContent a.extiw,#bodyContent a.extiw:active {
    color: $hl;
    background: none;
    padding: 0;
}

#bodyContent a.external {
    color: $hl;
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
