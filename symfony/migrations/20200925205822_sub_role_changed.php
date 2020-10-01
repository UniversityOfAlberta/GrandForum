<?php

use Phinx\Migration\AbstractMigration;

class SubRoleChanged extends AbstractMigration
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
        $table = $this->table('grand_role_subtype');
        $table->addColumn('changed', 'timestamp', array('default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'after' => 'sub_role'))
              ->save();
        $this->execute("UPDATE `grand_role_subtype` SET `changed` = '2020-08-01 00:00:00'");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
