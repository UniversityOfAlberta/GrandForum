<?php

use Phinx\Migration\AbstractMigration;

class DepartmentMemberStatus extends AbstractMigration
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
        $table = $this->table('grand_project_descriptions');
        $table->addColumn('member_status', 'string', array('after' => 'long_description', 'default' => 'Member'))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
