<?php

use Phinx\Migration\AbstractMigration;

class AccountCodes extends AbstractMigration
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
        $table = $this->table('grand_account_codes', array("id"=>"id"));
        $table->addColumn('user_id', 'integer')
              ->addColumn('speedcode', 'string', array('limit' => 16))
              ->addColumn('combocode', 'string', array('limit' => 16))
              ->addColumn('title', 'string', array('limit' => 32))
              ->addColumn('project', 'string', array('limit' => 16))
              ->addIndex('user_id')
              ->create();
    }
}
