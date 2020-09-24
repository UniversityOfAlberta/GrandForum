<?php

use Phinx\Migration\AbstractMigration;

class Cna extends AbstractMigration
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
        $table = $this->table('grand_cna', array("id"=>"id"));
        $table->addColumn('user_id', 'integer')
              ->addColumn('year', 'string', array('limit' => 4))
              ->addColumn('increment', 'string', array('limit' => 16))
              ->addIndex('user_id')
              ->addIndex('year')
              ->create();
    }
}
