<?php

/**
 * Textual blob types.
 *
 * These instruct the renderer to act accordingly.
 */
define('BLOB_TEXT',		1);
define('BLOB_HTML',		2);
define('BLOB_WIKI',		3);

/**
 * Structured blobs.
 *
 * Most structured blobs can be handled as arrays, which are serialized for
 * storage, automatically.
 */
define('BLOB_ARRAY',    1024);
define('BLOB_CSV',		1025);

// Structured blob: array containing "selected" and "text" keys, associated
// with textual values.  Suitable for questions that contain a select, radio,
// or option box along with an input or textarea element.
define('BLOB_OPTIONANDTEXT',	1026);

// Structured blob: array containing "approved" array with user casting votes
// (1 approve, 0 reject) and "text" keys, associated with textual description.
// Suitable for questions that contain a text blob that is supposed to be
// approved or rejected.
define('BLOB_TEXTANDAPPROVE',	1027);

// Structured blob: array containing keys "title", "description", "primary"
// "secondary", "tertiary" for (old-style?) artifacts.
define('BLOB_ARTIFACT',		1028);

// Structured blob: array containing keys "page-id", "not-applicable", "primary"
// "secondary", "tertiary" for (old-style?) publication.
define('BLOB_PUBLICATION',	1029);

// Structured blob: array; keys "description", "assessment", "title", "year",
// "month".
define('BLOB_NEWMILESTONE',	1030);

// Structured blob: array; keys "description", "assessment", "title", "year",
// "month", "not-applicable", "status".
define('BLOB_CURRENTMILESTONE',	1031);

// Milestones as reported by leader.
define('BLOB_MILESTONESTATUS',	1032);

// Structured blob for contributions: array with keys "type", "internal",
// "description", "source", "inkind", "cash", "primary", "secondary",
// "tertiary"
define('BLOB_CONTRIBUTION',	1033);


/**
 * Binary blobs.
 *
 * These blobs hold specialized data, such as PDFs or Excel spreadsheets.
 */
define('BLOB_PDF',		16384);
define('BLOB_EXCEL',		16385);


/**
 * Super special blobs.
 *
 * These are corner cases: a blob whose type is undefined.  The NULL blob should
 * be treated as an omission elsewhere: code that did not properly set it.  RAW
 * should be treated as an exception, and the renderer has no hints except that
 * it is raw data.
 */
define('BLOB_NULL',		0);
define('BLOB_RAW',		65535);

/******************************************************************************
 * This include contains the addressing used for all parts of all reports, as
 * well as constants for blob types.
 *
 * NOTE: changing *any* value here with a live database has major consequences.
 * If a value needs to be changed, it is critical to lock the database and
 * change them accordingly, before allowing end-users to change their report
 * blobs.
 *****************************************************************************/


/*
 * Top-level identifiers: report type.
 *
 * These distinguish across different kinds of reports.  Even similar reports
 * could be split into different types to ensure that the addressing scheme will
 * work properly (e.g.: evaluator report being split into researcher evaluation
 * and project evaluation).
 */
/*
 * GRAND
 */
define('RP_RESEARCHER',		1);
define('RP_HQP',			2);
define('RP_LEADER',			3);
define('RP_EVAL_RESEARCHER',4);
define('RP_EVAL_PROJECT',	5);
define('RP_SUPPLEMENTAL',	6);
define('RP_EVAL_PDF',		7);
define('RP_REVIEW', 11);
define('RP_MTG', 12);
define('RP_CHAMP', 13);
define('RP_ISAC', 14);
define('RP_PROJECT_CHAMP', 15);
define('RP_PROJECT_ISAC', 16);
define('RP_SUBPROJECT', 17);
//define('RP_EVAL_RESEARCHER_REV',		9); //Revised answers
//define('RP_EVAL_PROJECT_REV', 11); //Revised answers
/*
 * GlycoNet
 */
define('RP_PROJECT_PROPOSAL',   100);
define('RP_SAB_REVIEW',         101);
define('RP_SAB_REPORT',         102);
define('RP_CATALYST',           103);
define('RP_TRANS',              104);
define('RP_SAB_CAT_REVIEW',     105);
define('RP_SAB_CAT_REPORT',     106);

/*
 * AGE-WELL
 */
define('RP_HQP_APPLICATION',    200);
define('RP_CC_PLANNING',        201);
define('RP_CC_LEADER',          202);
define('RP_HQP_REVIEW',         203);
define('RP_PROJ_REVIEW',        204);
define('RP_PROJ_FEEDBACK',      205);

/*
 * TVN
 */
define('RP_FINAL_PROJECT',      300);
define('RP_IFP_PROGRESS',       301);
define('RP_PROGRESS',           302);
define('RP_SSA_FINAL_PROGRESS', 303);
define('RP_IFP_FINAL_PROJECT',  304);
define('RP_IFP_REVIEW',         305);
define('RP_TRANS_REVIEW',       306);

/*
 * FEC
 */


/*
 * Second-level identifiers: report section.
 *
 * These comprise high-level parts within a report, such as personal information,
 * budget, publications, and so on.  Each report type has its own section
 * definitions.
 */
define('SEC_NONE',              0); 

define('RES_EFFORT',			1);
define('RES_MILESTONES',		12); // Used to be '2', but moved into RES_RESACTIVITY ('12')
define('RES_PEOPLE_INTERACT',	3);
define('RES_PROJECT_INTERACT',	4);
define('RES_IMPACT',			5);
define('RES_HQPSELECTION',		6);
define('RES_HQPREPORT',			7);
define('RES_BUDGET',			8);
define('RES_PUBLICATIONS',		9);
define('RES_ARTIFACTS',			10);
define('RES_CONTRIBUTIONS',		11);
define('RES_RESACTIVITY',		12);
define('RES_ALLOC_BUDGET',      14);
define('RES_SUBPROJECTS',		5);

define('SUB_SUBPROJECTS',       1);

define('HQP_DEMOGRAPHIC',		1);
define('HQP_EFFORT',			2);
define('HQP_MILESTONES',		7); // Used to be '3', but moved into HQP_RESACTIVITY ('7')
define('HQP_PEOPLE_INTERACT',	4);
define('HQP_PROJECT_INTERACT',	5);
define('HQP_IMPACT',			6);
define('HQP_RESACTIVITY',		7);

define('HQP_APPLICATION_FORM',  1);
define('HQP_APPLICATION_DOCS',  2);
define('HQP_APPLICATION_EPIC',  3);

define('HQP_REVIEW',            1);

define('CC_PLANNING_1',         1);
define('CC_PLANNING_2',         2);
define('CC_PLANNING_3',         3);
define('CC_PLANNING_4',         4);

define('PROJ_REVIEW_COMMENTS',  1);
define('PROJ_REVIEW_FEEDBACK',  2);

define('PROJ_FEEDBACK_COMMENTS', 1);

define('LDR_SUMMARY',			1);
define('LDR_MILESTONESTATUS',		2);
define('LDR_NEWMILESTONE',		2);
define('LDR_MILESTONE', 13);
define('LDR_MULTIDISCIPLINARY',		3);
define('LDR_CROSSPOLLINATION',		4);
define('LDR_THEMES',			5);
define('LDR_THEMESREMARKS',		6);
define('LDR_RESEARCHERPERFORMANCE',	7);
define('LDR_PROJECTCHAMPIONS',		8);
define('LDR_BUDGET',			9);
define('LDR_RESACTIVITY',		10);
define('LDR_NICOMMENTS',		11);
define('LDR_BUDGETJUSTIF',		12);

define('CHAMP_REPORT', 1);

// Second-level identifiers for Evaluator Report.
define('EVL_EXCELLENCE',		1);
define('EVL_HQPDEVELOPMENT',	2);
define('EVL_NETWORKING',		3);
define('EVL_KNOWLEDGE',			4);
define('EVL_MANAGEMENT',		5);
define('EVL_OVERALLSCORE',		6);
define('EVL_OTHERCOMMENTS',		7);
define('EVL_REPORTQUALITY',		8);
define('EVL_CONFIDENCE',		9);
//New in 2012-2013
define('EVL_STOCKCOMMENTS',		10); 
define('EVL_EXCELLENCE_COM',	11);
define('EVL_HQPDEVELOPMENT_COM',12);
define('EVL_NETWORKING_COM',	13);
define('EVL_KNOWLEDGE_COM',		14);
define('EVL_MANAGEMENT_COM',	15);
define('EVL_REPORTQUALITY_COM',	16);
define('EVL_SEENOTHERREVIEWS',	17); //flag indicating that evaluator has seen other reviews
define('EVL_OTHERCOMMENTSAFTER',18); //Other comments after seeing other reviews
//GlycoNet
define('EVL_EXCELLENCE_OTHER',  19);
define('EVL_STRATEGIC',         20);
define('EVL_STRATEGIC_COM',     21);
define('EVL_STRATEGIC_OTHER',   22);
define('EVL_INTEG',             23);
define('EVL_INTEG_COM',         24);
define('EVL_INTEG_OTHER',       25);
define('EVL_NETWORKING_OTHER',  26);
define('EVL_KNOWLEDGE_OTHER',   27);
define('EVL_HQPDEVELOPMENT_OTHER',28);
define('EVL_REPORTQUALITY_OTHER',29);

// Second-level identifiers for Supplemental Report.
define('SUP_HQPS',			1);
define('SUP_PUBLICATIONS',		2);
define('SUP_ARTIFACTS',			3);
define('SUP_CONTRIBUTIONS',		4);
define('SUP_OTHER',			5);
define('SUP_BUDGET',			6);

// Second level for Eval PDFs
define('PDF_NI',			1);
define('PDF_PROJ',			2);

// Second level for Mind The Gap
define('MTG_INTRO', 1);
define('MTG_MUSIC', 2);
define('MTG_FIRST_NATIONS', 3);
define('MTG_SOCIAL_PROBLEMS', 4);
define('MTG_OTHER', 5);

// Second level for Project Proposal
define('PROP_DESC', 1);
define('PROP_MILESTONES', 2);
define('PROP_BUDGET', 3);
define('PROP_SUPPORT', 4);
define('PROP_CCV',     5);

// Second level for SAB Review
define('SAB_REVIEW',    1);

// Second level for SAB Report
define('SAB_REPORT',    1);

// Second level for RMC Review
define('RMC_REVIEW',    0);

// Second level for Catalyst and Translational
define('CAT_DESC',      0);
define('CAT_BUDGET',    1);
define('CAT_MILESTONES',2);
define('CAT_SUPPORT',   3);
define('CAT_CCV',       4);
define('CAT_COMM',      5);

// Second level for TVN Final Project Report
define('FINAL_INFORMATION',     1);
define('FINAL_MILESTONES',      2);
define('FINAL_RESEARCH',        3);
define('FINAL_KTEE',            4);
define('FINAL_PARTNERS',        5);
define('FINAL_ENGAGE',          6);
define('FINAL_NETWORK',         7);
define('FINAL_PLANNING',        8);
define('FINAL_ALIGN',           9);
define('FINAL_BUDGET',          10);
define('FINAL_HQP',             11);

// Second level for TVN Progress Report
define('PROG_INFORMATION',      1);
define('PROG_MILESTONES',       2);
define('PROG_KTEE',             3);
define('PROG_PARTNERS',         4);
define('PROG_ENGAGE',           5);
define('PROG_NETWORK',          6);
define('PROG_PLANNING',         7);
define('PROG_BUDGET',           8);
define('PROG_HQP',              9);

// Second level for TVN IFP Final Project Report
define('IFP_FINAL_MILESTONES',  1);
define('IFP_FINAL_KTEE',        2);
define('IFP_FINAL_EXTERNAL',    3);
define('IFP_FINAL_MENTORSHIP',  4);
define('IFP_FINAL_COLLAB',      5);
define('IFP_FINAL_DISS',        6);
define('IFP_FINAL_PARTNERS',    8);
define('IFP_FINAL_NETWORK',     9);
define('IFP_FINAL_COMMENTS',    10);
define('IFP_FINAL_SUPERVISOR',  11);

// Second level for TVN IFP Progress Report
define('IFP_PROG_MILESTONES',   1);
define('IFP_PROG_MENTORSHIP',   2);
define('IFP_PROG_COLLAB',       3);
define('IFP_PROG_DISS',         4);
define('IFP_PROG_CAPACITY',     5);
define('IFP_PROG_PARTNERS',     6);
define('IFP_PROG_NETWORK',      7);
define('IFP_PROG_PLANNING',     8);
define('IFP_PROG_COMMENTS',     9);
define('IFP_PROG_SUPERVISOR',   10);

// SSA Final Report
define('SSA_REPORT',            1);
define('SSA_HQP',               2);

//FEC Report

// IFP Review
define('IFP_REVIEW',            1);

// Transformative Review
define('TRANS_SRC_REVIEW',      1);
define('TRANS_RMC_REVIEW',      2);

/*
 * Third-level identifiers: question within a section.
 *
 * Each section in the report can have an arbitrary amount of questions.  These
 * questions are, thus, section-specific, and relatively numerous.  Constants
 * are prefixed with the section they belong to in order to make it manageable.
 */

define('RES_MIL_NOTAPPLICABLE',		12);  // Used to be '1', changed to '12'
define('RES_MIL_CONTRIBUTIONS',		11);  // Used to be '2', changed to '11'
define('RES_MIL_PRIMCRITERIA',		13);  // Used to be '3', changed to '13'
define('RES_MIL_SECCRITERIA',		14);  // Used to be '4', changed to '14'
define('RES_MIL_SUMMARY',		    15);  // Used to be '5', changed to '15'

define('RES_HQP_MILESTONEFEEDBACK',	1);
define('RES_HQP_PPLINTERACTFEEDBACK',	2);
define('RES_HQP_PROJINTERACTFEEDBACK',	3);
define('RES_HQP_OTHERACTIVITIES',	4);
define('RES_HQP_THESISCOMPLETED',	5);
define('RES_HQP_GRADUATED',		6);
define('RES_HQP_MOVEDTO',		7);

define('RES_BUD_BUDGET',            0);
define('RES_BUD_JUSTIF',			1);

define('RES_RESACT_OVERALL',		1);
define('RES_RESACT_EXCELLENCE',		2);
define('RES_RESACT_NETWORKING',		3);
define('RES_RESACT_HQPDEV', 		4);
define('RES_RESACT_KTEE',   		5);
define('RES_RESACT_NETMAN',   		6);
define('RES_RESACT_BENEF',   		7);
define('RES_RESACT_OTHER',   		8);
define('RES_RESACT_NEXTPLANS',      9);
define('RES_RESACT_FILE',           10);
define('RES_RESACT_CONTRIBUTIONS',  11);
define('RES_RESACT_NOTAPPLICABLE',  12);
define('RES_RESACT_PRIMCRITERIA',   13);
define('RES_RESACT_SECCRITERIA',    14);
define('RES_RESACT_SUMMARY',        15);

define('HQP_DEM_LEVEL',			1);
define('HQP_DEM_GENDER',		2);
define('HQP_DEM_FULLNAME',		3);
define('HQP_DEM_CITIZENSHIP',		4);

define('HQP_EFF_HOURS',			1);
define('HQP_EFF_MONTHS',		2);
define('HQP_EFF_REMARKS',		3);

define('HQP_MIL_NOTAPPLICABLE',		6); // Used to be '1', changed to '6'
define('HQP_MIL_CONTRIBUTIONS',		5); // Used to be '2', changed to '5'
define('HQP_MIL_PRIMCRITERIA',		7); // Used to be '3', changed to '7'
define('HQP_MIL_SECCRITERIA',		8); // Used to be '4', changed to '8'

define('HQP_RESACT_OVERALL',		1);
define('HQP_RESACT_EXCELLENCE',		2);
define('HQP_RESACT_NETWORKING',		3);
define('HQP_RESACT_KTEE',   		4);
define('HQP_RESACT_CONTRIBUTIONS',  5);
define('HQP_RESACT_NOTAPPLICABLE',  6);
define('HQP_RESACT_PRIMCRITERIA',   7);
define('HQP_RESACT_SECCRITERIA',    8);

/*
 * AGE-WELL HQP Application second level
 */
define('HQP_APPLICATION_NAME',      1);
define('HQP_APPLICATION_UNI',       2);
define('HQP_APPLICATION_SUP',       3);
define('HQP_APPLICATION_LVL',       4);
define('HQP_APPLICATION_LVL_OTH',   5);
define('HQP_APPLICATION_PROJ',      6);
define('HQP_APPLICATION_KEYWORDS',  7);
define('HQP_APPLICATION_RESEARCH',  8);
define('HQP_APPLICATION_TRAIN',     9);
define('HQP_APPLICATION_BIO',       10);
define('HQP_APPLICATION_OBJ',       11);
define('HQP_APPLICATION_GOALS',     12);
define('HQP_APPLICATION_ALIGN',     13);
define('HQP_APPLICATION_COMM',      14);
define('HQP_APPLICATION_IND',       15);
define('HQP_APPLICATION_INN',       16);
define('HQP_APPLICATION_BOUNDARY',  17);
define('HQP_APPLICATION_FUND',      18);
define('HQP_APPLICATION_PROGRAM',   19);
define('HQP_APPLICATION_START',     20);
define('HQP_APPLICATION_START_OTH', 21); 

define('HQP_APPLICATION_SUPPORT1',  1);
define('HQP_APPLICATION_SUPPORT2',  2);
define('HQP_APPLICATION_ADMISSION', 3);
define('HQP_APPLICATION_EVIDENCE',  4);
define('HQP_APPLICATION_TRANS1',    5);
define('HQP_APPLICATION_TRANS2',    6);
define('HQP_APPLICATION_CV',        7);

define('HQP_EPIC_ORIENTATION_DESC', 1);
define('HQP_EPIC_ORIENTATION_HQP',  2);
define('HQP_EPIC_ORIENTATION_NMO',  3);
define('HQP_EPIC_ORIENTATION_SUP',  4);
define('HQP_EPIC_TRAINING_DESC',    5);
define('HQP_EPIC_TRAINING_HQP',     6);
define('HQP_EPIC_TRAINING_NMO',     7);
define('HQP_EPIC_TRAINING_SUP',     8);
define('HQP_EPIC_WORKSHOP_DESC',    9);
define('HQP_EPIC_WORKSHOP_HQP',     10);
define('HQP_EPIC_WORKSHOP_NMO',     11);
define('HQP_EPIC_WORKSHOP_SUP',     12);
define('HQP_EPIC_CORE_DESC',        33);
define('HQP_EPIC_CORE_HQP',         34);
define('HQP_EPIC_CORE_NMO',         35);
define('HQP_EPIC_CORE_SUP',         36);
define('HQP_EPIC_KTEE_DESC',        37);
define('HQP_EPIC_KTEE_HQP',         38);
define('HQP_EPIC_KTEE_NMO',         39);
define('HQP_EPIC_KTEE_SUP',         40);
define('HQP_EPIC_TRANS_DESC',       41);
define('HQP_EPIC_TRANS_HQP',        42);
define('HQP_EPIC_TRANS_NMO',        43);
define('HQP_EPIC_TRANS_SUP',        44);
define('HQP_EPIC_ETHICS_DESC',      45);
define('HQP_EPIC_ETHICS_HQP',       46);
define('HQP_EPIC_ETHICS_NMO',       47);
define('HQP_EPIC_ETHICS_SUP',       48);
define('HQP_EPIC_IMPACT_DESC',      49);
define('HQP_EPIC_IMPACT_HQP',       50);
define('HQP_EPIC_IMPACT_NMO',       51);
define('HQP_EPIC_IMPACT_SUP',       52);
define('HQP_EPIC_WEB_DESC',         13);
define('HQP_EPIC_WEB_HQP',          14);
define('HQP_EPIC_WEB_NMO',          15);
define('HQP_EPIC_WEB_SUP',          16);
define('HQP_EPIC_BLOG_DESC',        17);
define('HQP_EPIC_BLOG_HQP',         18);
define('HQP_EPIC_BLOG_NMO',         19);
define('HQP_EPIC_BLOG_SUP',         20);
define('HQP_EPIC_EXP1_DESC',        53);
define('HQP_EPIC_EXP1_HQP',         54);
define('HQP_EPIC_EXP1_NMO',         55);
define('HQP_EPIC_EXP1_SUP',         56);
define('HQP_EPIC_EXP2_DESC',        57);
define('HQP_EPIC_EXP2_HQP',         58);
define('HQP_EPIC_EXP2_NMO',         59);
define('HQP_EPIC_EXP2_SUP',         60);
define('HQP_EPIC_SELF_DESC',        21);
define('HQP_EPIC_SELF_HQP',         22);
define('HQP_EPIC_SELF_NMO',         23);
define('HQP_EPIC_SELF_SUP',         24);
define('HQP_EPIC_PUBS_DESC',        25);
define('HQP_EPIC_PUBS_HQP',         26);
define('HQP_EPIC_PUBS_NMO',         27);
define('HQP_EPIC_PUBS_SUP',         28);
define('HQP_EPIC_REP_DESC',         29);
define('HQP_EPIC_REP_HQP',          30);
define('HQP_EPIC_REP_NMO',          31);
define('HQP_EPIC_REP_SUP',          32);

define('HQP_REVIEW_OVERALL_COMM',   1);
define('HQP_REVIEW_QUALITY',        2);
define('HQP_REVIEW_QUALITY_COMM',   3);
define('HQP_REVIEW_GOALS',          4);
define('HQP_REVIEW_GOALS_COMM',     5);
define('HQP_REVIEW_TRAIN',          6);
define('HQP_REVIEW_TRAIN_COMM',     7);

define('CC_1_OBJECTIVES',   1);
define('CC_1_MOBILIZE',     2);
define('CC_1_PARTNERS',     3);
define('CC_1_ENGAGE',       4);
define('CC_1_ACHIEVE',      5);
define('CC_1_EXPERTISE',    6);
define('CC_1_RESOURCES',    7);
define('CC_1_MEASURE',      8);
define('CC_1_SUPPORT',      9);
define('CC_1_ATTACH',       10);

define('CC_2_PRODUCTS',     1);
define('CC_2_COMMERCIALIZE',2);
define('CC_2_PROVIDE',      3);
define('CC_2_PARTNERS',     4);
define('CC_2_STAGE',        5);
define('CC_2_MARKET',       6);
define('CC_2_IP',           7);
define('CC_2_ACHIEVE',      8);
define('CC_2_MEASURE',      9);
define('CC_2_SUPPORT',      10);
define('CC_2_ATTACH',       11);

define('CC_3_TRANS',        1);
define('CC_3_ADOPT',        2);
define('CC_3_TEAMWORK',     3);
define('CC_3_NETWORK',      4);
define('CC_3_SYNERGY',      5);
define('CC_3_ACCEPT',       6);
define('CC_3_SUPPORT',      7);
define('CC_3_EVAL',         8);
define('CC_3_ATTACH',       9);
define('CC_3_FEEDBACK',     10);

define('CC_4_TRAIN',        1);
define('CC_4_KNOW',         2);
define('CC_4_OUTCOME',      3);
define('CC_4_INDUSTRY',     4);
define('CC_4_OTHER',        5);
define('CC_4_MEASURE',      6);
define('CC_4_ATTACH',       7);

define('PROJ_REVIEW_COMM',     1);

define('PROJ_FEEDBACK_COMM',   1);

define('CHAMP_REPRESENT', 1);
define('CHAMP_ACTIVITY', 2);
define('CHAMP_ORG', 3);
define('CHAMP_BENEFITS', 4);
define('CHAMP_SHORTCOMINGS', 5);
define('CHAMP_CASH', 6);
define('CHAMP_RESEARCHERS', 7);

define('SUB_SUBPROJECT_CHAMPS',     1);
define('SUB_SUBPROJECT_COMMENTS',   2);

define('ISAC_PHASE2', 1);

// No third-level identifiers for interactions with people (HQP_PEOPLE_INTERACT),
// projects (HQP_PROJECT_INTERACT), or impact (HQP_IMPACT).

// Third-level identifiers for Leader report.
define('LDR_MLT_BALANCE',		1);
define('LDR_MLT_CIHR',			2);
define('LDR_MLT_ARTDESIGN',		3);

define('LDR_CHA_NOMINATIONREASON',	1);
define('LDR_CHA_CONTRIBUTORS',		2);
define('LDR_CHA_INTERACTIONS',		3);

define('LDR_MILE_REPORT', 1);

define('LDR_BUD_ADJUSTMENT',		1);
define('LDR_BUD_RESEARCHERREMARKS',	2);
define('LDR_BUD_COMMENTS',		3);

define('LDR_RESACT_OVERALL',		1);
define('LDR_RESACT_EXCELLENCE',		2);
define('LDR_RESACT_NETWORKING',		3);
define('LDR_RESACT_HQPDEV', 		4);
define('LDR_RESACT_KTEE',   		5);
define('LDR_RESACT_OTHEROUTCOMES',  6);
define('LDR_RESACT_NETBENEFITS',   	7);
define('LDR_RESACT_NEXTPLANS',   	8);
define('LDR_RESACT_OTHER',   	    9);

define('LDR_NICOMMENTS_COMMENTS',	0);

define('LDR_BUD_JUST',              0);
define('LDR_BUD_REVISED',           1);
define('LDR_BUD_UPLOAD',            2);
define('LDR_BUD_ALLOC',             3);

// Third-level identifiers for Supplemental report: corrections on HQPs.
define('SUP_HQP_UGRAD_COUNT',		10);
define('SUP_HQP_UGRAD_DETAILS',		11);
define('SUP_HQP_MSC_COUNT',		20);
define('SUP_HQP_MSC_DETAILS',		21);
define('SUP_HQP_PHD_COUNT',		30);
define('SUP_HQP_PHD_DETAILS',		31);
define('SUP_HQP_POSTDOC_COUNT',		40);
define('SUP_HQP_POSTDOC_DETAILS',	41);
define('SUP_HQP_TECH_COUNT',		50);
define('SUP_HQP_TECH_DETAILS',		51);
define('SUP_HQP_OTHER_COUNT',		60);
define('SUP_HQP_OTHER_DETAILS',		61);

define('SUP_PUB_COUNT',			1);
define('SUP_PUB_DETAILS',		2);

define('SUP_ART_COUNT',			1);
define('SUP_ART_DETAILS',		2);

define('SUP_CONT_COUNT',		1);
define('SUP_CONT_VOLUME',		2);
define('SUP_CONT_DETAILS',		3);

define('SUP_OTH_DETAILS',		1);

define('ISAC_PHASE1_COMMENT', 1);
define('ISAC_PHASE2_COMMENT', 2);

define('PROP_DESC_THEME',           1);
define('PROP_DESC_TITLE',           2);
define('PROP_DESC_LEAD',            3);
define('PROP_DESC_OTHER',           4);
define('PROP_DESC_PART',            5);
define('PROP_DESC_ENV',             6);
define('PROP_DESC_ENV_UP',          7);
define('PROP_DESC_CONFLICT',        8);
define('PROP_DESC_CONFLICT_WHICH',  9);
define('PROP_DESC_CONFLICT_COMP',   10);
define('PROP_DESC_SUMMARY',         11);
define('PROP_DESC_PROPOSAL',        12);

define('PROP_MIL_UPLOAD',           1);

define('PROP_BUD_UPLOAD',           1);
define('PROP_BUD_JUSTIF',           2);

define('PROP_SUP_UPLOAD1',           1);
define('PROP_SUP_UPLOAD2',           2);
define('PROP_SUP_UPLOAD3',           3);
define('PROP_SUP_UPLOAD4',           4);
define('PROP_SUP_UPLOAD5',           5);

define('PROP_CCV_UPLOAD1',           1);
define('PROP_CCV_UPLOAD2',           2);
define('PROP_CCV_UPLOAD3',           3);
define('PROP_CCV_UPLOAD4',           4);
define('PROP_CCV_UPLOAD5',           5);
define('PROP_CCV_UPLOAD6',           6);
define('PROP_CCV_UPLOAD7',           7);
define('PROP_CCV_UPLOAD8',           8);
define('PROP_CCV_UPLOAD9',           9);
define('PROP_CCV_UPLOAD10',          10);

define('SAB_REVIEW_STRENGTH',        1);
define('SAB_REVIEW_WEAKNESS',        2);
define('SAB_REVIEW_RANKING',         3);

define('SAB_REPORT_SUMMARY',         1);

/* These are used for both the catalyst and translational reports */
define('CAT_DESC_THEME',           1);
define('CAT_DESC_TITLE',           2);
define('CAT_DESC_LEAD',            3);
define('CAT_DESC_OTHER',           4);
define('CAT_DESC_PART',            5);
define('CAT_DESC_ENV',             6);
define('CAT_DESC_ENV_UP',          7);
define('CAT_DESC_CONFLICT',        8);
define('CAT_DESC_CONFLICT_WHICH',  9);
define('CAT_DESC_CONFLICT_COMP',   10);
define('CAT_DESC_SUMMARY',         11);
define('CAT_DESC_ABSTRACT',        12);
define('CAT_DESC_ABSTRACT_UPLOAD', 13);
define('CAT_DESC_PROPOSAL',        14);
define('CAT_DESC_KNOW',            15);
define('CAT_DESC_TRAIN',           16);

define('CAT_BUD_UPLOAD',           1);
define('CAT_BUD_JUSTIF',           2);

define('CAT_MIL_UPLOAD',           1);

define('CAT_SUP_UPLOAD1',           1);
define('CAT_SUP_UPLOAD2',           2);
define('CAT_SUP_UPLOAD3',           3);
define('CAT_SUP_UPLOAD4',           4);
define('CAT_SUP_UPLOAD5',           5);

define('CAT_CCV_UPLOAD1',           1);
define('CAT_CCV_UPLOAD2',           2);
define('CAT_CCV_UPLOAD3',           3);
define('CAT_CCV_UPLOAD4',           4);
define('CAT_CCV_UPLOAD5',           5);
define('CAT_CCV_UPLOAD6',           6);
define('CAT_CCV_UPLOAD7',           7);
define('CAT_CCV_UPLOAD8',           8);
define('CAT_CCV_UPLOAD9',           9);
define('CAT_CCV_UPLOAD10',          10);

define('CAT_COMM_UPLOAD',           1);

/* These are used for TVN Project Reports */
// Final Report
define('FINAL_INFORMATION_START',   5);
define('FINAL_INFORMATION_END',     6);
define('FINAL_INFORMATION_ROLE',    1);
define('FINAL_INFORMATION_TIME',    2);
define('FINAL_INFORMATION_STATUS',  3);

define('FINAL_MIL_MILESTONES1',     1);
define('FINAL_MIL_MILESTONES2',     2);
define('FINAL_MIL_MILESTONES3',     6);
define('FINAL_MIL_DELIVERABLES1',   3);
define('FINAL_MIL_DELIVERABLES2',   4);
define('FINAL_MIL_DELIVERABLES3',   7);
define('FINAL_MIL_INCOMPLETE',      5);

define('FINAL_RES_KEY',         1);
define('FINAL_RES_SUMMARY',     2);
define('FINAL_RES_ABSTRACT',    3);
define('FINAL_RES_REVIEW',      4);

define('FINAL_KTEE_SUCCESS',    1);
define('FINAL_KTEE_TECH',       2);
define('FINAL_KTEE_UPLOAD',     3);

define('FINAL_PART_CONTR',      1);
define('FINAL_PART_DESC',       2);

define('FINAL_ENGAGE_PATIENT',  1);

define('FINAL_NET_ACTIVITY',    1);

define('FINAL_PLAN_FINDINGS',       6);
define('FINAL_PLAN_FINDINGS_YES',   7);
define('FINAL_PLAN_FINDINGS_NO',    8);
define('FINAL_PLAN_ADDITIONAL',     9);
define('FINAL_PLAN_ADDITIONAL_YES', 10);
define('FINAL_PLAN_THEMES',         1);
define('FINAL_PLAN_PRIORITIES',     2);
define('FINAL_PLAN_SYNTH',          3);
define('FINAL_PLAN_CREATION',       4);
define('FINAL_PLAN_TRANS',          5);

define('FINAL_ALIGN_MULTI',     1);
define('FINAL_ALIGN_CHALLENGE', 2);
define('FINAL_ALIGN_LEADER',    3);

define('FINAL_BUD_UPLOAD',      1);

// Progress Report
define('PROG_INFORMATION_START',    4);
define('PROG_INFORMATION_END',      5);
define('PROG_INFORMATION_ROLE',     1);
define('PROG_INFORMATION_TIME',     2);
define('PROG_INFORMATION_STATUS',   3);

define('PROG_MIL_MILESTONES1',      1);
define('PROG_MIL_MILESTONES2',      4);
define('PROG_MIL_MILESTONES3',      9);
define('PROG_MIL_DELIVERABLES1',    5);
define('PROG_MIL_DELIVERABLES2',    6);
define('PROG_MIL_DELIVERABLES3',    10);
define('PROG_MIL_INCOMPLETE',       7);
define('PROG_MIL_DELAYED',          8);
define('PROG_MIL_CHALLENGE',        2);
define('PROG_MIL_ACHIEVE',          3);

define('PROG_KTEE_SUCCESS',     1);
define('PROG_KTEE_TECH',        2);
define('PROG_KTEE_UPLOAD',      3);

define('PROG_PART_CONTR',       1);
define('PROG_PART_DESC',        2);

define('PROG_ENGAGE_PATIENT',   1);

define('PROG_NET_ACTIVITY',     1);

define('PROG_PLAN_THEMES',          1);
define('PROG_PLAN_PRIORITIES',      2);
define('PROG_PLAN_SYNTH',           3);
define('PROG_PLAN_CREATION',        4);
define('PROG_PLAN_TRANS',           5);

define('PROG_BUD_UPLOAD',           1);

// IFP Final Report
define('IFP_FINAL_MIL_START',           21);
define('IFP_FINAL_MIL_END',             22);
define('IFP_FINAL_MIL_MILESTONES1',     1);
define('IFP_FINAL_MIL_MILESTONES2',     14);
define('IFP_FINAL_MIL_INCOMPLETE',      15);
define('IFP_FINAL_MIL_DELAYED',         16);
define('IFP_FINAL_MIL_DELIVERABLES1',   17);
define('IFP_FINAL_MIL_DELIVERABLES2',   18);
define('IFP_FINAL_MIL_DELETED',         19);
define('IFP_FINAL_MIL_DELAYED2',        20);
define('IFP_FINAL_MIL_CHALLENGE',       2);
define('IFP_FINAL_MIL_KEY',             3);
define('IFP_FINAL_MIL_SUMMARY',         4);
define('IFP_FINAL_MIL_ABSTRACT',        5);
define('IFP_FINAL_MIL_REVIEW',          6);
define('IFP_FINAL_MIL_THEMES',          7);
define('IFP_FINAL_MIL_PRIORITIES',      8);
define('IFP_FINAL_MIL_CARE',            9);
define('IFP_FINAL_MIL_IMPACT',          10);
define('IFP_FINAL_MIL_SYNTH',           11);
define('IFP_FINAL_MIL_CREATION',        12);
define('IFP_FINAL_MIL_TRANS',           13);

define('IFP_FINAL_KTEE_SUCCESS',    1);

define('IFP_FINAL_EXTERNAL_PLACEMENT',  1);

define('IFP_FINAL_MENTORSHIP_PROGRAM',  1);

define('IFP_FINAL_COLLAB_MILESTONE',    1);
define('IFP_FINAL_COLLAB_PROGRESS',     2);
define('IFP_FINAL_COLLAB_BENEFITS',     3);

define('IFP_FINAL_DISS_OTHER',          1);
define('IFP_FINAL_DISS_UPLOAD',         2);

define('IFP_FINAL_PARTNERS_FUNDING',    1);

define('IFP_FINAL_NETWORK_MEETING',     1);

define('IFP_FINAL_COMMENTS_FEEDBACK',           1);
define('IFP_FINAL_COMMENTS_ACADEMIC',           2);
define('IFP_FINAL_COMMENTS_ACADEMIC_DETAILS',   3);
define('IFP_FINAL_COMMENTS_EMPLOYMENT',         4);
define('IFP_FINAL_COMMENTS_EMPLOYMENT_DETAILS', 5);

define('IFP_FINAL_SUP_MULTI',           1);
define('IFP_FINAL_SUP_SOCIAL',          2);
define('IFP_FINAL_SUP_LEADER',          3);
define('IFP_FINAL_SUP_ASSESSMENT',      4);
define('IFP_FINAL_SUP_ELABORATE',       5);

// IFP Progress Report
define('IFP_PROG_MIL_START',            6);
define('IFP_PROG_MIL_END',              7);
define('IFP_PROG_MIL_MILESTONES',       1);
define('IFP_PROG_MIL_DELETED',          4);
define('IFP_PROG_MIL_DELAYED',          5);
define('IFP_PROG_MIL_CHALLENGE',        2);
define('IFP_PROG_MIL_PLACEMENT',        3);

define('IFP_PROG_MENTORSHIP_FREQ',      1);
define('IFP_PROG_MENTORSHIP_TIMES',     2);
define('IFP_PROG_MENTORSHIP_PFSS',      3);
define('IFP_PROG_MENTORSHIP_PROGRAM',   4);

define('IFP_PROG_COLLAB_MILESTONE',     1);
define('IFP_PROG_COLLAB_PROGRESS',      2);
define('IFP_PROG_COLLAB_BENEFITS',      3);

define('IFP_PROG_DISS_OTHER',           1);
define('IFP_PROG_DISS_UPLOAD',          2);

define('IFP_PROG_CAPACITY_ACTIVITY',    1);
define('IFP_PROG_CAPACITY_ACADEMIC',    2);
define('IFP_PROG_CAPACITY_ACADEMIC_DETAILS', 4);
define('IFP_PROG_CAPACITY_EMPLOYMENT',  3);
define('IFP_PROG_CAPACITY_EMPLOYMENT_DETAILS', 5);

define('IFP_PROG_PARTNERS_FUNDING',     1);

define('IFP_PROG_NETWORK_MEETING',      1);

define('IFP_PROG_PLAN_THEMES',          1);
define('IFP_PROG_PLAN_PRIORITIES',      2);
define('IFP_PROG_PLAN_CARE',            3);
define('IFP_PROG_PLAN_IMPACT',          4);
define('IFP_PROG_PLAN_SYNTH',           5);
define('IFP_PROG_PLAN_CREATION',        6);
define('IFP_PROG_PLAN_TRANS',           7);

define('IFP_PROG_COMMENTS_FEEDBACK',    1);

define('IFP_PROG_SUP_ASSESSMENT',       1);
define('IFP_PROG_SUP_ELABORATE',        2);

// SSA Report
define('SSA_HQP_GRANT',     1);
define('SSA_HQP_START',     2);
define('SSA_HQP_TERM',      3);
define('SSA_HQP_COMPLETED', 4);
define('SSA_HQP_TRAIN',     5);
define('SSA_HQP_EXP',       6);
define('SSA_HQP_THESIS',    7);
define('SSA_HQP_GRAD',      8);
define('SSA_HQP_EMPL',      9);
define('SSA_HQP_WHERE',     10);

define('SSA_START',         1);
define('SSA_END',           2);
define('SSA_TIME',          3);
define('SSA_NO',            4);
define('SSA_ANOTHER',       5);
define('SSA_ACHIEVEMENTS',  6);
define('SSA_PRIORITIES',    7);
define('SSA_THEMES',        8);
define('SSA_CARE',          9);
define('SSA_IMPACT',        10);
define('SSA_SYNTH',         11);
define('SSA_CREATION',      12);
define('SSA_MOBILIZATION',  13);
define('SSA_INSTITUTION',   14);
define('SSA_LEARNING',      15);

// IFP Review
define('IFP_REVIEW_RELEVANCE',  1);
define('IFP_REVIEW_MOTIVATION', 2);
define('IFP_REVIEW_CAPACITY',   3);
define('IFP_REVIEW_SUPPORT',    4);
define('IFP_REVIEW_REFEREE',    5);
define('IFP_REVIEW_COMMENTS',   6);
define('IFP_REVIEW_FUNDING',    7);

//FEC Report

// Transformative Review
define('TRANS_SRC_TOPIC',       1);
define('TRANS_SRC_GAP',         2);
define('TRANS_SRC_QUESTION',    3);
define('TRANS_SRC_METHODOLOGY', 4);
define('TRANS_SRC_DESIGN',      5);
define('TRANS_SRC_PROCEDURES',  6);
define('TRANS_SRC_OUTCOMES',    7);
define('TRANS_SRC_INTEGRATION', 8);
define('TRANS_SRC_RECOGNITION', 9);
define('TRANS_SRC_COMMENTS1',   14);
define('TRANS_SRC_EXPERIENCE',  10);
define('TRANS_SRC_EXPERTISE',   11);
define('TRANS_SRC_BUDGET',      12);
define('TRANS_SRC_OBJECTIVES',  13);
define('TRANS_SRC_COMMENTS2',   15);

define('TRANS_RMC_GRANT',       1);
define('TRANS_RMC_IMPACT',      2);
define('TRANS_RMC_POLICY',      3);
define('TRANS_RMC_MISSION',     4);
define('TRANS_RMC_PRIORITIES',  5);
define('TRANS_RMC_NETWORKING',  6);
define('TRANS_RMC_PARTNERSHIPS',7);
define('TRANS_RMC_HQP',         8);
define('TRANS_RMC_ENGAGEMENT',  9);
define('TRANS_RMC_COMMENTS',    10);
define('TRANS_RMC_SUGGESTIONS', 11);

/*
 * There are no third-level identifiers for Evaluator Report.  Those are used
 * to identify a user ID or project ID, which is the focus of the evaluation.
 *
 * The kind of ID depends on the report type: user ID if the report is of type
 * RP_EVAL_RESEARCHER; project ID if report type is RP_EVAL_PROJECT.
 */

// Sub-Items
define('RES_RESACT_PHASE1',     1);
