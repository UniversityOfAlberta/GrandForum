<?php

use Phinx\Migration\AbstractMigration;

class FecHistoryFields extends AbstractMigration
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
        $this->table('grand_personal_fec_info')
             ->addColumn('date_probation2', 'timestamp', array('after' => 'date_professor'))
             ->addColumn('date_probation1', 'timestamp', array('after' => 'date_professor'))
             ->save();
    }
}