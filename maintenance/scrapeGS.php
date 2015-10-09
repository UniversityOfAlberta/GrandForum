<?php
    require_once( "commandLine.inc" );
    $wgUser=User::newFromName("Admin");
      //this is where the user name/email will be set
    $person = Person::newFromName("Eleni Stroulia");
      //this is where google scholar url will be set and grabbed as an html
    $url = file_get_contents('http://grand.cs.ualberta.ca/~ruby/index.html');
    $gs_metric = new GsMetric(array());
    $gs_metric->user_id = $person->getId(); 
      //grabbing hindex and citation count information using regex
    $index_regex = '/\<td class\=\"gsc\_rsb\_std\"\>(.+?)\<\/td\>/';
    preg_match_all($index_regex, $url, $index);
      //setting the info in gs_metric
    $gs_metric->citation_count = $index[1][0];
    $gs_metric->hindex = $index[1][2]; 
    $gs_metric->hindex_5_years = $index[1][3];
    $gs_metric->i10_index = $index[1][4];
    $gs_metric->i10_index_5_years = $index[1][5];
    print_r($index[1]);
     //grabbing all citation years
    $citationArray = array();
    $year_regex = '/\<span class\=\"gsc_g_t\"(.+?)\>(.+?)\<\/span\>/';
    preg_match_all($year_regex, $url, $yearmatch);
    $years = $yearmatch[2];
      //grabbing all citation counts
    $counts_regex = '/\<span class\=\"gsc_g_al\"\>(.+?)\<\/span\>/';
    preg_match_all($counts_regex, $url, $countsmatch);
    $counts = $countsmatch[1];
    $i = 0;
    foreach($years as $year){
	$citationArray[$year] = $counts[$i];
	$i++; 

    }
     //setting citation counts in array
    $gs_metric->gs_citations = $citationArray;
      //save to db
    // $status =$gs_metric->create();
    print_r($gs_metric);
    print_r($status);

?>

