<?php
$dir = __DIR__;

require_once("$dir/Classes/Patch.php");

// Hack to change to MYSQLI_ASSOC in doFetchRow
$objPatch = new Patch("$dir/includes/libs/rdbms/database/resultwrapper/MysqliResultWrapper.php");
$objPatch->redefineFunction("
    protected function doFetchRow() {
	\$array = \$this->result->fetch_array(MYSQLI_ASSOC); // Changed to MYSQLI_ASSOC
	\$this->checkFetchError();
	if ( \$array === null ) {
		return false;
	}
	return \$array;
}");
try{
    eval($objPatch->getCode());
} catch (Throwable $e) {
    //code to handle the exception or error
}

?>
