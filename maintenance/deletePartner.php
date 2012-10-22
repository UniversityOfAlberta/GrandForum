<?php
    require_once('commandLine.inc');

    if( count( $args ) >= 1 && count($args) < 3){
        $sql = "DELETE FROM `grand_partners`
                WHERE `id` = '{$args[0]}'";
        DBFunctions::execSQL($sql, true);
        echo "Partner {$args[0]} removed";
        if(count($args) == 2){
            $sql = "UPDATE `grand_contributions_partners`
                    SET `partner` = '{$args[1]}'
                    WHERE `partner` = '{$args[0]}'";
            DBFunctions::execSQL($sql, true);
            echo ", replaced with {$args[1]}";
        }
        echo "\n";
    }
    else{
        echo "deletePartner expects 2 arguments.\n";
        echo "\t php deletePartner.php <pId> <rId>\n";
        echo "\t pId is the partner Id to delete\n";
        echo "\t rId is the partner Id to replace any contributions with\n";
    }
?>
