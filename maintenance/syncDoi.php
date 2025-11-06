<?php
require_once('commandLine.inc');

global $wgUser;

$rows = DBFunctions::select(array('grand_products'),
                            array('*'));
                            
foreach($rows as $row){
    $data = unserialize($row['data']);
    $doi = @Product::cleanDOI($data['doi']);
    if($doi != ""){
        DBFunctions::update('grand_products',
                            array('bibtex_id' => $doi,
                                  'date_changed' => $row['date_changed']),
                            array('id' => $row['id']));
        echo "{$row['id']}: {$doi}\n";
    }
}

?>
