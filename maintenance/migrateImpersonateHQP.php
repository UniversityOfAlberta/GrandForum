<?php
require_once('commandLine.inc');

$totalQueries = 8;
$queriesSoFar = 0;

// PDF Table
$data = DBFunctions::execSQL("select * from information_schema.columns where table_name = 'mw_pdf_report' and column_name = 'submission_user_id'");

if(count($data) > 0){
    DBFunctions::execSQL("ALTER TABLE `mw_pdf_report`
                            DROP `submission_user_id`", true);
}
show_status(++$queriesSoFar, $totalQueries);
$data = DBFunctions::execSQL("select * from information_schema.columns where table_name = 'mw_pdf_report' and column_name = 'generation_user_id'");

if(count($data) > 0){
    DBFunctions::execSQL("ALTER TABLE `mw_pdf_report`
                            DROP `generation_user_id`", true);
}
show_status(++$queriesSoFar, $totalQueries);
DBFunctions::execSQL("ALTER TABLE `mw_pdf_report` 
                        ADD `submission_user_id` INT NOT NULL AFTER `user_id`, 
                        ADD INDEX ( `submission_user_id` )", true);
show_status(++$queriesSoFar, $totalQueries);
DBFunctions::execSQL("ALTER TABLE `mw_pdf_report` 
                        ADD `generation_user_id` INT NOT NULL AFTER `user_id`, 
                        ADD INDEX ( `generation_user_id` )", true);
show_status(++$queriesSoFar, $totalQueries);
DBFunctions::execSQL("UPDATE `mw_pdf_report`
                        SET `submission_user_id` = `user_id`,
                            `generation_user_id` = `user_id`", true);
show_status(++$queriesSoFar, $totalQueries);

// Blob Table
$data = DBFunctions::execSQL("select * from information_schema.columns where table_name = 'grand_report_blobs' and column_name = 'edited_by'");

if(count($data) > 0){
    DBFunctions::execSQL("ALTER TABLE `grand_report_blobs`
                            DROP `edited_by`", true);
}
show_status(++$queriesSoFar, $totalQueries);

DBFunctions::execSQL("ALTER TABLE `grand_report_blobs` 
                        ADD `edited_by` INT NOT NULL AFTER `blob_id`", true);
show_status(++$queriesSoFar, $totalQueries);

DBFunctions::execSQL("UPDATE `grand_report_blobs`
                        SET `edited_by` = `user_id`", true);
show_status(++$queriesSoFar, $totalQueries);

?>
