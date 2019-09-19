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

    function insert_report($rid, $subj) {
        $type = 'PROJECT';
        if($subj instanceof Person){
            $type = 'PERSON';
        }
        DBFunctions::execSQL("INSERT INTO grand_pdf_index (report_id, user_id, sub_id, type, created) VALUES 
                              ({$rid}, {$this->_uid}, {$subj->getId()}, '$type', CURRENT_TIMESTAMP());", true);
        DBFunctions::commit();
        return true;
    }

    /// Returns an array of project identifiers for which the user has reports.
    function list_projects() {
        $ret = array();
        $res = DBFunctions::execSQL("SELECT DISTINCT sub_id 
                                     FROM grand_pdf_index 
                                     WHERE user_id = {$this->_uid} 
                                     ORDER BY sub_id;");
        foreach ($res as &$row) {
            $ret[] = $row['sub_id'];
        }
        return $ret;
    }

    /// Returns a result-set with up to #lim reports for this user, on project #proj,
    /// which is either a Project instance or a project ID (integer).
    function list_reports($subj, $lim = 1, $spec = 1) {
        if($lim == 0){
            $lim = "";
        }
        else{
            $lim = "LIMIT {$lim}";
        }
        if(is_object($subj)) {
            $type = 'PROJECT';
            if($subj instanceof Person){
                $type = 'PERSON';
            }
            return DBFunctions::execSQL("SELECT i.report_id, p.token, i.created, i.last_download, i.nr_download, p.year 
                                         FROM grand_pdf_index i LEFT JOIN (grand_pdf_report p) ON (i.report_id = p.report_id) 
                                         WHERE i.user_id = {$this->_uid} 
                                         AND i.sub_id = {$subj->getId()} 
                                         AND p.special = {$spec}
                                         AND i.type = '{$type}'
                                         ORDER BY created DESC
                                         {$lim}");
        }
        else {
            return DBFunctions::execSQL("SELECT i.report_id, p.token, i.created, i.last_download, i.nr_download, p.year 
                                         FROM grand_pdf_index i LEFT JOIN (grand_pdf_report p) ON (i.report_id = p.report_id) 
                                         WHERE i.user_id = {$this->_uid} 
                                         AND i.sub_id = {$subj} 
                                         AND p.special = {$spec} 
                                         ORDER BY created DESC
                                         {$lim};");
        }
    }

}
