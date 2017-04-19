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

$hlFontColor = $config->getValue("highlightFontColor");
$hl = $config->getValue("highlightColor");
$brighterhl = $hl1 = ($config->getValue("brighterHighlightColor") != "") ? $config->getValue("brighterHighlightColor") : adjustBrightness($hl, + 100);
$hl1 = ($config->getValue("highlightColor1") != "") ? $config->getValue("highlightColor1") : adjustBrightness($hl, -15);
$hl2 = ($config->getValue("highlightColor2") != "") ? $config->getValue("highlightColor2") : adjustBrightness($hl, -30);
$hlDark = adjustBrightness($hl1, -50);
$hlVeryDark = adjustBrightness($hl1, -75);
$inputColor = ($config->getValue("inputColor") != "") ? $config->getValue("inputColor") : adjustBrightness($hl, -50);
$inputColorDark = ($config->getValue("inputColor") != "") ? adjustBrightness($config->getValue("inputColor"), -10) : adjustBrightness($hl, -60);
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

.highlightsBackground0 {
    background: $hl;
    color: $hlFontColor;
}

.highlightsBackground1 {
    background: $hl1;
    color: $hlFontColor;
}

.highlightsBackground2 {
    background: $hl2;
    color: $hlFontColor;
}

input.highlightsBackground0 {
    -webkit-box-shadow: 0 0 0px 1000px $hl inset;
    -webkit-text-fill-color: $hlFontColor !important;
}

input.highlightsBackground1 {
    -webkit-box-shadow: 0 0 0px 1000px $hl1 inset;
    -webkit-text-fill-color: $hlFontColor !important;
}

input.highlightsBackground2 {
    -webkit-box-shadow: 0 0 0px 1000px $hl2 inset;
    -webkit-text-fill-color: $hlFontColor !important;
}

#nav li a:hover {
    background: $hlDark !important;
}

.underlined:hover {
    text-decoration: underline !important;
}

h1 {
    color: $hl2;
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

input:focus, textarea:focus {
    outline: none;
    border: 1px solid $inputColor !important;
}

input[type=button], input[type=submit], .button, .button:visited, .ui-button, .button:link , :not(.mce-btn) > button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]) {
    background: $inputColor !important;
}

input[type=button]:active, input[type=submit]:active, .button:active, .ui-button:active, .ui-state button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]):active {
    background: $inputColorDark !important;
    color: $hlFontColor !important;
}

input[type=button]:hover, input[type=submit]:hover, .button:hover, .ui-button:hover, button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]):hover, :not(.mce-btn button) {
    background: $inputColorDark !important;
    color: $hlFontColor !important;
}

input[type=button], input[type=submit], .button, .button:visited, .ui-button, .button:link , button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]) {
    color:#ffffff !important;
    border-radius: 20px;
}

input[disabled] , input[disabled]:hover , input[disabled]:active , select[disabled], button[disabled], a.disabledButton, a.disabledButton:hover, a.disabledButton:active {
    color:#ffffff !important;
}

select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    padding-left: 0;
    border-image: url("../../{$iconPathHighlighted}border.png") 1 27 1 12 round stretch;
    border-right-width: 27px;
    border-top-width:1px;
    border-bottom-width:1px;
    border-left-width:12px;
    height: 25px;
}

select::-ms-expand {
    display: none;
}

select:focus {
    outline: none;
    padding-left: 0;
    border-image: url("../../{$iconPathHighlighted}border_focus.png") 1 27 1 12 round stretch;
    border-right-width: 27px;
    border-top-width:1px;
    border-bottom-width:1px;
    border-left-width:12px;
}

select[size], select[size]:focus {
    background: none;
    border-image: none;
    border: 1px solid #AAA !important;
    padding: 8px;
    height: auto;
}

select[size]:focus {
    border: 1px solid $inputColor !important;
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

/* Global Input */

#globalSearchResults {
    border: 0 solid $hlVeryDark;
    border-bottom: 0;
    background: $hlDark;
}

.globalSearchResultsGroup {
    background: $hlVeryDark;
    border-right: 1px solid $hlVeryDark;
}

.globalSearchResultsRows {
    background: $hlDark;
}

.globalSearchResultsBorder {
    border-bottom: 1px $hlVeryDark solid;
}

.globalSearchResultsCell {
    background: $hlDark;
}

.globalSearchResultsMoreRows {
    background: $hlDark;
}

.showMore {
    background: $hlDark;
}

.showMore > #showMoreResults {
    cursor: pointer;
    color: $brighterhl !important;
}

.showMore > #showMoreResults:hover {
    color: #FFFFFF !important;
}

/* JQuery UI */

.ui-tabs .ui-tabs-nav li.ui-tabs-selected a{
	color: $hlFontColor;
	font-weight:bold;
	background: $inputColor !important;
	border-radius:15px;
}

.ui-datepicker {
    border: 1px solid $hl !important;
}

.ui-progressbar .ui-progressbar-value {
    background: $hl;
}

.ui-tabs .ui-tabs-nav li a {
    background: $hl;
    color: $hlFontColor;
}

.ui-tabs .ui-tabs-nav li:hover:not(.ui-state-disabled) a{
    background: $hl2;
}

.ui-widget-content a {
    color: $hl;
}

.ui-dialog .ui-dialog-titlebar {
	background: $hlDark;
}

.ui-widget-header .ui-icon {
    background-image: url("../smoothness/images/ui-icons_ffffff_256x240.png");
}

.ui-state-hover .ui-icon, .ui-state-focus .ui-icon {
    background-image: url("../smoothness/images/ui-icons_454545_256x240.png"); 
}

li.ui-menu-item:hover > a, li.ui-menu-item > a#ui-active-menuitem, li.ui-menu-item > a.ui-state-focus {
    background: $inputColor !important;
}

/* Other */

.purpleInfo a, .purpleInfo a:visited, .inlinePurpleInfo a, .inlinePurpleInfo a:visited {
    color: $hl;
}

.large_card .card_photo > img {
    background: $hl2;
}

.small_card:hover, .small_card_hover {
    background: $hlVeryDark !important;
    color: #ffffff !important;
}

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
    color: $hlVeryDark;
}

#sideToggle:hover {
    background: $hl2;
}

#header ul a {
    color: $hlTextColor;
}

#submenu li {
    background: $hl;
}

#submenu ul li.selected a, #submenu ul a:hover {
    background: $hl2;
}
	
#submenu .highlights-tab {
    color: $brighterhl;
}

#header li.selected a, #header li a:hover {
    font-weight:normal;
    background: $hl1;
}

#allTabs:hover {
    background: $hl2;
}

#allTabsDropdown {
    background: $hl2;
}

#allTabsDropdown a {
    color: #FFFFFF;
}

#allTabsDropdown a:hover {
    background: $hlDark;    
}

a {
    color: $hl1;
}

a:hover {
    color: $hl1;
}

a:visited {
    color: $hl1;
}

a:active {
    color: $hl1;
}

.highlightsBackground0 a, 
.highlightsBackground1 a, 
.highlightsBackground2 a {
    color: $hlFontColor;
}

.highlightsBackground0, 
.highlightsBackground1, 
.highlightsBackground2 {
    color: $hlFontColor;
}

::-webkit-input-placeholder {
   color: $hlFontColor;
}

:-moz-placeholder { /* Firefox 18- */
   color: $hlFontColor;  
}

::-moz-placeholder {  /* Firefox 19+ */
   color: $hlFontColor;  
}

:-ms-input-placeholder {  
   color: $hlFontColor;  
}

/* TinyMCE */
.mce-btn button, .mce-btn button:hover {
    background: transparent !important;
}

EOF;
?>
