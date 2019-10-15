<?php


use Phinx\Migration\AbstractMigration;

class SopAnnotation extends AbstractMigration
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
        // CURRENT
        $stmt = $this->query("SELECT s.user_id, g.id as gsms_id, s.id as sop_id 
                              FROM grand_sop s, grand_gsms g 
                              WHERE g.user_id = s.user_id");
        $rows = $stmt->fetchAll();
        foreach($rows as $row){
            $this->execute("UPDATE grand_sop_annotation
                            SET sop_id = {$row['gsms_id']}
                            WHERE sop_id = '{$row['sop_id']}'");
        }
        
        // 2017
        $stmt = $this->query("SELECT s.user_id, g.id as gsms_id, s.id as sop_id 
                              FROM grand_sop_2017 s, grand_gsms_2017 g 
                              WHERE g.user_id = s.user_id");
        $rows = $stmt->fetchAll();
        foreach($rows as $row){
            $this->execute("UPDATE grand_sop_annotation_2017
                            SET sop_id = {$row['gsms_id']}
                            WHERE sop_id = '{$row['sop_id']}'");
        }
        
        // 2018
        $stmt = $this->query("SELECT s.user_id, g.id as gsms_id, s.id as sop_id 
                              FROM grand_sop_2018 s, grand_gsms_2018 g 
                              WHERE g.user_id = s.user_id");
        $rows = $stmt->fetchAll();
        foreach($rows as $row){
            $this->execute("UPDATE grand_sop_annotation_2018
                            SET sop_id = {$row['gsms_id']}
                            WHERE sop_id = '{$row['sop_id']}'");
        }
    }
}
