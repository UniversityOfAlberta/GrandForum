<?php

use Phinx\Migration\AbstractMigration;

class ReportBlobIndex extends AbstractMigration
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
        $table = $this->table('grand_report_blobs');
        $table->removeIndexByName('year')
              ->addIndex('year')
              ->addIndex('user_id')
              ->addIndex('proj_id')
              ->addIndex('rp_type')
              ->addIndex('rp_section')
              ->addIndex('rp_item')
              ->addIndex('rp_subitem')
              ->update();
    }
}
