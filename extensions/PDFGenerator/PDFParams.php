<?php

define('SUBM', 1);
define('NOTSUBM', 0);
define('LEADER', 1);
define('NOTLEADER', 0);

// PDF types
define('RPTP_NORMAL', 0);
define('RPTP_INPUT', 1);	// Not used yet.
define('RPTP_LEADER', 2);
define('RPTP_REVIEWER', 3);
define('RPTP_SUPPORTING', 4);
define('RPTP_EVALUATOR', 5);
define('RPTP_EVALUATOR_PROJ', 6);
define('RPTP_EVALUATOR_NI', 7);
define('RPTP_LEADER_COMMENTS', 8);
define('RPTP_EXIT_HQP', 9);
define('RPTP_HQP', 9); // Exit is not longer used, but they should be considered equivallent
define('RPTP_NI_COMMENTS', 10);
define('RPTP_HQP_COMMENTS', 11);

define('RPTP_NI_ZIP', 100);
define('RPTP_PROJ_ZIP', 101); 

// Subject types (for RPTP_EVALUATOR reports)
define('EVTP_PERSON', 1);
define('EVTP_PROJECT', 2);

