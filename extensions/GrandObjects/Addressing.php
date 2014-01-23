<?php

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
define('RP_RESEARCHER',		1);
define('RP_HQP',			2);
define('RP_LEADER',			3);
define('RP_EVAL_RESEARCHER',4);
define('RP_EVAL_PROJECT',	5);
define('RP_EVAL_LOI',		9);
define('RP_SUPPLEMENTAL',	6);
define('RP_EVAL_PDF',		7);
define('RP_EVAL_CNI',		8);
define('RP_EVAL_LOI_FEED',	10);
define('RP_REVIEW', 11);
define('RP_MTG', 12);
define('RP_CHAMP', 13);
define('RP_ISAC', 14);
define('RP_PROJECT_CHAMP', 15);
define('RP_PROJECT_ISAC', 16);
//define('RP_EVAL_RESEARCHER_REV',		9); //Revised answers
//define('RP_EVAL_CNI_REV',		10); //Revised answers
//define('RP_EVAL_PROJECT_REV', 11); //Revised answers


/*
 * Second-level identifiers: report section.
 *
 * These comprise high-level parts within a report, such as personal information,
 * budget, publications, and so on.  Each report type has its own section
 * definitions.
 */
define('SEC_NONE',              0); 

define('RES_EFFORT',			1);
define('RES_MILESTONES',		2);
define('RES_PEOPLE_INTERACT',		3);
define('RES_PROJECT_INTERACT',		4);
define('RES_IMPACT',			5);
define('RES_HQPSELECTION',		6);
define('RES_HQPREPORT',			7);
define('RES_BUDGET',			8);
define('RES_PUBLICATIONS',		9);
define('RES_ARTIFACTS',			10);
define('RES_CONTRIBUTIONS',		11);
define('RES_RESACTIVITY',		12);
define('RES_BUDGET_PNIADMIN',	13);
define('RES_ALLOC_BUDGET',      14);

define('HQP_DEMOGRAPHIC',		1);
define('HQP_EFFORT',			2);
define('HQP_MILESTONES',		3);
define('HQP_PEOPLE_INTERACT',		4);
define('HQP_PROJECT_INTERACT',		5);
define('HQP_IMPACT',			6);
define('HQP_RESACTIVITY',		7);

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

//Evaluator LOI
define('EVL_LOI1',		1);
define('EVL_LOI2',		2);
define('EVL_LOI3',		3);
define('EVL_LOI4',		4);
define('EVL_LOI5',		5);
define('EVL_LOI6',		6);
define('EVL_LOI7',		7);
define('EVL_LOI8',		8);
define('EVL_LOI9',		9);
define('EVL_LOI10',		10);
define('EVL_LOI11',		11);
define('EVL_LOI12',		12);
define('EVL_LOI13',		13);
define('EVL_LOI14',		14);
define('EVL_LOI15',		15);

// Second-level identifiers for Supplemental Report.
define('SUP_HQPS',			1);
define('SUP_PUBLICATIONS',		2);
define('SUP_ARTIFACTS',			3);
define('SUP_CONTRIBUTIONS',		4);
define('SUP_OTHER',			5);
define('SUP_BUDGET',			6);

// Second level for Eval PDFs
define('PDF_PNI',			1);
define('PDF_PROJ',			2);

// Second level for Mind The Gap
define('MTG_INTRO', 1);
define('MTG_MUSIC', 2);
define('MTG_FIRST_NATIONS', 3);
define('MTG_SOCIAL_PROBLEMS', 4);
define('MTG_OTHER', 5);

/*
 * Third-level identifiers: question within a section.
 *
 * Each section in the report can have an arbitrary amount of questions.  These
 * questions are, thus, section-specific, and relatively numerous.  Constants
 * are prefixed with the section they belong to in order to make it manageable.
 */

define('RES_MIL_NOTAPPLICABLE',		1);
define('RES_MIL_CONTRIBUTIONS',		2);
define('RES_MIL_PRIMCRITERIA',		3);
define('RES_MIL_SECCRITERIA',		4);
define('RES_MIL_SUMMARY',		    5);

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

define('HQP_DEM_LEVEL',			1);
define('HQP_DEM_GENDER',		2);
define('HQP_DEM_FULLNAME',		3);
define('HQP_DEM_CITIZENSHIP',		4);

define('HQP_EFF_HOURS',			1);
define('HQP_EFF_MONTHS',		2);
define('HQP_EFF_REMARKS',		3);

define('HQP_MIL_NOTAPPLICABLE',		1);
define('HQP_MIL_CONTRIBUTIONS',		2);
define('HQP_MIL_PRIMCRITERIA',		3);
define('HQP_MIL_SECCRITERIA',		4);

define('HQP_RESACT_OVERALL',		1);
define('HQP_RESACT_EXCELLENCE',		2);
define('HQP_RESACT_NETWORKING',		3);
define('HQP_RESACT_KTEE',   		4);

define('CHAMP_REPRESENT', 1);
define('CHAMP_ACTIVITY', 2);
define('CHAMP_ORG', 3);
define('CHAMP_BENEFITS', 4);
define('CHAMP_SHORTCOMINGS', 5);
define('CHAMP_CASH', 6);
define('CHAMP_RESEARCHERS', 7);

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

//Third level Eval LOI => Only 2 types for each question: Y/N radio(YN) AND Comment (C)
define('EVL_LOI_YN',	1);
define('EVL_LOI_C',		2);

/*
 * There are no third-level identifiers for Evaluator Report.  Those are used
 * to identify a user ID or project ID, which is the focus of the evaluation.
 *
 * The kind of ID depends on the report type: user ID if the report is of type
 * RP_EVAL_RESEARCHER; project ID if report type is RP_EVAL_PROJECT.
 */

