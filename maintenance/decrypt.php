<?php

require_once('commandLine.inc');
if(isset($args[0])){
    echo "\n".decrypt($args[0])."\n\n";
}

?>
