<?php

class Nominations {
	private $_nominees;

	// Hold data from the reports.
	private $_people;
	private $_reports;

	function __construct() {
		$this->_people = null;
		$this->_reports = null;
		$this->_nominees = null;
	}

	function get_metric() {
		// Load data if needed.
		if ($this->_people === null) {
			$this->_people = array();

			$nis = Person::getAllPeople();
			foreach ($nis as $ni){
				$this->_people[$ni->getId()] = $ni;
            }
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
		}

		if ($this->_nominees !== null)
			return $this->_nominees;

		// Chew the leader reports.
		$noms = array();
		foreach ($this->_reports as &$rep) {
		    if($rep == null){
		        continue;
		    }
			$keys = array_keys($rep);
			foreach ($keys as $key) {
				// Find out a nomination under keys like "PLq5aMEOW123".
				if (strpos($key, 'PLq5a') === false)
					continue;

				// Check for a positive nomination.
				if (strtolower(self::post_string($rep, $key)) !== 'yes') {
					continue;
				}

				// Chop off the "PLq5a" prefix.
				$key = substr($key, 5);
				$brk = strcspn($key, '0123456789');
				$pn = substr($key, 0, $brk);
				$cr = substr($key, $brk);

				// Load the (possibly empty) value from noms array,
				// increment, and write.
				$arr = self::post_array($noms, $cr);
				$val = self::post_field($arr, $pn, 0) + 1;
				$arr[$pn] = $val;
				$noms[$cr] = $arr;
			}
		}

		$this->_nominees = $noms;
		return $this->_nominees;
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

