<?php
include_once('../commandLine.inc');

$sql = "DROP TABLE `mw_bibtex_raw`";
DBFunctions::execSQL($sql, true);
  
?>
