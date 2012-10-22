<?php

class Themes {
	private $_themes;

	// Hold data from the reports.
	private $_people;
	private $_reports;
	private $_project;

	function __construct($project) {
		$this->_project = $project;
		$this->_people = null;
		$this->_reports = null;
		$this->_themes = null;
	}

	function get_metric() {
		if ($this->_themes !== null)
			return $this->_themes;

		// Load data.
		$this->_people = array();

		$nis = $this->_project->getLeaders();
		foreach ($nis as $ni)
			$this->_people[$ni->getId()] = $ni;

		// Load submitted and unsubmitted reports.
		$users = array_keys($this->_people);
		$subm = ReportStorage::list_latest_reports($users, SUBM, 0, RPTP_LEADER);
		$nsub = ReportStorage::list_latest_reports($users, NOTSUBM, 0, RPTP_LEADER);

		// Reindex the reports.
		// FIXME: this should be refactored for easier use.
		$this->_reports = array();
		foreach ($subm as $rep) {
			$id = $rep['user_id'];
			$repo = new ReportStorage($this->_people[$id]);
			$this->_reports[$id] = $repo->fetch_data($rep['token']);
		}
		foreach ($nsub as $rep) {
			$id = $rep['user_id'];
			if (! array_key_exists($id, $this->_reports)) {
				$repo = new ReportStorage($this->_people[$id]);
				$this->_reports[$id] = $repo->fetch_data($rep['token']);
			}
		}
		// Fill in the blanks, if any.
		foreach ($users as $id) {
			if (! array_key_exists($id, $this->_reports))
				$this->_reports[$id] = array();
		}

		// Get old themes from the DB.
		$themes = $this->_project->getThemes();
		$pn = $this->_project->getName();

		// Process reports for each user, extending the #themes array.
		$tmp = array();
		foreach ($users as $id) {
			if (! array_key_exists($id, $this->_reports)) {
				// No report for this user.
				$newthemes[$id] = array('??', '??', '??', '??', '??');
				continue;
			}

			$name = $this->_people[$id]->getNameForForms();
			$tmp[$name] = array(1 => self::post_string($this->_reports[$id], "PLq4a{$pn}1"),
					2 => self::post_string($this->_reports[$id], "PLq4a{$pn}2"),
					3 => self::post_string($this->_reports[$id], "PLq4a{$pn}3"),
					4 => self::post_string($this->_reports[$id], "PLq4a{$pn}4"),
					5 => self::post_string($this->_reports[$id], "PLq4a{$pn}5"));
		}

		$themes['data'] = $tmp;
		$this->_themes = $themes;
		return $this->_themes;
	}


	// FIXME: the following methods should be relocated to an 'ArrayUtils' module.


	// Tests whether a field #f is set in #post, and returns it if set,
	// #def otherwise.
	static function post_field(&$post, $f, $def = false) {
		if (is_array($post) && array_key_exists($f, $post)) {
			return $post[$f];
		}
		else {
			return $def;
		}
	}

	// Tests whether a field #f is set in #post, and returns it if set,
	// #def otherwise.
	static function post_array(&$post, $f, $def = array()) {
		if (is_array($post) && array_key_exists($f, $post)) {
			return (array)$post[$f];
		}
		else {
			return $def;
		}
	}

	// Tests whether a field #f1 and its subfield #f2 are set in #post,
	// returning the inner array if set, #def otherwise.
	static function post_subarray(&$post, $f1, $f2, $def = array()) {
		if (is_array($post) && array_key_exists($f1, $post) && array_key_exists($f2, $post[$f1])) {
			return (array)$post[$f1][$f2];
		}
		else {
			return $def;
		}
	}

	// Tests whether a field #f is set in #post, and returns it if set,
	// empty string otherwise.
	static function post_string(&$post, $f, $def = "") {
		if (is_array($post) && array_key_exists($f, $post) && (strlen($post[$f]) > 0)) {
			return $post[$f];
		}
		else {
			return $def;
		}
	}
}
