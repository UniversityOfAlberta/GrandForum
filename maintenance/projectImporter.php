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
	$url = $args[0];
	$project = $args[1];
	echo "Importing from $url, to $project\n";
	echo "Retrieving URL:\t";
	exec("wget -q -O - --post-data 'authid=david&authpw=grandNCETeam' http://wiki.grand-nce.ca/pmwiki.php?n=HalfPager.$url", $outputArray);
	if($url == "PrivacyAndSecurity"){
		exec("wget -q -O - --post-data 'authid=david&authpw=grandNCETeam' http://wiki.grand-nce.ca/pmwiki.php?n=$url.$url", $outputArray2);
	}
	else {
		exec("wget -q -O - --post-data 'authid=david&authpw=grandNCETeam' http://wiki.grand-nce.ca/pmwiki.php?n=Main.$url", $outputArray2);
	}
	echo "Done!\n";
	echo "Parsing Text:\t";
	$lastType = "";
	
	$title = "";
	$description = "";
	$hqp = "";
	$np = "";
	$ktee = "";
	$ref = "";
	
	$output = implode("\n", $outputArray);
	$outputArray = explode("<div id=\"subcontent\">", $output);
	$output = $outputArray[0];
	$outputArray = explode("<div id='wikitext'>", $output);
	$output = $outputArray[1];
	$outputArray = explode("<p", $output);
	
	for($i = 0; $i < count($outputArray); $i++){
		$block = $outputArray[$i];
		if(strstr($block, "<hr />")){
			$lastType = "";
		}
		//$blockArray = explode("</p>", $block);
		//$block = $blockArray[0]."\n";
		if(strstr($block, "Other:") || strstr($block, "<strong>Other")){
			break;
		}
		else if(strstr($block, "<h1>(")){;
			$blockArray = explode("'>", $block);
			$block = str_replace("</a>", "", $blockArray[1]);
			$title = $block;
		}
		else if(strstr($block, "<h1>")){
			// DO NOTHING
		}
		else if((strstr($block, "HQP:") || strstr($block, "<strong>HQP") || $lastType == "HQP") 
			&& !strstr($block, "Networking and Partnerships:") 
			&& !strstr($block, "<strong>Network and Partnerships") 
			&& !strstr($block, "<strong>Networking and Partnerships")
			&& !strstr($block, "N+P:")
			&& !strstr($block, "Knowledge and Technology Exchange and Exploitation:") 
			&& !strstr($block, "<strong>Knowledge and Technology Exchange and Exploitation")
			&& !strstr($block, "Knowledge Transfer")
			&& !strstr($block, "K+TE+E:")
			&& !strstr($block, "K + TE + E:")
			&& !strstr($block, "K+T+E:")
			&& !strstr($block, "Networking:")){
			$hqp .= $block;
			$lastType = "HQP";
		}
		else if((strstr($block, "Networking and Partnerships:") || strstr($block, "Networking:") || strstr($block, "N+P:") || strstr($block, "<strong>Network and Partnerships") || strstr($block, "<strong>Networking and Partnerships") || $lastType == "NP")
			&& !strstr($block, "Knowledge and Technology Exchange and Exploitation:") 
			&& !strstr($block, "Knowledge Transfer")
			&& !strstr($block, "<strong>Knowledge and Technology Exchange and Exploitation")
			&& !strstr($block, "K+TE+E:")
			&& !strstr($block, "K+T+E:")
			&& !strstr($block, "K + TE + E:")){
			$np .= $block;
			$lastType = "NP";
		}
		else if((strstr($block, "Knowledge and Technology Exchange and Exploitation:") || strstr($block, "K+TE+E:") || strstr($block, "K + TE + E:") || strstr($block, "Knowledge Transfer") || strstr($block, "<strong>Knowledge and Technology Exchange and Exploitation") || strstr($block, "K+T+E:") || $lastType == "KTEE")){
			$ktee .= $block;
			$lastType = "KTEE";
		}
		else if(preg_match("/^.*\([0-9]{4}\)/", $block) > 0){
			$ref .= $block;
		}
		else if(!strstr($block, "NIs:") 
			&& !strstr($block, "NIS:")
			&& !strstr($block, "CRs:") 
			&& !strstr($block, "CRS:") 
			&& !strstr($block, "<strong>NI")
			&& !strstr($block, "<strong>CR")
			&& !strstr($block, "Network Investigators:") 
			&& !strstr($block, "Network Investigators</a>:") 
			&& !strstr($block, "<strong>Network Investigators")
			&& !strstr($block, "Collaborating Researchers:") 
			&& !strstr($block, "Collaborating Researchers</a>:") 
			&& !strstr($block, "<strong>Collaborating Researchers")
			&& !strstr($block, "PL=") 
			&& !strstr($block, "CPL=")
			&& !strstr($block, "PL =") 
			&& !strstr($block, "CPL =")
			&& !strstr($block, "Researchers:")){
			$description .= $block;
			$lastType = "Description";
		}
	}
	
	$output = implode("\n", $outputArray2);
	$outputArray2 = explode("<table border='1", $output);
	$output = $outputArray2[1];
	$outputArray2 = explode("</table>", $output);
	$output = $outputArray2[0];
	$outputArray2 = explode("<tr >", $output);
	
	$theme1 = "";
	$theme2 = "";
	$theme3 = "";
	$theme4 = "";
	$theme5 = "";
	
	foreach($outputArray2 as $block){
		if(strstr($block, "<th")){
			// DO NOTHING
		}
		else if(strstr($block, "Theme 1") && !strstr($block, ">0%")){
			$block = str_replace("<td  align='center'>", "", $block);
			$block = str_replace("</td>", " : ", $block);
			$block = str_replace("Theme 1", "*[[GRAND:Theme1 - New Media Challenges and Opportunities | Theme 1]]", $block);
			$blockArray = explode("%", $block);
			$block = $blockArray[0];
			$block .= "%\n";
			$theme1 = $block;
		}
		else if(strstr($block, "Theme 2") && !strstr($block, ">0%")){
			$block = str_replace("<td  align='center'>", "", $block);
			$block = str_replace("</td>", " : ", $block);
			$block = str_replace("Theme 2", "*[[GRAND:Theme2 - Games and Interactive Simulation | Theme 2]]", $block);
			$blockArray = explode("%", $block);
			$block = $blockArray[0];
			$block .= "%\n";
			$theme2 = $block;
		}
		else if(strstr($block, "Theme 3") && !strstr($block, ">0%")){
			$block = str_replace("<td  align='center'>", "", $block);
			$block = str_replace("</td>", " : ", $block);
			$block = str_replace("Theme 3", "*[[GRAND:Theme3 - Animation, Graphics, and Imaging | Theme 3]]", $block);
			$blockArray = explode("%", $block);
			$block = $blockArray[0];
			$block .= "%\n";
			$theme3 = $block;
		}
		else if(strstr($block, "Theme 4") && !strstr($block, ">0%")){
			$block = str_replace("<td  align='center'>", "", $block);
			$block = str_replace("</td>", " : ", $block);
			$block = str_replace("Theme 4", "*[[GRAND:Theme4 - Social, Legal, Economic, and Cultural Perspectives | Theme 4]]", $block);
			$blockArray = explode("%", $block);
			$block = $blockArray[0];
			$block .= "%\n";
			$theme4 = $block;
		}
		else if(strstr($block, "Theme 5") && !strstr($block, ">0%")){
			$block = str_replace("<td  align='center'>", "", $block);
			$block = str_replace("</td>", " : ", $block);
			$block = str_replace("Theme 5", "*[[GRAND:Theme5 - Enabling Technologies and Methodologies | Theme 5]]", $block);
			$blockArray = explode("%", $block);
			$block = $blockArray[0];
			$block .= "%\n";
			$theme5 = $block;
		}
	}
	
	
	$title = parse($title);
	$description = parse($description);
	$hqp = parse($hqp);
	$np = parse($np);
	$ktee = parse($ktee);
	$ref = parse($ref);
	
	$firstText = 
	
	$finalText = 
"|themes = $theme1$theme2$theme3$theme4$theme5
|description = $description
|excellence_of_the_research = 
|development_of_highly_qualified_personnel = $hqp
|networking_and_partnerships = $np
|knowledge_and_technology_exchange_and_exploitation = $ktee
|project_milestones = 
|references_to_the_literature = $ref
}}";
	$sql = "SELECT t.old_text, t.old_id
		FROM mw_page p, mw_text t, mw_an_extranamespaces ns, mw_revision r
		WHERE r.rev_text_id = t.old_id
		AND r.rev_id = p.page_latest
		AND ns.nsId = p.page_namespace
		AND CONCAT(ns.nsName, CONCAT(':', p.page_title)) = '$project'";
	$data = execSQL($sql);
	if(count($data) == 1){
		$oldText = $data[0]['old_text'];
		$oldArray = explode("|themes =", $oldText);
		$oldText = $oldArray[0].$finalText;
		$oldText = str_replace("'", "\'", $oldText);
		
		$oldArray = explode("|leader", $oldText);
		$oldText = "{{Project\n |project_name = ".$title."|leader".$oldArray[1];
		
		echo "Done!\n";
		echo "Updating DB:\t";
		if(!isset( $options['preview'])){
			$sql = "UPDATE mw_text
				SET old_text='$oldText'
				WHERE old_id='{$data[0]['old_id']}'";
			execSQL($sql, true);
			echo "Done!";
		}
		else{
			echo $oldText;
		}	
	}	
	else {
		echo "Failed!";
	}
	
	
	echo "\n\n\r";
	function showHelp() {
		echo( <<<EOT
Scrapes the original grand wiki page given by <url> and adds the information to the new grand forum for the <project_page>

USAGE: php projectImporter.php [--help|--preview] <url> <project>

	--help
		Show this help information
	--preview
		Shows what the page text will look like, but will not actually update the database

EOT
		);
	}
	
	function execSQL($sql, $update=false) {
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
	
	function parse($text){
		$text = mb_convert_encoding($text, "UTF-8");
		$text = str_replace("<strong>", "", $text);
		$text = str_replace("</strong>", "", $text);
		$text = str_replace("HQP:", "", $text);
		$text = str_replace("HQP", "", $text);
		$text = str_replace("Networking and Partnerships:", "", $text);
		$text = str_replace("Networking:", "", $text);
		$text = str_replace("Network and Partnerships", "", $text);
		$text = str_replace("N+P:", "", $text);
		$text = str_replace("Knowledge and Technology Exchange and Exploitation:", "", $text);
		$text = str_replace("Knowledge Transfer", "", $text);
		$text = str_replace("K+TE+E:", "", $text);
		$text = str_replace("K + TE + E:", "", $text);
		$text = str_replace("K+T+E:", "", $text);
		
		while(strstr($text, " *")){
			$text = str_replace(" *", "*", $text);
		}
		while(strstr($text, "  ")){
			$text = str_replace("  ", " ", $text);
		}
		$text = str_replace("</p>", "", $text);
		$text = str_replace("<em>", "''", $text);
		$text = str_replace("</em>", "''", $text);
		$text = str_replace("</pre>", "", $text);
		$text = str_replace("re>", "", $text);
		$text = str_replace("</div>", "", $text);
		$text = str_replace("<hr />", "", $text);
		$text = str_replace("<div class='vspace'>", "", $text);
		$text = str_replace(" class='vspace'>", "", $text);
		$text = str_replace(" class='vspace'>", "", $text);
		
		$text = str_replace("<ul>", "", $text);
		$text = str_replace("</ul>", "", $text);
		$text = str_replace("<ol>", "", $text);
		$text = str_replace("</ol>", "", $text);
		$text = str_replace("<li>", "*", $text);
		$text = str_replace("</li>", "", $text);
		$text = str_replace("\t", "", $text);
		$text = str_replace("\n ", "\n", $text);
		$text = str_replace("\n\n\n", "", $text);
		
		$text = str_replace("<a class='urllink' href='", "[", $text);
		$text = str_replace("<a class='wikilink' href='", "[", $text);
		$text = str_replace("' rel='nofollow'>", " ", $text);
		$text = str_replace("'>", " ", $text);
		$text = str_replace("</a>", "]", $text);
		$text = str_replace("</h1>", "", $text);
		$text = str_replace(">", "", $text);
		return $text;
	}
?>
