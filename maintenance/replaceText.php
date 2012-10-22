<?php
	$options = array('help');
	require_once( 'commandLine.inc' );
	
	if( isset( $options['help'] ) ) {
		showHelp();
		exit(1);
	}
	
	if( count( $args ) != 2){
		showHelp();
		exit(1);
	}
	$search = $args[0];
	$replace = $args[1];
	
	echo "Search: $search\nReplace: $replace\n";

	$sql = "SELECT * FROM mw_sentence WHERE content LIKE '%$search%'";
	$data = execSQLStatement($sql);
	foreach($data as $row){
		$sentence = str_replace("$search", $replace, $row['content']);
		$sentence = str_replace("'", "&#39;" ,$sentence);
		//echo "$sentence\n";
		$sql = "UPDATE mw_sentence 
			SET content=CONVERT('$sentence', BINARY)
			WHERE sen_id = '{$row['sen_id']}'";
		execSQLStatement($sql, true);
	}
	
	$sql = "SELECT * FROM mw_text WHERE old_text LIKE '%$search%'";
	$data = execSQLStatement($sql);
	foreach($data as $row){
		$sentence = str_replace("$search", $replace, $row['old_text']);
		$sentence = str_replace("'", "&#39;" ,$sentence);
		//echo "$sentence\n";
		$sql = "UPDATE mw_text
			SET old_text=CONVERT('$sentence', BINARY)
			WHERE old_id = '{$row['old_id']}'";
		execSQLStatement($sql, true);
	}
	
	$sql = "SELECT * FROM mw_an_extranamespaces WHERE nsName LIKE '%$search%'";
	$data = execSQLStatement($sql);
	foreach($data as $row){
		$sentence = str_replace("$search", $replace, $row['nsName']);
		$sentence = str_replace("'", "&#39;" ,$sentence);
		//echo "$sentence\n";
		$sql = "UPDATE mw_an_extranamespaces
			SET nsName='$sentence'
			WHERE nsId = '{$row['nsId']}'";
		execSQLStatement($sql, true);
	}
	
	$sql = "SELECT * FROM mw_page WHERE page_title LIKE '%$search%'";
	$data = execSQLStatement($sql);
	foreach($data as $row){
		$sentence = str_replace("$search", $replace, $row['page_title']);
		$sentence = str_replace("'", "&#39;" ,$sentence);
		//echo "$sentence\n";
		$sql = "UPDATE mw_page
			SET page_title='$sentence'
			WHERE page_id = '{$row['page_id']}'";
		execSQLStatement($sql, true);
	}

	$sql = "SELECT * FROM mw_user_groups WHERE ug_group LIKE '%$search%'";
	$data = execSQLStatement($sql);
	foreach($data as $row){
		$sentence = str_replace("$search", $replace, $row['ug_group']);
		$sentence = str_replace("'", "&#39;" ,$sentence);
		//echo "$sentence\n";
		$sql = "UPDATE mw_user_groups
			SET ug_group='$sentence'
			WHERE ug_user = '{$row['ug_user']}'
			AND ug_group='{$row['ug_group']}'";
		execSQLStatement($sql, true);
	}

	
	function execSQLStatement($sql, $update=false) {
		if($update == false){
			$dbr = wfGetDB(DB_SLAVE);
		}
		else {
			$dbr = wfGetDB(DB_MASTER);
			return $dbr->query($sql);
		}
		$result = $dbr->query($sql);
		$rows = null;
		if($update == false){
			$rows = array();
			while ($row = $dbr->fetchRow($result)) {
				$rows[] = $row;
			}
		}
		return $rows;
	}
	
	function showHelp() {
		echo( <<<EOT
Finds and Replaces any instance of the given string <search> to the string <replace

USAGE: php replaceText.php [--help] <search> <replace>

	--help
		Show this help information

EOT
	);
}
?>
