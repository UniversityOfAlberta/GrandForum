<?php

/**
 * @package PDFGenerator
 */

define('SUBM', 1);
define('NOTSUBM', 0);
define('LEADER', 1);
define('NOTLEADER', 0);

// PDF types
/*
 * GRAND
 */
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
define('RPTP_LEADER_MILESTONES', 12);
define('RPTP_NI_PROJECT_COMMENTS', 13);
define('RPTP_MTG', 18); // Mind The Gap
define('RPTP_PROJECT_ISAC', 21);
define('RPTP_SUBPROJECT', 22);

define('RPTP_NI_ZIP', 100);
define('RPTP_PROJ_ZIP', 101);
define('RPTP_HQP_ZIP', 102);

// Subject types (for RPTP_EVALUATOR reports)
define('EVTP_PERSON', 1);
define('EVTP_PROJECT', 2);

/*
 * GlycoNet
 */
define('RPTP_PROJECT_PROPOSAL', 200);
define('RPTP_SAB_REPORT',       201);
define('RPTP_RMC_PROJ_REPORT',  202);
define('RPTP_CATALYST',         203);
define('RPTP_TRANS',            204);
define('RPTP_SAB_CAT_REPORT',   205);

/*
 * AGE-WELL
 */
define('RPTP_HQP_APPLICATION',  300);
define('RPTP_CC_PLANNING',      301);
define('RPTP_CC1_PLANNING',     302);
define('RPTP_CC2_PLANNING',     303);
define('RPTP_CC3_PLANNING',     304);
define('RPTP_CC4_PLANNING',     305);
define('RPTP_PL_FEEDBACK',      306);

/*
 * TVN
 */
define('RPTP_FINAL_PROJECT',    400);
define('RPTP_PROGRESS',         401);
define('RPTP_IFP_FINAL_PROJECT',402);
define('RPTP_IFP_PROGRESS',     403);
define('RPTP_SSA_FINAL_REPORT', 404);

?>
