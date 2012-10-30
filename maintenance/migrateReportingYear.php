<?php

require_once('commandLine.inc');

$queriesSoFar = 0;
$totalQueries = 0;

$sql = "SELECT * FROM mw_pdf_report";
$rows = DBFunctions::execSQL($sql);
$totalQueries += count($rows);
global $wgDBname;
// PDF Table
$data = DBFunctions::execSQL("select * from information_schema.columns where table_name = 'mw_pdf_report' and column_name = 'year' and table_schema = '$wgDBname'");
if(count($data) > 0){
    echo "Deleting Old Column...\n";
    DBFunctions::execSQL("ALTER TABLE `mw_pdf_report`
                            DROP `year`", true);
}
echo "Creating New Column...\n";
$sql = "ALTER TABLE `mw_pdf_report` ADD `year` INT NOT NULL AFTER `submission_user_id`";
DBFunctions::execSQL($sql, true);
$sql = "ALTER TABLE `mw_pdf_report` ADD INDEX ( `year` )";
DBFunctions::execSQL($sql, true);
echo "Migrating Years...\n";
show_status(++$queriesSoFar, $totalQueries);

$prod2012 = "2013".REPORTING_NCE_START_MONTH." 00:00:00";
$prod2011 = "2012".REPORTING_NCE_START_MONTH." 00:00:00";
$prod2010 = "2011".REPORTING_NCE_START_MONTH." 00:00:00";

foreach($rows as $row){
    $rId = $row['report_id'];
    $tst = $row['timestamp'];
    if(strcmp($tst, $prod2010) < 0){
        $sql = "UPDATE mw_pdf_report
                SET year = '2010',
                    timestamp = '$tst'
                WHERE report_id = '$rId'";
        DBFunctions::execSQL($sql, true);
    }
    else if(strcmp($tst, $prod2011) < 0){
        $sql = "UPDATE mw_pdf_report
                SET year = '2011',
                    timestamp = '$tst'
                WHERE report_id = '$rId'";
        DBFunctions::execSQL($sql, true);
    }
    else if(strcmp($tst, $prod2012) < 0){
        $sql = "UPDATE mw_pdf_report
                SET year = '2012',
                    timestamp = '$tst'
                WHERE report_id = '$rId'";
        DBFunctions::execSQL($sql, true);
    }
    show_status(++$queriesSoFar, $totalQueries);
}


?>


