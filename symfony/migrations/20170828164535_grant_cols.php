<?php

use Phinx\Migration\AbstractMigration;

class GrantCols extends AbstractMigration
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
        $table = $this->table('grand_grants');
        $table->addColumn('role', 'string', array('after' => 'description', 'limit' => 32))
              ->addColumn('seq_no', 'integer', array('after' => 'role'))
              ->addColumn('prog_description', 'string', array('after' => 'seq_no', 'limit' => 32))
              ->update();
    }
}
