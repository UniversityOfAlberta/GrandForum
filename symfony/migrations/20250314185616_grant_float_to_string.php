<?php

use Phinx\Migration\AbstractMigration;

class GrantFloatToString extends AbstractMigration
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
        $table->changeColumn('total', 'string', array('limit' => 16))
              ->changeColumn('adjusted_amount', 'string', array('limit' => 16))
              ->changeColumn('adjusted_amount', 'string', array('limit' => 16))
              ->changeColumn('funds_before', 'string', array('limit' => 16))
              ->changeColumn('funds_after', 'string', array('limit' => 16))
              ->update();
    }
}
