<?php
include_once('../commandLine.inc');

//change the schema
$sql = "RENAME TABLE `mw_an_votes` TO `grand_feature_votes` ;";
DBFunctions::execSQL($sql, true);
  
?>
