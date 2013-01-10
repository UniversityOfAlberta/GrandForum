<?php

/// Encapsulates the storage and retrieval of reports generated as PDF files.
class ReportStorage {
	private $_uid;
	private $_person;

	private $_cache;

	function __construct($person) {
		$this->_person = $person;
		$this->_uid = $person->getId();
		$this->_cache = null;
	}

	/// Tries to "select" a report for download.  If the user_id does not
	/// match, the request is denied and the state of the object is not
	/// changed.
	function select_report($tok, $strict = true) {
		if ($strict)
			$ext = "user_id = {$this->_uid} AND";
		else
			$ext = "";

		$res = DBFunctions::execSQL("SELECT user_id FROM mw_pdf_report WHERE {$ext} token = '{$tok}';");
		if (DBFunctions::getNRows() > 0) {
			$this->load_metadata($tok, $strict);
			return $this->_cache['token'];
		}

		return false;
	}

	/// Store a new report.
	function store_report(&$data, &$html, &$pdf, $special = 0, $auto = 0, $type = 0, $year = REPORTING_YEAR) {
	    global $wgImpersonating, $wgRealUser, $wgUser;
	    $impersonateId = $this->_uid;
	    if($wgImpersonating){
	        $impersonateId = $wgRealUser->getId();
	    }
	    else{
	        $impersonateId = $wgUser->getId();
	    }
	    
		$uname = $this->_person->getName();

		$tst = time();
		$sdata = serialize($data);
		$hdata = sha1($sdata);
		$hpdf = sha1($pdf);
		$len = strlen($pdf);

		// The token is the MD5 digest of the user ID, user name, timestamp,
		// the hash of the data and the hash of PDF file.
		$tok = md5($this->_uid . $uname . $tst . $hdata . $hpdf);

		$sql = "INSERT INTO mw_pdf_report (user_id, generation_user_id, year, type, special, auto, token, timestamp, len_pdf, hash_data, hash_pdf, data, html, pdf) VALUES ({$this->_uid}, {$impersonateId}, {$year}, {$type}, {$special}, {$auto}, '{$tok}', FROM_UNIXTIME({$tst}), '{$len}', '{$hdata}', '{$hpdf}', '" .
			mysql_real_escape_string($sdata) . "', '" .
			mysql_real_escape_string($html) . "', '" .
			mysql_real_escape_string($pdf) . "');";

		DBFunctions::execSQL($sql, true);
		// Update metadata.
		return $this->load_metadata($tok);
	}

	/// Retrieves a specific report (PDF) for the user.  The PDF returned
	/// (if any) is a string.
	function fetch_pdf($tok, $strict = true) {
		if ($this->_cache !== null && isset($this->_cache['pdf']) &&
				isset($this->_cache['token']) && $this->_cache['token'] === $tok) {
			return $this->_cache['pdf'];
		}

		$user = ($strict) ? "user_id = {$this->_uid} AND" : '';
		$sql = "SELECT report_id, user_id, type, submitted, auto, timestamp, len_pdf, pdf, generation_user_id, submission_user_id, year FROM mw_pdf_report WHERE {$user} token = '{$tok}' ORDER BY timestamp DESC LIMIT 1;";
		$res = DBFunctions::execSQL($sql);
		if (count($res) <= 0) {
			return false;
		}

		$this->_cache['report_id'] = $res[0]['report_id'];
		$this->_cache['user_id'] = $res[0]['user_id'];
		$this->_cache['type'] = $res[0]['type'];
		$this->_cache['submitted'] = $res[0]['submitted'];
		$this->_cache['auto'] = $res[0]['auto'];
		$this->_cache['token'] = $tok;
		$this->_cache['timestamp'] = $res[0]['timestamp'];
		$this->_cache['year'] = $res[0]['year'];
		$this->_cache['len_pdf'] = $res[0]['len_pdf'];
		$this->_cache['generation_user_id'] = $res[0]['generation_user_id'];
		$this->_cache['submission_user_id'] = $res[0]['submission_user_id'];

		return $res[0]['pdf'];
	}
	
	function fetch_html($tok){
	    $tok = mysql_real_escape_string($tok);
	    $sql = "SELECT html FROM mw_pdf_report WHERE token = '{$tok}';";
		$res = DBFunctions::execSQL($sql);
		if (DBFunctions::getNRows() <= 0) {
			return false;
		}

		// FIXME: dangerous.
		return $res[0]['html'];
	}

	function fetch_data($tok) {
	    $tok = mysql_real_escape_string($tok);
		$sql = "SELECT data FROM mw_pdf_report WHERE token = '{$tok}';";
		$res = DBFunctions::execSQL($sql);
		if (DBFunctions::getNRows() <= 0) {
			return false;
		}

		// FIXME: dangerous.
		return unserialize($res[0][0]);
	}

	function mark_submitted($tok) {
		// XXX: workaround for an odd bug where a previous token is sent.
		// Unfortunately, it does not solve the issue, which seems to be
		// due to stale client-side cache.
		$tok = mysql_real_escape_string($tok);
		global $wgImpersonating, $wgRealUser;
	    $impersonateId = $this->_uid;
	    if($wgImpersonating){
	        $impersonateId = $wgRealUser->getId();
	    }
		
		$res = DBFunctions::execSQL("SELECT special, submitted FROM mw_pdf_report WHERE token = '{$tok}' AND user_id = {$this->_uid};");
		if (DBFunctions::getNRows() <= 0) {
			return 0;
		}
		if ($res[0]['special'] == 1) {
			return 0;
		}
		if ($res[0]['submitted'] == 1) {
			// Already submitted.
			return 2;
		}

		DBFunctions::execSQL("UPDATE mw_pdf_report 
		                      SET submitted = 1,
		                          submission_user_id = $impersonateId
		                      WHERE token = '{$tok}' AND user_id = {$this->_uid};", true);
		// Refresh.
		$this->load_metadata($tok);
		// Either 0 or 1.
		return $this->_cache['submitted'];
	}
	
	/* Not strict version of the function above. Used for submitting project report PDFs. Both leader and co-leader can submit */
	function mark_submitted_ns($tok) {
		// XXX: workaround for an odd bug where a previous token is sent.
		// Unfortunately, it does not solve the issue, which seems to be
		// due to stale client-side cache.
		global $wgImpersonating, $wgRealUser;
		$tok = mysql_real_escape_string($tok);
	    $impersonateId = $this->_uid;
	    if($wgImpersonating){
	        $impersonateId = $wgRealUser->getId();
	    }
		
		$res = DBFunctions::execSQL("SELECT special, submitted FROM mw_pdf_report WHERE token = '{$tok}'");
		if (DBFunctions::getNRows() <= 0) {
			return 0;
		}
		if ($res[0]['special'] == 1) {
			return 0;
		}
		if ($res[0]['submitted'] == 1) {
			// Already submitted.
			return 2;
		}

		DBFunctions::execSQL("UPDATE mw_pdf_report 
		                      SET submitted = 1,
		                          submission_user_id = $impersonateId 
		                      WHERE token = '{$tok}'", true);
		// Refresh.
		$this->load_metadata($tok);
		// Either 0 or 1.
		return $this->_cache['submitted'];
	}

	private function load_metadata($tok = false, $strict = false) {
		if ($strict)
			$ext = "user_id = {$this->_uid} AND";
		else
			$ext = "";

		// Load data from the DB.
		if ($tok === false) {
			// FIXME: token must be enforced --- no token-use must be removed.
			$sql = "SELECT report_id, type, user_id, submitted, auto, token, timestamp, len_pdf, generation_user_id, submission_user_id, year FROM mw_pdf_report WHERE user_id = {$this->_uid} ORDER BY timestamp DESC LIMIT 1;";
		}
		else {
			$sql = "SELECT report_id, type, user_id, submitted, auto, token, timestamp, len_pdf, generation_user_id, submission_user_id, year FROM mw_pdf_report WHERE {$ext} token = '{$tok}' ORDER BY timestamp DESC LIMIT 1;";
		}
		$res = DBFunctions::execSQL($sql);
		if (count($res) <= 0) {
			// Something odd happened.  Invalidate cache.
			unset($this->_cache);
			$this->_cache = null;
			return false;
		}
		$this->_cache['report_id'] = $res[0]['report_id'];
		$this->_cache['type'] = $res[0]['type'];
		$this->_cache['user_id'] = $res[0]['user_id'];
		$this->_cache['submitted'] = $res[0]['submitted'];
		$this->_cache['auto'] = $res[0]['auto'];
		$this->_cache['token'] = $res[0]['token'];
		$this->_cache['year'] = $res[0]['year'];
		$this->_cache['timestamp'] = $res[0]['timestamp'];
		$this->_cache['len_pdf'] = $res[0]['len_pdf'];
		$this->_cache['generation_user_id'] = $res[0]['generation_user_id'];
		$this->_cache['submission_user_id'] = $res[0]['submission_user_id'];

		return true;
	}

	/// Return a field from the cache.  If the field is not available,
	/// the state is considered stale and a DB request is made.
	function metadata($field) {
		if ($this->_cache === null || !isset($this->_cache[$field])) {
			// FIXME / XXX:
			// With multiple report types, this is dangerous:
			$this->load_metadata();
		}

		if (isset($this->_cache[$field])) {
			return $this->_cache[$field];
		}
		else {
			return false;
		}
	}


	/// Trigger the downloading of a PDF.  If the user is not the owner of
	/// the report, the request is denied and the state of the object is
	/// not changed.
	/// The download is offered using #fname as filename.  If empty, it is
	/// assumed to be "<user_name>_<#tok>.pdf".
	/// If successful, the state of the object is changed to that of the
	/// requested report (#tok).
	function trigger_download($tok, $fname, $strict = true) {
		if ($this->_cache['token'] !== $tok) {
			if ($this->select_report($tok) === false && $strict === true) {
				// This user cannot download this report.
				return false;
			}
		}

		$pdf = $this->fetch_pdf($tok, $strict);
		if ($pdf === false) {
			return false;
		}
		if (empty($fname)) {
			$fname = $this->_person->getNameForPost() . "_{$tok}.pdf";
		}

		// XXX: wgOut must be *disabled*.
		ob_clean();
		header('Content-Type: application/pdf');
		header('Content-Length: ' . $this->_cache['len_pdf']);
		header('Content-Disposition: attachment; filename="'.$fname.'"');
		header('Cache-Control: private, max-age=0, must-revalidate');
		header('Pragma: public');
		ini_set('zlib.output_compression','0');
		echo $pdf;
		// This avoids mediawiki sending stuff regardless of $wgOut being disabled.
		exit;
	}


	/// Returns an array of report entries for users #uarr with as many as #lim
	/// entries per user.  By default, submitted reports are considered, which
	/// can be changed with #subm.  #uarr is either an array of numeric user IDs
	/// or an integer (for the user ID).
	static function list_reports($uarr, $subm = 1, $lim = 1, $special = 0, $type = 0) {
		if (is_array($uarr)) {
			$uarr = implode(', ', $uarr);
		}

		if (strlen($uarr) === 0)
			return array();

		$sql = "SELECT user_id, report_id, submitted, auto, token, timestamp, year
		        FROM mw_pdf_report 
		        WHERE user_id IN ({$uarr}) 
		        AND submitted = {$subm} 
		        AND type = {$type} 
		        AND special = {$special} 
		        AND report_id NOT IN (SELECT `report_id` FROM mw_pdf_index)
		        ORDER BY timestamp DESC LIMIT {$lim};";
		return DBFunctions::execSQL($sql);
	}
	
	static function list_reports_past($uarr, $year, $subm = 1, $lim = 1, $special = 0, $type = 0) {
		if (is_array($uarr)) {
			$uarr = implode(', ', $uarr);
		}

		if (strlen($uarr) === 0)
			return array();

		$sql = "SELECT user_id, report_id, submitted, auto, token, timestamp, year
		        FROM mw_pdf_report 
		        WHERE user_id IN ({$uarr}) 
		        AND submitted = {$subm} 
		        AND type = {$type} 
		        AND special = {$special} 
		        AND year = {$year} 
		        AND report_id NOT IN (SELECT `report_id` FROM mw_pdf_index)
		        ORDER BY timestamp DESC LIMIT {$lim};";
       
		return DBFunctions::execSQL($sql);
	}
	
	static function list_reports_current($uarr, $lim = 1, $special = 0, $type = 0) {
		if (is_array($uarr)) {
			$uarr = implode(', ', $uarr);
		}

		if (strlen($uarr) === 0)
			return array();

		$sql = "SELECT user_id, report_id, submitted, auto, token, timestamp, r.year
		        FROM mw_pdf_report 
		        WHERE user_id IN ({$uarr}) 
		        AND type = {$type} 
		        AND special = {$special} 
		        AND DATE(timestamp) > DATE('".REPORTING_PRODUCTION."') 
		        AND report_id NOT IN (SELECT `report_id` FROM mw_pdf_index)
		        ORDER BY timestamp DESC LIMIT {$lim};";
        $res = DBFunctions::execSQL($sql);

		return $res;
	}
	
	static function list_project_reports($proj_id, $lim = 1, $special = 0, $type = RPTP_LEADER) {
        $project = Project::newFromId($proj_id);
		if (!$proj_id || $project == null || $project->getName() == null){
			return array();
        }
            
		$sql = "SELECT r.user_id, r.report_id, r.submitted, r.auto, r.token, r.timestamp, r.year
		        FROM mw_pdf_report r, mw_pdf_index i 
		        WHERE r.report_id = i.report_id
		        AND i.project_id = {$proj_id}
		        AND r.type = {$type} 
		        AND r.special = {$special} 
		        AND DATE(r.timestamp) > DATE('2011-10-01') 
		        ORDER BY timestamp DESC LIMIT {$lim}";
		        
        $res = DBFunctions::execSQL($sql);

		return $res;
	}
	
	static function list_user_project_reports($proj_id, $user_id, $lim = 1, $special = 0, $type = RPTP_LEADER){
	    $project = Project::newFromId($proj_id);
		if (!$proj_id || $project == null || $project->getName() == null){
			return array();
        }
		$sql = "SELECT r.user_id, r.report_id, r.submitted, r.auto, r.token, r.timestamp, r.year
		        FROM mw_pdf_report r, mw_pdf_index i 
		        WHERE r.report_id = i.report_id
		        AND i.project_id = {$proj_id}
		        AND r.user_id = {$user_id}
		        AND r.type = {$type}
		        AND r.special = {$special}
		        AND DATE(r.timestamp) > DATE('2011-10-01')
		        ORDER BY timestamp DESC LIMIT {$lim}";
		        
        $res = DBFunctions::execSQL($sql);

		return $res;
	}


	/// Returns a resultset with the latest report for each user in #uarr.
	/// The flags #subm (for submitted reports) and #special (for input
	/// reports for project leaders) refine the search.
	/// Columns: user_id, report_id, token, timestamp.
	static function list_latest_reports($uarr, $subm = 1, $special = 0, $type = 0) {
		if (is_array($uarr)) {
			$uarr = implode(', ', $uarr);
		}

		if (strlen($uarr) === 0)
			return array();

		$sql = "SELECT p1.user_id, p1.report_id, p1.auto, p1.token, p1.timestamp, p1.year FROM mw_pdf_report p1 WHERE p1.user_id IN ({$uarr}) AND p1.timestamp IN (SELECT MAX(p2.timestamp) FROM mw_pdf_report p2 WHERE p1.user_id = p2.user_id AND p2.submitted = {$subm} AND p2.type = {$type} AND p2.special = {$special} AND p2.timestamp < '2011-08-01') ORDER BY p1.user_id;";

		return DBFunctions::execSQL($sql);
	}
}
