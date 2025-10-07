<?php

use Phinx\Migration\AbstractMigration;

class PdfProjectId extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('grand_pdf_report');
        $table->addColumn('proj_id', 'integer', array('after' => 'user_id'))
              ->addIndex('proj_id')
              ->update();
        
        $stmt = $this->query('SELECT * FROM `grand_pdf_index`');
        $rows = $stmt->fetchAll();
        
        foreach($rows as $row){
            $this->execute("UPDATE `grand_pdf_report`
                            SET proj_id = '{$row['sub_id']}',
                                timestamp = timestamp
                            WHERE report_id = '{$row['report_id']}'");
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
