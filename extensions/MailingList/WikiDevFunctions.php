<?php

function insertNonPrefix($dbw, $table, $newData) {
	if (count($newData) == 0) {
		return;
	}
	
	$sql = "INSERT INTO $table (" . implode( ',', array_keys($newData[0])) . ") VALUES ";
	
	$first = true;
	
	foreach ( $newData as $row ) {
		if ( $first ) {
			$first = false;
		} else {
			$sql .= ',';
		}
		$sql .= '(' . $dbw->makeList( $row ) . ')';
	}
	
	return $dbw->query($sql);
}

function updateNonPrefix($dbw, $table, $values, $conds) {
	$sql = "UPDATE $table SET " . $dbw->makeList( $values, LIST_SET );
	if ( $conds != '*' ) {
		$sql .= " WHERE " . $dbw->makeList( $conds, LIST_AND );
	}
	
	return $dbw->query( $sql );
}	

?>
