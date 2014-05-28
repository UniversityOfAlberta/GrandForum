<?php

require_once( 'commandLine.inc' );

$journals = Product::getAllPapers('all', 'all', 'both');
$count = 0;

foreach($journals as $journal){
    $data = $journal->getData();
    if(isset($data['journal_title'])){
        if(!isset($data['published_in'])){
            $data['published_in'] = $data['journal_title'];
        }
        unset($data['journal_title']);
        $serialized = mysql_real_escape_string(serialize($data));
        $id = $journal->getId();
        $sql = "UPDATE `grand_products`
                SET `data` = '$serialized',
                    `date_changed` = date_changed
                WHERE `id` = $id";
        DBFunctions::execSQL($sql, true);
    }
}

?>
