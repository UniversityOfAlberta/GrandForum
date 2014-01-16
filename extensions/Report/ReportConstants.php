<?php

define("REPORTING_YEAR_REAL", 2013); // Hard-coded year for the reporting period
if(isset($_GET['reportingYear']) && 
   ((preg_match("/.*Special:Report.*/", $_SERVER["REQUEST_URI"]) !== false && isset($_GET['ticket']) && isset($_GET['report'])) || 
     preg_match("/.*Special:CreatePDF.*/", $_SERVER["REQUEST_URI"]) !== false)){
    define("REPORTING_YEAR", str_replace("'", "", $_GET['reportingYear']));
}
else{
    define("REPORTING_YEAR", REPORTING_YEAR_REAL);
}

define("REPORTING_CYCLE_START_MONTH", '-00-00');
define("REPORTING_NCE_START_MONTH", '-04-01');
define("REPORTING_START_MONTH", '-09-01');
define("REPORTING_END_MONTH", '-12-31');
define("REPORTING_CYCLE_END_MONTH_ACTUAL", '-12-31');
define("REPORTING_CYCLE_END_MONTH", '-01-15');
define("REPORTING_PRODUCTION_MONTH", '-01-15');
define("REPORTING_RMC_MEETING_MONTH", '-02-15');
define("REPORTING_RMC_REVISED_MONTH", '-02-19');
define("REPORTING_NCE_END_MONTH", '-03-31');
define("REPORTING_NCE_PRODUCTION_MONTH", '-06-15');

define("REPORTING_CYCLE_START", REPORTING_YEAR.REPORTING_CYCLE_START_MONTH); // Start of internal reporting cycle (Used for range queries)
define("REPORTING_NCE_START", REPORTING_YEAR.REPORTING_NCE_START_MONTH); // Start of NCE reporting cycle
define("REPORTING_START", REPORTING_YEAR.REPORTING_START_MONTH); // Start of reporting period
define("REPORTING_END", REPORTING_YEAR.REPORTING_END_MONTH); // End of reporting period for HQP, NIs and Projects
define("REPORTING_CYCLE_END_ACTUAL", REPORTING_YEAR.REPORTING_CYCLE_END_MONTH_ACTUAL); // End of internal reporting cycle (Used for range queries)
define("REPORTING_CYCLE_END", (REPORTING_YEAR+1).REPORTING_CYCLE_END_MONTH); // End of internal reporting cycle (Used for range queries)
define("REPORTING_PRODUCTION", (REPORTING_YEAR+1).REPORTING_PRODUCTION_MONTH); // Production of NI and Project reports
define("REPORTING_RMC_MEETING", (REPORTING_YEAR+1).REPORTING_RMC_MEETING_MONTH); // RMC meeting for fund allocation
define("REPORTING_RMC_REVISED", (REPORTING_YEAR+1).REPORTING_RMC_REVISED_MONTH); // RMC when evaluator reports can be revised
define("REPORTING_NCE_END", (REPORTING_YEAR+1).REPORTING_NCE_END_MONTH); // End of NCE reporting cycle
define("REPORTING_NCE_PRODUCTION", (REPORTING_YEAR+1).REPORTING_NCE_PRODUCTION_MONTH); // Production of NCE report

?>
