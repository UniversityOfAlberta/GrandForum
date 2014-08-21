<?php

require_once('commandLine.inc');

$sql = "UPDATE grand_report_blobs
        SET `rp_item` = 11,
            `changed` = changed
        WHERE rp_type = 1
        AND rp_section = 2
        AND rp_item = 2";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE grand_report_blobs
        SET `rp_item` = 5,
            `changed` = changed
        WHERE rp_type = 2
        AND rp_section = 3
        AND rp_item = 2";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE grand_report_blobs
        SET `rp_section` = 12,
            `changed` = changed
        WHERE rp_type = 1
        AND rp_section = 2";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE grand_report_blobs
        SET `rp_section` = 7,
            `changed` = changed
        WHERE rp_type = 2
        AND rp_section = 3";
DBFunctions::execSQL($sql, true);

?>
