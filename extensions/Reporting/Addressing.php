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
