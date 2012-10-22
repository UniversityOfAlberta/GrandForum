<?php

require_once('commandLine.inc');

if(count($args) > 0){
    if($args[0] == "help"){
        showHelp();
        exit;
    }
}
if(count( $args ) != 4){
    showHelp();
    exit;
}

$year = $args[0];
$category = $args[1];
$type = $args[2];
$strict = (strtolower($args[3]) == 'true');
if($type == RMC){
    $papers = Paper::getAllPapersDuring('all', $category, 'grand', $year.REPORTING_CYCLE_START_MONTH, ($year).REPORTING_CYCLE_END_MONTH, $strict);
}
else{
    $papers = Paper::getAllPapersDuring('all', $category, 'grand', $year.REPORTING_CYCLE_START_MONTH, ($year).REPORTING_CYCLE_END_MONTH);
}
$nPapers = 0;
$i = 0;
foreach($papers as $paper){
    if(!$paper->hasBeenReported($year, $type) && ($type == "RMC" || ($type == "NCE" && $paper->isPublished()))){
        $sql = "INSERT INTO `grand_products_reported` (`product_id`,`reported_type`,`year`,`data`)
                VALUES ('{$paper->getId()}','{$type}','{$year}','".addslashes(serialize($paper))."')";
        DBFunctions::execSQL($sql, true);
        $nPapers++;
    }
    $i++;
    show_status($i, count($papers));
    flush();
}
echo "\n$nPapers {$category}s marked as reported to '{$type}' for {$year}\n\n";

function showHelp() {
		echo( <<<EOF
Marks whether or not the products in the given year have been reported to the RMC or NCE

USAGE: php productReported.php [--help] <year> <category> <type[NCE|RMC]> <strict[true|false]>

    <year>     : Specifies what reporting year this is for
    <category> : What type of product to report (ie. Publication, Artifact, all)
    <type>     : The type of report this was for (either NCE or RMC)
    <strict>   : Whether to count unpublished products (only for RMC)
    
	--help
		Show this help information

EOF
	);
}
?>
