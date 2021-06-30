<?php

use Phinx\Migration\AbstractMigration;

class UserCreateType extends AbstractMigration
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
             ->changeColumn('start_date', 'string', array('limit' => 256))
             ->changeColumn('end_date', 'string', array('limit' => 256))
             ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
