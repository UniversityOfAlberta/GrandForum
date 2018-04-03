<?php
    require_once( "commandLine.inc" );
    $wgUser=User::newFromId(1);

    $data = DBFunctions::select(array('grand_sop'),
                                array('*'));
    foreach($data as $row){
        if(strlen($row['pdf_contents']) > 0){
            $deflated = gzinflate($row['pdf_contents']);
            DBFunctions::update('grand_sop',
                                array('pdf_contents' => $deflated),
                                array('id' => $row['id']));
            echo strlen($row['pdf_contents'])." -> ".strlen($deflated)."\n";
        }
    }
?>
