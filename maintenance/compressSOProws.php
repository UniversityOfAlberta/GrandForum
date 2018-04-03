<?php
    require_once( "commandLine.inc" );
    $wgUser=User::newFromId(1);

    $data = DBFunctions::select(array('grand_sop'),
                                array('id'));
    foreach($data as $row){
        $data2 = DBFunctions::select(array('grand_sop'),
                                     array('*'),
                                     array('id' => $row['id']));
        $row = $data2[0];
        if(strlen($row['pdf_contents']) > 0){
            $deflated = gzdeflate($row['pdf_contents']);
            DBFunctions::update('grand_sop',
                                array('pdf_contents' => $deflated),
                                array('id' => $row['id']));
            echo strlen($row['pdf_contents'])." -> ".strlen($deflated)."\n";
        }
    }
?>
