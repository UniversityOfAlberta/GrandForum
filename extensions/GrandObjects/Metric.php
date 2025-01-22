<?php 

/**
 *@package GrandObjects
 */
class Metric {

    var $id;
    var $user_id;
    var $acm_start_date;
    var $acm_end_date;
    var $acm_publication_count;
    var $acm_avg_citations_per_article;
    var $acm_citation_count;
    var $acm_avg_download_per_article;
    var $acm_available_download;
    var $acm_download_cumulative;
    var $acm_download_6_weeks;
    var $acm_download_1_year;
    var $sciverse_coauthor_count;
    var $sciverse_hindex;
    var $sciverse_citation_count;
    var $sciverse_cited_by_count;
    var $sciverse_doc_count;
    var $change_date;
    var $gs_id;
    var $gs_hindex_5_years;
    var $gs_i10_index_5_years;
    var $gs_hindex;
    var $gs_i10_index;
    var $gs_change_date;

    // constructor
    function __construct($data){
        $this->id = $data[0]['id'];
        $this->user_id = $data[0]['user_id'];
        $this->acm_start_date = $data[0]['acm_start_date'];
        $this->acm_end_date = $data[0]['acm_end_date'];
        $this->acm_publication_count = $data[0]['acm_publication_count'];
        $this->acm_avg_citations_per_article = $data[0]['acm_avg_citations_per_article'];
        $this->acm_citation_count = $data[0]['acm_citation_count'];
        $this->acm_avg_download_per_article = $data[0]['acm_avg_download_per_article'];
        $this->acm_available_download = $data[0]['acm_available_download'];
        $this->acm_download_cumulative = $data[0]['acm_download_cumulative'];
        $this->acm_download_6_weeks = $data[0]['acm_download_6_weeks'];
        $this->acm_download_1_year = $data[0]['acm_download_1_year'];
        $this->sciverse_coauthor_count = $data[0]['sciverse_coauthor_count'];
        $this->sciverse_hindex = $data[0]['sciverse_hindex'];
        $this->sciverse_citation_count = $data[0]['sciverse_citation_count'];
        $this->sciverse_cited_by_count = $data[0]['sciverse_cited_by_count'];
        $this->sciverse_doc_count = $data[0]['sciverse_doc_count'];
        $this->change_date = $data[0]['change_date'];
    }

    /**
     * Returns a new Metric from the given id
     * @param integer $id The id of the metric
     * @return Metric The Metric with the given id. If no 
     * metric exists with that id, it will return an empty metric.
    */
    static function newFromId($id){
        $me = Person::newFromWgUser();
        $sql = "SELECT *
                FROM grand_user_metrics
                WHERE `id` = '$id'";
        $data = DBFunctions::execSQL($sql);
        $metric = new Metric($data);
        return $metric;
    }

    /**
     * Returns an array of all the recent metrics in the database
     * return Array of metric objects
     */
    static function getAllMetrics(){
        $sql = "SELECT v1.id
                FROM grand_user_metrics v1
                WHERE v1.change_date >= (SELECT MAX(v2.change_date)
                            FROM grand_user_metrics v2
                            WHERE v1.user_id = v2.user_id)";
        $data = DBFunctions::execSQL($sql);
        $metrics = array();
        foreach($data as $row){
            $metrics[] = Metric::newFromId($row['id']);
        }
        return $metrics;
    }

    /**
     * Returns the user's most recent metric in the database
     * return Metric The most recent Metric of the user.
     */      
    static function getUserMetric($id){
        $sql = "SELECT v1.id
                FROM grand_user_metrics v1
                WHERE v1.user_id = $id 
                AND v1.change_date >= (SELECT MAX(v2.change_date)
                                       FROM grand_user_metrics v2
                                       WHERE v1.user_id = v2.user_id)";
        $data = DBFunctions::execSQL($sql);
        $metric = "";
        if(count($data)>0){
            $metric = Metric::newFromId($data[0]['id']);
        }
        return $metric;
    }
}

?>
