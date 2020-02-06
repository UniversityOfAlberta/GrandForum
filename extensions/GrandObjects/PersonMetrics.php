<?php 

/**
 *@package GrandObjects
 */
class PersonMetrics {

    var $id;
    var $user_id;
    var $gs_citation_count;
    var $gs_hindex_5_years;
    var $gs_i10_index_5_years;
    var $gs_hindex;
    var $gs_i10_index;
    var $gs_citations = array();
    var $change_date;

    // constructor
    function PersonMetrics($data){
        if(count($data)>0){
            $this->id = $data[0]['id'];
            $this->user_id = $data[0]['user_id'];
            $this->gs_citation_count = $data[0]['gs_citation_count'];
            $this->gs_hindex_5_years = $data[0]['gs_hindex_5_years'];
            $this->gs_i10_index_5_years = $data[0]['gs_i10_index_5_years'];
            $this->gs_hindex = $data[0]['gs_hindex'];
            $this->gs_i10_index = $data[0]['gs_i10_index'];
            $this->change_date = $data[0]['change_date'];
            $this->gs_citations = $this->getGsCitations();
        }
     }

    /**
     * Returns a new Metric from the given id
     * @param integer $id The id of the metric
     * @return Metric The Metric with the given id. If no 
     * metric exists with that id, it will return an empty metric.
     */
    static function newFromId($id){
        $sql = "SELECT *
                FROM grand_user_metrics
                WHERE `id` = '$id'";
        $data = DBFunctions::execSQL($sql);
        $metric = new PersonMetrics($data);
        return $metric;
    }

    /**
     * Returns an array of all the recent metrics in the database
     * return Array of metric objects
     */
    static function getAllPersonMetrics(){
        $sql = "SELECT v1.id
                FROM grand_user_metrics v1
                WHERE v1.change_date >= (SELECT MAX(v2.change_date)
                                         FROM grand_user_metrics v2
                                         WHERE v1.user_id = v2.user_id)";
        $data = DBFunctions::execSQL($sql);
        $metrics = array();
        foreach($data as $row){
            $metrics[] = PersonMetrics::newFromId($row['id']);
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
                $metric = PersonMetrics::newFromId($data[0]['id']);
            }
            return $metric;
        }

    function getRecentCitationCount(){
        $citationCount = 0;
        $citationArray = $this->gs_citations;
        $year = date("Y");
        $count = 0;
        while($count <= 5){
            if(isset($citationArray[$year])){
                $citationCount += $citationArray[$year];
            }
            $count++;
            $year--;
        }
        return $citationCount;
    }

    /**
     * Returns an array of the users citations count from Google Scholar
     * return Array An array with a key-value pair of [year] => citation count
    */
    function getGsCitations(){
        if(count($this->gs_citations) == 0){
            $sql = "SELECT *
                    FROM grand_gs_citations
                    WHERE user_id = {$this->user_id}";
            $data = DBFunctions::execSQL($sql);
            $newArray = array();
            foreach($data as $row){
                $year = $row['year'];
                $newArray[$year] = $row['count'];
            }
            $this->gs_citations = $newArray;
        }
        return $this->gs_citations;
    }

    /**
     * Returns True if the PersonMetrics is saved correctly
     * @return boolean True if database accepted new PersonMetrics
    */
    function create(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()){
            DBFunctions::begin();
            $status = DBFunctions::insert('grand_user_metrics',
                                          array('user_id' => $this->user_id,
                                                'gs_citation_count' => $this->gs_citation_count,
                                                'gs_hindex_5_years' => $this->gs_hindex_5_years,
                                                'gs_i10_index_5_years' => $this->gs_i10_index_5_years,
                                                'gs_hindex' => $this->gs_hindex,
                                                'gs_i10_index' => $this->gs_i10_index),
                                          true);
            if($status){
                $status = DBFunctions::delete('grand_gs_citations',
                            array('user_id' => $this->user_id),
                            true);

                while($status && list($key, $val) = each($this->gs_citations)){
                    $status = DBFunctions::insert('grand_gs_citations',
                                      array('user_id' => $this->user_id,
                                            'year' => $key,
                                            'count' => $val),
                                      true);
                    if(!$status){
                        return false;
                    }
                }
                DBFunctions::commit();
                return $status;
            }
        }
        return false;
    }
}
?>
