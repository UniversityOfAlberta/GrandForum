<?php

use Phinx\Migration\AbstractMigration;

class UserEmployeeId extends AbstractMigration
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
        $table = $this->table('mw_user');
	    $table->addColumn('employee_id', 'integer', array('after' => 'prev_last_name'))
	          ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
