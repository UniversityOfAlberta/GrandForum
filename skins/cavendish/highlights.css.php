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
$th = $config->getValue("topHeaderColor");
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
    color: #FFFFFF !important;
}

.highlights-background-hover:hover {
    background: $hl !important;
    color: #FFFFFF !important;
}

h1, h2, h3, h4, h5, h6, h7 {
    color: $hc !important;
}

/* Layout */

#topheader {
    background: $th;
}

#header {
    background: $th;
}

#submenu, #submenu li:not(.action) {
    background: $th !important;
}

#submenu ul a, #header ul a {
    color: #FFFFFF;
}

#submenu li.action a {
    color: $th !important;
}

#submenu li.action a:hover {
    color: #FFFFFF !important;
    background: $th;
}

#sideToggle, #allTabs {
    background: $th;
    border:none;
    color: #FFFFFF;
    border-top: 3px solid $th;
}

#sideToggle:hover, #allTabs:hover {
    color: $hl;
    background: $th;
    border-top: 3px solid $hl;
}

#side, #nav, #sideFooter {
    background: $th;
    color: #ccc;
}

#sideFooter a {
    color: $hl;
}

#bodyContent {
    border-color: $hl;
}

#globalSearchResults {
    border-color: $th;
}

#showMoreResults:hover {
    color: $hl !important;
}

.selected .highlights-tab, .highlights-tab:hover {
    border-width: 3px 0 0 0;
    border-style: solid;
    border-color: $hl;
    color: $hl !important;
}

#allTabsDropdown a:hover {
    color: $th !important;
}

/* Input */

input:focus, textarea:focus {
    outline: none;
    border: 1px solid $th !important;
	box-shadow: inset 0 0 2px $th;
    -moz-box-shadow: inset 0 0 2px $th;
    -webkit-box-shadow: inset 0 0 2px $th;
}

input[type=button]:active, input[type=submit]:active, .button:active, .dt-button, .ui-button:active, .ui-state button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]):active {
    color: $th !important;
    fill: $th !important;
}

input[type=button]:hover, input[type=submit]:hover, .button:hover,  .dt-button:hover, .ui-button:hover, :not(.mce-btn):not(.mce-window-head) > button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]):hover {
    color: $th !important;
    fill: $th !important;
}

input[type=button], input[type=submit], .button, .button:visited, .dt-button, .ui-button, .button:link , :not(.mce-btn):not(.mce-window-head) > button:not(#cboxPrevious):not(#cboxNext):not(#cboxSlideshow):not(#cboxClose):not([disabled]) {
    color:#606060 !important;
    fill:#606060 !important;
}

input[disabled] , input[disabled]:hover , input[disabled]:active, select[disabled], button[disabled], a.disabledButton, a.disabledButton:hover, a.disabledButton:active {
    color:#606060 !important;
    fill:#606060 !important;
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
    margin-left: 1px;
    margin-right:1px;
    background: url("../../{$iconPath}glyphicons_207_remove_2.png");
}

.delete-icon:hover {
    background: url("../../{$iconPathHighlighted}glyphicons_207_remove_2.png");
}

/* JQuery UI */

.ui-tabs .ui-tabs-nav li.ui-tabs-selected a{
	color: $th;
}

.ui-datepicker {
    border: 1px solid $th !important;
}

.ui-progressbar .ui-progressbar-value {
    background: $th;
}

.ui-tabs .ui-tabs-nav li:hover:not(.ui-state-disabled) a{
    color: $th;
}

.ui-widget-content a {
    color: $th;
}

.ui-dialog .ui-dialog-titlebar {
	background: $th;
}

li.ui-menu-item:hover > a, li.ui-menu-item > a#ui-active-menuitem, li.ui-menu-item > a.ui-state-focus {
    background: $th !important;
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
    color: $th;
    text-decoration: none;
}

ul.tagit li.tagit-choice {
    background: white !important;
    color: $th !important;
    border-color: $th !important;
}

ul.tagit li.tagit-choice .tagit-label:not(a) {
    color: $th;
}

ul.tagit li.tagit-choice .tagit-close .text-icon {
    color: $th;
}

ul.tagit li.tagit-choice.remove {
    background: #DDDDDD !important;
}

#bodyContent a.extiw,#bodyContent a.extiw:active {
    color: $th;
    background: none;
    padding: 0;
}

#bodyContent a.external {
    color: $th;
}

a {
    color: $th;
}

a:hover {
    color: $th;
}

a:visited {
    color: $th;
}

a:active {
    color: $th;
}

EOF;

?>
