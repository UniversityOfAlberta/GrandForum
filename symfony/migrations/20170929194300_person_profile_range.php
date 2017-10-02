<?php

use Phinx\Migration\AbstractMigration;

class PersonProfileRange extends AbstractMigration
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
        $table = $this->table('mw_user');
        $table->addColumn('profile_start_date', 'timestamp', array('after' => 'user_private_profile', 'default' => '0000:00:00 00-00-00'))
              ->addColumn('profile_end_date','timestamp', array('after' => 'profile_start_date', 'default' => '0000:00:00 00-00-00'))
              ->update();
    }
}
