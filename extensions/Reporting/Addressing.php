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
define('RP_RESEARCHER',		1);

define('SEC_NONE',              0); 
