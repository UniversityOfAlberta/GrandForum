<?php

require_once('commandLine.inc');
if(isset($args[0])){
    $encrypted = encrypt($args[0]);
    if(isset($args[1])){
        echo "\n".urlencode($encrypted)."\n\n";
    }
    else{
        echo "\n{$encrypted}\n\n";
    }
}

?>
