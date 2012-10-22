<?php
$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Postings'] = 'Postings';
$wgExtensionMessagesFiles['Postings'] = $dir . 'Postings.i18n.php';
$wgSpecialPageGroups['Postings'] = 'other-tools';

function runPostings($par) {
	Postings::run($par);
}

define('PST_ID', 'id');
define('PST_USER', 'user');
define('PST_TYPE', 'type');
define('PST_START', 'start');
define('PST_END', 'end');
define('PST_TITLE', 'title');
define('PST_URL', 'url');
define('PST_DESCR', 'descr');
define('PST_CHANGE', 'changed');

define('IGN_DATE', '2011-1-1');
define('NULL_DATE', '0000-0-0');

// This should be always sorted, and the type values immutable (ideally).
$_types = array(
		1 => 'Academic position',
		2 => 'Academic position (M.Sc.)',
		3 => 'Academic position (Ph.D.)',
		4 => 'Academic position (Post-doc)',
		5 => 'Call for Papers',
		13 => 'Call for Participation',
		12 => 'Conference / Symposium',
		6 => 'Highly-qualified Personnel Position',
		7 => 'Professional position',
		8 => 'Professional position (M.Sc.)',
		9 => 'Professional position (Ph.D.)',
		10 => 'Professional position (Post-doc)',
		11 => 'Research-collaboration opportunity',
		14 => 'Research Study',
		0 => 'Other'
		);
//asort($_types);

$_foldscript = "
<script type='text/javascript'>
function mySelect(form){ form.select(); }
function ShowOrHide(d1, d2) {
	if (d1 != '') DoDiv(d1);
	if (d2 != '') DoDiv(d2);
}
function DoDiv(id) {
	var item = null;
	if (document.getElementById) {
		item = document.getElementById(id);
	} else if (document.all) {
		item = document.all[id];
	} else if (document.layers) {
		item = document.layers[id];
	}
	if (!item) {
	}
	else if (item.style) {
		if (item.style.display == 'none') { item.style.display = ''; }
		else { item.style.display = 'none'; }
	}
	else { item.visibility = 'show'; }
}
</script>";


// TODO: move this to GRAND objects.
class Posting {
	private $_id;
	private $_user;
	private $_type;
	private $_start;
	private $_end;
	private $_title;
	private $_url;
	private $_descr;
	private $_changed;

	function __construct($id = array()) {
		// Use the argument to fill in the array.
		$this->_id	= ArrayUtils::get_field($id, PST_ID, 0);
		$this->_user	= ArrayUtils::get_field($id, PST_USER, 0);
		$this->_type	= ArrayUtils::get_field($id, PST_TYPE, 0);
		$this->_start	= ArrayUtils::get_string($id, PST_START);
		$this->_end	= ArrayUtils::get_string($id, PST_END);
		$this->_title	= ArrayUtils::get_string($id, PST_TITLE);
		$this->_url     = ArrayUtils::get_string($id, PST_URL);
		$this->_descr	= ArrayUtils::get_string($id, PST_DESCR);
		$this->_changed = ArrayUtils::get_string($id, PST_CHANGE);
	}


	/// Modify the attribute #field of this posting to that of #value.
	/// The ID field cannot be changed.
	function change($field, $value) {
		switch ($field) {
		case PST_USER:
			$this->_user = $value;
			break;
		case PST_TYPE:
			$this->_type = $value;
			break;
		case PST_START:
			$this->_start = $value;
			break;
		case PST_END:
			$this->_end = $value;
			break;
		case PST_TITLE:
			$this->_title = $value;
			break;
		case PST_URL:
		    $this->_url = $value;
		    break;
		case PST_DESCR:
			$this->_descr = $value;
			break;

		default:
			// Incorrect field, bail out.
			return false;
		}

		return true;
	}


	/// Render this posting as a table row.
	function render_tablerow() {
		global $_types;
		$repl = array("\r\n" => '<br />', "\r" => '<br />', "\n" => '<br />');

		$chunk = "<tr><td align='center' valign='top'>" . ArrayUtils::get_string($_types, $this->_type, 'Unknown');
        $chunk .= "<td valign='top'>{$this->_changed}</td>";
		$chunk .= "<td valign='top'>{$this->_start}";
		if (strlen($this->_end) > 0 && $this->_end[0] !== '0')
			$chunk .= " to<br />{$this->_end}";
			
		$chunk .= "<td>";
		if (! empty($this->_title)){
		    if($this->_url != ""){
			    $chunk .= "<p align='center'><b><a href='{$this->_url}'>{$this->_title}</a></b></p>";
			}
			else{
			    $chunk .= "<p align='center'><b>{$this->_title}</b></p>";
			}
	    }
	    

		$dlen = strlen($this->_descr);
		$text = strtr($this->_descr, $repl);
		if ($dlen > 120) {
			// Present 2 divs: a snippet and the long, foldable version.
			$sid = "short_{$this->_id}";
			$lid = "long_{$this->_id}";
			$chunk .= "<div id='{$sid}'>" . substr($this->_descr, 0, 120) .
				"&hellip; <a href=\"javascript:ShowOrHide('{$sid}','{$lid}')\">(more)</a></div>" .
				"<div id='{$lid}' style='display:none'>{$text} " .
				"<a href=\"javascript:ShowOrHide('{$sid}','{$lid}')\">(less)</a></div>";
		}
		else if ($dlen > 0) {
			// The description is too short for folding tricks.
			$chunk .= "{$text}";
		}
		else
			$chunk .= "(No description)";

		return $chunk;
	}


	/// Commit the posting to the database, either updating or creating
	/// a new one.
	function commit() {
		if ($this->_id !== 0) {
			$sql = "UPDATE grand_postings SET user_id = {$this->_user}, " .
				"type = {$this->_type}, " .
				"start = '{$this->_start}', " .
				"end = '{$this->_end}', " .
				"title = '" . mysql_real_escape_string($this->_title) . "', " .
				"url = '" . mysql_real_escape_string($this->_url) . "', " .
				"descr = '" . mysql_real_escape_string($this->_descr) . "';";
		}
		else {
			$sql = "INSERT INTO grand_postings (user_id, type, start, end, title, url, descr) " .
				"VALUES ({$this->_user}, {$this->_type}, '{$this->_start}', '$this->_end', '" .
				mysql_real_escape_string($this->_title) . "', '" .
				mysql_real_escape_string($this->_url) ."', '" .
				mysql_real_escape_string($this->_descr) . "');";
		}

		DBFunctions::execSQL($sql, true);
		// TODO: check result.
		return true;
	}


	/// Queries the database for all postings expiring 1 week after the
	/// current day, and create instances of Posting objects in an array
	/// indexed by posting ID, returning it.
	/// If there are no postings matching the date range, the returned
	/// array will be empty.
	static function all_postings() {
		$ret = array();
		$sql = "SELECT * FROM grand_postings WHERE DATEDIFF(ADDDATE(GREATEST(start, end), 7), CURDATE()) > 0 ORDER BY start;";
		$res = DBFunctions::execSQL($sql);
		foreach ($res as $row) {
			$ret[$row['id']] = new Posting($row);
		}

		return $ret;
	}
}


/// The class for the actual page.
class Postings extends SpecialPage {


	function __construct() {
		// FIXME: these do NOT seem to be executed.
		wfLoadExtensionMessages('Postings');
		SpecialPage::SpecialPage("Postings", HQP.'+', true, 'runPostings');
	}

	function run($par) {
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $_types, $_foldscript, $_POST;
		$wgOut->setPageTitle("GRAND Postings");
		if (ArrayUtils::get_field($_POST, 'submit_posting')) {
			// Prepare an array with the posting information.
			$arr = array();
			$arr[PST_USER]	= $wgUser->getId();
			$arr[PST_TYPE]	= ArrayUtils::get_field($_POST, PST_TYPE, 0);
			// If no date is available, make start date being today,
			// and end date being zero.
			$ds = ArrayUtils::get_string($_POST, 'day_s', date('d'));
			$de = ArrayUtils::get_string($_POST, 'day_e', 0);
			$ms = ArrayUtils::get_string($_POST, 'mon_s', date('m'));
			$me = ArrayUtils::get_string($_POST, 'mon_e', 0);
			$ys = ArrayUtils::get_string($_POST, 'year_s', date('Y'));
			$ye = ArrayUtils::get_string($_POST, 'year_e', 0);
			$enddate = "{$ye}-{$me}-{$de}";
			if ($enddate == IGN_DATE)
				$enddate = NULL_DATE;
			$arr[PST_START]	= "{$ys}-{$ms}-{$ds}";
			$arr[PST_END]	= $enddate;
			$arr[PST_TITLE] = ArrayUtils::get_string($_POST, PST_TITLE);
			$arr[PST_URL] = ArrayUtils::get_string($_POST, PST_URL);
			$arr[PST_DESCR]	= ArrayUtils::get_string($_POST, PST_DESCR);

			// TODO: validation checks.

			// Create the object and commit it.
			$npst = new Posting($arr);
			$npst->commit();
		}

		$wgOut->addScript($_foldscript);
		$pg = "$wgServer$wgScriptPath/index.php/Special:Postings";

		// Prepare selection boxes.
		$select = "<select name='type'>\n\t<option value='0'>(Choose)</option>";
		foreach ($_types as $k => $v){
		    if($k != 6){ // HQP option is no longer available
			    $select .= "\n\t<option value='$k'>$v</option>";
			}
	    }
		$select .= "\n</select>\n";

		$day = "";
		for ($i = 1; $i <= 31; $i++)
			$day .= "\n\t<option value='{$i}'>{$i}</option>";
		$day_s = "<select name='day_s'>{$day}</select>";
		$day_e = "<select name='day_e'>{$day}</select>";

		$mon = "";
		foreach (array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
					5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
					9 => 'September', 10 => 'October', 11 => 'November',
					12 => 'December') as $k => $v)
			$mon .= "\n\t<option value='$k'>$v</option>";
		$mon_s = "<select name='mon_s'>{$mon}</select>";
		$mon_e = "<select name='mon_e'>{$mon}</select>";

		$year = "";
		for ($i = 2011; $i <= 2015; $i++)
			$year .= "\n\t<option value='$i'>$i</option>";
		$year_s = "<select name='year_s'>{$year}</select>";
		$year_e = "<select name='year_e'>{$year}</select>";

		// Form for new postings.
		$li_st = 'width:8em;display:inline-block;font-weight:bold';
		$chunk = "
<p>&nbsp;</p>
<p align='right'><a href=\"javascript:ShowOrHide('new_posting','')\" style='background-color:#f2efb0;padding:6px;font-weight:bold'>New posting...</a></p>
<div id='new_posting' style='display:none'>
<form action='{$pg}' name='posting_form' method='post'><fieldset>
	<legend>Add new posting</legend>
	<ul>
		<li>
			<label for='type' style='{$li_st}'>Posting type:</label>
			{$select}
		<li>
			<label for='day_s' style='{$li_st}'>Opening date:</label>
			{$day_s}
			{$mon_s}
			{$year_s}
	       	<li>
			<label for='day_e' style='{$li_st}'>Closing date:</label>
			{$day_e}
			{$mon_e}
			{$year_e}
			(Optional)
		<li>
			<label for='title' style='{$li_st}'>Title:</label>
			<input name='title' size='80' />
	    <li>
			<label for='url' style='{$li_st}'>URL:</label>
			<input name='url' size='80' />
		<li>
			<label for='descr' style='{$li_st}'>Description:</label>
			<textarea name='descr' rows='20' cols='80'></textarea>
	</ul>
	<p align='center'><input type='submit' name='submit_posting' value='Submit new posting' /></p>
</fieldset></form></div><br />";

		// Show postings.
		$chunk .= "
<table class='wikitable sortable' cellspacing='1' cellpadding='2' frame='box' rules='all' width='100%'>
<tr><th style='width:14em'>What<th style='width:12em'>Posted<th style='width:8em'>When<th>Details
";
		$postings = Posting::all_postings();
		foreach ($postings as &$posting) {
			$chunk .= $posting->render_tablerow();
		}
		$chunk .= "</table>";

		$wgOut->addHTML($chunk);
	}
}
?>
