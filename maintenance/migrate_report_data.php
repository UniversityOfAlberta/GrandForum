<?php
require_once('commandLine.inc');
require_once('../extensions/GrandObjects/Addressing.php');
require_once('../extensions/GrandObjects/Blob.php');

// handle -> page mappings that contain data in mw_session_data.
// XXX: not used; just for reference.
//$combos = array(
//	array(0 => 'http://forum.grand-nce.ca/index.php/Special:Report'),
//	array(1 => 'http://forum.grand-nce.ca/index.php/Special:Report'),
//	array(2 => 'http://forum.grand-nce.ca/index.php/Special:Report'),
//	array(0 => 'http://forum.grand-nce.ca/index.php/Special:ProjectData'),
//	array(0 => 'http://forum.grand-nce.ca/index.php/Special:Evaluate'),
//	array(3 => 'http://forum.grand-nce.ca/index.php/Special:SupplementalReport'),
//	array(4 => 'http://forum.grand-nce.ca/index.php/Special:SupplementalReport')
//);


function helper_update_array_blob(&$blobref, $templ, $addr, $key, $data) {
	if ($blobref->load($addr)) {
		// Get an existing blob.
		echo "     > blob exists (id={$blobref->getId()}), updating.\n";
		$bdata = $blobref->getData();
	}
	else {
		// Create a new blob.
		echo "     > new blob, using template.\n";
		$bdata = ReportBlob::get_template($templ);
	}

	$bdata[$key] = $data;

	$blobref->store($bdata, $addr);
}


$all_people = Person::getAllPeople('all');
$total = count($all_people);
$i = 0;

foreach ($all_people as &$person) {
	$i++;
	$uid = $person->getId();

	echo "{$person->getName()}({$person->getId()})/{$person->getType()} ({$i}/{$total})...\n";


	/**********************************************************************
	 * Individual Report **************************************************
	 **********************************************************************/
	echo "    Individual Report: ";
	$sd = new SessionData($person->getId(), 'http://forum.grand-nce.ca/index.php/Special:Report', SD_REPORT);
	$data = $sd->fetch();
	if (! empty($data)) {
		echo "found\n";

		// Use Roles to guess whether this is an HQP report.
		$hqpreport = false;
		$roles = $person->getRoles();
		foreach ($roles as &$role)
			if ($role->getRole() === HQP)
				$hqpreport = true;

		if ($hqpreport) {
			// Person is HQP, so treat $data as an HQP report.
			$rptp = RP_HQP;
			foreach ($data as $k => $v) {
				$proj = null;
				$sec = null;
				$ssec = null;
				$item = null;

				switch (substr($k, 0, 3)) {
				case 'cit':
					$sec = HQP_DEMOGRAPHIC;
					$ssec = HQP_DEM_CITIZENSHIP;
					break;
				case 'ful':
					$sec = HQP_DEMOGRAPHIC;
					$ssec = HQP_DEM_FULLNAME;
					break;
				case 'gen':
					$sec = HQP_DEMOGRAPHIC;
					$ssec = HQP_DEM_GENDER;
					break;
				case 'lev':
					$sec = HQP_DEMOGRAPHIC;
					$ssec = HQP_DEM_LEVEL;
					break;
				case 'Iq1':
					$sec = HQP_EFFORT;
					$pnlen = strlen($k) - 3; // Base length of project name.
					if (stripos($k, '_mos') > 0) {
						$ssec = HQP_EFF_MONTHS;
						$pnlen -= 4;
					}
					else if (stripos($k,'_rem') > 0) {
						$ssec = HQP_EFF_REMARKS;
						$pnlen -= 4;
					}
					else
						$ssec = HQP_EFF_HOURS;

					$proj = Project::newFromName(substr($k, 3, $pnlen));
					break;
				case 'Iq2':
					$sec = HQP_MILESTONES;
					$ssec = HQP_MIL_CONTRIBUTIONS;
					$inv = strrev($k);
					$pninv = substr($inv, 0, strcspn($inv, '1234567890'));
					$pninv = strrev($pninv);
					// Using reversed string, starting from strlen of the project name,
					// grab digits, then reverse the resulting substring.
					$item = substr($inv, strlen($pninv), strspn($inv, '1234567890', strlen($pninv)));
					$item = strrev($item);
					$proj = Project::newFromName($pninv);
					break;
				case 'Iq3':
					$sec = HQP_PEOPLE_INTERACT;
					// No $ssec.
					$proj = Project::newFromName(substr($k, 3));
					break;
				case 'Iq4':
					$sec = HQP_PROJECT_INTERACT;
					// No $ssec.
					$proj = Project::newFromName(substr($k, 3));
					break;
				case 'Iq5':
					$sec = HQP_IMPACT;
					// No $ssec.
					$proj = Project::newFromName(substr($k, 3));
					break;
				case 'na_':
					$sec = HQP_MILESTONES;
					$ssec = HQP_MIL_NOTAPPLICABLE;
					$inv = strrev($k);
					$pninv = substr($inv, 0, strcspn($inv, '1234567890'));
					$pninv = strrev($pninv);
					// Using reversed string, starting from strlen of the project name,
					// grab digits, then reverse the resulting substring.
					$item = substr($inv, strlen($pninv), strspn($inv, '1234567890', strlen($pninv)));
					$item = strrev($item);
					$proj = Project::newFromName($pninv);
					break;
				case 'pri':
					$sec = HQP_MILESTONES;
					$ssec = HQP_MIL_PRIMCRITERIA;
					$inv = strrev($k);
					$pninv = substr($inv, 0, strcspn($inv, '1234567890'));
					$pninv = strrev($pninv);
					// Using reversed string, starting from strlen of the project name,
					// grab digits, then reverse the resulting substring.
					$item = substr($inv, strlen($pninv), strspn($inv, '1234567890', strlen($pninv)));
					$item = strrev($item);
					$proj = Project::newFromName($pninv);
					break;
				case 'sec':
					$sec = HQP_MILESTONES;
					$ssec = HQP_MIL_SECCRITERIA;
					$inv = strrev($k);
					$pninv = substr($inv, 0, strcspn($inv, '1234567890'));
					$pninv = strrev($pninv);
					// Using reversed string, starting from strlen of the project name,
					// grab digits, then reverse the resulting substring.
					$item = substr($inv, strlen($pninv), strspn($inv, '1234567890', strlen($pninv)));
					$item = strrev($item);
					$proj = Project::newFromName($pninv);
					break;

				default:
					echo "    > unexpected key '{$k}' while processing HQP report (ignoring).\n";
					continue 2;
				}

				$v = trim($v);
				if (strlen($v) == 0) {
					echo "    > ignoring key='{$k}' (empty contents) while processing HQP report.\n";
					continue;
				}
				// These are not needed.
				if ($v == 'Choose' || $v == 'None') {
					echo "    > ignoring key='{$k}' (value='{$v}') while processing HQP report.\n";
					continue;
				}

				$addr = ReportBlob::create_address($rptp, $sec, $ssec, $item);
				if ($proj !== null)
					$pid = $proj->getId();
				else
					$pid = 0;

				$blob = new ReportBlob(BLOB_TEXTANDAPPROVE, 2010, $uid, $pid);
				try {
					if ($blob->load($addr))
						// Won't happen until supervisor report is processed.  Well.  Hopefully.
						$bdata = $blob->getData();
					else
						// Frequent case.
						$bdata = ReportBlob::get_template(BLOB_TEXTANDAPPROVE);
				}
				catch (DomainException $e) {
					echo $e;
					echo "\nuid={$uid}  pid={$pid}  k={$k}  sec={$sec}  ssec={$ssec}  item={$item}\n";
					print_r($addr);
					exit;
				}

				$bdata['text'] = $v;
				$blob->store($bdata, $addr);
			}
		}
		else {
			foreach ($data as $k => $v) {
				// Normally an individual report, but some keys are from leader report.
				$rptp = RP_RESEARCHER;
				$proj = null;
				$pid = 0;
				$sec = null;
				$ssec = null;
				$item = null;
				$bdata = null;
				$skipdb = false;

				echo "    * key='$k'\n";

				// NOTE!
				//
				// This switch is a scary beast.  Many keys need special treatment,
				// so it tries to split them in more "manageable" groups.  It is still
				// terrible, but since the patterns are sometimes irregular, there isn't
				// much to do.
				//
				// Also note that since individual and leader report are stored in the
				// same data block, the elements are scathered, which only makes this
				// more difficult.  This means that leader report is handled together.
				//
				// The HQPs that might be in the report are handled indirectly in the
				// 'hqp' case.  Thus, the hqps selected are processed.

				switch (substr($k, 0, 3)) {
				case 'Con':
					// Contributions array.
					$sec = RES_CONTRIBUTIONS;
					$ssec = 1;
					foreach ($v as $cont_arr) {
						// These are [randomtoken] => array; we have an array here.
						$blob = new ReportBlob(BLOB_CONTRIBUTION, 2010, $uid);
						$addr = ReportBlob::create_address($rptp, $sec, $ssec);
						$bdata = ReportBlob::get_template(BLOB_CONTRIBUTION);
						$bdata['cash'] = ArrayUtils::get_string($cont_arr, 'Cash');
						$bdata['description'] = ArrayUtils::get_string($cont_arr, 'Desc');
						$bdata['inkind'] = ArrayUtils::get_string($cont_arr, 'Inkind');
						$bdata['internal'] = ArrayUtils::get_string($cont_arr, 'Internal');
						$bdata['source'] = ArrayUtils::get_string($cont_arr, 'Source');
						$bdata['type'] = PartV::translate(ArrayUtils::get_string($cont_arr, 'Type'));
						$bdata['primary'] = ArrayUtils::get_subfield($cont_arr, 'Proj', 1, "");
						$bdata['secondary'] = ArrayUtils::get_field($cont_arr, 'Proj', 2, "");
						$bdata['tertiary'] = ArrayUtils::get_field($cont_arr, 'Proj', 3, "");
						// Remove "none".
						if ($bdata['primary'] == 'none')
							$bdata['primary'] = '';
						if ($bdata['secondary'] == 'none')
							$bdata['secondary'] = '';
						if ($bdata['tertiary'] == 'none')
							$bdata['tertiary'] = '';

						// Store.
						$blob->store($bdata, $addr);
					}
					$skipdb = true;
					break;

				case 'Iq1':
					$sec = RES_EFFORT;
					$proj = Project::newFromName(substr($k, 3));
					break;
				case 'Iq2':
					$sec = RES_MILESTONES;
					$ssec = RES_MIL_CONTRIBUTIONS;
					$inv = strrev($k);
					$pninv = substr($inv, 0, strcspn($inv, '1234567890'));
					$pninv = strrev($pninv);
					// Using reversed string, starting from strlen of the project name,
					// grab digits, then reverse the resulting substring.
					$item = substr($inv, strlen($pninv), strspn($inv, '1234567890', strlen($pninv)));
					$item = strrev($item);
					$proj = Project::newFromName($pninv);
					break;
/*
					$sec = RES_MILESTONES;
					$tmp = substr($k, strlen('Iq2m'));
					// Milestone number is first.
					$ssec = substr($tmp, 0, strspn($tmp, '1234567890'));
					// Proj name.
					$pname = substr($tmp, strspn($tmp, '123467890'));
					$proj = Project::newFromName($pname);
					if ($proj === null)
						echo "    * k='$k'  ssec=$ssec  pname=$pname\n";
					$pid = $proj->getId();
					$blobtype = BLOB_CURRENTMILESTONE;
					$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
					$addr = ReportBlob::create_address($rptp, $sec, $ssec);
					helper_update_array_blob($blob, $blobtype, $addr, 'description', $v);
					$skipdb = true;
					break;
*/
				case 'Iq3':
					$sec = RES_PEOPLE_INTERACT;
					// No $ssec.
					$proj = Project::newFromName(substr($k, 3));
					break;
				case 'Iq4':
					$sec = RES_PROJECT_INTERACT;
					// No $ssec.
					$proj = Project::newFromName(substr($k, 3));
					break;
				case 'Iq5':
					$sec = RES_IMPACT;
					// No $ssec.
					$proj = Project::newFromName(substr($k, 3));
					break;
				case 'hqp':
					// This is an array in the form "uNNN" => "Username".
					if (empty($v)) {
						echo "    > empty hqp array, skipping.\n";
						continue 2;
					}
					$arr = array();
					foreach ($v as $u => $n) {
						$uu = substr($u, 1);
						$arr[] = $uu;

						// Approve votes.  This is old style: an approve vote affects a bunch of fields.
						// XXX: for the new stuff, votes can/should be cast on a per-field basis --- much
						// easier to handle.
						$student_p = Person::newFromId($uu);
						$hqparr = ArrayUtils::get_array($data, $student_p->getNameForPost());
						foreach ($hqparr as $hqpk => $hqpv) {
							// Check vote.
							$approve_vote = (ArrayUtils::get_string($hqpv, 'approve') == 'approved') ? 1 : 0;
							switch (substr($hqpk, 0, 3)) {
							case 'Iq1':
								$hqpproj = Project::newFromName(substr($hqpk, 3));
								$hqpblob = new ReportBlob(BLOB_TEXTANDAPPROVE, 2010, $uu, $hqpproj->getId());
								$hqpaddr = ReportBlob::create_address(RP_HQP, HQP_EFFORT, HQP_EFF_REMARKS);
								if ($hqpblob->load($hqpaddr))
									$hqpdata = $hqpblob->getData();
								else
									$hqpdata = ReportBlob::get_template(BLOB_TEXTANDAPPROVE);
								// Cast vote from supervisor ($uid).
								$hqpdata['approved'][$uid] = $approve_vote;
								$hqpblob->store($hqpdata, $hqpaddr);
								// Same, but effort-months now.
								$hqpblob = new ReportBlob(BLOB_TEXTANDAPPROVE, 2010, $uu, $hqpproj->getId());
								$hqpaddr = ReportBlob::create_address(RP_HQP, HQP_EFFORT, HQP_EFF_MONTHS);
								if ($hqpblob->load($hqpaddr))
									$hqpdata = $hqpblob->getData();
								else
									$hqpdata = ReportBlob::get_template(BLOB_TEXTANDAPPROVE);
								$hqpdata['approved'][$uid] = $approve_vote;
								$hqpblob->store($hqpdata, $hqpaddr);
								// And for hours.
								$hqpblob = new ReportBlob(BLOB_TEXTANDAPPROVE, 2010, $uu, $hqpproj->getId());
								$hqpaddr = ReportBlob::create_address(RP_HQP, HQP_EFFORT, HQP_EFF_HOURS);
								if ($hqpblob->load($hqpaddr))
									$hqpdata = $hqpblob->getData();
								else
									$hqpdata = ReportBlob::get_template(BLOB_TEXTANDAPPROVE);
								echo "    >>> included vote='$approve_vote' from $uid to $uu\n";
								$hqpdata['approved'][$uid] = $approve_vote;
								$hqpblob->store($hqpdata, $hqpaddr);
								break;

							case 'Iq2':
								$inv = strrev($hqpk);
								$pninv = substr($inv, 0, strcspn($inv, '1234567890'));
								$pninv = strrev($pninv);
								// Using reversed string, starting from strlen of the project name,
								// grab digits, then reverse the resulting substring.
								$hqpitem = substr($inv, strlen($pninv), strspn($inv, '1234567890', strlen($pninv)));
								$hqpitem = strrev($hqpitem);
								$hqpproj = Project::newFromName($pninv);
								// Now the blob stuff.
								$hqpblob = new ReportBlob(BLOB_TEXTANDAPPROVE, 2010, $uu, $hqpproj->getId());
								$hqpaddr = ReportBlob::create_address(RP_HQP, HQP_MILESTONES, HQP_MIL_CONTRIBUTIONS, $hqpitem);
								if ($hqpblob->load($hqpaddr))
									$hqpdata = $hqpblob->getData();
								else
									$hqpdata = ReportBlob::get_template(BLOB_TEXTANDAPPROVE);
								// Cast vote from supervisor ($uid).
								$hqpdata['approved'][$uid] = $approve_vote;
								$hqpblob->store($hqpdata, $hqpaddr);
								// N/A field.
								$hqpblob = new ReportBlob(BLOB_TEXTANDAPPROVE, 2010, $uu, $hqpproj->getId());
								$hqpaddr = ReportBlob::create_address(RP_HQP, HQP_MILESTONES, HQP_MIL_NOTAPPLICABLE, $hqpitem);
								if ($hqpblob->load($hqpaddr))
									$hqpdata = $hqpblob->getData();
								else
									$hqpdata = ReportBlob::get_template(BLOB_TEXTANDAPPROVE);
								$hqpdata['approved'][$uid] = $approve_vote;
								$hqpblob->store($hqpdata, $hqpaddr);
								// Primary criteria.
								$hqpblob = new ReportBlob(BLOB_TEXTANDAPPROVE, 2010, $uu, $hqpproj->getId());
								$hqpaddr = ReportBlob::create_address(RP_HQP, HQP_MILESTONES, HQP_MIL_PRIMCRITERIA, $hqpitem);
								if ($hqpblob->load($hqpaddr))
									$hqpdata = $hqpblob->getData();
								else
									$hqpdata = ReportBlob::get_template(BLOB_TEXTANDAPPROVE);
								$hqpdata['approved'][$uid] = $approve_vote;
								$hqpblob->store($hqpdata, $hqpaddr);
								// Secondary criteria.
								$hqpblob = new ReportBlob(BLOB_TEXTANDAPPROVE, 2010, $uu, $hqpproj->getId());
								$hqpaddr = ReportBlob::create_address(RP_HQP, HQP_MILESTONES, HQP_MIL_SECCRITERIA, $hqpitem);
								if ($hqpblob->load($hqpaddr))
									$hqpdata = $hqpblob->getData();
								else
									$hqpdata = ReportBlob::get_template(BLOB_TEXTANDAPPROVE);
								echo "    >>> included vote='$approve_vote' from $uid to $uu\n";
								$hqpdata['approved'][$uid] = $approve_vote;
								$hqpblob->store($hqpdata, $hqpaddr);
								break;

							case 'Iq3':
								$hqpproj = Project::newFromName(substr($hqpk, 3));
								$hqpblob = new ReportBlob(BLOB_TEXTANDAPPROVE, 2010, $uu, $hqpproj->getId());
								$hqpaddr = ReportBlob::create_address(RP_HQP, HQP_PEOPLE_INTERACT);
								if ($hqpblob->load($hqpaddr))
									$hqpdata = $hqpblob->getData();
								else
									$hqpdata = ReportBlob::get_template(BLOB_TEXTANDAPPROVE);
								// Cast vote from supervisor ($uid).
								echo "    >>> included vote='$approve_vote' from $uid to $uu\n";
								$hqpdata['approved'][$uid] = $approve_vote;
								$hqpblob->store($hqpdata, $hqpaddr);
								break;

							case 'Iq4':
								$hqpproj = Project::newFromName(substr($hqpk, 3));
								$hqpblob = new ReportBlob(BLOB_TEXTANDAPPROVE, 2010, $uu, $hqpproj->getId());
								$hqpaddr = ReportBlob::create_address(RP_HQP, HQP_PROJECT_INTERACT);
								if ($hqpblob->load($hqpaddr))
									$hqpdata = $hqpblob->getData();
								else
									$hqpdata = ReportBlob::get_template(BLOB_TEXTANDAPPROVE);
								// Cast vote from supervisor ($uid).
								echo "    >>> included vote='$approve_vote' from $uid to $uu\n";
								$hqpdata['approved'][$uid] = $approve_vote;
								$hqpblob->store($hqpdata, $hqpaddr);
								break;

							case 'Iq5':
								$hqpproj = Project::newFromName(substr($hqpk, 3));
								$hqpblob = new ReportBlob(BLOB_TEXTANDAPPROVE, 2010, $uu, $hqpproj->getId());
								$hqpaddr = ReportBlob::create_address(RP_HQP, HQP_IMPACT);
								if ($hqpblob->load($hqpaddr))
									$hqpdata = $hqpblob->getData();
								else
									$hqpdata = ReportBlob::get_template(BLOB_TEXTANDAPPROVE);
								// Cast vote from supervisor ($uid).
								echo "    >>> included vote='$approve_vote' from $uid to $uu\n";
								$hqpdata['approved'][$uid] = $approve_vote;
								$hqpblob->store($hqpdata, $hqpaddr);
								break;
							}
						}
					}
					sort($arr, SORT_NUMERIC);
					$blob = new ReportBlob(BLOB_ARRAY, 2010, $uid);
					$blob->store($arr, ReportBlob::create_address($rptp, RES_HQPSELECTION));
					$skipdb = true;
					break;

				case 'IIq':
					// Feedback on HQPs over various items.  $k[3] has the appropriate question number.
					$sec = RES_HQPREPORT;
					switch ($k[3]) {
					case '2':
						$ssec = RES_HQP_MILESTONEFEEDBACK;
						// HQP ID from nameFromPost.
						$lastdash = strrpos($k, '_');
						if ($lastdash === false) {
							echo "    > lastdash not found in k='{$k}', PANIC!\n";
							exit;
						}
						$hname = substr($k, 4, $lastdash - 4);
						$hname = str_replace('_', '.', $hname);
						$hqpobj = Person::newFromName($hname);
						$item = $hqpobj->getId();
						$pname = substr($k, $lastdash + 1, strspn($k, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $lastdash + 1));
						$proj = Project::newFromName($pname);
						break;
					case '3':
						$ssec = RES_HQP_PPLINTERACTFEEDBACK;
						// HQP ID from nameFromPost.
						$lastdash = strrpos($k, '_');
						if ($lastdash === false) {
							echo "    > lastdash not found in k='{$k}', PANIC!\n";
							exit;
						}
						$hname = substr($k, 4, $lastdash - 4);
						$hname = str_replace('_', '.', $hname);
						$hqpobj = Person::newFromName($hname);
						$item = $hqpobj->getId();
						$pname = substr($k, $lastdash + 1, strspn($k, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $lastdash + 1));
						$proj = Project::newFromName($pname);
						break;
					case '4':
						$ssec = RES_HQP_PROJINTERACTFEEDBACK;
						// HQP ID from nameFromPost.
						$lastdash = strrpos($k, '_');
						if ($lastdash === false) {
							echo "    > lastdash not found in k='{$k}', PANIC!\n";
							exit;
						}
						$hname = substr($k, 4, $lastdash - 4);
						$hname = str_replace('_', '.', $hname);
						$hqpobj = Person::newFromName($hname);
						$item = $hqpobj->getId();
						$pname = substr($k, $lastdash + 1, strspn($k, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $lastdash + 1));
						$proj = Project::newFromName($pname);
						break;
					case '5':
						$ssec = RES_HQP_OTHERACTIVITIES;
						// HQP ID from nameFromPost.
						$lastdash = strrpos($k, '_');
						if ($lastdash === false) {
							echo "    > lastdash not found in k='{$k}', PANIC!\n";
							exit;
						}
						$hname = substr($k, 4, $lastdash - 4);
						$hname = str_replace('_', '.', $hname);
						$hqpobj = Person::newFromName($hname);
						$item = $hqpobj->getId();
						$pname = substr($k, $lastdash + 1, strspn($k, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', $lastdash + 1));
						$proj = Project::newFromName($pname);
						break;
					case '6':
						$ssec = RES_HQP_THESISCOMPLETED;
						break;
					case '7':
						$ssec = RES_HQP_GRADUATED;
						break;
					case '8':
						$ssec = RES_HQP_MOVEDTO;
						// Use Productivity module to "clean" this input, since most are textual descriptions.
						$v = ProjectProductivity::resolve_where($v);
						break;
					}
					// Done, and use the simple method at the end of the larger switch.
					break;

				case 'des':
					if (stripos($k, 'desc_IVq2aId') !== false) {
						// Artifact
						$blobtype = BLOB_ARTIFACT;
						$sec = RES_ARTIFACTS;
						// ID from key, starting at len=12.
						$ssec = substr($k, strlen('desc_IVq2aId'));
						// No project associated.
					}
					else if (stripos($k, 'descPLq1a') !== false) {
						// descPLq1a{N}{PROJNAME}
						// Current milestone.  The key is DIFFERENT from new milestone keys.
						$rptp = RP_LEADER;
						$sec = LDR_MILESTONESTATUS;
						$tmp = substr($k, strlen('descPLq1a'));
						// Proj name (last in the string).
						$pname = substr($tmp, strcspn($tmp, '1234567890'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
						// Milestone number (first in the string).
						$ssec = substr($tmp, 0, strspn($tmp, '1234567890'));
						$blobtype = BLOB_MILESTONESTATUS;
					}
					else if (stripos($k, 'desc_PLq1b') !== false) {
						// New milestone.
						$rptp = RP_LEADER;
						$sec = LDR_NEWMILESTONE;
						$tmp = substr($k, strlen('desc_PLq1b'));
						// Proj name.
						$pname = substr($tmp, 0, strcspn($tmp, '123467890'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
						// Milestone number.
						$ssec = substr($tmp, strcspn($tmp, '1234567890'));
						$blobtype = BLOB_NEWMILESTONE;
					}
					else {
						// Unexpected.
						echo "    > unexpected key '{$k}' while processing researcher report.\n";
						exit;
					}
					// Luckily these share the same array key to update.
					$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
					$addr = ReportBlob::create_address($rptp, $sec, $ssec);
					helper_update_array_blob($blob, $blobtype, $addr, 'description', $v);
					// Don't go over simplified way after switch.
					$skipdb = true;
					break;

				case 'na_':
					// Similar to des case above, but different blobs.
					if (stripos($k, 'na_IVq1pId') !== false) {
						// Publication
						$blobtype = BLOB_PUBLICATION;
						$sec = RES_PUBLICATIONS;
						// ID from key.
						$ssec = substr($k, strlen('na_IVq1pId'));
						// No project associated.
					}
					else if (stripos($k, 'na_Iq2m') !== false) {
						// Milestone N/A field.
						// eg: na_Iq2m1MOTION
						$sec = RES_MILESTONES;
						$ssec = RES_MIL_NOTAPPLICABLE;
						$tmp = substr($k, strlen('na_Iq2m'));
						// Milestone number is first.
						$item = substr($tmp, 0, strspn($tmp, '1234567890'));
						// Proj name.
						$pname = substr($tmp, strspn($tmp, '1234567890'));
						$proj = Project::newFromName($pname);
						if (! is_object($proj))
							echo "    * k=$k  tmp=$tmp  ssec=$ssec  pname=$pname\n";
						$pid = $proj->getId();
//						$blobtype = BLOB_CURRENTMILESTONE;
						// Get out of this switch; use simplified method after it.
						break;
					}
					else {
						// Unexpected.
						echo "    > unexpected key '{$k}' while processing researcher report.\n";
						exit;
					}
					// Luckily these share the same array key to update.
					$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
					$addr = ReportBlob::create_address($rptp, $sec, $ssec);
					helper_update_array_blob($blob, $blobtype, $addr, 'not-applicable', $v);
					$skipdb = true;
					break;

				case 'pri':
					// Another case of clashing keys.
					if (stripos($k, 'prim_IVq1pId') !== false) {
						// Publication.
						$blobtype = BLOB_PUBLICATION;
						$sec = RES_PUBLICATIONS;
						// Publication ID.
						$ssec = substr($k, 12);
						// No projects.
					}
					else if (stripos($k, 'prim_IVq2aId') !== false) {
						// Artifact
						$blobtype = BLOB_ARTIFACT;
						$sec = RES_ARTIFACTS;
						// ID from key, starting at len=12.
						$ssec = substr($k, 12);
						// No project associated.
					}
					else if (stripos($k, 'prim_criteria_Iq2m') !== false) {
						// Milestone criteria.
						$sec = RES_MILESTONES;
						$ssec = RES_MIL_PRIMCRITERIA;
						// This method looks odd at first, but project name and milestone ID are at the
						// end of the string, side-by-side.
						$inv = strrev($k);
						$pninv = substr($inv, 0, strcspn($inv, '1234567890'));
						$pninv = strrev($pninv);
						// Using reversed string, starting from strlen of the project name,
						// grab digits, then reverse the resulting substring.
						$item = substr($inv, strlen($pninv), strspn($inv, '1234567890', strlen($pninv)));
						$item = strrev($item);
						$proj = Project::newFromName($pninv);
						// Get out of this switch; use simplified method after it.
						break;
					}
					else {
						// Unexpected.
						echo "    > unexpected key '{$k}' while processing researcher report.\n";
						exit;
					}
					// Luckily some of these share the same array key to update.
					$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
					$addr = ReportBlob::create_address($rptp, $sec, $ssec);
					helper_update_array_blob($blob, $blobtype, $addr, 'primary', $v);
					$skipdb = true;
					break;

				case 'sec':
					// Another case of clashing keys.
					if (stripos($k, 'sec_IVq1pId') !== false) {
						// Publication.
						$blobtype = BLOB_PUBLICATION;
						$sec = RES_PUBLICATIONS;
						// Publication ID.
						$ssec = substr($k, 11);
						// No projects.
					}
					else if (stripos($k, 'sec_IVq2aId') !== false) {
						// Artifact
						$blobtype = BLOB_ARTIFACT;
						$sec = RES_ARTIFACTS;
						// ID from key, starting at len=11.
						$ssec = substr($k, 11);
						// No project associated.
					}
					else if (stripos($k, 'sec_criteria_Iq2m') !== false) {
						// Milestone criteria.
						$sec = RES_MILESTONES;
						$ssec = RES_MIL_PRIMCRITERIA;
						$inv = strrev($k);
						$pninv = substr($inv, 0, strcspn($inv, '1234567890'));
						$pninv = strrev($pninv);
						// Using reversed string, starting from strlen of the project name,
						// grab digits, then reverse the resulting substring.
						$item = substr($inv, strlen($pninv), strspn($inv, '1234567890', strlen($pninv)));
						$item = strrev($item);
						$proj = Project::newFromName($pninv);
						// Get out of this switch; use simplified method after it.
						break;
					}
					else {
						// Unexpected.
						echo "    > unexpected key '{$k}' while processing researcher report.\n";
						exit;
					}
					// Luckily some of these share the same array key to update.
					$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
					$addr = ReportBlob::create_address($rptp, $sec, $ssec);
					helper_update_array_blob($blob, $blobtype, $addr, 'secondary', $v);
					$skipdb = true;
					break;

				case 'ter':
					// Another case of clashing keys.
					if (stripos($k, 'ter_IVq1pId') !== false) {
						// Publication.
						$blobtype = BLOB_PUBLICATION;
						$sec = RES_PUBLICATIONS;
						// Publication ID.
						$ssec = substr($k, 11);
						// No projects.
					}
					else if (stripos($k, 'ter_IVq2aId') !== false) {
						// Artifact
						$blobtype = BLOB_ARTIFACT;
						$sec = RES_ARTIFACTS;
						// ID from key, starting at len=11.
						$ssec = substr($k, 11);
						// No project associated.
					}
					else {
						// Unexpected.
						echo "    > unexpected key '{$k}' while processing researcher report.\n";
						exit;
					}
					// Luckily some of these share the same array key to update.
					$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
					$addr = ReportBlob::create_address($rptp, $sec, $ssec);
					helper_update_array_blob($blob, $blobtype, $addr, 'tertiary', $v);
					$skipdb = true;
					break;

				case 'tit':
					if (stripos($k, 'title_IVq2aId') !== false) {
						// Artifact
						$blobtype = BLOB_ARTIFACT;
						$sec = RES_ARTIFACTS;
						// ID from key.
						$ssec = substr($k, strlen('title_IVq2aId'));
						// No project associated.
					}
					else if (stripos($k, 'title_PLq1b') !== false) {
						// New milestone.
						$rptp = RP_LEADER;
						$sec = LDR_NEWMILESTONE;
						$tmp = substr($k, strlen('title_PLq1b'));
						// Proj name.
						$pname = substr($tmp, 0, strcspn($tmp, '1234567890'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
						// Milestone number.
						$ssec = substr($tmp, strcspn($tmp, '1234567890'));
						$blobtype = BLOB_NEWMILESTONE;
					}
					else {
						// Unexpected.
						echo "    > unexpected key '{$k}' while processing researcher report.\n";
						exit;
					}
					// Luckily these share the same array key to update.
					$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
					$addr = ReportBlob::create_address($rptp, $sec, $ssec);
					helper_update_array_blob($blob, $blobtype, $addr, 'title', $v);
					// Don't go over simplified way after switch.
					$skipdb = true;
					break;

				case 'aba':
					// Abandoned milestone.  Seen only in leader report.
					$rptp = RP_LEADER;
					$sec = LDR_MILESTONESTATUS;
					$tmp = substr($k, strlen('abandonedPLq1a'));
					// Proj name (last in the string  :-( ).
					$pname = substr($tmp, strcspn($tmp, '1234567890'));
					$proj = Project::newFromName($pname);
					$pid = $proj->getId();
					// Milestone number.
					$ssec = substr($tmp, 0, strspn($tmp, '1234567890'));
					$blobtype = BLOB_MILESTONESTATUS;
					$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
					$addr = ReportBlob::create_address($rptp, $sec, $ssec);
					helper_update_array_blob($blob, $blobtype, $addr, 'abandoned', $v);
					$skipdb = true;
					break;

				case 'ass':
					// Milestone assessment, from leader report.
					// 2010.uid.pid.RP_LEADER.LDR_NEWMILESTONE.{milestone_number}
					// But first decide if it's a new or status milestone...
					if (strpos($k, 'assPLq1a') !== false) {
						// Status.  NOTE: key is different from new milestone.
						$rptp = RP_LEADER;
						$sec = LDR_MILESTONESTATUS;
						$tmp = substr($k, strlen('assPLq1a'));
						// Proj name (last in the string  :-( ).
						$pname = substr($tmp, strcspn($tmp, '1234567890'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
						// Milestone number.
						$ssec = substr($tmp, 0, strspn($tmp, '1234567890'));
						$blobtype = BLOB_MILESTONESTATUS;
					}
					else if (strpos($k, 'ass_PLq1b') !== false) {
						$rptp = RP_LEADER;
						$sec = LDR_NEWMILESTONE;
						$tmp = substr($k, strlen('ass_PLq1b'));
						// Proj name.
						$pname = substr($tmp, 0, strcspn($tmp, '1234567890'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
						// Milestone number.
						$ssec = substr($tmp, strcspn($tmp, '1234567890'));
						$blobtype = BLOB_CURRENTMILESTONE;
					}
					else {
						// Unexpected.
						echo "    > unexpected key '{$k}' while processing researcher report.\n";
						exit;
					}

					$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
					$addr = ReportBlob::create_address($rptp, $sec, $ssec);
					helper_update_array_blob($blob, $blobtype, $addr, 'assessment', $v);
					$skipdb = true;
					break;

				case 'com':
					// Completed milestone.  Seen only in leader report.
					$rptp = RP_LEADER;
					$sec = LDR_MILESTONESTATUS;
					$tmp = substr($k, strlen('completedPLq1a'));
					// Proj name (last in the string  :-( ).
					$pname = substr($tmp, strcspn($tmp, '1234567890'));
					$proj = Project::newFromName($pname);
					$pid = $proj->getId();
					// Milestone number.
					$ssec = substr($tmp, 0, strspn($tmp, '1234567890'));
					$blobtype = BLOB_MILESTONESTATUS;
					$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
					$addr = ReportBlob::create_address($rptp, $sec, $ssec);
					helper_update_array_blob($blob, $blobtype, $addr, 'completed', $v);
					$skipdb = true;
					break;

				case 'mon':
					// Milestone month, from leader report.
					// 2010.uid.pid.RP_LEADER.LDR_NEWMILESTONE.{milestone_number}
					// There is a key clash.
					if (strpos($k, 'monthPLq1a') !== false) {
						// Remember, key is composed in a different order here.
						$rptp = RP_LEADER;
						$sec = LDR_MILESTONESTATUS;
						$tmp = substr($k, strlen('monthPLq1a'));
						// Proj name.
						$pname = substr($tmp, strcspn($tmp, '1234567890'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
						// Milestone number.
						$ssec = substr($tmp, 0, strcspn($tmp, '1234567890'));
						$blobtype = BLOB_MILESTONESTATUS;
					}
					else if (strpos($k, 'monthPLq1b') !== false) {
						$rptp = RP_LEADER;
						$sec = LDR_NEWMILESTONE;
						$tmp = substr($k, strlen('monthPLq1b'));
						// Proj name.
						$pname = substr($tmp, 0, strcspn($tmp, '1234567890'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
						// Milestone number.
						$ssec = substr($tmp, strcspn($tmp, '1234567890'));
						$blobtype = BLOB_CURRENTMILESTONE;
					}
					else {
						// Unexpected.
						echo "    > unexpected key '{$k}' while processing researcher report.\n";
						exit;
					}

					$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
					$addr = ReportBlob::create_address($rptp, $sec, $ssec);
					helper_update_array_blob($blob, $blobtype, $addr, 'month', $v);
					$skipdb = true;
					break;

				case 'sta':
					// Milestone status.  Seen only in leader report.
					$rptp = RP_LEADER;
					$sec = LDR_MILESTONESTATUS;
					$tmp = substr($k, strlen('statusPLq1a'));
					// Proj name (last in the string  :-( ).
					$pname = substr($tmp, strcspn($tmp, '1234567890'));
					$proj = Project::newFromName($pname);
					$pid = $proj->getId();
					// Milestone number.
					$ssec = substr($tmp, 0, strspn($tmp, '1234567890'));
					$blobtype = BLOB_MILESTONESTATUS;
					$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
					$addr = ReportBlob::create_address($rptp, $sec, $ssec);
					helper_update_array_blob($blob, $blobtype, $addr, 'status', $v);
					$skipdb = true;
					break;

				case 'yea':
					// Milestone year, from leader report.
					// 2010.uid.pid.RP_LEADER.LDR_NEWMILESTONE.{milestone_number}
					if (strpos($k, 'yearPLq1a') !== false) {
						// Different order again.
						$rptp = RP_LEADER;
						$sec = LDR_MILESTONESTATUS;
						$tmp = substr($k, strlen('yearPLq1a'));
						// Proj name.
						$pname = substr($tmp, 0, strcspn($tmp, '1234567890'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
						// Milestone number.
						$ssec = substr($tmp, strcspn($tmp, '1234567890'));
						$blobtype = BLOB_MILESTONESTATUS;
					}
					else if (strpos($k, 'yearPLq1b') !== false) {
						$rptp = RP_LEADER;
						$sec = LDR_NEWMILESTONE;
						$tmp = substr($k, strlen('yearPLq1b'));
						// Proj name.
						$pname = substr($tmp, 0, strcspn($tmp, '1234567890'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
						// Milestone number.
						$ssec = substr($tmp, strcspn($tmp, '1234567890'));
						$blobtype = BLOB_CURRENTMILESTONE;
					}
					else {
						// Unexpected.
						echo "    > unexpected key '{$k}' while processing researcher report.\n";
						exit;
					}
					$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
					$addr = ReportBlob::create_address($rptp, $sec, $ssec);
					helper_update_array_blob($blob, $blobtype, $addr, 'year', $v);
					$skipdb = true;
					break;

				case 'PLq':
					// Handle most leader cases.
					// Ugly chained-ifs, but the key patterns are chaotic.
					if (stripos($k, 'PLqpre') !== false) {
						// Project summary.
						$sec = LDR_SUMMARY;
						// Proj name.
						$pname = substr($k, strlen('PLqpre'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
					}
					else if (stripos($k, 'PLq2') !== false) {
						// Multidisciplinary.
						$sec = LDR_MULTIDISCIPLINARY;
						switch ($k[4]) {
						case 'a': $ssec = LDR_MLT_BALANCE; break;
						case 'b': $ssec = LDR_MLT_CIHR; break;
						case 'c': $ssec = LDR_MLT_ARTDESIGN; break;
						}
						$pname = substr($k, strlen('PLq2X'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
					}
					else if (stripos($k, 'PLq3') !== false) {
						$sec = LDR_CROSSPOLLINATION;
						$pname = substr($k, strlen('PLq3'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
					}
					else if (stripos($k, 'PLq4a') !== false) {
						// Cross-pollination.
						// Theme index will be the last component of the address ($ssec).
						// eg: PLq4aAESTHVIS1
						$sec = LDR_CROSSPOLLINATION;
						$pname = substr($k, strlen('PLq4a'));
						$ssec = substr($k, strlen('PLq4a') + strlen($pname));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
					}
					else if (stripos($k, 'PLq4b') !== false) {
						// Cross-pollination remarks.
						$sec = LDR_CROSSPOLLINATION;
						$pname = substr($k, strlen('PLq4b'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
					}
					// PLq5a handled implicitly by reasonPLq5a; see 'rea' (below).
					else if (stripos($k, 'PLq5b') !== false) {
						$sec = LDR_PROJECTCHAMPIONS;
						$ssec = LDR_CHA_CONTRIBUTORS;
						$pname = substr($k, strlen('PLq5b'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
					}
					else if (stripos($k, 'PLq6') !== false) {
						$sec = LDR_PROJECTCHAMPIONS;
						$ssec = LDR_CHA_INTERACTIONS;
						$pname = substr($k, strlen('PLq6'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
					}
					else if (stripos($k, 'PLq7b') !== false) {
						// eg: PLq7bAESTHVIS8
						$sec = LDR_BUDGET;
						$ssec = LDR_BUD_RESEARCHERREMARKS;
						$pname = substr($k, strlen('PLq7b'), strcspn($k, '1234567890', strlen('PLq7b')));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
						$item = substr($k, strlen('PLq7b') + strlen($pname));
					}
					else if (stripos($k, 'PLq7c') !== false) {
						$sec = LDR_BUDGET;
						$ssec = LDR_BUD_COMMENTS;
						$pname = substr($k, strlen('PLq7c'));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
					}
					else if (stripos($k, 'PLq7') !== false) {
						$sec = LDR_BUDGET;
						$ssec = LDR_BUD_ADJUSTMENT;
						$pname = substr($k, strlen('PLq7'), strcspn($k, '1234567890', strlen('PLq7')));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
						$item = substr($k, strlen('PLq7') + strlen($pname));
					}

					$rptp = RP_LEADER;
					$blobtype = BLOB_TEXT;
//					$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
//					$addr = ReportBlob::create_address($rptp, $sec, $ssec, $item);
//					helper_update_array_blob($blob, $blobtype, $addr, 'year', $v);
//					$skipdb = true;
					break;

				case 'rea':
					// Champion nomination.
					$v = trim($v);
					if (! empty($v) && $v != 'null') {
						$sec = LDR_PROJECTCHAMPIONS;
						$ssec = LDR_CHA_NOMINATIONREASON;
						$pname = substr($k, strlen('reasonPLq5a'), strcspn($k, '1234567890', strlen('reasonPLq5a')));
						$proj = Project::newFromName($pname);
						$pid = $proj->getId();
						// nominated user id is the last component.
						$item = substr($k, strlen('reasonPLq5a') + strlen($pname));

						$rptp = RP_LEADER;
						$blobtype = BLOB_TEXT;
//						$blob = new ReportBlob($blobtype, 2010, $uid, $pid);
//						$addr = ReportBlob::create_address($rptp, $sec, $ssec, $item);
//						helper_update_array_blob($blob, $blobtype, $addr, 'year', $v);
//						$skipdb = true;
					}
					break;

				// Some POST management keys.
				case '0':
				case '1':
				case '2':
				case '3':
				case '4':
				case '5':
				case '6':
				case '7':
				case '8':
				case '9':
				case 'fin':
				case 'mar':
				case 'pdf':
					echo "    > unimportant key '{$k}', ignoring.\n";
					continue 2;

				default:
					echo "    > unexpected key '{$k}' while processing individual/leader report (ignoring).\n";
//					exit;
					continue 2;
				}

				if ($skipdb)
					continue;

				// Only simple cases here.
				$addr = ReportBlob::create_address($rptp, $sec, $ssec, $item);
				if ($proj !== null)
					$pid = $proj->getId();
				else
					$pid = 0;

				echo "      > using general writer on addr=" . implode(', ', $addr) . "\n";
				$blob = new ReportBlob(BLOB_TEXT, 2010, $uid, $pid);
				$blob->store($v, $addr);
			}
		}
	}
	else {
		echo "NONE\n";
	}


	/**********************************************************************
	 * Evaluator Report ***************************************************
	 **********************************************************************/
	echo "    Evaluator Report: ";
	$sd = new SessionData($person->getId(), 'http://forum.grand-nce.ca/index.php/Special:Evaluate', SD_REPORT);
	$data = $sd->fetch();
	if ($data !== false && count($data) > 0) {
		echo "found\n";

		foreach ($data as $k => $v) {
			// Decide whether this is a researcher or project evaluation.
			$rptp = null;
			if (stripos($k, 'person') !== false) {
				// Researcher.
				$rptp = RP_EVAL_RESEARCHER;
			}
			else if (stripos($k, 'project') !== false) {
				// Project.
				$rptp = RP_EVAL_PROJECT;
			}
			else {
				// Unexpected.
				echo "    > unexpected key '{$k}' while processing evaluator report (ignoring).\n";
				continue;
			}

			// Section.
			$parts = explode('_', $k);
			if (count($parts) != 3) {
				echo "    > malformed key '{$k}' while processing evaluator report.\n";
				exit;
			}
			switch ($parts[1]) {
			case 'I':	$sec = EVL_EXCELLENCE; break;
			case 'II':	$sec = EVL_HQPDEVELOPMENT; break;
			case 'III':	$sec = EVL_NETWORKING; break;
			case 'IV':	$sec = EVL_KNOWLEDGE; break;
			case 'V':	$sec = EVL_MANAGEMENT; break;
			case 'VI':	$sec = EVL_OVERALLSCORE; break;
			case 'VII':	$sec = EVL_OTHERCOMMENTS; break;
			case 'VIII':	$sec = EVL_REPORTQUALITY; break;
			case 'IX':	$sec = EVL_CONFIDENCE; break;
			default:
				echo "    > unexpected section '{$parts[1]}' while processing evaluator report.\n";
				exit;
			}

			// Extract numeric ID.
			$id = substr($parts[2], strcspn($parts[2], '1234567890'));
			if (! is_numeric($id)) {
				echo "    > malformed user/project ID '{$id}' while processing evaluator report.\n";
				exit;
			}

			// Check for non-empty data.
			$v = trim($v);
			if (strlen($v) == 0) {
				echo "    > ignoring key='{$k}' (empty contents) while processing evaluator report.\n";
				continue;
			}


			// Address: report type, report section, user/project ID.
			$addr = ReportBlob::create_address($rptp, $sec, $id);
			$blob = new ReportBlob(BLOB_OPTIONANDTEXT, 2010, $uid);
			if ($blob->load($addr)) {
				// Get an existing blob.
				$bdata = $blob->getData();
			}
			else {
				// Create a new blob.
				$bdata = ReportBlob::get_template(BLOB_OPTIONANDTEXT);
			}
			// Update the proper component.
			if ($k[0] == 'r')
				$bdata['text'] = $v;
			else
				$bdata['selected'] = $v;

			// Store in the database.
			$blob->store($bdata, $addr);
		}
	}
	else {
		echo "NONE\n";
	}

	continue;


	/**********************************************************************
	 * Supplemental Report ************************************************
	 **********************************************************************/
	echo "    Supplemental Report: ";
	$sd = new SessionData($person->getId(), 'http://forum.grand-nce.ca/index.php/Special:SupplementalReport', SD_SUPPL_REPORT);
	$data = $sd->fetch();
	if (count($data) > 0) {
		echo "found\n";

		$fields = array(
			'fix_hqps_ugrad' => array(SUP_HQPS, SUP_HQP_UGRAD_COUNT),
			'det_hqps_ugrad' => array(SUP_HQPS, SUP_HQP_UGRAD_DETAILS),
			'fix_hqps_master' => array(SUP_HQPS, SUP_HQP_MSC_COUNT),
			'det_hqps_master' => array(SUP_HQPS, SUP_HQP_MSC_DETAILS),
			'fix_hqps_phd' => array(SUP_HQPS, SUP_HQP_PHD_COUNT),
			'det_hqps_phd' => array(SUP_HQPS, SUP_HQP_PHD_DETAILS),
			'fix_hqps_postdoc' => array(SUP_HQPS, SUP_HQP_POSTDOC_COUNT),
			'det_hqps_postdoc' => array(SUP_HQPS, SUP_HQP_POSTDOC_DETAILS),
			'fix_hqps_tech' => array(SUP_HQPS, SUP_HQP_TECH_COUNT),
			'det_hqps_tech' => array(SUP_HQPS, SUP_HQP_TECH_DETAILS),
			'fix_hqps_other' => array(SUP_HQPS, SUP_HQP_OTHER_COUNT),
			'det_hqps_other' => array(SUP_HQPS, SUP_HQP_OTHER_DETAILS),
			'fix_publications' => array(SUP_PUBLICATIONS, SUP_PUB_COUNT),
			'det_publications' => array(SUP_PUBLICATIONS, SUP_PUB_DETAILS),
			'fix_artifacts' => array(SUP_ARTIFACTS, SUP_ART_COUNT),
			'det_artifacts' => array(SUP_ARTIFACTS, SUP_ART_DETAILS),
			'fix_contr_count' => array(SUP_CONTRIBUTIONS, SUP_CONT_COUNT),
			'fix_contr_volume' => array(SUP_CONTRIBUTIONS, SUP_CONT_VOLUME),
			'det_contr' => array(SUP_CONTRIBUTIONS, SUP_CONT_DETAILS),
			'det_general' => array(SUP_OTHER, SUP_OTH_DETAILS),
		);

		foreach ($fields as $f => $varr) {
			$val = ArrayUtils::get_field($data, $f);
			if ($val === false)
				continue;
			$val = trim($val);
			if (strlen($val) == 0)
				continue;

			$blob = new ReportBlob(BLOB_TEXT, 2010, $uid);
			$blob->store($val, ReportBlob::create_address(RP_SUPPLEMENTAL, $varr[0], $varr[1]));
		}
	}
	else {
		echo "NONE\n";
	}

	/**********************************************************************
	 * Supplemental Budget ************************************************
	 **********************************************************************/
	echo "    Supplemental Budget: ";
	$sd = new SessionData($person->getId(), 'http://forum.grand-nce.ca/index.php/Special:SupplementalReport', SD_SUPPL_BUDGET);
	$data = $sd->fetch(false);
	if (strlen($data) > 0) {
		echo "found\n";
		$blob = new ReportBlob(BLOB_EXCEL, 2010, $uid);
		$blob->store($data, ReportBlob::create_address(RP_SUPPLEMENTAL, SUP_BUDGET));
	}
	else {
		echo "NONE\n";
	}


	/**********************************************************************
	 * PDFs ***************************************************************
	 **********************************************************************/
	echo "    PDF reports:\n";
	$reps = reset(ReportStorage::list_latest_reports($uid, SUBM, 0, RPTP_NORMAL));
	$data = false;
	if ($reps !== false) {
		echo "    > individual report, submitted\n";
		$sto = new ReportStorage($uid);
		$data = $sto->fetch_pdf($rep['token']);
	}
	else {
		$reps = reset(ReportStorage::list_latest_reports($uid, NOTSUBM, 0, RPTP_NORMAL));
		if ($reps !== false) {
			echo "    > individual report, NOT submitted\n";
			$sto = new ReportStorage($uid);
			$data = $sto->fetch_pdf($rep['token']);
		}
	}
	if ($data !== false) {
		$blob = new ReportBlob(BLOB_PDF, 2010, $uid);
		$blob->store($data, ReportBlob::create_address(RP_RESEARCHER));
	}

	$reps = reset(ReportStorage::list_latest_reports($uid, SUBM, 0, RPTP_LEADER));
	$data = false;
	if ($reps !== false) {
		echo "    > leader report, submitted\n";
		$sto = new ReportStorage($uid);
		$data = $sto->fetch_pdf($rep['token']);
	}
	else {
		$reps = reset(ReportStorage::list_latest_reports($uid, NOTSUBM, 0, RPTP_LEADER));
		if ($reps !== false) {
			echo "    > leader report, NOT submitted\n";
			$sto = new ReportStorage($uid);
			$data = $sto->fetch_pdf($rep['token']);
		}
	}
	if ($data !== false) {
		$blob = new ReportBlob(BLOB_PDF, 2010, $uid);
		$blob->store($data, ReportBlob::create_address(RP_LEADER));
	}

	// XXX: evaluator PDF is more complex.  Not handled currently.
}

