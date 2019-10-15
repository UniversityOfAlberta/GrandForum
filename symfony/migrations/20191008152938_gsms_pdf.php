<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class GsmsPdf extends AbstractMigration
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
        $conn = $this->getAdapter()->getConnection();
        
        // CURRENT
        $table = $this->table('grand_gsms');
        $table->addColumn('pdf_contents', 'blob', array('limit' => MysqlAdapter::BLOB_LONG, 'after' => 'additional'))
              ->save();
    
        $stmt = $this->query("SELECT * 
                              FROM grand_sop");
        $rows = $stmt->fetchAll();
        foreach($rows as $row){
            $pdf_contents = $conn->quote($row['pdf_contents']);
            $this->execute("UPDATE grand_gsms
                            SET pdf_contents = {$pdf_contents}
                            WHERE user_id = '{$row['user_id']}'");
        }
        
        
        // 2017
        $table = $this->table('grand_gsms_2017');
        $table->addColumn('pdf_contents', 'blob', array('limit' => MysqlAdapter::BLOB_LONG, 'after' => 'additional'))
              ->save();
    
        $stmt = $this->query("SELECT * 
                              FROM grand_sop_2017");
        $rows = $stmt->fetchAll();
        foreach($rows as $row){
            $pdf_contents = $conn->quote($row['pdf_contents']);
            $this->execute("UPDATE grand_gsms_2017
                            SET pdf_contents = {$pdf_contents}
                            WHERE user_id = '{$row['user_id']}'");
        }
        
        // 2018
        $table = $this->table('grand_gsms_2018');
        $table->addColumn('pdf_contents', 'blob', array('limit' => MysqlAdapter::BLOB_LONG, 'after' => 'additional'))
              ->save();
    
        $stmt = $this->query("SELECT * 
                              FROM grand_sop_2018");
        $rows = $stmt->fetchAll();
        foreach($rows as $row){
            $pdf_contents = $conn->quote($row['pdf_contents']);
            $this->execute("UPDATE grand_gsms_2018
                            SET pdf_contents = {$pdf_contents}
                            WHERE user_id = '{$row['user_id']}'");
        }
    }
}
