<?php

use Phinx\Migration\AbstractMigration;

class UserRequestField extends AbstractMigration
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
        $this->table('grand_user_request')
             ->addColumn('nationality', 'string', array('after' => 'position', 'limit' => 64))
             ->addColumn('start_date', 'datetime', array('after' => 'nationality'))
             ->addColumn('end_date', 'datetime', array('after' => 'start_date'))
             ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
