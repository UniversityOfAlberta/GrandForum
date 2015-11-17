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


// Schema
// CREATE TABLE  `grand_migration`.`grand_report_blobs` (
// `blob_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
// `year` SMALLINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  'Reporting cycle',
// `user_id` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  'User ID',
// `proj_id` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  'Project ID',
// `rp_type` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  'Report type',
// `rp_section` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  'Report section',
// `rp_item` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  'Section item',
// `rp_subitem` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  'Section subitem',
// `changed` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT 0 COMMENT  'Last-change timestamp',
// `blob_type` SMALLINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  'Type of blob data',
// `data` LONGBLOB NOT NULL COMMENT  'Blob contents',
// INDEX (  `year` ,  `user_id` ,  `proj_id` ,  `rp_type` ,  `rp_section` ,  `rp_item` ,  `rp_subitem` )
// ) ENGINE = INNODB;

/**
 * @package GrandObjects
 */

class ReportBlob {

    static $cache = array();
    static $counter = 0;
    
	/// Blob type (TEXT, HTML, CSV, ...), as defined in the constants before
	/// this class declaration.
	private	$_type;

	/// ID that uniquely identifies this blob; should be used in Morteza's
	/// framework engine.
	private $_blob_id;

	/// User that owns this blob (if any; usually yes).
	private $_owner_id;

	/// Project associated with this blob (if any).
	private $_proj_id;

	/// Address component of this blob.
	private $_address;

	/// The data.
	private $_data;
	/// Data transformed, for storing in the database.
	private $_data_transformed;

	/// Last changed.
	private $_changed;
	
	// The hash for this blob (based on time of creation, user, project etc.)
	private $_md5;

	function __construct($type = BLOB_NULL, $year = 0, $owner = 0, $proj = 0) {
		$this->_type = $type;
		$this->_year = substr($year, 0, 4); // Make sure that a full date string isn't passed

		if ($owner instanceof Person)
			$this->_owner_id = $owner->getId();
		else if (is_numeric($owner))
			$this->_owner_id = $owner;
		else
			// Bad argument, but carry on (callee maybe wants to load from database).
			$this->_owner_id = false;

		if ($proj instanceof Project)
			$this->_proj_id = $proj->getId();
		else if (is_numeric($proj))
			$this->_proj_id = $proj;
		else
			$this->_proj_id = false;

		$this->_address = null;
		$this->_data = null;
		$this->_data_transformed = null;
		$this->_blob_id = null;
		$this->_changed = null;
		$this->_md5 = null;
	}


	// Getters.
	public function getAddress() {
		return $this->_address;
	}

	public function getData() {
		return $this->_data;
	}

	public function getId() {
		return $this->_blob_id;
	}

	public function getLastChanged() {
		return $this->_changed;
	}

	public function getOwnerId() {
		return $this->_owner_id;
	}

	public function getProjectId() {
		return $this->_proj_id;
	}

	public function getType() {
		return $this->_type;
	}

    public function getMD5(){
        return $this->_md5;
    }

	/// Stores the blob data at the specified address.  If the address is not
	/// known internally ($this->_address), and the argument is empty, the
	/// call will fail and nothing will be written to the database.
	///
	/// Note: some blob types are automatically serialized/unserialized.
	///
	/// The #address parameter is expected to be an array created using the
	/// ::create_address() method.
	///
	/// Throws:
	///	- InvalidArgumentException (serialize() returned false, or address
	///	  components have a blank key and/or associated value).
	///	- DomainException (blob type is null).
	///	- UnexpectedValueException (unknown blob type).
	public function store(&$data, $address = null) {
		// Some checks before trying to actually store data.
		global $wgImpersonating, $wgRealUser;
		Cache::delete($this->getCacheId($address));
		if ($address === null && $this->_address === null)
			return false;

		if ($this->_owner_id === false || $this->_proj_id === false)
			return false;

		if (! is_array($address) || count($address) == 0)
			return false;

		// Perform adjustments to the data: serialization (if needed), and escaping.
		switch ($this->_type) {
		case BLOB_TEXT:
		case BLOB_HTML:
		case BLOB_WIKI:
		case BLOB_PDF:
		case BLOB_EXCEL:
		case BLOB_RAW:
			// Don't transform the data.
			$this->_data = $data;
			break;
		case BLOB_ARRAY:
		case BLOB_CSV:
		case BLOB_OPTIONANDTEXT:
		case BLOB_TEXTANDAPPROVE:
		case BLOB_ARTIFACT:
		case BLOB_PUBLICATION:
		case BLOB_NEWMILESTONE:
		case BLOB_CURRENTMILESTONE:
		case BLOB_MILESTONESTATUS:
		case BLOB_CONTRIBUTION:
			// Serialize.
			$this->_data = serialize($data);
			// Check serialization.
			if ($this->_data === false)
				throw new InvalidArgumentException('Error during serialization().');
			break;

		case BLOB_NULL:
			// Null blob -- complain about it.
			throw new DomainException('Attempted to store a blob with type BLOB_NULL.');

		default:
			// Unexpected type -- complain about it.
			throw new UnexpectedValueException('Attempted to store a blob of an unknown type (new type registered?).');
		}

		// Prepare data for writing into the database.
		$this->_data_transformed = DBFunctions::escape($this->_data);

		// Prepare the address for an SQL statement.
		$where_list = array();
		foreach ($address as $ind => $val) {
			if (strlen($ind) == 0 || strlen($val) == 0)
				throw new InvalidArgumentException("Empty blob address for key='{$ind}', val='{$val}'.");

			$where_list[] = " {$ind} = '{$val}'";
		}
		$where = implode(' AND ', $where_list);

		// Fetch row ID from the database if there is data for this
		// #address (making this an update query), else insert a new
		// row in the database.
		$res = DBFunctions::execSQL("SELECT blob_id, data FROM grand_report_blobs WHERE " .
			"user_id = {$this->_owner_id} AND " .
			"year = {$this->_year} AND " .
			"proj_id = {$this->_proj_id} AND {$where};");
	    $impersonateId = $this->_owner_id;
		if (count($res) > 0) {
			// Update query.
			$this->_blob_id = $res[0]['blob_id'];
			
			$status = DBFunctions::execSQL("UPDATE grand_report_blobs SET data = '{$this->_data_transformed}', " .
				"blob_type = {$this->_type} ," .
				"edited_by = {$impersonateId} " .
				"WHERE blob_id = {$this->_blob_id}", true);
	        if($wgImpersonating){
	            $oldData = DBFunctions::escape($res[0]['data']);
	            $impersonateId = $wgRealUser->getId();
	            $sql = "INSERT INTO `grand_report_blobs_impersonated` (`blob_id`, `user_id`, `previous_value`, `current_value`)
	                    VALUES ('{$this->_blob_id}', '{$impersonateId}', '{$oldData}', '{$this->_data_transformed}')";
	            DBFunctions::execSQL($sql, true);
	        }
		}
		else {
			// Insert query.
			$md5_keys = implode('_', array_values($address));
	        $md5 = md5("{$this->_proj_id}_{$this->_owner_id}_{$md5_keys}_".time()."_".self::$counter++);
			$insert_keys = implode(',', array_keys($address));
			$insert_data = "'".implode("','", array_values($address))."'";
			DBFunctions::execSQL("INSERT INTO grand_report_blobs " .
				"(edited_by, year, user_id, proj_id, {$insert_keys}, blob_type, data, md5) " .
				"VALUES ({$impersonateId}, {$this->_year}, {$this->_owner_id}, {$this->_proj_id}, " .
				"{$insert_data}, {$this->_type}, '{$this->_data_transformed}', '{$md5}');", true);
			if($wgImpersonating){
			    $res = DBFunctions::execSQL("SELECT blob_id FROM grand_report_blobs WHERE " .
			                                "user_id = {$this->_owner_id} AND " .
			                                "year = {$this->_year} AND " .
			                                "proj_id = {$this->_proj_id} AND {$where};");
			    if(count($res) > 0){
			        $blob_id = $res[0]['blob_id'];
			        $oldData = "";
	                $impersonateId = $wgRealUser->getId();
	                $sql = "INSERT INTO `grand_report_blobs_impersonated` (`blob_id`, `user_id`, `previous_value`, `current_value`)
	                        VALUES ('$blob_id', '{$impersonateId}', '$oldData', '{$this->_data_transformed}')";
	                DBFunctions::execSQL($sql, true);
	            }
	        }
		}
		DBFunctions::commit();
		//Cache::store($this->getCacheId($address), $data);
		return true;
	}


	/// Populates internal state with a complete row from the database.
	private function populate(&$dbdata) {
		$this->_blob_id = $dbdata['blob_id'];
		$this->_year = $dbdata['year'];
		$this->_owner_id = $dbdata['user_id'];
		$this->_proj_id = $dbdata['proj_id'];
		$this->_address = self::create_address($dbdata['rp_type'], $dbdata['rp_section'],
			                                   $dbdata['rp_item'], $dbdata['rp_subitem']);
		$this->_changed = $dbdata['changed'];
		$this->_type = $dbdata['blob_type'];
		$this->_md5 = $dbdata['md5'];

		// Undo data transformations, if necessary.
		switch ($this->_type) {
		case BLOB_ARRAY:
		case BLOB_CSV:
		case BLOB_OPTIONANDTEXT:
		case BLOB_TEXTANDAPPROVE:
		case BLOB_ARTIFACT:
		case BLOB_PUBLICATION:
		case BLOB_NEWMILESTONE:
		case BLOB_MILESTONESTATUS:
		case BLOB_CURRENTMILESTONE:
		case BLOB_CONTRIBUTION:
			// Unserialize.
			$this->_data = unserialize($dbdata['data']);
			if ($this->_data === false)
				throw new RuntimeException("Unserialization of blob #{$this->_blob_id} failed.");
			break;

		default:
			// Assume any other method does not need transforming.
			$this->_data = $dbdata['data'];
		}

		return true;
	}


	/// Loads the complete record (data + metadata) for a given address.
	///
	/// If #address is not unique (ie, it is not specific enough to fetch a single
	/// entry from the database), a DomainException is thrown and the internal state
	/// of the Blob instance is unchanged.
	public function load($address = null, $skipCache=false) {
	    $cacheId = $this->getCacheId($address);
	    if(Cache::exists($cacheId) && !$skipCache){
	        $this->_data = Cache::fetch($cacheId);
	        return true;
	    }
		// Some checks before going to the database.
		if ($address === null && $this->_address === null)
			return false;

		if ($this->_owner_id === false || $this->_proj_id === false)
			return false;

		if (! is_array($address) || count($address) == 0)
			return false;

		// Prepare the address for an SQL statement.
		$where_list = array();
		foreach ($address as $ind => $val) {
			if (strlen($ind) == 0 || strlen($val) == 0)
				throw new InvalidArgumentException("Empty blob address for key='{$ind}', val='{$val}'.");

			$where_list[] = " {$ind} = '{$val}'";
		}
		$where = implode(' AND ', $where_list);

		// Load all data from database.
		$sql = "SELECT * FROM grand_report_blobs WHERE " .
			"user_id = {$this->_owner_id} AND " .
			"year = {$this->_year} AND " .
			"proj_id = {$this->_proj_id} AND {$where};";
		$res = DBFunctions::execSQL($sql);
        $ret = false;
		switch (count($res)) {
		case 0:
			// No data.
			$ret = false;
            break;
		case 1:
			// Populate internal state.
			$ret = $this->populate($res[0]);
            break;
		default:
			// Collision.
			//echo ">>>> Offending SQL:\n{$sql}\n";
			throw new DomainException('Address leads to ambiguous data.');
		}
		Cache::store($cacheId, $this->_data);
		return $ret;
	}

    private function getCacheId($address){
        $id = "{$this->_type}_{$this->_year}_{$this->_owner_id}_{$this->_proj_id}_";
        if($address != null){
            $id .= implode("_", $address);
        }
        return $id;
    }

	/// Loads the complete record (data + metadata) for a given blob ID.
	public function loadFromId($id = null) {
		if ($id == null || !is_numeric($id))
			return false;

        $res = DBFunctions::select(array('grand_report_blobs'),
                                   array('*'),
                                   array('blob_id' => EQ($id)));
		if (count($res) > 0)
			// MySQL enforces unique ID.
			return $this->populate($res[0]);
		else
			// No data.
			return false;
	}

    /// Loads the complete record (data + metadata) for a given blob MD5.
	public function loadFromMD5($id = null) {
		if ($id == null)
			return false;

        $res = DBFunctions::select(array('grand_report_blobs'),
                                   array('*'),
                                   array('md5' => EQ($id)));
		if (count($res) > 0)
			// MySQL enforces unique ID.
			return $this->populate($res[0]);
		else
			// No data.
			return false;
	}

	/// Assemble a addressing array based on the arguments received.
	public static function create_address($rptype = null, $section = null, $item = null, $subitem = null) {
		$ret = array();

		if ($rptype !== null)
			$ret['rp_type'] = $rptype;
		if ($section !== null)
			$ret['rp_section'] = $section;
		if ($item !== null)
			$ret['rp_item'] = $item;
		if ($subitem !== null)
			$ret['rp_subitem'] = $subitem;

		return $ret;
	}

}
