<?php

// $Id: SessionData.php 611 2011-07-22 16:18:41Z dwt $

// Default handlers.
define('SD_REPORT', 0);		// Regular report area.
define('SD_BUDGET_CSV', 1);	// Budget report in CSV format.
define('SD_BUDGET_EXCEL', 2);	// Budget report in Excel format (original).
define('SD_SUPPL_REPORT', 3);	// Supplemental report area.
define('SD_SUPPL_BUDGET', 4);	// Supplemental single-year budget (original).

class SessionData {
	private $_userid;
	private $_page;
	private $_tstamp;
	private $_data;		// Useful?
	private $_table;
	private $_s_id;		// Session ID.
	private $_handle;

	// Pre-formatted query.
	private $_fetch;

	function __construct($user, $page, $handle = 1) {
		$this->_table = getTableName("session_data");
		$this->_page = mysql_real_escape_string($page);
		$this->_handle = $handle;
		$this->_tstamp = null;

		if (is_numeric($user)) {
			$this->_userid = $user;
		}
		else {
			// Got username, resolve it.
			$tbl = getTableName("user");
			$sql = "SELECT user_id FROM $tbl WHERE user_name = '$user'";
			$res = DBFunctions::execSQL($sql);
			if (DBFunctions::getNRows() == 0 || !is_numeric($row[0])) {
				$this->_userid = 0;
			}
			else {
				$this->_userid = $res[0];
			}
		}

		$this->refresh();
	}

	private function refresh() {
		// Check whether an old session is available.
		$sql = "SELECT session_id, timestamp FROM {$this->_table} WHERE user_id = {$this->_userid} AND page LIKE '%{$this->_page}' AND handle = {$this->_handle} ORDER BY timestamp DESC;";
		$res = DBFunctions::execSQL($sql);
		if (DBFunctions::getNRows() > 0) {
			$this->_s_id = $res[0]['session_id'];
			$this->_tstamp = $res[0]['timestamp'];

			// Format the query for this instance.
			$this->_fetch = "SELECT timestamp, data FROM {$this->_table} WHERE session_id = {$this->_s_id};";
		}
		else {
			$this->_s_id = false;
		}
	}


	// Fetches saved session data for the pair user:page.  If there is no
	// data saved, returns false.
	// If the data must not be serialized, the argument "false" must be
	// supplied since the normal behavior is to serialize data included in
	// the table for session data.
	function fetch($do_unserialize = true) {
		if ($this->_s_id === false) {
			// There is no session associated with this instance.
			return false;
		}

		$res = DBFunctions::execSQL($this->_fetch);
		if (count($res) == 0) {
			return false;
		}

		$this->_tstamp = @$res[0][0];
		if ($do_unserialize) {
			$this->_data = @unserialize($res[0][1]);
		}
		else {
			$this->_data = $res[0][1];
		}

		return $this->_data;
	}


	// Stores session data for the pair user:page.  The data is updated in
	// the database if the session was previously registered, overwriting
	// the old data.
	// The data is normally stored in a serialized form.  If the callee does
	// not want the data to be serialized, the function must be call with
	// an extra "false" argument, and remember to do the same when fetching
	// the data.
	function store(&$data, $do_serialize = true) {
		$data_esc = null;
		if ($do_serialize) {
			// Avoid issues.
			$data_esc = mysql_real_escape_string(serialize($data));
		}
		else {
			$data_esc = mysql_real_escape_string($data);
		}

		if ($this->_s_id === false) {
			// No session associated yet.  Insert a new entry, and
			// save the new session ID.
			DBFunctions::execSQL("INSERT INTO {$this->_table} (user_id, page, handle, data) VALUES ({$this->_userid}, '{$this->_page}', {$this->_handle}, '{$data_esc}');", true);
		}
		else {
			DBFunctions::execSQL("UPDATE {$this->_table} SET data = '{$data_esc}' WHERE session_id = {$this->_s_id};", true);
		}

		$this->refresh();
		return true;
	}


	// Removes the session from the database.
	function remove() {
		if ($this->_s_id !== false) {
			DBFunctions::execSQL("DELETE FROM {$this->_table} WHERE session_id = {$this->_s_id};", true);
			return true;
		}

		return false;
	}


	// Retrieves when the data was last updated, or false in case of error.
	function last_update() {
		if ($this->_s_id === false) {
			// A relevant session data was never created in the past.
			return false;
		}

		if ($this->_tstamp === null) {
			$res = DBFunctions::execSQL("SELECT timestamp FROM {$this->_table} WHERE session_id = {$this->_s_id};");
			if (count($res) > 0) {
				$this->_tstamp = $res[0][0];
			}
			else {
				return false;
			}
		}

		return $this->_tstamp;
	}


	// Dangerous clean-up: removes all sessions for the user in this
	// page, without caring about session ID.  Useful in the case where
	// stale data piles up.
	// Should be useful when the session data is no longer necessary or
	// relevant for that user:page pair.
	function wipe() {
		DBFunctions::execSQL("DELETE FROM {$this->_table} WHERE user_id = {$this->_userid} AND page = '{$this->_page}' AND handle = {$this->_handle};", true);
		$this->_s_id = false;

		return true;
	}

	// Queries the metadata of a session data entry, given by #id.
	// Returns an associative array with user_id, page, handle, and
	// timestamp.
	static function query_id($id) {
		$ret = array();
		if (!is_numeric($id)) {
			return $ret;
		}
		$res = DBFunctions::execSQL("SELECT user_id, page, handle, timestamp FROM mw_session_data WHERE session_id = {$id};");
		if (DBFunctions::getNRows() > 0) {
			// Copy the array.
			$ret = $res[0];
		}

		return $ret;
	}

	/// Returns a resultset with all users that have data stored in the given
	/// page #page and handle #hnd, and the timestamp of the respective entry.
	/// If #unixfmt is true, then the timestamp returned is in Unix Epoch format.
	/// Optionally, the users can be filtered by a group or array of groups the
	/// user should belong.
	static function list_users_in($page, $hnd, $grpfilter = "", $unixfmt = false) {
		if (!empty($grpfilter)) {
			if (is_array($grpfilter)) {
				$grpfilter = implode("','", $grpfilter);
			}

			$grpfilter = "s1.user_id IN (SELECT DISTINCT ug_user FROM mw_user_groups WHERE ug_group IN ('{$grpfilter}')) AND";
		}	
		$tst = ($unixfmt) ? 'UNIX_TIMESTAMP(s1.timestamp)' : 's1.timestamp';
		return DBFunctions::execSQL("SELECT DISTINCT s1.user_id, {$tst} FROM mw_session_data s1 WHERE {$grpfilter} s1.timestamp IN (SELECT MAX(s2.timestamp) FROM mw_session_data s2 WHERE s1.user_id = s2.user_id AND handle = {$hnd} AND page LIKE '%{$page}');");
	}
}
?>
