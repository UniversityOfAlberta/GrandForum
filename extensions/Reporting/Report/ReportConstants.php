<?php

define("REPORTING_YEAR_REAL", YEAR); // Hard-coded year for the reporting period
define("REPORTING_YEAR", REPORTING_YEAR_REAL);

define("REPORTING_CYCLE_START_MONTH", CYCLE_START_MONTH);
define("REPORTING_NCE_START_MONTH", NCE_START_MONTH);
define("REPORTING_START_MONTH", START_MONTH);
define("REPORTING_END_MONTH", END_MONTH);
define("REPORTING_CYCLE_END_MONTH_ACTUAL", CYCLE_END_MONTH_ACTUAL);
define("REPORTING_CYCLE_END_MONTH", CYCLE_END_MONTH);

define("REPORTING_CYCLE_START", (REPORTING_YEAR-1).REPORTING_CYCLE_START_MONTH); // Start of internal reporting cycle (Used for range queries)
define("REPORTING_NCE_START", (REPORTING_YEAR-1).REPORTING_NCE_START_MONTH); // Start of NCE reporting cycle
define("REPORTING_START", (REPORTING_YEAR-1).REPORTING_START_MONTH); // Start of reporting period
define("REPORTING_END", REPORTING_YEAR.REPORTING_END_MONTH); // End of reporting period for HQP, NIs and Projects
define("REPORTING_CYCLE_END_ACTUAL", REPORTING_YEAR.REPORTING_CYCLE_END_MONTH_ACTUAL); // End of internal reporting cycle (Used for range queries)
define("REPORTING_CYCLE_END", REPORTING_YEAR.REPORTING_CYCLE_END_MONTH); // End of internal reporting cycle (Used for range queries)

?>
