<?php 

require_once('../../commandLine.inc');

$filename = "publications_full_list.csv";

$int_start 	= '2012'.REPORTING_CYCLE_START_MONTH.' 00:00:00';
$int_end   	= '2013'.REPORTING_NCE_END_MONTH. ' 23:59:59';

//Get all publications in this period
$publications = Paper::getAllPapersDuring('all', 'Publication', "grand", $int_start, $int_end);
$old_pubs = array();

if (($handle = fopen($filename, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $old_pubs[] = $data[0];
    }
    fclose($handle);
}

foreach ($publications as $pub) {
	$pub_id = $pub->getId();
	$pub_name = $pub->getTitle();
	$status = $pub->getStatus();
            
    switch ($pub->getType()) {
        case 'Book':
        case 'Book Chapter':
        case 'Collections Paper':
        case 'Proceedings Paper':
            if($status != "Published"){
                continue 2;
            }
          
            break;

        case 'Journal Paper':
        case 'Magazine/Newspaper Article':
            if($status != "Published" && $status != "Submitted"){
                continue 2;
            }
            
            break;

        case 'Masters Thesis':
        case 'PhD Thesis':
        case 'Tech Report':
            if($status != "Published"){
                continue 2;
            }
            
            break;

        case 'Misc':
        case 'Poster':
        default:
            if($status != "Published"){
                continue 2;
            }
    }


	foreach($old_pubs as $old_pub_name){
		$percent = null;
		similar_text($pub_name, $old_pub_name, $percent);
		$percent = round($percent);
		if($percent >= 85){
			echo $percent. "%  ". "{$pub_id}[".$pub_name."] VS [".$old_pub_name . "]\n";
		}
	}
}

?>