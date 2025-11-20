<?php

header('Content-Type: text/css');
if(file_exists("../../test.tmp")){
    define("TESTING", true);
}
else{
    define("TESTING", false);
}
require_once("../../config/Config.php");

function adjustBrightness($hex, $steps) {
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));

    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
    }

    // Split into three parts: R, G and B
    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color) {
        $color   = hexdec($color); // Convert to decimal
        $color   = max(0,min(255,$color + $steps)); // Adjust color
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }

    return $return;
}

$hl = $config->getValue("highlightColor");
$hc = $config->getValue("headerColor");
$thc = $config->getValue("topHeaderColor");
$iconPath = $config->getValue("iconPath");
$iconPathHighlighted = $config->getValue("iconPathHighlighted");

$darkerHl = adjustBrightness($hl, -25);

echo <<<EOF

/* General */

#side, #nav, #sideFooter {
    background: #F8F8F8;
    color: #ccc;
}

#outerHeader {
    background: #F8F8F8;
}

#header {
    background: #F8F8F8;
}

#submenu, #submenu li:not(.action) {
    background: #F8F8F8 !important;
}

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

#globalSearchResults {
    border-color: $thc;
}

#showMoreResults:hover {
    color: $hl !important;
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
    border: 1px solid $hl;
}

input[type=button], input[type=submit], .button, .button:visited, .dt-button, .ui-button, .button:link , :not(.mce-btn):not(.mce-window-head) >  button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]) {
    border-color: {$hl} !important;
    background: {$hl} !important;
    transition: color .15s ease-in-out, 
                background-color .15s ease-in-out, 
                border-color .15s ease-in-out;
}

input[type=button]:active, input[type=submit]:active, .button:active, .dt-button, .ui-button:active, .ui-state button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]):active {
    color: white !important;
    border-color: {$darkerHl} !important;
    background: {$darkerHl} !important;
}

input[type=button]:hover, input[type=submit]:hover, .button:hover, .dt-button:hover, .ui-button:hover, :not(.mce-btn):not(.mce-window-head) >  button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]):hover {
    color: white !important;
    border-color: {$darkerHl} !important;
    background: {$darkerHl} !important;
}

input[disabled] , input[disabled]:hover , input[disabled]:active , select[disabled], button[disabled], a.disabledButton, a.disabledButton:hover, a.disabledButton:active {
    border-color: #CCC !important;
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

.copy-icon {
    cursor: pointer;
    display: inline-block;
    vertical-align: bottom;
    width: 17px;
    height: 17px;
    margin-left: 1px;
    margin-right:1px;
    background: url("../../{$iconPath}glyphicons_154_more_windows.png");
}

.copy-icon:hover {
    background: url("../../{$iconPathHighlighted}glyphicons_154_more_windows.png");
}

.delete-icon {
    cursor: pointer;
    display: inline-block;
    vertical-align: bottom;
    width: 16px;
    height: 16px;
    margin-left: 5px;
    margin-right:5px;
    background: url("../../{$iconPath}glyphicons_207_remove_2.png");
}

.delete-icon:hover {
    background: url("../../{$iconPathHighlighted}glyphicons_207_remove_2.png");
}

.move-icon {
    cursor: move;
    display: inline-block;
    vertical-align: bottom;
    width: 16px;
    height: 16px;
    margin-left: 1px;
    margin-right:1px;
    background: url("../../skins/icons/gray_dark/move_vertical_alt2_16x16.png");
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

#topheader {
    background: {$thc};
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

.qtip-content a {
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

.clicktooltip:hover {
    color: $hl;
}

.rte-autocomplete >li > a:hover, .rte-autocomplete > li > a:focus, .rte-autocomplete:hover > a, .rte-autocomplete:focus > a {
    background-color: $hl !important;
    background-image: none !important;
}

.rte-autocomplete >.active > a, .rte-autocomplete > .active > a:hover, .rte-autocomplete > .active > a:focus {
    background-color: $hl !important;
    background-image: none !important;
}

EOF;

?>
