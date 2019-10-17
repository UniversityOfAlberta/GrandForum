<?php


use Phinx\Migration\AbstractMigration;

class RpComMigrate extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->execute("DELETE FROM `grand_report_blobs` 
                        WHERE rp_type = 'RP_COM' 
                        AND year = 2019
                        AND proj_id NOT IN (SELECT id FROM grand_sop)");
        
        $this->execute("DELETE FROM `grand_report_blobs` 
                        WHERE rp_type = 'RP_COM' 
                        AND year = 2017
                        AND proj_id NOT IN (SELECT id FROM grand_sop_2017)");
                        
        $this->execute("DELETE FROM `grand_report_blobs` 
                        WHERE rp_type = 'RP_COM' 
                        AND year = 2018
                        AND proj_id NOT IN (SELECT id FROM grand_sop_2018)");
    
        // CURRENT
        $stmt = $this->query("SELECT s.user_id, g.id as gsms_id, s.id as sop_id 
                              FROM grand_sop s, grand_gsms g 
                              WHERE g.user_id = s.user_id");
        $rows = $stmt->fetchAll();
        $idMap = array();
        foreach($rows as $row){
            $idMap[$row['sop_id']] = $row['gsms_id'];
        }
        $stmt = $this->query("SELECT * 
                              FROM grand_report_blobs
                              WHERE year = 2019
                              AND rp_type = 'RP_COM'");
        $rows = $stmt->fetchAll();
        foreach($rows as $row){
            if(isset($idMap[$row['proj_id']])){
                $this->execute("UPDATE grand_report_blobs
                                SET proj_id    = {$idMap[$row['proj_id']]},
                                    rp_subitem = {$idMap[$row['proj_id']]}
                                WHERE blob_id = {$row['blob_id']}");
            }
        }
        
        // 2017
        $stmt = $this->query("SELECT s.user_id, g.id as gsms_id, s.id as sop_id 
                              FROM grand_sop_2017 s, grand_gsms_2017 g 
                              WHERE g.user_id = s.user_id");
        $rows = $stmt->fetchAll();
        $idMap = array();
        foreach($rows as $row){
            $idMap[$row['sop_id']] = $row['gsms_id'];
        }
        $stmt = $this->query("SELECT * 
                              FROM grand_report_blobs
                              WHERE year = 2017
                              AND rp_type = 'RP_COM'");
        $rows = $stmt->fetchAll();
        foreach($rows as $row){
            if(isset($idMap[$row['proj_id']])){
                $this->execute("UPDATE grand_report_blobs
                                SET proj_id    = {$idMap[$row['proj_id']]},
                                    rp_subitem = {$idMap[$row['proj_id']]}
                                WHERE blob_id = {$row['blob_id']}");
            }
        }
        
        // 2018
        $stmt = $this->query("SELECT s.user_id, g.id as gsms_id, s.id as sop_id 
                              FROM grand_sop_2018 s, grand_gsms_2018 g 
                              WHERE g.user_id = s.user_id");
        $rows = $stmt->fetchAll();
        $idMap = array();
        foreach($rows as $row){
            $idMap[$row['sop_id']] = $row['gsms_id'];
        }
        $stmt = $this->query("SELECT * 
                              FROM grand_report_blobs
                              WHERE year = 2018
                              AND rp_type = 'RP_COM'");
        $rows = $stmt->fetchAll();
        foreach($rows as $row){
            if(isset($idMap[$row['proj_id']])){
                $this->execute("UPDATE grand_report_blobs
                                SET proj_id    = {$idMap[$row['proj_id']]},
                                    rp_subitem = {$idMap[$row['proj_id']]}
                                WHERE blob_id = {$row['blob_id']}");
            }
        }
    }
}
