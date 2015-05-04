<?php

define('PJST_HQPS', 1);
define('PJST_PUBLICATIONS', 2);
define('PJST_ARTIFACTS', 3);
define('PJST_CONTRIBUTIONS', 4);

$moved_classes = array(
		'Autodesk' => 'industry',
		'Carr&eacute; Technologies' => 'industry',
		'Conversion Works Corporation' => 'industry',
		'Disney Research, Zurich' => 'industry',
		'HB Studios' => 'industry',
		'Honda Research' => 'industry',
		'Morgan Solar' => 'industry',
		'Purdue University' => 'university',
		'Self-employed' => 'industry',
		'Simon Fraser University' => 'university',
		'Ubisoft' => 'industry',
		'University of Alberta' => 'university',
		'University of British Columbia' => 'university',
		'University of Dalhousie' => 'university',
		'University of Saskatchewan' => 'university',
		'University of Waterloo' => 'university',
		'(unknown industry)' => 'industry',
		'(unknown university)' => 'university',
		'none' => 'none',
		'unemployed' => 'unemployed',
		'unknown' => 'unknown',
		'Visiq' => 'industry',
		'Yves Bilodeau' => 'industry');

$moved_aliases = array(
		'adrian reetz will have a meeting to transfer from the m.sc. program to the ph.d. program during the current funding period. adrian will remain at the university of saskatchewan.' => 'University of Saskatchewan',
		'andre completed his m.sc. thesis and is now working toward his ph.d. with carl and i at the uofs.' => 'University of Saskatchewan',
		'andre received his m.sc. in august 2010, and joined the ph.d. program at the university of saskatchewan in september 2010.' => 'University of Saskatchewan',
		'andrew will defend his master thesis in december 2010. after that he plans to continue working towards a doctoral degree.' => '(unknown university)',
		'autodesk (toronto), intern, effective january 4, 2011' => 'Autodesk',
		'a web-database programmer in the office of the dean of science at the university of alberta.' => 'University of Alberta',
		'completed his comprehensive exams; working on ethics protocol for the dissertation and a thesis proposal.' => '(unknown university)',
		'completing her undergraduate degree at waterloo.' => 'University of Waterloo',
		'completing his undergraduate degree at waterloo.' => 'University of Waterloo',
		'conversion works (vancouver), software engineer' => 'Conversion Works Corporation',
		'<could not removed this item from my list>' => 'none',
		'craig is completing his m.sc. thesis and will be finished before the end of the current funding period.' => 'unknown',
		'currently employed as user interface designer at large bank' => '(unknown industry)',
		'currently unemployed' => 'unemployed',
		'dalhousie' => 'University of Dalhousie',
		'dana completed her work for grand, she is currently working full-time in the information technology office at the faculty of computer science at dalhousie university' => 'University of Dalhousie',
		'derek finished his ph.d. and is now a postdoc at disney research zurich.' => 'Disney Research, Zurich',
		"don't know" => 'unknown',
		'during the reporting period, andy completed his m.sc. and is now a ph.d. under my supervision.' => '(unknown university)',
		'expects to complete msc in december 2010. will continue on to phd with me as supervisor.' => '(unknown university)',
		'faculty of computer science, dalhousie university, assistant professor effective january 1, 2011' => 'University of Dalhousie',
		'finished course work. developing dissertation proposal linked to navel' => '(unknown university)',
		'finished the dissertation; applied for a sshrc post-doc; received sshrc post-doc; deposited thesis; oral defense scheduled for january, 2011. after this her status will have changed from doctoral researcher to post-doc (feb 1)' => '(unknown university)',
		'finishing course work. developing dissertation proposal linked to navel' => '(unknown university)',
		'finishing undergraduate degree.' => 'unknown',
		'finishing undergraduate degree in computer science.' => 'unknown',
		'finishing undergraduate program; looking for graduate school.' => 'unemployed',
		'graduated with an msc. currently in a phd program at university of alberta' => 'University of Alberta',
		'hb studios (gaming company)' => 'HB Studios',
		'hired by the university of alberta' => 'University of Alberta',
		"hqp will move on to complete his master's thesis next term" => '(unknown university)',
		"in midst of master's" => '(unknown university)',
		"in midst of master's research" => '(unknown university)',
		'in toronto applying for jobs.' => 'unemployed',
		'jeffery joined the lab as a research associated, he has an mfa from mit, and thus was considered in a status similar to a post doc. he is currently working as a consultant for a game company in san francisco' => '(unknown industry)',
		'just defended this week. will be looking for a job in january.' => 'unemployed',
		'justin gowen was a b.sc. summer student during the current funding period.' => 'unknown',
		'just moved to silicon valley and has started collaborating with researcher at honda research.' => 'Honda Research',
		'mark has completed his summer undergraduate work project.' => 'unknown',
		'michael completed his m.sc. in december 2010. future plans with respect to grand are yet to be decided.' => 'unknown',
		'morgan solar' => 'Morgan Solar',
		'moving to europe where she will initially curate an event on the subject of game and art works that use open source software. she will also continue to develop games as an independent producer. i expect to present a panel on games with her at the international symposium of electronic arts in istanbul next fall.' => 'Self-employed',
		'mr. hashemian will work in my lab as a research assistant until august, when he will decide on a location for the tenure of his phd.' => '(unknown university)',
		'mr. kapiszka was a summer undergraduate student, who will graduate in 2010, and begin working at college mobile a local smartphone startup company.' => '(unknown industry)',
		'nathan was an undergrad. he is now working in industry on various contracts related to new media.' => '(unknown industry)',
		'(not known)' => 'unknown',
		'now working for ubisoft' => 'Ubisoft',
		'oliver schneider was a b.sc. summer student during the current funding period. oliver started a m.sc. degree at the university of british columbia in september 2010.' => 'University of British Columbia',
		'pierre-alexandre fournier works full time at carr' => 'Carr&eacute; Technologies',
		'playtest coordinator for ubisoft montreal' => 'Ubisoft',
		'postdoc at disney research, zurich' => 'Disney Research, Zurich',
		'private sector' => '(unknown industry)',
		'purdue (pursuing his ph.d.)' => 'Purdue University',
		'ra at sfu' => 'Simon Fraser University',
		'recently working for yves bilodeau as a robotics programmer' => 'Yves Bilodeau',
		'robert xiao was an nserc usra summer student during the current funding period.' => 'unknown',
		'scott completed his llm thesis. he is now considering his options for doctoral studies in the law and  in information studies and in the interim is assisting me with the privacy research.' => 'unknown',
		'she will be continuing her work for me on the social media aspects of the project for the near future.' => '(unknown university)',
		'sijie has defended (dec 2010) and is currently looking for a product manager or interaction designer position in shanghai or beijing.' => 'unemployed',
		'smack in the middle of his msc' => '(unknown university)',
		'sophie completed her work for grand;  she will be graduating in winter 2011' => 'unknown',
		'still an undergraduate at the university of saskatchewan.  mr. knowles was a nserc usra summer research student.' => 'University of Saskatchewan',
		'toronto, virtual reality development' => '(unknown industry)',
		'tyler completed his m.sc. under my supervision and is now a ph.d. student under my supervision.' => '(unknown university)',
		'union representative' => 'unknown',
		'visiq (imaging startup in vancouver)' => 'Visiq',
		'went back to home school.' => '(unknown university)',
		'went back to home university.' => '(unknown university)',
		'working at a company in victoria.' => '(unknown industry)',
		'working at a local iphone app development company.' => '(unknown industry)',
		'working for ubisoft.' => 'Ubisoft',
		'working on interactive installations within an new media company' => '(unknown industry)');

class ProjectProductivity {
	private $_project;
	private $_pn;
	private $_pid;

	// These hold the respective stats.  Note that each stat is actually a
	// group, thus associate arrays.  The only key that is common to all
	// arrays is 'total'.
	private $_artifacts;
	private $_contributions;
	private $_hqps;
	private $_publications;

	// Hold data from the reports.
	private $_people;
	private $_reports;

	function __construct($project) {
		$this->_project = $project;
		$this->_pn = $project->getName();
		$this->_pid = $project->getId();

		$this->_artifacts = array('total' => 0);
		$this->_contributions = array('total' => 0);
		$this->_hqps = array('total' => 0);
		$this->_publications = array('total' => 0);

		$this->_people = null;
		$this->_reports = null;
	}

	function get_metric($which) {
		// Load data if needed.
		if ($this->_people === null) {
			$this->_people = array();

			$nis = $this->_project->getAllPeople(NI);
			foreach ($nis as $ni)
				$this->_people[$ni->getId()] = $ni;

			// Load submitted and unsubmitted reports.
			$users = array_keys($this->_people);
			$subm = ReportStorage::list_latest_reports($users, SUBM, 0, RPTP_NORMAL);
			$nsub = ReportStorage::list_latest_reports($users, NOTSUBM, 0, RPTP_NORMAL);

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

		// Obtain the requested metric.
		switch ($which) {
		case PJST_HQPS:
			return $this->count_hqps();

		case PJST_PUBLICATIONS:
			return $this->count_publications();

		case PJST_ARTIFACTS:
			return $this->count_artifacts();

		case PJST_CONTRIBUTIONS:
			return $this->count_contributions();
		}
	}

	
	/// Computes the number of HQPs in a project, and also also the projects
	/// each HQP participates.
	/// The resulting array looks like:
	/// Array(
	///	'total' => int,
	///	<hqpid> => Array(
	///			'PROJ1' => {array with details},
	///			'PROJ2' => {array with details},
	///			...
	///		),
	///	...
	///	'master' => int,
	///	'phd' => int,
	///	... (other levels)
	/// )
	private function count_hqps() {
		global $moved_classes;

		if ($this->_hqps['total'] > 0)
			// Already computed.
			return $this->_hqps;

		$tmptest = "project: {$this->_pn}\n";

		// Traverse all reports and count HQPs.
		$level = array();
		$projs = array();
		$table4 = array();
		$table5 = array();
		foreach ($this->_reports as $k => $rep) {
			$hqps = ArrayUtils::get_array($rep, 'hqp');

			// Make sure the HQP is not counted twice for this project.
			foreach ($hqps as $hqpid => $hqpname) {
				// The ID has an 'u' prepended.  Remove, since it was  not a good idea.  :+)
				$hqpid = substr($hqpid, 1);
				// Fake the name as if it was generated with ::getNameForPost().
				$hqpname = strtr($hqpname, array('.' => '_', ' ' => '_'));
				// Check if the HQP is mentioned for this project in the report.
				$repkeys = array_keys($rep);
				foreach ($repkeys as &$repkey) {
					if (strpos($repkey, "{$hqpname}_{$this->_pn}") === false)
						continue;

					// Found -- mark it, collect level, and interrupt loop.
					$tmparr = ArrayUtils::get_array($projs, $hqpid);
					if (ArrayUtils::get_field($tmparr, $this->_pn) !== false) {
						// Do not count again.
						continue;
					}
					$tmparr[$this->_pn] = 1;
					$projs[$hqpid] = $tmparr;
					// Level.
					$hqplvl = ArrayUtils::get_subarray($rep, 'ident', $hqpname);
					$lvl = ArrayUtils::get_string($hqplvl, 'level');
					if (strlen($lvl) === 0)
						// Cannot continue with this HQP.
						break;

					$val = ArrayUtils::get_field($level, $lvl, 0) + 1;
					$level[$lvl] = $val;

					// Look for HQP completed/moved/where information.
					//
					// XXX: note that the processing is merged in the switch(),
					// and has a fall-through condition.  The core processing is
					// done in the last case (postdoc).
					$compl = ArrayUtils::get_string($rep, "IIq6{$hqpname}", "none");
					$moved = ArrayUtils::get_string($rep, "IIq7{$hqpname}");
					$where = ArrayUtils::get_string($rep, "IIq8{$hqpname}");
					$cit = ArrayUtils::get_string($hqplvl, 'citizenship', 'unknown');
					if ($cit === 'none')
						$cit = 'unknown';
					$gen = ArrayUtils::get_string($hqplvl, 'gender', 'unknown');
					if ($cit === 'none')
						$cit = 'unknown';

					$tmptest .= "{$hqpname} ({$lvl}, {$cit}): c={$compl}, m={$moved}, w=${where} ";
					// "oktocount" controls whether this HQP should be counted for table5.
					$oktocount = true;
					switch ($lvl) {
					default:
						$oktocount = false;
						break;

					case 'master':
					case 'phd':
						// Leave if the HQP did not complete the degree in this fiscal year.
						if ($compl !== 'yes') {
							$oktocount = false;
						}
						// else, fall through.
					case 'postdoc':
						// Check whether is doing something else, leaving the switch if so.
						if ($moved !== 'yes') {
							$oktocount = false;
						}

						if ($oktocount) {
							// Resolve "where" string.
							$where_key = self::resolve_where($where);
							// Convert that to a field.
							// XXX: odd cases are assumed 'none'.
							// TODO: ideally, this should match the master list from NCE,
							// to reduce the chances of breaking things.
							$field = ArrayUtils::get_string($moved_classes, $where_key, 'none');

							// $field should have a key inside $moved_classes array.  If it is
							// interesting, gather information for this HQP.
							switch ($field) {
							case 'none':
								// Do not count.
								break;

							case 'unknown':
								$outer = ArrayUtils::get_array($table5, $lvl);
								$inner = ArrayUtils::get_array($outer, $field);
								$inner[$hqpname] = array('field' => $field, 'where' => $where_key,
									'original' => $where, 'report' => $k);
								$outer[$field] = $inner;
								$table5[$lvl] = $outer;
								break;

							default:
								// Compute this HQP.  The arrays may not be there yet, so make sure
								// they are created if necessary.
								$outer = ArrayUtils::get_array($table5, $lvl);
								$mid = ArrayUtils::get_array($outer, $cit);
								$inner = ArrayUtils::get_array($mid, $field);
								$inner[$hqpname] = array('field' => $field, 'where' => $where_key,
									'original' => $where, 'report' => $k);
								$mid[$field] = $inner;
								$outer[$cit] = $mid;
								$table5[$lvl] = $outer;
							}

							// Grand total.
							$ttt = ArrayUtils::get_field($table5, 'total', 0);
							$ttt++;
							$table5['total'] = $ttt;
							$tmptest .= "(counted, field: '$field', key: '$where_key')";
						}

						// Compute HQP data for Table 4, only if "valid".
						switch ($compl) {
						case 'yes':
						case 'no':
						case 'none':
							$outer = ArrayUtils::get_array($table4, $lvl);
							$mid = ArrayUtils::get_array($outer, $gen);
							$inner = ArrayUtils::get_array($mid, $cit);
							$inner[$hqpname] = $compl;
							$mid[$cit] = $inner;
							$outer[$gen] = $mid;
							$table4[$lvl] = $outer;
						}
					}

					$tmptest .= "\n";

					// Done with this HQP --- interrupt loop.
					break;
				}
			}
		}

//		echo "<pre>{$tmptest}\ntable4:";
//		print_r($table4);
//		echo "\ntable5:\n";
//		print_r($table5);
//		echo "</pre>";

		// Each HQP is at least in the current project, so the total HQP is the number of keys.
		$this->_hqps = $projs + $level;
		$this->_hqps['total'] = count(array_keys($projs));
		$this->_hqps['table4'] = $table4;
		$this->_hqps['moved'] = $table5;
		return $this->_hqps;
	}


	private function count_publications() {
		if ($this->_publications['total'] > 0)
			// Already computed.
			return $this->_publications;

		// Traverse all reports and count publications.  Also make sure
		// the same publication is counted only once.
		$pids = array();
		$table7 = array();
		foreach ($this->_reports as $k => $rep) {
			$publi = self::restructure($rep, '_IVq1pId');

			$prim = ArrayUtils::get_array($publi, 'prim');
			$sec = ArrayUtils::get_array($publi, 'sec');
			$ter = ArrayUtils::get_array($publi, 'ter');

			// Filter out those with N/A field set to non-null.
			$na = ArrayUtils::get_array($publi, 'na');
			$possibles = array_keys($na, 'null', true);

			foreach ($possibles as $possible) {
				// Check whether this has been counted already, skipping if so.
				if (ArrayUtils::get_field($pids, $possible) == 1)
					continue;

				// Use the same check as when building the report for publications.
				$art = Article::newFromId($possible);
				if ($art === null)
					continue;

				$ft = trim($art->getRawText());
				if (strlen($ft) == 0)
					continue;

				// Check whether this possible publication is listed for the project.
				$v1 = ArrayUtils::get_string($prim, $possible);
				$v2 = ArrayUtils::get_string($sec, $possible);
				$v3 = ArrayUtils::get_string($ter, $possible);

				$val = ($v1 == $this->_pn) || ($v2 == $this->_pn) || ($v3 == $this->_pn);
				$this->_publications[$k] = ArrayUtils::get_field($this->_publications, $k, 0) + $val;
				if ($val) {
//					$tmparr = ArrayUtils::get_array($pids, $possible);
//
//					$tmparr['title'] = $arg->getTitle();
//
//					$ptype = self::resolve_type($tmparr['title']);
//					$tmparr['type'] = $ptype;
//					$table7[$ptype] = ArrayUtils::get_field($table7, $ptype, 0) + 1;

					// TODO: author list.
					// TODO: research groups?

					// Mark for counting total.
					$pids[$possible] = 1;

					$tmpdata = self::scrape_publication($ft);
					$tmpdata['title'] = $art->getTitle()->getText();
					switch (ArrayUtils::get_string($tmpdata, '__type__')) {
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
					$tmparr = ArrayUtils::get_array($table7, $tmpcounter);
					$tmparr[$possible] = $tmpdata;
					$table7[$tmpcounter] = $tmparr;
				}
			}
		}

//		if (!empty($table7)) {
//			echo "<pre>{$this->_pn}\n";
//			print_r($table7);
//			echo "</pre>";
//		}

		// The number of publications for the project is, thus, the number
		// of unique IDs seen during the traversal.
		$this->_publications['total'] = count($pids);
		$this->_publications['table7'] = $table7;
		return $this->_publications;
	}
	
	
	private function count_artifacts() {
		if ($this->_artifacts['total'] > 0)
			// Already computed.
			return $this->_artifacts;

		// Traverse all reports and count artifacts.
		$details = array();
		foreach ($this->_reports as $k => $rep) {
			$artif = self::restructure($rep, '_IVq2aId');
			
			// Use titles for counting, but avoid trusting them blindly.
			$titles = ArrayUtils::get_array($artif, 'title');
			$descs = ArrayUtils::get_array($artif, 'desc');
			$cnt = 0;
			foreach ($titles as $title)
				$cnt += (strlen(trim($title)) > 0);

			$this->_artifacts[$k] = $cnt;
			$this->_artifacts['total'] = ArrayUtils::get_field($this->_artifacts, 'total', 0) + $cnt;

			if ($cnt > 0) {
				$detarr = array_combine($titles, $descs);
				$details = $details + $detarr;
			}
		}

		$this->_artifacts['details'] = $details;

		return $this->_artifacts;
	}
	
	
	private function count_contributions() {
		if ($this->_contributions['total'] > 0)
			// Already computed.
			return $this->_contributions;

		// Traverse all reports, count contributions and their total.
		foreach ($this->_reports as $k => $rep) {
			$conts = ArrayUtils::get_array($rep, 'Cont');
			foreach ($conts as $cont) {
				$cash = self::extract_number(ArrayUtils::get_string($cont, 'Cash', '0'));
				$kind = self::extract_number(ArrayUtils::get_string($cont, 'Inkind', '0'));
				$tot = $cash + $kind;
				// XXX: confirm whether inkind+cash in a same contribution counts as 1 or 2.
				$cnt = ($cash > 0) + ($kind > 0);
				$name = $this->_people[$k]->getNameForForms();
				$bigarr = ArrayUtils::get_array($this->_contributions, 'details');
				$arr = ArrayUtils::get_array($bigarr, $name);
				if ($cash > 0) {
					$tarr = ArrayUtils::get_array($arr, 'cash');
					$tarr[] = $cash;
					$arr['cash'] = $tarr;
				}
				if ($kind > 0) {
					$tarr = ArrayUtils::get_array($arr, 'inkind');
					$tarr[] = $kind;
					$arr['inkind'] = $tarr;
				}
				$this->_contributions['details'][$name] = $arr;
				$this->_contributions['total'] = ArrayUtils::get_field($this->_contributions, 'total', 0) + $tot;
				$this->_contributions['count'] = ArrayUtils::get_field($this->_contributions, 'count', 0) + $cnt;
			}
		}

		return $this->_contributions;
	}


	/// Extract a number from #val, wiping every non-numeric characters from
	/// the argument, or 0 if the result is not numeric.
	static private function extract_number($val) {
		$val = trim($val);
		$tmp = "";
		for ($i = 0; $i < strlen($val); $i++) {
			switch ($val[$i]) {
			case '0': case '1': case '2': case '3': case '4': case '5':
			case '5': case '6': case '7': case '8': case '9': case '.':
				$tmp .= $val[$i];
				break;
			}
		}
		if (is_numeric($tmp)) {
			return $tmp;
		}
		return 0;
	}


	/// Returns the kind of location an HQP might have went to based on the
	/// list of aliases.
	/// Now public, since it can be useful elsewhere.
	static public function resolve_where($str) {
		global $moved_aliases;

		if (strlen($str) === 0)
			return 'unknown';

		foreach ($moved_aliases as $k => $v) {
			// Try to find k as being a substring of str.
			if (stripos($str, $k) !== false)
				return $v;
		}

		return 'unknown';
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
	private static function scrape_publication(&$txt) {
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
						// Try to instantiate this author -- if successful, make
						// it bold as requested on the NCE specs.
						$aa = trim($aa);
						try {
							$usr = Person::newFromAlias($aa);
							if (is_object($usr))
								$aa = "<b>{$aa}</b>";
						}
						catch (DomainException $de) {
							// Do nothing.
						}

						// Append author.
						$authors[] = trim($aa);
					}
					$val = implode(', ', array_values($authors));
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
					$val = implode(', ', array_values($authors));
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
