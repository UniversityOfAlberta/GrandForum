<?php
include_once('../commandLine.inc');

$sql = "RENAME TABLE `mw_pdf_index` TO `grand_pdf_index` ;";
DBFunctions::execSQL($sql, true);

$sql = "RENAME TABLE `mw_pdf_report` TO `grand_pdf_report` ;";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE  `grand_pdf_index` DROP INDEX  `user_id` ,
ADD INDEX  `user_id` (  `user_id` ,  `sub_id` ,  `type` )";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE  `grand_pdf_index` ADD  `type` ENUM('PROJECT','PERSON') NOT NULL DEFAULT 'PROJECT' AFTER  `project_id`";
DBFunctions::execSQL($sql, true);

$sql = "ALTER TABLE `grand_pdf_index` CHANGE COLUMN `project_id` `sub_id` INT(11) NOT NULL";
DBFunctions::execSQL($sql, true);

$sql = "SELECT * FROM `mw_evalpdf_index`";
$data = DBFunctions::execSQL($sql);
foreach($data as $row){
    $report_id = $row['report_id'];
    $user_id = $row['user_id'];
    $subject_id = $row['subject_id'];
    $type = $row['type'];
    $nr_download = $row['nr_download'];
    $last_download = $row['last_download'];
    $created = $row['created'];
    if($type == 1){
        $type = 'PERSON';
    }
    else if($type == 2){
        $type = 'PROJECT';
    }
    $sql = "INSERT INTO `grand_pdf_index` (`report_id`,`user_id`,`sub_id`,`type`,`nr_download`,`last_download`,`created`) VALUES
            ($report_id, $user_id, $subject_id, '$type', $nr_download, '$last_download', '$created')";
    DBFunctions::execSQL($sql, true);
}

$sql = "SELECT * FROM `mw_review_index`";
$data = DBFunctions::execSQL($sql);
foreach($data as $row){
    $report_id = $row['report_id'];
    $user_id = $row['reviewer_id'];
    $subject_id = $row['project_id'];
    $type = 'PROJECT';
    $nr_download = $row['nr_download'];
    $last_download = $row['last_download'];
    $created = $row['created'];
    $sql = "INSERT INTO `grand_pdf_index` (`report_id`,`user_id`,`sub_id`,`type`,`nr_download`,`last_download`,`created`) VALUES
            ($report_id, $user_id, $subject_id, '$type', $nr_download, '$last_download', '$created')";
    DBFunctions::execSQL($sql, true);
}

$sql = "DROP TABLE `mw_evalpdf_index`";
DBFunctions::execSQL($sql, true);

$sql = "DROP TABLE `mw_review_index`";
DBFunctions::execSQL($sql, true);

?>
