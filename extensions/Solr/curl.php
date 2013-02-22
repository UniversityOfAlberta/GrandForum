<?php

$query = $_GET['query'];

$url = "http://grand.cs.ualberta.ca:8980"
     . "/solr/select?"
     . "indent=on&version=2.2&fq=&start=0&rows=10&fl=*&wt=json&q="
     . urlencode($query)
;

//echo json_encode(array('url' => $url));

// Open connection
$ch = curl_init();

// Set the curl
curl_setopt($ch,CURLOPT_HTTPHEADER, array('Content-type: application/json'));
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

// Execute post
$result = curl_exec($ch);

echo $result;

// Close connection
curl_close($ch);

?>

