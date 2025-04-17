<?php

/**
 * Textual blob types.
 *
 * These instruct the renderer to act accordingly.
 */
define('BLOB_TEXT',		1);

/**
 * Structured blobs.
 *
 * Most structured blobs can be handled as arrays, which are serialized for
 * storage, automatically.
 */
define('BLOB_ARRAY',    1024);


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

define('SEC_NONE',              0); 
