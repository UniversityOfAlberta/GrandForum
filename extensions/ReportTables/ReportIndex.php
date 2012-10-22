<?php

/// Encapsulates the indexing of reports generated as PDF files.
class ReportIndex {
	private $_uid;
	private $_person;

	private $_cache;

	function __construct($person) {
		$this->_person = $person;
		$this->_uid = $person->getId();

		$this->_cache = null;
	}

	function insert_report($rid, $proj) {
		DBFunctions::execSQL("INSERT INTO mw_pdf_index (report_id, user_id, project_id, created) VALUES ({$rid}, {$this->_uid}, {$proj->getId()}, CURRENT_TIMESTAMP());", true);
		return true;
	}

	/// Returns an array of project identifiers for which the user has reports.
	function list_projects() {
		$ret = array();
		$res = DBFunctions::execSQL("SELECT DISTINCT project_id FROM mw_pdf_index WHERE user_id = {$this->_uid} ORDER BY project_id;");
		foreach ($res as &$row) {
			$ret[] = $row['project_id'];
		}
		return $ret;
	}

	/// Returns a result-set with up to #lim reports for this user, on project #proj,
	/// which is either a Project instance or a project ID (integer).
	function list_reports($proj, $lim = 1, $spec = 1) {
		if (is_object($proj)) {
			return DBFunctions::execSQL("SELECT i.report_id, n.nsName, p.token, i.created, i.last_download, i.nr_download FROM mw_pdf_index i LEFT JOIN (mw_pdf_report p, mw_an_extranamespaces n) ON (i.report_id = p.report_id AND i.project_id = n.nsId) WHERE i.user_id = {$this->_uid} AND i.project_id = {$proj->getId()} AND p.special = {$spec} AND p.submitted = 0 ORDER BY created DESC LIMIT {$lim};");
		}
		else {
			return DBFunctions::execSQL("SELECT i.report_id, n.nsName, p.token, i.created, i.last_download, i.nr_download FROM mw_pdf_index i LEFT JOIN (mw_pdf_report p, mw_an_extranamespaces n) ON (i.report_id = p.report_id AND i.project_id = n.nsId) WHERE i.user_id = {$this->_uid} AND i.project_id = {$proj} AND p.special = {$spec} AND p.submitted = 0 ORDER BY created DESC LIMIT {$lim};");
		}
	}

	function trigger_download(&$repo, $tok, $fname) {
		$rid = $repo->metadata('report_id');
		if (is_numeric($rid)) {
			DBFunctions::execSQL("UPDATE mw_pdf_index SET nr_download = nr_download + 1, last_download = CURRENT_TIMESTAMP() WHERE report_id = {$rid};", true);
		}

		return $repo->trigger_download($tok, $fname);
	}
}
