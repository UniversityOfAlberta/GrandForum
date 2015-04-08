<?php

    $contents = file_get_contents("symfony/output/output.html");
    
    if(strstr($contents, 'class="failed"') !== false){
        header('Content-Type: image/svg+xml');
        $img = file_get_contents("skins/failing.svg");
        echo $img;
    }
    else {
        header('Content-Type: image/svg+xml');
        $img = file_get_contents("skins/passing.svg");
        echo $img;
    }
    exit;

?>
