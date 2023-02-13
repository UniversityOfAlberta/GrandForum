<?php

use Phinx\Migration\AbstractMigration;

class Encrypted extends AbstractMigration
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
        $this->table('grand_report_blobs')
             ->addColumn('encrypted', 'boolean', array('after' => 'md5'))
             ->save();
    
        $this->table('grand_pdf_report')
             ->addColumn('encrypted', 'boolean', array('after' => 'pdf'))
             ->save();
    }
}
