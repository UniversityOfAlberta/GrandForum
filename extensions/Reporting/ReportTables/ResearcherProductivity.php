<?php

define('RPST_HQPS', 1);
define('RPST_PUBLICATIONS', 2);
define('RPST_ARTIFACTS', 3);
define('RPST_CONTRIBUTIONS', 4);
define('RPST_RESEARCHER', 5);


class ResearcherProductivity {
	private $_person;
	private $_pid;

	// These hold the respective stats.  Note that each stat is actually a
	// group, thus associate arrays.  The only key that is common to all
	// arrays is 'total'.
	private $_artifacts;
	private $_contributions;
	private $_hqps;
	private $_publications;
	private $_researcher_prod;

	private $_report;

	function __construct($person) {
		$this->_person = $person;
		$this->_pid = $person->getId();

		$this->_artifacts = array('total' => 0);
		$this->_contributions = array('total' => 0);
		$this->_hqps = array('total' => 0);
		$this->_publications = array('total' => 0);
		$this->_researcher_prod = null;

		$this->_report = null;
	}

	function get_metric($which) {
		// Load data if needed.
		if ($this->_report === null) {
			// Try submitted report, then unsubmitted.
			$rep = ReportStorage::list_latest_reports($this->_pid, SUBM, 0, RPTP_NORMAL);
			if (! (reset($rep)))
				$rep = ReportStorage::list_latest_reports($this->_pid, NOTSUBM, 0, RPTP_NORMAL);

			$this->_report = reset($rep);
			if ($this->_report !== false) {
				$repo = new ReportStorage($this->_person);
				$this->_report = $repo->fetch_data($this->_report['token']);
			}

			// At this point, ::_report either contain a report
			// blob, or it is false.  If false, no report was
			// found for the user.
		}

		// Obtain the requested metric.
		switch ($which) {
		case RPST_HQPS:
			return $this->count_hqps();

		case RPST_PUBLICATIONS:
			return $this->count_publications();

		case RPST_ARTIFACTS:
			return $this->count_artifacts();

		case RPST_CONTRIBUTIONS:
			return $this->count_contributions();

		case RPST_RESEARCHER:
			return $this->researcher_productivity();
		}
	}

	
	private function count_hqps() {
		if ($this->_hqps['total'] > 0 || $this->_report === false)
			// Already computed.
			return $this->_hqps;

		// Traverse report and count HQPs.
		$this->_hqps = array();
		$hqps = ArrayUtil::get_array($this->_report, 'hqp');
		foreach ($hqps as $hqpid => $hqpname) {
			// The ID has an 'u' prepended.  Remove, since it was  not a good idea.  :+)
			$hqpid = substr($hqpid, 1);
			// Fake the name as if it was generated with ::getNameForPost().
			$hqpname = strtr($hqpname, array('.' => '_', ' ' => '_'));
			// Check if the HQP is mentioned in the report.
			$repkeys = array_keys($this->_report);
			foreach ($repkeys as &$repkey) {
				if (strpos($repkey, "{$hqpname}_") === false)
					continue;

				// Fake the name back to a regular name.
				$hqpfname = str_replace('_', '&nbsp;', $hqpname);

				// Found -- mark it, collect level, and interrupt loop.
				$tmparr = ArrayUtil::get_array($this->_hqps, 'hqps');
				$tmparr[$hqpid] = $hqpfname;
				$this->_hqps['hqps'] = $tmparr;

				// Level.
				$hqplvl = ArrayUtil::get_subarray($this->_report, 'ident', $hqpname);
				$lvl = ArrayUtil::get_string($hqplvl, 'level');
				if (strlen($lvl) === 0)
					// Cannot continue with this HQP.
					break;
				// Remap "none" to "other".
				if ($lvl == 'none')
					$lvl = 'other';

				$tmparr = ArrayUtil::get_array($this->_hqps, $lvl);
				$tmparr[$hqpid] = $hqpfname;
				$this->_hqps[$lvl] = $tmparr;

				// Done with this HQP --- interrupt loop.
				break;
			}
		}

		return $this->_hqps;
	}


	private function count_publications() {
		if ($this->_publications['total'] > 0 || $this->_report === false)
			// Already computed.
			return $this->_publications;

		// Traverse all reports and count publications.  Also make sure
		// the same publication is counted only once.
		$pids = array();
		$table7 = array();

		$publi = self::restructure($this->_report, '_IVq1pId');

		$prim = ArrayUtil::get_array($publi, 'prim');
		$sec = ArrayUtil::get_array($publi, 'sec');
		$ter = ArrayUtil::get_array($publi, 'ter');

		// Filter out those with N/A field set to non-null.
		$na = ArrayUtil::get_array($publi, 'na');
		$possibles = array_keys($na, 'null', true);

		foreach ($possibles as $possible) {
			// Check whether this has been counted already, skipping if so.
			if (ArrayUtil::get_field($pids, $possible) == 1)
				continue;

			// Use the same check as when building the report for publications.
			$art = Article::newFromId($possible);
			if ($art === null)
				continue;

			$ft = trim($art->getRawText());
			if (strlen($ft) == 0)
				continue;

			// Check whether this possible publication is listed for the project.
			$v1 = ArrayUtil::get_string($prim, $possible);
			$v2 = ArrayUtil::get_string($sec, $possible);
			$v3 = ArrayUtil::get_string($ter, $possible);

			// Assume the report is correct: only check whether a publication
			// is listed for any project.
			$val = ($v1 !== 'none') || ($v2 !== 'none') || ($v3 !== 'none');
			if ($val) {
				// Mark for counting total.
				$pids[$possible] = 1;

				$tmpdata = self::scrape_publication($ft);
				$tmpdata['title'] = $art->getTitle()->getText();
				switch (ArrayUtil::get_string($tmpdata, '__type__')) {
				case 'Book':
				case 'Collection':
				case 'Proceedings_Paper':
					$tmpcounter = 'a2';
					break;

				case 'Journal_Paper':
					$tmpcounter = 'a1';
					break;

				case 'MastersThesis_Paper':
				case 'TechReport':
					$tmpcounter = 'c';
					break;

				case 'Misc_Paper':
				case 'Poster':
				case 'Poster_Ref':
				default:
					$tmpcounter = 'b';
				}

				// Mark this publication in the appropriate publication class.
				$tmparr = ArrayUtil::get_array($table7, $tmpcounter);
				$tmparr[$possible] = $tmpdata;
				$table7[$tmpcounter] = $tmparr;
			}
		}

		// The number of publications for the project is, thus, the number
		// of unique IDs seen during the traversal.
		$this->_publications['total'] = count($pids);
		$this->_publications['table7'] = $table7;
		return $this->_publications;
	}
	
	
	private function count_artifacts() {
		if ($this->_artifacts['total'] > 0 || $this->_report === false)
			// Already computed.
			return $this->_artifacts;

		$artif = self::restructure($this->_report, '_IVq2aId');
		
		// Use titles for counting, but avoid trusting them blindly.
		$titles = ArrayUtil::get_array($artif, 'title');
		$descs = ArrayUtil::get_array($artif, 'desc');
		$cnt = 0;
		foreach ($titles as $title)
			$cnt += (strlen(trim($title)) > 0);
		$this->_artifacts['total'] = $cnt;

		if ($cnt > 0)
			$this->_artifacts['details'] = array_combine($titles, $descs);

		return $this->_artifacts;
	}
	
	
	private function count_contributions() {
		if ($this->_contributions['total'] > 0 || $this->_report === false)
			// Already computed.
			return $this->_contributions;

		$conts = ArrayUtil::get_array($this->_report, 'Cont');
		foreach ($conts as $cont) {
			$cash = self::extract_number(ArrayUtil::get_string($cont, 'Cash', '0'));
			$kind = self::extract_number(ArrayUtil::get_string($cont, 'Inkind', '0'));
			$tot = $cash + $kind;
			// XXX: confirm whether inkind+cash in a same contribution counts as 1 or 2.
			$cnt = ($cash > 0) + ($kind > 0);
			$name = $this->_person->getNameForForms();
			$bigarr = ArrayUtil::get_array($this->_contributions, 'details');
			$arr = ArrayUtil::get_array($bigarr, $name);
			if ($cash > 0) {
				$tarr = ArrayUtil::get_array($arr, 'cash');
				$tarr[] = $cash;
				$arr['cash'] = $tarr;
			}
			if ($kind > 0) {
				$tarr = ArrayUtil::get_array($arr, 'inkind');
				$tarr[] = $kind;
				$arr['inkind'] = $tarr;
			}
			$this->_contributions['details'][$name] = $arr;
			$this->_contributions['total'] = ArrayUtil::get_field($this->_contributions, 'total', 0) + $tot;
			$this->_contributions['count'] = ArrayUtil::get_field($this->_contributions, 'count', 0) + $cnt;
		}

		$this->_contributions['table2'] = $conts;
		return $this->_contributions;
	}


	/// Extract a number from #val, wiping every non-numeric characters from
	/// the argument, or 0 if the result is not numeric.
	static public function extract_number($val) {
		$val = trim($val);
		$tmp = "";
		$hasdot = false;
		$N = strlen($val);
		for ($i = 0; $i < $N; $i++) {
			switch ($val[$i]) {
			case '0': case '1': case '2': case '3': case '4': case '5':
			case '5': case '6': case '7': case '8': case '9':
				$tmp .= $val[$i];
				break;

			case '.':
				if (! $hasdot) {
					$tmp .= $val[$i];
					$hasdot = true;
				}
				break;

			case ',':
				if (($N - $i - 1) < 3) {
					// This must be a mistaken dot.
					$tmp .= '.';
					$hasdot = true;
				}
				break;
			}
		}
		if (is_numeric($tmp)) {
			return $tmp;
		}
		return 0;
	}


	/// Rebuilds an array with keys from #arr that match the partial pattern
	/// #kpat.  If no key matches are found, returns an empty array.
	/// The pattern is *removed* from the key, breaking it into 2 keys.  The
	/// left part of the subkey creates a subarray, in which the second
	/// subkey indexes the actual data.  If the second subkey is empty, the
	/// next numerical index is assigned.
	///
	/// Example: a key sec_IVq2aId1 using kpat=_IVq2aId becomes the array [sec][1].
	static function restructure(&$arr, $kpat) {
		$ret = array();
		if($arr != null){
		    foreach ($arr as $k => $v) {
			    if (($p = strpos($k, $kpat)) !== false) {
				    $sk1 = null;
				    $sk2 = null;
				    if ($p > 0) {
					    $sk1 = substr($k, 0, $p);
				    }
				    else {
					    // Insert an arbitrary next element, and
					    // get the key that was created.
					    $ret[] = null;
					    $sk1 = current($ret);
				    }
				    $sk2 = substr($k, $p + strlen($kpat));
				    if (empty($sk2)) {
					    $ret[$sk1][] = $v;
				    }
				    else {
					    $ret[$sk1][$sk2] = $v;
				    }
			    }
		    }
		}
		return $ret;
	}

	/// Process a wikitext chunk, scraping publication information from the
	/// first template data block found.  Anything that does not match the
	/// format is not included.  "var=val" pairs have the "var" part modified
	/// to lower-case characters (so cases like Author= and author= in the
	/// same block will clash, and the last definition persists).
	/// Returns an associative array with the results, or an empty array.
	static function scrape_publication(&$txt) {
		// Format (line-oriented):
		// {{token
		// |var = value
		// ..
		// }}
		$ret = array();
		$blob = self::block_finder($txt, '{{', "\n}}", 1);

		if (empty($blob) || empty($blob[0]))
			return $ret;
		
		// Join lines that were meant to be single.
		$len = strlen($blob[0]);
		$ind = 0;
		while (($nl = strpos($blob[0], "\n", $ind)) !== false) {
			// A newline was found.  Check if the next character is
			// not a wiki formatting --- if so, replace the \n by a
			// blank space to "join" the lines.
			$ind = $nl + 1;
			if (($ind) >= $len)
				break;

			switch ($blob[0][$ind]) {
			case '|':
			case '}':
				// Nothing to do.
				break;

			default:
				// Character at $ind is likely to be an extension
				// of the line --- remove the newline at $nl.
				$blob[0][$nl] = ' ';
			}
		}

		foreach (explode("\n", $blob[0]) as $line) {
			if (strlen($line) == 0)
				continue;

			// Parse differently depending on first character in the line.
			switch ($line[0]) {
			case '|':
				// |var = value
				$var = substr($line, 1, strcspn($line, "\n\r\t ="));
				$len = strlen($var);
				$val = substr($line, $len + strspn($line, "\n\r\t =", $len));
				// Cleanup.
				$var = strtolower(trim($var));
				$val = trim($val);
				// Expand authors if this is an author line.
				switch ($var) {
				case 'authors':
					// "Normal" authors format.
					$authors = array();
					$dewiki = explode(',', $val);
					foreach ($dewiki as $aa) {
						$i = strpos($aa, '|');
						if ($i !== false) {
							// De-wikify.
							$f = $i + strspn($aa, "\t\n\r ", $i + 1);
							$e = $f + strcspn($aa, "\n\r[]", $f + 1);
							$aa = substr($aa, $f, $e - $f + 1);
						}
						// Append author.
						$authors[] = trim($aa);
					}
					$val = $authors;
					break;

				case 'people':
					// "Old" authors format, as a wiki list.
					$authors = array();
					$dewiki = explode('*', $val);
					foreach ($dewiki as $aa) {
						if (empty($aa))
							continue;
						$i = strpos($aa, '|');
						if ($i !== false) {
							// De-wikify.
							$f = $i + strspn($aa, "\t\n\r ", $i + 1);
							$e = $f + strcspn($aa, "\n\r[]", $f + 1);
							$aa = substr($aa, $f, $e - $f + 1);
						}
						// Append author.
						$authors[] = trim($aa);
					}
					$val = $authors;
					// Remap "people" to authors.
					$var = 'authors';
				}
				// Store.
				if (!empty($var) && !empty($val)) {
					$ret[$var] = $val;
				}
				break;

			case '{':
				// {{token
				$type = trim(substr($line, 2));
				if (!empty($type)) {
					$ret['__type__'] = $type;
				}
			}
		}

		// Check for traces of paper being under 'submitted' status.
		$ret['submitted'] = (stripos($txt, 'submitted') !== false) ||
			(stripos($txt, 'submission') !== false) ||
			(stripos($txt, 'to appear') !== false);

		return $ret;
	}

	/// Returns an array of block-delimited chunks from string #data, using
	/// #blk_st as starting delimiter and #blk_en as ending separator.
	/// Return as many as #limit blocks (unlimited if #limit is zero), or an
	/// empty array.
	private static function block_finder(&$data, $blk_st, $blk_en, $limit = 0) {
		$blocks = array();
		$len = strlen($data);
		$stlen = strlen($blk_st);
		$enlen = strlen($blk_en);
		$pos = 0;

		while ($pos < $len) {
			$ini = strpos($data, $blk_st, $pos);
			if ($ini === false) {
				// Starting delimiter not found.
				break;
			}
			$next = $ini + $stlen;
			if ($next >= $len) {
				// Incomplete block.
				break;
			}
			$end = strpos($data, $blk_en, $next);
			if ($end === false) {
				// Ending delimiter not found.
				break;
			}
			// Include block.
			$blocks[] = substr($data, $ini, $end + $enlen - $ini);
			if (count($blocks) === $limit) {
				// Enough blocks.
				break;
			}

			$pos = $end + $enlen;
		}

		return $blocks;
	}
}
