<?php

    require_once('../commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);
    
    $files = array_diff(scandir("pdfs"), array('..', '.'));
    
    foreach($files as $file){
        $contents = file_get_contents("pdfs/$file");
        $api = new ConvertPdfAPI();
        $data = $api->extract_pdf_data($contents);
        echo $data['first_name']." ".$data['last_name'];
        $person = Person::newFromNameLike($data['first_name']." ".$data['last_name']);
        if($person->getId() != 0){
            echo "\t\033[32mSuccess!\033[0m\n";
            $sdata = serialize($data);
            $status = DBFunctions::update('grand_sop',
                                          array('pdf_data' => $sdata),
                                          array('user_id' => EQ($person->getId())));
        }
        else{
            echo "\t\033[31mNot Found!\033[0m\n";
        }
    }

?>
