<?php

/// Encapsulates the storage and retrieval of reports generated as PDF files.
class ReportStorage {

    private $_uid = 0;
    private $_pid = 0;
    private $_person;
    private $_project;
    private $_cache;

    function __construct($person, $project){
        $this->_person = $person;
        $this->_project = $project;
        if($person != null && $person->getId() != null){
            $this->_uid = $person->getId();
        }
        if($project != null){
            $this->_pid = $project->getId();
        }
        $this->_cache = null;
    }

    /// Tries to "select" a report for download.  If the user_id does not
    /// match, the request is denied and the state of the object is not
    /// changed.
    function select_report($tok, $strict = true) {
        $ext = ($strict) ? "user_id = {$this->_uid} AND proj_id = {$this->_pid} AND" : "";
        $res = DBFunctions::execSQL("SELECT user_id 
                                     FROM grand_pdf_report 
                                     WHERE {$ext} token = '{$tok}'");
        if(DBFunctions::getNRows() > 0){
            $this->load_metadata($tok, $strict);
            return $this->_cache['token'];
        }
        return false;
    }

    /// Store a new report.
    function store_report(&$data, &$html, &$pdf, $special = 0, $auto = 0, $type = 0, $year = REPORTING_YEAR, $encrypt = false) {
        global $wgImpersonating, $wgRealUser, $wgUser;
        $impersonateId = $this->_uid;
        if($wgImpersonating){
            $impersonateId = $wgRealUser->getId();
        }
        else{
            $impersonateId = $wgUser->getId();
        }
        
        $uname = $this->_person->getName();
        
        $len = strlen($pdf);
        $sdata = serialize($data);
        
        if($encrypt){
            $html = encrypt($html);
            $sdata = encrypt($sdata);
            $pdf = encrypt($pdf);
        }

        $tst = time();
        $hdata = sha1($sdata);
        $hpdf = sha1($pdf);

        // The token is the MD5 digest of the user ID, user name, timestamp,
        // the hash of the data and the hash of PDF file.
        $tok = md5($this->_uid . $uname . $tst . $hdata . $hpdf);

        $sql = "INSERT INTO grand_pdf_report (user_id, proj_id, generation_user_id, year, type, special, auto, token, timestamp, len_pdf, hash_data, hash_pdf, data, html, pdf, encrypted) 
                VALUES ({$this->_uid}, {$this->_pid}, {$impersonateId}, {$year}, '{$type}', {$special}, {$auto}, '{$tok}', FROM_UNIXTIME({$tst}), '{$len}', '{$hdata}', '{$hpdf}', '" .
            DBFunctions::escape($sdata) . "', '" .
            DBFunctions::escape(utf8_decode($html)) . "', '" .
            DBFunctions::escape($pdf) . "', $encrypt)";

        DBFunctions::execSQL($sql, true);
        DBFunctions::commit();
        // Update metadata.
        return $this->load_metadata($tok);
    }

    /// Retrieves a specific report (PDF) for the user.  The PDF returned
    /// (if any) is a string.
    function fetch_pdf($tok, $strict = true) {
        $ext = ($strict) ? "user_id = {$this->_uid} AND proj_id = {$this->_pid} AND" : "";
        $sql = "SELECT report_id, user_id, proj_id, type, submitted, auto, timestamp, len_pdf, pdf, encrypted, generation_user_id, submission_user_id, year 
                FROM grand_pdf_report 
                WHERE {$ext} token = '{$tok}' 
                ORDER BY timestamp DESC LIMIT 1";
        $res = DBFunctions::execSQL($sql);
        if (count($res) <= 0) {
            return false;
        }

        $this->_cache['report_id'] = $res[0]['report_id'];
        $this->_cache['user_id'] = $res[0]['user_id'];
        $this->_cache['proj_id'] = $res[0]['proj_id'];
        $this->_cache['type'] = $res[0]['type'];
        $this->_cache['submitted'] = $res[0]['submitted'];
        $this->_cache['auto'] = $res[0]['auto'];
        $this->_cache['token'] = $tok;
        $this->_cache['timestamp'] = $res[0]['timestamp'];
        $this->_cache['year'] = $res[0]['year'];
        $this->_cache['len_pdf'] = $res[0]['len_pdf'];
        $this->_cache['generation_user_id'] = $res[0]['generation_user_id'];
        $this->_cache['submission_user_id'] = $res[0]['submission_user_id'];
        $this->_cache['encrypted'] = $res[0]['encrypted'];

        return ($this->_cache['encrypted']) ? decrypt($res[0]['pdf']) : $res[0]['pdf'];
    }
    
    function mark_submitted($tok) {
        global $wgImpersonating, $wgRealUser;
        $tok = DBFunctions::escape($tok);
        $impersonateId = $this->_uid;
        if($wgImpersonating){
            $impersonateId = $wgRealUser->getId();
        }
        
        $res = DBFunctions::execSQL("SELECT special, submitted 
                                     FROM grand_pdf_report 
                                     WHERE token = '{$tok}'");
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

        DBFunctions::execSQL("UPDATE grand_pdf_report 
                              SET submitted = 1,
                                  submission_user_id = $impersonateId,
                                  timestamp = timestamp
                              WHERE token = '{$tok}'", true);
        DBFunctions::commit();
        // Refresh.
        $this->load_metadata($tok);
        // Either 0 or 1.
        return $this->_cache['submitted'];
    }

    private function load_metadata($tok = false, $strict = false) {
        $ext = ($strict) ? "user_id = {$this->_uid} AND proj_id = {$this->_pid} AND" : "";

        // Load data from the DB.
        if ($tok === false) {
            // FIXME: token must be enforced --- no token-use must be removed.
            $sql = "SELECT report_id, type, user_id, proj_id, submitted, auto, token, timestamp, len_pdf, generation_user_id, submission_user_id, year 
                    FROM grand_pdf_report 
                    WHERE user_id = {$this->_uid}
                    AND proj_id = {$this->_pid}
                    ORDER BY timestamp DESC LIMIT 1;";
        }
        else {
            $sql = "SELECT report_id, type, user_id, proj_id, submitted, auto, token, timestamp, len_pdf, generation_user_id, submission_user_id, year 
                    FROM grand_pdf_report 
                    WHERE {$ext} token = '{$tok}' 
                    ORDER BY timestamp DESC LIMIT 1;";
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
        $this->_cache['proj_id'] = $res[0]['proj_id'];
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
            $this->load_metadata();
        }

        if (isset($this->_cache[$field])) {
            return $this->_cache[$field];
        }
        else {
            return false;
        }
    }

    function get_report_project_id(){
        return $this->metadata('proj_id');
    }

    /// Returns an array of report entries for users #uarr with as many as #lim
    /// entries per user.  By default, submitted reports are considered, which
    /// can be changed with #subm.  #uarr is either an array of numeric user IDs
    /// or an integer (for the user ID).
    static function list_reports($uarr, $subm = 1, $lim = 1, $special = 0, $type = 0, $year = "") {
        if (is_array($uarr)) {
            $uarr = implode(', ', $uarr);
        }

        if (strlen($uarr) === 0)
            return array();
        if($lim == 0){
            $lim = "";
        }
        else{
            $lim = "LIMIT {$lim}";
        }
        if($year !== ""){
            $year = "AND year = {$year}";
        }
        $sql = "SELECT user_id, proj_id, generation_user_id, submission_user_id, report_id, submitted, auto, token, timestamp, year
                FROM grand_pdf_report 
                WHERE user_id IN ({$uarr}) 
                AND submitted = {$subm} 
                AND type = '{$type}' 
                {$year}
                AND proj_id = 0
                ORDER BY timestamp DESC
                {$lim};";
        return DBFunctions::execSQL($sql);
    }
    
    static function list_project_reports($proj_id, $lim = 1, $special = 0, $type = RPTP_LEADER, $year=REPORTING_YEAR) {
        if($lim == 0){
            $lim = "";
        }
        else{
            $lim = "LIMIT {$lim}";
        }
        $sql = "SELECT user_id, proj_id, generation_user_id, submission_user_id, report_id, submitted, auto, token, timestamp, year
                FROM grand_pdf_report
                WHERE proj_id = {$proj_id}
                AND type = '{$type}' 
                AND year = {$year} 
                ORDER BY timestamp DESC
                {$lim}";
        $res = DBFunctions::execSQL($sql);
        return $res;
    }
    
    static function list_user_project_reports($proj_id, $user_id, $lim = 1, $special = 0, $type = RPTP_LEADER, $year = null){
        if($user_id == ""){
            $user_id = 0;
        }
        if($lim == 0){
            $lim = "";
        }
        else{
            $lim = "LIMIT {$lim}";
        }
        if($year !== null || $year !== ""){
            $year = "AND year = '$year'";
        }
        $sql = "SELECT user_id, proj_id, generation_user_id, submission_user_id, report_id, submitted, auto, token, timestamp, year
                FROM grand_pdf_report
                WHERE proj_id = {$proj_id}
                AND user_id = {$user_id}
                AND type = '{$type}'
                {$year}
                ORDER BY timestamp DESC
                {$lim}";
        $res = DBFunctions::execSQL($sql);
        return $res;
    }

}
