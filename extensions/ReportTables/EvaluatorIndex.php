<?php

// Table schema for mw_evalpdf_index:
//
// +---------------+------------------+------+-----+---------------------+-------+
// | Field         | Type             | Null | Key | Default             | Extra |
// +---------------+------------------+------+-----+---------------------+-------+
// | report_id     | int(10) unsigned | NO   | MUL | NULL                |       |
// | user_id       | int(10) unsigned | NO   |     | NULL                |       |
// | subject_id    | int(10) unsigned | NO   |     | NULL                |       |
// | type          | tinyint(1)       | NO   |     | 0                   |       |
// | nr_download   | int(10) unsigned | NO   |     | 0                   |       |
// | last_download | timestamp        | NO   |     | 0000-00-00 00:00:00 |       |
// | created       | timestamp        | NO   |     | CURRENT_TIMESTAMP   |       |
// +---------------+------------------+------+-----+---------------------+-------+


/// Encapsulates the indexing of evaluator reports.
class EvaluatorIndex {
	private $_uid;
	private $_person;


	/// Constructor: only needs the Person instance associated to the evaluator.
	function __construct($person) {
		$this->_person = $person;
		$this->_uid = $person->getId();
	}


	/// Insert a report (with identifier #rid from mw_pdf_report) in the index,
	/// associated with the evaluator of this instance, and related to the Person
	/// or Project being evaluated #subj.
	function insert_report($rid, $subj, $kind) {
		switch (strtolower($kind)) {
		case 'researcher':
			$ty = EVTP_PERSON;
			break;

		case 'project':
			$ty = EVTP_PROJECT;
			break;

		default:
			return false;
		}

		DBFunctions::execSQL("INSERT INTO mw_evalpdf_index (report_id, user_id, subject_id, type, created) VALUES ({$rid}, {$this->_uid}, {$subj}, {$ty}, CURRENT_TIMESTAMP());", true);
		return true;
	}

	/// Returns an array of project identifiers tied to the user.
	function list_subjects() {
		$ret = array();
		$res = DBFunctions::execSQL("SELECT DISTINCT subject_id, type FROM mw_evalpdf_index WHERE user_id = {$this->_uid} ORDER BY project_id;");
		foreach ($res as &$row) {
			$ret[] = array('subject_id' => $row['subject_id'], 'type' => $row['type']);
		}
		return $ret;
	}


	/// Returns a resultset for the reports available for the evaluator
	/// associated with subject #subj, up to #lim entries.
	function list_reports($subj, $lim = 1) {
		if ($subj instanceof Person) {
			$ty = EVTP_PERSON;
			$tb = 'mw_user';
			$c1 = 'user_name';
			$c2 = 'user_id';
		}
		else if ($subj instanceof Project) {
			$ty = EVTP_PROJECT;
			$tb = 'mw_an_extranamespaces';
			$c1 = 'nsName';
			$c2 = 'nsId';
		}
		else
			return array();

		return DBFunctions::execSQL("SELECT i.report_id, t.{$c1}, i.type, p.token, i.created, i.last_download, i.nr_download FROM mw_evalpdf_index i LEFT JOIN (mw_pdf_report p, {$tb} t) ON (i.report_id = p.report_id AND i.subject_id = t.{$c2}) WHERE i.user_id = {$this->_uid} AND i.subject_id = {$subj->getId()} AND i.type = {$ty} ORDER BY created DESC LIMIT {$lim};");
	}


	function metadata($rid) {
		$ret = array();
		$tq = DBFunctions::execSQL("SELECT type FROM mw_evalpdf_index WHERE report_id = {$rid};");
		if (count($tq) == 0)
			return $ret;
		switch ($tq[0][0]) {
		case EVTP_PERSON:
			$tb = 'mw_user';
			$c1 = 'user_name';
			$c2 = 'user_id';
			break;

		case EVTP_PROJECT:
			$tb = 'mw_an_extranamespaces';
			$c1 = 'nsName';
			$c2 = 'nsId';
		}

		$res = DBFunctions::execSQL("SELECT t.{$c1}, i.type, p.token, i.created, i.last_download, i.nr_download FROM mw_evalpdf_index i LEFT JOIN (mw_pdf_report p, {$tb} t) ON (i.report_id = p.report_id AND i.subject_id = t.{$c2}) WHERE i.report_id = {$rid} ORDER BY created DESC LIMIT 1;");
		if (count($res) == 0)
			return $ret;

		return $res[0];
	}
	

	function trigger_download(&$repo, $tok, $fname) {
		$rid = $repo->metadata('report_id');
		if (is_numeric($rid)) {
			DBFunctions::execSQL("UPDATE mw_evalpdf_index SET nr_download = nr_download + 1, last_download = CURRENT_TIMESTAMP() WHERE report_id = {$rid};", true);
		}

		return $repo->trigger_download($tok, $fname);
	}
}
