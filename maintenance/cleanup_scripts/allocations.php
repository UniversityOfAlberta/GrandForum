<?php
include_once('../commandLine.inc');

$sql = "DROP TABLE `mw_allocations`";
DBFunctions::execSQL($sql, true);

?>
