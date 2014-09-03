<?php

require_once('commandLine.inc');

// NI Report Changes
$sql = "UPDATE grand_report_blobs
        SET `rp_item` = 11,
            `changed` = changed
        WHERE rp_type = 1
        AND rp_section = 2
        AND rp_item = 2";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE grand_report_blobs
        SET `rp_item` = 12,
            `changed` = changed
        WHERE rp_type = 1
        AND rp_section = 2
        AND rp_item = 1";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE grand_report_blobs
        SET `rp_item` = 13,
            `changed` = changed
        WHERE rp_type = 1
        AND rp_section = 2
        AND rp_item = 3";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE grand_report_blobs
        SET `rp_item` = 14,
            `changed` = changed
        WHERE rp_type = 1
        AND rp_section = 2
        AND rp_item = 4";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE grand_report_blobs
        SET `rp_item` = 15,
            `changed` = changed
        WHERE rp_type = 1
        AND rp_section = 2
        AND rp_item = 5";
DBFunctions::execSQL($sql, true);

// HQP Report Changes
$sql = "UPDATE grand_report_blobs
        SET `rp_item` = 5,
            `changed` = changed
        WHERE rp_type = 2
        AND rp_section = 3
        AND rp_item = 2";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE grand_report_blobs
        SET `rp_item` = 6,
            `changed` = changed
        WHERE rp_type = 2
        AND rp_section = 3
        AND rp_item = 1";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE grand_report_blobs
        SET `rp_item` = 7,
            `changed` = changed
        WHERE rp_type = 2
        AND rp_section = 3
        AND rp_item = 3";
DBFunctions::execSQL($sql, true);

$sql = "UPDATE grand_report_blobs
        SET `rp_item` = 8,
            `changed` = changed
        WHERE rp_type = 2
        AND rp_section = 3
        AND rp_item = 4";
DBFunctions::execSQL($sql, true);

// NI Report Section Change
$sql = "UPDATE grand_report_blobs
        SET `rp_section` = 12,
            `changed` = changed
        WHERE rp_type = 1
        AND rp_section = 2";
DBFunctions::execSQL($sql, true);

// HQP Report Section Change
$sql = "UPDATE grand_report_blobs
        SET `rp_section` = 7,
            `changed` = changed
        WHERE rp_type = 2
        AND rp_section = 3";
DBFunctions::execSQL($sql, true);

?>
